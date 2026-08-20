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
        $this->assertFalse($this->ceo->canEditQuotationDocument());

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
}
