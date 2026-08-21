<?php

namespace Tests\Feature;

use App\Actions\CrossReferenceDocuments;
use App\Actions\VerifyDocument;
use App\Models\AuditLog;
use App\Models\Document;
use App\Models\DocumentLineItem;
use App\Models\DocumentTotal;
use App\Models\Product;
use App\Models\ProductAlias;
use App\Models\Project;
use App\Models\PurchaseOrder;
use App\Models\Quotation;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DocumentIngestionTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Vendor $vendor;
    protected Project $project;
    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->vendor = Vendor::create(['name' => 'Huenics Industrial Corp.', 'slug' => 'huenics']);
        $this->project = Project::create(['name' => 'Palanza Tower', 'code' => 'PRJ-PALANZA']);
        $this->product = Product::create([
            'canonical_name' => '1-1/4" PVC Pipe Sch 40',
            'unit_default' => 'pcs',
            'default_price' => 1880.56,
            'is_huenics_owned' => true,
        ]);
    }

    public function test_document_verification_creates_transaction_and_audit_log(): void
    {
        $doc = Document::create([
            'vendor_id' => $this->vendor->id,
            'project_id' => $this->project->id,
            'uploaded_by' => $this->user->id,
            'document_type' => Document::TYPE_PURCHASE_ORDER,
            'document_number' => 'PO-998811',
            'document_date' => '2026-08-10',
            'original_filename' => 'PO_998811.pdf',
            'disk_path' => 'documents/uploads/sample.pdf',
            'file_hash' => 'hash_test_verification_001',
            'status' => Document::STATUS_REQUIRES_REVIEW,
        ]);

        $line = DocumentLineItem::create([
            'document_id' => $doc->id,
            'line_no' => 1,
            'description' => 'PVC PIPE 1.25 INCH',
            'qty' => 100,
            'unit' => 'pcs',
            'unit_price' => 1880.00,
            'printed_total' => 188000.00,
            'computed_total' => 188000.00,
            'product_id' => $this->product->id,
        ]);

        DocumentTotal::create([
            'document_id' => $doc->id,
            'printed_subtotal' => 188000.00,
            'printed_vat' => 22560.00,
            'printed_total' => 210560.00,
        ]);

        $verifier = app(VerifyDocument::class);
        $trx = $verifier->execute($doc, $this->user, [
            [
                'id' => $line->id,
                'description' => 'PVC PIPE 1.25 INCH',
                'qty' => 100,
                'unit' => 'pcs',
                'unit_price' => 1880.00,
                'printed_total' => 188000.00,
                'product_id' => $this->product->id,
            ]
        ]);

        // Document verified
        $this->assertEquals(Document::STATUS_VERIFIED, $doc->fresh()->status);
        $this->assertEquals($this->user->id, $doc->fresh()->verified_by);

        // Transaction created
        $this->assertInstanceOf(Transaction::class, $trx);
        $this->assertEquals($doc->id, $trx->purchase_order_document_id);
        $this->assertEquals(210560.00, (float) $trx->final_amount);

        // Audit Log recorded
        $this->assertTrue(AuditLog::where('action', 'document_verified')->where('auditable_id', $doc->id)->exists());

        // Product Alias learned
        $this->assertTrue(ProductAlias::where('product_id', $this->product->id)->where('normalized_alias', ProductAlias::normalize('PVC PIPE 1.25 INCH'))->exists());
    }

    public function test_cross_reference_correlates_quotation_and_po(): void
    {
        $vaf = Document::create([
            'vendor_id' => $this->vendor->id,
            'project_id' => $this->project->id,
            'uploaded_by' => $this->user->id,
            'document_type' => Document::TYPE_VENDORS_AGREEMENT,
            'document_number' => 'VAF-2026-001',
            'document_date' => '2026-08-01',
            'original_filename' => 'VAF_2026_001.pdf',
            'disk_path' => 'documents/uploads/vaf.pdf',
            'file_hash' => 'hash_crossref_vaf',
            'status' => Document::STATUS_VERIFIED,
        ]);

        $po = Document::create([
            'vendor_id' => $this->vendor->id,
            'project_id' => $this->project->id,
            'uploaded_by' => $this->user->id,
            'document_type' => Document::TYPE_PURCHASE_ORDER,
            'document_number' => '4010027092',
            'document_date' => '2026-08-02',
            'original_filename' => 'PO_4010027092.pdf',
            'disk_path' => 'documents/uploads/po.pdf',
            'file_hash' => 'hash_crossref_po',
            'status' => Document::STATUS_REQUIRES_REVIEW,
        ]);

        $crossReferencer = app(CrossReferenceDocuments::class);
        $result = $crossReferencer->execute($po);

        $this->assertNotNull($result['quotation']);
        $this->assertEquals($vaf->id, $result['quotation']->id);
        $this->assertEquals($po->id, $result['purchase_order']->id);
    }

    public function test_ingest_document_action_executes_quotation_and_po_with_templates(): void
    {
        $action = app(\App\Actions\IngestDocumentAction::class);

        // Ingest quotation
        $quotationDoc = $action->execute(
            diskPath: 'documents/uploads/test_quotation.pdf',
            originalFilename: 'Quotation_QT-2026.pdf',
            documentType: Document::TYPE_VENDORS_AGREEMENT,
            vendorId: $this->vendor->id,
            projectId: $this->project->id,
            userId: $this->user->id
        );

        $this->assertInstanceOf(Document::class, $quotationDoc);
        $this->assertEquals(Document::TYPE_VENDORS_AGREEMENT, $quotationDoc->document_type);
        $this->assertEquals($this->vendor->id, $quotationDoc->vendor_id);
        $this->assertEquals($this->project->id, $quotationDoc->project_id);

        // Ingest PO
        $poDoc = $action->execute(
            diskPath: 'documents/uploads/test_po.pdf',
            originalFilename: 'PO_PO-2026.pdf',
            documentType: Document::TYPE_PURCHASE_ORDER,
            vendorId: $this->vendor->id,
            projectId: $this->project->id,
            userId: $this->user->id
        );

        $this->assertInstanceOf(Document::class, $poDoc);
        $this->assertEquals(Document::TYPE_PURCHASE_ORDER, $poDoc->document_type);
    }

    public function test_document_verification_with_unassigned_vendor_or_project_succeeds_without_fk_error(): void
    {
        $doc = Document::create([
            'vendor_id' => null,
            'project_id' => null,
            'uploaded_by' => $this->user->id,
            'document_type' => Document::TYPE_VENDORS_AGREEMENT,
            'document_number' => 'QT-2026-UNASSIGNED',
            'original_filename' => 'Quotation_Unassigned.pdf',
            'disk_path' => 'documents/uploads/unassigned.pdf',
            'file_hash' => 'hash_unassigned_test_001',
            'status' => Document::STATUS_REQUIRES_REVIEW,
        ]);

        $verifier = app(VerifyDocument::class);
        $trx = $verifier->execute($doc, $this->user);

        $this->assertInstanceOf(Transaction::class, $trx);
        $this->assertEquals(Document::STATUS_VERIFIED, $doc->fresh()->status);
        $this->assertNotNull($trx->vendor_id);
        $this->assertNotNull($trx->project_id);

        // Verified quotation document automatically creates an approved Quotation resource record
        $quotation = Quotation::where('document_id', $doc->id)->first();
        $this->assertNotNull($quotation);
        $this->assertEquals(Quotation::STATUS_APPROVED, $quotation->status);
    }

    public function test_ingesting_document_with_new_product_auto_creates_product_in_database_and_links_it(): void
    {
        $parser = app(\App\Services\DocumentParsers\DynamicDocumentParser::class);
        $doc = Document::create([
            'vendor_id' => $this->vendor->id,
            'project_id' => $this->project->id,
            'uploaded_by' => $this->user->id,
            'document_type' => Document::TYPE_VENDORS_AGREEMENT,
            'document_number' => 'QT-AUTO-PRODUCT-99',
            'original_filename' => 'AutoProduct.pdf',
            'disk_path' => 'documents/uploads/autoproduct.pdf',
            'file_hash' => 'hash_autoproduct_test_99',
            'status' => Document::STATUS_UPLOADED,
        ]);

        $fullText = "VENDORS AGREEMENT\nCustomer: Test Client\nHISI-MTL-99W High Power LED Floodlight\n10 pcs 1500.00 15000.00\nTotal: 15000.00";
        $lines = explode("\n", $fullText);

        $reflection = new \ReflectionClass($parser);
        $method = $reflection->getMethod('extractLineItems');
        $method->setAccessible(true);
        $lineItems = $method->invoke($parser, null, $fullText, $lines, $doc);

        $this->assertNotEmpty($lineItems);
        $this->assertNotNull($lineItems[0]['product_id']);

        $createdProduct = Product::find($lineItems[0]['product_id']);
        $this->assertNotNull($createdProduct);
        $this->assertEquals(1500.00, (float) $createdProduct->default_price);
        $this->assertEquals('pcs', $createdProduct->unit_default);
    }
}



