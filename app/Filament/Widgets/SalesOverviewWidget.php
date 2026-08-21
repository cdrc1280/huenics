<?php

namespace App\Filament\Widgets;

use App\Models\PurchaseOrder;
use App\Models\Quotation;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SalesOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    public ?int $agentId = null;
    public bool $isInhouse = false;
    public string $periodType = 'month';
    public ?string $selectedDate = null;
    public ?int $selectedWeek = null;
    public ?int $selectedMonth = null;
    public ?int $selectedYear = null;

    public function getDateRange(): array
    {
        $year = (int) ($this->selectedYear ?: now()->year);

        return match ($this->periodType) {
            'days' => [
                $date = !empty($this->selectedDate) ? Carbon::parse($this->selectedDate)->startOfDay() : now()->startOfDay(),
                $date->copy()->endOfDay(),
                $date->format('F d, Y'),
            ],
            'weeks' => [
                $start = Carbon::now()->setISODate($year, $this->selectedWeek ?: now()->weekOfYear)->startOfWeek(),
                $end = $start->copy()->endOfWeek(),
                "Week " . ($this->selectedWeek ?: now()->weekOfYear) . " (" . $start->format('M d') . " – " . $end->format('M d, Y') . ")",
            ],
            'years' => [
                $start = Carbon::create($year, 1, 1)->startOfYear(),
                $end = Carbon::create($year, 12, 31)->endOfYear(),
                "Year {$year}",
            ],
            default => [
                $start = Carbon::create($year, (int) ($this->selectedMonth ?: now()->month), 1)->startOfMonth(),
                $end = $start->copy()->endOfMonth(),
                $start->format('F Y'),
            ],
        };
    }

    protected function getStats(): array
    {
        [$startDate, $endDate, $periodLabel] = $this->getDateRange();
        $agentId = $this->agentId;
        $isInhouse = $this->isInhouse;

        $startStr = $startDate->toDateString();
        $endStr = $endDate->toDateString();

        $quotationQuery = Quotation::whereBetween('quotation_date', [$startStr, $endStr]);
        $poQuery = PurchaseOrder::whereBetween('order_date', [$startStr, $endStr]);

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

        $periodPrefix = match ($this->periodType) {
            'days'  => 'Today / Selected Day',
            'weeks' => 'This Week',
            'years' => 'This Year',
            default => 'This Month',
        };

        return [
            Stat::make("Quotations ({$periodLabel})", $totalQuotations)
                ->description($periodPrefix)
                ->descriptionIcon('heroicon-m-document-text')
                ->color('info')
                ->extraAttributes(['title' => "Total customer quotations created during {$periodLabel}"]),

            Stat::make('POs Won (Converted)', $convertedPos)
                ->description("Win Rate: {$winRate}%")
                ->descriptionIcon('heroicon-m-trophy')
                ->color('success')
                ->extraAttributes(['title' => "Quotations converted into confirmed Purchase Orders during {$periodLabel} ({$winRate}% conversion rate)"]),

            Stat::make("Revenue ({$periodLabel})", '₱' . number_format($totalRevenue, 2))
                ->description("Profit: ₱" . number_format($totalProfit, 2))
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('primary')
                ->extraAttributes(['title' => "Gross sales revenue & realized gross profit for confirmed orders during {$periodLabel}"]),

            Stat::make('Warranties Expiring Soon', $expiringSoon)
                ->description('Within 30 days')
                ->descriptionIcon('heroicon-m-clock')
                ->color($expiringSoon > 0 ? 'warning' : 'success')
                ->extraAttributes(['title' => 'Active warranties with 30 or fewer days remaining before expiration']),

            Stat::make('Overdue Deliveries', $overdueDeliveries)
                ->description('Past expected delivery date')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($overdueDeliveries > 0 ? 'danger' : 'success')
                ->extraAttributes(['title' => 'Confirmed Purchase Orders exceeding expected delivery date']),
        ];
    }
}
