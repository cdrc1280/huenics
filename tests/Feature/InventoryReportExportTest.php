<?php

namespace Tests\Feature;

use App\Models\InventoryItem;
use App\Models\InventoryTransaction;
use App\Models\Product;
use App\Models\User;
use App\Services\InventoryReportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryReportExportTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role'  => User::ROLE_ADMIN,
            'name'  => 'Admin User',
            'email' => 'admin@huenics.com',
        ]);
    }

    public function test_inventory_item_supports_report_fields(): void
    {
        $product = Product::create([
            'product_code'     => 'CLU028-1204C4-303M2KI',
            'sku'              => 'CLU028-1204C4-303M2KI',
            'canonical_name'   => 'CITIZEN CLU028-3000K, CRI80, STANDARD, VER. 5',
            'unit_default'     => 'pcs',
            'is_huenics_owned' => true,
        ]);

        $item = InventoryItem::create([
            'product_id'       => $product->id,
            'quantity_on_hand' => 22,
            'reorder_point'    => 10,
            'unit'             => 'pcs',
            'location'         => 'Mam CBS ROOM INSIDE CABINET',
            'supplier_name'    => 'SUPREME COMPONENTS INTL PTE.LTD',
            'po_number'        => '2022-3263',
            'customer_name'    => 'FOOTACTION INTERNATIONAL MANUFACTURING CORP.',
            'project_name'     => 'FAIRVIEW MERRELL STORE',
            'date_released'    => '2024-01-24',
            'inbound_date'     => '2024-12-01',
            'remarks'          => 'COMPLETE DELIVERY',
        ]);

        $this->assertDatabaseHas('inventory_items', [
            'id'            => $item->id,
            'location'      => 'Mam CBS ROOM INSIDE CABINET',
            'supplier_name' => 'SUPREME COMPONENTS INTL PTE.LTD',
            'po_number'     => '2022-3263',
            'customer_name' => 'FOOTACTION INTERNATIONAL MANUFACTURING CORP.',
            'project_name'  => 'FAIRVIEW MERRELL STORE',
            'remarks'       => 'COMPLETE DELIVERY',
        ]);

        $this->assertEquals('Mam CBS ROOM INSIDE CABINET', $item->location);
        $this->assertEquals('SUPREME COMPONENTS INTL PTE.LTD', $item->supplier_name);
        $this->assertEquals('2024-01-24', $item->date_released->format('Y-m-d'));
        $this->assertEquals('2024-12-01', $item->inbound_date->format('Y-m-d'));
    }

    public function test_generate_sample_inventory_csv_returns_15_columns(): void
    {
        $service = app(InventoryReportService::class);
        $csv = $service->generateSampleInventoryCsv();

        $expectedHeaders = [
            'Date',
            'P.O. Nos.',
            'Suppliers Name',
            'S.K.U.',
            'Item Code',
            'Pictures',
            'Particulars',
            'Transit In',
            'Transit Out',
            'Balance',
            'Location',
            'Customer Name',
            'Project Name',
            'Date Released',
            'Remarks',
        ];

        foreach ($expectedHeaders as $header) {
            $this->assertStringContainsString($header, $csv);
        }

        $this->assertStringContainsString('SUPREME COMPONENTS INTL PTE.LTD', $csv);
        $this->assertStringContainsString('Mam CBS ROOM INSIDE CABINET', $csv);
        $this->assertStringContainsString('CLU028-1204C4-303M2KI', $csv);
    }

    public function test_export_inventory_report_produces_valid_csv_matching_reference(): void
    {
        $product = Product::create([
            'product_code'     => 'CLU028-1203C4-403H5M3-F1',
            'sku'              => 'CLU028-1203C4-403H5M3-F1',
            'canonical_name'   => 'CITIZEN CLU028-1203C4-403H5M3-F1',
            'unit_default'     => 'pcs',
            'is_huenics_owned' => true,
        ]);

        InventoryItem::create([
            'product_id'       => $product->id,
            'quantity_on_hand' => 3,
            'unit'             => 'pcs',
            'location'         => 'Mam CBS ROOM INSIDE CABINET',
            'supplier_name'    => 'SUPREME COMPONENTS INTL PTE.LTD',
            'inbound_date'     => '2024-12-01',
        ]);

        $service = app(InventoryReportService::class);
        $csv = $service->exportInventoryReport();

        $this->assertStringContainsString('Date', $csv);
        $this->assertStringContainsString('P.O. Nos.', $csv);
        $this->assertStringContainsString('Suppliers Name', $csv);
        $this->assertStringContainsString('S.K.U.', $csv);
        $this->assertStringContainsString('Item Code', $csv);
        $this->assertStringContainsString('Particulars', $csv);
        $this->assertStringContainsString('Balance', $csv);
        $this->assertStringContainsString('Location', $csv);
        $this->assertStringContainsString('CLU028-1203C4-403H5M3-F1', $csv);
        $this->assertStringContainsString('Mam CBS ROOM INSIDE CABINET', $csv);
        $this->assertStringContainsString('SUPREME COMPONENTS INTL PTE.LTD', $csv);
        $this->assertStringContainsString(',3,', $csv);
    }

    public function test_import_inventory_report_parses_reference_sheet_accurately(): void
    {
        $csvData = implode("\n", [
            'Date,P.O. Nos.,Suppliers Name,S.K.U.,Item Code,Pictures,Particulars,Transit In,Transit Out,Balance,Location,Customer Name,Project Name,Date Released, Remarks',
            '12/1/2024,,SUPREME COMPONENTS INTL PTE.LTD,CLU028-1204C4-303M2KI,CLU028-1204C4-303M2KI,,"CITIZEN CLU028-3000K, CRI80, STANDARD, VER. 5",22,,22,Mam CBS ROOM INSIDE CABINET,,,,',
            '01/18/2024,241000010-M,SUPREME COMPONENTS INTL PTE.LTD,CLU028-1204C4-353H5M3-F1,CLU028-1204C4-353H5M3-F1,,"CITIZEN CLU028-3500K, CRI90, STANDARD, VER. 6",74,2,72,Mam CBS ROOM INSIDE CABINET,FOOTACTION INTERNATIONAL MANUFACTURING CORP.,FAIRVIEW MERRELL STORE,01/24/2024,COMPLETE DELIVERY',
        ]);

        $tempFile = tempnam(sys_get_temp_dir(), 'inv_test_');
        file_put_contents($tempFile, $csvData);

        try {
            $service = app(InventoryReportService::class);
            $result = $service->importInventoryReport($tempFile, updateExisting: true);

            $this->assertEquals(2, $result['imported']);
            $this->assertEquals(0, $result['updated']);
            $this->assertEmpty($result['errors']);

            // Verify item 1
            $product1 = Product::where('product_code', 'CLU028-1204C4-303M2KI')->first();
            $this->assertNotNull($product1);
            $item1 = InventoryItem::where('product_id', $product1->id)->first();
            $this->assertNotNull($item1);
            $this->assertEquals(22, (float) $item1->quantity_on_hand);
            $this->assertEquals('Mam CBS ROOM INSIDE CABINET', $item1->location);
            $this->assertEquals('SUPREME COMPONENTS INTL PTE.LTD', $item1->supplier_name);

            // Verify item 2 with release details
            $product2 = Product::where('product_code', 'CLU028-1204C4-353H5M3-F1')->first();
            $this->assertNotNull($product2);
            $item2 = InventoryItem::where('product_id', $product2->id)->first();
            $this->assertNotNull($item2);
            $this->assertEquals(72, (float) $item2->quantity_on_hand);
            $this->assertEquals('Mam CBS ROOM INSIDE CABINET', $item2->location);
            $this->assertEquals('241000010-M', $item2->po_number);
            $this->assertEquals('FOOTACTION INTERNATIONAL MANUFACTURING CORP.', $item2->customer_name);
            $this->assertEquals('FAIRVIEW MERRELL STORE', $item2->project_name);
            $this->assertEquals('2024-01-24', $item2->date_released->format('Y-m-d'));
            $this->assertEquals('COMPLETE DELIVERY', $item2->remarks);

            // Verify transactions were recorded
            $this->assertDatabaseHas('inventory_transactions', [
                'inventory_item_id' => $item1->id,
                'location'          => 'Mam CBS ROOM INSIDE CABINET',
            ]);
            $this->assertDatabaseHas('inventory_transactions', [
                'inventory_item_id' => $item2->id,
                'po_number'         => '241000010-M',
                'customer_name'     => 'FOOTACTION INTERNATIONAL MANUFACTURING CORP.',
            ]);
        } finally {
            if (file_exists($tempFile)) {
                @unlink($tempFile);
            }
        }
    }

    public function test_filament_inventory_routes(): void
    {
        $responseTemplate = $this->actingAs($this->admin)->get(route('inventory.download-template'));
        $responseTemplate->assertStatus(200);
        $responseTemplate->assertHeader('Content-Disposition', 'attachment; filename="huenics-inventory-template.csv"');

        $responseExport = $this->actingAs($this->admin)->get(route('inventory.export-report'));
        $responseExport->assertStatus(200);
        $this->assertStringContainsString('attachment; filename="huenics-inventory-report-', $responseExport->headers->get('Content-Disposition'));
    }
}
