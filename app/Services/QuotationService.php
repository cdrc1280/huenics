<?php

namespace App\Services;

use App\Models\Document;
use App\Models\PurchaseOrder;
use App\Models\Quotation;
use App\Models\QuotationLineItem;
use App\Models\SalesQuota;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class QuotationService
{
    /**
     * Create a quotation manually or from a verified document.
     */
    public function create(array $data, User $agent): Quotation
    {
        return DB::transaction(function () use ($data, $agent) {
            $quotation = Quotation::create([
                'quotation_number' => Quotation::generateNumber(),
                'document_id'      => $data['document_id'] ?? null,
                'sales_agent_id'   => $agent->id,
                'customer_name'    => $data['customer_name'],
                'project_id'       => $data['project_id'] ?? null,
                'total_amount'     => 0,
                'total_cost'       => 0,
                'estimated_profit' => 0,
                'status'           => Quotation::STATUS_PENDING,
                'quotation_date'   => $data['quotation_date'] ?? now(),
                'valid_until'      => $data['valid_until'] ?? null,
                'notes'            => $data['notes'] ?? null,
            ]);

            $totalAmount = 0;
            $totalCost   = 0;

            foreach ($data['line_items'] ?? [] as $i => $item) {
                $lineTotal   = round((float) $item['qty'] * (float) $item['unit_price'], 2);
                $lineCost    = round((float) $item['qty'] * (float) ($item['base_cost'] ?? 0), 2);
                $grossProfit = round($lineTotal - $lineCost, 2);

                $quotation->lineItems()->create([
                    'line_no'      => $i + 1,
                    'product_id'   => $item['product_id'] ?? null,
                    'description'  => $item['description'],
                    'qty'          => $item['qty'],
                    'unit'         => $item['unit'] ?? 'pcs',
                    'unit_price'   => $item['unit_price'],
                    'base_cost'    => $item['base_cost'] ?? 0,
                    'line_total'   => $lineTotal,
                    'gross_profit' => $grossProfit,
                ]);

                $totalAmount += $lineTotal;
                $totalCost   += $lineCost;
            }

            $quotation->update([
                'total_amount'     => $totalAmount,
                'total_cost'       => $totalCost,
                'estimated_profit' => round($totalAmount - $totalCost, 2),
            ]);

            // Increment quota quotation count
            $this->incrementQuotationCount($agent);

            return $quotation;
        });
    }

    /**
     * Mark a quotation as reviewed by staff/management.
     */
    public function review(Quotation $quotation, ?User $reviewer = null): void
    {
        $reviewer = $reviewer ?: auth()->user();
        $quotation->update([
            'reviewed_by' => $reviewer?->id ?: 1,
            'reviewed_at' => now(),
        ]);
    }

    /**
     * Approve a quotation.
     */
    public function approve(Quotation $quotation, ?User $approver = null): void
    {
        if ($quotation->status !== Quotation::STATUS_PENDING) {
            throw new \RuntimeException("Only pending quotations can be approved.");
        }

        $approver = $approver ?: auth()->user();
        $quotation->update([
            'status'      => Quotation::STATUS_APPROVED,
            'approved_by' => $approver?->id ?: 1,
            'approved_at' => now(),
            'reviewed_by' => $quotation->reviewed_by ?: ($approver?->id ?: 1),
            'reviewed_at' => $quotation->reviewed_at ?: now(),
        ]);
    }

    /**
     * Reject a quotation with a reason.
     */
    public function reject(Quotation $quotation, string $reason): void
    {
        $quotation->update([
            'status'           => Quotation::STATUS_REJECTED,
            'rejection_reason' => $reason,
        ]);
    }

    /**
     * 1-click Quotation → Purchase Order conversion.
     * Validates 12% VAT, creates PO with inherited line items.
     * Enforces that the quotation must be approved (or serve as official signed PO).
     */
    public function convertToPO(Quotation $quotation, array $options = []): PurchaseOrder
    {
        if (!in_array($quotation->status, [Quotation::STATUS_APPROVED, Quotation::STATUS_PENDING]) && !$quotation->canServeAsOfficialPO()) {
            throw new \RuntimeException("Cannot convert a rejected or already converted quotation.");
        }

        // Must be approved (or serve as an Official PO with customer signature)
        if (!$quotation->isApproved() && !$quotation->canServeAsOfficialPO()) {
            throw new \RuntimeException("Quotation must be Approved before converting to PO.");
        }

        return DB::transaction(function () use ($quotation, $options) {
            $subtotal    = (float) $quotation->total_amount;
            $computedVat = round($subtotal * 0.12, 2);

            $po = PurchaseOrder::create([
                'po_number'               => PurchaseOrder::generateNumber(),
                'quotation_id'            => $quotation->id,
                'sales_agent_id'          => $quotation->sales_agent_id,
                'customer_name'           => $quotation->customer_name,
                'project_id'              => $quotation->project_id,
                'order_amount'            => $subtotal + $computedVat,
                'total_cost'              => (float) $quotation->total_cost,
                'realized_profit'         => round($subtotal - (float) $quotation->total_cost, 2),
                'computed_vat'            => $computedVat,
                'printed_vat'             => $options['printed_vat'] ?? null,
                'order_date'              => $options['order_date'] ?? now(),
                'expected_delivery_date'  => $options['expected_delivery_date'] ?? null,
                'has_warranty'            => $options['has_warranty'] ?? true,
                'warranty_period'         => $options['warranty_period'] ?? PurchaseOrder::WARRANTY_1_YEAR,
                'warranty_status'         => PurchaseOrder::WARRANTY_NONE,
                'delivery_status'         => PurchaseOrder::DELIVERY_PENDING,
                'status'                  => PurchaseOrder::STATUS_PENDING,
                'notes'                   => $options['notes'] ?? null,
            ]);

            // Inherit line items from quotation
            foreach ($quotation->lineItems as $item) {
                $po->lineItems()->create([
                    'line_no'          => $item->line_no,
                    'item_code'        => $item->item_code ?? null,
                    'product_id'       => $item->product_id,
                    'description'      => $item->description,
                    'qty'              => $item->qty,
                    'unit'             => $item->unit,
                    'unit_price'       => $item->unit_price,
                    'discounted_price' => $item->discounted_price ?? null,
                    'base_cost'        => $item->base_cost,
                    'line_total'       => $item->line_total,
                    'line_cost'        => round((float) $item->qty * (float) $item->base_cost, 2),
                ]);
            }

            // Mark quotation as converted
            $quotation->update(['status' => Quotation::STATUS_CONVERTED]);

            return $po;
        });
    }

    protected function incrementQuotationCount(User $agent): void
    {
        $quota = SalesQuota::firstOrCreate(
            ['user_id' => $agent->id, 'month' => now()->month, 'year' => now()->year],
            ['target_amount' => 0, 'achieved_amount' => 0]
        );
        $quota->increment('total_quotations');
    }
}
