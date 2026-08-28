<?php

namespace Tests\Feature;

use App\Actions\IngestDocumentAction;
use App\Enums\WarrantyPeriod;
use App\Models\Document;
use App\Models\DocumentLineItem;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Quotation;
use App\Models\QuotationLineItem;
use App\Models\User;
use App\Services\DocumentParsers\FieldExtractor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NewRequirementsEnhancementTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'email' => 'admin_test_' . uniqid() . '@huenics.com',
        ]);
    }

    public function test_warranty_period_enum_and_purchase_order_options(): void
    {
        // 1. Enum cases exist and return expected labels and months
        $sixMonths = WarrantyPeriod::SixMonths;
        $this->assertEquals('6_months', $sixMonths->value);
        $this->assertEquals('6 Months', $sixMonths->getLabel());
        $this->assertEquals(6, $sixMonths->getMonths());

        $oneYear = WarrantyPeriod::OneYear;
        $this->assertEquals('1_year', $oneYear->value);
        $this->assertEquals('1 Year', $oneYear->getLabel());
        $this->assertEquals(12, $oneYear->getMonths());

        $twoYears = WarrantyPeriod::TwoYears;
        $this->assertEquals('2_years', $twoYears->value);
        $this->assertEquals('2 Years', $twoYears->getLabel());
        $this->assertEquals(24, $twoYears->getMonths());

        // 2. PO options helper returns the 3 selectable options
        $options = PurchaseOrder::getWarrantyPeriodOptions();
        $this->assertArrayHasKey('6_months', $options);
        $this->assertArrayHasKey('1_year', $options);
        $this->assertArrayHasKey('2_years', $options);
        $this->assertEquals('6 Months', $options['6_months']);
        $this->assertEquals('1 Year', $options['1_year']);
        $this->assertEquals('2 Years', $options['2_years']);

        // 3. Months calculation handles all 3 periods
        $this->assertEquals(6, PurchaseOrder::getWarrantyPeriodMonths('6_months'));
        $this->assertEquals(12, PurchaseOrder::getWarrantyPeriodMonths('1_year'));
        $this->assertEquals(24, PurchaseOrder::getWarrantyPeriodMonths('2_years'));
    }

    public function test_field_extractor_captures_payment_and_delivery_terms(): void
    {
        $extractor = new FieldExtractor();

        $sampleText = "
            HUENICS INDUSTRIAL SUPPLY
            Quotation No: QT-2026-0001
            Date: August 28, 2026
            Customer: MGS Construction, Inc.
            Payment Terms: 50% DP, 50% upon delivery
            Terms of Delivery: 15-30 working days upon receipt of approved PO
            Validity: 30 days
            Terms and Conditions:
            1. Prices are subject to change without prior notice.
            2. Deliveries are within Metro Manila only.
        ";

        $paymentTerms = $extractor->extractPaymentTerms($sampleText);
        $this->assertNotNull($paymentTerms);
        $this->assertStringContainsString('50% DP', $paymentTerms);

        $deliveryTerms = $extractor->extractDeliveryTerms($sampleText);
        $this->assertNotNull($deliveryTerms);
        $this->assertStringContainsString('15-30 working days', $deliveryTerms);

        $termsAndConditions = $extractor->extractTermsAndConditions($sampleText);
        $this->assertNotNull($termsAndConditions);
        $this->assertStringContainsString('Prices are subject to change', $termsAndConditions);
    }

    public function test_quotation_and_po_models_support_terms_and_conforme_fields(): void
    {
        $quotation = Quotation::create([
            'sales_agent_id' => $this->admin->id,
            'quotation_date' => now()->toDateString(),
            'quotation_number' => 'QT-2026-9999',
            'customer_name' => 'ABC Builders Corp.',
            'terms_and_conditions' => 'Standard commercial terms apply.',
            'payment_terms' => '30 Days Net',
            'delivery_terms' => 'FOB Jobsite',
            'is_official_po' => true,
            'customer_signature_name' => 'Engr. Juan Dela Cruz',
        ]);

        $this->assertEquals('Standard commercial terms apply.', $quotation->terms_and_conditions);
        $this->assertEquals('30 Days Net', $quotation->payment_terms);
        $this->assertEquals('FOB Jobsite', $quotation->delivery_terms);
        $this->assertTrue($quotation->is_official_po);
        $this->assertEquals('Engr. Juan Dela Cruz', $quotation->customer_signature_name);

        $po = PurchaseOrder::create([
            'sales_agent_id' => $this->admin->id,
            'order_date' => now()->toDateString(),
            'po_number' => 'PO-2026-9999',
            'customer_name' => 'ABC Builders Corp.',
            'terms_and_conditions' => 'Standard commercial terms apply.',
            'payment_terms' => '30 Days Net',
            'delivery_terms' => 'FOB Jobsite',
            'is_conforme_po' => true,
            'warranty_period' => '2_years',
        ]);

        $this->assertEquals('Standard commercial terms apply.', $po->terms_and_conditions);
        $this->assertEquals('30 Days Net', $po->payment_terms);
        $this->assertTrue($po->is_conforme_po);
        $this->assertEquals('2_years', $po->warranty_period);
    }

    public function test_conforme_po_is_exempt_from_quotation_line_item_verification(): void
    {
        $product = Product::create([
            'canonical_name' => 'Heavy Duty Ball Valve 1/2"',
            'sku' => 'VALVE-12',
            'selling_price' => 500.00,
            'unit_default' => 'pcs',
            'is_active' => true,
        ]);

        // Create a quotation with 10 pcs @ 500
        $quotation = Quotation::create([
            'sales_agent_id' => $this->admin->id,
            'quotation_date' => now()->toDateString(),
            'quotation_number' => 'QT-2026-CONFORME-1',
            'customer_name' => 'XYZ Engineering',
            'total_amount' => 5000.00,
            'status' => Quotation::STATUS_APPROVED,
        ]);
        $quotation->lineItems()->create([
            'line_no' => 1,
            'product_id' => $product->id,
            'item_code' => 'VALVE-12',
            'description' => 'Heavy Duty Ball Valve 1/2"',
            'qty' => 10,
            'unit' => 'pcs',
            'unit_price' => 500.00,
            'line_total' => 5000.00,
        ]);

        // Create a Conforme PO with completely different qty and price (50 pcs @ 450)
        $doc = Document::create([
            'uploaded_by' => $this->admin->id,
            'original_filename' => 'po_conforme_001.pdf',
            'disk_path' => 'documents/uploads/po_conforme_001.pdf',
            'file_hash' => hash('sha256', 'po_conforme_001'),
            'document_date' => now()->toDateString(),
            'document_type' => Document::TYPE_PURCHASE_ORDER,
            'document_number' => 'PO-CONFORME-001',
            'raw_extracted_text' => "PURCHASE ORDER\nCONFORMED BY: Supplier Representative\nItem: VALVE-12 Qty: 50 Price: 450.00",
            'status' => Document::STATUS_VERIFIED,
        ]);

        $po = PurchaseOrder::create([
            'sales_agent_id' => $this->admin->id,
            'order_date' => now()->toDateString(),
            'po_number' => 'PO-CONFORME-001',
            'document_id' => $doc->id,
            'quotation_id' => $quotation->id,
            'customer_name' => 'XYZ Engineering',
            'is_conforme_po' => true, // Conforme PO
            'order_amount' => 22500.00,
            'status' => PurchaseOrder::STATUS_PENDING,
        ]);
        $po->lineItems()->create([
            'line_no' => 1,
            'product_id' => $product->id,
            'item_code' => 'VALVE-12',
            'description' => 'Heavy Duty Ball Valve 1/2"',
            'qty' => 50, // Differs from quotation's 10
            'unit' => 'pcs',
            'unit_price' => 450.00, // Differs from quotation's 500
            'line_total' => 22500.00,
        ]);

        // Verify that IngestDocumentAction does NOT fail or block conforme POs
        $action = app(IngestDocumentAction::class);
        $method = new \ReflectionMethod($action, 'verifyPOAgainstQuotation');
        $method->setAccessible(true);

        // This should execute smoothly without throwing exceptions because is_conforme_po is true
        $method->invoke($action, $doc, $po);
        $this->assertTrue($po->is_conforme_po);
    }
}
