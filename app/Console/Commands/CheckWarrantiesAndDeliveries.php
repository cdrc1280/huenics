<?php

namespace App\Console\Commands;

use App\Models\PurchaseOrder;
use App\Models\User;
use App\Notifications\DeliveryOverdueNotification;
use App\Services\WarrantyService;
use Illuminate\Console\Command;

class CheckWarrantiesAndDeliveries extends Command
{
    protected $signature   = 'huenics:check-warranties-deliveries';
    protected $description = 'Daily check: refresh warranty statuses and flag overdue deliveries with notifications.';

    public function handle(WarrantyService $warranty): int
    {
        $this->info('Checking warranty statuses...');
        $updated = $warranty->refreshAllStatuses();
        $this->info("✓ {$updated} warranty status(es) updated.");

        $this->info('Checking overdue deliveries...');
        $overduePos = PurchaseOrder::where('delivery_status', PurchaseOrder::DELIVERY_PENDING)
            ->whereNotNull('expected_delivery_date')
            ->where('expected_delivery_date', '<', now())
            ->get();

        $notified = 0;
        foreach ($overduePos as $po) {
            $po->update(['delivery_status' => PurchaseOrder::DELIVERY_OVERDUE]);

            $recipients = User::whereIn('role', [
                User::ROLE_ADMIN,
                User::ROLE_OPERATIONS_MANAGER,
                User::ROLE_SALES_EXECUTIVE,
            ])->get();

            foreach ($recipients as $user) {
                $user->notify(new DeliveryOverdueNotification($po));
            }
            $notified++;
        }

        $this->info("✓ {$notified} overdue PO(s) flagged and notified.");
        $this->info('Done.');

        return Command::SUCCESS;
    }
}
