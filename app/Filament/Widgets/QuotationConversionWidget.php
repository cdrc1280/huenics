<?php

namespace App\Filament\Widgets;

use App\Models\PurchaseOrder;
use App\Models\Quotation;
use App\Models\User;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Illuminate\Contracts\Support\Htmlable;
use Livewire\Attributes\On;

class QuotationConversionWidget extends ChartWidget
{
    protected static ?int $sort = 3;
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
        return 'Quotation Conversion & Pipeline Distribution';
    }

    public function getDescription(): ?string
    {
        return 'Distribution of pipeline quotes by conversion status in selected period';
    }

    protected function getData(): array
    {
        $quotationQuery = Quotation::query();

        // Timeframe filter
        if ($this->periodType === 'day' && $this->selectedDate) {
            $quotationQuery->whereDate('quotation_date', $this->selectedDate);
        } elseif ($this->periodType === 'week' && $this->selectedYear && $this->selectedWeek) {
            $startOfWeek = Carbon::now()->setISODate($this->selectedYear, $this->selectedWeek)->startOfWeek();
            $endOfWeek = (clone $startOfWeek)->endOfWeek();
            $quotationQuery->whereBetween('quotation_date', [$startOfWeek, $endOfWeek]);
        } elseif ($this->periodType === 'month' && $this->selectedYear && $this->selectedMonth) {
            $quotationQuery->whereYear('quotation_date', $this->selectedYear)
                ->whereMonth('quotation_date', $this->selectedMonth);
        } elseif ($this->periodType === 'year' && $this->selectedYear) {
            $quotationQuery->whereYear('quotation_date', $this->selectedYear);
        }

        // Sales executive & inhouse filtering
        if ($this->filterInhouse) {
            $quotationQuery->whereHas('salesAgent', fn ($q) => $q->where('is_owner', true));
        } elseif ($this->selectedAgentId) {
            $quotationQuery->where('sales_agent_id', $this->selectedAgentId);
        }

        $allQuotes = $quotationQuery->get(['id', 'status', 'total_amount', 'valid_until']);

        $convertedCount = $allQuotes->where('status', 'converted')->count();
        $approvedCount = $allQuotes->where('status', 'approved')->count();
        $pendingCount = $allQuotes->whereIn('status', ['pending_approval', 'under_review'])->count();
        $draftCount = $allQuotes->where('status', 'draft')->count();
        $rejectedCount = $allQuotes->whereIn('status', ['rejected', 'expired'])->count();

        // Also check if any quote has linked PO
        if ($convertedCount === 0 && $allQuotes->isNotEmpty()) {
            $quoteIds = $allQuotes->pluck('id');
            $convertedCount = PurchaseOrder::whereIn('quotation_id', $quoteIds)->distinct('quotation_id')->count();
        }

        return [
            'datasets' => [
                [
                    'label' => 'Quotations',
                    'data' => [
                        $convertedCount,
                        $approvedCount,
                        $pendingCount,
                        $draftCount,
                        $rejectedCount,
                    ],
                    'backgroundColor' => [
                        '#10b981', // Converted / Won (Green)
                        '#3b82f6', // Approved (Blue)
                        '#f59e0b', // Pending (Amber)
                        '#6366f1', // Draft (Indigo)
                        '#ef4444', // Rejected / Expired (Red)
                    ],
                    'borderWidth' => 2,
                    'borderColor' => 'transparent',
                ],
            ],
            'labels' => [
                'Won / Converted (' . $convertedCount . ')',
                'Approved (' . $approvedCount . ')',
                'Under Review (' . $pendingCount . ')',
                'Draft (' . $draftCount . ')',
                'Rejected / Expired (' . $rejectedCount . ')',
            ],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'position' => 'bottom',
                    'labels' => [
                        'boxWidth' => 12,
                        'padding' => 12,
                        'font' => [
                            'size' => 11,
                        ],
                    ],
                ],
            ],
            'cutout' => '60%',
        ];
    }
}
