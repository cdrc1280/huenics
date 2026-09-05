<?php

namespace Tests\Feature;

use App\Models\InventoryItem;
use App\Models\Product;
use App\Models\User;
use App\Services\ProductImportExportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Writer\XLSX\Writer as XlsxWriter;
use Tests\TestCase;

class ProductImportExportTest extends TestCase
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

    public function test_product_model_supports_specs_attributes(): void
    {
        $product = Product::create([
            'product_code'      => 'HISI-LS-9.6W',
            'canonical_name'    => 'SMD LED Strip Light 9.6W/M Indoor',
            'description'       => 'SMD LED STRIPS SIZE 2835,120PCS LED/M,IP20 INDOOR',
            'category'          => 'SMD LED STRIP LIGHT INDOOR',
            'wattage'           => '9.6W/M',
            'voltage'           => 'DC12V',
            'color_temperature' => '3000K/6000K',
            'unit_default'      => 'roll',
            'selling_price'     => 850.00,
            'default_price'     => 850.00,
            'base_cost_price'   => 595.00,
            'is_huenics_owned'  => true,
            'is_active'         => true,
        ]);

        $this->assertDatabaseHas('products', [
            'product_code'      => 'HISI-LS-9.6W',
            'wattage'           => '9.6W/M',
            'voltage'           => 'DC12V',
            'color_temperature' => '3000K/6000K',
            'unit_default'      => 'roll',
            'selling_price'     => 850.00,
        ]);

        $this->assertEquals('9.6W/M', $product->wattage);
        $this->assertEquals('DC12V', $product->voltage);
        $this->assertEquals('3000K/6000K', $product->color_temperature);
    }

    public function test_generate_sample_csv_template_returns_pricelist_structure(): void
    {
        $service = app(ProductImportExportService::class);
        $csvContent = $service->generateSampleCsvTemplate();

        $this->assertStringContainsString('CATEGORY', $csvContent);
        $this->assertStringContainsString('PICTURE', $csvContent);
        $this->assertStringContainsString('CODE', $csvContent);
        $this->assertStringContainsString('WATTAGE', $csvContent);
        $this->assertStringContainsString('DESCRIPTION', $csvContent);
        $this->assertStringContainsString('VOLTAGE', $csvContent);
        $this->assertStringContainsString('COLOR', $csvContent);
        $this->assertStringContainsString('PRICE', $csvContent);
        $this->assertStringContainsString('UNIT', $csvContent);

        $this->assertStringContainsString('HISI-LS-9.6W', $csvContent);
        $this->assertStringContainsString('HISI-LS-COB-12W', $csvContent);
        $this->assertStringContainsString('HISI-PLUG', $csvContent);
    }

    public function test_import_csv_parses_reference_pricelist_accurately(): void
    {
        $csvData = implode("\n", [
            'PRICELIST 2024,,,,,,',
            'PICTURE,CODE,WATTAGE,DISCRIPTION,VOLTAGE DC12V,COLOR,PRICE',
            'SMD LED STRIP LIGHT INDOOR,,,,,,',
            ',HISI-LS-9.6W,9.6W/M ,"SMD LED STRIPS SIZE 2835,120PCS LED/M,IP20 INDOOR",DC12V,3000K/6000K, 850.00/ROLL ',
            ',HISI-LS-14.4W,14.4W/M,"SMD LED STRIPS SIZE 5050,60PCS LED/M,IP20 INDOOR",DC12V,3000K/6000K, 950.00/ROLL ',
            'COB LED STRIP LIGHT ,,,,,,',
            ',HISI-LS-COB-12W,12W/M," LED COB STRIPS SIZE 5050,60PCS LED/M,IP20 INDOOR",DC12V,3000K/6000K/4000K," 1,950.00/ROLL "',
            'SMD LED STRIP LIGHT ACCESSORIES,,,,,,',
            ',HISI-PLUG,XXX,POWER CABLE FOR LED STRIPLIGHT,XXX,XXX, 250.00/SET ',
            ',HISI-CONNECTOR,XXX,CONNECTOR,XXX,XXX, 50.00/PC ',
        ]);

        $tempFile = tempnam(sys_get_temp_dir(), 'csv_test_');
        file_put_contents($tempFile, $csvData);

        try {
            $service = app(ProductImportExportService::class);
            $result = $service->importCsv($tempFile, updateExisting: true);

            $this->assertEquals(5, $result['imported']);
            $this->assertEquals(0, $result['updated']);
            $this->assertEmpty($result['errors']);

            // Verify HISI-LS-9.6W
            $strip9w = Product::where('product_code', 'HISI-LS-9.6W')->first();
            $this->assertNotNull($strip9w);
            $this->assertEquals('SMD LED STRIP LIGHT INDOOR', $strip9w->category);
            $this->assertEquals('9.6W/M', $strip9w->wattage);
            $this->assertEquals('DC12V', $strip9w->voltage);
            $this->assertEquals('3000K/6000K', $strip9w->color_temperature);
            $this->assertEquals(850.00, (float) $strip9w->selling_price);
            $this->assertEquals('roll', $strip9w->unit_default);

            // Verify HISI-LS-COB-12W with comma formatted price
            $cob12w = Product::where('product_code', 'HISI-LS-COB-12W')->first();
            $this->assertNotNull($cob12w);
            $this->assertEquals('COB LED STRIP LIGHT', $cob12w->category);
            $this->assertEquals('12W/M', $cob12w->wattage);
            $this->assertEquals('3000K/6000K/4000K', $cob12w->color_temperature);
            $this->assertEquals(1950.00, (float) $cob12w->selling_price);
            $this->assertEquals('roll', $cob12w->unit_default);

            // Verify HISI-PLUG with XXX placeholders cleaned
            $plug = Product::where('product_code', 'HISI-PLUG')->first();
            $this->assertNotNull($plug);
            $this->assertEquals('SMD LED STRIP LIGHT ACCESSORIES', $plug->category);
            $this->assertNull($plug->wattage);
            $this->assertNull($plug->voltage);
            $this->assertNull($plug->color_temperature);
            $this->assertEquals(250.00, (float) $plug->selling_price);
            $this->assertEquals('set', $plug->unit_default);

            // Verify InventoryItems were created
            $this->assertDatabaseHas('inventory_items', [
                'product_id' => $strip9w->id,
            ]);
            $this->assertDatabaseHas('inventory_items', [
                'product_id' => $plug->id,
            ]);
        } finally {
            if (file_exists($tempFile)) {
                @unlink($tempFile);
            }
        }
    }

    public function test_import_csv_updates_existing_products_when_flag_is_true(): void
    {
        $existing = Product::create([
            'product_code'      => 'HISI-LS-2835',
            'canonical_name'    => 'Old Name',
            'description'       => 'Old Description',
            'category'          => 'General',
            'wattage'           => '3W',
            'voltage'           => '12V',
            'color_temperature' => '3000K',
            'unit_default'      => 'pcs',
            'selling_price'     => 500.00,
            'default_price'     => 500.00,
            'base_cost_price'   => 350.00,
            'is_huenics_owned'  => true,
            'is_active'         => true,
        ]);

        $csvData = implode("\n", [
            'CODE,WATTAGE,DESCRIPTION,VOLTAGE,COLOR,PRICE,UNIT,CATEGORY',
            'HISI-LS-2835,4.8W/M,SMD LED STRIPS SIZE 2835 60PCS LED/M,DC12V,3000K/6000K,700.00,ROLL,SMD LED STRIP LIGHT OUTDOOR',
        ]);

        $tempFile = tempnam(sys_get_temp_dir(), 'csv_update_');
        file_put_contents($tempFile, $csvData);

        try {
            $service = app(ProductImportExportService::class);
            $result = $service->importCsv($tempFile, updateExisting: true);

            $this->assertEquals(0, $result['imported']);
            $this->assertEquals(1, $result['updated']);

            $existing->refresh();
            $this->assertEquals('4.8W/M', $existing->wattage);
            $this->assertEquals('DC12V', $existing->voltage);
            $this->assertEquals('3000K/6000K', $existing->color_temperature);
            $this->assertEquals('roll', $existing->unit_default);
            $this->assertEquals(700.00, (float) $existing->selling_price);
            $this->assertEquals('SMD LED STRIP LIGHT OUTDOOR', $existing->category);
        } finally {
            if (file_exists($tempFile)) {
                @unlink($tempFile);
            }
        }
    }

    public function test_export_csv_produces_valid_csv_matching_reference(): void
    {
        Product::create([
            'product_code'      => 'HISI-LS-8W',
            'canonical_name'    => 'SMD LED Strip Light 8W/M 220V Indoor',
            'description'       => 'SMD LED STRIPS SIZE 5050, 60PCS LED/M, 220V INDOOR',
            'category'          => 'SMD LED STRIP LIGHT INDOOR 220V',
            'wattage'           => '8W/M',
            'voltage'           => '220V',
            'color_temperature' => '3000K/6000K',
            'unit_default'      => 'm',
            'selling_price'     => 150.00,
            'default_price'     => 150.00,
            'base_cost_price'   => 100.00,
            'is_huenics_owned'  => true,
            'is_active'         => true,
        ]);

        $service = app(ProductImportExportService::class);
        $csv = $service->exportCsv();

        $this->assertStringContainsString('CATEGORY,PICTURE,CODE,CANONICAL_NAME,WATTAGE,DESCRIPTION,VOLTAGE,COLOR,PRICE,UNIT,STOCK_ON_HAND', $csv);
        $this->assertStringContainsString('HISI-LS-8W', $csv);
        $this->assertStringContainsString('8W/M', $csv);
        $this->assertStringContainsString('220V', $csv);
        $this->assertStringContainsString('150.00', $csv);
    }

    public function test_filament_routes_export_and_template_download(): void
    {
        $responseTemplate = $this->actingAs($this->admin)->get(route('products.download-template'));
        $responseTemplate->assertStatus(200);
        $responseTemplate->assertHeader('Content-Disposition', 'attachment; filename="huenics-product-import-template.csv"');

        $responseExport = $this->actingAs($this->admin)->get(route('products.export-csv'));
        $responseExport->assertStatus(200);
        $this->assertStringContainsString('attachment; filename="huenics-products-catalog-', $responseExport->headers->get('Content-Disposition'));
    }

    public function test_generate_sample_excel_template_returns_valid_xlsx_binary(): void
    {
        $service = app(ProductImportExportService::class);
        $excelContent = $service->generateSampleExcelTemplate();

        $this->assertNotEmpty($excelContent);
        // ZIP / XLSX magic bytes: PK\x03\x04
        $this->assertStringStartsWith("PK\x03\x04", $excelContent);
    }

    public function test_export_excel_produces_valid_xlsx_with_products(): void
    {
        Product::create([
            'product_code'      => 'HISI-LS-XLSX-EXP',
            'canonical_name'    => 'SMD LED Strip Light Excel Export',
            'description'       => 'High-efficiency 24V strip for Excel export testing',
            'category'          => 'SMD LED STRIP LIGHT INDOOR',
            'wattage'           => '14.4W/M',
            'voltage'           => 'DC24V',
            'color_temperature' => '4000K',
            'unit_default'      => 'roll',
            'selling_price'     => 1150.00,
            'default_price'     => 1150.00,
            'base_cost_price'   => 800.00,
            'is_huenics_owned'  => true,
            'is_active'         => true,
        ]);

        $service = app(ProductImportExportService::class);
        $excelContent = $service->exportExcel();

        $this->assertNotEmpty($excelContent);
        $this->assertStringStartsWith("PK\x03\x04", $excelContent);

        // Verify readable via OpenSpout
        $tempFile = tempnam(sys_get_temp_dir(), 'exp_test_') . '.xlsx';
        file_put_contents($tempFile, $excelContent);

        try {
            $reader = new \OpenSpout\Reader\XLSX\Reader();
            $reader->open($tempFile);
            $rows = [];
            foreach ($reader->getSheetIterator() as $sheet) {
                foreach ($sheet->getRowIterator() as $row) {
                    $rows[] = $row->toArray();
                }
            }
            $reader->close();

            $this->assertGreaterThanOrEqual(2, count($rows));
            $this->assertEquals('CATEGORY', $rows[0][0]);
            $this->assertEquals('PRICE', $rows[0][8]);

            $found = false;
            foreach ($rows as $row) {
                if (isset($row[2]) && $row[2] === 'HISI-LS-XLSX-EXP') {
                    $found = true;
                    $this->assertEquals('14.4W/M', $row[4]);
                    $this->assertEquals('1150.00', $row[8]);
                    $this->assertEquals('ROLL', $row[9]);
                    break;
                }
            }
            $this->assertTrue($found, 'Exported Excel did not contain the test product');
        } finally {
            if (file_exists($tempFile)) {
                @unlink($tempFile);
            }
        }
    }

    public function test_import_file_parses_excel_xlsx_accurately(): void
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'xlsx_import_') . '.xlsx';
        $writer = new XlsxWriter();
        $writer->openToFile($tempFile);

        $writer->addRow(Row::fromValues([
            'PICTURE', 'CODE', 'WATTAGE', 'DISCRIPTION', 'VOLTAGE DC12V', 'COLOR', 'PRICE',
        ]));
        $writer->addRow(Row::fromValues([
            'SMD LED STRIP LIGHT INDOOR', '', '', '', '', '', '',
        ]));
        $writer->addRow(Row::fromValues([
            '', 'HISI-LS-XLSX-1', '9.6W/M', 'SMD LED STRIPS SIZE 2835, 120PCS/M', 'DC12V', '3000K/6000K', '850.00/ROLL',
        ]));
        $writer->addRow(Row::fromValues([
            '', 'HISI-LS-XLSX-2', '14.4W/M', 'SMD LED STRIPS SIZE 5050, 60PCS/M', 'DC12V', '4000K', ' 1,950.00/ROLL ',
        ]));
        $writer->close();

        try {
            $service = app(ProductImportExportService::class);
            $result = $service->importFile($tempFile, updateExisting: true);

            $this->assertEquals(2, $result['imported']);
            $this->assertEquals(0, $result['updated']);
            $this->assertEmpty($result['errors']);

            $product1 = Product::where('product_code', 'HISI-LS-XLSX-1')->first();
            $this->assertNotNull($product1);
            $this->assertEquals('SMD LED STRIP LIGHT INDOOR', $product1->category);
            $this->assertEquals('9.6W/M', $product1->wattage);
            $this->assertEquals('DC12V', $product1->voltage);
            $this->assertEquals(850.00, (float) $product1->selling_price);
            $this->assertEquals('roll', $product1->unit_default);

            $product2 = Product::where('product_code', 'HISI-LS-XLSX-2')->first();
            $this->assertNotNull($product2);
            $this->assertEquals(1950.00, (float) $product2->selling_price);
            $this->assertEquals('roll', $product2->unit_default);

            $this->assertDatabaseHas('inventory_items', [
                'product_id' => $product1->id,
            ]);
            $this->assertDatabaseHas('inventory_items', [
                'product_id' => $product2->id,
            ]);
        } finally {
            if (file_exists($tempFile)) {
                @unlink($tempFile);
            }
        }
    }

    public function test_import_file_updates_existing_products_from_excel(): void
    {
        $existing = Product::create([
            'product_code'      => 'HISI-LS-XLSX-UP',
            'canonical_name'    => 'Initial Name',
            'description'       => 'Initial Description',
            'category'          => 'General',
            'wattage'           => '5W',
            'voltage'           => '12V',
            'color_temperature' => '3000K',
            'unit_default'      => 'pcs',
            'selling_price'     => 400.00,
            'default_price'     => 400.00,
            'base_cost_price'   => 280.00,
            'is_huenics_owned'  => true,
            'is_active'         => true,
        ]);

        $tempFile = tempnam(sys_get_temp_dir(), 'xlsx_update_') . '.xlsx';
        $writer = new XlsxWriter();
        $writer->openToFile($tempFile);

        $writer->addRow(Row::fromValues([
            'CODE', 'WATTAGE', 'DESCRIPTION', 'VOLTAGE', 'COLOR', 'PRICE', 'UNIT', 'CATEGORY',
        ]));
        $writer->addRow(Row::fromValues([
            'HISI-LS-XLSX-UP', '10W/M', 'Updated Excel Spec Description', 'DC24V', '6000K', '750.00', 'ROLL', 'COB LED STRIP LIGHT',
        ]));
        $writer->close();

        try {
            $service = app(ProductImportExportService::class);
            $result = $service->importFile($tempFile, updateExisting: true);

            $this->assertEquals(0, $result['imported']);
            $this->assertEquals(1, $result['updated']);

            $existing->refresh();
            $this->assertEquals('10W/M', $existing->wattage);
            $this->assertEquals('DC24V', $existing->voltage);
            $this->assertEquals('6000K', $existing->color_temperature);
            $this->assertEquals('roll', $existing->unit_default);
            $this->assertEquals(750.00, (float) $existing->selling_price);
            $this->assertEquals('COB LED STRIP LIGHT', $existing->category);
        } finally {
            if (file_exists($tempFile)) {
                @unlink($tempFile);
            }
        }
    }

    public function test_filament_routes_excel_export_and_template_download(): void
    {
        $responseTemplate = $this->actingAs($this->admin)->get(route('products.download-template-excel'));
        $responseTemplate->assertStatus(200);
        $responseTemplate->assertHeader('Content-Disposition', 'attachment; filename="huenics-product-import-template.xlsx"');

        $responseExport = $this->actingAs($this->admin)->get(route('products.export-excel'));
        $responseExport->assertStatus(200);
        $this->assertStringContainsString('attachment; filename="huenics-products-catalog-', $responseExport->headers->get('Content-Disposition'));
        $this->assertStringContainsString('.xlsx', $responseExport->headers->get('Content-Disposition'));
    }
}
