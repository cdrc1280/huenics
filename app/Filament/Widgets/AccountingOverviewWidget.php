<?php

namespace App\Filament\Widgets;

use App\Models\PurchaseOrder;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Cache;

class AccountingOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getColumns(): int | array | null
    {
        return [
            'default' => 1,
            'sm'      => 2,
            'md'      => 4,
            'lg'      => 4,
            'xl'      => 4,
        ];
    }

    protected function getStats(): array
    {
        $user = auth()->user();
        $userId = $user?->id ?? 0;
        $isSalesExec = $user && $user->isSalesExecutive() && !$user->isAdmin() && !$user->is_owner && !$user->isOperationsManager() && !$user->isCeo();

        $cacheKey = 'widget_accounting_overview_' . ($isSalesExec ? "agent_{$userId}" : 'all') . '_' . date('Y-m-d_H-i');

        $stats = Cache::remember($cacheKey, 60, function () use ($user, $isSalesExec) {
            $query = PurchaseOrder::whereNotIn('status', [PurchaseOrder::STATUS_CANCELLED, PurchaseOrder::STATUS_REJECTED]);

            if ($isSalesExec) {
                $query->where('sales_agent_id', $user->id);
            }

            $allOrders = $query->get();

            $totalReceivables = (float) $allOrders->where('payment_status', '!=', PurchaseOrder::PAYMENT_STATUS_PAID)->sum('order_amount');
            $totalCollected = (float) $allOrders->where('payment_status', PurchaseOrder::PAYMENT_STATUS_PAID)->sum('order_amount');
            $pendingCount = $allOrders->where('payment_status', '!=', PurchaseOrder::PAYMENT_STATUS_PAID)->count();
            $totalPendingDelivery = $allOrders->filter(fn($po) => !$po->isDelivered())->count();
            $paidCount = $allOrders->where('payment_status', PurchaseOrder::PAYMENT_STATUS_PAID)->count();

            $overdueCount = 0;
            $overdueAmount = 0.0;
            $warningCount = 0;
            $warningAmount = 0.0;

            $nowDate = Carbon::now()->startOfDay();
            $warningWindow = Carbon::now()->addDays(10)->endOfDay();

            foreach ($allOrders as $po) {
                if ($po->isPaid()) {
                    continue;
                }

                if ($po->payment_due_date) {
                    $dueDate = Carbon::parse($po->payment_due_date)->startOfDay();
                    $amount = (float) $po->order_amount;

                    if ($dueDate->lt($nowDate)) {
                        $overdueCount++;
                        $overdueAmount += $amount;
                    } elseif ($dueDate->lte($warningWindow)) {
                        $warningCount++;
                        $warningAmount += $amount;
                    }
                }
            }

            return [
                'totalReceivables'     => $totalReceivables,
                'totalCollected'       => $totalCollected,
                'pendingCount'         => $pendingCount,
                'totalPendingDelivery' => $totalPendingDelivery,
                'paidCount'            => $paidCount,
                'overdueCount'         => $overdueCount,
                'overdueAmount'        => $overdueAmount,
                'warningCount'         => $warningCount,
                'warningAmount'        => $warningAmount,
            ];
        });

        return [
            Stat::make('Total Receivables', '₱' . number_format($stats['totalReceivables'], 2))
                ->description("{$stats['pendingCount']} orders pending • {$stats['totalPendingDelivery']} awaiting delivery")
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('primary')
                ->extraAttributes(['title' => 'Total outstanding accounts receivable across all pending purchase orders']),

            Stat::make('Cleared Collections', '₱' . number_format($stats['totalCollected'], 2))
                ->description("{$stats['paidCount']} settled orders (COD, PDC & 30-Day)")
                ->descriptionIcon('heroicon-m-check-badge')
                ->color('success')
                ->extraAttributes(['title' => 'Total cleared payments received via Cash On Delivery, Post-Dated Checks, and verified credit terms']),

            Stat::make('Due in ≤ 10 Days', "{$stats['warningCount']} POs (₱" . number_format($stats['warningAmount'], 2) . ")")
                ->description($stats['warningCount'] > 0 ? 'Urgent: dispatch email reminders' : 'All accounts within safe window')
                ->descriptionIcon('heroicon-m-clock')
                ->color($stats['warningCount'] > 0 ? 'warning' : 'success')
                ->extraAttributes(['title' => 'Orders with settlement due dates in 10 or fewer calendar days']),

            Stat::make('Overdue Accounts', "{$stats['overdueCount']} POs (₱" . number_format($stats['overdueAmount'], 2) . ")")
                ->description($stats['overdueCount'] > 0 ? 'Past 30-day limit: collection required' : 'Zero overdue accounts')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($stats['overdueCount'] > 0 ? 'danger' : 'success')
                ->extraAttributes(['title' => 'Orders that have surpassed the strict 30-day settlement limit without payment clearance']),
        ];
    }
}
