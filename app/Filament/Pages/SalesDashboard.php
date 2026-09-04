<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\SalesOverviewWidget;
use App\Filament\Widgets\SalesRevenueChartWidget;
use App\Models\PurchaseOrder;
use App\Models\User;
use App\Services\ExportExecutiveReportPdf;
use BackedEnum;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use UnitEnum;

class SalesDashboard extends Page implements HasTable, HasForms
{
    use InteractsWithTable;
    use InteractsWithForms;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-chart-bar-square';
    protected static UnitEnum|string|null $navigationGroup = 'Reports & Analytics';
    protected static ?string $navigationLabel = 'Sales Analytics & Leaderboard';
    protected static ?string $title = 'Sales & Performance Dashboard';
    protected string $view = 'filament.pages.sales-dashboard';
    protected static ?int $navigationSort = 1;

    public ?array $filterData = [];

    public static function canAccess(): bool
    {
        return auth()->user()?->canViewSalesReports() ?? false;
    }

    public function mount(): void
    {
        $user = auth()->user();
        $defaultAgentId = ($user && $user->isSalesExecutive()) ? $user->id : null;

        $this->form->fill([
            'selectedAgentId' => $defaultAgentId,
            'filterInhouse' => false,
            'periodType' => 'month',
            'selectedDate' => now()->toDateString(),
            'selectedWeek' => (int) now()->weekOfYear,
            'selectedMonth' => (int) now()->month,
            'selectedYear' => (int) now()->year,
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('filterData')
            ->components([
                Section::make('Sales Performance Filters')
                    ->description('Filter leaderboard rankings and revenue KPIs by sales executive, inhouse ownership, and timeframe granularity.')
                    ->icon('heroicon-o-adjustments-horizontal')
                    ->collapsible()
                    ->schema([
                        Grid::make([
                            'default' => 1,
                            'sm' => 12,
                            'md' => 12,
                            'lg' => 12,
                        ])->schema([
                                    Select::make('selectedAgentId')
                                        ->label('Filter by S.E.')
                                        ->options(function ($get) {
                                            $q = User::whereIn('role', [
                                                User::ROLE_SALES_EXECUTIVE,
                                                User::ROLE_ADMIN,
                                                User::ROLE_OPERATIONS_MANAGER,
                                                User::ROLE_CEO,
                                            ]);
                                            if ((bool) $get('filterInhouse')) {
                                                $q->where('is_owner', true);
                                            }
                                            return $q->pluck('name', 'id');
                                        })
                                        ->placeholder('All Sales Executives')
                                        ->searchable()
                                        ->live()
                                        ->disabled(fn($get) => (bool) $get('filterInhouse'))
                                        ->columnSpan(['default' => 12, 'sm' => 7, 'md' => 7, 'lg' => 7]),

                                    Toggle::make('filterInhouse')
                                        ->label('Inhouse (Owner)')
                                        ->helperText('Filter by owner accounts')
                                        ->inline(false)
                                        ->live()
                                        ->afterStateUpdated(function ($state, callable $set) {
                                            if ($state) {
                                                $set('selectedAgentId', null);
                                            }
                                        })
                                        ->columnSpan(['default' => 12, 'sm' => 5, 'md' => 5, 'lg' => 5]),

                                    ToggleButtons::make('periodType')
                                        ->label('Filter Granularity')
                                        ->options([
                                            'days' => 'Days',
                                            'weeks' => 'Weeks',
                                            'month' => 'Month',
                                            'years' => 'Years',
                                        ])
                                        ->icons([
                                            'days' => 'heroicon-m-calendar',
                                            'weeks' => 'heroicon-m-calendar-days',
                                            'month' => 'heroicon-m-chart-bar',
                                            'years' => 'heroicon-m-presentation-chart-line',
                                        ])
                                        ->colors([
                                            'days' => 'info',
                                            'weeks' => 'warning',
                                            'month' => 'primary',
                                            'years' => 'success',
                                        ])
                                        ->default('month')
                                        ->grouped()
                                        ->live()
                                        ->columnSpan(['default' => 12, 'sm' => 12, 'md' => 6, 'lg' => 6]),

                                    DatePicker::make('selectedDate')
                                        ->label('Select Day')
                                        ->default(now()->toDateString())
                                        ->visible(fn($get) => $get('periodType') === 'days')
                                        ->live()
                                        ->columnSpan(['default' => 12, 'sm' => 12, 'md' => 6, 'lg' => 6]),

                                    Select::make('selectedWeek')
                                        ->label('Select Week')
                                        ->options(function ($get) {
                                            $year = (int) ($get('selectedYear') ?: now()->year);
                                            $weeks = [];
                                            for ($w = 1; $w <= 52; $w++) {
                                                $wStart = Carbon::now()->setISODate($year, $w)->startOfWeek();
                                                $wEnd = $wStart->copy()->endOfWeek();
                                                $weeks[$w] = "Week {$w} ({$wStart->format('M d')} - {$wEnd->format('M d')})";
                                            }
                                            return $weeks;
                                        })
                                        ->default((int) now()->weekOfYear)
                                        ->visible(fn($get) => $get('periodType') === 'weeks')
                                        ->live()
                                        ->columnSpan(['default' => 12, 'sm' => 8, 'md' => 4, 'lg' => 4]),

                                    Select::make('selectedMonth')
                                        ->label('Month')
                                        ->options([
                                            1 => 'January',
                                            2 => 'February',
                                            3 => 'March',
                                            4 => 'April',
                                            5 => 'May',
                                            6 => 'June',
                                            7 => 'July',
                                            8 => 'August',
                                            9 => 'September',
                                            10 => 'October',
                                            11 => 'November',
                                            12 => 'December',
                                        ])
                                        ->default((int) now()->month)
                                        ->visible(fn($get) => $get('periodType') === 'month')
                                        ->live()
                                        ->columnSpan(['default' => 12, 'sm' => 8, 'md' => 4, 'lg' => 4]),

                                    Select::make('selectedYear')
                                        ->label('Year')
                                        ->options(function () {
                                            $years = [];
                                            for ($y = now()->year - 3; $y <= now()->year + 2; $y++) {
                                                $years[$y] = (string) $y;
                                            }
                                            return $years;
                                        })
                                        ->default((int) now()->year)
                                        ->visible(fn($get) => in_array($get('periodType'), ['weeks', 'month', 'years']))
                                        ->live()
                                        ->columnSpan(fn($get) => $get('periodType') === 'years'
                                            ? ['default' => 12, 'sm' => 12, 'md' => 6, 'lg' => 6]
                                            : ['default' => 12, 'sm' => 4, 'md' => 2, 'lg' => 2]),
                                ]),
                    ]),
            ]);
    }

    public function getDateRange(): array
    {
        $periodType = $this->filterData['periodType'] ?? 'month';
        $year = (int) ($this->filterData['selectedYear'] ?? now()->year);

        switch ($periodType) {
            case 'days':
                $date = !empty($this->filterData['selectedDate'])
                    ? Carbon::parse($this->filterData['selectedDate'])->startOfDay()
                    : now()->startOfDay();
                return [
                    $date,
                    $date->copy()->endOfDay(),
                    $date->format('F d, Y'),
                ];

            case 'weeks':
                $week = (int) ($this->filterData['selectedWeek'] ?? now()->weekOfYear);
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
                $month = (int) ($this->filterData['selectedMonth'] ?? now()->month);
                $start = Carbon::create($year, $month, 1)->startOfMonth();
                $end = $start->copy()->endOfMonth();
                return [
                    $start,
                    $end,
                    $start->format('F Y'),
                ];
        }
    }

    public function getWidgetData(): array
    {
        return [
            'agentId' => $this->filterData['selectedAgentId'] ?? null,
            'selectedAgentId' => $this->filterData['selectedAgentId'] ?? null,
            'isInhouse' => (bool) ($this->filterData['filterInhouse'] ?? false),
            'filterInhouse' => (bool) ($this->filterData['filterInhouse'] ?? false),
            'periodType' => $this->filterData['periodType'] ?? 'month',
            'selectedDate' => $this->filterData['selectedDate'] ?? now()->toDateString(),
            'selectedWeek' => (int) ($this->filterData['selectedWeek'] ?? now()->weekOfYear),
            'selectedMonth' => (int) ($this->filterData['selectedMonth'] ?? now()->month),
            'selectedYear' => (int) ($this->filterData['selectedYear'] ?? now()->year),
        ];
    }

    public function updatedFilterData(): void
    {
        $this->flushCachedTableRecords();
        $this->table = $this->table($this->makeTable());
        $this->dispatch('salesFilterUpdated', filterData: $this->filterData);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('downloadExecutiveReport')
                ->label('Download Executive Report (PDF)')
                ->icon('heroicon-o-document-arrow-down')
                ->color('primary')
                ->tooltip('Download formal corporate executive sales & performance summary PDF')
                ->action(function (ExportExecutiveReportPdf $service) {
                    return $service->downloadResponse($this->filterData);
                }),

            Action::make('exportLeaderboardCsv')
                ->label('Export Leaderboard (CSV)')
                ->icon('heroicon-o-table-cells')
                ->color('gray')
                ->tooltip('Download sales executive rankings and KPIs as CSV spreadsheet')
                ->action(function (ExportExecutiveReportPdf $service) {
                    $data = $service->buildReportData($this->filterData);
                    $periodLabel = preg_replace('/[^a-zA-Z0-9_-]/', '_', $data['periodLabel']);
                    $filename = 'sales-leaderboard-' . strtolower($periodLabel) . '-' . date('Ymd') . '.csv';

                    return response()->streamDownload(function () use ($data) {
                        $handle = fopen('php://output', 'w');
                        fputs($handle, "\xEF\xBB\xBF");

                        fputcsv($handle, ['Rank', 'Sales Executive', 'Account Type', 'Sales Achieved (PHP)', 'Realized Profit (PHP)', 'Quotations Count', 'POs Won', 'Win Rate']);

                        foreach ($data['leaderboard'] as $idx => $row) {
                            fputcsv($handle, [
                                $idx + 1,
                                $row['name'],
                                $row['role_label'],
                                number_format($row['sales_achieved'], 2, '.', ''),
                                number_format($row['profit'], 2, '.', ''),
                                $row['quotations'],
                                $row['pos'],
                                $row['win_rate'],
                            ]);
                        }

                        fputcsv($handle, []);
                        fputcsv($handle, [
                            'TOTALS',
                            '',
                            '',
                            number_format($data['kpis']['total_sales'], 2, '.', ''),
                            number_format($data['kpis']['total_profit'], 2, '.', ''),
                            $data['kpis']['total_quotations'],
                            $data['kpis']['total_pos'],
                            $data['kpis']['win_rate'] . '%',
                        ]);

                        fclose($handle);
                    }, $filename, [
                        'Content-Type' => 'text/csv',
                    ]);
                }),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [];
    }

    public function table(Table $table): Table
    {
        [$startDate, $endDate, $periodLabel] = $this->getDateRange();
        $startStr = $startDate->copy()->startOfDay()->toDateTimeString();
        $endStr = $endDate->copy()->endOfDay()->toDateTimeString();
        $startDateOnly = $startDate->toDateString();
        $endDateOnly = $endDate->toDateString();

        $selectedAgentId = $this->filterData['selectedAgentId'] ?? null;
        $filterInhouse = (bool) ($this->filterData['filterInhouse'] ?? false);

        $poDateScope = function ($q) use ($startStr, $endStr, $startDateOnly, $endDateOnly) {
            $q->where(function ($sub) use ($startStr, $endStr, $startDateOnly, $endDateOnly) {
                $sub->whereBetween('order_date', [$startStr, $endStr])
                    ->orWhere(fn($s) => $s->whereDate('order_date', '>=', $startDateOnly)->whereDate('order_date', '<=', $endDateOnly))
                    ->orWhereBetween('actual_delivery_date', [$startDateOnly, $endDateOnly])
                    ->orWhereBetween('completed_at', [$startStr, $endStr])
                    ->orWhereBetween('created_at', [$startStr, $endStr]);
            });
        };

        $qDateScope = function ($q) use ($startStr, $endStr, $startDateOnly, $endDateOnly) {
            $q->where(function ($sub) use ($startStr, $endStr, $startDateOnly, $endDateOnly) {
                $sub->whereBetween('quotation_date', [$startStr, $endStr])
                    ->orWhere(fn($s) => $s->whereDate('quotation_date', '>=', $startDateOnly)->whereDate('quotation_date', '<=', $endDateOnly))
                    ->orWhereBetween('created_at', [$startStr, $endStr]);
            });
        };

        $query = User::query()
            ->whereIn('role', [
                User::ROLE_SALES_EXECUTIVE,
                User::ROLE_ADMIN,
                User::ROLE_OPERATIONS_MANAGER,
                User::ROLE_CEO,
            ])
            ->withCount([
                'quotations as period_quotations' => $qDateScope,
                'purchaseOrders as period_pos' => fn($q) => $q->whereNotIn('status', [PurchaseOrder::STATUS_CANCELLED, PurchaseOrder::STATUS_REJECTED])->where($poDateScope),
            ])
            ->withSum([
                'purchaseOrders as period_achieved' => fn($q) => $q->whereNotIn('status', [PurchaseOrder::STATUS_CANCELLED, PurchaseOrder::STATUS_REJECTED])->where($poDateScope),
            ], 'order_amount')
            ->withSum([
                'purchaseOrders as period_profit' => fn($q) => $q->whereNotIn('status', [PurchaseOrder::STATUS_CANCELLED, PurchaseOrder::STATUS_REJECTED])->where($poDateScope),
            ], 'realized_profit');

        if ($filterInhouse) {
            $query->where('is_owner', true);
        } elseif ($selectedAgentId) {
            $query->where('id', $selectedAgentId);
        }

        return $table
            ->query($query->orderByDesc('period_achieved'))
            ->columns([
                TextColumn::make('rank')
                    ->label('#')
                    ->state(fn($record, $rowLoop) => $rowLoop->iteration)
                    ->tooltip(fn($record, $rowLoop): string => "Rank #{$rowLoop->iteration} sales performer"),

                TextColumn::make('name')
                    ->label('Sales Agent')
                    ->searchable()
                    ->weight('bold')
                    ->description(fn(User $record): string => $record->is_owner ? 'Inhouse (Owner)' : ucfirst(str_replace('_', ' ', $record->role)))
                    ->tooltip(fn(User $record): string => "Sales Executive: {$record->name} ({$record->email})"),

                TextColumn::make('period_achieved')
                    ->label('Sales Achieved')
                    ->money('PHP')
                    ->sortable()
                    ->state(fn(User $record) => (float) ($record->period_achieved ?? 0))
                    ->color(fn($state) => (float) $state > 0 ? 'success' : 'gray')
                    ->weight('bold')
                    ->tooltip(fn(User $record): string => "Confirmed converted PO sales during {$periodLabel}: ₱" . number_format((float) ($record->period_achieved ?? 0), 2)),

                TextColumn::make('period_profit')
                    ->label('Realized Profit')
                    ->money('PHP')
                    ->sortable()
                    ->state(fn(User $record) => (float) ($record->period_profit ?? 0))
                    ->color(fn($state) => (float) $state > 0 ? 'primary' : 'gray')
                    ->tooltip(fn(User $record): string => "Realized net gross profit during {$periodLabel}: ₱" . number_format((float) ($record->period_profit ?? 0), 2)),

                TextColumn::make('period_quotations')
                    ->label('Quotations')
                    ->sortable()
                    ->alignCenter()
                    ->tooltip(fn(User $record): string => "Total customer quotations created during {$periodLabel}"),

                TextColumn::make('period_pos')
                    ->label('POs Won')
                    ->sortable()
                    ->alignCenter()
                    ->tooltip(fn(User $record): string => "Total quotations converted into Purchase Orders during {$periodLabel}"),

                TextColumn::make('win_rate')
                    ->label('Win Rate')
                    ->state(function (User $record): string {
                        $quotes = (int) ($record->period_quotations ?? 0);
                        $pos = (int) ($record->period_pos ?? 0);
                        return ($quotes > 0 ? round(($pos / $quotes) * 100, 1) : 0) . '%';
                    })
                    ->badge()
                    ->color(function (User $record): string {
                        $quotes = (int) ($record->period_quotations ?? 0);
                        $pos = (int) ($record->period_pos ?? 0);
                        $rate = $quotes > 0 ? ($pos / $quotes) * 100 : 0;
                        return $rate >= 50 ? 'success' : ($rate > 0 ? 'warning' : 'gray');
                    })
                    ->tooltip(fn(User $record): string => "Conversion win rate efficiency during {$periodLabel}"),
            ])
            ->heading("Sales Leaderboard — {$periodLabel}")
            ->emptyStateHeading('No sales activity found for this period')
            ->emptyStateDescription('Quotations and POs created in this timeframe will populate the leaderboard rankings.')
            ->emptyStateIcon('heroicon-o-chart-bar');
    }
}
