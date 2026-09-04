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
            'phone_no' => '0917-123-4567',
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

    public function test_all_products_listed_on_customer_side_originate_from_database(): void
    {
        \Illuminate\Support\Facades\Cache::flush();

        // 1. Create a unique new product in the database
        $uniqueSku = 'DB-TEST-' . uniqid();
        $dbProduct = Product::create([
            'sku' => $uniqueSku,
            'canonical_name' => 'Database Verified Luminaire ' . $uniqueSku,
            'category' => 'Indoor Downlights',
            'unit_default' => 'set',
            'default_price' => 4599.00,
            'selling_price' => 4599.00,
            'is_active' => true,
            'description' => 'Real-time database product verification test.',
        ]);

        // 2. Product must appear in /products catalog from DB with Quote Upon Request
        $response = $this->get('/products?search=' . $uniqueSku);
        $response->assertStatus(200);
        $response->assertSee($dbProduct->canonical_name);
        $response->assertSee('Quote Upon Request');

        // 3. Product must appear in Quotation Builder catalog modal from DB
        \Illuminate\Support\Facades\Cache::flush();
        $builderResponse = $this->get('/quotation/builder');
        $builderResponse->assertStatus(200);
        $builderResponse->assertSee($dbProduct->canonical_name);

        // 4. Update the product in the database, and verify customer side immediately reflects DB changes
        \Illuminate\Support\Facades\Cache::flush();
        $dbProduct->update([
            'canonical_name' => 'Updated DB Luminaire ' . $uniqueSku,
            'selling_price' => 5999.00,
        ]);

        $updatedResponse = $this->get('/products?search=' . $uniqueSku);
        $updatedResponse->assertStatus(200);
        $updatedResponse->assertSee('Updated DB Luminaire ' . $uniqueSku);
        $updatedResponse->assertSee('Quote Upon Request');

        // 5. Inactivate product in DB, verify it disappears from customer catalog
        \Illuminate\Support\Facades\Cache::flush();
        $dbProduct->update(['is_active' => false]);

        $inactiveResponse = $this->get('/products?search=' . $uniqueSku);
        $inactiveResponse->assertStatus(200);
        $inactiveResponse->assertDontSee('Updated DB Luminaire ' . $uniqueSku);
    }

    public function test_dark_theme_is_supported_across_customer_side(): void
    {
        // 1. Home page renders with dark theme layout tokens and toggle
        $homeResponse = $this->get('/');
        $homeResponse->assertStatus(200);
        $homeResponse->assertSee('theme-toggle', false);
        $homeResponse->assertSee('theme-icon-sun', false);
        $homeResponse->assertSee('theme-icon-moon', false);
        $homeResponse->assertSee('class="h-full dark antialiased"', false);
        $homeResponse->assertSee('huenics_theme', false);
        $homeResponse->assertSee('dark:bg-[#070b14]', false);

        // 2. About page renders with dark theme
        $aboutResponse = $this->get('/about');
        $aboutResponse->assertStatus(200);
        $aboutResponse->assertSee('dark:bg-[#070b14]', false);
        $aboutResponse->assertSee('dark:text-white', false);

        // 3. Products page renders with dark theme
        $productsResponse = $this->get('/products');
        $productsResponse->assertStatus(200);
        $productsResponse->assertSee('dark:bg-[#070b14]', false);
        $productsResponse->assertSee('dark:border-slate-800', false);

        // 4. Quotation builder renders with dark theme
        $builderResponse = $this->get('/quotation/builder');
        $builderResponse->assertStatus(200);
        $builderResponse->assertSee('dark:bg-[#070b14]', false);
        $builderResponse->assertSee('dark:bg-[#111827]', false);
        $builderResponse->assertSee('catalog-modal', false);
    }

    public function test_uniform_customer_modal_is_rendered_and_no_native_dialogs_used(): void
    {
        // 1. Uniform modal container exists on the customer layout
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('id="huenics-modal"', false);
        $response->assertSee('id="huenics-modal-card"', false);
        $response->assertSee('id="huenics-modal-title"', false);
        $response->assertSee('id="huenics-modal-confirm-btn"', false);
        $response->assertSee('id="huenics-modal-cancel-btn"', false);
        $response->assertSee('window.HuenicsModal =', false);

        // 2. Quotation builder uses HuenicsModal and never calls browser native confirm()
        $builderResponse = $this->get('/quotation/builder');
        $builderResponse->assertStatus(200);
        $builderResponse->assertSee('HuenicsModal.confirm', false);
        $builderResponse->assertDontSee("confirm('Are you sure", false);
        $builderResponse->assertDontSee('window.confirm(', false);
    }

    public function test_tablet_mode_responsiveness_prevents_navbar_and_logo_collision(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);

        // Verify desktop nav uses lg:flex so it does not overlap logo on tablet screens (768px - 1023px)
        $response->assertSee('class="hidden lg:flex items-center gap-4 xl:gap-7', false);

        // Verify hamburger toggle is active on tablet (< 1024px)
        $response->assertSee('lg:hidden', false);

        // Verify mobile/tablet drawer is active up to lg
        $response->assertSee('id="mobile-menu" class="hidden lg:hidden', false);

        // Verify logo and right-actions are marked shrink-0 so they never get squashed
        $response->assertSee('class="flex items-center gap-2 sm:gap-3 shrink-0"', false);
    }
}
