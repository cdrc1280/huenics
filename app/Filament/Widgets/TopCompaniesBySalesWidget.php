<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\PurchaseOrder;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Illuminate\Contracts\Support\Htmlable;
use Livewire\Attributes\On;

class TopCompaniesBySalesWidget extends ChartWidget
{
    protected static ?int $sort = 5;

    protected int|string|array $columnSpan = 'full';

    protected ?string $maxHeight = '360px';

    public const TOP_COMPANIES_LIMIT = 10;

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
        $this->periodType = (string) ($filterData['periodType'] ?? 'month');
        $this->selectedDate = (string) ($filterData['selectedDate'] ?? now()->toDateString());
        $this->selectedWeek = (int) ($filterData['selectedWeek'] ?? now()->weekOfYear);
        $this->selectedMonth = (int) ($filterData['selectedMonth'] ?? now()->month);
        $this->selectedYear = (int) ($filterData['selectedYear'] ?? now()->year);
        $this->selectedAgentId = isset($filterData['selectedAgentId']) ? (int) $filterData['selectedAgentId'] : null;
        $this->filterInhouse = (bool) ($filterData['filterInhouse'] ?? false);
        $this->cachedData = null;
        $this->updateChartData();
    }

    protected function resolveDateRange(): array
    {
        $year = (int) ($this->selectedYear ?: now()->year);

        return match ($this->periodType) {
            'days', 'day' => [
                Carbon::parse($this->selectedDate ?: now()->toDateString())->startOfDay(),
                Carbon::parse($this->selectedDate ?: now()->toDateString())->endOfDay(),
            ],
            'weeks', 'week' => [
                Carbon::now()->setISODate($year, (int) ($this->selectedWeek ?: now()->weekOfYear))->startOfWeek(),
                Carbon::now()->setISODate($year, (int) ($this->selectedWeek ?: now()->weekOfYear))->endOfWeek(),
            ],
            'years', 'year' => [
                Carbon::create($year, 1, 1)->startOfYear(),
                Carbon::create($year, 12, 31)->endOfYear(),
            ],
            default => [
                Carbon::create($year, (int) ($this->selectedMonth ?: now()->month), 1)->startOfMonth(),
                Carbon::create($year, (int) ($this->selectedMonth ?: now()->month), 1)->endOfMonth(),
            ],
        };
    }

    public function getHeading(): string|Htmlable|null
    {
        return 'Top 10 Client Accounts by Sales';
    }

    public function getDescription(): ?string
    {
        [$start, $end] = $this->resolveDateRange();
        $periodLabel = match ($this->periodType) {
            'days', 'day' => $start->format('M d, Y'),
            'weeks', 'week' => "Week {$this->selectedWeek} ({$start->format('M d')} – {$end->format('M d, Y')})",
            'years', 'year' => "Year {$start->format('Y')}",
            default => $start->format('F Y'),
        };

        return "Highest revenue contributing corporate accounts in {$periodLabel}";
    }

    protected function getData(): array
    {
        [$start, $end] = $this->resolveDateRange();
        $startStr = $start->copy()->startOfDay()->toDateTimeString();
        $endStr = $end->copy()->endOfDay()->toDateTimeString();
        $startDateOnly = $start->toDateString();
        $endDateOnly = $end->toDateString();

        $poQuery = PurchaseOrder::query()
            ->whereNotIn('status', [PurchaseOrder::STATUS_CANCELLED, PurchaseOrder::STATUS_REJECTED]);

        $poQuery->where(function ($q) use ($startStr, $endStr, $startDateOnly, $endDateOnly) {
            $q->whereBetween('order_date', [$startStr, $endStr])
                ->orWhere(fn ($s) => $s->whereDate('order_date', '>=', $startDateOnly)->whereDate('order_date', '<=', $endDateOnly))
                ->orWhereBetween('actual_delivery_date', [$startDateOnly, $endDateOnly])
                ->orWhereBetween('completed_at', [$startStr, $endStr])
                ->orWhereBetween('created_at', [$startStr, $endStr]);
        });

        if ($this->filterInhouse) {
            $poQuery->where(fn ($q) => $q->whereHas('salesAgent', fn ($u) => $u->where('is_owner', true))->orWhereNull('sales_agent_id'));
        } elseif ($this->selectedAgentId) {
            $poQuery->where('sales_agent_id', $this->selectedAgentId);
        }

        $orders = $poQuery->with('quotation:id,customer_company,customer_name')
            ->get(['id', 'quotation_id', 'customer_name', 'order_amount']);

        if ($orders->isEmpty()) {
            return [
                'datasets' => [
                    [
                        'label' => 'Total Sales (₱)',
                        'data' => [0],
                        'backgroundColor' => ['#94a3b8'],
                    ],
                ],
                'labels' => ['No Confirmed Orders in Period'],
            ];
        }

        $grouped = $orders->groupBy(function ($order) {
            $company = trim((string) ($order->quotation?->customer_company ?? ''));
            if ($company !== '' && strtolower($company) !== 'n/a' && strtolower($company) !== 'none') {
                return $company;
            }

            $customerName = trim((string) ($order->customer_name ?? ''));
            if ($customerName !== '' && strtolower($customerName) !== 'n/a' && strtolower($customerName) !== 'none') {
                return $customerName;
            }

            return 'Individual Client';
        })->map(function ($items, $account) {
            return [
                'account' => (string) $account,
                'total_sales' => (float) $items->sum('order_amount'),
                'order_count' => $items->count(),
            ];
        })->sortByDesc('total_sales')->take(self::TOP_COMPANIES_LIMIT);

        $labels = [];
        $data = [];

        foreach ($grouped as $row) {
            $name = $row['account'];
            $shortName = strlen($name) > 28 ? substr($name, 0, 26).'..' : $name;
            $labels[] = $shortName;
            $data[] = round((float) $row['total_sales'], 2);
        }

        $palette = [
            '#2563eb', // Blue 600
            '#3b82f6', // Blue 500
            '#0ea5e9', // Sky 500
            '#06b6d4', // Cyan 500
            '#14b8a6', // Teal 500
            '#10b981', // Emerald 500
            '#6366f1', // Indigo 500
            '#8b5cf6', // Violet 500
            '#a855f7', // Purple 500
            '#d946ef', // Fuchsia 500
        ];

        return [
            'datasets' => [
                [
                    'label' => 'Total Sales (₱)',
                    'data' => $data,
                    'backgroundColor' => array_slice($palette, 0, count($data)),
                    'borderRadius' => 6,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'indexAxis' => 'y',
            'plugins' => [
                'legend' => [
                    'display' => false,
                ],
                'tooltip' => [
                    'callbacks' => [
                        'label' => 'function(context) { return " ₱" + Number(context.raw).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}); }',
                    ],
                ],
            ],
            'scales' => [
                'x' => [
                    'grid' => [
                        'display' => true,
                    ],
                    'ticks' => [
                        'callback' => 'function(val) { return "₱" + Number(val).toLocaleString(); }',
                    ],
                ],
                'y' => [
                    'grid' => [
                        'display' => false,
                    ],
                ],
            ],
        ];
    }
}
