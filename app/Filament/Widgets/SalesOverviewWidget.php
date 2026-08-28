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

        switch ($this->periodType) {
            case 'days':
                $date = !empty($this->selectedDate) ? Carbon::parse($this->selectedDate)->startOfDay() : now()->startOfDay();
                return [
                    $date,
                    $date->copy()->endOfDay(),
                    $date->format('F d, Y'),
                ];

            case 'weeks':
                $week = (int) ($this->selectedWeek ?: now()->weekOfYear);
                $start = Carbon::now()->setISODate($year, $week)->startOfWeek();
                $end = $start->copy()->endOfWeek();
                return [
                    $start,
                    $end,
                    "Week {$week} (" . $start->format('M d') . " – " . $end->format('M d, Y') . ")",
                ];

            case 'years':
                $start = Carbon::create($year, 1, 1)->startOfYear();
                $end = Carbon::create($year, 12, 31)->endOfYear();
                return [
                    $start,
                    $end,
                    "Year {$year}",
                ];

            case 'month':
            default:
                $month = (int) ($this->selectedMonth ?: now()->month);
                $start = Carbon::create($year, $month, 1)->startOfMonth();
                $end = $start->copy()->endOfMonth();
                return [
                    $start,
                    $end,
                    $start->format('F Y'),
                ];
        }
    }

    protected function getStats(): array
    {
        [$startDate, $endDate, $periodLabel] = $this->getDateRange();
        $agentId = $this->agentId;
        $isInhouse = $this->isInhouse;
        $periodType = $this->periodType;
        $selectedDate = $this->selectedDate;
        $selectedWeek = $this->selectedWeek;
        $selectedMonth = $this->selectedMonth;
        $selectedYear = $this->selectedYear;

        $cacheKey = 'sales_overview_' . md5(json_encode([
            $agentId, $isInhouse, $periodType, $selectedDate, $selectedWeek, $selectedMonth, $selectedYear,
        ]));

        $data = \Illuminate\Support\Facades\Cache::remember($cacheKey, now()->addMinutes(5), function () use ($startDate, $endDate, $agentId, $isInhouse) {
            $startStr = $startDate->toDateString();
            $endStr = $endDate->toDateString();

            $quotationQuery = Quotation::where(function ($q) use ($startStr, $endStr, $startDate, $endDate) {
                $q->whereBetween('quotation_date', [$startStr, $endStr])
                  ->orWhere(fn($sub) => $sub->whereNull('quotation_date')->whereBetween('created_at', [$startDate, $endDate]));
            });

            $poQuery = PurchaseOrder::where('is_completed', true)
                ->whereNotIn('status', [PurchaseOrder::STATUS_CANCELLED, PurchaseOrder::STATUS_REJECTED])
                ->where(function ($q) use ($startStr, $endStr, $startDate, $endDate) {
                    $q->whereBetween('order_date', [$startStr, $endStr])
                      ->orWhere(fn($sub) => $sub->whereNull('order_date')->whereBetween('created_at', [$startDate, $endDate]));
                });

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

            $totalRevenue = (float) (clone $poQuery)->sum('order_amount') ?? 0.0;
            $totalProfit = (float) (clone $poQuery)->sum('realized_profit') ?? 0.0;

            $warrantyQuery = PurchaseOrder::whereBetween('warranty_end_date', [now(), now()->addDays(30)])
                ->whereNotIn('warranty_status', ['expired', 'no_warranty']);

            $overdueQuery = PurchaseOrder::where('expected_delivery_date', '<', now())
                ->where('delivery_status', '!=', 'delivered')
                ->where('is_completed', false);

            if ($isInhouse) {
                $warrantyQuery->whereHas('salesAgent', fn($q) => $q->where('is_owner', true));
                $overdueQuery->whereHas('salesAgent', fn($q) => $q->where('is_owner', true));
            } elseif ($agentId) {
                $warrantyQuery->where('sales_agent_id', $agentId);
                $overdueQuery->where('sales_agent_id', $agentId);
            }

            $expiringSoon = $warrantyQuery->count();
            $overdueDeliveries = $overdueQuery->count();

            return [
                'totalQuotations' => $totalQuotations,
                'convertedPos' => $convertedPos,
                'winRate' => $winRate,
                'totalRevenue' => $totalRevenue,
                'totalProfit' => $totalProfit,
                'expiringSoon' => $expiringSoon,
                'overdueDeliveries' => $overdueDeliveries,
            ];
        });

        $periodPrefix = match ($this->periodType) {
            'days'  => 'Today / Selected Day',
            'weeks' => 'This Week',
            'years' => 'This Year',
            default => 'This Month',
        };

        return [
            Stat::make("Quotations ({$periodLabel})", $data['totalQuotations'])
                ->description($periodPrefix)
                ->descriptionIcon('heroicon-m-document-text')
                ->color('info')
                ->extraAttributes(['title' => "Total customer quotations created during {$periodLabel}"]),

            Stat::make('POs Won (Converted)', $data['convertedPos'])
                ->description("Win Rate: {$data['winRate']}%")
                ->descriptionIcon('heroicon-m-trophy')
                ->color('success')
                ->extraAttributes(['title' => "Quotations converted into confirmed Purchase Orders during {$periodLabel} ({$data['winRate']}% conversion rate)"]),

            Stat::make("Revenue ({$periodLabel})", '₱' . number_format($data['totalRevenue'], 2))
                ->description("Profit: ₱" . number_format($data['totalProfit'], 2))
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('primary')
                ->extraAttributes(['title' => "Gross sales revenue & realized gross profit for confirmed orders during {$periodLabel}"]),

            Stat::make('Warranties Expiring Soon', $data['expiringSoon'])
                ->description('Within 30 days')
                ->descriptionIcon('heroicon-m-clock')
                ->color($data['expiringSoon'] > 0 ? 'warning' : 'success')
                ->extraAttributes(['title' => 'Active warranties with 30 or fewer days remaining before expiration']),

            Stat::make('Overdue Deliveries', $data['overdueDeliveries'])
                ->description('Past expected delivery date')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($data['overdueDeliveries'] > 0 ? 'danger' : 'success')
                ->extraAttributes(['title' => 'Confirmed Purchase Orders exceeding expected delivery date']),
        ];
    }
}
