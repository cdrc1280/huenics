<x-filament-panels::page>
    <style>
        .fi-fo-toggle-buttons-btn-group,
        .fi-fo-toggle-buttons .fi-btn-group,
        .fi-fo-toggle-buttons div[role="group"],
        .fi-fo-toggle-buttons .fi-fo-toggle-buttons-options-container {
            display: flex !important;
            width: 100% !important;
            max-width: 100% !important;
        }
        .fi-fo-toggle-buttons-btn-group > *,
        .fi-fo-toggle-buttons .fi-btn-group > *,
        .fi-fo-toggle-buttons div[role="group"] > *,
        .fi-fo-toggle-buttons .fi-fo-toggle-buttons-options-container > * {
            flex: 1 1 0% !important;
            min-width: 0 !important;
            display: inline-flex !important;
            justify-content: center !important;
            align-items: center !important;
            text-align: center !important;
            padding-left: 0.375rem !important;
            padding-right: 0.375rem !important;
        }
        .fi-fo-toggle-buttons-btn-group > * span,
        .fi-fo-toggle-buttons .fi-btn-group > * span,
        .fi-fo-toggle-buttons div[role="group"] > * span,
        .fi-fo-toggle-buttons .fi-fo-toggle-buttons-options-container > * span {
            white-space: nowrap !important;
            overflow: hidden !important;
            text-overflow: ellipsis !important;
            font-size: 0.8125rem !important;
        }
        @media (max-width: 640px) {
            .fi-fo-toggle-buttons-btn-group > *,
            .fi-fo-toggle-buttons .fi-btn-group > *,
            .fi-fo-toggle-buttons div[role="group"] > *,
            .fi-fo-toggle-buttons .fi-fo-toggle-buttons-options-container > * {
                padding-left: 0.25rem !important;
                padding-right: 0.25rem !important;
            }
            .fi-fo-toggle-buttons-btn-group > * svg,
            .fi-fo-toggle-buttons .fi-btn-group > * svg,
            .fi-fo-toggle-buttons div[role="group"] > * svg,
            .fi-fo-toggle-buttons .fi-fo-toggle-buttons-options-container > * svg {
                width: 0.875rem !important;
                height: 0.875rem !important;
                margin-right: 0.25rem !important;
            }
        }
    </style>

    <div class="space-y-6">
        {{-- 1. Sales Performance Filters Section at the very top --}}
        {{ $this->form }}

        {{-- 2. Sales Overview KPI Stat Cards --}}
        @livewire(\App\Filament\Widgets\SalesOverviewWidget::class, $this->getWidgetData(), key('sales-overview-widget'))

        {{-- 3. Revenue & Pipeline Trend Chart --}}
        @livewire(\App\Filament\Widgets\SalesRevenueChartWidget::class, $this->getWidgetData(), key('sales-revenue-chart-widget'))

        {{-- 4. Sales Leaderboard Rankings Table --}}
        {{ $this->table }}
    </div>
</x-filament-panels::page>
