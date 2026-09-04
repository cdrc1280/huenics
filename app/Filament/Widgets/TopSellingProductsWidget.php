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
        ?int $selectedMonth = null
    ): void {
        $this->selectedYear = $selectedYear ?? (int) now()->year;
        $this->selectedAgentId = $selectedAgentId;
        $this->filterInhouse = $filterInhouse;
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
        return 'Top Products by Revenue';
    }

    public function getDescription(): ?string
    {
        return 'Highest revenue generating products for won POs in selected period';
    }

    protected function getData(): array
    {
        $poQuery = PurchaseOrder::query()->where('is_completed', true);

        // Timeframe filter
        if ($this->periodType === 'day' && $this->selectedDate) {
            $poQuery->whereDate('order_date', $this->selectedDate);
        } elseif ($this->periodType === 'week' && $this->selectedYear && $this->selectedWeek) {
            $startOfWeek = Carbon::now()->setISODate($this->selectedYear, $this->selectedWeek)->startOfWeek();
            $endOfWeek = (clone $startOfWeek)->endOfWeek();
            $poQuery->whereBetween('order_date', [$startOfWeek, $endOfWeek]);
        } elseif ($this->periodType === 'month' && $this->selectedYear && $this->selectedMonth) {
            $poQuery->whereYear('order_date', $this->selectedYear)
                ->whereMonth('order_date', $this->selectedMonth);
        } elseif ($this->periodType === 'year' && $this->selectedYear) {
            $poQuery->whereYear('order_date', $this->selectedYear);
        }

        // Sales executive & inhouse filtering
        if ($this->filterInhouse) {
            $poQuery->whereHas('salesAgent', fn ($q) => $q->where('is_owner', true));
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
                'labels' => ['No Completed Orders in Period'],
            ];
        }

        $topItems = PurchaseOrderLineItem::query()
            ->whereIn('purchase_order_id', $poIds)
            ->with('product')
            ->select('product_id', DB::raw('SUM(line_total) as total_revenue'), DB::raw('SUM(qty) as total_qty'))
            ->groupBy('product_id')
            ->orderByDesc('total_revenue')
            ->limit(5)
            ->get();

        $labels = [];
        $data = [];

        foreach ($topItems as $item) {
            $productName = $item->product?->canonical_name ?: 'Unknown Item';
            $shortName = strlen($productName) > 22 ? substr($productName, 0, 20) . '..' : $productName;
            $labels[] = $shortName;
            $data[] = round((float) $item->total_revenue, 2);
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
                        '#bfdbfe',
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
