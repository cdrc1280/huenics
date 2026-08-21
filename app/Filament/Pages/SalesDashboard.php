<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\SalesOverviewWidget;
use App\Models\PurchaseOrder;
use App\Models\Quotation;
use App\Models\User;
use Carbon\Carbon;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;
use BackedEnum;

class SalesDashboard extends Page implements HasTable, HasForms
{
    use InteractsWithTable;
    use InteractsWithForms;

    protected static BackedEnum|string|null $navigationIcon  = 'heroicon-o-chart-bar-square';
    protected static UnitEnum|string|null   $navigationGroup = 'Reports & Analytics';
    protected static ?string $navigationLabel = 'Sales Analytics & Leaderboard';
    protected static ?string $title           = 'Sales & Performance Dashboard';
    protected string $view                    = 'filament.pages.sales-dashboard';
    protected static ?int $navigationSort     = 1;

    public string $periodType = 'month'; // 'days', 'weeks', 'month', 'years'
    public ?string $selectedDate = null;
    public ?int $selectedWeek = null;
    public int $selectedMonth;
    public int $selectedYear;
    public ?int $selectedAgentId = null;
    public bool $filterInhouse = false;

    public static function canAccess(): bool
    {
        return auth()->user()?->canViewSalesReports() ?? false;
    }

    public function mount(): void
    {
        $this->selectedDate  = now()->toDateString();
        $this->selectedWeek  = now()->weekOfYear;
        $this->selectedMonth = now()->month;
        $this->selectedYear  = now()->year;

        $user = auth()->user();
        if ($user && $user->isSalesExecutive()) {
            $this->selectedAgentId = $user->id;
        }
    }

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

    public function setPeriodType(string $type): void
    {
        $this->periodType = $type;
    }

    public function setToday(): void
    {
        $this->periodType = 'days';
        $this->selectedDate = now()->toDateString();
    }

    public function setYesterday(): void
    {
        $this->periodType = 'days';
        $this->selectedDate = now()->subDay()->toDateString();
    }

    public function setThisWeek(): void
    {
        $this->periodType = 'weeks';
        $this->selectedYear = now()->year;
        $this->selectedWeek = now()->weekOfYear;
    }

    public function setLastWeek(): void
    {
        $this->periodType = 'weeks';
        $lastWeek = now()->subWeek();
        $this->selectedYear = $lastWeek->year;
        $this->selectedWeek = $lastWeek->weekOfYear;
    }

    public function setThisMonth(): void
    {
        $this->periodType = 'month';
        $this->selectedMonth = now()->month;
        $this->selectedYear = now()->year;
    }

    public function setThisYear(): void
    {
        $this->periodType = 'years';
        $this->selectedYear = now()->year;
    }

    protected function getHeaderWidgets(): array
    {
        return [
            SalesOverviewWidget::make([
                'agentId'       => $this->selectedAgentId,
                'isInhouse'     => $this->filterInhouse,
                'periodType'    => $this->periodType,
                'selectedDate'  => $this->selectedDate,
                'selectedWeek'  => $this->selectedWeek,
                'selectedMonth' => $this->selectedMonth,
                'selectedYear'  => $this->selectedYear,
            ]),
        ];
    }

    public function getSalesAgentsProperty(): array
    {
        return User::whereIn('role', [
            User::ROLE_SALES_EXECUTIVE,
            User::ROLE_ADMIN,
            User::ROLE_OPERATIONS_MANAGER,
        ])->pluck('name', 'id')->toArray();
    }

    public function table(Table $table): Table
    {
        [$startDate, $endDate, $periodLabel] = $this->getDateRange();
        $startStr = $startDate->toDateString();
        $endStr = $endDate->toDateString();

        $query = User::query()
            ->whereIn('role', [
                User::ROLE_SALES_EXECUTIVE,
                User::ROLE_ADMIN,
                User::ROLE_OPERATIONS_MANAGER,
            ])
            ->withCount([
                'quotations as period_quotations' => fn($q) => $q->whereBetween('quotation_date', [$startStr, $endStr]),
                'purchaseOrders as period_pos' => fn($q) => $q->whereBetween('order_date', [$startStr, $endStr]),
            ])
            ->withSum([
                'purchaseOrders as period_achieved' => fn($q) => $q->whereBetween('order_date', [$startStr, $endStr]),
            ], 'order_amount')
            ->withSum([
                'purchaseOrders as period_profit' => fn($q) => $q->whereBetween('order_date', [$startStr, $endStr]),
            ], 'realized_profit');

        if ($this->filterInhouse) {
            $query->where('is_owner', true);
        } elseif ($this->selectedAgentId) {
            $query->where('id', $this->selectedAgentId);
        }

        return $table
            ->query($query->orderByDesc('period_achieved'))
            ->columns([
                Tables\Columns\TextColumn::make('rank')
                    ->label('#')
                    ->state(fn($record, $rowLoop) => $rowLoop->iteration)
                    ->tooltip(fn($record, $rowLoop): string => "Rank #{$rowLoop->iteration} sales performer"),

                Tables\Columns\TextColumn::make('name')
                    ->label('Sales Agent')
                    ->searchable()
                    ->weight('bold')
                    ->description(fn(User $record): string => $record->is_owner ? 'Inhouse (Owner)' : ucfirst(str_replace('_', ' ', $record->role)))
                    ->tooltip(fn(User $record): string => "Sales Executive: {$record->name} ({$record->email})"),

                Tables\Columns\TextColumn::make('period_achieved')
                    ->label('Sales Achieved')
                    ->money('PHP')
                    ->sortable()
                    ->state(fn(User $record) => (float) ($record->period_achieved ?? 0))
                    ->color(fn($state) => (float) $state > 0 ? 'success' : 'gray')
                    ->weight('bold')
                    ->tooltip(fn(User $record): string => "Confirmed converted PO sales during {$periodLabel}: ₱" . number_format((float) ($record->period_achieved ?? 0), 2)),

                Tables\Columns\TextColumn::make('period_profit')
                    ->label('Realized Profit')
                    ->money('PHP')
                    ->sortable()
                    ->state(fn(User $record) => (float) ($record->period_profit ?? 0))
                    ->color(fn($state) => (float) $state > 0 ? 'primary' : 'gray')
                    ->tooltip(fn(User $record): string => "Realized net gross profit during {$periodLabel}: ₱" . number_format((float) ($record->period_profit ?? 0), 2)),

                Tables\Columns\TextColumn::make('period_quotations')
                    ->label('Quotations')
                    ->sortable()
                    ->alignCenter()
                    ->tooltip(fn(User $record): string => "Total customer quotations created during {$periodLabel}"),

                Tables\Columns\TextColumn::make('period_pos')
                    ->label('POs Won')
                    ->sortable()
                    ->alignCenter()
                    ->tooltip(fn(User $record): string => "Total quotations converted into Purchase Orders during {$periodLabel}"),

                Tables\Columns\TextColumn::make('win_rate')
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
