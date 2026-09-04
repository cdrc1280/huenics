<x-filament-panels::page>
    <div class="space-y-6">
        {{-- 1. Sales Performance Filters Section at the very top --}}
        <div>
            {{ $this->form }}
        </div>

        {{-- 2. Sales Overview KPI Stat Cards --}}
        <div>
            @livewire(\App\Filament\Widgets\SalesOverviewWidget::class, $this->getWidgetData(), key('sales-overview-widget'))
        </div>

        {{-- 3. Revenue & Pipeline Trend Chart --}}
        <div>
            @livewire(\App\Filament\Widgets\SalesRevenueChartWidget::class, $this->getWidgetData(), key('sales-revenue-chart-widget'))
        </div>

        {{-- 4. Sales Leaderboard Rankings Table --}}
        <div>
            {{ $this->table }}
        </div>
    </div>
</x-filament-panels::page>
