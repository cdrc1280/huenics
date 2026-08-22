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
     * When a PO is first created, record toward sales quota and auto-deduct stock.
     */
    public function created(PurchaseOrder $po): void
    {
        try {
            $this->quota->recordConversion($po);
        } catch (\Throwable $e) {
            Log::error("SalesQuotaService::recordConversion failed: " . $e->getMessage());
        }

        try {
            if ($po->status !== PurchaseOrder::STATUS_CANCELLED && $po->status !== PurchaseOrder::STATUS_REJECTED) {
                $this->inventory->deductPurchaseOrderStock($po);
            }
        } catch (\Throwable $e) {
            Log::error("InventoryService::deductPurchaseOrderStock failed on created: " . $e->getMessage());
        }
    }

    /**
     * Handle state transitions and synchronization before saving.
     */
    public function saving(PurchaseOrder $po): void
    {
        // 1. If actual_delivery_date is cleared/empty
        if (empty($po->actual_delivery_date)) {
            if ($po->delivery_status === PurchaseOrder::DELIVERY_DELIVERED) {
                $po->delivery_status = ($po->expected_delivery_date && \Carbon\Carbon::parse($po->expected_delivery_date)->isPast())
                    ? PurchaseOrder::DELIVERY_OVERDUE
                    : PurchaseOrder::DELIVERY_PENDING;
            }
            if ($po->status === PurchaseOrder::STATUS_DELIVERED) {
                $po->status = PurchaseOrder::STATUS_PENDING;
            }
            $po->warranty_status = PurchaseOrder::WARRANTY_NONE;
            $po->warranty_start_date = null;
            $po->warranty_end_date = null;
        }

        // 2. If delivery_status is not 'delivered'
        if ($po->delivery_status !== PurchaseOrder::DELIVERY_DELIVERED) {
            if ($po->status === PurchaseOrder::STATUS_DELIVERED) {
                $po->status = PurchaseOrder::STATUS_PENDING;
            }
            $po->warranty_status = PurchaseOrder::WARRANTY_NONE;
            $po->warranty_start_date = null;
            $po->warranty_end_date = null;
        }

        // 3. If has_warranty is false
        if (!$po->has_warranty) {
            $po->warranty_status = PurchaseOrder::WARRANTY_NONE;
            $po->warranty_start_date = null;
            $po->warranty_end_date = null;
        }

        // 4. If delivered and has actual_delivery_date
        if (!empty($po->actual_delivery_date) && $po->delivery_status === PurchaseOrder::DELIVERY_DELIVERED) {
            $po->status = PurchaseOrder::STATUS_DELIVERED;
            if ($po->has_warranty) {
                $months = PurchaseOrder::getWarrantyPeriodMonths($po->warranty_period ?? PurchaseOrder::WARRANTY_1_YEAR);
                $startDate = \Carbon\Carbon::parse($po->actual_delivery_date);
                $endDate = $startDate->copy()->addMonths($months);
                $po->warranty_start_date = $startDate;
                $po->warranty_end_date = $endDate;
                if ($endDate->isPast()) {
                    $po->warranty_status = PurchaseOrder::WARRANTY_EXPIRED;
                } elseif ($endDate->lte(now()->addDays(30))) {
                    $po->warranty_status = PurchaseOrder::WARRANTY_EXPIRING;
                } else {
                    $po->warranty_status = PurchaseOrder::WARRANTY_ACTIVE;
                }
            }
        }
    }

    /**
     * When delivery_status or status transitions:
     * — Deduct inventory components on delivered / confirmed
     * — Restore inventory on cancelled / rejected
     * — Activate / deactivate warranty clock
     * — Check for overdue deliveries
     */
    public function updated(PurchaseOrder $po): void
    {
        // 1. If PO was cancelled or rejected, restore stock
        if ($po->wasChanged('status')) {
            if (in_array($po->status, [PurchaseOrder::STATUS_CANCELLED, PurchaseOrder::STATUS_REJECTED])) {
                try {
                    $this->inventory->restorePurchaseOrderStock($po);
                } catch (\Throwable $e) {
                    Log::error("Inventory restore failed for PO {$po->po_number}: " . $e->getMessage());
                }
            } elseif ($po->getOriginal('status') === PurchaseOrder::STATUS_CANCELLED || $po->getOriginal('status') === PurchaseOrder::STATUS_REJECTED) {
                // Restored from cancelled -> deduct
                try {
                    $this->inventory->deductPurchaseOrderStock($po);
                } catch (\Throwable $e) {
                    Log::error("Inventory deduction failed for restored PO {$po->po_number}: " . $e->getMessage());
                }
            }
        }

        // 2. Inventory + Warranty: trigger on delivery confirmation
        if ($po->wasChanged('delivery_status')) {
            if ($po->delivery_status === PurchaseOrder::DELIVERY_DELIVERED) {
                try {
                    $this->inventory->deductPurchaseOrderStock($po);
                } catch (\Throwable $e) {
                    Log::error("InventoryService::deductPurchaseOrderStock failed for PO {$po->po_number}: " . $e->getMessage());
                }

                try {
                    $this->warranty->activateWarranty($po);
                } catch (\Throwable $e) {
                    Log::error("WarrantyService::activateWarranty failed for PO {$po->po_number}: " . $e->getMessage());
                }

                if ($po->status !== PurchaseOrder::STATUS_DELIVERED) {
                    $po->updateQuietly(['status' => PurchaseOrder::STATUS_DELIVERED]);
                }
            } else {
                // Reverted away from delivered
                try {
                    $this->warranty->deactivateWarranty($po);
                } catch (\Throwable $e) {
                    Log::error("WarrantyService::deactivateWarranty failed for PO {$po->po_number}: " . $e->getMessage());
                }

                if ($po->status === PurchaseOrder::STATUS_DELIVERED) {
                    $po->updateQuietly(['status' => PurchaseOrder::STATUS_PENDING]);
                }
            }
        }

        // 3. Overdue check: flag if past expected delivery and still pending
        if ($po->wasChanged('expected_delivery_date') || $po->wasChanged('delivery_status')) {
            if (
                $po->delivery_status === PurchaseOrder::DELIVERY_PENDING
                && $po->expected_delivery_date
                && \Carbon\Carbon::parse($po->expected_delivery_date)->isPast()
            ) {
                $po->updateQuietly(['delivery_status' => PurchaseOrder::DELIVERY_OVERDUE]);

                // Notify
                $recipients = \App\Models\User::whereIn('role', [
                    \App\Models\User::ROLE_ADMIN,
                    \App\Models\User::ROLE_OPERATIONS_MANAGER,
                    \App\Models\User::ROLE_SALES_EXECUTIVE,
                    \App\Models\User::ROLE_CEO,
                ])->get();

                foreach ($recipients as $user) {
                    $user->notify(new \App\Notifications\DeliveryOverdueNotification($po));
                }
            }
        }
    }

    /**
     * When a PO is deleted, restore deducted stock.
     */
    public function deleted(PurchaseOrder $po): void
    {
        try {
            $this->inventory->restorePurchaseOrderStock($po);
        } catch (\Throwable $e) {
            Log::error("InventoryService::restorePurchaseOrderStock failed on deleted: " . $e->getMessage());
        }
    }
}
