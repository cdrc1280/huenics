<?php

namespace Tests\Feature;

use App\Enums\DeliveryReceiptStatus;
use App\Enums\SalesInvoiceStatus;
use App\Models\DeliveryReceipt;
use App\Models\Document;
use App\Models\InventoryItem;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderLineItem;
use App\Models\Quotation;
use App\Models\SalesInvoice;
use App\Models\User;
use App\Services\InventoryService;
use App\Services\QuotationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PoInventoryAndSalesIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected User $agent;
    protected Product $product;
    protected InventoryItem $inventoryItem;

    protected function setUp(): void
    {
        parent::setUp();

        $this->agent = User::factory()->create([
            'role' => User::ROLE_SALES_EXECUTIVE,
            'name' => 'Agent John',
        ]);

        $this->product = Product::create([
            'product_code' => 'LED-DWN-001',
            'canonical_name' => '7W LED COB Down Light',
            'unit_default' => 'pcs',
            'default_price' => 1730.94,
            'base_cost_price' => 1200.00,
            'is_huenics_owned' => true,
        ]);

        $this->inventoryItem = InventoryItem::create([
            'product_id' => $this->product->id,
            'quantity_on_hand' => 1000,
            'quantity_reserved' => 0,
            'reorder_point' => 100,
            'unit' => 'pcs',
        ]);
    }

    public function test_purchase_order_creation_auto_deducts_inventory_stock(): void
    {
        $initialStock = (float) $this->inventoryItem->quantity_on_hand;
        $this->assertEquals(1000, $initialStock);

        $po = PurchaseOrder::create([
            'po_number' => 'PO-TEST-001',
            'sales_agent_id' => $this->agent->id,
            'customer_name' => 'MGS Construction',
            'order_amount' => 865470.00,
            'total_cost' => 600000.00,
            'realized_profit' => 265470.00,
            'order_date' => now()->toDateString(),
            'status' => PurchaseOrder::STATUS_APPROVED,
        ]);

        $po->lineItems()->create([
            'line_no' => 1,
            'product_id' => $this->product->id,
            'description' => '7W LED COB Down Light',
            'qty' => 500,
            'unit' => 'pcs',
            'unit_price' => 1730.94,
            'line_total' => 865470.00,
        ]);

        // Trigger deduction via service
        app(InventoryService::class)->deductPurchaseOrderStock($po);

        $this->inventoryItem->refresh();
        $this->assertEquals(500, (float) $this->inventoryItem->quantity_on_hand);
        $this->assertTrue($po->fresh()->is_inventory_deducted);

        $this->assertDatabaseHas('inventory_transactions', [
            'inventory_item_id' => $this->inventoryItem->id,
            'transaction_type' => 'sales_out',
            'quantity' => 500,
            'reference_id' => $po->id,
        ]);
    }

    public function test_cancelling_purchase_order_restores_inventory_stock(): void
    {
        $po = PurchaseOrder::create([
            'po_number' => 'PO-TEST-002',
            'sales_agent_id' => $this->agent->id,
            'customer_name' => 'MGS Construction',
            'order_amount' => 346188.00,
            'order_date' => now()->toDateString(),
            'status' => PurchaseOrder::STATUS_APPROVED,
        ]);

        $po->lineItems()->create([
            'line_no' => 1,
            'product_id' => $this->product->id,
            'description' => '7W LED COB Down Light',
            'qty' => 200,
            'unit' => 'pcs',
            'unit_price' => 1730.94,
            'line_total' => 346188.00,
        ]);

        app(InventoryService::class)->deductPurchaseOrderStock($po);
        $this->assertEquals(800, (float) $this->inventoryItem->fresh()->quantity_on_hand);

        // Cancel PO
        $po->update(['status' => PurchaseOrder::STATUS_CANCELLED]);

        $this->assertEquals(1000, (float) $this->inventoryItem->fresh()->quantity_on_hand);
        $this->assertFalse($po->fresh()->is_inventory_deducted);
    }

    public function test_quotation_conversion_to_po_auto_reflects_in_sales_and_deducts_inventory(): void
    {
        $quotation = Quotation::create([
            'quotation_number' => 'QT-2026-TEST',
            'sales_agent_id' => $this->agent->id,
            'customer_name' => 'Acme Corp',
            'total_amount' => 173094.00,
            'total_cost' => 120000.00,
            'estimated_profit' => 53094.00,
            'status' => Quotation::STATUS_APPROVED,
            'quotation_date' => now()->toDateString(),
        ]);

        $quotation->lineItems()->create([
            'line_no' => 1,
            'product_id' => $this->product->id,
            'description' => '7W LED COB Down Light',
            'qty' => 100,
            'unit' => 'pcs',
            'unit_price' => 1730.94,
            'line_total' => 173094.00,
        ]);

        $po = app(QuotationService::class)->convertToPO($quotation);

        $this->assertInstanceOf(PurchaseOrder::class, $po);
        $this->assertEquals(Quotation::STATUS_CONVERTED, $quotation->fresh()->status);
        $this->assertEquals(1, $po->lineItems()->count());

        // Inventory is not deducted yet (waiting for DR & SI fulfillment)
        $this->inventoryItem->refresh();
        $this->assertEquals(1000, (float) $this->inventoryItem->quantity_on_hand);

        // Fulfill with DR & SI
        app(\App\Services\OrderFulfillmentService::class)->fulfillOrder($po, [
            'dr_number' => 'DR-TEST-001',
            'si_number' => 'SI-TEST-001',
            'delivery_date' => now()->toDateString(),
        ], $this->agent);

        // Now inventory is deducted
        $this->inventoryItem->refresh();
        $this->assertEquals(900, (float) $this->inventoryItem->quantity_on_hand);
        $this->assertTrue($po->fresh()->isCompleted());
    }

    public function test_delivery_receipt_and_sales_invoice_generation(): void
    {
        $po = PurchaseOrder::create([
            'po_number' => 'PO-TEST-DRSI',
            'sales_agent_id' => $this->agent->id,
            'customer_name' => 'Palanza Tower Project',
            'order_amount' => 1050000.00,
            'order_date' => now()->toDateString(),
            'status' => PurchaseOrder::STATUS_APPROVED,
        ]);

        $po->lineItems()->create([
            'line_no' => 1,
            'product_id' => $this->product->id,
            'description' => '7W LED COB Down Light',
            'qty' => 500,
            'unit' => 'pcs',
            'unit_price' => 1730.94,
            'line_total' => 865470.00,
        ]);

        // 1. Delivery Receipt
        $dr = DeliveryReceipt::create([
            'dr_number' => DeliveryReceipt::generateNumber(),
            'purchase_order_id' => $po->id,
            'delivery_date' => now(),
            'delivered_by' => 'Driver Alex',
            'received_by' => 'Engr. Smith',
            'status' => DeliveryReceiptStatus::Delivered->value,
        ]);

        $dr->items()->create([
            'product_id' => $this->product->id,
            'description' => '7W LED COB Down Light',
            'qty_delivered' => 500,
            'unit' => 'pcs',
        ]);

        $this->assertEquals(1, $dr->items()->count());
        $this->assertEquals('delivered', $dr->status);

        // 2. Sales Invoice
        $si = SalesInvoice::create([
            'si_number' => SalesInvoice::generateNumber(),
            'purchase_order_id' => $po->id,
            'delivery_receipt_id' => $dr->id,
            'customer_name' => 'Palanza Tower Project',
            'invoice_date' => now(),
            'subtotal' => 865470.00,
            'vat_amount' => 103856.40,
            'total_amount' => 969326.40,
            'payment_status' => SalesInvoiceStatus::Paid->value,
            'payment_date' => now(),
        ]);

        $si->items()->create([
            'product_id' => $this->product->id,
            'description' => '7W LED COB Down Light',
            'qty' => 500,
            'unit' => 'pcs',
            'unit_price' => 1730.94,
            'line_total' => 865470.00,
        ]);

        $this->assertEquals('paid', $si->payment_status);
        $this->assertEquals(969326.40, (float) $si->total_amount);
    }
}
