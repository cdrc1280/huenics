<?php

namespace App\Services\DocumentParsers;

use App\Models\Document;
use App\Models\DocumentLineItem;
use App\Models\DocumentTotal;
use App\Models\Product;
use App\Models\ProductAlias;
use App\Models\Project;
use App\Models\Vendor;
use App\Models\VendorDocumentLayout;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class DynamicDocumentParser
{
    public function __construct(
        protected PdfTextExtractor $textExtractor,
        protected FieldExtractor $fieldExtractor
    ) {}

    /**
     * Ingest and parse a document.
     *
     * @param Document $document
     * @return array{success: bool, confidence: float, line_items_count: int, message: string}
     */
    public function parseDocument(Document $document): array
    {
        $fullText = '';
        $lines = [];

        $filePath = $document->getAbsolutePath();
        if ($filePath && file_exists($filePath)) {
            $extractionResult = $this->textExtractor->extract($filePath);
            $fullText = $this->preprocessExtractedText($this->sanitizeUtf8($extractionResult['text']));
            $lines = array_map(fn($l) => $this->sanitizeUtf8($l), $extractionResult['lines']);
            $document->raw_extracted_text = $fullText;
            if (!empty($extractionResult['companion_pdf'])) {
                $document->companion_pdf_path = $extractionResult['companion_pdf'];
            }
        } elseif (!empty($document->raw_extracted_text)) {
            $fullText = $this->preprocessExtractedText($this->sanitizeUtf8($document->raw_extracted_text));
            $lines = array_map(fn($l) => $this->sanitizeUtf8($l), explode("\n", $fullText));
            $document->raw_extracted_text = $fullText;
        } else {
            $document->update([
                'status' => Document::STATUS_FAILED,
                'failure_reason' => "Physical file not found on disk: {$document->disk_path}",
            ]);
            return ['success' => false, 'confidence' => 0, 'line_items_count' => 0, 'message' => 'File not found'];
        }

        // 2. Auto-detect document type if needed
        $detectedType = $this->detectDocumentType($fullText);
        if ($detectedType && ($document->document_type === Document::TYPE_PURCHASE_ORDER && $detectedType !== Document::TYPE_PURCHASE_ORDER)) {
            $document->document_type = $detectedType;
        }

        // 3. Resolve Vendor if not set
        if (!$document->vendor_id) {
            $document->vendor_id = $this->detectVendorId($fullText);
        }

        // 4. Resolve Project if not set
        if (!$document->project_id) {
            $document->project_id = $this->detectProjectId($fullText);
        }

        // 5. Find layout configuration
        $layout = $this->resolveLayout($document->vendor_id, $document->document_type, $fullText);

        // 6. Extract Header metadata
        $headerData = $this->extractHeaderMetadata($layout, $fullText, $lines, $document->document_type);
        if (!empty($headerData['document_number'])) {
            $document->document_number = $headerData['document_number'];
        }
        if (!empty($headerData['document_date'])) {
            $document->document_date = $headerData['document_date'];
        }
        if (!empty($headerData['customer_name'])) {
            $document->customer_name = $headerData['customer_name'];
        }
        if (!empty($headerData['customer_company'])) {
            $document->customer_company = $headerData['customer_company'];
        }
        if (!empty($headerData['project_name'])) {
            $document->project_name = $headerData['project_name'];
        }
        if (!empty($headerData['project_location'])) {
            $document->project_location = $headerData['project_location'];
        }
        if (!empty($headerData['phone_no'])) {
            $document->phone_no = $headerData['phone_no'];
        }

        // 7. Extract Line Items
        $lineItems = $this->extractLineItems($layout, $fullText, $lines, $document);

        // 8. Extract Totals
        $totalsData = $this->extractTotals($layout, $fullText, $lines, $document->document_type);

        // 9. Save Line Items & Totals in DB
        $document->lineItems()->delete();
        foreach ($lineItems as $itemData) {
            $document->lineItems()->create($itemData);
        }

        // Create or update DocumentTotals
        $document->totals()->updateOrCreate(
            ['document_id' => $document->id],
            $totalsData
        );

        // 10. Calculate Confidence Score
        $confidence = $this->calculateConfidence($document, $headerData, $lineItems, $totalsData);
        $document->extraction_confidence = $confidence;
        $document->processed_at = now();
        $document->status = Document::STATUS_REQUIRES_REVIEW;
        $document->save();

        return [
            'success' => true,
            'confidence' => $confidence,
            'line_items_count' => count($lineItems),
            'message' => 'Document extracted successfully',
        ];
    }

    /**
     * Preprocess extracted raw text to separate merged prices, quantities, and words from OCR/PDF streams.
     */
    public function preprocessExtractedText(?string $text): string
    {
        if (empty($text)) {
            return '';
        }

        // 1. Separate digits and units e.g. "15pcs" -> "15 pcs", "500pcs" -> "500 pcs"
        $text = preg_replace('/(\d+)(pcs|set|sets|lot|unit|units|box|boxes|roll|rolls|meter|meters|pack|packs|pair|pairs|length|lengths|kg|ltr)\b/i', '$1 $2', $text);

        // 2. Separate merged currency/price numbers: e.g. "3,250.002,925.0043,875.00" -> "3,250.00 2,925.00 43,875.00"
        $text = preg_replace('/(\.\d{2})(?=\d)/', '$1 ', $text);

        // 3. Separate merged price followed immediately by letters: e.g. "43,875.00Led" or "298,620.00HISI" -> "43,875.00 Led"
        $text = preg_replace('/(\.\d{2})([A-Za-z])/u', '$1 $2', $text);

        // 4. Separate lowercase letter followed by capital item code if merged: e.g. "casingHISI" -> "casing HISI"
        $text = preg_replace('/([a-z0-9])(HISI[\-\_])/u', '$1 $2', $text);

        return $text;
    }

    /**
     * Auto-detect document type from header text.
     */
    public function detectDocumentType(string $text): ?string
    {
        $upper = mb_strtoupper($text);

        if (str_contains($upper, 'VENDORS AGREEMENT') || str_contains($upper, 'VENDOR\'S AGREEMENT') || str_contains($upper, 'QUOTATION')) {
            return Document::TYPE_VENDORS_AGREEMENT;
        }
        if (str_contains($upper, 'PURCHASE ORDER') || str_contains($upper, 'ORDER SLIP') || str_contains($upper, 'P.O.') || str_contains($upper, 'S.O.')) {
            return Document::TYPE_PURCHASE_ORDER;
        }
        if (str_contains($upper, 'DELIVERY RECEIPT') || str_contains($upper, 'D.R.')) {
            return Document::TYPE_DELIVERY_RECEIPT;
        }
        if (str_contains($upper, 'SALES INVOICE') || str_contains($upper, 'S.I.')) {
            return Document::TYPE_SALES_INVOICE;
        }

        return Document::TYPE_PURCHASE_ORDER;
    }

    /**
     * Resolve vendor from full text.
     */
    protected function detectVendorId(string $fullText): ?int
    {
        $vendors = Vendor::all();
        foreach ($vendors as $vendor) {
            if (stripos($fullText, $vendor->name) !== false) {
                return $vendor->id;
            }
            if ($vendor->slug && stripos($fullText, $vendor->slug) !== false) {
                return $vendor->id;
            }
        }
        return $vendors->first()?->id;
    }

    /**
     * Resolve project from full text.
     */
    protected function detectProjectId(string $fullText): ?int
    {
        $projects = Project::all();
        foreach ($projects as $project) {
            if (stripos($fullText, $project->name) !== false) {
                return $project->id;
            }
            if ($project->code && stripos($fullText, $project->code) !== false) {
                return $project->id;
            }
        }
        return $projects->first()?->id;
    }

    /**
     * Resolve layout configuration based on vendor, document type, or signature.
     */
    protected function resolveLayout(?int $vendorId, string $docType, string $fullText): ?VendorDocumentLayout
    {
        if ($vendorId) {
            $layout = VendorDocumentLayout::where('vendor_id', $vendorId)
                ->where('document_type', $docType)
                ->where('is_active', true)
                ->first();

            if ($layout) {
                return $layout;
            }
        }

        $activeLayouts = VendorDocumentLayout::where('document_type', $docType)
            ->where('is_active', true)
            ->get();

        foreach ($activeLayouts as $layout) {
            if ($layout->matchesSignature($fullText)) {
                return $layout;
            }
        }

        return null;
    }

    /**
     * Extract header metadata (Doc number, date, customer, project, etc.).
     */
    protected function extractHeaderMetadata(?VendorDocumentLayout $layout, string $fullText, array $lines, string $docType): array
    {
        $data = [
            'document_number' => null,
            'document_date' => null,
            'customer_name' => null,
            'customer_company' => null,
            'project_name' => null,
            'project_location' => null,
            'phone_no' => null,
        ];

        if ($layout) {
            $headerRules = $layout->header_rules ?? [];
            foreach ($headerRules as $field => $rules) {
                $rawVal = $this->fieldExtractor->extractByRules($fullText, $lines, $rules);
                if ($rawVal !== null) {
                    $data[$field] = $rawVal;
                }
            }
        }

        if (empty($data['document_number'])) {
            if (preg_match('/(?:Quotation|Quote|PO|P\.O\.|Order\s*Slip|S\.O\.|Sales\s*Order|Invoice|SI|DR|Delivery\s*Receipt)\s*(?:No\.?|NO|Number|\#)?\s*[:\.\-]?\s*([A-Za-z0-9\-\s\.\_\/]+?)(?=(?:\s+(?:Date|Dated|Customer|Company|Address|Page|For|Phone|Project)|\r|\n|$))/i', $fullText, $m)) {
                $candidate = trim($m[1]);
                if (preg_match('/\d/', $candidate) && !preg_match('/^(?:FORM|AGREEMENT|VENDORS|REFERENCES|PRODUCT|DESCRIPTION|QTY|UNIT|TOTAL)$/i', $candidate)) {
                    $data['document_number'] = $candidate;
                }
            }
        }

        if (empty($data['document_date'])) {
            if (preg_match('/(?:Date|Quotation\s*Date|PO\s*Date|Order\s*Date|Dated)\s*[:\.]?\s*([0-9]{1,2}[\/\-\.][0-9]{1,2}[\/\-\.][0-9]{2,4}|[A-Za-z]+\s+[0-9]{1,2},?\s+[0-9]{4}|[0-9]{4}[\/\-\.][0-9]{1,2}[\/\-\.][0-9]{1,2})/i', $fullText, $m)) {
                $data['document_date'] = $this->fieldExtractor->postProcess($m[1], 'parse_date');
            }
        }

        if (preg_match('/(?:Customer\s*(?:Name)?|Customs|Client|Sold\s*To|To|Attn|Attention)\s*[:\.\-]?\s*([^\r\n]+)/i', $fullText, $m)) {
            $data['customer_name'] = trim(preg_replace('/\s+(?:Company|Compmy|Address|Mdress|For\s*Project|Project|Date).*/i', '', $m[1]));
        }

        if (preg_match('/(?:Company\s*(?:Name)?|Compmy|Customer\s*Company)\s*[:\.\-]?\s*([^\r\n]+)/i', $fullText, $m)) {
            $data['customer_company'] = trim(preg_replace('/\s+(?:Address|Mdress|For\s*Project|Project|Date).*/i', '', $m[1]));
        }

        if (preg_match('/(?:For\s*Project|Project\s*Name|Project|Job\s*Site|Site)\s*[:\.\-]?\s*([^\r\n]+)/i', $fullText, $m)) {
            $data['project_name'] = trim(preg_replace('/\s+(?:Project\s*Location|Project\s*Loation|Location|Phone|Phme|Date).*/i', '', $m[1]));
        }

        if (preg_match('/(?:Project\s*Location|Project\s*Loation|Location|Delivery\s*Address|Site\s*Address)\s*[:\.\-]?\s*([^\r\n]+)/i', $fullText, $m)) {
            $data['project_location'] = trim(preg_replace('/\s+(?:Phone\s*No|Phme\s*No|Phone|Phme|\r|\n).*/i', '', $m[1]));
        }

        if (preg_match('/(?:Phone\s*(?:No\.?|\#)?|Phme\s*(?:No\.?|\#)?|Tel\s*(?:No\.?|\#)?|Contact\s*(?:No\.?|\#)?|Mobile)\s*[:\.\-]?\s*([0-9\-\s\(\)\+]+)/i', $fullText, $m)) {
            $data['phone_no'] = trim($m[1]);
        }

        return $data;
    }

    /**
     * Extract line items from table structure with multi-row decomposition,
     * table boundary isolation, self-healing quantity calculation, and noise rejection.
     */
    protected function extractLineItems(?VendorDocumentLayout $layout, string $fullText, array $lines, Document $document): array
    {
        // 1. Preprocess and isolate table section between header columns and footer notes/terms
        $tableText = $this->preprocessExtractedText($fullText);

        // Strip text up to table header columns
        if (preg_match('/(?:Item\s*Code|Product\s*Description|References\s*from\s*Client|Qty\s+Unit|Unit\s+Price|Discounted\s+Price|Total)(?:\s+(?:Item\s*Code|Product\s*Description|References\s*from\s*Client|Qty|Unit|Unit\s*Price|Discounted\s*Price|Total))*/i', $tableText, $matches, PREG_OFFSET_CAPTURE)) {
            $startPos = $matches[0][1] + strlen($matches[0][0]);
            $tableText = substr($tableText, $startPos);
        }

        // Truncate at end of table markers (notes, terms, totals)
        $stopPatterns = [
            '/\bTotal\s*Amount(?:\s*[:\.]|\s+[\d\,\.]+)/i',
            '/\bNegotiated\s*Amount\s*[:\.]?/i',
            '/\bPrices\s*are\s*subject\s*to\s*change/i',
            '/\bTerms\s*(?:and|&)\s*Conditions/i',
            '/\bStock\s*Availability/i',
            '/\bValidity\s*\d+/i',
            '/\bTerms\s*Of\s*Delivery/i',
            '/\bPayment\s*Terms/i',
            '/\bNOTES\s*:/i',
            '/\*\s*Minimum\s*amount/i',
            '/\*\s*Return\s*&\s*Exchange/i',
            '/\*\s*Gate\s*fees/i',
            '/\*\s*Please\s*inspect/i',
            '/\*\s*Special\s*order/i',
            '/I\/We\s*hereby\s*agree/i',
            '/How\s*To\s*Claim\s*The\s*Warranty/i',
            '/Customer\s*Service\s*No/i',
            '/Office\s*Add/i',
        ];

        $earliestStop = strlen($tableText);
        foreach ($stopPatterns as $p) {
            if (preg_match($p, $tableText, $sm, PREG_OFFSET_CAPTURE)) {
                if ($sm[0][1] < $earliestStop) {
                    $earliestStop = $sm[0][1];
                }
            }
        }

        $tableSection = substr($tableText, 0, $earliestStop);

        // 2. Global row regex scanning that decomposes concatenated rows & handles multiline descriptions
        $rowPattern = '/(?:(?<itemCode>HISI\s*[\-\_]?\s*(?:MTL\-\s*\d+W|[A-Z0-9\-\_]+)|[A-Z0-9]{2,10}\-[A-Z0-9\-\_]+)\s+)?(?<desc>.*?\b)\s*(?<qty>\d+(?:[\,\.]\d+)?)\s+(?<unit>pcs|set|sets|lot|unit|units|box|boxes|roll|rolls|meter|meters|pack|packs|pair|pairs|length|lengths|kg|ltr)\s+(?:₱|P|PHP)?\s*(?<unitPrice>[\d\,\.]+(?:\.\d{2})?)\s+(?:(?:₱|P|PHP)?\s*(?<discPrice>[\d\,\.]+(?:\.\d{2})?)\s+)?(?:₱|P|PHP)?\s*(?<total>[\d\,\.]+(?:\.\d{2})?)/is';

        preg_match_all($rowPattern, $tableSection, $matches, PREG_SET_ORDER);

        $items = [];
        $lineIndex = 1;
        $seenFingerprints = [];

        foreach ($matches as $m) {
            $itemCode = !empty($m['itemCode']) ? trim($m['itemCode']) : null;
            $rawDesc = trim($m['desc']);

            // Remove noise from repeated header column labels
            $rawDesc = preg_replace('/^(?:(?:Item\s*Code|Product\s*Description|References\s*from\s*Client|Qty|Unit|Unit\s*Price|Discounted\s*Price|Total|Price)\s*)+/i', '', $rawDesc);
            $rawDesc = preg_replace('/^\d+[\.\)]\s+/', '', $rawDesc);
            $rawDesc = trim(preg_replace('/\s+/', ' ', $rawDesc));

            if (empty($rawDesc)) {
                continue;
            }

            // If Item Code was captured at beginning of description
            if (!$itemCode && preg_match('/^(HISI\s*[\-\_]?\s*(?:MTL\-\s*\d+W|[A-Z0-9\-\_]+)|[A-Z0-9]{2,10}\-[A-Z0-9\-\_]+)\s+(.*)$/i', $rawDesc, $cm)) {
                $itemCode = trim($cm[1]);
                $rawDesc = trim($cm[2]);
            }

            $qty = (float) str_replace(',', '', $m['qty']);
            $unit = strtolower(trim($m['unit']));
            $unitPrice = (float) str_replace(',', '', $m['unitPrice']);
            $discPrice = !empty($m['discPrice']) ? (float) str_replace(',', '', $m['discPrice']) : null;
            $printedTotal = (float) str_replace(',', '', $m['total']);

            $effectiveUnitPrice = ($discPrice !== null && $discPrice > 0) ? $discPrice : $unitPrice;
            $computedTotal = round($qty * $effectiveUnitPrice, 2);

            // Self-heal quantity if OCR merged or shifted numbers (e.g. 3250 vs 15 @ 2925)
            if ($effectiveUnitPrice > 0 && abs($qty * $effectiveUnitPrice - $printedTotal) > 1.0) {
                $recomputedQty = round($printedTotal / $effectiveUnitPrice, 4);
                if (abs($recomputedQty * $effectiveUnitPrice - $printedTotal) < 0.05 && $recomputedQty > 0) {
                    $qty = $recomputedQty;
                    $computedTotal = round($qty * $effectiveUnitPrice, 2);
                }
            }

            $totalMismatch = abs($printedTotal - $computedTotal) > 0.01;

            // Deduplication check: Avoid adding exact duplicate items from repetitive OCR passes
            $fingerprint = md5(($itemCode ?? '') . '|' . strtolower($rawDesc) . '|' . $qty . '|' . $unitPrice . '|' . ($discPrice ?? 0) . '|' . $printedTotal);
            if (isset($seenFingerprints[$fingerprint])) {
                continue;
            }
            $seenFingerprints[$fingerprint] = true;

            $items[] = [
                'line_no' => $lineIndex++,
                'material_code' => $this->sanitizeUtf8($itemCode),
                'description' => $this->sanitizeUtf8($rawDesc),
                'qty' => $qty,
                'unit' => $this->sanitizeUtf8($unit),
                'unit_price' => $unitPrice,
                'discounted_price' => $discPrice,
                'printed_total' => $printedTotal,
                'computed_total' => $computedTotal,
                'total_mismatch' => $totalMismatch,
                'product_id' => $this->matchProductByDescription($rawDesc, $document->vendor_id, $itemCode, $unitPrice, $unit),
                'raw_line_text' => $this->sanitizeUtf8($m[0]),
            ];
        }

        // Fallback: If global regex matched 0 items, use line-by-line parser on raw lines
        if (empty($items)) {
            $descBuffer = [];
            $pendingCode = null;
            $lastCode = null;

            foreach ($lines as $rawLine) {
                $line = trim($this->sanitizeUtf8($rawLine));
                if (empty($line)) continue;

                if (preg_match('/^(?:HUENICS|Colors\s*•|VENDORS\s*AGREEMENT|Quotation|Customer|Customs|Company|Compmy|Address|Mdress|2F\s*Starmall|For\s*Project|Project|Project\s*Location|Phone|Item|Product|Description|Discounted|Price|Unit\s*Price|Total|Total\s*Amount|Negotiated\s*Amount|Prices\s*are\s*subject|Terms\s*and\s*Conditions|Validity|Stock\s*Availability|Terms\s*Of\s*Delivery|Payment\s*Terms|Remarks|NOTES|Minimum\s*amount|Return|Gate\s*fees|Please\s*inspect|Special\s*order|I\/We\s*hereby|Customer\'s\s*Name|Prepared\s*by|Approved\s*by|Customer\s*Service|Office\s*Add|THE\s*WARRANTY)/i', $line)) {
                    continue;
                }

                $prefixText = '';
                $qty = 0.0;
                $unit = '';
                $unitPrice = 0.0;
                $discountedPrice = null;
                $printedTotal = 0.0;
                $matched = false;

                if (preg_match('/^(.*?)\s*([\d\,\.]+)\s+([A-Za-z]+)\s+(?:₱|P|PHP)?\s*([\d\,\.]+)\s+(?:₱|P|PHP)?\s*([\d\,\.]+)((?:\s+(?:₱|P|PHP)?\s*[\d\,\.]+)?)$/i', $line, $m)) {
                    $prefixText = trim($m[1]);
                    $qty = (float) str_replace(',', '', $m[2]);
                    $unit = trim($m[3]);
                    $unitPrice = (float) str_replace(',', '', $m[4]);
                    $discountedPrice = (float) str_replace(',', '', $m[5]);
                    $tStr = trim($m[6]);
                    $tStr = preg_replace('/(?:₱|P|PHP)?\s*/i', '', $tStr);
                    $printedTotal = !empty($tStr) ? (float) str_replace(',', '', $tStr) : round($qty * $discountedPrice, 2);
                    $matched = true;
                } elseif (preg_match('/^(.*?)\s*([\d\,\.]+)\s+([A-Za-z]+)\s+(?:₱|P|PHP)?\s*([\d\,\.]+)\s+(?:₱|P|PHP)?\s*([\d\,\.]+)$/i', $line, $m)) {
                    $prefixText = trim($m[1]);
                    $qty = (float) str_replace(',', '', $m[2]);
                    $unit = trim($m[3]);
                    $unitPrice = (float) str_replace(',', '', $m[4]);
                    $tStr = trim($m[5]);
                    $tStr = preg_replace('/(?:₱|P|PHP)?\s*/i', '', $tStr);
                    $printedTotal = (float) str_replace(',', '', $tStr);
                    $matched = true;
                }

                if ($matched && $qty > 0 && $unitPrice > 0) {
                    if (!empty($prefixText)) {
                        $descBuffer[] = $prefixText;
                    }
                    $itemCode = $pendingCode ?: $lastCode;
                    $desc = implode(' ', $descBuffer);
                    $desc = preg_replace('/^(?:(?:Item\s*Code|Product\s*Description|References\s*from\s*Client|Qty|Unit|Unit\s*Price|Discounted\s*Price|Total|Price)\s*)+/i', '', $desc);
                    if (empty($desc)) {
                        $desc = "Item line " . $lineIndex;
                    }

                    $effectiveUnitPrice = ($discountedPrice !== null && $discountedPrice > 0) ? $discountedPrice : $unitPrice;
                    $computedTotal = round($qty * $effectiveUnitPrice, 2);

                    $items[] = [
                        'line_no' => $lineIndex++,
                        'material_code' => $this->sanitizeUtf8($itemCode),
                        'description' => $this->sanitizeUtf8($desc),
                        'qty' => $qty,
                        'unit' => $this->sanitizeUtf8($unit ?: 'pcs'),
                        'unit_price' => $unitPrice,
                        'discounted_price' => $discountedPrice,
                        'printed_total' => $printedTotal,
                        'computed_total' => $computedTotal,
                        'total_mismatch' => abs($printedTotal - $computedTotal) > 0.01,
                        'product_id' => $this->matchProductByDescription($desc, $document->vendor_id, $itemCode, $unitPrice, $unit),
                        'raw_line_text' => $this->sanitizeUtf8($rawLine),
                    ];

                    $descBuffer = [];
                    $pendingCode = null;
                } else {
                    $descBuffer[] = $line;
                }
            }
        }

        return $items;
    }

    public function sanitizeUtf8(?string $string): ?string
    {
        if ($string === null || trim($string) === '') {
            return null;
        }
        $string = str_replace("\xD8", 'Ø', $string);
        $converted = @mb_convert_encoding($string, 'UTF-8', 'UTF-8');
        return $converted ?: $string;
    }

    /**
     * Extract Subtotal, VAT, Grand Total, and Negotiated Amount.
     */
    protected function extractTotals(?VendorDocumentLayout $layout, string $fullText, array $lines, string $docType): array
    {
        $totals = [
            'printed_subtotal' => null,
            'printed_vat' => null,
            'printed_total' => null,
            'negotiated_amount' => null,
            'computed_subtotal' => 0,
            'computed_vat' => 0,
            'computed_grand_total' => 0,
            'subtotal_mismatch' => false,
            'vat_mismatch' => false,
            'total_mismatch' => false,
        ];

        // 1. Regex search for printed subtotal
        if (preg_match('/(?:subtotal|sub-total|total\s*before\s*vat|net\s*amount)\s*[:\.]?\s*(?:PHP|₱)?\s*([\d\,\.]+)/i', $fullText, $m)) {
            $totals['printed_subtotal'] = (float) str_replace(',', '', $m[1]);
        }

        // 2. Regex search for printed VAT (12%)
        if (preg_match('/(?:12\%\s*VAT|VAT\s*(?:12\%)?|Value\s*Added\s*Tax)\s*[:\.]?\s*(?:PHP|₱)?\s*([\d\,\.]+)/i', $fullText, $m)) {
            $totals['printed_vat'] = (float) str_replace(',', '', $m[1]);
        }

        // 3. Regex search for printed grand total
        if (preg_match('/(?:grand\s*total|total\s*amount|total\s*due|amount\s*payable|total)\s*[:\.]?\s*(?:PHP|₱)?\s*([\d\,\.]+)/i', $fullText, $m)) {
            $raw = trim($m[1]);
            if (substr_count($raw, '.') > 1) {
                $lastDot = strrpos($raw, '.');
                $whole = str_replace('.', '', substr($raw, 0, $lastDot));
                $dec = substr($raw, $lastDot + 1);
                $totals['printed_total'] = (float) ($whole . '.' . $dec);
            } else {
                $totals['printed_total'] = (float) str_replace(',', '', $raw);
            }
        }

        // 4. Quotations: Search for "Negotiated Amount"
        if (preg_match('/(?:negotiated\s*amount|discounted\s*total|agreed\s*amount|final\s*deal)\s*[:\.]?\s*(?:PHP|₱)?\s*([\d\,\.]+)/i', $fullText, $m)) {
            $raw = trim($m[1]);
            if (substr_count($raw, '.') > 1) {
                $lastDot = strrpos($raw, '.');
                $whole = str_replace('.', '', substr($raw, 0, $lastDot));
                $dec = substr($raw, $lastDot + 1);
                $totals['negotiated_amount'] = (float) ($whole . '.' . $dec);
            } else {
                $totals['negotiated_amount'] = (float) str_replace(',', '', $raw);
            }
        }

        return $totals;
    }

    /**
     * Normalize description, lookup product aliases or canonical match, and auto-create product if not existing.
     */
    protected function matchProductByDescription(string $description, ?int $vendorId = null, ?string $itemCode = null, ?float $unitPrice = null, ?string $unit = null): ?int
    {
        $cleanName = trim($description);
        if (empty($cleanName)) {
            return null;
        }

        $normalized = ProductAlias::normalize($cleanName);

        // 1. Direct alias match (vendor-specific first, then global)
        if (!empty($normalized)) {
            $alias = ProductAlias::where('normalized_alias', $normalized)
                ->where(function ($q) use ($vendorId) {
                    if ($vendorId) {
                        $q->where('vendor_id', $vendorId)->orWhereNull('vendor_id');
                    } else {
                        $q->whereNull('vendor_id');
                    }
                })
                ->first();

            if ($alias) {
                return $alias->product_id;
            }
        }

        // 2. Direct canonical product name match (ensures variant distinction like "Color White" is respected)
        $product = Product::whereRaw('LOWER(canonical_name) = ?', [Str::lower($cleanName)])->first();
        if ($product) {
            return $product->id;
        }

        // 3. Auto-create distinct product if not existing in DB so it immediately appears in the dropdown!
        try {
            $sku = $itemCode;
            if ($sku && Product::where('sku', $sku)->exists()) {
                // If another product has this base SKU, keep SKU unique or null on product record while preserving exact material_code on line item
                $sku = null;
            }

            $newProduct = Product::create([
                'canonical_name' => $cleanName,
                'sku' => $sku,
                'unit_default' => $unit ?: 'pcs',
                'default_price' => $unitPrice ?: null,
                'selling_price' => $unitPrice ?: null,
                'is_huenics_owned' => true,
                'is_active' => true,
            ]);

            if (!empty($normalized)) {
                ProductAlias::create([
                    'product_id' => $newProduct->id,
                    'alias_text' => $cleanName,
                    'normalized_alias' => $normalized,
                    'vendor_id' => $vendorId,
                ]);
            }

            \Illuminate\Support\Facades\Cache::forget('lookup_products_list');

            return $newProduct->id;
        } catch (\Throwable $e) {
            Log::warning("Auto-creating product for line item '{$cleanName}' notice: " . $e->getMessage());
        }

        return null;
    }

    /**
     * Confidence scoring based on parsed field completeness.
     */
    protected function calculateConfidence(Document $document, array $header, array $lineItems, array $totals): float
    {
        $score = 100.0;

        if (empty($header['document_number'])) {
            $score -= 15.0;
        }
        if (empty($header['document_date'])) {
            $score -= 10.0;
        }
        if (empty($lineItems)) {
            $score -= 40.0;
        }
        if (empty($totals['printed_total']) && empty($totals['negotiated_amount'])) {
            $score -= 15.0;
        }

        return max(0.0, min(100.0, $score));
    }
}
