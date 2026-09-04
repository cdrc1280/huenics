<?php

namespace App\Filament\Widgets;

use App\Models\PurchaseOrder;
use App\Models\Quotation;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Livewire\Attributes\On;

class SalesRevenueChartWidget extends ChartWidget
{
    protected ?string $heading = 'Revenue & Quotation Analytics Trend';
    protected static ?int $sort = 2;
    protected int | string | array $columnSpan = 'full';
    protected ?string $maxHeight = '280px';

    public ?int $selectedYear = null;
    public ?int $selectedAgentId = null;
    public bool $filterInhouse = false;

    public function mount(?int $selectedYear = null, ?int $selectedAgentId = null, bool $filterInhouse = false): void
    {
        $this->selectedYear = $selectedYear ?? (int) now()->year;
        $this->selectedAgentId = $selectedAgentId;
        $this->filterInhouse = $filterInhouse;
    }

    #[On('salesFilterUpdated')]
    public function updateFilter(array $filterData): void
    {
        $this->selectedYear = (int) ($filterData['selectedYear'] ?? now()->year);
        $this->selectedAgentId = $filterData['selectedAgentId'] ?? null;
        $this->filterInhouse = (bool) ($filterData['filterInhouse'] ?? false);
        $this->cachedData = null;
        $this->updateChartData();
    }

    protected function getData(): array
    {
        $year = $this->selectedYear ?: (int) now()->year;
        $months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

        $revenueData = [];
        $quotationData = [];

        for ($m = 1; $m <= 12; $m++) {
            $start = Carbon::create($year, $m, 1)->startOfMonth()->toDateTimeString();
            $end = Carbon::create($year, $m, 1)->endOfMonth()->toDateTimeString();
            $startDateOnly = Carbon::create($year, $m, 1)->startOfMonth()->toDateString();
            $endDateOnly = Carbon::create($year, $m, 1)->endOfMonth()->toDateString();

            $poQuery = PurchaseOrder::whereNotIn('status', [PurchaseOrder::STATUS_CANCELLED, PurchaseOrder::STATUS_REJECTED])
                ->where(function ($q) use ($start, $end, $startDateOnly, $endDateOnly) {
                    $q->whereBetween('order_date', [$start, $end])
                        ->orWhere(fn($s) => $s->whereDate('order_date', '>=', $startDateOnly)->whereDate('order_date', '<=', $endDateOnly))
                        ->orWhereBetween('actual_delivery_date', [$startDateOnly, $endDateOnly])
                        ->orWhereBetween('completed_at', [$start, $end])
                        ->orWhereBetween('created_at', [$start, $end]);
                });

            $qQuery = Quotation::whereNotIn('status', [Quotation::STATUS_REJECTED])
                ->where(function ($q) use ($start, $end, $startDateOnly, $endDateOnly) {
                    $q->whereBetween('quotation_date', [$start, $end])
                        ->orWhere(fn($s) => $s->whereDate('quotation_date', '>=', $startDateOnly)->whereDate('quotation_date', '<=', $endDateOnly))
                        ->orWhereBetween('created_at', [$start, $end]);
                });

            if ($this->filterInhouse) {
                $poQuery->whereHas('salesAgent', fn($u) => $u->where('is_owner', true));
                $qQuery->whereHas('salesAgent', fn($u) => $u->where('is_owner', true));
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
            'labels' => $months,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
