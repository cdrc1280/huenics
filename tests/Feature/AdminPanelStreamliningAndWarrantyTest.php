<?php

namespace Tests\Feature;

use App\Filament\Pages\VendorLayoutEditorPage;
use App\Filament\Resources\ProductAliasResource;
use App\Filament\Resources\ProductResource;
use App\Filament\Resources\ProductResource\RelationManagers\AliasesRelationManager;
use App\Models\PurchaseOrder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPanelStreamliningAndWarrantyTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::create([
            'name' => 'Operations Officer',
            'email' => 'ops@huenics.com',
            'password' => bcrypt('password'),
            'role' => User::ROLE_ADMIN,
        ]);
    }

    protected function createPo(array $attributes = []): PurchaseOrder
    {
        static $counter = 100;
        $counter++;

        return PurchaseOrder::create(array_merge([
            'po_number' => "PO-WARR-{$counter}",
            'customer_name' => 'Megaworld Construction Corp',
            'order_date' => now()->toDateString(),
            'sales_agent_id' => $this->user->id,
            'order_amount' => 50000.00,
            'delivery_status' => PurchaseOrder::DELIVERY_PENDING,
            'status' => PurchaseOrder::STATUS_APPROVED,
            'has_warranty' => true,
            'warranty_period' => PurchaseOrder::WARRANTY_1_YEAR,
        ], $attributes));
    }

    public function test_vendor_layout_editor_page_is_hidden_from_navigation(): void
    {
        $this->assertFalse(
            VendorLayoutEditorPage::shouldRegisterNavigation(),
            'VendorLayoutEditorPage should not be registered in the primary sidebar navigation.'
        );
    }

    public function test_product_alias_resource_is_hidden_from_navigation(): void
    {
        $this->assertFalse(
            ProductAliasResource::shouldRegisterNavigation(),
            'ProductAliasResource should not be registered as a standalone sidebar navigation item.'
        );
    }

    public function test_product_resource_includes_aliases_relation_manager(): void
    {
        $relations = ProductResource::getRelations();
        $this->assertContains(
            AliasesRelationManager::class,
            $relations,
            'ProductResource must include AliasesRelationManager to manage aliases directly in the Product workspace.'
        );
    }

    public function test_purchase_order_warranty_countdown_for_no_warranty(): void
    {
        $po = $this->createPo([
            'has_warranty' => false,
            'warranty_status' => PurchaseOrder::WARRANTY_NONE,
        ]);

        $this->assertEquals('No Warranty', $po->warranty_countdown);
        $this->assertEquals('gray', $po->warranty_countdown_color);
        $this->assertEquals('No Warranty', $po->warranty_summary);
    }

    public function test_purchase_order_warranty_countdown_for_pending_delivery(): void
    {
        $po = $this->createPo([
            'has_warranty' => true,
            'warranty_period' => PurchaseOrder::WARRANTY_1_YEAR,
            'actual_delivery_date' => null,
            'warranty_end_date' => null,
        ]);

        $this->assertEquals('Pending Delivery', $po->warranty_countdown);
        $this->assertEquals('info', $po->warranty_countdown_color);
    }

    public function test_purchase_order_warranty_countdown_for_active_warranty(): void
    {
        $po = $this->createPo([
            'has_warranty' => true,
            'warranty_period' => PurchaseOrder::WARRANTY_1_YEAR,
            'is_completed' => true,
            'actual_delivery_date' => now()->subMonths(4)->toDateString(),
            'delivery_status' => PurchaseOrder::DELIVERY_DELIVERED,
            'status' => PurchaseOrder::STATUS_DELIVERED,
        ]);

        $countdown = $po->warranty_countdown;
        $this->assertStringContainsString('left', $countdown);
        $this->assertStringContainsString('mo', $countdown);
        $this->assertEquals('success', $po->warranty_countdown_color);

        $summary = $po->warranty_summary;
        $this->assertStringContainsString('1 Year', $summary);
        $this->assertStringContainsString('left', $summary);
    }

    public function test_purchase_order_warranty_countdown_for_expired_warranty(): void
    {
        $po = $this->createPo([
            'has_warranty' => true,
            'warranty_period' => PurchaseOrder::WARRANTY_6_MONTHS,
            'is_completed' => true,
            'actual_delivery_date' => now()->subMonths(7)->toDateString(),
            'delivery_status' => PurchaseOrder::DELIVERY_DELIVERED,
            'status' => PurchaseOrder::STATUS_DELIVERED,
        ]);

        $this->assertStringContainsString('Expired', $po->warranty_countdown);
        $this->assertEquals('danger', $po->warranty_countdown_color);
    }

    public function test_purchase_order_warranty_countdown_warning_for_expiring_soon(): void
    {
        $deliveryDate = now()->subYear()->addDays(20);

        $po = $this->createPo([
            'has_warranty' => true,
            'warranty_period' => PurchaseOrder::WARRANTY_1_YEAR,
            'is_completed' => true,
            'actual_delivery_date' => $deliveryDate->toDateString(),
            'delivery_status' => PurchaseOrder::DELIVERY_DELIVERED,
            'status' => PurchaseOrder::STATUS_DELIVERED,
        ]);

        $this->assertEquals('warning', $po->warranty_countdown_color);
        $this->assertStringContainsString('20 days left', $po->warranty_countdown);
    }
}
