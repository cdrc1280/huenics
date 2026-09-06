<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderLineItem;
use App\Models\Quotation;
use App\Models\QuotationLineItem;
use App\Models\User;
use App\Services\ExportPurchaseOrderPdf;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuotationAndPurchaseOrderPdfExportTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'role' => User::ROLE_ADMIN,
        ]);
    }

    public function test_quotation_pdf_export_downloads_valid_pdf(): void
    {
        $product = Product::create([
            'canonical_name' => 'LED High Bay Light 150W IP65',
            'sku' => 'HUE-LHB-150',
            'selling_price' => 4500.00,
            'is_active' => true,
        ]);

        $quotation = Quotation::create([
            'quotation_number' => 'QTN-2026-TEST-001',
            'sales_agent_id' => $this->user->id,
            'customer_name' => 'Engr. Ronald Rey Sandoval',
            'customer_company' => 'MGS CONSTRUCTION, INC.',
            'project_name' => 'Palanza Tower Project',
            'project_location' => 'Palanza St. cor. Guirayan St., Doña Imelda, Q.C.',
            'phone_no' => '0906-144-2553',
            'quotation_date' => now()->toDateString(),
            'total_amount' => 45000.00,
            'total_cost' => 0,
            'estimated_profit' => 0,
            'status' => Quotation::STATUS_APPROVED,
        ]);

        QuotationLineItem::create([
            'quotation_id' => $quotation->id,
            'line_no' => 1,
            'item_code' => $product->sku,
            'product_id' => $product->id,
            'description' => $product->canonical_name,
            'qty' => 10,
            'unit' => 'SET',
            'unit_price' => 4500.00,
            'line_total' => 45000.00,
        ]);

        $response = $this->actingAs($this->user)->get(route('quotations.export-pdf', $quotation));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
        $this->assertStringContainsString('attachment;', $response->headers->get('Content-Disposition') ?? '');
        $this->assertStringContainsString('quotation-'.$quotation->quotation_number.'.pdf', $response->headers->get('Content-Disposition') ?? '');
        $this->assertStringStartsWith('%PDF', $response->getContent());
    }

    public function test_quotation_pdf_preview_returns_inline_pdf(): void
    {
        $quotation = Quotation::create([
            'quotation_number' => 'QTN-2026-PREVIEW-001',
            'sales_agent_id' => $this->user->id,
            'customer_name' => 'Acme Builders Corp',
            'quotation_date' => now()->toDateString(),
            'total_amount' => 12500.00,
            'total_cost' => 0,
            'estimated_profit' => 0,
            'status' => Quotation::STATUS_APPROVED,
        ]);

        $response = $this->actingAs($this->user)->get(route('quotations.preview-pdf', $quotation));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
        $this->assertStringContainsString('inline;', $response->headers->get('Content-Disposition') ?? '');
        $this->assertStringStartsWith('%PDF', $response->getContent());
    }

    public function test_purchase_order_pdf_export_downloads_valid_pdf(): void
    {
        $product = Product::create([
            'canonical_name' => 'Deformed Reinforcing Steel Bar 16mm',
            'sku' => 'STEEL-RB-16',
            'selling_price' => 520.00,
            'is_active' => true,
        ]);

        $po = PurchaseOrder::create([
            'po_number' => '4010027093',
            'sales_agent_id' => $this->user->id,
            'customer_name' => 'MGS CONSTRUCTION, INC.',
            'order_amount' => 1050000.00,
            'total_cost' => 840000.00,
            'realized_profit' => 210000.00,
            'printed_vat' => 0,
            'computed_vat' => 0,
            'status' => PurchaseOrder::STATUS_APPROVED,
            'delivery_status' => PurchaseOrder::DELIVERY_PENDING,
            'payment_status' => PurchaseOrder::PAYMENT_STATUS_UNPAID,
            'payment_terms' => 'COD 50% DP; 50% PDC 30 Days',
            'order_date' => now()->toDateString(),
            'expected_delivery_date' => now()->addDays(45)->toDateString(),
            'has_warranty' => true,
            'warranty_period' => PurchaseOrder::WARRANTY_1_YEAR,
            'warranty_status' => PurchaseOrder::WARRANTY_ACTIVE,
        ]);

        PurchaseOrderLineItem::create([
            'purchase_order_id' => $po->id,
            'line_no' => 1,
            'item_code' => $product->sku,
            'product_id' => $product->id,
            'description' => $product->canonical_name,
            'qty' => 200,
            'unit' => 'PC',
            'unit_price' => 520.00,
            'line_total' => 104000.00,
        ]);

        $response = $this->actingAs($this->user)->get(route('purchase-orders.export-pdf', $po));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
        $this->assertStringContainsString('attachment;', $response->headers->get('Content-Disposition') ?? '');
        $this->assertStringContainsString('purchase-order-'.$po->po_number.'.pdf', $response->headers->get('Content-Disposition') ?? '');
        $this->assertStringStartsWith('%PDF', $response->getContent());
    }

    public function test_purchase_order_pdf_preview_returns_inline_pdf(): void
    {
        $po = PurchaseOrder::create([
            'po_number' => 'PO-2026-PREVIEW-001',
            'sales_agent_id' => $this->user->id,
            'customer_name' => 'Palanza Realty Holdings',
            'order_amount' => 75000.00,
            'total_cost' => 0,
            'realized_profit' => 0,
            'printed_vat' => 0,
            'computed_vat' => 0,
            'status' => PurchaseOrder::STATUS_APPROVED,
            'delivery_status' => PurchaseOrder::DELIVERY_PENDING,
            'payment_status' => PurchaseOrder::PAYMENT_STATUS_UNPAID,
            'order_date' => now()->toDateString(),
            'warranty_status' => PurchaseOrder::WARRANTY_NONE,
        ]);

        $response = $this->actingAs($this->user)->get(route('purchase-orders.preview-pdf', $po));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
        $this->assertStringContainsString('inline;', $response->headers->get('Content-Disposition') ?? '');
        $this->assertStringStartsWith('%PDF', $response->getContent());
    }

    public function test_export_purchase_order_pdf_service_direct_generation(): void
    {
        $po = PurchaseOrder::create([
            'po_number' => 'PO-DIRECT-GEN-001',
            'sales_agent_id' => $this->user->id,
            'customer_name' => 'Metro Manila Development Group',
            'order_amount' => 320000.00,
            'total_cost' => 0,
            'realized_profit' => 0,
            'printed_vat' => 0,
            'computed_vat' => 0,
            'status' => PurchaseOrder::STATUS_APPROVED,
            'delivery_status' => PurchaseOrder::DELIVERY_PENDING,
            'payment_status' => PurchaseOrder::PAYMENT_STATUS_UNPAID,
            'order_date' => now()->toDateString(),
            'warranty_status' => PurchaseOrder::WARRANTY_NONE,
        ]);

        $service = app(ExportPurchaseOrderPdf::class);
        $binary = $service->generate($po);

        $this->assertNotEmpty($binary);
        $this->assertStringStartsWith('%PDF', $binary);
    }
}
