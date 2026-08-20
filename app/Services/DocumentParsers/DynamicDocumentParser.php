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
            $fullText = $this->sanitizeUtf8($extractionResult['text']);
            $lines = array_map(fn($l) => $this->sanitizeUtf8($l), $extractionResult['lines']);
            $document->raw_extracted_text = $fullText;
            if (!empty($extractionResult['companion_pdf'])) {
                $document->companion_pdf_path = $extractionResult['companion_pdf'];
            }
        } elseif (!empty($document->raw_extracted_text)) {
            $fullText = $this->sanitizeUtf8($document->raw_extracted_text);
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
     * Auto-detect document type from header text.
     */
    public function detectDocumentType(string $text): ?string
    {
        $upper = mb_strtoupper($text);

        if (str_contains($upper, 'VENDORS AGREEMENT') || str_contains($upper, 'VENDOR\'S AGREEMENT') || str_contains($upper, 'QUOTATION')) {
            return Document::TYPE_VENDORS_AGREEMENT;
        }

        if (str_contains($upper, 'PURCHASE ORDER') || str_contains($upper, 'PO NO.') || str_contains($upper, 'P.O. NO.') || str_contains($upper, 'ORDER SLIP') || str_contains($upper, 'S.O.')) {
            return Document::TYPE_PURCHASE_ORDER;
        }

        return null;
    }

    /**
     * Resolve vendor from content.
     */
    protected function detectVendorId(string $text): ?int
    {
        $vendors = Vendor::where('is_active', true)->get();
        foreach ($vendors as $vendor) {
            if (stripos($text, $vendor->name) !== false || ($vendor->tin && str_contains($text, $vendor->tin))) {
                return $vendor->id;
            }
        }

        // Default to first vendor (e.g. Huenics) if exists
        return $vendors->first()?->id;
    }

    /**
     * Resolve project from content.
     */
    protected function detectProjectId(string $text): ?int
    {
        $projects = Project::all();
        foreach ($projects as $project) {
            if (stripos($text, $project->name) !== false || ($project->code && stripos($text, $project->code) !== false)) {
                return $project->id;
            }
        }

        // Look for project name patterns like "Project: Palanza Tower", "Job: ...", "Site: ..."
        if (preg_match('/(?:project|project\s*name|job\s*site|site)\s*[:\-]?\s*([^\r\n\,]+)/i', $text, $matches)) {
            $projectName = trim($matches[1]);
            if (strlen($projectName) > 2) {
                $project = Project::firstOrCreate(
                    ['name' => $projectName],
                    ['customer_name' => 'Auto-Detected']
                );
                return $project->id;
            }
        }

        return $projects->first()?->id;
    }

    /**
     * Resolve layout configuration.
     */
    protected function resolveLayout(?int $vendorId, string $documentType, string $fullText): ?VendorDocumentLayout
    {
        if ($vendorId) {
            $layout = VendorDocumentLayout::where('vendor_id', $vendorId)
                ->where('document_type', $documentType)
                ->where('is_active', true)
                ->with('fieldMappings')
                ->latest('layout_version')
                ->first();

            if ($layout) {
                return $layout;
            }
        }

        // Try to find any active layout matching document type
        return VendorDocumentLayout::where('document_type', $documentType)
            ->where('is_active', true)
            ->with('fieldMappings')
            ->first();
    }

    /**
     * Extract header fields.
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

        // 1. Try DB layout field mappings if present
        if ($layout) {
            $docNumMapping = $layout->fieldMappings->where('field_key', 'document_number')->first();
            if ($docNumMapping) {
                $val = $this->fieldExtractor->extractField($docNumMapping, $fullText, $lines);
                if ($val && preg_match('/\d/', (string) $val) && !preg_match('/^(?:FORM|AGREEMENT|VENDORS|REFERENCES|PRODUCT|DESCRIPTION|QTY|UNIT|TOTAL)$/i', (string) $val)) {
                    $data['document_number'] = $val;
                }
            }

            $dateMapping = $layout->fieldMappings->where('field_key', 'document_date')->first();
            if ($dateMapping) {
                $data['document_date'] = $this->fieldExtractor->extractField($dateMapping, $fullText, $lines);
            }
        }

        // 2. Smart fallback regex if not extracted
        if (empty($data['document_number'])) {
            if (preg_match('/(?:Quotation|Quote)\s*(?:No\.?|Number|\#)?\s*[:\.\-]?\s*([A-Za-z0-9\-\_\s]+?)(?=\s+(?:Date|Dated)|\r|\n|$)/i', $fullText, $m)) {
                $candidate = trim($m[1]);
                if (preg_match('/\d/', $candidate) && !preg_match('/^(?:FORM|AGREEMENT|VENDORS|REFERENCES|PRODUCT|DESCRIPTION|QTY|UNIT|TOTAL)$/i', $candidate)) {
                    $data['document_number'] = $candidate;
                }
            }
            if (empty($data['document_number']) && preg_match('/(?:Purchase\s*Order|P\.?O\.?|Order\s*Slip|S\.?O\.?)\s*(?:No\.?|Number|\#)?\s*[:\.\-]?\s*([A-Za-z0-9\-\_\s]+?)(?=\s+(?:Date|Dated)|\r|\n|$)/i', $fullText, $m)) {
                $candidate = trim($m[1]);
                if (preg_match('/\d/', $candidate) && !preg_match('/^(?:FORM|AGREEMENT|VENDORS|REFERENCES|PRODUCT|DESCRIPTION|QTY|UNIT|TOTAL)$/i', $candidate)) {
                    $data['document_number'] = $candidate;
                }
            }
            // Standalone document number patterns near top of document
            if (empty($data['document_number']) && preg_match('/\b(26\d{4}\s*-\s*[A-Za-z0-9]+|\d{6,12}(?:-\w+)?|PO-\d+|SO-\d+)\b/i', $fullText, $m)) {
                $data['document_number'] = trim($m[1]);
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
     * Extract line items from table structure.
     */
    protected function extractLineItems(?VendorDocumentLayout $layout, string $fullText, array $lines, Document $document): array
    {
        $items = [];
        $lineIndex = 1;
        $descBuffer = [];
        $pendingCode = null;
        $lastCode = null;

        $cleanDescBuffer = function (array $buffer): string {
            $clean = [];
            foreach ($buffer as $line) {
                $l = trim($line);
                if (empty($l)) continue;
                if (preg_match('/^(?:Price|Discounted|Total|Unit|Qty|Item\s*Code|Product\s*Description)/i', $l)) continue;
                $l = preg_replace('/^\d+[\.\)]\s+/', '', $l);
                $l = preg_replace('/\s+(?:₱|P|PHP)?\s*[\d\,\.]+\.\d{2}\s*$/i', '', $l);
                if (!empty($l)) {
                    $clean[] = $l;
                }
            }
            return implode(" ", $clean);
        };

        foreach ($lines as $rawLine) {
            $line = trim($this->sanitizeUtf8($rawLine));
            if (empty($line)) {
                continue;
            }

            // Header / Footer skip triggers
            if (preg_match('/^(?:HUENICS|Colors\s*•|VENDORS\s*AGREEMENT|Quotation|Customer|Customs|Company|Compmy|Address|Mdress|2F\s*Starmall|For\s*Project|Project|Project\s*Location|Project\s*Loation|Phone|Phme|Item|Product|Description|Discounted|Price|Unit\s*Price|Total|Total\s*Amount|Negotiated\s*Amount|Prices\s*are\s*subject|Terms\s*and\s*Conditions|Validity|Stock\s*Availability|Terms\s*Of\s*Delivery|Payment\s*Terms|Remarks|NOTES|Minimum\s*amount|Return|Gate\s*fees|Please\s*inspect|Special\s*order|I\/We\s*hereby|Customer\'s\s*Name|Prepared\s*by|Approved\s*by|Customer\s*Service|Office\s*Add|THE\s*WARRANTY|Itæ|Qty|trait)/i', $line)) {
                continue;
            }

            $prefixText = '';
            $qty = 0.0;
            $unit = '';
            $unitPrice = 0.0;
            $discountedPrice = null;
            $printedTotal = 0.0;
            $matched = false;

            // 5 or 6 columns: Qty, Unit, UnitPrice, DiscountedPrice, [Total]
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
            }
            // 4 columns: Qty, Unit, UnitPrice, Total
            elseif (preg_match('/^(.*?)\s*([\d\,\.]+)\s+([A-Za-z]+)\s+(?:₱|P|PHP)?\s*([\d\,\.]+)\s+(?:₱|P|PHP)?\s*([\d\,\.]+)$/i', $line, $m)) {
                $prefixText = trim($m[1]);
                $qty = (float) str_replace(',', '', $m[2]);
                $unit = trim($m[3]);
                $unitPrice = (float) str_replace(',', '', $m[4]);
                
                $tStr = trim($m[5]);
                $tStr = preg_replace('/(?:₱|P|PHP)?\s*/i', '', $tStr);
                $printedTotal = (float) str_replace(',', '', $tStr);
                $matched = true;
            }
            // 3 columns: Qty, UnitPrice, Total
            elseif (preg_match('/^(.*?)\s*([\d\,\.]+)\s+(?:₱|P|PHP)?\s*([\d\,\.]+)\s+(?:₱|P|PHP)?\s*([\d\,\.]+)$/i', $line, $m)) {
                $prefixText = trim($m[1]);
                $qty = (float) str_replace(',', '', $m[2]);
                $unit = 'pcs';
                $unitPrice = (float) str_replace(',', '', $m[3]);
                
                $tStr = trim($m[4]);
                $tStr = preg_replace('/(?:₱|P|PHP)?\s*/i', '', $tStr);
                $printedTotal = (float) str_replace(',', '', $tStr);
                $matched = true;
            }

            if ($matched && $qty > 0 && $unitPrice > 0) {
                if (!empty($prefixText)) {
                    $descBuffer[] = $prefixText;
                }

                $itemCode = $pendingCode ?: $lastCode;
                if (!$itemCode && !empty($descBuffer)) {
                    $firstLine = $descBuffer[0];
                    if (preg_match('/^(HISI\s*[\-\_]?\s*(?:MTL\-\s*\d+W|[A-Z0-9\-\_]+))\s*(.*)$/i', $firstLine, $cm)) {
                        $itemCode = trim($cm[1]);
                        $descBuffer[0] = trim($cm[2]);
                    } elseif (preg_match('/^([A-Z0-9]{2,10}\-[A-Z0-9\-\_]+)\s*(.*)$/i', $firstLine, $cm)) {
                        $itemCode = trim($cm[1]);
                        $descBuffer[0] = trim($cm[2]);
                    }
                }

                if ($itemCode) {
                    $lastCode = $itemCode;
                }

                $desc = $cleanDescBuffer($descBuffer);
                if (empty($desc)) {
                    $desc = "Item line " . $lineIndex;
                }

                $effectiveUnitPrice = ($discountedPrice !== null && $discountedPrice > 0) ? $discountedPrice : $unitPrice;
                $computedTotal = round($qty * $effectiveUnitPrice, 2);
                $totalMismatch = abs($printedTotal - $computedTotal) > 0.01;

                $items[] = [
                    'line_no' => $lineIndex++,
                    'material_code' => $this->sanitizeUtf8($itemCode),
                    'description' => $this->sanitizeUtf8($desc),
                    'qty' => $qty,
                    'unit' => $this->sanitizeUtf8($unit),
                    'unit_price' => $unitPrice,
                    'discounted_price' => $discountedPrice,
                    'printed_total' => $printedTotal,
                    'computed_total' => $computedTotal,
                    'total_mismatch' => $totalMismatch,
                    'product_id' => $this->matchProductByDescription($desc, $document->vendor_id),
                    'raw_line_text' => $this->sanitizeUtf8($rawLine),
                ];

                $descBuffer = [];
                $pendingCode = null;
                continue;
            }

            // Check if line is a standalone Item Code
            if (preg_match('/^(HISI\s*[\-\_]?\s*(?:MTL\-\s*\d+W|[A-Z0-9\-\_]+)|[A-Z0-9]{2,10}\-[A-Z0-9\-\_]+)$/i', $line)) {
                $pendingCode = $line;
                continue;
            }

            // Otherwise, buffer line as description line
            $descBuffer[] = $line;
        }

        return $items;
    }

    public function sanitizeUtf8(?string $string): string
    {
        if (empty($string)) {
            return '';
        }
        $string = str_replace("\xD8", 'Ø', $string);
        $converted = @mb_convert_encoding($string, 'UTF-8', 'UTF-8');
        return @iconv('UTF-8', 'UTF-8//IGNORE', $converted) ?: $converted;
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
     * Normalize description and lookup product aliases.
     */
    protected function matchProductByDescription(string $description, ?int $vendorId = null): ?int
    {
        $normalized = ProductAlias::normalize($description);
        if (empty($normalized)) {
            return null;
        }

        // 1. Direct alias match (vendor-specific first)
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

        // 2. Direct canonical product name match
        $product = Product::whereRaw('LOWER(canonical_name) = ?', [Str::lower(trim($description))])->first();
        if ($product) {
            return $product->id;
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
