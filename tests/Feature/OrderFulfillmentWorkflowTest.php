<?php

namespace Tests\Feature;

use App\Enums\DeliveryStatus;
use App\Enums\DocumentType;
use App\Models\DeliveryReceipt;
use App\Models\Document;
use App\Models\InventoryItem;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\SalesInvoice;
use App\Models\Transaction;
use App\Models\User;
use App\Services\OrderFulfillmentService;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class OrderFulfillmentWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $agent;
    protected Product $product;
    protected InventoryItem $inventoryItem;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'name' => 'Admin User',
            'email' => 'admin@huenics.com',
        ]);

        $this->agent = User::factory()->create([
            'role' => User::ROLE_SALES_EXECUTIVE,
            'name' => 'Sales Agent John',
            'email' => 'agent.john@huenics.com',
        ]);

        $this->product = Product::create([
            'product_code' => 'LED-DWN-101',
            'canonical_name' => '12W LED Commercial Down Light',
            'unit_default' => 'pcs',
            'default_price' => 2500.00,
            'base_cost_price' => 1750.00,
            'is_huenics_owned' => true,
        ]);

        $this->inventoryItem = InventoryItem::create([
            'product_id' => $this->product->id,
            'quantity_on_hand' => 500,
            'quantity_reserved' => 0,
            'reorder_point' => 50,
            'unit' => 'pcs',
        ]);
    }

    public function test_po_cannot_be_marked_as_delivered_without_dr_and_si_attached(): void
    {
        $po = PurchaseOrder::create([
            'po_number' => 'PO-FULFILL-001',
            'sales_agent_id' => $this->agent->id,
            'customer_name' => 'Megawide Construction',
            'order_amount' => 250000.00,
            'total_cost' => 175000.00,
            'realized_profit' => 75000.00,
            'order_date' => now()->toDateString(),
            'status' => PurchaseOrder::STATUS_APPROVED,
            'delivery_status' => PurchaseOrder::DELIVERY_PENDING,
        ]);

        $po->lineItems()->create([
            'line_no' => 1,
            'product_id' => $this->product->id,
            'description' => '12W LED Commercial Down Light',
            'qty' => 100,
            'unit' => 'pcs',
            'unit_price' => 2500.00,
            'line_total' => 250000.00,
        ]);

        // Attempt to prematurely mark the PO as delivered directly without DR & SI attached
        $po->update([
            'delivery_status' => PurchaseOrder::DELIVERY_DELIVERED,
            'status' => PurchaseOrder::STATUS_DELIVERED,
            'actual_delivery_date' => now()->toDateString(),
        ]);

        $fresh = $po->fresh();

        // PO is guarded: delivery_status CANNOT be 'delivered' without DR & SI
        $this->assertNotEquals(PurchaseOrder::DELIVERY_DELIVERED, $fresh->delivery_status);
        $this->assertNotEquals(PurchaseOrder::STATUS_DELIVERED, $fresh->status);
        $this->assertFalse($fresh->is_completed);
        $this->assertFalse($fresh->is_inventory_deducted);

        // Stock MUST remain unchanged (500)
        $this->inventoryItem->refresh();
        $this->assertEquals(500, (float) $this->inventoryItem->quantity_on_hand);
    }

    public function test_uploading_dr_and_si_together_triggers_inventory_deduction_and_completes_order(): void
    {
        Storage::fake('local');

        $po = PurchaseOrder::create([
            'po_number' => 'PO-FULFILL-002',
            'sales_agent_id' => $this->agent->id,
            'customer_name' => 'Megawide Construction',
            'order_amount' => 250000.00,
            'total_cost' => 175000.00,
            'realized_profit' => 75000.00,
            'order_date' => now()->toDateString(),
            'status' => PurchaseOrder::STATUS_APPROVED,
            'delivery_status' => PurchaseOrder::DELIVERY_PENDING,
        ]);

        $po->lineItems()->create([
            'line_no' => 1,
            'product_id' => $this->product->id,
            'description' => '12W LED Commercial Down Light',
            'qty' => 100,
            'unit' => 'pcs',
            'unit_price' => 2500.00,
            'line_total' => 250000.00,
        ]);

        // Create fake DR image and SI PDF
        $drImage = UploadedFile::fake()->image('dr_receipt_scan.png');
        $siPdf = UploadedFile::fake()->create('sales_invoice_doc.pdf', 500, 'application/pdf');

        $drPath = $drImage->store('documents/dr', 'local');
        $siPath = $siPdf->store('documents/si', 'local');

        // Execute fulfillment via OrderFulfillmentService (Attaching DR & SI)
        $service = app(OrderFulfillmentService::class);
        $result = $service->fulfillOrder($po, [
            'dr_file' => $drPath,
            'dr_number' => 'DR-2026-0099',
            'delivery_date' => now()->toDateString(),
            'delivered_by' => 'Driver Leo',
            'received_by' => 'Engr. Ramos',
            'si_file' => $siPath,
            'si_number' => 'SI-2026-0099',
            'invoice_date' => now()->toDateString(),
            'payment_status' => SalesInvoice::STATUS_PAID,
            'total_amount' => 250000.00,
        ], $this->admin);

        // 1. Verify PO is completed AND marked delivered
        $poFresh = $po->fresh();
        $this->assertTrue($poFresh->is_completed);
        $this->assertTrue($poFresh->isCompleted());
        $this->assertTrue($poFresh->is_inventory_deducted);
        $this->assertEquals(PurchaseOrder::DELIVERY_DELIVERED, $poFresh->delivery_status);
        $this->assertEquals(PurchaseOrder::STATUS_DELIVERED, $poFresh->status);
        $this->assertEquals('DR-2026-0099', $poFresh->delivery_receipt_no);

        // 2. Verify stock is deducted
        $this->inventoryItem->refresh();
        $this->assertEquals(400, (float) $this->inventoryItem->quantity_on_hand);

        // 3. Verify Delivery Receipt record & document
        $this->assertDatabaseHas('delivery_receipts', [
            'dr_number' => 'DR-2026-0099',
            'purchase_order_id' => $po->id,
            'status' => DeliveryReceipt::STATUS_DELIVERED,
        ]);
        $this->assertDatabaseHas('documents', [
            'document_type' => DocumentType::DeliveryReceipt->value,
            'document_number' => 'DR-2026-0099',
        ]);

        // 4. Verify Sales Invoice record & document
        $this->assertDatabaseHas('sales_invoices', [
            'si_number' => 'SI-2026-0099',
            'purchase_order_id' => $po->id,
            'total_amount' => 250000.00,
            'payment_status' => 'paid',
        ]);
        $this->assertDatabaseHas('documents', [
            'document_type' => DocumentType::SalesInvoice->value,
            'document_number' => 'SI-2026-0099',
        ]);

        // 5. Verify master Transaction is completed
        $this->assertDatabaseHas('transactions', [
            'purchase_order_id' => $po->id,
            'is_completed' => true,
            'status' => Transaction::STATUS_DELIVERED,
        ]);
    }

    public function test_sales_dashboard_and_widgets_only_count_completed_fulfilled_pos(): void
    {
        // 1. Unfulfilled PO (Pending, not completed with DR+SI)
        $unfulfilledPo = PurchaseOrder::create([
            'po_number' => 'PO-UNFULFILLED',
            'sales_agent_id' => $this->agent->id,
            'customer_name' => 'Pending Client',
            'order_amount' => 100000.00,
            'total_cost' => 70000.00,
            'realized_profit' => 30000.00,
            'order_date' => now()->toDateString(),
            'status' => PurchaseOrder::STATUS_APPROVED,
            'delivery_status' => PurchaseOrder::DELIVERY_PENDING,
            'is_completed' => false,
        ]);

        // 2. Fulfilled PO (DR + SI uploaded, is_completed = true)
        $fulfilledPo = PurchaseOrder::create([
            'po_number' => 'PO-FULFILLED',
            'sales_agent_id' => $this->agent->id,
            'customer_name' => 'Completed Client',
            'order_amount' => 300000.00,
            'total_cost' => 210000.00,
            'realized_profit' => 90000.00,
            'order_date' => now()->toDateString(),
            'status' => PurchaseOrder::STATUS_DELIVERED,
            'delivery_status' => PurchaseOrder::DELIVERY_DELIVERED,
            'is_completed' => true,
            'completed_at' => now(),
        ]);

        // Sales achieved for agent should only sum completed POs (300,000, not 400,000)
        $completedSum = PurchaseOrder::where('sales_agent_id', $this->agent->id)
            ->where('is_completed', true)
            ->sum('order_amount');

        $this->assertEquals(300000.00, (float) $completedSum);

        $unfulfilledCount = PurchaseOrder::where('sales_agent_id', $this->agent->id)
            ->where('is_completed', false)
            ->count();

        $this->assertEquals(1, $unfulfilledCount);
    }

    public function test_attaching_dr_and_si_first_and_then_marking_delivered_in_separate_steps(): void
    {
        Storage::fake('local');

        $po = PurchaseOrder::create([
            'po_number' => 'PO-FULFILL-003',
            'sales_agent_id' => $this->agent->id,
            'customer_name' => 'Ayala Land Inc',
            'order_amount' => 500000.00,
            'total_cost' => 350000.00,
            'realized_profit' => 150000.00,
            'order_date' => now()->toDateString(),
            'status' => PurchaseOrder::STATUS_APPROVED,
            'delivery_status' => PurchaseOrder::DELIVERY_PENDING,
        ]);

        $po->lineItems()->create([
            'line_no' => 1,
            'product_id' => $this->product->id,
            'description' => '12W LED Commercial Down Light',
            'qty' => 200,
            'unit' => 'pcs',
            'unit_price' => 2500.00,
            'line_total' => 500000.00,
        ]);

        $drImage = UploadedFile::fake()->image('dr_signed_copy.png');
        $siPdf = UploadedFile::fake()->create('si_signed_copy.pdf', 500, 'application/pdf');

        $drPath = $drImage->store('documents/dr', 'local');
        $siPath = $siPdf->store('documents/si', 'local');

        // Step 1: User uploads DR & SI without immediate delivery completion
        $service = app(OrderFulfillmentService::class);
        $service->attachFulfillmentDocuments($po, [
            'dr_file' => $drPath,
            'dr_number' => 'DR-2026-0300',
            'delivery_date' => now()->toDateString(),
            'si_file' => $siPath,
            'si_number' => 'SI-2026-0300',
            'invoice_date' => now()->toDateString(),
            'payment_status' => SalesInvoice::STATUS_PAID,
            'total_amount' => 500000.00,
        ], $this->admin);

        $po = $po->fresh();

        // DR & SI are verified and attached
        $this->assertTrue($po->hasBothDrAndSi());
        $this->assertFalse($po->is_completed);
        $this->assertEquals(PurchaseOrder::DELIVERY_PENDING, $po->delivery_status);

        // Inventory is NOT deducted yet
        $this->inventoryItem->refresh();
        $this->assertEquals(500, (float) $this->inventoryItem->quantity_on_hand);

        // Step 2: Now that DR & SI are attached, user clicks "Mark as Delivered"
        $service->completeDelivery($po, [
            'actual_delivery_date' => now()->toDateString(),
        ], $this->admin);

        $po = $po->fresh();

        // Now PO is marked delivered and completed
        $this->assertTrue($po->is_completed);
        $this->assertEquals(PurchaseOrder::DELIVERY_DELIVERED, $po->delivery_status);
        $this->assertEquals(PurchaseOrder::STATUS_DELIVERED, $po->status);

        // Inventory is now deducted
        $this->inventoryItem->refresh();
        $this->assertEquals(300, (float) $this->inventoryItem->quantity_on_hand);

        // Step 3: Verify AuditLog entries exist for the full lifecycle
        $this->assertDatabaseHas('audit_logs', [
            'auditable_type' => PurchaseOrder::class,
            'auditable_id'   => $po->id,
            'event'          => \App\Models\AuditLog::EVENT_DOCUMENTS_ATTACHED,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'auditable_type' => PurchaseOrder::class,
            'auditable_id'   => $po->id,
            'event'          => \App\Models\AuditLog::EVENT_DELIVERED,
        ]);
    }
}
