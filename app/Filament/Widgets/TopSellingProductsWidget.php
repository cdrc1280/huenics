<?php

namespace App\Filament\Widgets;

use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderLineItem;
use App\Models\User;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;

class TopSellingProductsWidget extends ChartWidget
{
    protected static ?int $sort = 4;
    protected int | string | array $columnSpan = 1;
    protected ?string $maxHeight = '280px';

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

    public function getHeading(): string | Htmlable | null
    {
        return 'Top Products by Revenue';
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

        return "Highest revenue generating products for won orders in {$periodLabel}";
    }

    public function getData(): array
    {
        [$start, $end] = $this->resolveDateRange();
        $startStr = $start->copy()->startOfDay()->toDateTimeString();
        $endStr = $end->copy()->endOfDay()->toDateTimeString();
        $startDateOnly = $start->toDateString();
        $endDateOnly = $end->toDateString();

        $poQuery = PurchaseOrder::query()
            ->whereNotIn('status', [PurchaseOrder::STATUS_CANCELLED, PurchaseOrder::STATUS_REJECTED]);

        // Resilient date filter matching SalesDashboard and SalesRevenueChartWidget
        $poQuery->where(function ($q) use ($startStr, $endStr, $startDateOnly, $endDateOnly) {
            $q->whereBetween('order_date', [$startStr, $endStr])
                ->orWhere(fn ($s) => $s->whereDate('order_date', '>=', $startDateOnly)->whereDate('order_date', '<=', $endDateOnly))
                ->orWhereBetween('actual_delivery_date', [$startDateOnly, $endDateOnly])
                ->orWhereBetween('completed_at', [$startStr, $endStr])
                ->orWhereBetween('created_at', [$startStr, $endStr]);
        });

        // Sales executive & inhouse filtering
        if ($this->filterInhouse) {
            $poQuery->where(fn ($q) => $q->whereHas('salesAgent', fn ($u) => $u->where('is_owner', true))->orWhereNull('sales_agent_id'));
        } elseif ($this->selectedAgentId) {
            $poQuery->where('sales_agent_id', $this->selectedAgentId);
        }

        $poIds = $poQuery->pluck('id');

        if ($poIds->isEmpty()) {
            return [
                'datasets' => [
                    [
                        'label' => 'Revenue (₱)',
                        'data' => [0],
                        'backgroundColor' => ['#94a3b8'],
                    ],
                ],
                'labels' => ['No Won Orders in Period (0)'],
            ];
        }

        $rawItems = PurchaseOrderLineItem::query()
            ->whereIn('purchase_order_id', $poIds)
            ->with('product')
            ->get();

        if ($rawItems->isEmpty()) {
            return [
                'datasets' => [
                    [
                        'label' => 'Revenue (₱)',
                        'data' => [0],
                        'backgroundColor' => ['#94a3b8'],
                    ],
                ],
                'labels' => ['No Order Line Items in Period (0)'],
            ];
        }

        $grouped = $rawItems->groupBy(function ($item) {
            return $item->product?->canonical_name
                ?: ($item->description ?: ($item->item_code ?: 'Item #' . $item->id));
        })->map(function ($items, $name) {
            return [
                'name' => (string) $name,
                'revenue' => (float) $items->sum('line_total'),
                'qty' => (float) $items->sum('qty'),
            ];
        })->sortByDesc('revenue')->take(7);

        $labels = [];
        $data = [];

        foreach ($grouped as $item) {
            $productName = $item['name'];
            $shortName = strlen($productName) > 24 ? substr($productName, 0, 22) . '..' : $productName;
            $labels[] = $shortName;
            $data[] = round((float) $item['revenue'], 2);
        }

        return [
            'datasets' => [
                [
                    'label' => 'Revenue (₱)',
                    'data' => $data,
                    'backgroundColor' => [
                        '#2563eb',
                        '#3b82f6',
                        '#60a5fa',
                        '#93c5fd',
                        '#38bdf8',
                        '#818cf8',
                        '#a5b4fc',
                    ],
                    'borderRadius' => 4,
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
            ],
            'scales' => [
                'x' => [
                    'ticks' => [
                        'callback' => '(function(value) { return "₱" + Number(value).toLocaleString(); })',
                    ],
                ],
            ],
        ];
    }
}
