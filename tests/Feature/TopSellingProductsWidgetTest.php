<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Widgets\TopSellingProductsWidget;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderLineItem;
use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class TopSellingProductsWidgetTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleAndPermissionSeeder::class);
        $this->admin = User::where('email', 'admin@huenics.com')->first();
    }

    public function test_top_selling_products_widget_displays_up_to_10_products(): void
    {
        $this->actingAs($this->admin);

        // Create a Purchase Order in September 2026
        $po = PurchaseOrder::create([
            'po_number' => 'PO-2026-SEP-TOP10',
            'order_date' => '2026-09-06',
            'sales_agent_id' => $this->admin->id,
            'customer_name' => 'Metro Manila Construction Corp',
            'order_amount' => 500000.00,
            'realized_profit' => 150000.00,
            'status' => PurchaseOrder::STATUS_APPROVED,
            'delivery_status' => PurchaseOrder::DELIVERY_PENDING,
            'is_completed' => false,
            'is_conforme_po' => false,
        ]);

        // Create 12 distinct products and line items with descending revenue
        for ($i = 1; $i <= 12; $i++) {
            $product = Product::create([
                'product_code' => sprintf('PROD-SEP-%02d', $i),
                'sku' => sprintf('SKU-SEP-%02d', $i),
                'canonical_name' => sprintf('Industrial Component Item %02d High Precision', $i),
                'price' => (float) ($i * 1000),
                'stock_quantity' => 100,
            ]);

            PurchaseOrderLineItem::create([
                'purchase_order_id' => $po->id,
                'product_id' => $product->id,
                'line_no' => $i,
                'item_code' => $product->product_code,
                'description' => $product->canonical_name,
                'qty' => 10,
                'unit' => 'PC',
                'unit_price' => (float) ($i * 1000),
                'line_total' => (float) ($i * 1000 * 10), // $10,000 to $120,000
            ]);
        }

        $widget = Livewire::test(TopSellingProductsWidget::class, [
            'periodType' => 'month',
            'selectedYear' => 2026,
            'selectedMonth' => 9,
            'selectedAgentId' => $this->admin->id,
        ])
            ->assertSuccessful();

        $this->assertStringContainsString('September 2026', $widget->instance()->getDescription());

        $chartData = $widget->instance()->getData();

        // Must display exactly 10 top products
        $this->assertCount(10, $chartData['labels']);
        $this->assertCount(10, $chartData['datasets'][0]['data']);
        $this->assertCount(10, $chartData['datasets'][0]['backgroundColor']);

        // Highest revenue product should be Item 12 (120,000)
        $this->assertEquals(120000.00, $chartData['datasets'][0]['data'][0]);
        // 10th product should be Item 03 (30,000)
        $this->assertEquals(30000.00, $chartData['datasets'][0]['data'][9]);
    }
}
