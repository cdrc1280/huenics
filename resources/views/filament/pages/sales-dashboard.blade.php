<x-filament-panels::page>
    <div class="space-y-8 sm:space-y-10 pb-8">
        {{-- 1. Sales Performance Filters Section at the very top --}}
        <section aria-label="Sales Performance Filters" class="w-full">
            {{ $this->form }}
        </section>

        {{-- 2. Sales Overview KPI Stat Cards --}}
        <section aria-label="Sales Overview KPIs" class="w-full">
            @livewire(\App\Filament\Widgets\SalesOverviewWidget::class, $this->getWidgetData(), key('sales-overview-widget'))
        </section>

        {{-- 3. Revenue & Pipeline Trend Chart --}}
        <section aria-label="Monthly Revenue and Quotation Trend" class="w-full">
            @livewire(\App\Filament\Widgets\SalesRevenueChartWidget::class, $this->getWidgetData(), key('sales-revenue-chart-widget'))
        </section>

        {{-- 4. Sales Leaderboard Rankings Table --}}
        <section aria-label="Sales Leaderboard" class="w-full">
            {{ $this->table }}
        </section>
    </div>
</x-filament-panels::page>
