<?php

namespace Tests\Feature;

use App\Filament\Resources\InventoryItemResource;
use App\Filament\Resources\ProductResource;
use App\Filament\Resources\ProductResource\RelationManagers\SubComponentsRelationManager;
use App\Models\InventoryItem;
use App\Models\Product;
use App\Models\ProductComponent;
use App\Models\User;
use Filament\Tables\Table;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductSubComponentsTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role'  => User::ROLE_ADMIN,
            'name'  => 'Admin User',
            'email' => 'admin@huenics.com',
        ]);
    }

    public function test_product_component_schema_and_model_persistence(): void
    {
        $parent = Product::create([
            'product_code'      => 'HISI-BULB-9W',
            'canonical_name'    => 'LED Bulb 9W Daylight E27',
            'category'          => 'LED BULB',
            'wattage'           => '9W',
            'voltage'           => '220V',
            'color_temperature' => '6500K',
            'unit_default'      => 'pcs',
            'selling_price'     => 185.00,
            'base_cost_price'   => 95.00,
            'is_huenics_owned'  => true,
            'is_composite'      => true,
            'is_active'         => true,
        ]);

        $component = ProductComponent::create([
            'parent_product_id' => $parent->id,
            'component_group'   => 'Base Socket',
            'option_name'       => 'E27 Aluminum Base',
            'product_code'      => 'BASE-E27-AL',
            'component_name'    => 'E27 Aluminum Screw Base Cap',
            'category'          => 'Socket Hardware',
            'wattage'           => null,
            'voltage'           => '220V',
            'color_temperature' => null,
            'unit'              => 'pcs',
            'cost_price'        => 12.50,
            'quantity'          => 1.0000,
            'image_path'        => 'products/components/e27-base.jpg',
            'notes'             => 'Standard nickel-plated aluminum screw thread',
            'is_default'        => true,
        ]);

        $this->assertDatabaseHas('product_components', [
            'id'                => $component->id,
            'parent_product_id' => $parent->id,
            'product_code'      => 'BASE-E27-AL',
            'component_name'    => 'E27 Aluminum Screw Base Cap',
            'category'          => 'Socket Hardware',
            'voltage'           => '220V',
            'unit'              => 'pcs',
            'cost_price'        => 12.50,
            'quantity'          => 1.0000,
        ]);

        $this->assertEquals('BASE-E27-AL', $component->effective_code);
        $this->assertEquals('E27 Aluminum Screw Base Cap', $component->effective_name);
        $this->assertEquals('Socket Hardware', $component->effective_category);
        $this->assertEquals('220V', $component->effective_voltage);
        $this->assertEquals(12.50, $component->effective_cost);
        $this->assertEquals(12.50, $component->total_cost);
    }

    public function test_component_inherits_specs_from_linked_catalog_product(): void
    {
        $parent = Product::create([
            'product_code'   => 'HISI-BULB-12W',
            'canonical_name' => 'LED Bulb 12W Warm White',
            'unit_default'   => 'pcs',
            'selling_price'  => 220.00,
        ]);

        $driverCatalog = Product::create([
            'product_code'      => 'DRV-12W-CC',
            'canonical_name'    => 'Constant Current LED Driver 12W',
            'category'          => 'LED Drivers',
            'wattage'           => '12W',
            'voltage'           => 'DC12V',
            'color_temperature' => null,
            'unit_default'      => 'pcs',
            'base_cost_price'   => 65.00,
            'selling_price'     => 95.00,
            'image_path'        => 'products/driver-12w.jpg',
            'is_huenics_owned'  => true,
        ]);

        // Create inventory for the driver catalog part
        InventoryItem::create([
            'product_id'       => $driverCatalog->id,
            'quantity_on_hand' => 150.00,
            'unit'             => 'pcs',
            'location'         => 'Rack A-2',
        ]);

        $component = ProductComponent::create([
            'parent_product_id'    => $parent->id,
            'component_product_id' => $driverCatalog->id,
            'quantity'             => 2.0000,
            'is_default'           => true,
        ]);

        // Specs should be dynamically inherited from the linked catalog product
        $this->assertEquals('DRV-12W-CC', $component->effective_code);
        $this->assertEquals('Constant Current LED Driver 12W', $component->effective_name);
        $this->assertEquals('LED Drivers', $component->effective_category);
        $this->assertEquals('12W', $component->effective_wattage);
        $this->assertEquals('DC12V', $component->effective_voltage);
        $this->assertEquals('pcs', $component->effective_unit);
        $this->assertEquals(65.00, $component->effective_cost);
        $this->assertEquals('products/driver-12w.jpg', $component->effective_image);
        $this->assertEquals(150.00, $component->stock_on_hand);
        // 2 units * ₱65.00 = ₱130.00
        $this->assertEquals(130.00, $component->total_cost);
    }

    public function test_parent_product_calculates_total_bom_assembly_cost(): void
    {
        $bulb = Product::create([
            'product_code'   => 'HISI-BULB-COMPLETE',
            'canonical_name' => 'Complete Modular Bulb Assembly',
            'unit_default'   => 'pcs',
            'selling_price'  => 350.00,
            'is_composite'   => true,
        ]);

        // Component 1: Custom part (Socket: 1 pc @ ₱15.00)
        ProductComponent::create([
            'parent_product_id' => $bulb->id,
            'component_name'    => 'Aluminum Socket',
            'cost_price'        => 15.00,
            'quantity'          => 1.0000,
        ]);

        // Component 2: Catalog driver (1 pc @ ₱80.00)
        $driver = Product::create([
            'product_code'    => 'DRV-BOM',
            'canonical_name'  => 'Isolated Driver',
            'base_cost_price' => 80.00,
            'unit_default'    => 'pcs',
        ]);
        ProductComponent::create([
            'parent_product_id'    => $bulb->id,
            'component_product_id' => $driver->id,
            'quantity'             => 1.0000,
        ]);

        // Component 3: Custom LED Chipboard (2 pcs @ ₱45.00 each = ₱90.00)
        ProductComponent::create([
            'parent_product_id' => $bulb->id,
            'component_name'    => 'SMD Chip Plate',
            'cost_price'        => 45.00,
            'quantity'          => 2.0000,
        ]);

        // Component 4: Glass Diffuser (1 pc @ ₱25.00)
        ProductComponent::create([
            'parent_product_id' => $bulb->id,
            'component_name'    => 'Frosted Glass Diffuser',
            'cost_price'        => 25.00,
            'quantity'          => 1.0000,
        ]);

        $bulb = $bulb->fresh();

        $this->assertTrue($bulb->has_sub_components);
        $this->assertEquals(4, $bulb->components_count);
        // Total BOM cost: 15 + 80 + (2 * 45 = 90) + 25 = 210.00
        $this->assertEquals(210.00, $bulb->total_bom_cost);
    }

    public function test_product_resource_registers_sub_components_relation_manager(): void
    {
        $relations = ProductResource::getRelations();

        $this->assertContains(SubComponentsRelationManager::class, $relations);
    }

    public function test_inventory_item_resource_does_not_have_adjust_stock_action(): void
    {
        $this->assertFalse(
            method_exists(InventoryItemResource::class, 'getAdjustStockAction'),
            'InventoryItemResource must NOT have getAdjustStockAction method. Adding stock belongs solely in ProductResource.'
        );

        $this->assertTrue(
            method_exists(ProductResource::class, 'getAddStockAction'),
            'ProductResource must have getAddStockAction as the Single Source of Truth.'
        );
    }

    public function test_sub_components_relation_manager_form_schema_builds_without_missing_classes(): void
    {
        $relationManager = new SubComponentsRelationManager();
        $schema = \Filament\Schemas\Schema::make($relationManager);
        
        $configuredSchema = $relationManager->form($schema);
        $components = $configuredSchema->getComponents();

        $this->assertNotEmpty($components, 'SubComponentsRelationManager form schema must contain components');
        $this->assertInstanceOf(\Filament\Schemas\Components\Section::class, $components[0]);
    }

    public function test_inventory_items_table_renders_cleanly_with_null_dates(): void
    {
        $product = Product::create([
            'product_code'   => 'HISI-TEST-NULL-DATE',
            'canonical_name' => 'LED Test Null Date Product',
            'unit_default'   => 'pcs',
            'selling_price'  => 100.00,
        ]);

        $inventoryItem = InventoryItem::create([
            'product_id'        => $product->id,
            'quantity_on_hand'  => 50,
            'reorder_point'     => null,
            'inbound_date'      => null,
            'date_released'     => null,
            'unit'              => 'pcs',
        ]);

        $this->actingAs($this->admin);

        $response = $this->get('/admin/inventory-items');
        $response->assertSuccessful();
    }
}
