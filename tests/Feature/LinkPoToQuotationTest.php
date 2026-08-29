<?php

namespace Tests\Feature;

use App\Filament\Resources\PurchaseOrderResource;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderLineItem;
use App\Models\Quotation;
use App\Models\QuotationLineItem;
use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LinkPoToQuotationTest extends TestCase
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

        // Create an Approved Quotation
        $this->quotation = Quotation::create([
            'quotation_number' => 'HISI-Q-2026-0001',
            'sales_agent_id' => $this->admin->id,
            'customer_name' => 'Megawide Construction Corp',
            'customer_company' => 'Megawide Construction Corp',
            'project_name' => 'Clark Airport Terminal',
            'phone_no' => '09171234567',
            'total_amount' => 150000.00,
            'total_cost' => 100000.00,
            'status' => Quotation::STATUS_APPROVED,
            'quotation_date' => now(),
            'payment_terms' => 'COD / 50% DP ; 50% PDC 30 Days',
            'delivery_terms' => '4-7 days',
            'terms_and_conditions' => 'Standard warranty applies',
        ]);

        // Create an Unlinked Purchase Order
        $this->po = PurchaseOrder::create([
            'po_number' => 'PO-MEGA-2026-99',
            'sales_agent_id' => $this->admin->id,
            'customer_name' => 'Valued Customer',
            'order_amount' => 150000.00,
            'total_cost' => 100000.00,
            'status' => PurchaseOrder::STATUS_PENDING,
            'order_date' => now(),
        ]);
    }

    public function test_link_po_to_approved_quotation_syncs_details_and_updates_status(): void
    {
        $this->actingAs($this->admin);

        $action = PurchaseOrderResource::getLinkToQuotationAction();

        // Execute action callback with data
        ($action->getActionFunction())($this->po, [
            'quotation_id' => $this->quotation->id,
            'sync_missing_details' => true,
            'verify_line_items' => false,
        ]);

        $this->po->refresh();
        $this->quotation->refresh();

        // Verify PO linked to quotation
        $this->assertEquals($this->quotation->id, $this->po->quotation_id);
        $this->assertEquals('Megawide Construction Corp', $this->po->customer_name);
        $this->assertEquals('COD / 50% DP ; 50% PDC 30 Days', $this->po->payment_terms);
        $this->assertEquals('4-7 days', $this->po->delivery_terms);

        // Verify Quotation status converted
        $this->assertEquals(Quotation::STATUS_CONVERTED, $this->quotation->status);
    }

    public function test_link_po_cross_verifies_line_items(): void
    {
        $this->actingAs($this->admin);

        $product = Product::create([
            'canonical_name' => 'PVC Pipe 4 inch',
            'sku' => 'PVC-4-INCH',
            'default_price' => 500.00,
            'base_cost_price' => 350.00,
        ]);

        // Quotation has 100 pcs at 500.00
        QuotationLineItem::create([
            'quotation_id' => $this->quotation->id,
            'product_id' => $product->id,
            'description' => 'PVC Pipe 4 inch',
            'item_code' => 'PVC-4-INCH',
            'qty' => 100,
            'unit_price' => 500.00,
            'subtotal' => 50000.00,
        ]);

        // PO has differing qty: 80 pcs
        PurchaseOrderLineItem::create([
            'purchase_order_id' => $this->po->id,
            'product_id' => $product->id,
            'description' => 'PVC Pipe 4 inch',
            'item_code' => 'PVC-4-INCH',
            'qty' => 80,
            'unit_price' => 500.00,
            'subtotal' => 40000.00,
        ]);

        $action = PurchaseOrderResource::getLinkToQuotationAction();
        ($action->getActionFunction())($this->po, [
            'quotation_id' => $this->quotation->id,
            'sync_missing_details' => true,
            'verify_line_items' => true,
        ]);

        $this->po->refresh();
        $this->assertEquals($this->quotation->id, $this->po->quotation_id);
    }

    public function test_unlink_quotation_reverts_quotation_status(): void
    {
        $this->actingAs($this->admin);

        // First link
        $this->po->update(['quotation_id' => $this->quotation->id]);
        $this->quotation->update(['status' => Quotation::STATUS_CONVERTED]);

        $action = PurchaseOrderResource::getLinkToQuotationAction();
        ($action->getActionFunction())($this->po, [
            'quotation_id' => null, // Clear/unlink
        ]);

        $this->po->refresh();
        $this->quotation->refresh();

        $this->assertNull($this->po->quotation_id);
        $this->assertEquals(Quotation::STATUS_APPROVED, $this->quotation->status);
    }

    public function test_normal_unlinked_po_disables_approve_and_review_actions(): void
    {
        $this->actingAs($this->admin);

        // Normal PO, unlinked
        $this->assertFalse((bool) $this->po->is_conforme_po);
        $this->assertNull($this->po->quotation_id);

        $tableActions = collect(PurchaseOrderResource::getTableActions())
            ->first(fn($a) => $a instanceof \Filament\Actions\ActionGroup)
            ->getActions();

        $approveAction = collect($tableActions)->first(fn($a) => $a->getName() === 'approve_po');
        $reviewAction = collect($tableActions)->first(fn($a) => $a->getName() === 'review');

        $approveAction->record($this->po);
        $reviewAction->record($this->po);

        $this->assertTrue($approveAction->isDisabled());
        $this->assertTrue($reviewAction->isDisabled());
        $this->assertTrue($approveAction->isHidden());
        $this->assertTrue($reviewAction->isHidden());

        // Calling action callback directly should reject approval
        ($approveAction->getActionFunction())($this->po);
        $this->po->refresh();
        $this->assertNotEquals(PurchaseOrder::STATUS_APPROVED, $this->po->status);
    }

    public function test_conforme_po_enables_approve_and_review_without_quotation(): void
    {
        $this->actingAs($this->admin);

        $this->po->update(['is_conforme_po' => true]);

        $tableActions = collect(PurchaseOrderResource::getTableActions())
            ->first(fn($a) => $a instanceof \Filament\Actions\ActionGroup)
            ->getActions();

        $approveAction = collect($tableActions)->first(fn($a) => $a->getName() === 'approve_po');
        $reviewAction = collect($tableActions)->first(fn($a) => $a->getName() === 'review');

        $approveAction->record($this->po);
        $reviewAction->record($this->po);

        $this->assertFalse($approveAction->isDisabled());
        $this->assertFalse($reviewAction->isDisabled());
        $this->assertTrue($approveAction->isVisible());
        $this->assertTrue($reviewAction->isVisible());

        ($approveAction->getActionFunction())($this->po);
        $this->po->refresh();
        $this->assertEquals(PurchaseOrder::STATUS_APPROVED, $this->po->status);
    }

    public function test_linked_normal_po_enables_approve_and_review(): void
    {
        $this->actingAs($this->admin);

        $this->po->update([
            'is_conforme_po' => false,
            'quotation_id' => $this->quotation->id,
        ]);

        $tableActions = collect(PurchaseOrderResource::getTableActions())
            ->first(fn($a) => $a instanceof \Filament\Actions\ActionGroup)
            ->getActions();

        $approveAction = collect($tableActions)->first(fn($a) => $a->getName() === 'approve_po');
        $reviewAction = collect($tableActions)->first(fn($a) => $a->getName() === 'review');

        $approveAction->record($this->po);
        $reviewAction->record($this->po);

        $this->assertFalse($approveAction->isDisabled());
        $this->assertFalse($reviewAction->isDisabled());
        $this->assertTrue($approveAction->isVisible());
        $this->assertTrue($reviewAction->isVisible());

        ($approveAction->getActionFunction())($this->po);
        $this->po->refresh();
        $this->assertEquals(PurchaseOrder::STATUS_APPROVED, $this->po->status);
    }

    public function test_review_queue_page_detects_unlinked_normal_po(): void
    {
        $this->actingAs($this->admin);

        // Document for PO
        $doc = \App\Models\Document::create([
            'document_type' => \App\Models\Document::TYPE_PURCHASE_ORDER,
            'document_number' => 'PO-MEGA-2026-99',
            'original_filename' => 'test.pdf',
            'disk_path' => 'documents/test.pdf',
            'file_hash' => hash('sha256', 'po_test_document_content'),
            'status' => \App\Models\Document::STATUS_REQUIRES_REVIEW,
            'uploaded_by' => $this->admin->id,
        ]);

        $this->po->update(['document_id' => $doc->id, 'is_conforme_po' => false, 'quotation_id' => null]);

        $page = new \App\Filament\Pages\ReviewQueuePage();
        $page->currentDocument = $doc;

        // Must detect unlinked normal PO
        $this->assertTrue($page->getIsUnlinkedNormalPoProperty());

        // Conforme PO should not be flagged as unlinked normal PO
        $this->po->update(['is_conforme_po' => true]);
        $this->assertFalse($page->getIsUnlinkedNormalPoProperty());

        // Linked PO should not be flagged
        $this->po->update(['is_conforme_po' => false, 'quotation_id' => $this->quotation->id]);
        $this->assertFalse($page->getIsUnlinkedNormalPoProperty());
    }

    public function test_connected_quotation_and_po_type_columns_render_correctly(): void
    {
        $this->actingAs($this->admin);

        // 1. Normal unlinked PO
        $this->po->update(['is_conforme_po' => false, 'quotation_id' => null]);
        $listUrl = PurchaseOrderResource::getUrl('index');
        $response = $this->get($listUrl);
        $response->assertSuccessful();
        $response->assertSee('Normal PO');
        $response->assertSee('Not Linked');

        // 2. Conforme PO
        $this->po->update(['is_conforme_po' => true, 'quotation_id' => null]);
        $response = $this->get($listUrl);
        $response->assertSuccessful();
        $response->assertSee('Conforme PO');
        $response->assertSee('Conforme PO (No Quotation)');

        // 3. Normal linked PO
        $this->po->update(['is_conforme_po' => false, 'quotation_id' => $this->quotation->id]);
        $response = $this->get($listUrl);
        $response->assertSuccessful();
        $response->assertSee($this->quotation->quotation_number);
    }

    public function test_reconciliation_detects_exact_match_and_discrepancies(): void
    {
        $this->actingAs($this->admin);

        // Setup Quotation line items
        $this->quotation->lineItems()->create([
            'line_no' => 1,
            'item_code' => 'LED-100',
            'description' => '100W LED Floodlight',
            'qty' => 50,
            'unit' => 'pcs',
            'unit_price' => 1000.00,
            'discounted_price' => 900.00,
            'line_total' => 45000.00,
        ]);

        // Scenario A: Exact match PO line item
        $poLine = $this->po->lineItems()->create([
            'line_no' => 1,
            'item_code' => 'LED-100',
            'description' => '100W LED Floodlight',
            'qty' => 50,
            'unit' => 'pcs',
            'unit_price' => 900.00,
            'line_total' => 45000.00,
        ]);

        $this->po->update([
            'is_conforme_po' => false,
            'quotation_id' => $this->quotation->id,
            'order_amount' => 45000.00,
        ]);

        $report = $this->po->getReconciliationReport();
        $this->assertTrue($report['has_linked_quotation']);
        $this->assertFalse($report['has_discrepancies']);
        $this->assertEquals(1, $report['exact_matches_count']);
        $this->assertEquals(0, $report['discrepancy_count']);

        // Scenario B: Quantity and Price Discrepancy
        $poLine->update([
            'qty' => 60, // Mismatch: 60 vs 50
            'unit_price' => 1200.00, // Mismatch: 1200 vs 900
            'line_total' => 72000.00,
        ]);

        // Add an extra PO line item not in quotation
        $this->po->lineItems()->create([
            'line_no' => 2,
            'item_code' => 'CBL-50',
            'description' => '50m Heavy Duty Cable',
            'qty' => 10,
            'unit' => 'rolls',
            'unit_price' => 2500.00,
            'line_total' => 25000.00,
        ]);

        $this->po->update(['order_amount' => 97000.00]);

        $reportWithDiscrepancy = $this->po->getReconciliationReport();
        $this->assertTrue($reportWithDiscrepancy['has_discrepancies']);
        $this->assertGreaterThan(0, $reportWithDiscrepancy['discrepancy_count']);
        $this->assertEquals(1, $reportWithDiscrepancy['qty_mismatches_count']);
        $this->assertEquals(1, $reportWithDiscrepancy['price_mismatches_count']);
        $this->assertEquals(1, $reportWithDiscrepancy['missing_in_quotation_count']);

        // View PO page renders the reconciliation section
        $viewUrl = PurchaseOrderResource::getUrl('view', ['record' => $this->po]);
        $response = $this->get($viewUrl);
        $response->assertSuccessful();
        $response->assertSee('Line Item Discrepancies Detected');
        $response->assertSee('Qty Mismatch');
        $response->assertSee('Price Mismatch');
        $response->assertSee('Not in Quotation');
    }

    public function test_discrepancy_restricts_po_approval_and_hides_approve_action_while_allowing_review(): void
    {
        $this->actingAs($this->admin);

        // 1. Setup matching quotation line item
        $this->quotation->lineItems()->create([
            'line_no' => 1,
            'item_code' => 'PUMP-50',
            'description' => '50HP Submersible Pump',
            'qty' => 2,
            'unit' => 'units',
            'unit_price' => 50000.00,
            'discounted_price' => 45000.00,
            'line_total' => 90000.00,
        ]);

        // 2. Setup PO line item with a price discrepancy
        $poLine = $this->po->lineItems()->create([
            'line_no' => 1,
            'item_code' => 'PUMP-50',
            'description' => '50HP Submersible Pump',
            'qty' => 2,
            'unit' => 'units',
            'unit_price' => 40000.00, // Discrepancy: 40,000 vs 45,000
            'line_total' => 80000.00,
        ]);

        $doc = \App\Models\Document::create([
            'document_type' => \App\Models\Document::TYPE_PURCHASE_ORDER,
            'document_number' => 'PO-DISCREP-001',
            'original_filename' => 'po_discrep.pdf',
            'disk_path' => 'documents/po_discrep.pdf',
            'file_hash' => hash('sha256', 'po_discrep_content'),
            'status' => \App\Models\Document::STATUS_REQUIRES_REVIEW,
            'uploaded_by' => $this->admin->id,
        ]);

        $this->po->update([
            'document_id' => $doc->id,
            'is_conforme_po' => false,
            'quotation_id' => $this->quotation->id,
            'order_amount' => 80000.00,
            'status' => PurchaseOrder::STATUS_PENDING,
        ]);

        $this->assertTrue($this->po->hasLineItemDiscrepancies());

        // A. Table actions: approve_po must be HIDDEN, review must be VISIBLE
        $tableActions = collect(PurchaseOrderResource::getTableActions())
            ->first(fn($a) => $a instanceof \Filament\Actions\ActionGroup)
            ->getActions();

        $approveAction = collect($tableActions)->first(fn($a) => $a->getName() === 'approve_po');
        $reviewAction = collect($tableActions)->first(fn($a) => $a->getName() === 'review');

        $approveAction->record($this->po);
        $reviewAction->record($this->po);

        // approve_po MUST BE HIDDEN
        $this->assertTrue($approveAction->isHidden());
        // review MUST BE VISIBLE (can still review!)
        $this->assertTrue($reviewAction->isVisible());

        // Calling action callback directly should reject approval
        ($approveAction->getActionFunction())($this->po);
        $this->po->refresh();
        $this->assertNotEquals(PurchaseOrder::STATUS_APPROVED, $this->po->status);

        // B. ReviewQueuePage: isPoWithDiscrepancy must be true and approveAndVerify must be blocked
        $page = new \App\Filament\Pages\ReviewQueuePage();
        $page->currentDocument = $doc;

        $this->assertTrue($page->getIsPoWithDiscrepancyProperty());

        $page->approveAndVerify();
        $doc->refresh();
        $this->assertNotEquals(\App\Models\Document::STATUS_VERIFIED, $doc->status);

        // C. Once discrepancy is resolved: approve_po becomes VISIBLE
        $poLine->update(['unit_price' => 45000.00, 'line_total' => 90000.00]);
        $this->po->refresh();
        $this->po->update(['order_amount' => 90000.00]);

        $this->assertFalse($this->po->hasLineItemDiscrepancies());
        $approveAction->record($this->po);
        $this->assertTrue($approveAction->isVisible());

        $freshPage = new \App\Filament\Pages\ReviewQueuePage();
        $freshPage->currentDocument = $doc;
        $this->assertFalse($freshPage->getIsPoWithDiscrepancyProperty());
    }

    public function test_switching_from_mismatched_quotation_to_matching_quotation_clears_discrepancies_and_unlocks_approval(): void
    {
        $this->actingAs($this->admin);

        // Wrong Quotation A (Water meters)
        $quotationA = Quotation::create([
            'quotation_number' => 'HISI-Q-WRONG-01',
            'quotation_date' => now(),
            'sales_agent_id' => $this->admin->id,
            'customer_name' => 'Megawide Construction Corp',
            'total_amount' => 50000.00,
            'status' => Quotation::STATUS_APPROVED,
        ]);
        $quotationA->lineItems()->create([
            'line_no' => 1,
            'item_code' => 'WM-050',
            'description' => 'Water Meter 1/2 inch Brass',
            'qty' => 10,
            'unit' => 'pcs',
            'unit_price' => 5000.00,
            'line_total' => 50000.00,
        ]);

        // Correct Quotation B (Lighting)
        $quotationB = Quotation::create([
            'quotation_number' => 'HISI-Q-CORRECT-02',
            'quotation_date' => now(),
            'sales_agent_id' => $this->admin->id,
            'customer_name' => 'Megawide Construction Corp',
            'total_amount' => 120000.00,
            'status' => Quotation::STATUS_APPROVED,
        ]);
        $quotationB->lineItems()->create([
            'line_no' => 1,
            'item_code' => 'HISI-LD-100W',
            'description' => 'Led Driver for Magnetic Tracklight 100w',
            'qty' => 15,
            'unit' => 'pcs',
            'unit_price' => 3000.00,
            'discounted_price' => 2800.00,
            'line_total' => 42000.00,
        ]);
        $quotationB->lineItems()->create([
            'line_no' => 2,
            'description' => '90° L connector',
            'qty' => 56,
            'unit' => 'pcs',
            'unit_price' => 1000.00,
            'discounted_price' => 950.00,
            'line_total' => 53200.00,
        ]);
        $quotationB->lineItems()->create([
            'line_no' => 3,
            'description' => 'End Cap',
            'qty' => 8,
            'unit' => 'pcs',
            'unit_price' => 300.00,
            'discounted_price' => 250.00,
            'line_total' => 2000.00,
        ]);

        // Setup PO line items matching Quotation B (with client PO description formatting)
        $this->po->lineItems()->create([
            'line_no' => 1,
            'description' => 'LED Driver, 100W',
            'qty' => 15,
            'unit' => 'pc',
            'unit_price' => 2800.00,
            'line_total' => 42000.00,
        ]);
        $this->po->lineItems()->create([
            'line_no' => 2,
            'description' => 'Track Bar 90 degrees Connector',
            'qty' => 56,
            'unit' => 'pc',
            'unit_price' => 950.00,
            'line_total' => 53200.00,
        ]);
        $this->po->lineItems()->create([
            'line_no' => 3,
            'description' => 'End Cap',
            'qty' => 8,
            'unit' => 'pc',
            'unit_price' => 250.00,
            'line_total' => 2000.00,
        ]);

        $this->po->update(['order_amount' => 97200.00, 'is_conforme_po' => false]);

        $linkAction = PurchaseOrderResource::getLinkToQuotationAction();

        // Step 1: User initially links PO to wrong Quotation A
        ($linkAction->getActionFunction())($this->po, [
            'quotation_id' => $quotationA->id,
            'sync_missing_details' => false,
            'verify_line_items' => true,
        ]);

        $this->po->refresh();
        $this->assertEquals($quotationA->id, $this->po->quotation_id);
        $this->assertTrue($this->po->hasLineItemDiscrepancies());

        // Step 2: User changes the linked quotation to the CORRECT Quotation B
        ($linkAction->getActionFunction())($this->po, [
            'quotation_id' => $quotationB->id,
            'sync_missing_details' => false,
            'verify_line_items' => true,
        ]);

        $this->po->refresh();
        $this->assertEquals($quotationB->id, $this->po->quotation_id);

        // Previous Quotation A must be restored to approved
        $quotationA->refresh();
        $this->assertEquals(Quotation::STATUS_APPROVED, $quotationA->status);

        // New Quotation B is converted
        $quotationB->refresh();
        $this->assertEquals(Quotation::STATUS_CONVERTED, $quotationB->status);

        // Line item checker must now accurately report ZERO discrepancies!
        $this->assertFalse($this->po->hasLineItemDiscrepancies());
        $report = $this->po->getReconciliationReport();
        $this->assertFalse($report['has_discrepancies']);
        $this->assertEquals(0, $report['discrepancy_count']);
        $this->assertEquals(3, $report['exact_matches_count']);

        // Approve PO action must now be VISIBLE
        $tableActions = collect(PurchaseOrderResource::getTableActions())
            ->first(fn($a) => $a instanceof \Filament\Actions\ActionGroup)
            ->getActions();

        $approveAction = collect($tableActions)->first(fn($a) => $a->getName() === 'approve_po');
        $approveAction->record($this->po);
        $this->assertTrue($approveAction->isVisible());
    }
}
