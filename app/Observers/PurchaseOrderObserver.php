<?php

namespace App\Observers;

use App\Models\PurchaseOrder;
use App\Services\InventoryService;
use App\Services\SalesQuotaService;
use App\Services\WarrantyService;
use Illuminate\Support\Facades\Log;

class PurchaseOrderObserver
{
    public function __construct(
        protected InventoryService $inventory,
        protected WarrantyService $warranty,
        protected SalesQuotaService $quota,
    ) {}

    /**
     * When a PO is first created, record toward sales quota.
     */
    public function created(PurchaseOrder $po): void
    {
        try {
            $this->quota->recordConversion($po);
        } catch (\Throwable $e) {
            Log::error("SalesQuotaService::recordConversion failed: " . $e->getMessage());
        }
    }

    /**
     * When delivery_status transitions to 'delivered':
     * — Deduct inventory components
     * — Activate warranty clock
     * — Check for overdue deliveries
     */
    public function updated(PurchaseOrder $po): void
    {
        // Inventory + Warranty: trigger on delivery confirmation
        if ($po->wasChanged('delivery_status') && $po->delivery_status === PurchaseOrder::DELIVERY_DELIVERED) {
            try {
                $this->inventory->deductComponents($po);
            } catch (\Throwable $e) {
                Log::error("InventoryService::deductComponents failed for PO {$po->po_number}: " . $e->getMessage());
            }

            try {
                $this->warranty->activateWarranty($po);
            } catch (\Throwable $e) {
                Log::error("WarrantyService::activateWarranty failed for PO {$po->po_number}: " . $e->getMessage());
            }

            // Also update PO status to delivered
            if ($po->status !== PurchaseOrder::STATUS_DELIVERED) {
                $po->updateQuietly(['status' => PurchaseOrder::STATUS_DELIVERED]);
            }
        }

        // Overdue check: flag if past expected delivery and still pending
        if ($po->wasChanged('expected_delivery_date') || $po->wasChanged('delivery_status')) {
            if (
                $po->delivery_status === PurchaseOrder::DELIVERY_PENDING
                && $po->expected_delivery_date
                && $po->expected_delivery_date->isPast()
            ) {
                $po->updateQuietly(['delivery_status' => PurchaseOrder::DELIVERY_OVERDUE]);

                // Notify
                $recipients = \App\Models\User::whereIn('role', [
                    \App\Models\User::ROLE_ADMIN,
                    \App\Models\User::ROLE_OPERATIONS_MANAGER,
                    \App\Models\User::ROLE_SALES_EXECUTIVE,
                ])->get();

                foreach ($recipients as $user) {
                    $user->notify(new \App\Notifications\DeliveryOverdueNotification($po));
                }
            }
        }
    }
}
