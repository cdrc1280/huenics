<?php

namespace Tests\Feature;

use App\Actions\ReconcileDocumentTotals;
use App\Models\Document;
use App\Models\DocumentLineItem;
use App\Models\DocumentTotal;
use App\Models\Product;
use App\Models\Project;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReconciliationTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Vendor $vendor;
    protected Project $project;
    protected Product $product;
    protected ReconcileDocumentTotals $reconciler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->vendor = Vendor::create(['name' => 'Huenics Industrial', 'slug' => 'huenics']);
        $this->project = Project::create(['name' => 'Palanza Tower', 'code' => 'PRJ-PAL']);
        $this->product = Product::create([
            'canonical_name' => '1-1/4" PVC Pipe Sch 40',
            'unit_default' => 'pcs',
            'default_price' => 1880.56,
            'is_huenics_owned' => true,
        ]);
        $this->reconciler = app(ReconcileDocumentTotals::class);
    }

    public function test_line_total_arithmetic_flags_printed_discrepancy(): void
    {
        // PO #4010027092 case: 158 * 1880.56 = 297,128.48, printed as 297,128.85
        $doc = Document::create([
            'vendor_id' => $this->vendor->id,
            'project_id' => $this->project->id,
            'uploaded_by' => $this->user->id,
            'document_type' => Document::TYPE_PURCHASE_ORDER,
            'document_number' => '4010027092',
            'original_filename' => 'PO_4010027092.pdf',
            'disk_path' => 'documents/uploads/test.pdf',
            'file_hash' => 'hash_test_1',
            'status' => Document::STATUS_REQUIRES_REVIEW,
        ]);

        $line = DocumentLineItem::create([
            'document_id' => $doc->id,
            'line_no' => 30,
            'description' => '1-1/4" PVC Pipe Sch 40',
            'qty' => 158,
            'unit' => 'pcs',
            'unit_price' => 1880.56,
            'printed_total' => 297128.85, // .85 bug
            'computed_total' => 0,
            'total_mismatch' => false,
            'product_id' => $this->product->id,
        ]);

        DocumentTotal::create([
            'document_id' => $doc->id,
            'printed_subtotal' => 297128.85,
            'printed_vat' => 35655.46,
            'printed_total' => 332784.31,
        ]);

        $totals = $this->reconciler->execute($doc);
        $freshLine = $line->fresh();

        $this->assertEquals(297128.48, (float) $freshLine->computed_total);
        $this->assertTrue((bool) $freshLine->total_mismatch);
        $this->assertTrue((bool) $totals->total_mismatch);
    }

    public function test_vat_discrepancy_detection_flags_copied_vat(): void
    {
        // Order Slip S.O.#26005 case: Printed VAT is 112,500.00 while computed 12% is 101,785.65
        $doc = Document::create([
            'vendor_id' => $this->vendor->id,
            'project_id' => $this->project->id,
            'uploaded_by' => $this->user->id,
            'document_type' => Document::TYPE_PURCHASE_ORDER,
            'document_number' => 'SO-26005',
            'original_filename' => 'SO_26005.pdf',
            'disk_path' => 'documents/uploads/test_so.pdf',
            'file_hash' => 'hash_test_2',
            'status' => Document::STATUS_REQUIRES_REVIEW,
        ]);

        DocumentLineItem::create([
            'document_id' => $doc->id,
            'line_no' => 1,
            'description' => '1-1/4" PVC Pipe Sch 40',
            'qty' => 451,
            'unit' => 'pcs',
            'unit_price' => 1880.74,
            'printed_total' => 848214.34,
            'computed_total' => 848213.74,
            'total_mismatch' => false,
        ]);

        DocumentTotal::create([
            'document_id' => $doc->id,
            'printed_subtotal' => 848214.34,
            'printed_vat' => 112500.00, // Copied wrong VAT
            'printed_total' => 960714.34,
        ]);

        $totals = $this->reconciler->execute($doc);

        $this->assertTrue((bool) $totals->vat_mismatch);
        $this->assertEquals(101785.65, (float) $totals->computed_vat);
    }

    public function test_quotation_preserves_negotiated_amount_as_authoritative(): void
    {
        // Quotation case: Line sum = 1,074,060.00, but negotiated deal = 1,050,000.00
        $doc = Document::create([
            'vendor_id' => $this->vendor->id,
            'project_id' => $this->project->id,
            'uploaded_by' => $this->user->id,
            'document_type' => Document::TYPE_VENDORS_AGREEMENT,
            'document_number' => 'VAF-2026-081',
            'original_filename' => 'Quotation_VAF.pdf',
            'disk_path' => 'documents/uploads/test_vaf.pdf',
            'file_hash' => 'hash_test_3',
            'status' => Document::STATUS_REQUIRES_REVIEW,
        ]);

        DocumentLineItem::create([
            'document_id' => $doc->id,
            'line_no' => 1,
            'description' => 'Package Materials',
            'qty' => 1,
            'unit' => 'lot',
            'unit_price' => 1074060.00,
            'printed_total' => 1074060.00,
            'computed_total' => 1074060.00,
            'total_mismatch' => false,
        ]);

        DocumentTotal::create([
            'document_id' => $doc->id,
            'printed_subtotal' => 1074060.00,
            'printed_vat' => 0.00,
            'printed_total' => 1074060.00,
            'negotiated_amount' => 1050000.00,
        ]);

        $totals = $this->reconciler->execute($doc);

        $this->assertFalse((bool) $totals->total_mismatch);
        $this->assertEquals(1050000.00, (float) $totals->negotiated_amount);
    }
}
