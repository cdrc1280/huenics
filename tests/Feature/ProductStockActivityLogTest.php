<?php

namespace Tests\Feature;

use App\Filament\Resources\ActivityLogResource;
use App\Filament\Resources\ProductResource;
use App\Models\AuditLog;
use App\Models\InventoryItem;
use App\Models\InventoryTransaction;
use App\Models\Product;
use App\Models\User;
use App\Services\InventoryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductStockActivityLogTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $opsManager;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@huenics.com',
            'password' => bcrypt('password'),
            'role' => User::ROLE_ADMIN,
            'is_owner' => true,
        ]);

        $this->opsManager = User::create([
            'name' => 'Operations Manager',
            'email' => 'ops@huenics.com',
            'password' => bcrypt('password'),
            'role' => User::ROLE_OPERATIONS_MANAGER,
        ]);
    }

    public function test_add_stock_increments_inventory_and_logs_to_activity_log(): void
    {
        $this->actingAs($this->admin);

        $product = Product::create([
            'product_code' => 'COB-DL-001',
            'canonical_name' => 'C.O.B Downlight 15W Warm White',
            'sku' => 'HISI-DL-15W',
            'unit_default' => 'pcs',
            'base_cost_price' => 250.00,
            'selling_price' => 450.00,
            'is_huenics_owned' => true,
            'is_active' => true,
        ]);

        // Initially no stock
        $this->assertEquals(0, (float) ($product->inventoryItem?->quantity_on_hand ?? 0));

        // Add 75 pcs of stock
        $service = app(InventoryService::class);
        $transaction = $service->addStock(
            product: $product,
            quantity: 75.0,
            type: 'purchase_in',
            notes: 'Restocked 75 units from supplier batch',
            reference: 'DR-99218',
            user: $this->admin
        );

        $this->assertInstanceOf(InventoryTransaction::class, $transaction);
        $this->assertEquals(75.0, (float) $transaction->quantity);
        $this->assertEquals('purchase_in', $transaction->transaction_type);

        // Verify inventory item on hand balance
        $product->refresh();
        $this->assertEquals(75.0, (float) $product->inventoryItem->quantity_on_hand);

        // Verify activity log entry
        $log = AuditLog::where('auditable_type', Product::class)
            ->where('auditable_id', $product->id)
            ->where('event', AuditLog::EVENT_STOCK_ADDED)
            ->latest('id')
            ->first();

        $this->assertNotNull($log, 'AuditLog entry for stock_added must be created');
        $this->assertEquals($this->admin->id, $log->user_id);
        $this->assertEquals('stock_added', $log->action);
        $this->assertStringContainsString('C.O.B Downlight 15W Warm White', $log->description);
        $this->assertStringContainsString('75.00 pcs', $log->description);
        $this->assertStringContainsString('0.00 → 75.00', $log->description);

        // Verify attribute diff
        $this->assertEquals(0.0, (float) $log->old_value['quantity_on_hand']);
        $this->assertEquals(75.0, (float) $log->new_value['quantity_on_hand']);
        $this->assertEquals(75.0, (float) $log->new_value['quantity_changed']);
        $this->assertEquals('purchase_in', $log->new_value['transaction_type']);
        $this->assertEquals($product->id, $log->properties['product_id']);
    }

    public function test_subsequent_stock_additions_track_cumulative_balances_in_audit_trail(): void
    {
        $this->actingAs($this->opsManager);

        $product = Product::create([
            'canonical_name' => 'Philips LED Driver 40W Constant Current',
            'sku' => 'PHIL-DRV-40W',
            'unit_default' => 'units',
            'is_huenics_owned' => true,
            'is_active' => true,
        ]);

        $service = app(InventoryService::class);

        // First addition: 50 units
        $service->addStock(
            product: $product,
            quantity: 50.0,
            type: 'initial_stock',
            notes: 'Initial warehouse intake',
            reference: 'INT-01',
            user: $this->opsManager
        );

        // Second addition: 30 units
        $service->addStock(
            product: $product,
            quantity: 30.0,
            type: 'purchase_in',
            notes: 'Inbound PO delivery #5502',
            reference: 'PO-5502',
            user: $this->opsManager
        );

        $product->refresh();
        $this->assertEquals(80.0, (float) $product->inventoryItem->quantity_on_hand);

        // Second log check
        $secondLog = AuditLog::where('auditable_type', Product::class)
            ->where('auditable_id', $product->id)
            ->where('event', AuditLog::EVENT_STOCK_ADDED)
            ->latest('id')
            ->first();

        $this->assertNotNull($secondLog);
        $this->assertStringContainsString('50.00 → 80.00', $secondLog->description);
        $this->assertEquals(50.0, (float) $secondLog->old_value['quantity_on_hand']);
        $this->assertEquals(80.0, (float) $secondLog->new_value['quantity_on_hand']);
    }

    public function test_activity_log_resource_includes_stock_added_in_options_and_badges(): void
    {
        $this->actingAs($this->admin);

        $product = Product::create([
            'canonical_name' => 'Highbay UFO 150W IP65',
            'unit_default' => 'pcs',
            'is_huenics_owned' => true,
        ]);

        app(InventoryService::class)->addStock(
            product: $product,
            quantity: 20.0,
            type: 'purchase_in',
            notes: 'Urgent site delivery replenishment',
            user: $this->admin
        );

        $log = AuditLog::where('auditable_id', $product->id)
            ->where('event', AuditLog::EVENT_STOCK_ADDED)
            ->first();

        $this->assertNotNull($log);
        $this->assertStringContainsString('Product: Highbay UFO 150W IP65', $log->subject_name);
        $this->assertEquals('Product', $log->subject_type_label);
    }

    public function test_theme_toggle_has_single_icon_display_css_rules(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('html.dark #theme-icon-sun { display: inline-block !important; }', false);
        $response->assertSee('html.dark #theme-icon-moon { display: none !important; }', false);
        $response->assertSee('html:not(.dark) #theme-icon-sun { display: none !important; }', false);
        $response->assertSee('html:not(.dark) #theme-icon-moon { display: inline-block !important; }', false);
    }
}
