<?php

namespace App\Filament\Widgets;

use App\Models\PurchaseOrder;
use App\Models\Quotation;
use App\Models\SalesQuota;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SalesOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    public ?int $agentId = null;
    public bool $isInhouse = false;
    public ?int $month = null;
    public ?int $year = null;

    protected function getStats(): array
    {
        $month = $this->month ?: now()->month;
        $year  = $this->year ?: now()->year;
        $agentId = $this->agentId;
        $isInhouse = $this->isInhouse;

        $quotationQuery = Quotation::whereMonth('quotation_date', $month)->whereYear('quotation_date', $year);
        $poQuery = PurchaseOrder::whereMonth('order_date', $month)->whereYear('order_date', $year);

        if ($isInhouse) {
            $quotationQuery->whereHas('salesAgent', fn($q) => $q->where('is_owner', true));
            $poQuery->whereHas('salesAgent', fn($q) => $q->where('is_owner', true));
        } elseif ($agentId) {
            $quotationQuery->where('sales_agent_id', $agentId);
            $poQuery->where('sales_agent_id', $agentId);
        }

        $totalQuotations = $quotationQuery->count();
        $convertedPos = (clone $poQuery)->count();
        $winRate = $totalQuotations > 0
            ? round(($convertedPos / $totalQuotations) * 100, 1)
            : 0;

        $totalRevenue = (float) (clone $poQuery)->sum('order_amount');
        $totalProfit = (float) (clone $poQuery)->sum('realized_profit');

        $warrantyQuery = PurchaseOrder::where('warranty_status', PurchaseOrder::WARRANTY_EXPIRING);
        $overdueQuery = PurchaseOrder::where('delivery_status', PurchaseOrder::DELIVERY_OVERDUE);
        if ($isInhouse) {
            $warrantyQuery->whereHas('salesAgent', fn($q) => $q->where('is_owner', true));
            $overdueQuery->whereHas('salesAgent', fn($q) => $q->where('is_owner', true));
        } elseif ($agentId) {
            $warrantyQuery->where('sales_agent_id', $agentId);
            $overdueQuery->where('sales_agent_id', $agentId);
        }

        $expiringSoon = $warrantyQuery->count();
        $overdueDeliveries = $overdueQuery->count();

        $stats = [
            'quotations' => $totalQuotations,
            'converted' => $convertedPos,
            'win_rate' => $winRate,
            'revenue' => $totalRevenue,
            'profit' => $totalProfit,
            'expiring' => $expiringSoon,
            'overdue' => $overdueDeliveries,
        ];

        return [
            Stat::make('Quotations This Month', $stats['quotations'])
                ->description(now()->format('F Y'))
                ->descriptionIcon('heroicon-m-document-text')
                ->color('info')
                ->extraAttributes(['title' => 'Total active customer quotations created this month']),

            Stat::make('POs Won (Converted)', $stats['converted'])
                ->description("Win Rate: {$stats['win_rate']}%")
                ->descriptionIcon('heroicon-m-trophy')
                ->color('success')
                ->extraAttributes(['title' => "Quotations converted into confirmed Purchase Orders ({$stats['win_rate']}% conversion rate)"]),

            Stat::make('Revenue This Month', '₱' . number_format($stats['revenue'], 2))
                ->description("Profit: ₱" . number_format($stats['profit'], 2))
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('primary')
                ->extraAttributes(['title' => 'Gross sales revenue & realized gross profit for confirmed orders this month']),

            Stat::make('Warranties Expiring Soon', $stats['expiring'])
                ->description('Within 30 days')
                ->descriptionIcon('heroicon-m-clock')
                ->color($stats['expiring'] > 0 ? 'warning' : 'success')
                ->extraAttributes(['title' => 'Active warranties with 30 or fewer days remaining before expiration']),

            Stat::make('Overdue Deliveries', $stats['overdue'])
                ->description('Past expected delivery date')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($stats['overdue'] > 0 ? 'danger' : 'success')
                ->extraAttributes(['title' => 'Confirmed Purchase Orders exceeding expected delivery date']),
        ];
    }

}
