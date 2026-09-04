<?php

namespace Tests\Feature;

use App\Enums\DeliveryReceiptStatus;
use App\Enums\SalesInvoiceStatus;
use App\Models\DeliveryReceipt;
use App\Models\Product;
use App\Models\Project;
use App\Models\PurchaseOrder;
use App\Models\SalesInvoice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MultipleDrAndSiOptimizationTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected Product $productA;
    protected Product $productB;
    protected Project $project;
    protected PurchaseOrder $purchaseOrder;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role'  => User::ROLE_ADMIN,
            'name'  => 'Admin User',
            'email' => 'admin@huenics.com',
        ]);

        $this->project = Project::create([
            'name'        => 'Palanza Tower',
            'code'        => 'PLZ-TWR-01',
            'client_name' => 'MGS Construction, Inc.',
            'location'    => '2F Starmall Annex Alabang-Zapote Rd., Las Pinas City',
            'status'      => 'active',
        ]);

        $this->productA = Product::create([
            'product_code'    => 'HISI-MTL-6W',
            'canonical_name'  => 'Magnetic Tracklight 6w 3000k',
            'unit_default'    => 'pcs',
            'default_price'   => 2100.00,
            'base_cost_price' => 1500.00,
            'is_huenics_owned' => true,
        ]);

        $this->productB = Product::create([
            'product_code'    => 'HISI-S20-2M',
            'canonical_name'  => 'Magnetic Trackbar 2 meters',
            'unit_default'    => 'pcs',
            'default_price'   => 4800.00,
            'base_cost_price' => 3200.00,
            'is_huenics_owned' => true,
        ]);

        $this->purchaseOrder = PurchaseOrder::create([
            'po_number'       => '4010027092',
            'customer_name'   => 'MGS Construction, Inc.',
            'project_id'      => $this->project->id,
            'sales_agent_id'  => $this->admin->id,
            'order_amount'    => 950000.00,
            'total_cost'      => 650000.00,
            'realized_profit' => 300000.00,
            'status'          => PurchaseOrder::STATUS_APPROVED,
            'delivery_status' => PurchaseOrder::DELIVERY_PENDING,
            'order_date'      => '2026-02-09',
        ]);

        $this->purchaseOrder->lineItems()->create([
            'line_no'      => 1,
            'product_id'   => $this->productA->id,
            'description'  => 'Magnetic Tracklight 6w 3000k',
            'qty'          => 158,
            'unit'         => 'pcs',
            'unit_price'   => 2100.00,
            'line_total'   => 331800.00,
        ]);

        $this->purchaseOrder->lineItems()->create([
            'line_no'      => 2,
            'product_id'   => $this->productB->id,
            'description'  => 'Magnetic Trackbar 2 meters',
            'qty'          => 14,
            'unit'         => 'pcs',
            'unit_price'   => 4800.00,
            'line_total'   => 67200.00,
        ]);
    }

    public function test_purchase_order_can_have_multiple_delivery_receipts(): void
    {
        // Batch 1: DR #00423
        $dr1 = DeliveryReceipt::create([
            'dr_number'             => '00423',
            'purchase_order_id'     => $this->purchaseOrder->id,
            'customer_name'         => 'MGS Construction, Inc.',
            'customer_tin'          => '005-129-052-00000',
            'delivery_address'      => '2F Starmall Annex Alabang-Zapote Rd., Las Pinas City',
            'terms'                 => '30 Days',
            'project_name'          => 'Palanza Tower',
            'sales_invoice_numbers' => '0402, 0403',
            'rs_number'             => 'RS-001',
            'delivery_type'         => 'partial',
            'delivered_by'          => 'Huenics Logistics',
            'prepared_by'           => 'Warehouse Team',
            'approved_by'           => 'Operations Manager',
            'received_by'           => 'R. SANDOVAL',
            'delivery_date'         => '2026-02-12',
            'received_date'         => '2026-02-12',
            'status'                => DeliveryReceiptStatus::Delivered->value,
        ]);

        $dr1->items()->create([
            'product_id'    => $this->productA->id,
            'description'   => 'Magnetic Tracklight 6w 3000k',
            'qty_delivered' => 158,
            'unit'          => 'pcs',
        ]);

        // Batch 2: DR #00426
        $dr2 = DeliveryReceipt::create([
            'dr_number'             => '00426',
            'purchase_order_id'     => $this->purchaseOrder->id,
            'customer_name'         => 'MGS Construction, Inc.',
            'customer_tin'          => '005-129-052-00000',
            'delivery_address'      => '2F Starmall Annex Alabang-Zapote Rd., Las Pinas City',
            'terms'                 => '30 Days',
            'project_name'          => 'Palanza Tower',
            'sales_invoice_numbers' => '0403',
            'rs_number'             => 'RS-002',
            'delivery_type'         => 'complete',
            'delivered_by'          => 'Huenics Logistics',
            'prepared_by'           => 'Warehouse Team',
            'approved_by'           => 'Operations Manager',
            'received_by'           => 'E.R. SACDALAN',
            'delivery_date'         => '2026-02-15',
            'received_date'         => '2026-02-15',
            'status'                => DeliveryReceiptStatus::Delivered->value,
        ]);

        $dr2->items()->create([
            'product_id'    => $this->productB->id,
            'description'   => 'Magnetic Trackbar 2 meters',
            'qty_delivered' => 14,
            'unit'          => 'pcs',
        ]);

        $po = $this->purchaseOrder->fresh();

        $this->assertCount(2, $po->deliveryReceipts);
        $this->assertTrue($po->hasDeliveryReceipt());
        $this->assertStringContainsString('00423', $po->delivery_receipt_numbers_string);
        $this->assertStringContainsString('00426', $po->delivery_receipt_numbers_string);
    }

    public function test_purchase_order_can_have_multiple_sales_invoices(): void
    {
        // Invoice 1: SI #0402
        $si1 = SalesInvoice::create([
            'si_number'                  => '0402',
            'purchase_order_id'          => $this->purchaseOrder->id,
            'customer_name'              => 'MGS Construction, Inc.',
            'customer_tin'               => '005-129-052-00000',
            'business_style'             => 'MGS CONSTRUCTION, INC.',
            'billing_address'            => '2F Starmall Annex Alabang-Zapote Rd., Las Pinas City',
            'delivery_receipt_numbers'   => '00423',
            'invoice_date'               => '2026-02-09',
            'subtotal'                   => 42383.84,
            'discount_amount'            => 0,
            'net_of_vat'                 => 37842.71,
            'vatable_sales'              => 37842.71,
            'vat_amount'                 => 4541.13,
            'total_amount'               => 42383.84,
            'payment_status'             => SalesInvoiceStatus::Paid->value,
            'cashier_representative'     => 'Anika',
            'cashier_signature_date'     => '2026-03-05',
        ]);

        // Invoice 2: SI #0403
        $si2 = SalesInvoice::create([
            'si_number'                  => '0403',
            'purchase_order_id'          => $this->purchaseOrder->id,
            'customer_name'              => 'MGS Construction, Inc.',
            'customer_tin'               => '005-129-052-00000',
            'business_style'             => 'MGS CONSTRUCTION, INC.',
            'billing_address'            => '2F Starmall Annex Alabang-Zapote Rd., Las Pinas City',
            'delivery_receipt_numbers'   => '00426, 00423',
            'collection_receipt_numbers' => 'RC# 1410708, 1410709, 1410710',
            'invoice_date'               => '2026-02-09',
            'subtotal'                   => 950000.00,
            'discount_amount'            => 0,
            'net_of_vat'                 => 848214.29,
            'vatable_sales'              => 848214.29,
            'vat_amount'                 => 101785.71,
            'total_amount'               => 950000.00,
            'payment_status'             => SalesInvoiceStatus::Paid->value,
            'cashier_representative'     => 'Anika',
            'cashier_signature_date'     => '2026-03-01',
        ]);

        $po = $this->purchaseOrder->fresh();

        $this->assertCount(2, $po->salesInvoices);
        $this->assertTrue($po->hasSalesInvoice());
        $this->assertStringContainsString('0402', $po->sales_invoice_numbers_string);
        $this->assertStringContainsString('0403', $po->sales_invoice_numbers_string);
        $this->assertEquals(992383.84, $po->total_invoiced_amount);
    }

    public function test_delivery_receipt_and_sales_invoice_pdf_exports_work(): void
    {
        $this->actingAs($this->admin);

        $dr = DeliveryReceipt::create([
            'dr_number'             => '00451',
            'purchase_order_id'     => $this->purchaseOrder->id,
            'customer_name'         => 'MGS Construction, Inc.',
            'customer_tin'          => '005-129-052-00000',
            'delivery_address'      => '2F Starmall Annex Alabang-Zapote Rd., Las Pinas City',
            'delivery_date'         => '2026-04-22',
            'sales_invoice_numbers' => '0424',
            'received_by'           => 'E.R. SACDALAN',
            'received_date'         => '2026-04-22',
            'status'                => DeliveryReceiptStatus::Delivered->value,
        ]);

        $dr->items()->create([
            'product_id'    => $this->productA->id,
            'description'   => 'LED DOWNLIGHT COB CITIZEN - 3500K - WHITE CASING',
            'qty_delivered' => 317,
            'unit'          => 'SETS',
        ]);

        $si = SalesInvoice::create([
            'si_number'                => '0424',
            'purchase_order_id'        => $this->purchaseOrder->id,
            'customer_name'            => 'MGS Construction, Inc.',
            'customer_tin'             => '005-129-052-00000',
            'business_style'           => 'MGS CONSTRUCTION, INC.',
            'billing_address'          => '2F Starmall Annex Alabang-Zapote Rd., Las Pinas City',
            'delivery_receipt_numbers' => '00430, 00451',
            'invoice_date'             => '2026-03-23',
            'subtotal'                 => 1002565.00,
            'discount_amount'          => 2565.00,
            'net_of_vat'               => 892857.14,
            'vatable_sales'            => 892857.14,
            'vat_amount'               => 107142.86,
            'total_amount'             => 1000000.00,
            'payment_status'           => SalesInvoiceStatus::Paid->value,
            'cashier_representative'   => 'Authorized Representative',
            'cashier_signature_date'   => '2026-03-23',
        ]);

        $si->items()->create([
            'product_id'  => $this->productA->id,
            'description' => 'LED DOWNLIGHT COB CITIZEN 3500K WARM 7W',
            'qty'         => 421,
            'unit'        => 'PCS',
            'unit_price'  => 1755.00,
            'line_total'  => 738855.00,
        ]);

        // Test DR PDF Export
        $drResponse = $this->get(route('delivery-receipts.export-pdf', $dr));
        $drResponse->assertStatus(200);
        $this->assertEquals('application/pdf', $drResponse->headers->get('Content-Type'));

        // Test SI PDF Export
        $siResponse = $this->get(route('sales-invoices.export-pdf', $si));
        $siResponse->assertStatus(200);
        $this->assertEquals('application/pdf', $siResponse->headers->get('Content-Type'));
    }
}
