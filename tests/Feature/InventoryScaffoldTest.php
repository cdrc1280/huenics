<?php

namespace Tests\Feature;

use App\Models\InventoryItem;
use App\Models\InventoryTransaction;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryScaffoldTest extends TestCase
{
    use RefreshDatabase;

    public function test_huenics_owned_product_inventory_tracking(): void
    {
        $user = User::factory()->create();

        // 1. Huenics owned product
        $huenicsProduct = Product::create([
            'canonical_name' => 'Proprietary PVC Joint 2"',
            'unit_default' => 'pcs',
            'is_huenics_owned' => true,
        ]);

        $item = InventoryItem::create([
            'product_id' => $huenicsProduct->id,
            'quantity_on_hand' => 100,
            'quantity_reserved' => 20,
            'unit' => 'pcs',
        ]);

        $this->assertEquals(80.0, $item->quantity_available);

        // 2. Inventory transaction
        $movement = InventoryTransaction::create([
            'inventory_item_id' => $item->id,
            'transaction_type' => 'purchase_in',
            'reference_type' => 'document',
            'reference_id' => 1,
            'quantity' => 50,
            'notes' => 'Received from shipment batch #1',
            'performed_by' => $user->id,
        ]);

        $this->assertEquals(1, $item->movements()->count());
        $this->assertEquals('purchase_in', $movement->transaction_type);
    }
}
