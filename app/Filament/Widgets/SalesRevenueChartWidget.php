<?php

namespace App\Filament\Widgets;

use App\Models\PurchaseOrder;
use App\Models\Quotation;
use App\Models\User;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Illuminate\Contracts\Support\Htmlable;
use Livewire\Attributes\On;

class SalesRevenueChartWidget extends ChartWidget
{
    protected static ?int $sort = 2;
    protected int | string | array $columnSpan = 'full';
    protected ?string $maxHeight = '320px';

    public string $periodType = 'month';
    public ?string $selectedDate = null;
    public ?int $selectedWeek = null;
    public ?int $selectedMonth = null;
    public ?int $selectedYear = null;
    public ?int $selectedAgentId = null;
    public bool $filterInhouse = false;

    public function mount(
        ?int $selectedYear = null,
        ?int $selectedAgentId = null,
        bool $filterInhouse = false,
        string $periodType = 'month',
        ?string $selectedDate = null,
        ?int $selectedWeek = null,
        ?int $selectedMonth = null,
        ?int $agentId = null,
        bool $isInhouse = false
    ): void {
        $this->selectedYear = $selectedYear ?? (int) now()->year;
        $this->selectedAgentId = $selectedAgentId ?? $agentId;
        $this->filterInhouse = $filterInhouse || $isInhouse;
        $this->periodType = $periodType;
        $this->selectedDate = $selectedDate ?? now()->toDateString();
        $this->selectedWeek = $selectedWeek ?? (int) now()->weekOfYear;
        $this->selectedMonth = $selectedMonth ?? (int) now()->month;
    }

    #[On('salesFilterUpdated')]
    public function updateFilter(array $filterData): void
    {
        $this->periodType = $filterData['periodType'] ?? 'month';
        $this->selectedDate = $filterData['selectedDate'] ?? now()->toDateString();
        $this->selectedWeek = (int) ($filterData['selectedWeek'] ?? now()->weekOfYear);
        $this->selectedMonth = (int) ($filterData['selectedMonth'] ?? now()->month);
        $this->selectedYear = (int) ($filterData['selectedYear'] ?? now()->year);
        $this->selectedAgentId = $filterData['selectedAgentId'] ?? null;
        $this->filterInhouse = (bool) ($filterData['filterInhouse'] ?? false);
        $this->cachedData = null;
        $this->updateChartData();
    }

    public function getHeading(): string | Htmlable | null
    {
        $year = $this->selectedYear ?: (int) now()->year;
        return match ($this->periodType) {
            'days' => "Daily Sales & Pipeline Velocity — " . Carbon::parse($this->selectedDate ?: now()->toDateString())->format('M d, Y'),
            'weeks' => "Weekly Sales & Pipeline Velocity — Week {$this->selectedWeek} ({$year})",
            'years' => "Annual Revenue & Quotation Trend — {$year}",
            default => "Monthly Revenue & Quotation Trend — " . Carbon::create($year, $this->selectedMonth ?: (int) now()->month, 1)->format('F Y'),
        };
    }

    public function getDescription(): string | Htmlable | null
    {
        $filterContext = 'All Sales Executives';
        if ($this->filterInhouse) {
            $filterContext = 'Inhouse / Owner Accounts';
        } elseif ($this->selectedAgentId) {
            $agent = User::find($this->selectedAgentId);
            if ($agent) {
                $filterContext = "Sales Executive: {$agent->name}";
            }
        }

        return "Closed Sales Revenue (₱) vs. Quoted Pipeline Value (₱) • Scope: {$filterContext}";
    }

    protected function getData(): array
    {
        $year = $this->selectedYear ?: (int) now()->year;
        $month = $this->selectedMonth ?: (int) now()->month;
        $labels = [];
        $intervals = [];

        switch ($this->periodType) {
            case 'days':
                $target = Carbon::parse($this->selectedDate ?: now()->toDateString())->startOfDay();
                for ($d = 6; $d >= 0; $d--) {
                    $day = $target->copy()->subDays($d);
                    $labels[] = $day->format('M d');
                    $intervals[] = [$day->copy()->startOfDay(), $day->copy()->endOfDay()];
                }
                break;

            case 'weeks':
                $week = (int) ($this->selectedWeek ?: now()->weekOfYear);
                $startOfWeek = Carbon::now()->setISODate($year, $week)->startOfWeek();
                for ($d = 0; $d < 7; $d++) {
                    $day = $startOfWeek->copy()->addDays($d);
                    $labels[] = $day->format('D (M d)');
                    $intervals[] = [$day->copy()->startOfDay(), $day->copy()->endOfDay()];
                }
                break;

            case 'years':
                $monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
                for ($m = 1; $m <= 12; $m++) {
                    $labels[] = $monthNames[$m - 1];
                    $intervals[] = [
                        Carbon::create($year, $m, 1)->startOfMonth(),
                        Carbon::create($year, $m, 1)->endOfMonth(),
                    ];
                }
                break;

            case 'month':
            default:
                $daysInMonth = Carbon::create($year, $month, 1)->daysInMonth;
                for ($d = 1; $d <= $daysInMonth; $d++) {
                    $day = Carbon::create($year, $month, $d);
                    $labels[] = (string) $d;
                    $intervals[] = [$day->copy()->startOfDay(), $day->copy()->endOfDay()];
                }
                break;
        }

        $revenueData = [];
        $quotationData = [];

        foreach ($intervals as [$start, $end]) {
            $startStr = $start->copy()->startOfDay()->toDateTimeString();
            $endStr = $end->copy()->endOfDay()->toDateTimeString();
            $startDateOnly = $start->toDateString();
            $endDateOnly = $end->toDateString();

            $poQuery = PurchaseOrder::whereNotIn('status', [PurchaseOrder::STATUS_CANCELLED, PurchaseOrder::STATUS_REJECTED])
                ->where(function ($q) use ($startStr, $endStr, $startDateOnly, $endDateOnly) {
                    $q->whereBetween('order_date', [$startStr, $endStr])
                        ->orWhere(fn($s) => $s->whereDate('order_date', '>=', $startDateOnly)->whereDate('order_date', '<=', $endDateOnly))
                        ->orWhereBetween('actual_delivery_date', [$startDateOnly, $endDateOnly])
                        ->orWhereBetween('completed_at', [$startStr, $endStr])
                        ->orWhereBetween('created_at', [$startStr, $endStr]);
                });

            $qQuery = Quotation::whereNotIn('status', [Quotation::STATUS_REJECTED])
                ->where(function ($q) use ($startStr, $endStr, $startDateOnly, $endDateOnly) {
                    $q->whereBetween('quotation_date', [$startStr, $endStr])
                        ->orWhere(fn($s) => $s->whereDate('quotation_date', '>=', $startDateOnly)->whereDate('quotation_date', '<=', $endDateOnly))
                        ->orWhereBetween('created_at', [$startStr, $endStr]);
                });

            if ($this->filterInhouse) {
                $poQuery->where(fn($q) => $q->whereHas('salesAgent', fn($u) => $u->where('is_owner', true))->orWhereNull('sales_agent_id'));
                $qQuery->where(fn($q) => $q->whereHas('salesAgent', fn($u) => $u->where('is_owner', true))->orWhereNull('sales_agent_id'));
            } elseif ($this->selectedAgentId) {
                $poQuery->where('sales_agent_id', $this->selectedAgentId);
                $qQuery->where('sales_agent_id', $this->selectedAgentId);
            }

            $revenueData[] = round((float) $poQuery->sum('order_amount'), 2);
            $quotationData[] = round((float) $qQuery->sum('total_amount'), 2);
        }

        return [
            'datasets' => [
                [
                    'label' => 'Closed Sales Revenue (₱)',
                    'data' => $revenueData,
                    'backgroundColor' => 'rgba(16, 185, 129, 0.15)',
                    'borderColor' => '#10b981',
                    'fill' => true,
                    'tension' => 0.35,
                ],
                [
                    'label' => 'Quoted Pipeline Value (₱)',
                    'data' => $quotationData,
                    'backgroundColor' => 'rgba(59, 130, 246, 0.10)',
                    'borderColor' => '#3b82f6',
                    'fill' => true,
                    'tension' => 0.35,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'bottom',
                    'labels' => [
                        'padding' => 20,
                        'usePointStyle' => true,
                        'pointStyle' => 'circle',
                        'font' => [
                            'size' => 12,
                            'weight' => '500',
                        ],
                    ],
                ],
                'tooltip' => [
                    'padding' => 12,
                    'cornerRadius' => 8,
                ],
            ],
            'layout' => [
                'padding' => [
                    'top' => 16,
                    'bottom' => 12,
                    'left' => 12,
                    'right' => 16,
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'padding' => 8,
                    ],
                    'grid' => [
                        'color' => 'rgba(156, 163, 175, 0.1)',
                    ],
                ],
                'x' => [
                    'ticks' => [
                        'padding' => 8,
                    ],
                    'grid' => [
                        'display' => false,
                    ],
                ],
            ],
        ];
    }
}
