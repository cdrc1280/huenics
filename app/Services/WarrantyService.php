<?php

namespace App\Services;

use App\Models\PurchaseOrder;
use Illuminate\Support\Collection;

class WarrantyService
{
    /**
     * Map warranty_period ENUM to month count.
     */
    public function periodToMonths(string $period): int
    {
        return PurchaseOrder::getWarrantyPeriodMonths($period);
    }

    /**
     * Activate warranty clock anchored to actual_delivery_date.
     * Computes warranty_end_date based on fixed period.
     * Clears warranty dates if not delivered or no warranty.
     */
    public function activateWarranty(PurchaseOrder $po): void
    {
        if (!$po->has_warranty || !$po->actual_delivery_date || $po->delivery_status !== PurchaseOrder::DELIVERY_DELIVERED) {
            $this->deactivateWarranty($po);
            return;
        }

        $months         = $this->periodToMonths($po->warranty_period);
        $startDate      = $po->actual_delivery_date;
        $endDate        = $startDate->copy()->addMonths($months);

        $po->updateQuietly([
            'warranty_start_date' => $startDate,
            'warranty_end_date'   => $endDate,
            'warranty_status'     => $this->resolveStatus($endDate),
        ]);
    }

    /**
     * Deactivate and clear warranty dates and status.
     */
    public function deactivateWarranty(PurchaseOrder $po): void
    {
        $po->updateQuietly([
            'warranty_status'     => PurchaseOrder::WARRANTY_NONE,
            'warranty_start_date' => null,
            'warranty_end_date'   => null,
        ]);
    }

    /**
     * Recompute and update warranty_status based on current date vs end date.
     */
    public function computeStatus(PurchaseOrder $po): string
    {
        if (!$po->has_warranty || !$po->warranty_end_date) {
            return PurchaseOrder::WARRANTY_NONE;
        }
        return $this->resolveStatus($po->warranty_end_date);
    }

    /**
     * Return POs whose warranty expires within $daysAhead days.
     */
    public function getExpiringSoon(int $daysAhead = 30): Collection
    {
        return PurchaseOrder::where('warranty_status', PurchaseOrder::WARRANTY_ACTIVE)
            ->where('has_warranty', true)
            ->whereNotNull('warranty_end_date')
            ->whereBetween('warranty_end_date', [now(), now()->addDays($daysAhead)])
            ->with(['salesAgent', 'project'])
            ->get();
    }

    /**
     * Refresh all active PO warranty statuses (called by scheduled command).
     */
    public function refreshAllStatuses(): int
    {
        $updated = 0;
        PurchaseOrder::where('has_warranty', true)
            ->whereNotNull('warranty_end_date')
            ->whereIn('warranty_status', [PurchaseOrder::WARRANTY_ACTIVE, PurchaseOrder::WARRANTY_EXPIRING])
            ->each(function (PurchaseOrder $po) use (&$updated) {
                $newStatus = $this->resolveStatus($po->warranty_end_date);
                if ($po->warranty_status !== $newStatus) {
                    $po->update(['warranty_status' => $newStatus]);
                    $updated++;

                    // Notify if now expiring_soon
                    if ($newStatus === PurchaseOrder::WARRANTY_EXPIRING) {
                        $this->triggerExpiringNotification($po);
                    }
                }
            });

        return $updated;
    }

    protected function resolveStatus(\Carbon\Carbon|\Illuminate\Support\Carbon $endDate): string
    {
        if ($endDate->isPast()) {
            return PurchaseOrder::WARRANTY_EXPIRED;
        }
        if ($endDate->lte(now()->addDays(30))) {
            return PurchaseOrder::WARRANTY_EXPIRING;
        }
        return PurchaseOrder::WARRANTY_ACTIVE;
    }

    protected function triggerExpiringNotification(PurchaseOrder $po): void
    {
        $recipients = \App\Models\User::whereIn('role', [
            \App\Models\User::ROLE_ADMIN,
            \App\Models\User::ROLE_SALES_EXECUTIVE,
            \App\Models\User::ROLE_CEO,
        ])->get();

        foreach ($recipients as $user) {
            $user->notify(new \App\Notifications\WarrantyExpiringNotification($po));
        }
    }
}
