<?php

namespace Tests\Feature;

use App\Models\DeliveryReceipt;
use App\Models\Document;
use App\Models\Product;
use App\Models\Project;
use App\Models\PurchaseOrder;
use App\Models\Quotation;
use App\Models\QuotationLineItem;
use App\Models\SalesInvoice;
use App\Models\User;
use App\Models\Vendor;
use App\Services\ExportQuotationPdf;
use App\Services\QuotationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ComprehensiveFeatureExpansionTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $opsManager;
    protected User $salesExec;
    protected User $ceo;
    protected User $inhouseOwner;
    protected Vendor $vendor;
    protected Project $project;
    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'name' => 'Admin User',
            'is_owner' => false,
        ]);

        $this->opsManager = User::factory()->create([
            'role' => User::ROLE_OPERATIONS_MANAGER,
            'name' => 'Ops Manager User',
            'is_owner' => false,
        ]);

        $this->salesExec = User::factory()->create([
            'role' => User::ROLE_SALES_EXECUTIVE,
            'name' => 'Sales Executive User',
            'is_owner' => false,
            'e_signature_path' => 'signatures/sales_exec.png',
        ]);

        $this->ceo = User::factory()->create([
            'role' => User::ROLE_CEO,
            'name' => 'CEO User',
            'is_owner' => false,
        ]);

        $this->inhouseOwner = User::factory()->create([
            'role' => User::ROLE_SALES_EXECUTIVE,
            'name' => 'Owner Seller',
            'is_owner' => true,
        ]);

        $this->vendor = Vendor::create([
            'name' => 'Huenics Industrial Corp.',
            'slug' => 'huenics-corp',
        ]);

        $this->project = Project::create([
            'name' => 'Palanza Tower Project',
            'code' => 'PRJ-PALANZA-001',
            'customer_name' => 'Engr. Ronald Rey Sandoval',
            'location' => 'Palanza St., Quezon City',
        ]);

        $this->product = Product::create([
            'canonical_name' => '1-1/4" PVC Pipe Sch 40',
            'unit_default' => 'pcs',
            'default_price' => 1880.56,
            'is_huenics_owned' => true,
        ]);
    }

    public function test_user_e_signature_and_quotation_edit_permissions(): void
    {
        // 1. Role-based edit permission check
        $this->assertTrue($this->admin->canEditQuotationDocument());
        $this->assertTrue($this->opsManager->canEditQuotationDocument());
        $this->assertTrue($this->salesExec->canEditQuotationDocument());
        $this->assertTrue($this->ceo->canEditQuotationDocument());

        // 2. E-signature URL and path accessors
        $this->assertEquals('signatures/sales_exec.png', $this->salesExec->e_signature_path);
        $this->assertNull($this->admin->e_signature_path);
    }

    public function test_unapproved_quotation_conversion_is_blocked(): void
    {
        $quotation = Quotation::create([
            'quotation_number' => Quotation::generateNumber(),
            'sales_agent_id' => $this->salesExec->id,
            'customer_name' => 'Engr. Ronald Rey Sandoval',
            'customer_company' => 'MGS CONSTRUCTION, INC.',
            'project_id' => $this->project->id,
            'total_amount' => 50000.00,
            'status' => Quotation::STATUS_PENDING,
            'quotation_date' => now()->toDateString(),
        ]);

        $service = app(QuotationService::class);

        $this->assertFalse($quotation->isReadyForConversion());
        $this->assertFalse($quotation->canServeAsOfficialPO());

        $this->expectException(\RuntimeException::class);
        $service->convertToPO($quotation);
    }

    public function test_quotation_approval_and_review_workflow_enables_conversion(): void
    {
        $quotation = Quotation::create([
            'quotation_number' => Quotation::generateNumber(),
            'sales_agent_id' => $this->salesExec->id,
            'customer_name' => 'Engr. Ronald Rey Sandoval',
            'customer_company' => 'MGS CONSTRUCTION, INC.',
            'project_id' => $this->project->id,
            'project_name' => 'Palanza Tower',
            'project_location' => 'Quezon City',
            'total_amount' => 50000.00,
            'status' => Quotation::STATUS_PENDING,
            'quotation_date' => now()->toDateString(),
        ]);

        QuotationLineItem::create([
            'quotation_id' => $quotation->id,
            'line_no' => 1,
            'description' => '1-1/4" PVC Pipe Sch 40',
            'qty' => 10,
            'unit' => 'pcs',
            'unit_price' => 5000.00,
            'line_total' => 50000.00,
        ]);

        $service = app(QuotationService::class);

        // 1. Review
        $service->review($quotation, $this->opsManager);
        $quotation->refresh();
        $this->assertTrue($quotation->isReviewed());
        $this->assertFalse($quotation->isReadyForConversion());

        // 2. Approve
        $service->approve($quotation, $this->admin);
        $quotation->refresh();
        $this->assertTrue($quotation->isApproved());
        $this->assertTrue($quotation->isReadyForConversion());

        // 3. Successful conversion to PO
        $po = $service->convertToPO($quotation);
        $this->assertInstanceOf(PurchaseOrder::class, $po);
        $this->assertEquals(PurchaseOrder::STATUS_PENDING, $po->status);
        $this->assertEquals(Quotation::STATUS_CONVERTED, $quotation->fresh()->status);
    }

    public function test_quotation_can_serve_as_official_po_with_customer_signature(): void
    {
        $quotation = Quotation::create([
            'quotation_number' => Quotation::generateNumber(),
            'sales_agent_id' => $this->salesExec->id,
            'customer_name' => 'Engr. Ronald Rey Sandoval',
            'customer_company' => 'MGS CONSTRUCTION, INC.',
            'project_id' => $this->project->id,
            'total_amount' => 25000.00,
            'status' => Quotation::STATUS_PENDING,
            'is_official_po' => true,
            'customer_signature_name' => 'Engr. Ronald Rey Sandoval',
            'customer_signed_at' => now(),
            'quotation_date' => now()->toDateString(),
        ]);

        QuotationLineItem::create([
            'quotation_id' => $quotation->id,
            'line_no' => 1,
            'description' => '1-1/4" PVC Pipe Sch 40',
            'qty' => 5,
            'unit' => 'pcs',
            'unit_price' => 5000.00,
            'line_total' => 25000.00,
        ]);

        $this->assertTrue($quotation->canServeAsOfficialPO());

        $service = app(QuotationService::class);
        $po = $service->convertToPO($quotation);

        $this->assertInstanceOf(PurchaseOrder::class, $po);
        $this->assertEquals(PurchaseOrder::STATUS_PENDING, $po->status);
    }

    public function test_export_quotation_pdf_generates_pdf_binary(): void
    {
        $quotation = Quotation::create([
            'quotation_number' => Quotation::generateNumber(),
            'sales_agent_id' => $this->salesExec->id,
            'approved_by' => $this->admin->id,
            'approved_at' => now(),
            'reviewed_by' => $this->opsManager->id,
            'reviewed_at' => now(),
            'customer_name' => 'Engr. Ronald Rey Sandoval',
            'customer_company' => 'MGS CONSTRUCTION, INC.',
            'project_id' => $this->project->id,
            'project_name' => 'Palanza Tower',
            'project_location' => 'Quezon City',
            'phone_no' => '0906-144-2553',
            'total_amount' => 56000.00,
            'negotiated_amount' => 50000.00,
            'status' => Quotation::STATUS_APPROVED,
            'is_official_po' => true,
            'customer_signature_name' => 'Engr. Ronald Rey Sandoval',
            'customer_signed_at' => now(),
            'quotation_date' => now()->toDateString(),
        ]);

        QuotationLineItem::create([
            'quotation_id' => $quotation->id,
            'line_no' => 1,
            'item_code' => 'PVC-001',
            'description' => '1-1/4" PVC Pipe Sch 40',
            'qty' => 10,
            'unit' => 'pcs',
            'unit_price' => 5600.00,
            'discounted_price' => 5000.00,
            'line_total' => 50000.00,
        ]);

        $exporter = app(ExportQuotationPdf::class);
        $pdfOutput = $exporter->generate($quotation);

        $this->assertNotEmpty($pdfOutput);
        $this->assertStringStartsWith('%PDF', $pdfOutput);

        $downloadResp = $exporter->downloadResponse($quotation);
        $this->assertEquals(200, $downloadResp->getStatusCode());
        $this->assertEquals('application/pdf', $downloadResp->headers->get('Content-Type'));

        $previewResp = $exporter->previewResponse($quotation);
        $this->assertEquals(200, $previewResp->getStatusCode());
        $this->assertEquals('application/pdf', $previewResp->headers->get('Content-Type'));
    }

    public function test_delivery_receipt_and_sales_invoice_creation_and_numbers(): void
    {
        $po = PurchaseOrder::create([
            'po_number' => PurchaseOrder::generateNumber(),
            'sales_agent_id' => $this->salesExec->id,
            'customer_name' => 'MGS CONSTRUCTION, INC.',
            'project_id' => $this->project->id,
            'order_amount' => 100000.00,
            'order_date' => now()->toDateString(),
            'status' => PurchaseOrder::STATUS_PENDING,
        ]);

        // DR Number generation
        $drNumber = DeliveryReceipt::generateNumber();
        $this->assertMatchesRegularExpression('/^DR-\d{4}-\d{4}$/', $drNumber);

        $dr = DeliveryReceipt::create([
            'dr_number' => $drNumber,
            'purchase_order_id' => $po->id,
            'delivered_by' => 'Driver John',
            'received_by' => 'Site Receiver Mark',
            'delivery_date' => now()->toDateString(),
            'status' => DeliveryReceipt::STATUS_DELIVERED,
        ]);

        $drItem = $dr->items()->create([
            'description' => '1-1/4" PVC Pipe Sch 40',
            'qty_delivered' => 50,
            'unit' => 'pcs',
            'product_id' => $this->product->id,
        ]);

        $this->assertCount(1, $dr->fresh()->items);
        $this->assertEquals($po->id, $dr->purchaseOrder->id);

        // SI Number generation
        $siNumber = SalesInvoice::generateNumber();
        $this->assertMatchesRegularExpression('/^SI-\d{4}-\d{4}$/', $siNumber);

        $si = SalesInvoice::create([
            'si_number' => $siNumber,
            'purchase_order_id' => $po->id,
            'delivery_receipt_id' => $dr->id,
            'customer_name' => 'MGS CONSTRUCTION, INC.',
            'invoice_date' => now()->toDateString(),
            'subtotal' => 89285.71,
            'vat_amount' => 10714.29,
            'total_amount' => 100000.00,
            'payment_status' => SalesInvoice::STATUS_PAID,
            'payment_date' => now()->toDateString(),
        ]);

        $siItem = $si->items()->create([
            'description' => '1-1/4" PVC Pipe Sch 40',
            'qty' => 50,
            'unit' => 'pcs',
            'unit_price' => 2000.00,
            'line_total' => 100000.00,
        ]);

        $this->assertCount(1, $si->fresh()->items);
        $this->assertEquals($po->id, $si->purchaseOrder->id);
        $this->assertEquals($dr->id, $si->deliveryReceipt->id);
    }

    public function test_sales_dashboard_inhouse_and_agent_filtering(): void
    {
        // Quotation by regular sales executive
        Quotation::create([
            'quotation_number' => Quotation::generateNumber(),
            'sales_agent_id' => $this->salesExec->id,
            'customer_name' => 'Customer A',
            'total_amount' => 30000.00,
            'status' => Quotation::STATUS_APPROVED,
            'quotation_date' => now()->toDateString(),
        ]);

        // Quotation by Inhouse owner
        Quotation::create([
            'quotation_number' => Quotation::generateNumber(),
            'sales_agent_id' => $this->inhouseOwner->id,
            'customer_name' => 'Customer B',
            'total_amount' => 75000.00,
            'status' => Quotation::STATUS_APPROVED,
            'quotation_date' => now()->toDateString(),
        ]);

        // Query filtering by Inhouse owner
        $inhouseQuotes = Quotation::whereHas('salesAgent', function ($q) {
            $q->where('is_owner', true);
        })->get();

        $this->assertCount(1, $inhouseQuotes);
        $this->assertEquals(75000.00, (float) $inhouseQuotes->first()->total_amount);

        // Query filtering by specific Sales Executive
        $agentQuotes = Quotation::where('sales_agent_id', $this->salesExec->id)->get();
        $this->assertCount(1, $agentQuotes);
        $this->assertEquals(30000.00, (float) $agentQuotes->first()->total_amount);
    }

    public function test_quotation_and_purchase_order_line_items_field_alignment_and_conversion(): void
    {
        $quotation = Quotation::create([
            'quotation_number' => Quotation::generateNumber(),
            'sales_agent_id'   => $this->salesExec->id,
            'customer_name'    => 'Contractor Megawide',
            'project_id'       => $this->project->id,
            'total_amount'     => 12000.00,
            'total_cost'       => 8000.00,
            'status'           => Quotation::STATUS_APPROVED,
            'reviewed_by'      => $this->opsManager->id,
            'reviewed_at'      => now(),
            'approved_by'      => $this->admin->id,
            'approved_at'      => now(),
            'quotation_date'   => now()->toDateString(),
        ]);

        $lineItem = $quotation->lineItems()->create([
            'line_no'          => 1,
            'item_code'        => 'PIPE-PVC-001',
            'product_id'       => $this->product->id,
            'description'      => '1-1/4" PVC Pipe Sch 40 High Grade',
            'qty'              => 10,
            'unit'             => 'lengths',
            'unit_price'       => 1500.00,
            'discounted_price' => 1200.00,
            'base_cost'        => 800.00,
            'line_total'       => 12000.00,
            'gross_profit'     => 4000.00,
        ]);

        $service = app(QuotationService::class);
        $po = $service->convertToPO($quotation);

        $this->assertEquals(Quotation::STATUS_CONVERTED, $quotation->fresh()->status);
        $this->assertCount(1, $po->lineItems);

        $poItem = $po->lineItems->first();
        $this->assertEquals(1, $poItem->line_no);
        $this->assertEquals('PIPE-PVC-001', $poItem->item_code);
        $this->assertEquals($this->product->id, $poItem->product_id);
        $this->assertEquals('1-1/4" PVC Pipe Sch 40 High Grade', $poItem->description);
        $this->assertEquals(10, (float) $poItem->qty);
        $this->assertEquals('lengths', $poItem->unit);
        $this->assertEquals(1500.00, (float) $poItem->unit_price);
        $this->assertEquals(1200.00, (float) $poItem->discounted_price);
        $this->assertEquals(800.00, (float) $poItem->base_cost);
        $this->assertEquals(12000.00, (float) $poItem->line_total);
    }

    public function test_clearing_actual_delivery_date_or_delivery_status_reverts_po_status_and_deactivates_warranty(): void
    {
        $po = PurchaseOrder::create([
            'po_number'            => PurchaseOrder::generateNumber(),
            'sales_agent_id'       => $this->salesExec->id,
            'customer_name'        => 'Metro Construction Corp',
            'order_date'           => now()->toDateString(),
            'order_amount'         => 25000.00,
            'status'               => PurchaseOrder::STATUS_DELIVERED,
            'delivery_status'      => PurchaseOrder::DELIVERY_DELIVERED,
            'is_completed'         => true,
            'actual_delivery_date' => now()->toDateString(),
            'delivery_receipt_no'  => 'DR-2026-999',
            'has_warranty'         => true,
            'warranty_period'      => PurchaseOrder::WARRANTY_2_YEARS_6_MONTHS,
        ]);

        $po = $po->fresh();
        $this->assertEquals(PurchaseOrder::STATUS_DELIVERED, $po->status);
        $this->assertEquals(PurchaseOrder::DELIVERY_DELIVERED, $po->delivery_status);
        $this->assertEquals(PurchaseOrder::WARRANTY_ACTIVE, $po->warranty_status);
        $this->assertNotNull($po->warranty_start_date);
        $this->assertNotNull($po->warranty_end_date);

        // 1. User clears actual_delivery_date and sets delivery_status back to pending
        $po->update([
            'actual_delivery_date' => null,
            'delivery_status'      => PurchaseOrder::DELIVERY_PENDING,
            'delivery_receipt_no'  => null,
        ]);

        $po = $po->fresh();
        $this->assertNull($po->actual_delivery_date);
        $this->assertEquals(PurchaseOrder::DELIVERY_PENDING, $po->delivery_status);
        $this->assertEquals(PurchaseOrder::STATUS_PENDING, $po->status);
        $this->assertEquals(PurchaseOrder::WARRANTY_NONE, $po->warranty_status);
        $this->assertNull($po->warranty_start_date);
        $this->assertNull($po->warranty_end_date);

        // 2. Re-deliver with 1 year warranty
        $po->update([
            'delivery_status'      => PurchaseOrder::DELIVERY_DELIVERED,
            'actual_delivery_date' => now()->toDateString(),
            'warranty_period'      => PurchaseOrder::WARRANTY_1_YEAR,
        ]);

        $po = $po->fresh();
        $this->assertEquals(PurchaseOrder::STATUS_DELIVERED, $po->status);
        $this->assertEquals(PurchaseOrder::DELIVERY_DELIVERED, $po->delivery_status);
        $this->assertEquals(PurchaseOrder::WARRANTY_ACTIVE, $po->warranty_status);

        // 3. User toggles off has_warranty
        $po->update(['has_warranty' => false]);
        $po = $po->fresh();
        $this->assertFalse((bool) $po->has_warranty);
        $this->assertEquals(PurchaseOrder::WARRANTY_NONE, $po->warranty_status);
        $this->assertNull($po->warranty_start_date);
        $this->assertNull($po->warranty_end_date);
    }

    public function test_document_ingestion_creates_pending_quotation_and_purchase_order(): void
    {
        // 1. Ingest Quotation document
        $quotationDoc = Document::create([
            'disk_path' => 'documents/uploads/test_qt.pdf',
            'original_filename' => 'test_qt.pdf',
            'document_number' => 'QT-INGEST-001',
            'document_type' => Document::TYPE_VENDORS_AGREEMENT,
            'status' => Document::STATUS_REQUIRES_REVIEW,
            'customer_name' => 'Acme Corporation',
            'customer_company' => 'Acme Builders',
            'project_id' => $this->project->id,
            'project_name' => 'Palanza Tower',
            'uploaded_by' => $this->salesExec->id,
            'file_hash' => 'hash_test_qt_001',
        ]);

        $quotationDoc->lineItems()->create([
            'line_no' => 1,
            'material_code' => 'MAT-001',
            'description' => 'Industrial Steel Pipe 2"',
            'qty' => 5,
            'unit' => 'pcs',
            'unit_price' => 2000.00,
            'discounted_price' => 1800.00,
            'printed_total' => 9000.00,
            'computed_total' => 9000.00,
        ]);

        $ingestAction = app(\App\Actions\IngestDocumentAction::class);
        $ingestAction->syncInitialResourceRecord($quotationDoc, $this->salesExec->id);

        $quotation = Quotation::where('document_id', $quotationDoc->id)->first();
        $this->assertNotNull($quotation);
        $this->assertEquals(Quotation::STATUS_PENDING, $quotation->status);
        $this->assertEquals('Acme Corporation', $quotation->customer_name);
        $this->assertEquals(1, $quotation->lineItems()->count());
        $this->assertEquals('MAT-001', $quotation->lineItems()->first()->item_code);

        // 2. Ingest Purchase Order document
        $poDoc = Document::create([
            'disk_path' => 'documents/uploads/test_po.pdf',
            'original_filename' => 'test_po.pdf',
            'document_number' => 'PO-INGEST-001',
            'document_type' => Document::TYPE_PURCHASE_ORDER,
            'status' => Document::STATUS_REQUIRES_REVIEW,
            'customer_name' => 'Apex Engineering Corp',
            'project_id' => $this->project->id,
            'uploaded_by' => $this->salesExec->id,
            'file_hash' => 'hash_test_po_001',
        ]);

        $poDoc->lineItems()->create([
            'line_no' => 1,
            'material_code' => 'MAT-PO-001',
            'description' => 'Heavy Duty Ball Valve 1"',
            'qty' => 10,
            'unit' => 'pcs',
            'unit_price' => 750.00,
            'discounted_price' => 750.00,
            'printed_total' => 7500.00,
            'computed_total' => 7500.00,
        ]);

        $ingestAction->syncInitialResourceRecord($poDoc, $this->salesExec->id);

        $po = PurchaseOrder::where('document_id', $poDoc->id)->first();
        $this->assertNotNull($po);
        $this->assertEquals(PurchaseOrder::STATUS_PENDING, $po->status);
        $this->assertEquals(PurchaseOrder::DELIVERY_PENDING, $po->delivery_status);
        $this->assertEquals('Apex Engineering Corp', $po->customer_name);
        $this->assertEquals(1, $po->lineItems()->count());
        $this->assertEquals('MAT-PO-001', $po->lineItems()->first()->item_code);

        // 3. Verify ReviewQueuePage is hidden from sidebar navigation
        $reviewPageReflection = new \ReflectionClass(\App\Filament\Pages\ReviewQueuePage::class);
        $shouldRegisterProp = $reviewPageReflection->getProperty('shouldRegisterNavigation');
        $shouldRegisterProp->setAccessible(true);
        $this->assertFalse($shouldRegisterProp->getValue());
    }

    public function test_rejecting_document_sets_designated_rejected_status_across_document_quotation_and_po(): void
    {
        $this->actingAs($this->opsManager);

        $doc = Document::create([
            'disk_path' => 'documents/uploads/reject_test.pdf',
            'original_filename' => 'reject_test.pdf',
            'document_number' => 'REJ-001',
            'document_type' => Document::TYPE_VENDORS_AGREEMENT,
            'status' => Document::STATUS_REQUIRES_REVIEW,
            'customer_name' => 'Reject Test Client',
            'project_id' => $this->project->id,
            'uploaded_by' => $this->salesExec->id,
            'file_hash' => 'hash_reject_test_001',
        ]);

        $quotation = Quotation::create([
            'quotation_number' => 'QT-REJ-001',
            'document_id' => $doc->id,
            'sales_agent_id' => $this->salesExec->id,
            'customer_name' => 'Reject Test Client',
            'status' => Quotation::STATUS_PENDING,
            'quotation_date' => now()->toDateString(),
        ]);

        $po = PurchaseOrder::create([
            'po_number' => 'PO-REJ-001',
            'document_id' => $doc->id,
            'sales_agent_id' => $this->salesExec->id,
            'customer_name' => 'Reject Test Client',
            'status' => PurchaseOrder::STATUS_PENDING,
            'order_date' => now()->toDateString(),
        ]);

        // Trigger rejection on ReviewQueuePage
        $page = new \App\Filament\Pages\ReviewQueuePage();
        $page->currentDocument = $doc;
        $page->rejectionReason = 'Pricing numbers do not match quote.';
        $page->rejectDocument();

        $this->assertEquals(\App\Enums\DocumentStatus::Rejected->value, $doc->fresh()->status);
        $this->assertEquals('Pricing numbers do not match quote.', $doc->fresh()->failure_reason);

        $this->assertEquals(\App\Enums\QuotationStatus::Rejected->value, $quotation->fresh()->status);
        $this->assertEquals('Pricing numbers do not match quote.', $quotation->fresh()->rejection_reason);

        $this->assertEquals(\App\Enums\PurchaseOrderStatus::Rejected->value, $po->fresh()->status);
    }

    public function test_project_wide_enums_implement_contracts_and_match_model_constants(): void
    {
        $this->assertEquals(\App\Enums\DocumentStatus::RequiresReview->value, Document::STATUS_REQUIRES_REVIEW);
        $this->assertEquals(\App\Enums\DocumentStatus::Rejected->value, Document::STATUS_REJECTED);
        $this->assertEquals(\App\Enums\QuotationStatus::Rejected->value, Quotation::STATUS_REJECTED);
        $this->assertEquals(\App\Enums\PurchaseOrderStatus::Rejected->value, PurchaseOrder::STATUS_REJECTED);
        $this->assertEquals(\App\Enums\DeliveryStatus::Delivered->value, PurchaseOrder::DELIVERY_DELIVERED);
        $this->assertEquals(\App\Enums\WarrantyStatus::Active->value, PurchaseOrder::WARRANTY_ACTIVE);
        $this->assertEquals(\App\Enums\WarrantyPeriod::OneYear->value, PurchaseOrder::WARRANTY_1_YEAR);
        $this->assertEquals(\App\Enums\DeliveryReceiptStatus::Draft->value, DeliveryReceipt::STATUS_DRAFT);
        $this->assertEquals(\App\Enums\SalesInvoiceStatus::Paid->value, SalesInvoice::STATUS_PAID);
        $this->assertEquals(\App\Enums\UserRole::Admin->value, User::ROLE_ADMIN);

        // Verify Enum contract methods
        $docStatus = \App\Enums\DocumentStatus::Rejected;
        $this->assertEquals('Rejected', $docStatus->getLabel());
        $this->assertEquals('danger', $docStatus->getColor());
        $this->assertEquals('heroicon-m-x-circle', $docStatus->getIcon());

        $warrantyPeriod = \App\Enums\WarrantyPeriod::TwoYearsSixMonths;
        $this->assertEquals('2 Years and 6 Months', $warrantyPeriod->getLabel());
        $this->assertEquals(30, $warrantyPeriod->getMonths());
    }

    public function test_product_image_field_and_pdf_export_with_image(): void
    {
        $product = Product::create([
            'canonical_name' => 'Magnetic Tracklight 6W 3000K',
            'product_code' => 'HISI-MTL-6W',
            'sku' => 'HISI-MTL-6W-SKU',
            'category' => 'Tracklights',
            'unit_default' => 'pcs',
            'selling_price' => 1890.00,
            'image_path' => 'products/images/sample_tracklight.png',
            'is_active' => true,
        ]);

        $this->assertEquals('products/images/sample_tracklight.png', $product->image_path);
        $this->assertNotNull($product->image_url);

        $quotation = Quotation::create([
            'quotation_number' => Quotation::generateNumber(),
            'sales_agent_id' => $this->salesExec->id,
            'customer_name' => 'Engr. Ronald Rey Sandoval',
            'total_amount' => 1890.00,
            'status' => Quotation::STATUS_APPROVED,
            'quotation_date' => now()->toDateString(),
        ]);

        $line = QuotationLineItem::create([
            'quotation_id' => $quotation->id,
            'product_id' => $product->id,
            'line_no' => 1,
            'item_code' => 'HISI-MTL-6W',
            'description' => 'Magnetic Tracklight 6W 3000K',
            'qty' => 1,
            'unit' => 'pcs',
            'unit_price' => 1890.00,
            'line_total' => 1890.00,
        ]);

        $this->assertEquals($product->id, $line->product->id);

        $exporter = app(ExportQuotationPdf::class);
        $pdfOutput = $exporter->generate($quotation);
        $this->assertNotEmpty($pdfOutput);
        $this->assertStringStartsWith('%PDF', $pdfOutput);
    }
}
