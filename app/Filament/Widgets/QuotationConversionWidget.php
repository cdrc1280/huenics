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
        return 'Quotation Conversion & Pipeline Distribution';
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

        return "Distribution of pipeline quotes by conversion status in {$periodLabel}";
    }

    public function getData(): array
    {
        [$start, $end] = $this->resolveDateRange();
        $startStr = $start->copy()->startOfDay()->toDateTimeString();
        $endStr = $end->copy()->endOfDay()->toDateTimeString();
        $startDateOnly = $start->toDateString();
        $endDateOnly = $end->toDateString();

        $quotationQuery = Quotation::query();

        // Scope date range against quotation_date and created_at fallback
        $quotationQuery->where(function ($q) use ($startStr, $endStr, $startDateOnly, $endDateOnly) {
            $q->whereBetween('quotation_date', [$startStr, $endStr])
                ->orWhere(fn ($s) => $s->whereDate('quotation_date', '>=', $startDateOnly)->whereDate('quotation_date', '<=', $endDateOnly))
                ->orWhereBetween('created_at', [$startStr, $endStr]);
        });

        // Sales executive & inhouse filtering
        if ($this->filterInhouse) {
            $quotationQuery->where(fn ($q) => $q->whereHas('salesAgent', fn ($u) => $u->where('is_owner', true))->orWhereNull('sales_agent_id'));
        } elseif ($this->selectedAgentId) {
            $quotationQuery->where('sales_agent_id', $this->selectedAgentId);
        }

        $allQuotes = $quotationQuery->get(['id', 'status', 'total_amount', 'valid_until']);

        if ($allQuotes->isEmpty()) {
            return [
                'datasets' => [
                    [
                        'label' => 'Quotations',
                        'data' => [0],
                        'backgroundColor' => ['#94a3b8'],
                        'borderWidth' => 2,
                        'borderColor' => 'transparent',
                    ],
                ],
                'labels' => ['No Pipeline Quotations in Period (0)'],
            ];
        }

        // Identify quotations that have won purchase orders
        $wonQuoteIds = PurchaseOrder::query()
            ->whereIn('quotation_id', $allQuotes->pluck('id'))
            ->whereNotIn('status', [PurchaseOrder::STATUS_CANCELLED, PurchaseOrder::STATUS_REJECTED])
            ->pluck('quotation_id')
            ->flip()
            ->all();

        $wonCount = 0;
        $approvedCount = 0;
        $reviewedCount = 0;
        $pendingCount = 0;
        $rejectedCount = 0;

        foreach ($allQuotes as $quote) {
            $status = strtolower((string) $quote->status);

            if (isset($wonQuoteIds[$quote->id]) || in_array($status, ['converted', 'converted_to_po'], true)) {
                $wonCount++;
            } elseif (in_array($status, ['approved'], true)) {
                $approvedCount++;
            } elseif (in_array($status, ['reviewed', 'under_review'], true)) {
                $reviewedCount++;
            } elseif (in_array($status, ['rejected', 'expired'], true)) {
                $rejectedCount++;
            } else {
                $pendingCount++;
            }
        }

        return [
            'datasets' => [
                [
                    'label' => 'Quotations',
                    'data' => [
                        $wonCount,
                        $approvedCount,
                        $reviewedCount,
                        $pendingCount,
                        $rejectedCount,
                    ],
                    'backgroundColor' => [
                        '#10b981', // Converted / Won (Green)
                        '#3b82f6', // Approved (Blue)
                        '#06b6d4', // Reviewed (Cyan)
                        '#f59e0b', // Pending / Draft (Amber)
                        '#ef4444', // Rejected / Expired (Red)
                    ],
                    'borderWidth' => 2,
                    'borderColor' => 'transparent',
                ],
            ],
            'labels' => [
                "Won / Converted ({$wonCount})",
                "Approved ({$approvedCount})",
                "Reviewed ({$reviewedCount})",
                "Pending ({$pendingCount})",
                "Rejected ({$rejectedCount})",
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
