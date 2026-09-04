<x-filament-panels::page>
    <div class="space-y-8 pb-8 sm:space-y-10" style="display: flex; flex-direction: column;">
        {{-- 1. Sales Performance Filters Section at the very top --}}
        <section aria-label="Sales Performance Filters" class="w-full" style="margin-bottom: 1.5rem;">
            {{ $this->form }}
        </section>

        {{-- 2. Sales Overview KPI Stat Cards (With generous top & bottom margins) --}}
        <section aria-label="Sales Overview KPIs" class="w-full my-8" style="margin-top: 2rem; margin-bottom: 2.5rem;">
            @livewire(\App\Filament\Widgets\SalesOverviewWidget::class, $this->getWidgetData(), key('sales-overview-widget'))
        </section>

        {{-- 3. Revenue & Pipeline Trend Chart (With generous top & bottom margins) --}}
        <section aria-label="Monthly Revenue and Quotation Trend" class="w-full my-8" style="margin-top: 2.5rem; margin-bottom: 2.5rem;">
            @livewire(\App\Filament\Widgets\SalesRevenueChartWidget::class, $this->getWidgetData(), key('sales-revenue-chart-widget'))
        </section>

        {{-- 4. Sales Leaderboard Rankings Table --}}
        <section aria-label="Sales Leaderboard" class="w-full mt-8" style="margin-top: 2rem;">
            {{ $this->table }}
        </section>
    </div>
</x-filament-panels::page>
