<?php

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerPortalTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class);

        Product::create([
            'sku' => 'TEST-PIPE-01',
            'canonical_name' => '1-1/4" PVC Pipe Sch 40',
            'category' => 'Pipes & Fittings',
            'unit_default' => 'pcs',
            'default_price' => 1880.56,
            'selling_price' => 1880.56,
            'is_active' => true,
        ]);

        Product::create([
            'sku' => 'TEST-STEEL-01',
            'canonical_name' => '1/2" Deformed Bar Grade 40',
            'category' => 'Structural Steel',
            'unit_default' => 'pcs',
            'default_price' => 320.00,
            'selling_price' => 320.00,
            'is_active' => true,
        ]);
    }

    public function test_customer_can_visit_home_page(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('HUENICS');
        $response->assertSee('Product Catalog');
    }

    public function test_customer_can_visit_about_page(): void
    {
        $response = $this->get('/about');

        $response->assertStatus(200);
        $response->assertSee('Huenics Industrial Sales Inc.');
        $response->assertSee('Colors');
        $response->assertSee('Technology');
        $response->assertSee('LIGHTING CLINIC');
    }

    public function test_customer_side_has_no_login_features(): void
    {
        $pages = ['/', '/about', '/products', '/quotation/builder'];

        foreach ($pages as $url) {
            $response = $this->get($url);
            $response->assertStatus(200);
            $response->assertDontSee('Employee Login');
            $response->assertDontSee('Staff Portal');
            $response->assertDontSee('/admin/login');
        }
    }

    public function test_customer_can_browse_product_catalog_and_filter(): void
    {
        $response = $this->get('/products');

        $response->assertStatus(200);
        $response->assertSee('1-1/4" PVC Pipe Sch 40');
        $response->assertSee('1/2" Deformed Bar Grade 40');

        // Filter by category
        $filteredResponse = $this->get('/products?category=Pipes+%26+Fittings');
        $filteredResponse->assertStatus(200);
        $filteredResponse->assertSee('1-1/4" PVC Pipe Sch 40');

        // Search by keyword
        $searchResponse = $this->get('/products?search=Deformed');
        $searchResponse->assertStatus(200);
        $searchResponse->assertSee('1/2" Deformed Bar Grade 40');
    }

    public function test_customer_can_open_quotation_builder(): void
    {
        $response = $this->get('/quotation/builder');

        $response->assertStatus(200);
        $response->assertSee('Commercial Quotation');
        $response->assertSee('Selected Line Items');
    }

    public function test_customer_can_generate_unofficial_quotation_estimate(): void
    {
        $payload = [
            'customer_name' => 'Engr. Roberto Santos',
            'customer_company' => 'MGS Construction Corp.',
            'email' => 'roberto@mgs.ph',
            'phone_no' => '0917-123-4567',
            'project_name' => 'Palanza Tower',
            'project_location' => 'Quezon City',
            'notes' => 'Urgent procurement for plumbing riser.',
            'items' => [
                [
                    'item_code' => 'TEST-PIPE-01',
                    'description' => '1-1/4" PVC Pipe Sch 40',
                    'quantity' => 10,
                    'unit' => 'pcs',
                    'unit_price' => 1880.56,
                ],
                [
                    'item_code' => 'TEST-STEEL-01',
                    'description' => '1/2" Deformed Bar Grade 40',
                    'quantity' => 20,
                    'unit' => 'pcs',
                    'unit_price' => 320.00,
                ],
            ],
            'action' => 'view',
        ];

        $response = $this->post('/quotation/generate-unofficial', $payload);

        $response->assertStatus(200);
        $response->assertSee('Unofficial Quotation Generated Successfully!');
        $response->assertSee('Engr. Roberto Santos');
        $response->assertSee('Palanza Tower');
        $response->assertSee('1-1/4" PVC Pipe Sch 40');
    }

    public function test_customer_can_download_unofficial_quotation_pdf(): void
    {
        $payload = [
            'customer_name' => 'Engr. Roberto Santos',
            'customer_company' => 'MGS Construction Corp.',
            'items' => [
                [
                    'item_code' => 'TEST-PIPE-01',
                    'description' => '1-1/4" PVC Pipe Sch 40',
                    'quantity' => 5,
                    'unit' => 'pcs',
                    'unit_price' => 1880.56,
                ]
            ],
            'action' => 'download_pdf',
        ];

        $response = $this->post('/quotation/generate-unofficial', $payload);

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
    }

    public function test_company_profile_products_are_stored_and_queryable(): void
    {
        $this->seed(\Database\Seeders\HuenicsCompanyProfileProductSeeder::class);

        $response = $this->get('/products?category=Indoor+Downlights');
        $response->assertStatus(200);
        $response->assertSee('Citizen Japan');
        $response->assertSee('HISI-JF-2240-7W');

        $smartResponse = $this->get('/products?category=Smart+Home+%26+Automation');
        $smartResponse->assertStatus(200);
        $smartResponse->assertSee('HISI-SMART-GW');
    }
}
