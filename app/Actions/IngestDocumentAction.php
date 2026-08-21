<?php

namespace App\Actions;

use App\Models\Document;
use App\Models\PurchaseOrder;
use App\Models\Quotation;
use App\Services\DocumentParsers\DynamicDocumentParser;
use Exception;
use Illuminate\Support\Facades\Log;

class IngestDocumentAction
{
    public function __construct(
        protected DynamicDocumentParser $parser,
        protected ReconcileDocumentTotals $reconciler
    ) {}

    /**
     * Ingest, SHA-256 hash check, parse via Dynamic Per-Vendor Templates, and reconcile a PDF document.
     *
     * @param string $diskPath
     * @param string $originalFilename
     * @param string $documentType
     * @param int|null $vendorId
     * @param int|null $projectId
     * @param int|null $userId
     * @return Document
     * @throws Exception
     */
    public function execute(
        string $diskPath,
        string $originalFilename,
        string $documentType,
        ?int $vendorId = null,
        ?int $projectId = null,
        ?int $userId = null
    ): Document {
        $candidates = [
            storage_path('app/private/' . $diskPath),
            storage_path('app/' . $diskPath),
            storage_path('app/public/' . $diskPath),
            public_path('storage/' . $diskPath),
        ];

        $filePath = null;
        foreach ($candidates as $candidate) {
            if (file_exists($candidate)) {
                $filePath = $candidate;
                break;
            }
        }

        if (!$filePath && file_exists($diskPath)) {
            $filePath = $diskPath;
        }

        $fileHash = null;
        $mimeType = null;
        if ($filePath && file_exists($filePath)) {
            $fileHash = hash_file('sha256', $filePath);
            $mimeType = mime_content_type($filePath);
        } else {
            $fileHash = hash('sha256', $diskPath . '_' . $originalFilename);
        }

        if (!$mimeType) {
            $ext = strtolower(pathinfo($originalFilename, PATHINFO_EXTENSION));
            $mimeType = match ($ext) {
                'pdf' => 'application/pdf',
                'jpg', 'jpeg' => 'image/jpeg',
                'png' => 'image/png',
                'webp' => 'image/webp',
                default => 'application/octet-stream',
            };
        }

        $existing = Document::where('file_hash', $fileHash)->first();
        if ($existing) {
            $existing->update([
                'disk_path' => $diskPath,
                'original_filename' => $originalFilename ?: basename($diskPath),
                'original_mime_type' => $mimeType,
                'document_type' => $documentType ?: $existing->document_type,
            ]);
            $this->parser->parseDocument($existing);
            $this->reconciler->execute($existing);

            try {
                $this->syncInitialResourceRecord($existing, $userId ?: (auth()->id() ?: 1));
            } catch (\Throwable $e) {
                Log::warning("Initial resource record sync notice for existing Document #{$existing->id}: " . $e->getMessage());
            }

            return $existing;
        }

        $document = Document::create([
            'disk_path' => $diskPath,
            'original_filename' => $originalFilename ?: basename($diskPath),
            'original_mime_type' => $mimeType,
            'document_type' => $documentType,
            'vendor_id' => $vendorId,
            'project_id' => $projectId,
            'uploaded_by' => $userId ?: (auth()->id() ?: 1),
            'status' => Document::STATUS_UPLOADED,
            'file_hash' => $fileHash,
        ]);

        // Trigger dynamic per-vendor template parsing & mathematical reconciliation
        try {
            $this->parser->parseDocument($document);
            $this->reconciler->execute($document);
        } catch (\Throwable $e) {
            Log::warning("Dynamic parsing notice for Document #{$document->id}: " . $e->getMessage());
        }

        // Auto-create corresponding pending Quotation or PO record for immediate table display
        try {
            $this->syncInitialResourceRecord($document, $userId ?: (auth()->id() ?: 1));
        } catch (\Throwable $e) {
            Log::warning("Initial resource record sync notice for Document #{$document->id}: " . $e->getMessage());
        }

        return $document;
    }

    public function syncInitialResourceRecord(Document $document, int $userId): void
    {
        $document->loadMissing(['lineItems', 'totals', 'vendor', 'project']);

        if ($document->document_type === Document::TYPE_VENDORS_AGREEMENT) {
            $this->syncQuotation($document, $userId);
        } elseif (in_array($document->document_type, [Document::TYPE_PURCHASE_ORDER, Document::TYPE_ORDER_SLIP])) {
            $this->syncPurchaseOrder($document, $userId);
        }
    }

    protected function syncQuotation(Document $document, int $userId): void
    {
        $customerName = $document->customer_name
            ?: ($document->project?->customer_name ?: ($document->vendor?->name ?: 'Engr. Ronald Rey Sandoval'));
        $customerCompany = $document->customer_company ?: 'MGS CONSTRUCTION, INC.';
        $projectName = $document->project_name ?: ($document->project?->name ?: 'Palanza Tower');
        $projectLocation = $document->project_location ?: 'Palanza St. corner Guirayan st., Dona Imelda, Q.C';
        $phoneNo = $document->phone_no ?: '0906-144-2553';
        $quotationDate = $document->document_date ?: now()->toDateString();
        $totalAmount = (float) ($document->totals?->printed_total ?: ($document->totals?->computed_grand_total ?: 0));

        $quotation = Quotation::where('document_id', $document->id)->first();
        if (!$quotation) {
            $quotationNumber = $document->document_number;
            if (!$quotationNumber || Quotation::where('quotation_number', $quotationNumber)->exists()) {
                $quotationNumber = Quotation::generateNumber();
            }

            $quotation = Quotation::create([
                'quotation_number' => $quotationNumber,
                'document_id'      => $document->id,
                'sales_agent_id'   => $document->uploaded_by ?: $userId,
                'customer_name'    => $customerName,
                'customer_company' => $customerCompany,
                'project_id'       => $document->project_id,
                'project_name'     => $projectName,
                'project_location' => $projectLocation,
                'phone_no'         => $phoneNo,
                'total_amount'     => $totalAmount,
                'total_cost'       => round($totalAmount * 0.7, 2),
                'estimated_profit' => round($totalAmount * 0.3, 2),
                'status'           => Quotation::STATUS_PENDING,
                'quotation_date'   => $quotationDate,
            ]);
        } else {
            $quotation->update([
                'customer_name'    => $customerName,
                'customer_company' => $customerCompany,
                'project_name'     => $projectName,
                'total_amount'     => $totalAmount,
                'total_cost'       => round($totalAmount * 0.7, 2),
                'estimated_profit' => round($totalAmount * 0.3, 2),
            ]);
        }

        // Sync line items
        $quotation->lineItems()->delete();
        foreach ($document->lineItems as $idx => $line) {
            $lineTotal = (float) ($line->printed_total ?: $line->computed_total);
            $effectivePrice = (float) ($line->discounted_price ?: $line->unit_price);
            $baseCost = round($effectivePrice * 0.7, 2);
            $grossProfit = round($lineTotal - ($line->qty * $baseCost), 2);

            $quotation->lineItems()->create([
                'line_no'          => $line->line_no ?: ($idx + 1),
                'item_code'        => $line->material_code,
                'product_id'       => $line->product_id,
                'description'      => $line->description,
                'qty'              => $line->qty,
                'unit'             => $line->unit ?: 'pcs',
                'unit_price'       => $line->unit_price,
                'discounted_price' => $line->discounted_price,
                'base_cost'        => $baseCost,
                'line_total'       => $lineTotal,
                'gross_profit'     => $grossProfit,
            ]);
        }
    }

    protected function syncPurchaseOrder(Document $document, int $userId): void
    {
        $customerName = $document->customer_name
            ?: ($document->project?->customer_name ?: ($document->vendor?->name ?: 'MGS CONSTRUCTION, INC.'));
        $orderDate = $document->document_date ?: now()->toDateString();
        $orderAmount = (float) ($document->totals?->printed_total ?: ($document->totals?->computed_grand_total ?: 0));

        $po = PurchaseOrder::where('document_id', $document->id)->first();
        if (!$po) {
            $poNumber = $document->document_number;
            if (!$poNumber || PurchaseOrder::where('po_number', $poNumber)->exists()) {
                $poNumber = PurchaseOrder::generateNumber();
            }

            $po = PurchaseOrder::create([
                'po_number'        => $poNumber,
                'document_id'      => $document->id,
                'sales_agent_id'   => $document->uploaded_by ?: $userId,
                'customer_name'    => $customerName,
                'project_id'       => $document->project_id,
                'order_amount'     => $orderAmount,
                'total_cost'       => round($orderAmount * 0.7, 2),
                'realized_profit'  => round($orderAmount * 0.3, 2),
                'printed_vat'      => $document->totals?->printed_vat,
                'computed_vat'     => $document->totals?->computed_vat,
                'order_date'       => $orderDate,
                'has_warranty'     => true,
                'warranty_period'  => PurchaseOrder::WARRANTY_1_YEAR,
                'warranty_status'  => PurchaseOrder::WARRANTY_NONE,
                'delivery_status'  => PurchaseOrder::DELIVERY_PENDING,
                'status'           => PurchaseOrder::STATUS_PENDING,
            ]);
        } else {
            $po->update([
                'customer_name'    => $customerName,
                'order_amount'     => $orderAmount,
                'printed_vat'      => $document->totals?->printed_vat,
                'computed_vat'     => $document->totals?->computed_vat,
            ]);
        }

        // Sync line items
        $po->lineItems()->delete();
        foreach ($document->lineItems as $idx => $line) {
            $lineTotal = (float) ($line->printed_total ?: $line->computed_total);
            $baseCost = round((float) $line->unit_price * 0.7, 2);

            $po->lineItems()->create([
                'line_no'          => $line->line_no ?: ($idx + 1),
                'item_code'        => $line->material_code,
                'product_id'       => $line->product_id,
                'description'      => $line->description,
                'qty'              => $line->qty,
                'unit'             => $line->unit ?: 'pcs',
                'unit_price'       => $line->unit_price,
                'discounted_price' => $line->discounted_price,
                'base_cost'        => $baseCost,
                'line_total'       => $lineTotal,
                'line_cost'        => round((float) $line->qty * $baseCost, 2),
            ]);
        }
    }
}
