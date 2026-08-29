<?php

namespace Tests\Feature;

use App\Filament\Resources\PurchaseOrderResource;
use App\Filament\Resources\QuotationResource;
use App\Models\PurchaseOrder;
use App\Models\Quotation;
use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RemoveEditFromQuotationAndPoTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected Quotation $quotation;
    protected PurchaseOrder $po;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleAndPermissionSeeder::class);
        $this->admin = User::where('email', 'admin@huenics.com')->first();

        $this->quotation = Quotation::create([
            'quotation_number' => 'HISI-Q-2026-TEST-NO-EDIT',
            'sales_agent_id' => $this->admin->id,
            'customer_name' => 'Acme Corp',
            'customer_company' => 'Acme Corp',
            'total_amount' => 50000.00,
            'status' => Quotation::STATUS_APPROVED,
            'quotation_date' => now()->toDateString(),
        ]);

        $this->po = PurchaseOrder::create([
            'po_number' => 'PO-2026-TEST-NO-EDIT',
            'sales_agent_id' => $this->admin->id,
            'customer_name' => 'Acme Corp',
            'order_amount' => 50000.00,
            'status' => PurchaseOrder::STATUS_PENDING,
            'order_date' => now()->toDateString(),
        ]);
    }

    public function test_quotation_resource_can_edit_returns_false(): void
    {
        $this->assertFalse(QuotationResource::canEdit($this->quotation));
    }

    public function test_purchase_order_resource_can_edit_returns_false(): void
    {
        $this->assertFalse(PurchaseOrderResource::canEdit($this->po));
    }

    public function test_quotation_resource_pages_do_not_have_edit_route(): void
    {
        $pages = QuotationResource::getPages();

        $this->assertArrayNotHasKey('edit', $pages);
        $this->assertArrayHasKey('view', $pages);
        $this->assertArrayHasKey('index', $pages);
        $this->assertArrayHasKey('create', $pages);
    }

    public function test_purchase_order_resource_pages_do_not_have_edit_route(): void
    {
        $pages = PurchaseOrderResource::getPages();

        $this->assertArrayNotHasKey('edit', $pages);
        $this->assertArrayHasKey('view', $pages);
        $this->assertArrayHasKey('index', $pages);
    }

    public function test_quotation_view_page_url_works(): void
    {
        $viewUrl = QuotationResource::getUrl('view', ['record' => $this->quotation]);
        $this->assertNotEmpty($viewUrl);

        $response = $this->actingAs($this->admin)->get($viewUrl);
        $response->assertSuccessful();
        $response->assertSee('HISI-Q-2026-TEST-NO-EDIT');
    }

    public function test_purchase_order_view_page_url_works(): void
    {
        $viewUrl = PurchaseOrderResource::getUrl('view', ['record' => $this->po]);
        $this->assertNotEmpty($viewUrl);

        $response = $this->actingAs($this->admin)->get($viewUrl);
        $response->assertSuccessful();
        $response->assertSee('PO-2026-TEST-NO-EDIT');
    }
}
