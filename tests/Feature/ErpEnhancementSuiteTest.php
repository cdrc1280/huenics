<?php

namespace Tests\Feature;

use App\Filament\Pages\ReviewQueuePage;
use App\Filament\Pages\SalesDashboard;
use App\Filament\Resources\InventoryItemResource;
use App\Models\CompanySetting;
use App\Models\DeliveryReceipt;
use App\Models\Document;
use App\Models\DocumentLineItem;
use App\Models\InventoryItem;
use App\Models\Product;
use App\Models\ProductComponent;
use App\Models\PurchaseOrder;
use App\Models\Quotation;
use App\Models\SalesInvoice;
use App\Models\User;
use App\Services\InventoryReportService;
use App\Services\LivePdfGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Livewire\Livewire;
use Tests\TestCase;

class ErpEnhancementSuiteTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $salesExec;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin.erp@huenics.test',
            'password' => bcrypt('password'),
            'role' => User::ROLE_ADMIN,
            'is_owner' => true,
        ]);

        $this->salesExec = User::create([
            'name' => 'Sales Agent',
            'email' => 'agent.erp@huenics.test',
            'password' => bcrypt('password'),
            'role' => User::ROLE_SALES_EXECUTIVE,
            'is_owner' => false,
        ]);
    }

    /**
     * Test 1: Multiple DR and SI per PO
     */
    public function test_multiple_dr_and_si_can_be_created_per_po(): void
    {
        $po = PurchaseOrder::create([
            'po_number' => 'PO-2026-MULTI-001',
            'customer_name' => 'First Multi Corp',
            'sales_agent_id' => $this->admin->id,
            'order_amount' => 100000,
            'status' => PurchaseOrder::STATUS_APPROVED,
            'order_date' => now(),
        ]);

        // First partial delivery
        $dr1 = DeliveryReceipt::create([
            'purchase_order_id' => $po->id,
            'dr_number' => 'DR-2026-001',
            'delivery_date' => now()->subDays(2),
            'status' => 'delivered',
            'received_by' => 'Site Receiver John',
        ]);

        // Second partial delivery
        $dr2 = DeliveryReceipt::create([
            'purchase_order_id' => $po->id,
            'dr_number' => 'DR-2026-002',
            'delivery_date' => now(),
            'status' => 'delivered',
            'received_by' => 'Site Receiver Jane',
        ]);

        // First progressive invoice
        $si1 = SalesInvoice::create([
            'purchase_order_id' => $po->id,
            'customer_name' => 'First Multi Corp',
            'si_number' => 'SI-2026-001',
            'invoice_date' => now()->subDays(2),
            'status' => 'issued',
            'total_amount' => 50000,
            'payment_status' => 'paid',
        ]);

        // Second progressive invoice
        $si2 = SalesInvoice::create([
            'purchase_order_id' => $po->id,
            'customer_name' => 'First Multi Corp',
            'si_number' => 'SI-2026-002',
            'invoice_date' => now(),
            'status' => 'issued',
            'total_amount' => 50000,
            'payment_status' => 'paid',
        ]);

        $this->assertCount(2, $po->deliveryReceipts);
        $this->assertCount(2, $po->salesInvoices);
        $this->assertEquals(100000, $po->salesInvoices()->sum('total_amount'));
    }

    /**
     * Test 2: Sub-components and BOM hierarchy in Inventory Management
     */
    public function test_inventory_management_displays_bom_assembly_hierarchy(): void
    {
        // Create Parent Product
        $parent = Product::create([
            'canonical_name' => 'Complete Commercial Highbay Luminaire',
            'product_code' => 'LUM-HB-150W',
            'sku' => 'SKU-LUM-150W',
            'is_huenics_owned' => true,
            'is_active' => true,
            'base_cost_price' => 2500.00,
            'selling_price' => 3500.00,
        ]);

        // Create Child Component Product
        $child = Product::create([
            'canonical_name' => '150W Constant Current LED Driver',
            'product_code' => 'DRV-150W-CC',
            'sku' => 'SKU-DRV-150W',
            'is_huenics_owned' => true,
            'is_active' => true,
            'base_cost_price' => 850.00,
            'selling_price' => 1200.00,
        ]);

        // Create BOM link
        ProductComponent::create([
            'parent_product_id' => $parent->id,
            'component_product_id' => $child->id,
            'component_name' => '150W Driver Module',
            'product_code' => 'DRV-150W-CC',
            'quantity' => 1,
            'cost_price' => 850.00,
        ]);

        // Create inventory records
        $parentItem = InventoryItem::create([
            'product_id' => $parent->id,
            'quantity_on_hand' => 10,
            'location' => 'Section A',
            'unit' => 'pcs',
        ]);

        $childItem = InventoryItem::create([
            'product_id' => $child->id,
            'quantity_on_hand' => 50,
            'location' => 'Section B',
            'unit' => 'pcs',
        ]);

        $standaloneProduct = Product::create([
            'canonical_name' => 'Standalone Bulb E27',
            'product_code' => 'BLB-E27-9W',
            'sku' => 'SKU-BLB-9W',
            'is_huenics_owned' => true,
            'is_active' => true,
        ]);

        $standaloneItem = InventoryItem::create([
            'product_id' => $standaloneProduct->id,
            'quantity_on_hand' => 20,
            'location' => 'Section C',
            'unit' => 'pcs',
        ]);

        $this->assertEquals(1, $parent->components()->count());
        $this->assertTrue(ProductComponent::where('component_product_id', $child->id)->exists());

        // Verify BOM type filter queries
        $parentQuery = InventoryItem::query()->whereHas('product.components')->pluck('id');
        $this->assertTrue($parentQuery->contains($parentItem->id));
        $this->assertFalse($parentQuery->contains($standaloneItem->id));

        $childQuery = InventoryItem::query()->whereHas('product', function ($pq) {
            $pq->whereIn('id', ProductComponent::whereNotNull('component_product_id')->select('component_product_id'));
        })->pluck('id');
        $this->assertTrue($childQuery->contains($childItem->id));
    }

    /**
     * Test 3: Delete line items in Review Queue and Livewire reactivity
     */
    public function test_line_item_deletion_in_review_queue_page(): void
    {
        $this->actingAs($this->admin);

        $doc = Document::create([
            'uploaded_by' => $this->admin->id,
            'document_type' => Document::TYPE_VENDORS_AGREEMENT,
            'document_number' => 'QTN-2026-TEST',
            'status' => 'pending_review',
            'original_filename' => 'quote.pdf',
            'stored_file_path' => 'documents/quote.pdf',
            'disk_path' => 'documents/quote.pdf',
            'file_hash' => md5('quote.pdf' . time()),
        ]);

        DocumentLineItem::create([
            'document_id' => $doc->id,
            'line_no' => 1,
            'description' => 'LED Strip 12V 5m',
            'qty' => 10,
            'unit_price' => 250.00,
            'line_total' => 2500.00,
        ]);

        DocumentLineItem::create([
            'document_id' => $doc->id,
            'line_no' => 2,
            'description' => 'LED Power Supply 60W',
            'qty' => 5,
            'unit_price' => 450.00,
            'line_total' => 2250.00,
        ]);

        $testable = Livewire::test(ReviewQueuePage::class, ['document_id' => $doc->id]);

        $this->assertCount(2, $testable->get('editableItems'));

        // Delete line item at index 0
        $testable->call('removeLineItem', 0);

        $this->assertCount(1, $testable->get('editableItems'));
        $this->assertEquals(1, $testable->get('editableItems')[0]['line_no']);
        $this->assertEquals('LED Power Supply 60W', $testable->get('editableItems')[0]['description']);
    }

    /**
     * Test 4: Live PDF URL safe Base64 and slash normalization
     */
    public function test_live_pdf_generator_normalizes_unicode_slashes_and_special_chars(): void
    {
        $generator = app(LivePdfGenerator::class);

        $payload = [
            'documentNumber' => "QTN-2026\xE2\x88\x95001", // Unicode division slash
            'documentDate' => "09\xE2\x81\x8405\xE2\x81\x842026", // Unicode fraction slash
            'customerName' => 'Universal Testing Corp.',
            'customerCompany' => 'BuildCorp Asia / Pacific',
            'projectName' => '1/2" Conduit Fitting Project',
            'projectLocation' => 'Ortigas Center, Pasig',
            'phoneNo' => '0917-000-1111',
            'items' => [
                [
                    'line_no' => 1,
                    'item_code' => 'CON-1/2-PVC',
                    'description' => "1/2\xEF\xBC\x8F3/4 Inch Electrical Connector",
                    'qty' => 100,
                    'unit' => 'pcs',
                    'unit_price' => 45.00,
                    'line_total' => 4500.00,
                ],
            ],
            'mod' => [],
            'tcValidity' => '15 days',
            'tcStock' => true,
            'tcNonStock' => false,
            'tcDelivery4To7' => true,
            'tcDelivery10To15' => false,
            'tcDelivery45To60' => false,
            'tcPaymentCodDp' => true,
            'tcPaymentApproved' => false,
            'tcRemarksOfficialPo' => true,
            'tcRemarksNonReturnable' => true,
        ];

        $pdf = $generator->generate($payload);

        $this->assertNotEmpty($pdf);
        $this->assertStringStartsWith('%PDF', $pdf);
    }

    /**
     * Test 5: Dynamic Business Years and CompanySetting Model
     */
    public function test_company_setting_calculates_dynamic_years_in_business(): void
    {
        Cache::flush();

        // Default founding year is 2022
        CompanySetting::setSetting('founding_year', 2022);
        CompanySetting::setSetting('years_in_business_override', null);

        $expectedYears = max(1, (int) date('Y') - 2022);
        $this->assertEquals($expectedYears, CompanySetting::getYearsInBusiness());

        // Test with override
        CompanySetting::setSetting('years_in_business_override', 10);
        $this->assertEquals(10, CompanySetting::getYearsInBusiness());

        // Test customer portal controller dynamically reflects it
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('10 Years');
    }

    /**
     * Test 6: Malformed UTF-8 prevention in Inventory Report CSV Downloads
     */
    public function test_inventory_report_csv_includes_utf8_bom_and_sanitizes_characters(): void
    {
        $service = app(InventoryReportService::class);

        $product = Product::create([
            'canonical_name' => "Industrial Luminaire ₱500 with Special UTF-8: \xE2\x80\x94 Em-Dash",
            'product_code' => 'LUM-UTF8',
            'sku' => 'SKU-LUM-UTF8',
            'is_huenics_owned' => true,
            'is_active' => true,
        ]);

        InventoryItem::create([
            'product_id' => $product->id,
            'quantity_on_hand' => 15,
            'remarks' => "Special approval: Validated & Checked \xE2\x9C\x93",
            'location' => 'A-1',
            'unit' => 'pcs',
        ]);

        $csv = $service->exportInventoryReport();

        // Must start with UTF-8 BOM \xEF\xBB\xBF
        $this->assertStringStartsWith("\xEF\xBB\xBF", $csv);
        $this->assertTrue(mb_check_encoding($csv, 'UTF-8'));

        $sampleCsv = $service->generateSampleInventoryCsv();
        $this->assertStringStartsWith("\xEF\xBB\xBF", $sampleCsv);
        $this->assertTrue(mb_check_encoding($sampleCsv, 'UTF-8'));
    }

    /**
     * Test 7: Sales Dashboard Analytics and Complete CSV Export
     */
    public function test_sales_dashboard_export_all_analytics_action(): void
    {
        $this->actingAs($this->admin);

        // Seed sample Quotation and PO
        $q = Quotation::create([
            'quotation_number' => 'QTN-2026-DASH-01',
            'sales_agent_id' => $this->admin->id,
            'customer_name' => 'Acme Corporation',
            'total_amount' => 75000,
            'status' => 'approved',
            'quotation_date' => now(),
        ]);

        PurchaseOrder::create([
            'po_number' => 'PO-2026-DASH-01',
            'customer_name' => 'Acme Corporation',
            'sales_agent_id' => $this->admin->id,
            'quotation_id' => $q->id,
            'order_amount' => 75000,
            'is_completed' => true,
            'status' => PurchaseOrder::STATUS_DELIVERED,
            'order_date' => now(),
        ]);

        Livewire::test(SalesDashboard::class)
            ->assertSuccessful()
            ->assertActionExists('exportAllAnalytics')
            ->assertActionExists('exportLeaderboardCsv')
            ->assertActionExists('downloadExecutiveReport');
    }
}
