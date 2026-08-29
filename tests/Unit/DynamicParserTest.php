<?php

namespace Tests\Unit;

use App\Models\Document;
use App\Models\ProductAlias;
use App\Services\DocumentParsers\DynamicDocumentParser;
use App\Services\DocumentParsers\FieldExtractor;
use App\Services\DocumentParsers\PdfTextExtractor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DynamicParserTest extends TestCase
{
    use RefreshDatabase;

    protected DynamicDocumentParser $parser;
    protected \App\Models\User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = \App\Models\User::factory()->create();
        $this->parser = new DynamicDocumentParser(
            new PdfTextExtractor(),
            new FieldExtractor()
        );
    }

    public function test_document_type_detection(): void
    {
        $poText = "MGS CONSTRUCTION CORPORATION\nPURCHASE ORDER\nP.O. No: 4010027092\nDate: August 1, 2026";
        $this->assertEquals(Document::TYPE_PURCHASE_ORDER, $this->parser->detectDocumentType($poText));

        $osText = "HUENICS INDUSTRIAL SUPPLY\nORDER SLIP\nS.O.# 26005\nDate: August 2, 2026";
        $this->assertEquals(Document::TYPE_PURCHASE_ORDER, $this->parser->detectDocumentType($osText));

        $vafText = "HUENICS INDUSTRIAL SUPPLY\nVENDORS AGREEMENT FORM (QUOTATION)\nQuotation No: VAF-2026-081";
        $this->assertEquals(Document::TYPE_VENDORS_AGREEMENT, $this->parser->detectDocumentType($vafText));
    }

    public function test_product_alias_normalization(): void
    {
        $raw1 = "  1-1/4\" PVC Pipe Sch 40  ";
        $this->assertEquals('1-1/4 pvc pipe sch 40', ProductAlias::normalize($raw1));

        $raw2 = "PVC PIPE (1.25 INCH) - HEAVY DUTY";
        $this->assertEquals('pvc pipe 1.25 inch - heavy duty', ProductAlias::normalize($raw2));
    }

    public function test_field_extractor_post_processing(): void
    {
        $extractor = new FieldExtractor();

        $this->assertEquals(1880.56, $extractor->postProcess("1,880.56", 'parse_decimal'));
        $this->assertEquals(1050000.00, $extractor->postProcess("PHP 1,050,000.00", 'parse_decimal'));
        $this->assertEquals(30, $extractor->postProcess("30 items", 'parse_int'));
        $this->assertEquals('2026-08-01', $extractor->postProcess("August 1, 2026", 'parse_date'));
        $this->assertEquals('HUENICS SUPPLY', $extractor->postProcess("  huenics supply  ", 'uppercase'));
    }

    public function test_unit_of_measure_enum_has_options_and_labels(): void
    {
        $options = \App\Enums\UnitOfMeasure::options();
        $this->assertArrayHasKey('pcs', $options);
        $this->assertArrayHasKey('set', $options);
        $this->assertArrayHasKey('meter', $options);
        $this->assertEquals('pcs', \App\Enums\UnitOfMeasure::Pcs->getLabel());
    }

    public function test_table_footer_stop_markers_prevent_terms_and_notes_from_being_items(): void
    {
        $ocrText = <<<OCR
Item Code Product Description References from Client Qty Unit Unit Price Discounted Price Total
HISI - MTL- 6W Magnetic Tracklight 6w 3000k 158 pcs 2,100.00 1,890.00 298,620.00
90° L connector 56 pcs 1,100.00 990.00 55,440.00
Total Amount: 354,060.00 Negotiated Amount: 350,000.00
NOTES: * Minimum amount of order should be Php 20,000 .00 above for Free Delivery within Metro Manila.
* Return & Exchange of Items should be within 7 days upon delivery.
* Special order, sale/phase out and non-regular items are not allowed for return.
OCR;

        $doc = new Document();
        $ref = new \ReflectionClass($this->parser);
        $method = $ref->getMethod('extractLineItems');
        $method->setAccessible(true);

        $items = $method->invoke($this->parser, null, $ocrText, explode("\n", $ocrText), $doc);

        $this->assertCount(2, $items);
        $this->assertEquals('HISI - MTL- 6W', $items[0]['material_code']);
        $this->assertEquals('Magnetic Tracklight 6w 3000k', $items[0]['description']);
        $this->assertEquals(158.0, $items[0]['qty']);

        $this->assertNull($items[1]['material_code']);
        $this->assertEquals('90° L connector', $items[1]['description']);
        $this->assertEquals(56.0, $items[1]['qty']);
    }

    public function test_parse_single_page_purchase_order_reference_4010027093(): void
    {
        $po1Text = <<<OCR
No. 4010027093
MGS CONSTRUCTION, INC.
2f Starmall Annex, Alabang-Zapote Rd.
Cor. Dona Manuela Ave, Pamplona Tres Las Pinas City
Tel.No.(02) 814-4600
VAT.Reg.TIN:005-129-052-00000
PURCHASE ORDER
10 500 PC 7W LED COB Down Light, 3500K,
White Rim 
 1,730.94 865,470.00
20 112 PC 7W LED COB Down Light, 3500K,
Black Rim 
 1,647.59 184,530.00
*************NOTHING FOLLOWS************
SUBTOTAL 937,500.00 PHP
VAT 112,500.00 PHP
TOTAL 1,050,000.00 PHP
Item
No. Material Code Qty UoM Material Description Unit Cost Total Cost
Vendor: 203974 HUENICS INDUSTRIAL SALES INC.
Address: ZULUETA STREET BARANGAY 678 ZONE 74 916
Date: 01/08/2026 Delivery Date: 01/26/2026
Deliver To: 3030 Palanza Tower
Address: Brgy. Doña Imelda, Quezon
OCR;

        $doc = Document::create([
            'uploaded_by' => $this->user->id,
            'file_hash' => 'hash_test_po_single_page',
            'original_filename' => 'PO_4010027093.pdf',
            'disk_path' => 'documents/uploads/PO_4010027093.pdf',
            'document_type' => Document::TYPE_PURCHASE_ORDER,
            'raw_extracted_text' => $po1Text,
        ]);

        $this->parser->parseDocument($doc);

        $this->assertEquals('4010027093', $doc->document_number);
        $this->assertEquals('MGS CONSTRUCTION, INC', $doc->customer_company);
        $this->assertEquals('Palanza Tower', $doc->project_name);
        $this->assertEquals(2, $doc->lineItems()->count());

        $item1 = $doc->lineItems->first();
        $this->assertEquals(500.0, (float) $item1->qty);
        $this->assertEquals('pc', $item1->unit);
        $this->assertEquals(1730.94, (float) $item1->unit_price);
        $this->assertEquals(865470.00, (float) $item1->printed_total);

        $this->assertEquals(937500.00, (float) $doc->totals->printed_subtotal);
        $this->assertEquals(112500.00, (float) $doc->totals->printed_vat);
        $this->assertEquals(1050000.00, (float) $doc->totals->printed_total);
    }

    public function test_parse_multi_page_purchase_order_reference_4010027092(): void
    {
        $po2Text = <<<OCR
No. 4010027092
MGS CONSTRUCTION, INC.
2f Starmall Annex, Alabang-Zapote Rd.
VAT.Reg.TIN:005-129-052-00000
PURCHASE ORDER
30 158 PC Magnetic Track Light 6W, 3000K 1,880.56 297,128.85
40 38 PC Magnetic Track Bar, 3m 6,710.76 255,008.85
50 14 PC Magnetic Track Bar, 2m 4,213.49 58,988.85
60 18 PC Magnetic Track Bar, 1m 3,157.16 56,828.84
70 56 PC Track Bar 90 degrees Connector 963.37 53,948.85
80 34 PC Track Bar Straight Connector 586.14 19,928.85
90 8 PC End Cap 83.61 668.85
100 15 PC LED Driver, 100W 2,825.59 42,383.85
110 2 PC LED Driver, 200W 2,604.43 5,208.85
120 5 PC Magnetic Track Light 6W, 3000K, 1,591.77 7,958.85
Item
No. Material Code Qty UoM Material Description Unit Cost Total Cost
Vendor: 203974 HUENICS INDUSTRIAL SALES INC.
Date: 01/08/2026 Delivery Date: 01/22/2026
Deliver To: 3030 Palanza Tower
Page 1 of 2

No. 4010027092
MGS CONSTRUCTION, INC.
PURCHASE ORDER
White 
130 20 PC Magnetic Track Bar, 3m 6,675.44 133,508.85
140 2 PC Magnetic Track Bar, 3m, White 6,004.43 12,008.85
150 4 PC Magnetic Track Light 6W, 3000K,
Movable 
 1,607.21 6,428.84
*************NOTHING FOLLOWS************
SUBTOTAL 848,214.34 PHP
VAT 101,785.69 PHP
TOTAL 950,000.03 PHP
Item
No. Material Code Qty UoM Material Description Unit Cost Total Cost
Page 2 of 2
OCR;

        $doc = Document::create([
            'uploaded_by' => $this->user->id,
            'file_hash' => 'hash_test_po_multi_page',
            'original_filename' => 'PO_4010027092.pdf',
            'disk_path' => 'documents/uploads/PO_4010027092.pdf',
            'document_type' => Document::TYPE_PURCHASE_ORDER,
            'raw_extracted_text' => $po2Text,
        ]);

        $this->parser->parseDocument($doc);

        $this->assertEquals('4010027092', $doc->document_number);
        $this->assertEquals('MGS CONSTRUCTION, INC', $doc->customer_company);
        $this->assertEquals('Palanza Tower', $doc->project_name);
        $this->assertEquals(13, $doc->lineItems()->count());

        $this->assertEquals(848214.34, (float) $doc->totals->printed_subtotal);
        $this->assertEquals(101785.69, (float) $doc->totals->printed_vat);
        $this->assertEquals(950000.03, (float) $doc->totals->printed_total);
    }

    // ═══════════════════════════════════════════════════════════════════
    // Document Number Extraction Accuracy Tests
    // ═══════════════════════════════════════════════════════════════════

    public function test_quotation_number_extraction_vaf_dash_format(): void
    {
        $text = <<<OCR
HUENICS INDUSTRIAL SUPPLY
VENDORS AGREEMENT FORM (QUOTATION)
Quotation No: VAF-2026-081
Date: August 5, 2026
Customer: MGS CONSTRUCTION
Item Code Product Description Qty Unit Unit Price Discounted Price Total
HISI-MTL-6W Magnetic Tracklight 6w 3000k 158 pcs 2,100.00 1,890.00 298,620.00
Total Amount: 298,620.00
OCR;

        $doc = Document::create([
            'uploaded_by' => $this->user->id,
            'file_hash' => 'hash_vaf_dash_' . uniqid(),
            'original_filename' => 'VAF-2026-081.pdf',
            'disk_path' => 'documents/uploads/VAF-2026-081.pdf',
            'document_type' => Document::TYPE_VENDORS_AGREEMENT,
            'raw_extracted_text' => $text,
        ]);

        $this->parser->parseDocument($doc);

        $this->assertEquals('VAF-2026-081', $doc->document_number);
    }

    public function test_quotation_number_extraction_labeled_format(): void
    {
        $text = <<<OCR
HUENICS INDUSTRIAL SUPPLY
VENDORS AGREEMENT FORM (QUOTATION)
Quotation No. 261001- P
Date: January 5, 2026
Company: MGS CONSTRUCTION, INC.
Item Code Product Description Qty Unit Unit Price Discounted Price Total
HISI-JF-2240-7W Led Downlight Citizen Japan 7w 500 pcs 1,730.94 1,557.85 778,925.00
Total Amount: 778,925.00
OCR;

        $doc = Document::create([
            'uploaded_by' => $this->user->id,
            'file_hash' => 'hash_labeled_qtn_' . uniqid(),
            'original_filename' => 'VAF_261001_P.pdf',
            'disk_path' => 'documents/uploads/VAF_261001_P.pdf',
            'document_type' => Document::TYPE_VENDORS_AGREEMENT,
            'raw_extracted_text' => $text,
        ]);

        $this->parser->parseDocument($doc);

        $this->assertEquals('261001- P', $doc->document_number);
    }

    public function test_po_number_extraction_mgs_10_digit(): void
    {
        $text = <<<OCR
No. 4010027093
MGS CONSTRUCTION, INC.
2f Starmall Annex, Alabang-Zapote Rd.
VAT.Reg.TIN:005-129-052-00000
PURCHASE ORDER
10 500 PC 7W LED COB Down Light, 3500K, White Rim 1,730.94 865,470.00
SUBTOTAL 865,470.00 PHP
TOTAL 865,470.00 PHP
Date: 01/08/2026 Delivery Date: 01/26/2026
OCR;

        $doc = Document::create([
            'uploaded_by' => $this->user->id,
            'file_hash' => 'hash_mgs_po_' . uniqid(),
            'original_filename' => 'PO_4010027093.pdf',
            'disk_path' => 'documents/uploads/PO_4010027093.pdf',
            'document_type' => Document::TYPE_PURCHASE_ORDER,
            'raw_extracted_text' => $text,
        ]);

        $this->parser->parseDocument($doc);

        $this->assertEquals('4010027093', $doc->document_number);
    }

    public function test_po_number_extraction_with_po_label(): void
    {
        $text = <<<OCR
ACME BUILDERS CORPORATION
PURCHASE ORDER
P.O. No: PO-2026-0045
Date: August 15, 2026
10 100 PCS LED Panel Light 40W 2,500.00 250,000.00
SUBTOTAL 250,000.00 PHP
TOTAL 250,000.00 PHP
OCR;

        $doc = Document::create([
            'uploaded_by' => $this->user->id,
            'file_hash' => 'hash_po_label_' . uniqid(),
            'original_filename' => 'PO-2026-0045.pdf',
            'disk_path' => 'documents/uploads/PO-2026-0045.pdf',
            'document_type' => Document::TYPE_PURCHASE_ORDER,
            'raw_extracted_text' => $text,
        ]);

        $this->parser->parseDocument($doc);

        $this->assertEquals('PO-2026-0045', $doc->document_number);
    }

    public function test_order_slip_number_extraction(): void
    {
        $text = <<<OCR
HUENICS INDUSTRIAL SUPPLY
ORDER SLIP
S.O.# 26005
Date: August 2, 2026
Deliver To: 3030 Palanza Tower
10 50 PCS LED Driver 100W 2,825.59 141,279.50
TOTAL 141,279.50 PHP
OCR;

        $doc = Document::create([
            'uploaded_by' => $this->user->id,
            'file_hash' => 'hash_order_slip_' . uniqid(),
            'original_filename' => 'OS_26005.pdf',
            'disk_path' => 'documents/uploads/OS_26005.pdf',
            'document_type' => Document::TYPE_PURCHASE_ORDER,
            'raw_extracted_text' => $text,
        ]);

        $this->parser->parseDocument($doc);

        $this->assertEquals('26005', $doc->document_number);
    }

    public function test_date_extraction_avoids_delivery_date(): void
    {
        $text = <<<OCR
No. 4010027093
MGS CONSTRUCTION, INC.
PURCHASE ORDER
10 500 PC LED Down Light 1,730.94 865,470.00
TOTAL 865,470.00 PHP
Date: 01/08/2026 Delivery Date: 01/26/2026
OCR;

        $doc = Document::create([
            'uploaded_by' => $this->user->id,
            'file_hash' => 'hash_date_test_' . uniqid(),
            'original_filename' => 'PO_date_test.pdf',
            'disk_path' => 'documents/uploads/PO_date_test.pdf',
            'document_type' => Document::TYPE_PURCHASE_ORDER,
            'raw_extracted_text' => $text,
        ]);

        $this->parser->parseDocument($doc);

        // Should extract order date (01/08/2026), NOT delivery date (01/26/2026)
        $this->assertNotNull($doc->document_date);
        $this->assertEquals('2026-01-08', $doc->document_date->format('Y-m-d'));
    }

    public function test_quotation_number_extraction_vaf_hash_format(): void
    {
        $text = <<<OCR
HUENICS INDUSTRIAL SUPPLY
VENDORS AGREEMENT FORM (QUOTATION)
VAF#251000163- P rev.2 - Palanza Tower - Magnetic Tracklights
Date: July 15, 2026
Company: MGS CONSTRUCTION, INC.
Item Code Product Description Qty Unit Unit Price Discounted Price Total
HISI-MTL-6W Magnetic Tracklight 6w 3000k 158 pcs 2,100.00 1,890.00 298,620.00
90° L connector 56 pcs 1,100.00 990.00 55,440.00
Total Amount: 354,060.00 Negotiated Amount: 350,000.00
OCR;

        $doc = Document::create([
            'uploaded_by' => $this->user->id,
            'file_hash' => 'hash_vaf_hash_' . uniqid(),
            'original_filename' => 'VAF_251000163.pdf',
            'disk_path' => 'documents/uploads/VAF_251000163.pdf',
            'document_type' => Document::TYPE_VENDORS_AGREEMENT,
            'raw_extracted_text' => $text,
        ]);

        $this->parser->parseDocument($doc);

        // Should extract the VAF# number with prefix preserved
        $this->assertNotNull($doc->document_number);
        $this->assertStringContainsString('251000163', $doc->document_number);
    }

    public function test_field_extractor_extract_by_rules_works(): void
    {
        $extractor = new FieldExtractor();
        $text = "PURCHASE ORDER\nP.O. No: PO-2026-0045\nDate: August 15, 2026";
        $lines = explode("\n", $text);

        // Test regex_header strategy
        $result = $extractor->extractByRules($text, $lines, [
            'extraction_strategy' => 'regex_header',
            'regex_pattern' => '/P\.O\.\s*No:\s*([A-Za-z0-9\-]+)/i',
            'post_process' => 'trim',
        ]);
        $this->assertEquals('PO-2026-0045', $result);

        // Test with string shorthand (simple regex)
        $result2 = $extractor->extractByRules($text, $lines, '/P\.O\.\s*No:\s*([A-Za-z0-9\-]+)/i');
        $this->assertEquals('PO-2026-0045', $result2);

        // Test returns null on no match
        $result3 = $extractor->extractByRules($text, $lines, [
            'extraction_strategy' => 'regex_header',
            'regex_pattern' => '/NONEXISTENT:\s*(\w+)/i',
            'post_process' => 'trim',
        ]);
        $this->assertNull($result3);
    }
}
