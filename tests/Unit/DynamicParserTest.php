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

    protected function setUp(): void
    {
        parent::setUp();
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
}
