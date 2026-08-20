<?php

namespace Tests\Unit;

use App\Models\Document;
use App\Models\ProductAlias;
use App\Services\DocumentParsers\DynamicDocumentParser;
use App\Services\DocumentParsers\FieldExtractor;
use App\Services\DocumentParsers\PdfTextExtractor;
use PHPUnit\Framework\TestCase;

class DynamicParserTest extends TestCase
{
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
}
