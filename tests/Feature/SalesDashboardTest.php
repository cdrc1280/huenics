<?php

namespace Tests\Feature;

use App\Filament\Pages\SalesDashboard;
use App\Filament\Widgets\SalesOverviewWidget;
use App\Models\PurchaseOrder;
use App\Models\Quotation;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SalesDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $salesRep;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'is_owner' => true,
            'name' => 'Admin User',
        ]);

        $this->salesRep = User::factory()->create([
            'role' => User::ROLE_SALES_EXECUTIVE,
            'is_owner' => false,
            'name' => 'Sales Executive One',
        ]);
    }

    public function test_sales_dashboard_page_can_be_rendered(): void
    {
        $this->actingAs($this->admin);

        Livewire::test(SalesDashboard::class)
            ->assertSuccessful()
            ->assertSee('Sales Performance Filters')
            ->assertSee('Sales Leaderboard');
    }

    public function test_sales_dashboard_fetches_correct_data_when_filtering_by_month(): void
    {
        $this->actingAs($this->admin);

        Quotation::create([
            'quotation_number' => 'HISI-Q-TEST-01',
            'quotation_date' => '2026-01-15',
            'sales_agent_id' => $this->salesRep->id,
            'customer_name' => 'Test Customer A',
            'total_amount' => 50000.00,
            'status' => Quotation::STATUS_CONVERTED,
        ]);

        PurchaseOrder::create([
            'po_number' => 'PO-TEST-01',
            'order_date' => '2026-01-20',
            'sales_agent_id' => $this->salesRep->id,
            'customer_name' => 'Test Customer A',
            'order_amount' => 50000.00,
            'realized_profit' => 15000.00,
            'status' => PurchaseOrder::STATUS_APPROVED,
            'delivery_status' => PurchaseOrder::DELIVERY_PENDING,
            'is_completed' => false,
            'is_conforme_po' => false,
        ]);

        Livewire::test(SalesDashboard::class)
            ->set('filterData.periodType', 'month')
            ->set('filterData.selectedYear', 2026)
            ->set('filterData.selectedMonth', 1)
            ->assertSuccessful()
            ->assertSee('Sales Executive One')
            ->assertSee('₱50,000.00');

        Livewire::test(SalesOverviewWidget::class, [
            'periodType' => 'month',
            'selectedYear' => 2026,
            'selectedMonth' => 1,
            'agentId' => $this->salesRep->id,
        ])
            ->assertSuccessful()
            ->assertSee('₱50,000.00')
            ->assertSee('100%');
    }

    public function test_sales_dashboard_fetches_correct_data_when_filtering_by_year(): void
    {
        $this->actingAs($this->admin);

        Quotation::create([
            'quotation_number' => 'HISI-Q-TEST-02',
            'quotation_date' => '2026-06-10',
            'sales_agent_id' => $this->salesRep->id,
            'customer_name' => 'Test Customer B',
            'total_amount' => 120000.00,
            'status' => Quotation::STATUS_APPROVED,
        ]);

        PurchaseOrder::create([
            'po_number' => 'PO-TEST-02',
            'order_date' => '2026-06-15',
            'sales_agent_id' => $this->salesRep->id,
            'customer_name' => 'Test Customer B',
            'order_amount' => 120000.00,
            'realized_profit' => 36000.00,
            'status' => PurchaseOrder::STATUS_DELIVERED,
            'delivery_status' => PurchaseOrder::DELIVERY_DELIVERED,
            'is_completed' => true,
            'is_conforme_po' => false,
        ]);

        Livewire::test(SalesDashboard::class)
            ->set('filterData.periodType', 'years')
            ->set('filterData.selectedYear', 2026)
            ->assertSuccessful()
            ->assertSee('₱120,000.00');

        Livewire::test(SalesOverviewWidget::class, [
            'periodType' => 'years',
            'selectedYear' => 2026,
        ])
            ->assertSuccessful()
            ->assertSee('₱120,000.00')
            ->assertSee('₱36,000.00');
    }

    public function test_sales_dashboard_fetches_correct_data_when_filtering_by_days(): void
    {
        $this->actingAs($this->admin);

        PurchaseOrder::create([
            'po_number' => 'PO-TEST-DAY',
            'order_date' => '2026-03-12',
            'sales_agent_id' => $this->salesRep->id,
            'customer_name' => 'Test Customer C',
            'order_amount' => 88000.00,
            'realized_profit' => 20000.00,
            'status' => PurchaseOrder::STATUS_APPROVED,
            'delivery_status' => PurchaseOrder::DELIVERY_PENDING,
            'is_completed' => false,
            'is_conforme_po' => false,
        ]);

        Livewire::test(SalesDashboard::class)
            ->set('filterData.periodType', 'days')
            ->set('filterData.selectedDate', '2026-03-12')
            ->assertSuccessful()
            ->assertSee('₱88,000.00');

        Livewire::test(SalesOverviewWidget::class, [
            'periodType' => 'days',
            'selectedDate' => '2026-03-12',
        ])
            ->assertSuccessful()
            ->assertSee('₱88,000.00');
    }

    public function test_sales_dashboard_fetches_correct_data_when_filtering_by_weeks(): void
    {
        $this->actingAs($this->admin);

        $date = Carbon::parse('2026-03-12');
        $week = $date->weekOfYear;

        PurchaseOrder::create([
            'po_number' => 'PO-TEST-WEEK',
            'order_date' => '2026-03-12',
            'sales_agent_id' => $this->salesRep->id,
            'customer_name' => 'Test Customer D',
            'order_amount' => 45000.00,
            'realized_profit' => 12000.00,
            'status' => PurchaseOrder::STATUS_APPROVED,
            'delivery_status' => PurchaseOrder::DELIVERY_PENDING,
            'is_completed' => false,
            'is_conforme_po' => false,
        ]);

        Livewire::test(SalesDashboard::class)
            ->set('filterData.periodType', 'weeks')
            ->set('filterData.selectedYear', 2026)
            ->set('filterData.selectedWeek', $week)
            ->assertSuccessful()
            ->assertSee('₱45,000.00');

        Livewire::test(SalesOverviewWidget::class, [
            'periodType' => 'weeks',
            'selectedYear' => 2026,
            'selectedWeek' => $week,
        ])
            ->assertSuccessful()
            ->assertSee('₱45,000.00');
    }

    public function test_inhouse_filter_toggles_owner_accounts_and_resets_selected_agent(): void
    {
        $this->actingAs($this->admin);

        PurchaseOrder::create([
            'po_number' => 'PO-REP',
            'order_date' => '2026-01-10',
            'sales_agent_id' => $this->salesRep->id,
            'customer_name' => 'Rep Client',
            'order_amount' => 30000.00,
            'realized_profit' => 9000.00,
            'status' => PurchaseOrder::STATUS_APPROVED,
            'delivery_status' => PurchaseOrder::DELIVERY_PENDING,
            'is_completed' => false,
            'is_conforme_po' => false,
        ]);

        PurchaseOrder::create([
            'po_number' => 'PO-INHOUSE',
            'order_date' => '2026-01-12',
            'sales_agent_id' => $this->admin->id,
            'customer_name' => 'Direct Owner Client',
            'order_amount' => 75000.00,
            'realized_profit' => 25000.00,
            'status' => PurchaseOrder::STATUS_APPROVED,
            'delivery_status' => PurchaseOrder::DELIVERY_PENDING,
            'is_completed' => false,
            'is_conforme_po' => false,
        ]);

        $test = Livewire::test(SalesDashboard::class)
            ->set('filterData.periodType', 'month')
            ->set('filterData.selectedYear', 2026)
            ->set('filterData.selectedMonth', 1)
            ->set('filterData.filterInhouse', true)
            ->assertSuccessful()
            ->assertSee('Admin User')
            ->assertSee('₱75,000.00')
            ->assertDontSee('Sales Executive One');

        Livewire::test(SalesOverviewWidget::class, [
            'periodType' => 'month',
            'selectedYear' => 2026,
            'selectedMonth' => 1,
            'isInhouse' => true,
        ])
            ->assertSuccessful()
            ->assertSee('₱75,000.00');
    }

    public function test_sales_figures_display_when_completed_in_period_even_with_prior_order_date(): void
    {
        $this->actingAs($this->admin);

        // PO with order_date in January 2026, but fulfilled/completed in August 2026
        PurchaseOrder::create([
            'po_number' => 'PO-REALIZED-AUG',
            'order_date' => '2026-01-08',
            'completed_at' => '2026-08-29 13:00:00',
            'actual_delivery_date' => '2026-08-29',
            'sales_agent_id' => $this->salesRep->id,
            'customer_name' => 'Realized Customer',
            'order_amount' => 950000.03,
            'realized_profit' => 285000.01,
            'status' => PurchaseOrder::STATUS_DELIVERED,
            'delivery_status' => PurchaseOrder::DELIVERY_DELIVERED,
            'is_completed' => true,
            'is_conforme_po' => false,
        ]);

        // When viewing August 2026, sales figures must display properly
        Livewire::test(SalesDashboard::class)
            ->set('filterData.periodType', 'month')
            ->set('filterData.selectedYear', 2026)
            ->set('filterData.selectedMonth', 8)
            ->assertSuccessful()
            ->assertSee('Sales Executive One')
            ->assertSee('₱950,000.03');

        // SalesOverviewWidget must also display the ₱950,000.03 revenue
        Livewire::test(SalesOverviewWidget::class, [
            'periodType' => 'month',
            'selectedYear' => 2026,
            'selectedMonth' => 8,
            'agentId' => $this->salesRep->id,
        ])
            ->assertSuccessful()
            ->assertSee('₱950,000.03')
            ->assertSee('₱285,000.01');
    }

    public function test_sales_revenue_chart_widget_adapts_to_granularity_and_agent_filters(): void
    {
        $this->actingAs($this->admin);

        PurchaseOrder::create([
            'po_number' => 'PO-CHART-TEST',
            'order_date' => '2026-08-15',
            'sales_agent_id' => $this->salesRep->id,
            'customer_name' => 'Chart Customer',
            'order_amount' => 175000.00,
            'realized_profit' => 52500.00,
            'status' => PurchaseOrder::STATUS_APPROVED,
            'delivery_status' => PurchaseOrder::DELIVERY_PENDING,
            'is_completed' => false,
            'is_conforme_po' => false,
        ]);

        Quotation::create([
            'quotation_number' => 'HISI-Q-CHART-TEST',
            'quotation_date' => '2026-08-14',
            'sales_agent_id' => $this->salesRep->id,
            'customer_name' => 'Chart Customer',
            'total_amount' => 250000.00,
            'status' => Quotation::STATUS_APPROVED,
        ]);

        // Test ChartWidget with month view
        $chart = Livewire::test(\App\Filament\Widgets\SalesRevenueChartWidget::class, [
            'periodType' => 'month',
            'selectedYear' => 2026,
            'selectedMonth' => 8,
            'selectedAgentId' => $this->salesRep->id,
        ])
            ->assertSuccessful();

        $this->assertStringContainsString('August 2026', $chart->instance()->getHeading());
        $this->assertStringContainsString('Sales Executive One', $chart->instance()->getDescription());

        // Test dispatching filter update to week view
        $chart->dispatch('salesFilterUpdated', [
            'periodType' => 'weeks',
            'selectedYear' => 2026,
            'selectedWeek' => 33,
            'selectedAgentId' => $this->salesRep->id,
            'filterInhouse' => false,
        ]);

        $this->assertStringContainsString('Week 33 (2026)', $chart->instance()->getHeading());

        // Test dispatching inhouse filter
        $chart->dispatch('salesFilterUpdated', [
            'periodType' => 'years',
            'selectedYear' => 2026,
            'selectedAgentId' => null,
            'filterInhouse' => true,
        ]);

        $this->assertStringContainsString('Annual Revenue & Quotation Trend — 2026', $chart->instance()->getHeading());
        $this->assertStringContainsString('Inhouse / Owner Accounts', $chart->instance()->getDescription());
    }

    public function test_quotation_conversion_and_top_products_widgets_fetch_data_properly(): void
    {
        $this->actingAs($this->admin);

        // 1. Create Quotation in August 2026
        $quote = Quotation::create([
            'quotation_number' => 'HISI-Q-CONV-01',
            'quotation_date' => '2026-08-10',
            'sales_agent_id' => $this->salesRep->id,
            'customer_name' => 'Conversion Customer',
            'total_amount' => 120000.00,
            'status' => Quotation::STATUS_APPROVED,
        ]);

        // 2. Create Won Purchase Order in August 2026
        $po = PurchaseOrder::create([
            'po_number' => 'PO-CONV-01',
            'order_date' => '2026-08-15',
            'quotation_id' => $quote->id,
            'sales_agent_id' => $this->salesRep->id,
            'customer_name' => 'Conversion Customer',
            'order_amount' => 120000.00,
            'realized_profit' => 36000.00,
            'status' => PurchaseOrder::STATUS_APPROVED,
            'delivery_status' => PurchaseOrder::DELIVERY_PENDING,
            'is_completed' => false,
            'is_conforme_po' => false,
        ]);

        // 3. Create PO Line Item
        \App\Models\PurchaseOrderLineItem::create([
            'purchase_order_id' => $po->id,
            'line_no' => 1,
            'item_code' => 'HISI-COB-01',
            'description' => 'Citizen Japan 15W COB Downlight',
            'qty' => 100,
            'unit' => 'PC',
            'unit_price' => 1200.00,
            'line_total' => 120000.00,
        ]);

        // Test QuotationConversionWidget
        $convWidget = Livewire::test(\App\Filament\Widgets\QuotationConversionWidget::class, [
            'periodType' => 'month',
            'selectedYear' => 2026,
            'selectedMonth' => 8,
            'selectedAgentId' => $this->salesRep->id,
        ])
            ->assertSuccessful();

        $this->assertStringContainsString('August 2026', $convWidget->instance()->getDescription());
        $convData = $convWidget->instance()->getData();
        // Since $quote has a linked won PO, it should be counted under Won/Converted (first index)
        $this->assertEquals(1, $convData['datasets'][0]['data'][0]);

        // Test TopSellingProductsWidget
        $topWidget = Livewire::test(\App\Filament\Widgets\TopSellingProductsWidget::class, [
            'periodType' => 'month',
            'selectedYear' => 2026,
            'selectedMonth' => 8,
            'selectedAgentId' => $this->salesRep->id,
        ])
            ->assertSuccessful();

        $this->assertStringContainsString('August 2026', $topWidget->instance()->getDescription());
        $topData = $topWidget->instance()->getData();
        $this->assertNotEmpty($topData['labels']);
        $this->assertStringContainsString('Citizen Japan 15W', $topData['labels'][0]);
        $this->assertEquals(120000.00, $topData['datasets'][0]['data'][0]);
    }
}
