<?php

namespace App\Actions;

use App\Models\AuditLog;
use App\Models\Document;
use App\Models\ProductAlias;
use App\Models\Project;
use App\Models\PurchaseOrder;
use App\Models\Quotation;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Support\Facades\DB;

class VerifyDocument
{
    public function __construct(
        protected ReconcileDocumentTotals $reconciler,
        protected CrossReferenceDocuments $crossReferencer
    ) {}

    /**
     * Commit document verification, create/update transactions, and record audit trail.
     *
     * @param Document $document
     * @param User $user
     * @param array<int, array> $editedLineItems
     * @param array $options
     * @return Transaction
     */
    public function execute(Document $document, User $user, array $editedLineItems = [], array $options = []): Transaction
    {
        return DB::transaction(function () use ($document, $user, $editedLineItems, $options) {
            $oldDocState = $document->toArray();

            // 1. Apply any line item adjustments made during review
            if (!empty($editedLineItems)) {
                foreach ($editedLineItems as $itemData) {
                    if (empty($itemData['id'])) {
                        continue;
                    }

                    $item = $document->lineItems()->find($itemData['id']);
                    if ($item) {
                        $oldItem = $item->toArray();
                        $item->update([
                            'description' => $itemData['description'] ?? $item->description,
                            'qty' => isset($itemData['qty']) ? (float) $itemData['qty'] : $item->qty,
                            'unit' => $itemData['unit'] ?? $item->unit,
                            'unit_price' => isset($itemData['unit_price']) ? (float) $itemData['unit_price'] : $item->unit_price,
                            'product_id' => $itemData['product_id'] ?? $item->product_id,
                            'printed_total' => isset($itemData['printed_total']) ? (float) $itemData['printed_total'] : $item->printed_total,
                        ]);

                        // Auto-learn product alias if user linked a canonical product
                        if (!empty($item->product_id) && !empty($item->description)) {
                            ProductAlias::firstOrCreate([
                                'product_id' => $item->product_id,
                                'normalized_alias' => ProductAlias::normalize($item->description),
                            ], [
                                'alias_text' => $item->description,
                                'vendor_id' => $document->vendor_id,
                            ]);
                        }

                        AuditLog::log('line_item_adjusted', $item, $oldItem, $item->fresh()->toArray(), $user);
                    }
                }
            }

            // 2. Re-run reconciliation
            $totals = $this->reconciler->execute($document);

            // 3. Mark document as verified
            $document->update([
                'status' => Document::STATUS_VERIFIED,
                'verified_by' => $user->id,
                'verified_at' => now(),
            ]);

            // 4. Find companion documents & transactions
            $crossRef = $this->crossReferencer->execute($document);
            $quotation = $crossRef['quotation'];
            $po = $crossRef['purchase_order'];
            $transaction = $crossRef['existing_transaction'];

            // Determine authoritative final amount
            // Priority: Quotation Negotiated Amount -> PO Total -> Document Total
            $finalAmount = 0.0;
            if ($quotation && $quotation->totals && $quotation->totals->negotiated_amount) {
                $finalAmount = (float) $quotation->totals->negotiated_amount;
            } elseif ($po && $po->totals) {
                $finalAmount = (float) ($po->totals->printed_total ?: $po->totals->computed_grand_total);
            } elseif ($totals) {
                $finalAmount = (float) ($totals->negotiated_amount ?: $totals->printed_total ?: $totals->computed_grand_total);
            }

            $orderDate = $document->document_date ?: now()->toDateString();

            // Safely resolve vendor_id
            $vendorId = null;
            if ($document->vendor_id && Vendor::where('id', $document->vendor_id)->exists()) {
                $vendorId = $document->vendor_id;
            } else {
                $vendorId = Vendor::value('id');
            }

            if (!$vendorId) {
                $defaultVendor = Vendor::firstOrCreate(
                    ['slug' => 'huenics-industrial'],
                    [
                        'name' => 'Huenics Industrial Supply Corp.',
                        'tin' => '009-876-543-000',
                        'is_active' => true,
                    ]
                );
                $vendorId = $defaultVendor->id;
            }

            if (!$document->vendor_id || $document->vendor_id !== $vendorId) {
                $document->update(['vendor_id' => $vendorId]);
            }

            // Safely resolve project_id
            $projectId = null;
            if ($document->project_id && Project::where('id', $document->project_id)->exists()) {
                $projectId = $document->project_id;
            } else {
                $projectId = Project::value('id');
            }

            if (!$projectId) {
                $defaultProject = Project::firstOrCreate(
                    ['code' => 'PRJ-GENERAL'],
                    [
                        'name' => 'General Construction Project',
                        'status' => 'active',
                    ]
                );
                $projectId = $defaultProject->id;
            }

            if (!$document->project_id || $document->project_id !== $projectId) {
                $document->update(['project_id' => $projectId]);
            }

            // 5. Sync or create Quotation / Purchase Order resources
            if ($document->document_type === Document::TYPE_VENDORS_AGREEMENT) {
                $this->syncQuotationFromDocument($document, $user, $totals, $finalAmount);
            }

            if ($document->document_type === Document::TYPE_PURCHASE_ORDER) {
                $this->syncPurchaseOrderFromDocument($document, $user, $totals, $finalAmount);
            }

            if ($transaction) {
                $oldTrx = $transaction->toArray();
                $transaction->update([
                    'quotation_document_id' => $quotation?->id ?: ($document->document_type === Document::TYPE_VENDORS_AGREEMENT ? $document->id : $transaction->quotation_document_id),
                    'purchase_order_document_id' => $po?->id ?: ($document->document_type === Document::TYPE_PURCHASE_ORDER ? $document->id : $transaction->purchase_order_document_id),
                    'final_amount' => $finalAmount ?: $transaction->final_amount,
                    'order_date' => $orderDate ?: $transaction->order_date,
                    'vendor_id' => $vendorId ?: $transaction->vendor_id,
                    'project_id' => $projectId ?: $transaction->project_id,
                ]);
                AuditLog::log('transaction_updated', $transaction, $oldTrx, $transaction->fresh()->toArray(), $user);
            } else {
                $transaction = Transaction::create([
                    'project_id' => $projectId,
                    'vendor_id' => $vendorId,
                    'quotation_document_id' => $quotation?->id ?: ($document->document_type === Document::TYPE_VENDORS_AGREEMENT ? $document->id : null),
                    'purchase_order_document_id' => $po?->id ?: ($document->document_type === Document::TYPE_PURCHASE_ORDER ? $document->id : null),
                    'final_amount' => $finalAmount,
                    'order_date' => $orderDate,
                    'status' => 'pending_delivery',
                    'created_by' => $user->id,
                ]);
                AuditLog::log('transaction_created', $transaction, null, $transaction->toArray(), $user);
            }

            AuditLog::log('document_verified', $document, $oldDocState, $document->fresh()->toArray(), $user);

            return $transaction;
        });
    }

    public function syncQuotationFromDocument(Document $document, User $user, $totals, float $finalAmount): Quotation
    {
        $quotation = Quotation::where('document_id', $document->id)->first();

        $customerName = $document->customer_name
            ?: ($document->project?->customer_name ?: ($document->vendor?->name ?: 'Engr. Ronald Rey Sandoval'));
        $customerCompany = $document->customer_company ?: 'MGS CONSTRUCTION, INC.';
        $projectName = $document->project_name ?: ($document->project?->name ?: 'Palanza Tower');
        $projectLocation = $document->project_location ?: 'Palanza St. corner Guirayan st., Dona Imelda, Q.C';
        $phoneNo = $document->phone_no ?: '0906-144-2553';

        $quotationDate = $document->document_date ?: now()->toDateString();
        $totalAmount = (float) ($totals?->printed_total ?: ($totals?->computed_grand_total ?: $finalAmount));
        $negotiatedAmount = $totals?->negotiated_amount ? (float) $totals->negotiated_amount : null;

        $isOfficialPo = isset($options['is_official_po']) ? (bool) $options['is_official_po'] : ($quotation?->is_official_po ?? false);
        $customerSigName = isset($options['customer_signature_name']) ? $options['customer_signature_name'] : ($quotation?->customer_signature_name ?? null);
        $customerSignedAt = !empty($options['customer_signed_at']) ? \Carbon\Carbon::parse($options['customer_signed_at']) : ($quotation?->customer_signed_at ?? null);

        if ($quotation) {
            $quotation->update([
                'quotation_number' => $document->document_number ?: $quotation->quotation_number,
                'customer_name' => $customerName,
                'customer_company' => $customerCompany,
                'project_id' => $document->project_id ?: $quotation->project_id,
                'project_name' => $projectName,
                'project_location' => $projectLocation,
                'phone_no' => $phoneNo,
                'total_amount' => $totalAmount,
                'negotiated_amount' => $negotiatedAmount,
                'estimated_profit' => round($totalAmount * 0.3, 2),
                'status' => Quotation::STATUS_APPROVED,
                'quotation_date' => $quotationDate,
                'terms_and_conditions' => $document->terms_and_conditions,
                'payment_terms' => $document->payment_terms,
                'delivery_terms' => $document->delivery_terms,
                'is_official_po' => $isOfficialPo,
                'customer_signature_name' => $customerSigName,
                'customer_signed_at' => $customerSignedAt,
            ]);
        } else {
            $quotationNumber = $document->document_number;
            if (!$quotationNumber || Quotation::where('quotation_number', $quotationNumber)->exists()) {
                $quotationNumber = Quotation::generateNumber();
            }

            $quotation = Quotation::create([
                'quotation_number' => $quotationNumber,
                'document_id' => $document->id,
                'sales_agent_id' => $document->uploaded_by ?: $user->id,
                'customer_name' => $customerName,
                'customer_company' => $customerCompany,
                'project_id' => $document->project_id,
                'project_name' => $projectName,
                'project_location' => $projectLocation,
                'phone_no' => $phoneNo,
                'total_amount' => $totalAmount,
                'negotiated_amount' => $negotiatedAmount,
                'total_cost' => round($totalAmount * 0.7, 2),
                'estimated_profit' => round($totalAmount * 0.3, 2),
                'status' => Quotation::STATUS_APPROVED,
                'quotation_date' => $quotationDate,
                'terms_and_conditions' => $document->terms_and_conditions,
                'payment_terms' => $document->payment_terms,
                'delivery_terms' => $document->delivery_terms,
                'is_official_po' => $isOfficialPo,
                'customer_signature_name' => $customerSigName,
                'customer_signed_at' => $customerSignedAt,
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
                'line_no' => $line->line_no ?: ($idx + 1),
                'item_code' => $line->material_code,
                'product_id' => $line->product_id,
                'description' => $line->description,
                'qty' => $line->qty,
                'unit' => $line->unit ?: 'pcs',
                'unit_price' => $line->unit_price,
                'discounted_price' => $line->discounted_price,
                'base_cost' => $baseCost,
                'line_total' => $lineTotal,
                'gross_profit' => $grossProfit,
            ]);
        }

        return $quotation;
    }

    public function syncPurchaseOrderFromDocument(Document $document, User $user, $totals, float $finalAmount): PurchaseOrder
    {
        $po = PurchaseOrder::where('document_id', $document->id)->first();

        $customerName = $document->project?->customer_name
            ?: ($document->vendor?->name ?: 'MGS CONSTRUCTION, INC.');

        $orderDate = $document->document_date ?: now()->toDateString();
        $orderAmount = (float) ($totals?->printed_total ?: ($totals?->computed_grand_total ?: $finalAmount));

        if ($po) {
            $po->update([
                'po_number' => $document->document_number ?: $po->po_number,
                'customer_name' => $customerName,
                'project_id' => $document->project_id ?: $po->project_id,
                'order_amount' => $orderAmount,
                'printed_vat' => $totals?->printed_vat,
                'computed_vat' => $totals?->computed_vat,
                'order_date' => $orderDate,
                'status' => PurchaseOrder::STATUS_APPROVED,
                'terms_and_conditions' => $document->terms_and_conditions,
                'payment_terms' => $document->payment_terms,
                'delivery_terms' => $document->delivery_terms,
            ]);
        } else {
            $poNumber = $document->document_number;
            if (!$poNumber || PurchaseOrder::where('po_number', $poNumber)->exists()) {
                $poNumber = PurchaseOrder::generateNumber();
            }

            $po = PurchaseOrder::create([
                'po_number' => $poNumber,
                'document_id' => $document->id,
                'sales_agent_id' => $document->uploaded_by ?: $user->id,
                'customer_name' => $customerName,
                'project_id' => $document->project_id,
                'order_amount' => $orderAmount,
                'total_cost' => round($orderAmount * 0.7, 2),
                'realized_profit' => round($orderAmount * 0.3, 2),
                'printed_vat' => $totals?->printed_vat,
                'computed_vat' => $totals?->computed_vat,
                'order_date' => $orderDate,
                'has_warranty' => true,
                'warranty_period' => PurchaseOrder::WARRANTY_1_YEAR,
                'warranty_status' => PurchaseOrder::WARRANTY_NONE,
                'delivery_status' => PurchaseOrder::DELIVERY_PENDING,
                'status' => PurchaseOrder::STATUS_APPROVED,
                'terms_and_conditions' => $document->terms_and_conditions,
                'payment_terms' => $document->payment_terms,
                'delivery_terms' => $document->delivery_terms,
            ]);
        }

        // Sync line items
        $po->lineItems()->delete();
        foreach ($document->lineItems as $idx => $line) {
            $lineTotal = (float) ($line->printed_total ?: $line->computed_total);
            $baseCost = round((float) $line->unit_price * 0.7, 2);

            $po->lineItems()->create([
                'line_no' => $line->line_no ?: ($idx + 1),
                'product_id' => $line->product_id,
                'description' => $line->description,
                'qty' => $line->qty,
                'unit' => $line->unit ?: 'pcs',
                'unit_price' => $line->unit_price,
                'base_cost' => $baseCost,
                'line_total' => $lineTotal,
                'line_cost' => round($line->qty * $baseCost, 2),
            ]);
        }

        return $po;
    }
}

