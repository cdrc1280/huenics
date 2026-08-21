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
     * Handle state transitions and synchronization before saving.
     */
    public function saving(PurchaseOrder $po): void
    {
        // 1. If actual_delivery_date is cleared/empty
        if (empty($po->actual_delivery_date)) {
            if ($po->delivery_status === PurchaseOrder::DELIVERY_DELIVERED) {
                $po->delivery_status = ($po->expected_delivery_date && $po->expected_delivery_date->isPast())
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
     * When delivery_status transitions:
     * — Deduct inventory components on delivered
     * — Activate / deactivate warranty clock
     * — Check for overdue deliveries
     */
    public function updated(PurchaseOrder $po): void
    {
        // Inventory + Warranty: trigger on delivery confirmation
        if ($po->wasChanged('delivery_status')) {
            if ($po->delivery_status === PurchaseOrder::DELIVERY_DELIVERED) {
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
