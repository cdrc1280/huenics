<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\SalesOverviewWidget;
use App\Models\PurchaseOrder;
use App\Models\Quotation;
use App\Models\SalesQuota;
use App\Models\User;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Illuminate\Database\Eloquent\Builder;

class SalesDashboard extends Page implements HasTable, HasForms
{
    use InteractsWithTable;
    use InteractsWithForms;

    protected static \BackedEnum|string|null $navigationIcon  = 'heroicon-o-chart-bar-square';
    protected static \UnitEnum|string|null   $navigationGroup = 'Reports & Analytics';
    protected static ?string $navigationLabel = 'Sales Analytics & Leaderboard';
    protected static ?string $title           = 'Sales & Performance Dashboard';
    protected string $view                    = 'filament.pages.sales-dashboard';
    protected static ?int $navigationSort     = 1;

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
        $this->selectedMonth = now()->month;
        $this->selectedYear  = now()->year;
        $user = auth()->user();
        if ($user && $user->isSalesExecutive()) {
            $this->selectedAgentId = $user->id;
        }
    }

    protected function getHeaderWidgets(): array
    {
        return [
            SalesOverviewWidget::make([
                'agentId' => $this->selectedAgentId,
                'isInhouse' => $this->filterInhouse,
                'month' => $this->selectedMonth,
                'year' => $this->selectedYear,
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
        $month = $this->selectedMonth;
        $year  = $this->selectedYear;

        $query = SalesQuota::query()
            ->with('user')
            ->where('month', $month)
            ->where('year', $year);

        if ($this->filterInhouse) {
            $query->whereHas('user', fn($q) => $q->where('is_owner', true));
        } elseif ($this->selectedAgentId) {
            $query->where('user_id', $this->selectedAgentId);
        }

        return $table
            ->query($query->orderByDesc('achieved_amount'))
            ->columns([
                Tables\Columns\TextColumn::make('rank')
                    ->label('#')
                    ->state(function ($record, $rowLoop) {
                        return $rowLoop->iteration;
                    })
                    ->tooltip(fn ($record, $rowLoop): string => "Rank #{$rowLoop->iteration} sales performer"),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Sales Agent')
                    ->searchable()
                    ->weight('bold')
                    ->tooltip(fn (SalesQuota $record): string => "Sales Executive: " . ($record->user?->name ?? 'Unknown')),

                Tables\Columns\TextColumn::make('target_amount')
                    ->label('Monthly Target')
                    ->money('PHP')
                    ->tooltip(fn (SalesQuota $record): string => "Target quota goal for " . now()->format('F Y') . ": ₱" . number_format((float) $record->target_amount, 2)),

                Tables\Columns\TextColumn::make('achieved_amount')
                    ->label('Achieved')
                    ->money('PHP')
                    ->color(fn ($record) => $record->achieved_amount >= $record->target_amount ? 'success' : 'warning')
                    ->tooltip(fn (SalesQuota $record): string => "Confirmed converted PO sales: ₱" . number_format((float) $record->achieved_amount, 2)),

                Tables\Columns\TextColumn::make('quota_percentage')
                    ->label('Quota %')
                    ->state(fn (SalesQuota $record): string => $record->quota_percentage . '%')
                    ->color(fn (SalesQuota $record): string => $record->quota_percentage >= 100 ? 'success' : ($record->quota_percentage >= 50 ? 'warning' : 'danger'))
                    ->tooltip(fn (SalesQuota $record): string => "Target achievement progress: {$record->quota_percentage}%"),

                Tables\Columns\TextColumn::make('total_quotations')
                    ->label('Quotations')
                    ->tooltip('Total customer quotations created this month'),

                Tables\Columns\TextColumn::make('converted_pos')
                    ->label('POs Won')
                    ->tooltip('Total quotations successfully converted into Purchase Orders'),

                Tables\Columns\TextColumn::make('conversion_rate')
                    ->label('Win Rate')
                    ->state(fn (SalesQuota $record): string => $record->conversion_rate . '%')
                    ->tooltip(fn (SalesQuota $record): string => "Conversion efficiency: {$record->conversion_rate}% of quotations won"),
            ])

            ->heading('Sales Leaderboard — ' . \Carbon\Carbon::createFromDate($year, $month)->format('F Y'))
            ->emptyStateHeading('No sales data for this period')
            ->emptyStateDescription('Quotations and POs will appear here once agents start creating records.')
            ->emptyStateIcon('heroicon-o-chart-bar');
    }
}
