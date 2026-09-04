<?php

namespace App\Services;

use App\Enums\DocumentType;
use App\Models\AuditLog;
use App\Models\DeliveryReceipt;
use App\Models\DeliveryReceiptItem;
use App\Models\Document;
use App\Models\PurchaseOrder;
use App\Models\SalesInvoice;
use App\Models\SalesInvoiceItem;
use App\Models\Transaction;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderFulfillmentService
{
    public function __construct(
        protected InventoryService $inventoryService,
        protected WarrantyService $warrantyService
    ) {}

    /**
     * Step 1: Ingest and attach DR and SI physical documents and records to a Purchase Order.
     * Leaves the PO ready for the user to "Mark as Delivered".
     */
    public function attachFulfillmentDocuments(PurchaseOrder $po, array $data, ?User $actor = null): array
    {
        $actor = $actor ?? auth()->user() ?? $po->salesAgent ?? User::where('role', User::ROLE_ADMIN)->first();
        $actorId = $actor?->id ?? 1;

        return DB::transaction(function () use ($po, $data, $actor, $actorId) {
            $deliveryDate = $data['delivery_date'] ?? now()->toDateString();
            $invoiceDate = $data['invoice_date'] ?? $deliveryDate;

            // ─── 1. Ingest & Create Delivery Receipt Document ─────────────
            $drDoc = null;
            if (!empty($data['dr_file'])) {
                $drDoc = $this->createDocumentFromFile(
                    diskPath: $data['dr_file'],
                    documentType: DocumentType::DeliveryReceipt->value,
                    projectId: $po->project_id,
                    userId: $actorId,
                    documentNumber: $data['dr_number'] ?? null,
                    documentDate: $deliveryDate
                );
            }

            // ─── 2. Create or Update DeliveryReceipt Model ────────────────
            $drNumber = $data['dr_number'] ?? ($drDoc?->document_number ?: DeliveryReceipt::generateNumber());
            $deliveryReceipt = DeliveryReceipt::create([
                'dr_number'             => $drNumber,
                'purchase_order_id'     => $po->id,
                'document_id'           => $drDoc?->id,
                'customer_name'         => $data['customer_name'] ?? $po->customer_name,
                'customer_tin'          => $data['customer_tin'] ?? null,
                'delivery_address'      => $data['delivery_address'] ?? ($po->project?->location ?? null),
                'terms'                 => $data['terms'] ?? ($po->delivery_terms ?? null),
                'project_name'          => $data['project_name'] ?? ($po->project?->name ?? null),
                'sales_invoice_numbers' => $data['sales_invoice_numbers'] ?? null,
                'delivered_by'          => $data['delivered_by'] ?? null,
                'received_by'           => $data['received_by'] ?? null,
                'delivery_date'         => $deliveryDate,
                'remarks'               => $data['dr_remarks'] ?? ($data['remarks'] ?? "Fulfilled for PO {$po->po_number}"),
                'status'                => DeliveryReceipt::STATUS_DELIVERED,
                'file_path'             => $data['dr_file'] ?? null,
            ]);

            // Populate DR Items from PO Line Items
            foreach ($po->lineItems()->with('product')->get() as $line) {
                DeliveryReceiptItem::create([
                    'delivery_receipt_id' => $deliveryReceipt->id,
                    'po_line_item_id'     => $line->id,
                    'product_id'          => $line->product_id,
                    'description'         => $line->description ?: ($line->product?->canonical_name ?? 'Line Item'),
                    'qty_delivered'       => (float) $line->qty,
                    'unit'                => $line->unit ?: 'pcs',
                    'remarks'             => $line->description,
                ]);
            }

            // ─── 3. Ingest & Create Sales Invoice Document ────────────────
            $siDoc = null;
            if (!empty($data['si_file'])) {
                $siDoc = $this->createDocumentFromFile(
                    diskPath: $data['si_file'],
                    documentType: DocumentType::SalesInvoice->value,
                    projectId: $po->project_id,
                    userId: $actorId,
                    documentNumber: $data['si_number'] ?? null,
                    documentDate: $invoiceDate
                );
            }

            // ─── 4. Create or Update SalesInvoice Model ───────────────────
            $siNumber = $data['si_number'] ?? ($siDoc?->document_number ?: SalesInvoice::generateNumber());
            $subtotal = isset($data['subtotal']) ? (float) $data['subtotal'] : (float) ($po->order_amount / 1.12);
            $discountAmount = isset($data['discount_amount']) ? (float) $data['discount_amount'] : 0.0;
            $netOfVat = isset($data['net_of_vat']) ? (float) $data['net_of_vat'] : (float) $subtotal;
            $vatAmount = isset($data['vat_amount']) ? (float) $data['vat_amount'] : (float) ($po->order_amount - $subtotal);
            $totalAmount = isset($data['total_amount']) ? (float) $data['total_amount'] : (float) $po->order_amount;

            $paymentStatus = $data['payment_status'] ?? SalesInvoice::STATUS_PAID;
            $salesInvoice = SalesInvoice::create([
                'si_number'                  => $siNumber,
                'purchase_order_id'          => $po->id,
                'delivery_receipt_id'        => $deliveryReceipt->id,
                'delivery_receipt_numbers'   => $drNumber,
                'document_id'                => $siDoc?->id,
                'customer_name'              => $data['customer_name'] ?? $po->customer_name,
                'customer_tin'               => $data['customer_tin'] ?? null,
                'business_style'             => $data['business_style'] ?? ($data['customer_name'] ?? $po->customer_name),
                'billing_address'            => $data['billing_address'] ?? ($po->project?->location ?? null),
                'terms'                      => $data['terms'] ?? ($po->payment_terms ?? null),
                'invoice_date'               => $invoiceDate,
                'due_date'                   => $data['due_date'] ?? null,
                'subtotal'                   => round($subtotal, 2),
                'discount_amount'            => round($discountAmount, 2),
                'net_of_vat'                 => round($netOfVat, 2),
                'vatable_sales'              => round($netOfVat, 2),
                'vat_amount'                 => round($vatAmount, 2),
                'total_amount'               => round($totalAmount, 2),
                'payment_status'             => $paymentStatus,
                'payment_date'               => $data['payment_date'] ?? ($paymentStatus === SalesInvoice::STATUS_PAID ? $invoiceDate : null),
                'notes'                      => $data['notes'] ?? "Sales invoice for PO {$po->po_number} and DR {$drNumber}",
                'file_path'                  => $data['si_file'] ?? null,
            ]);

            // Also update DR's cross-ref SI numbers
            $deliveryReceipt->update(['sales_invoice_numbers' => $siNumber]);

            // Populate SI Items from PO Line Items
            foreach ($po->lineItems()->with('product')->get() as $line) {
                $itemPrice = (float) ($line->discounted_price ?: $line->unit_price);
                $itemTotal = (float) ($line->line_total ?: round((float) $line->qty * $itemPrice, 2));

                SalesInvoiceItem::create([
                    'sales_invoice_id' => $salesInvoice->id,
                    'po_line_item_id'  => $line->id,
                    'product_id'       => $line->product_id,
                    'description'      => $line->description ?: ($line->product?->canonical_name ?? 'Line Item'),
                    'qty'              => (float) $line->qty,
                    'unit'             => $line->unit ?: 'pcs',
                    'unit_price'       => $itemPrice,
                    'line_total'       => $itemTotal,
                ]);
            }

            // ─── 5. Sync Master Transaction Document Linkages ────────────
            $transaction = Transaction::where('purchase_order_id', $po->id)
                ->orWhere(function ($q) use ($po) {
                    if ($po->document_id) {
                        $q->where('purchase_order_document_id', $po->document_id);
                    }
                    if ($po->quotation?->document_id) {
                        $q->orWhere('quotation_document_id', $po->quotation->document_id);
                    }
                })
                ->first();

            if ($transaction) {
                $transaction->update([
                    'purchase_order_id'            => $po->id,
                    'delivery_receipt_document_id' => $drDoc?->id ?: $transaction->delivery_receipt_document_id,
                    'sales_invoice_document_id'    => $siDoc?->id ?: $transaction->sales_invoice_document_id,
                    'final_amount'                 => $totalAmount ?: $transaction->final_amount,
                    'delivery_date'                => $deliveryDate,
                ]);
            } else {
                $transaction = Transaction::create([
                    'purchase_order_id'            => $po->id,
                    'project_id'                   => $po->project_id,
                    'quotation_document_id'        => $po->quotation?->document_id,
                    'purchase_order_document_id'   => $po->document_id,
                    'delivery_receipt_document_id' => $drDoc?->id,
                    'sales_invoice_document_id'    => $siDoc?->id,
                    'final_amount'                 => $totalAmount,
                    'order_date'                   => $po->order_date ?: now()->toDateString(),
                    'delivery_date'                => $deliveryDate,
                    'status'                       => Transaction::STATUS_PENDING,
                    'is_completed'                 => false,
                    'created_by'                   => $actorId,
                ]);
            }

            // Update PO delivery receipt no and sales invoice no (aggregate all attached numbers)
            $allDrNumbers = $po->deliveryReceipts()->pluck('dr_number')->filter()->unique()->implode(', ');
            $allSiNumbers = $po->salesInvoices()->pluck('si_number')->filter()->unique()->implode(', ');

            $po->update([
                'delivery_receipt_no'  => $allDrNumbers ?: $drNumber,
                'sales_invoice_no'     => $allSiNumbers ?: $siNumber,
                'actual_delivery_date' => $deliveryDate,
            ]);

            // Log Audit History for DR & SI Attachment
            try {
                AuditLog::create([
                    'user_id'        => $actorId,
                    'action'         => 'documents_attached',
                    'event'          => AuditLog::EVENT_DOCUMENTS_ATTACHED,
                    'auditable_type' => PurchaseOrder::class,
                    'auditable_id'   => $po->id,
                    'description'    => "Attached Delivery Receipt #{$drNumber} and Sales Invoice #{$siNumber} to Purchase Order {$po->po_number}.",
                    'properties'     => [
                        'po_number'      => $po->po_number,
                        'dr_number'      => $drNumber,
                        'si_number'      => $siNumber,
                        'dr_document_id' => $drDoc?->id,
                        'si_document_id' => $siDoc?->id,
                    ],
                    'ip_address'     => request()->ip() ?? '127.0.0.1',
                    'user_agent'     => request()->userAgent() ?? 'System',
                    'created_at'     => now(),
                ]);
            } catch (\Throwable $e) {
                Log::warning("AuditLog creation failed in attachFulfillmentDocuments: " . $e->getMessage());
            }

            return [
                'delivery_receipt' => $deliveryReceipt,
                'sales_invoice'    => $salesInvoice,
                'transaction'      => $transaction,
            ];
        });
    }

    /**
     * Step 2: Mark as Delivered (Only executable if DR and SI are attached & verified).
     * Deducts stock and realizes sales across the system.
     */
    public function completeDelivery(PurchaseOrder $po, array $options = [], ?User $actor = null): PurchaseOrder
    {
        if (!$po->hasBothDrAndSi() && !$po->is_completed) {
            throw new Exception("Cannot mark as delivered: Delivery Receipt (DR) and Sales Invoice (SI) hard copies must be uploaded and attached first.");
        }

        $actor = $actor ?? auth()->user() ?? $po->salesAgent ?? User::where('role', User::ROLE_ADMIN)->first();
        $actorId = $actor?->id ?? 1;

        return DB::transaction(function () use ($po, $options, $actor, $actorId) {
            $deliveryDate = $options['actual_delivery_date'] ?? ($po->actual_delivery_date ?: now()->toDateString());
            $drNumber = $options['delivery_receipt_no'] ?? ($po->delivery_receipt_no ?: $po->deliveryReceipts()->first()?->dr_number);

            // 1. Mark PO completed & delivered
            $po->update([
                'is_completed'         => true,
                'completed_at'         => now(),
                'delivery_status'      => PurchaseOrder::DELIVERY_DELIVERED,
                'status'               => PurchaseOrder::STATUS_DELIVERED,
                'actual_delivery_date' => $deliveryDate,
                'delivery_receipt_no'  => $drNumber,
                'has_warranty'         => $options['has_warranty'] ?? ($po->has_warranty ?? true),
                'warranty_period'      => $options['warranty_period'] ?? ($po->warranty_period ?? PurchaseOrder::WARRANTY_1_YEAR),
            ]);

            // 2. Mark master transaction completed
            $transaction = Transaction::where('purchase_order_id', $po->id)->first();
            if ($transaction) {
                $transaction->update([
                    'status'       => Transaction::STATUS_DELIVERED,
                    'is_completed' => true,
                    'delivery_date'=> $deliveryDate,
                ]);
            }

            // 3. Deduct inventory stock from catalog & BOM
            $this->inventoryService->deductPurchaseOrderStock($po);

            // 4. Activate Warranty Clock
            try {
                $this->warrantyService->activateWarranty($po);
            } catch (\Throwable $e) {
                Log::warning("Warranty activation warning during completeDelivery: " . $e->getMessage());
            }

            // Invalidate caches
            Cache::forget('widget_inventory_alerts_stats');

            // 5. Log Audit History
            try {
                AuditLog::create([
                    'user_id'         => $actorId,
                    'action'          => 'order_marked_delivered',
                    'event'           => 'delivered',
                    'auditable_type'  => PurchaseOrder::class,
                    'auditable_id'    => $po->id,
                    'description'     => "Purchase Order {$po->po_number} marked as Delivered with verified DR and SI documents attached. Stock deducted and sales realized.",
                    'properties'      => [
                        'po_number'           => $po->po_number,
                        'order_amount'        => $po->order_amount,
                        'delivery_receipt_no' => $drNumber,
                        'actual_delivery_date'=> $deliveryDate,
                        'is_completed'        => true,
                    ],
                    'ip_address'      => request()->ip() ?? '127.0.0.1',
                    'user_agent'      => request()->userAgent() ?? 'System',
                    'created_at'      => now(),
                ]);
            } catch (\Throwable $e) {
                Log::warning("AuditLog creation failed in completeDelivery: " . $e->getMessage());
            }

            return $po;
        });
    }

    /**
     * Full 1-step fulfillment: Attaches DR & SI and immediately marks delivered.
     */
    public function fulfillOrder(PurchaseOrder $po, array $data, ?User $actor = null): array
    {
        $attached = $this->attachFulfillmentDocuments($po, $data, $actor);
        $this->completeDelivery($po, [
            'actual_delivery_date' => $data['delivery_date'] ?? now()->toDateString(),
            'delivery_receipt_no'  => $attached['delivery_receipt']->dr_number,
            'has_warranty'         => $data['has_warranty'] ?? ($po->has_warranty ?? true),
            'warranty_period'      => $data['warranty_period'] ?? ($po->warranty_period ?? PurchaseOrder::WARRANTY_1_YEAR),
        ], $actor);

        return $attached;
    }

    /**
     * Ingest physical file from storage disk and register verified Document record.
     */
    protected function createDocumentFromFile(
        string $diskPath,
        string $documentType,
        ?int $projectId,
        int $userId,
        ?string $documentNumber = null,
        ?string $documentDate = null
    ): Document {
        $storage = \Illuminate\Support\Facades\Storage::disk('local');
        $fullPath = $storage->path($diskPath);

        $filename = basename($diskPath);
        $ext = strtolower(pathinfo($diskPath, PATHINFO_EXTENSION));
        $fileSize = file_exists($fullPath) ? filesize($fullPath) : 0;
        $hash = file_exists($fullPath) ? hash_file('sha256', $fullPath) : null;

        $mimeType = match ($ext) {
            'pdf' => 'application/pdf',
            'png' => 'image/png',
            'jpg', 'jpeg' => 'image/jpeg',
            'webp' => 'image/webp',
            default => 'application/octet-stream',
        };

        return Document::create([
            'project_id'         => $projectId,
            'uploaded_by'        => $userId,
            'verified_by'        => $userId,
            'verified_at'        => now(),
            'disk_path'          => $diskPath,
            'original_filename'  => $filename,
            'original_mime_type' => $mimeType,
            'file_size'          => $fileSize,
            'file_hash'          => $hash,
            'document_type'      => $documentType,
            'document_number'    => $documentNumber,
            'document_date'      => $documentDate ?? now()->toDateString(),
            'status'             => Document::STATUS_VERIFIED,
            'parsed_data'        => [
                'attached_via' => 'order_fulfillment_workflow',
                'format'       => $ext,
            ],
        ]);
    }
}
