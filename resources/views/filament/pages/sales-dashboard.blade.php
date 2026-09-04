<x-filament-panels::page>
    <div class="space-y-6 pb-6" style="display: flex; flex-direction: column; gap: 1.5rem;">
        {{-- 1. Sales Performance Filters Section --}}
        <section aria-label="Sales Performance Filters" class="w-full" style="margin-bottom: 0;">
            {{ $this->form }}
        </section>

        {{-- 2. Sales Overview KPI Stat Cards --}}
        <section aria-label="Sales Overview KPIs" class="w-full" style="margin-bottom: 0;">
            @livewire(\App\Filament\Widgets\SalesOverviewWidget::class, $this->getWidgetData(), key('sales-overview-widget'))
        </section>

        {{-- 3. Revenue & Pipeline Trend Chart --}}
        <section aria-label="Monthly Revenue and Quotation Trend" class="w-full" style="margin-bottom: 0;">
            @livewire(\App\Filament\Widgets\SalesRevenueChartWidget::class, $this->getWidgetData(), key('sales-revenue-chart-widget'))
        </section>

        {{-- 4. Analytics Deep Dive: Conversion Funnel & Top Revenue Products --}}
        <section aria-label="Sales Conversion and Product Performance" class="w-full" style="margin-bottom: 0;">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 1.5rem;">
                <div class="w-full">
                    @livewire(\App\Filament\Widgets\QuotationConversionWidget::class, $this->getWidgetData(), key('quotation-conversion-widget'))
                </div>
                <div class="w-full">
                    @livewire(\App\Filament\Widgets\TopSellingProductsWidget::class, $this->getWidgetData(), key('top-selling-products-widget'))
                </div>
            </div>
        </section>

        {{-- 5. Sales Leaderboard Rankings Table --}}
        <section aria-label="Sales Leaderboard" class="w-full" style="margin-bottom: 0;">
            {{ $this->table }}
        </section>
    </div>
</x-filament-panels::page>
