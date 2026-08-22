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

    public function test_ingesting_vendors_agreement_with_variations_and_footer_filtering(): void
    {
        $parser = app(\App\Services\DocumentParsers\DynamicDocumentParser::class);
        $doc = Document::create([
            'vendor_id' => $this->vendor->id,
            'project_id' => $this->project->id,
            'uploaded_by' => $this->user->id,
            'document_type' => Document::TYPE_VENDORS_AGREEMENT,
            'document_number' => '25100163 - P rev.2',
            'original_filename' => 'VendorsAgreement.pdf',
            'disk_path' => 'documents/uploads/vendorsagreement.pdf',
            'file_hash' => 'hash_vendors_agreement_test_full',
            'status' => Document::STATUS_UPLOADED,
        ]);

        $ocrText = <<<OCR
VENDORS AGREEMENT FORM
Quotation No. 25100163 - P rev.2 Date 01/05/25 Customer Name Engr. Ronald Rey Sandoval Company MGS CONSTRUCTION, INC.
Address 2F Starmall Annex, Alabang-Zapote Road, corner Doña Manuela Avenue, Pamplona III, Las Pinas, For Project Palanza Tower
Project Location Palanza St. corner guirayan st., Dona Imelda, Q.C Phone No. 0906-144-2553
Item Code Product Description References from Client Qty Unit Unit Price Discounted Price Total HISI - MTL- 6W Magnetic Tracklight 6w 3000k 158 pcs 2,100.00 1,890.00 298,620.00 HISI - MTL- 6W Magnetic Tracklight 6w 3000k Color White 5 pcs 2,100.00 1,890.00 9,450.00 HISI - MTL- 6W Magnetic Tracklight Movable 6w 3000k 4 pcs 2,200.00 1,980.00 7,920.00 HISI-S20-3M Magnetic Trackbar 3 meters 58 pcs 7,500.00 6,750.00 391,500.00 HISI-S20-3M Magnetic Trackbar 3 meters Color White 2 pcs 7,500.00 6,750.00 13,500.00 HISI-S20-2M Magnetic Trackbar 2 meters 14 pcs 4,800.00 4,320.00 60,480.00 HISI-S20-1M Magnetic Trackbar 1 meter 18 pcs 3,600.00 3,240.00 58,320.00
90° L connector 56 pcs 1,100.00 990.00 55,440.00
straight connector 34 pcs 700.00 630.00 21,420.00
End Cap 8 pcs 300.00 270.00 2,160.00 HISI-LD-100W Led Driver for Magnetic Tracklight 100w 15 pcs 3,250.00 2,925.00 43,875.00
Led Driver for Magnetic Tracklight 200w
(to be verified actual with client) 2 pcs 3,685.00 3,350.00 6,700.00
Total Amount: 969,385.00 Negotiated Amount: 950,000.00
Prices are subject to change without prior notice. (VAT INC.) Terms and Conditions Validity 15 days Stock Availability ✔ Stock Non-Stock / Special Items Terms Of Delivery 4-7 days 10-15 days ✔ 30-45days Payment Terms 50% DP, BAL. COD PDC 30 Days PDC 30 Days Remarks Serve as an Official P.O. ✔ Non- Returnable/ Non- Cancealable NOTES: * Minimum amount of order should be Php 20,000 .00 above for Free Delivery within Metro Manila. Outside Metro Manila Shipment cost will be applied. * Return & Exchange of Items should be within 7 days upon delivery. * Gate fees or any other entrance fees not included. Additional charges shall be applied for deliveries before or after office hour. * Please inspect item before installation. Complaints will not be enetertained after items have been installed. * Special order, sale/phase out and non-regular items are not allowed for return. I/We hereby agree and accept the Terms and Conditions written above on this form. How To Claim The Warranty Customer's Name over Signature: ____________________ __1__Yr. Limited warranty w/o physical damage *7 days item change policy provided that it must be in good Condition w/ complete accessories, packaging.
OCR;

        $reflection = new \ReflectionClass($parser);
        $method = $reflection->getMethod('extractLineItems');
        $method->setAccessible(true);
        $lineItems = $method->invoke($parser, null, $ocrText, explode("\n", $ocrText), $doc);

        $this->assertCount(12, $lineItems);

        // Check first 3 items share series code but have distinct product records & descriptions
        $this->assertEquals('HISI - MTL- 6W', $lineItems[0]['material_code']);
        $this->assertEquals('Magnetic Tracklight 6w 3000k', $lineItems[0]['description']);
        $this->assertEquals(158.0, $lineItems[0]['qty']);

        $this->assertEquals('HISI - MTL- 6W', $lineItems[1]['material_code']);
        $this->assertEquals('Magnetic Tracklight 6w 3000k Color White', $lineItems[1]['description']);
        $this->assertEquals(5.0, $lineItems[1]['qty']);

        $this->assertEquals('HISI - MTL- 6W', $lineItems[2]['material_code']);
        $this->assertEquals('Magnetic Tracklight Movable 6w 3000k', $lineItems[2]['description']);
        $this->assertEquals(4.0, $lineItems[2]['qty']);

        // Products created are distinct
        $this->assertNotEquals($lineItems[0]['product_id'], $lineItems[1]['product_id']);
        $this->assertNotEquals($lineItems[1]['product_id'], $lineItems[2]['product_id']);

        // Check items without item code
        $this->assertNull($lineItems[7]['material_code']);
        $this->assertEquals('90° L connector', $lineItems[7]['description']);

        $this->assertNull($lineItems[8]['material_code']);
        $this->assertEquals('straight connector', $lineItems[8]['description']);

        $this->assertNull($lineItems[9]['material_code']);
        $this->assertEquals('End Cap', $lineItems[9]['description']);

        // Check quantity self-healing on LED Driver (15 pcs @ 2925 = 43875)
        $this->assertEquals('HISI-LD-100W', $lineItems[10]['material_code']);
        $this->assertEquals(15.0, $lineItems[10]['qty']);
        $this->assertEquals(3250.00, $lineItems[10]['unit_price']);
        $this->assertEquals(2925.00, $lineItems[10]['discounted_price']);
        $this->assertEquals(43875.00, $lineItems[10]['printed_total']);

        // Ensure footer notes were NEVER captured as items
        foreach ($lineItems as $item) {
            $this->assertStringNotContainsString('Minimum amount', $item['description']);
            $this->assertStringNotContainsString('Special order', $item['description']);
            $this->assertStringNotContainsString('Return & Exchange', $item['description']);
            $this->assertStringNotContainsString('Gate fees', $item['description']);
            $this->assertStringNotContainsString('Warranty', $item['description']);
        }
    }

    public function test_ingesting_merged_price_numbers_and_downlight_document(): void
    {
        $parser = app(\App\Services\DocumentParsers\DynamicDocumentParser::class);

        // Document with concatenated numbers without spaces: 3,250.002,925.0043,875.00Led Driver...
        $doc1 = Document::create([
            'vendor_id' => $this->vendor->id,
            'project_id' => $this->project->id,
            'uploaded_by' => $this->user->id,
            'document_type' => Document::TYPE_VENDORS_AGREEMENT,
            'document_number' => 'DOC-MERGED-123',
            'original_filename' => 'Merged.pdf',
            'disk_path' => 'documents/uploads/merged.pdf',
            'file_hash' => 'hash_merged_123',
            'status' => Document::STATUS_UPLOADED,
        ]);

        $mergedText = <<<OCR
Item Code Product Description Qty Unit Unit Price Discounted Price Total
End Cap 8 pcs 300.00270.002,160.00HISI-LD-100W Led Driver for Magnetic Tracklight 100w 15 pcs 3,250.002,925.0043,875.00Led Driver for Magnetic Tracklight 200w
(to be verified actual with client) 2 pcs 3,685.003,350.006,700.00
Total Amount: 52,735.00 Negotiated Amount: 50,000.00
OCR;

        $reflection = new \ReflectionClass($parser);
        $method = $reflection->getMethod('extractLineItems');
        $method->setAccessible(true);
        $items1 = $method->invoke($parser, null, $mergedText, explode("\n", $mergedText), $doc1);

        $this->assertCount(3, $items1);
        $this->assertEquals(8.0, $items1[0]['qty']);
        $this->assertEquals(300.0, $items1[0]['unit_price']);
        $this->assertEquals(2160.0, $items1[0]['printed_total']);

        $this->assertEquals('HISI-LD-100W', $items1[1]['material_code']);
        $this->assertEquals(15.0, $items1[1]['qty']);
        $this->assertEquals(3250.0, $items1[1]['unit_price']);
        $this->assertEquals(2925.0, $items1[1]['discounted_price']);
        $this->assertEquals(43875.0, $items1[1]['printed_total']);

        $this->assertNull($items1[2]['material_code']);
        $this->assertEquals(2.0, $items1[2]['qty']);
        $this->assertEquals(3685.0, $items1[2]['unit_price']);
        $this->assertEquals(3350.0, $items1[2]['discounted_price']);
        $this->assertEquals(6700.0, $items1[2]['printed_total']);

        // Downlight document with sizes and warranties
        $doc2 = Document::create([
            'vendor_id' => $this->vendor->id,
            'project_id' => $this->project->id,
            'uploaded_by' => $this->user->id,
            'document_type' => Document::TYPE_VENDORS_AGREEMENT,
            'document_number' => '261001- P',
            'original_filename' => 'Downlight.pdf',
            'disk_path' => 'documents/uploads/downlight.pdf',
            'file_hash' => 'hash_downlight_123',
            'status' => Document::STATUS_UPLOADED,
        ]);

        $downlightText = <<<OCR
VENDORS AGREEMENT FORM
Quotation No. 261001- P Date 01/05/26
Customer Name Engr. Ronald Rey Sandoval
Company MGS CONSTRUCTION, INC. Address
2F Starmall Annex, Alabang-Zapote Road, corner Doña Manuela Avenue, Pamplona III, Las Pinas, For Project Palanza Tower Project Location Palanza St. corner Guirayan st., Dona Imelda, Q.C
Phone No. 0906-144-2553
Item Code Product Description Qty Unit Unit Price Discounted Price Total HISI-JF-2240-7w
Led Downlight C.O.B Citizen Japan 3500k Warmwhite 7w
Size:Ø110mm x 25mm, white casing
Warranty: 2 yrs 500 pcs 1,950.00 1,755.00 877,500.00
HISI-JF-2240-7w
Led Downlight C.O.B Citizen Japan 3500k Warmwhite 7w
Size:Ø110mm x 25mm, black casing
Warranty: 2 yrs 112 pcs 1,950.00 1,755.00 196,560.00
Total Amount 1,074,060.00
Negotiated Amount: 1,050,000.00
OCR;

        $items2 = $method->invoke($parser, null, $downlightText, explode("\n", $downlightText), $doc2);
        $this->assertCount(2, $items2);

        $this->assertEquals('HISI-JF-2240-7w', $items2[0]['material_code']);
        $this->assertEquals(500.0, $items2[0]['qty']);
        $this->assertEquals(1950.00, $items2[0]['unit_price']);
        $this->assertEquals(1755.00, $items2[0]['discounted_price']);
        $this->assertEquals(877500.00, $items2[0]['printed_total']);

        $this->assertEquals('HISI-JF-2240-7w', $items2[1]['material_code']);
        $this->assertEquals(112.0, $items2[1]['qty']);
        $this->assertEquals(1950.00, $items2[1]['unit_price']);
        $this->assertEquals(1755.00, $items2[1]['discounted_price']);
        $this->assertEquals(196560.00, $items2[1]['printed_total']);
    }

    public function test_uploading_po_with_linked_quotation_links_and_converts_quotation(): void
    {
        $quotation = Quotation::create([
            'quotation_number' => 'QUOT-TEST-LINK-001',
            'customer_name' => 'Acme Corp',
            'sales_agent_id' => $this->user->id,
            'project_id' => $this->project->id,
            'total_amount' => 50000.00,
            'status' => Quotation::STATUS_APPROVED,
            'quotation_date' => now()->toDateString(),
        ]);

        $action = app(\App\Actions\IngestDocumentAction::class);
        $doc = $action->execute(
            diskPath: 'documents/uploads/test_po_upload.pdf',
            originalFilename: 'test_po_upload.pdf',
            documentType: Document::TYPE_PURCHASE_ORDER,
            userId: $this->user->id,
            quotationId: $quotation->id
        );

        $po = PurchaseOrder::where('document_id', $doc->id)->first();
        $this->assertNotNull($po);
        $this->assertEquals($quotation->id, $po->quotation_id);
        $this->assertEquals(Quotation::STATUS_CONVERTED, $quotation->fresh()->status);
    }
}



