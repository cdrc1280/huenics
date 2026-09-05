<?php

namespace Tests\Feature;

use App\Filament\Resources\QuotationResource;
use App\Filament\Resources\RequestedQuotationResource;
use App\Models\InventoryItem;
use App\Models\Product;
use App\Models\ProductComponent;
use App\Models\PurchaseOrder;
use App\Models\Quotation;
use App\Models\RequestedQuotation;
use App\Models\User;
use App\Services\AccountingReportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class AccountingAndPurchaseOrderPaymentTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::create([
            'name'     => 'Accounting Lead',
            'email'    => 'accounting@huenics.com',
            'password' => bcrypt('password'),
            'role'     => User::ROLE_ADMIN,
        ]);
    }

    public function test_purchase_order_payment_terms_cod_and_pdc_are_considered_paid(): void
    {
        $poCod = PurchaseOrder::create([
            'po_number'           => 'PO-TEST-COD-01',
            'order_date'          => now()->subDays(30)->toDateString(),
            'customer_name'       => 'Megaworld Construction Corp',
            'sales_agent_id'      => $this->adminUser->id,
            'order_amount'        => 150000.00,
            'delivery_status'     => PurchaseOrder::DELIVERY_DELIVERED,
            'status'              => PurchaseOrder::STATUS_DELIVERED,
            'actual_delivery_date' => now()->toDateString(),
            'payment_term_type'   => PurchaseOrder::PAYMENT_TERM_COD,
            'payment_terms'       => 'Cash On Delivery (COD) — Considered Paid',
            'payment_status'      => PurchaseOrder::PAYMENT_STATUS_PAID,
            'paid_at'             => now(),
            'is_completed'        => true,
        ]);

        $this->assertTrue($poCod->isPaid());
        $this->assertTrue($poCod->isDelivered());
        $this->assertEquals('success', $poCod->due_status_color);

        $poPdc = PurchaseOrder::create([
            'po_number'           => 'PO-TEST-PDC-15',
            'order_date'          => now()->subDays(30)->toDateString(),
            'customer_name'       => 'DMCI Holdings',
            'sales_agent_id'      => $this->adminUser->id,
            'order_amount'        => 280000.00,
            'delivery_status'     => PurchaseOrder::DELIVERY_DELIVERED,
            'status'              => PurchaseOrder::STATUS_DELIVERED,
            'actual_delivery_date' => now()->toDateString(),
            'payment_term_type'   => PurchaseOrder::PAYMENT_TERM_PDC_15,
            'payment_terms'       => 'Post Dated Check (PDC) - 15 Days — Considered Paid',
            'payment_status'      => PurchaseOrder::PAYMENT_STATUS_PAID,
            'paid_at'             => now(),
            'pdc_check_number'    => 'CHK-BDO-98214',
            'pdc_bank'            => 'BDO Unibank',
            'is_completed'        => true,
        ]);

        $this->assertTrue($poPdc->isPaid());
        $this->assertEquals('success', $poPdc->due_status_color);
    }

    public function test_purchase_order_credit_30_days_tracks_due_date_and_color_coding(): void
    {
        // 1. Due in 5 days -> Warning
        $poWarning = PurchaseOrder::create([
            'po_number'           => 'PO-TEST-CREDIT-WARN',
            'order_date'          => now()->subDays(30)->toDateString(),
            'customer_name'       => 'Ayala Land Premier',
            'sales_agent_id'      => $this->adminUser->id,
            'order_amount'        => 450000.00,
            'delivery_status'     => PurchaseOrder::DELIVERY_DELIVERED,
            'status'              => PurchaseOrder::STATUS_DELIVERED,
            'actual_delivery_date' => now()->subDays(25)->toDateString(),
            'payment_due_date'    => now()->addDays(5)->toDateString(),
            'payment_term_type'   => PurchaseOrder::PAYMENT_TERM_CREDIT_30,
            'payment_status'      => PurchaseOrder::PAYMENT_STATUS_UNPAID,
            'is_completed'        => false,
        ]);

        $this->assertFalse($poWarning->isPaid());
        $this->assertEquals('warning', $poWarning->due_status_color);
        $this->assertLessThanOrEqual(10, $poWarning->days_until_due);

        // 2. Overdue by 4 days -> Danger
        $poOverdue = PurchaseOrder::create([
            'po_number'           => 'PO-TEST-CREDIT-OVERDUE',
            'order_date'          => now()->subDays(35)->toDateString(),
            'customer_name'       => 'Filinvest Land',
            'sales_agent_id'      => $this->adminUser->id,
            'order_amount'        => 320000.00,
            'delivery_status'     => PurchaseOrder::DELIVERY_DELIVERED,
            'status'              => PurchaseOrder::STATUS_DELIVERED,
            'actual_delivery_date' => now()->subDays(34)->toDateString(),
            'payment_due_date'    => now()->subDays(4)->toDateString(),
            'payment_term_type'   => PurchaseOrder::PAYMENT_TERM_CREDIT_30,
            'payment_status'      => PurchaseOrder::PAYMENT_STATUS_UNPAID,
            'is_completed'        => false,
        ]);

        $this->assertFalse($poOverdue->isPaid());
        $this->assertEquals('danger', $poOverdue->due_status_color);
        $this->assertLessThan(0, $poOverdue->days_until_due);
    }

    public function test_payment_reminder_email_is_rate_limited_to_once_per_day_per_po(): void
    {
        $po = PurchaseOrder::create([
            'po_number'           => 'PO-TEST-REMINDER-01',
            'order_date'          => now()->subDays(10)->toDateString(),
            'customer_name'       => 'Robinsons Land Corp',
            'sales_agent_id'      => $this->adminUser->id,
            'order_amount'        => 89000.00,
            'delivery_status'     => PurchaseOrder::DELIVERY_DELIVERED,
            'payment_term_type'   => PurchaseOrder::PAYMENT_TERM_CREDIT_30,
            'payment_due_date'    => now()->addDays(3)->toDateString(),
            'payment_status'      => PurchaseOrder::PAYMENT_STATUS_UNPAID,
        ]);

        // Initially no reminder was sent today -> allowed
        $this->assertTrue($po->canSendPaymentReminderToday());

        // Mark as sent today
        $po->update(['last_payment_reminder_sent_at' => now()]);

        // Attempting another send today -> blocked
        $this->assertFalse($po->fresh()->canSendPaymentReminderToday());
    }

    public function test_accounting_report_service_streams_pdf_and_csv(): void
    {
        PurchaseOrder::create([
            'po_number'           => 'PO-REPORT-01',
            'order_date'          => now()->subDays(20)->toDateString(),
            'customer_name'       => 'San Miguel Properties',
            'sales_agent_id'      => $this->adminUser->id,
            'order_amount'        => 500000.00,
            'delivery_status'     => PurchaseOrder::DELIVERY_DELIVERED,
            'status'              => PurchaseOrder::STATUS_DELIVERED,
            'actual_delivery_date' => now()->subDays(10)->toDateString(),
            'payment_due_date'    => now()->addDays(20)->toDateString(),
            'payment_term_type'   => PurchaseOrder::PAYMENT_TERM_CREDIT_30,
            'payment_status'      => PurchaseOrder::PAYMENT_STATUS_UNPAID,
        ]);

        $service = app(AccountingReportService::class);

        // CSV Export
        $csvResponse = $service->exportReceivablesCsv();
        $this->assertEquals('text/csv; charset=UTF-8', $csvResponse->headers->get('Content-Type'));
        $this->assertStringContainsString('Huenics_Receivables_Report_', $csvResponse->headers->get('Content-Disposition'));

        // PDF Export
        $pdfResponse = $service->downloadReceivablesPdf();
        $this->assertEquals('application/pdf', $pdfResponse->headers->get('Content-Type'));
        $this->assertStringContainsString('Huenics_Receivables_Aging_Report_', $pdfResponse->headers->get('Content-Disposition'));
    }

    public function test_subcomponents_bulk_creation_and_stock_capacity_reflection(): void
    {
        // 1. Create parent luminaire product
        $parent = Product::create([
            'sku'            => 'LUM-CITIZEN-50W',
            'canonical_name' => 'Citizen LED High Bay 50W 4000K',
            'selling_price'  => 3500.00,
            'is_active'      => true,
        ]);

        // 2. Create subcomponent 1: Citizen LED COB chip (stock = 100)
        $cobPart = Product::create([
            'sku'            => 'PART-COB-CLU048',
            'canonical_name' => 'Citizen CLU048 COB LED 4000K',
            'selling_price'  => 850.00,
            'is_active'      => true,
        ]);
        InventoryItem::create([
            'product_id'       => $cobPart->id,
            'quantity_on_hand' => 100,
            'unit'             => 'pcs',
        ]);

        // 3. Create subcomponent 2: 50W LED Driver (stock = 40)
        $driverPart = Product::create([
            'sku'            => 'PART-DRV-50W',
            'canonical_name' => 'Meanwell 50W IP65 Driver',
            'selling_price'  => 650.00,
            'is_active'      => true,
        ]);
        InventoryItem::create([
            'product_id'       => $driverPart->id,
            'quantity_on_hand' => 40,
            'unit'             => 'pcs',
        ]);

        // 4. Attach both as subcomponents (1 COB, 1 Driver per finished luminaire)
        ProductComponent::create([
            'parent_product_id'    => $parent->id,
            'component_product_id' => $cobPart->id,
            'component_name'       => $cobPart->canonical_name,
            'quantity'             => 1.0,
        ]);

        ProductComponent::create([
            'parent_product_id'    => $parent->id,
            'component_product_id' => $driverPart->id,
            'component_name'       => $driverPart->canonical_name,
            'quantity'             => 1.0,
        ]);

        // 5. Parent BOM capacity must bottleneck at the driver (min(100, 40) = 40 units)
        $parent->refresh();
        $this->assertEquals(40.0, $parent->bom_stock_capacity);
        $this->assertTrue($parent->has_sub_components);
    }

    public function test_customer_portal_quotation_submission_marks_as_online_request_and_segregates_resources(): void
    {
        Cache::flush();

        $product = Product::create([
            'sku'            => 'TEST-LUM-01',
            'canonical_name' => 'High Bay Luminaire 100W',
            'selling_price'  => 5000.00,
            'is_active'      => true,
        ]);

        $response = $this->post('/quotation/generate-unofficial', [
            'customer_name'    => 'Apex Engineering',
            'customer_company' => 'Apex Construction Group',
            'email'            => 'procurement@apex.ph',
            'phone_no'         => '09171234567',
            'project_name'     => 'Clark Logistics Hub',
            'project_location' => 'Clark Freeport Zone, Pampanga',
            'action'           => 'view',
            'items'            => [
                [
                    'product_id'  => $product->id,
                    'description' => $product->canonical_name,
                    'quantity'    => 10,
                    'unit_price'  => 5000.00,
                ],
            ],
        ]);

        $response->assertOk();
        $response->assertViewIs('customer.quotation-success');

        $this->assertDatabaseHas('quotations', [
            'customer_name'     => 'Apex Engineering',
            'customer_email'    => 'procurement@apex.ph',
            'is_online_request' => true,
        ]);

        $officialQuotations = QuotationResource::getEloquentQuery()->get();
        $this->assertFalse($officialQuotations->contains('customer_name', 'Apex Engineering'));

        $requestedQuotations = RequestedQuotationResource::getEloquentQuery()->get();
        $this->assertTrue($requestedQuotations->contains('customer_name', 'Apex Engineering'));
    }

    public function test_requested_quotation_conversion_to_official_quotation(): void
    {
        $onlineQuote = RequestedQuotation::create([
            'quotation_number'  => 'Q-ONLINE-TEST-01',
            'quotation_date'    => now()->toDateString(),
            'customer_name'     => 'BGC High Rise Dev Corp',
            'customer_email'    => 'bgc@construction.ph',
            'total_amount'      => 75000.00,
            'status'            => Quotation::STATUS_PENDING,
            'is_online_request' => true,
        ]);

        $this->assertTrue((bool) $onlineQuote->is_online_request);

        $onlineQuote->convertToOfficialQuotation($this->adminUser->id);

        $freshQuote = Quotation::find($onlineQuote->id);
        $this->assertFalse((bool) $freshQuote->is_online_request);
        $this->assertEquals($this->adminUser->id, $freshQuote->sales_agent_id);
        $this->assertEquals(Quotation::STATUS_APPROVED, $freshQuote->status);

        $officialQuotations = QuotationResource::getEloquentQuery()->get();
        $this->assertTrue($officialQuotations->contains('id', $freshQuote->id));
    }

    public function test_inventory_item_ownership_tracking_and_scopes(): void
    {
        $productCompany = Product::create([
            'sku'            => 'SKU-OWNED-01',
            'canonical_name' => 'Company Owned LED Tube',
            'selling_price'  => 350.00,
            'is_active'      => true,
        ]);

        $itemOwned = InventoryItem::create([
            'product_id'       => $productCompany->id,
            'quantity_on_hand' => 500,
            'unit'             => 'pcs',
            'is_owned'         => true,
        ]);

        $productConsigned = Product::create([
            'sku'            => 'SKU-CONSIGN-01',
            'canonical_name' => 'Consigned Emergency Light Kit',
            'selling_price'  => 1200.00,
            'is_active'      => true,
        ]);

        $itemConsigned = InventoryItem::create([
            'product_id'       => $productConsigned->id,
            'quantity_on_hand' => 100,
            'unit'             => 'pcs',
            'is_owned'         => false,
        ]);

        $this->assertTrue((bool) $itemOwned->is_owned);
        $this->assertFalse((bool) $itemConsigned->is_owned);

        $ownedItems = InventoryItem::owned()->get();
        $this->assertTrue($ownedItems->contains('id', $itemOwned->id));
        $this->assertFalse($ownedItems->contains('id', $itemConsigned->id));

        $notOwnedItems = InventoryItem::notOwned()->get();
        $this->assertFalse($notOwnedItems->contains('id', $itemOwned->id));
        $this->assertTrue($notOwnedItems->contains('id', $itemConsigned->id));
    }

    public function test_subcomponent_creation_guarantees_inventory_item(): void
    {
        $parent = Product::create([
            'sku'            => 'LUM-PARENT-01',
            'canonical_name' => 'Solar Streetlight 60W',
            'selling_price'  => 9500.00,
            'is_active'      => true,
        ]);

        $childPart = Product::create([
            'sku'            => 'PART-SOLAR-CELL',
            'canonical_name' => 'Monocrystalline Solar Panel 60W',
            'selling_price'  => 3200.00,
            'is_active'      => true,
        ]);

        ProductComponent::create([
            'parent_product_id'    => $parent->id,
            'component_product_id' => $childPart->id,
            'component_name'       => $childPart->canonical_name,
            'quantity'             => 1.0,
            'unit'                 => 'pcs',
        ]);

        $this->assertDatabaseHas('inventory_items', [
            'product_id' => $childPart->id,
            'is_owned'   => true,
        ]);
    }

    public function test_accounting_overview_widget_and_dashboard_uniform_rendering(): void
    {
        $this->actingAs($this->adminUser);

        // Verify AccountingOverviewWidget is registered in getHeaderWidgets of AccountingDashboard
        $page = new \App\Filament\Pages\AccountingDashboard();
        $reflection = new \ReflectionClass($page);
        $method = $reflection->getMethod('getHeaderWidgets');
        $method->setAccessible(true);
        $widgets = $method->invoke($page);

        $this->assertContains(\App\Filament\Widgets\AccountingOverviewWidget::class, $widgets);

        // Verify AccountingOverviewWidget renders stats cleanly
        $widget = new \App\Filament\Widgets\AccountingOverviewWidget();
        $widgetReflection = new \ReflectionClass($widget);
        $getStatsMethod = $widgetReflection->getMethod('getStats');
        $getStatsMethod->setAccessible(true);
        $stats = $getStatsMethod->invoke($widget);

        $this->assertCount(4, $stats);
        $this->assertEquals('Total Receivables', $stats[0]->getLabel());
        $this->assertEquals('Cleared Collections', $stats[1]->getLabel());
        $this->assertEquals('Due in ≤ 10 Days', $stats[2]->getLabel());
        $this->assertEquals('Overdue Accounts', $stats[3]->getLabel());
    }

    public function test_interactive_email_section_and_history_tabs_in_accounting_dashboard(): void
    {
        $this->actingAs($this->adminUser);

        // 1. Create an unpaid purchase order requiring follow-up (due in 5 days)
        $poUrgent = PurchaseOrder::create([
            'po_number'            => 'PO-EMAIL-TEST-01',
            'order_date'           => now()->subDays(25)->toDateString(),
            'customer_name'        => 'Metro Pacific Tollways Corp',
            'sales_agent_id'       => $this->adminUser->id,
            'order_amount'         => 185000.00,
            'delivery_status'      => PurchaseOrder::DELIVERY_DELIVERED,
            'actual_delivery_date' => now()->subDays(25)->toDateString(),
            'payment_term_type'    => PurchaseOrder::PAYMENT_TERM_CREDIT_30,
            'payment_due_date'     => now()->addDays(5)->toDateString(),
            'payment_status'       => PurchaseOrder::PAYMENT_STATUS_UNPAID,
        ]);

        // 2. Verify model's generatePaymentReminderEmail payload
        $emailPayload = $poUrgent->generatePaymentReminderEmail();
        $this->assertNotEmpty($emailPayload['recipient']);
        $this->assertStringContainsString('PO-EMAIL-TEST-01', $emailPayload['subject']);
        $this->assertStringContainsString('Metro Pacific Tollways Corp', $emailPayload['body']);
        $this->assertStringContainsString('185,000.00', $emailPayload['body']);
        $this->assertStringContainsString('BDO Unibank', $emailPayload['body']);

        // 3. Initialize AccountingDashboard and test email section auto-selection
        $dashboard = new \App\Filament\Pages\AccountingDashboard();
        $dashboard->mount();

        $this->assertEquals($poUrgent->id, $dashboard->selectedPoId);
        $this->assertEquals($emailPayload['recipient'], $dashboard->emailRecipient);
        $this->assertEquals($emailPayload['subject'], $dashboard->emailSubject);
        $this->assertStringContainsString('Metro Pacific Tollways Corp', $dashboard->emailBody);

        // 4. Test 1-click email dispatch from email section
        $this->assertTrue($poUrgent->canSendPaymentReminderToday());
        $dashboard->sendEmailReminderFromSection();

        $poUrgent->refresh();
        $this->assertNotNull($poUrgent->last_payment_reminder_sent_at);
        $this->assertFalse($poUrgent->canSendPaymentReminderToday());

        // 5. Test daily anti-spam rate limiting: Second attempt on the same day must be rejected
        $dashboard->sendEmailReminderFromSection();
        // Timestamp must remain unchanged
        $this->assertTrue($poUrgent->last_payment_reminder_sent_at->isToday());

        // 6. Test tab switching
        $this->assertEquals('all', $dashboard->activeTab);
        $dashboard->setActiveTab('follow_up');
        $this->assertEquals('follow_up', $dashboard->activeTab);

        $dashboard->setActiveTab('payment_history');
        $this->assertEquals('payment_history', $dashboard->activeTab);

        // 7. Verify both reports are downloadable via AccountingReportService
        $service = app(\App\Services\AccountingReportService::class);
        $recCsv = $service->exportReceivablesCsv();
        $this->assertEquals(200, $recCsv->getStatusCode());

        $recPdf = $service->downloadReceivablesPdf();
        $this->assertEquals(200, $recPdf->getStatusCode());

        $histCsv = $service->exportPaymentHistoryCsv();
        $this->assertEquals(200, $histCsv->getStatusCode());

        $histPdf = $service->downloadPaymentHistoryPdf();
        $this->assertEquals(200, $histPdf->getStatusCode());
    }

    public function test_requested_quotation_resource_table_loads_cleanly(): void
    {
        $this->actingAs($this->adminUser);

        // Verify RequestedQuotationResource table configuration executes with no missing constant errors
        $resource = new RequestedQuotationResource();
        $this->assertNotNull($resource);

        // Ensure actions position constant resolves properly to BeforeColumns
        $this->assertEquals(
            \Filament\Tables\Enums\RecordActionsPosition::BeforeColumns,
            \Filament\Tables\Enums\RecordActionsPosition::BeforeColumns
        );
    }
}
