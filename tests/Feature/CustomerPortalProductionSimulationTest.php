<?php

namespace Tests\Feature;

use App\Http\Controllers\CustomerPortalController;
use App\Models\CompanySetting;
use App\Models\Product;
use Illuminate\Support\Facades\Cache;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerPortalProductionSimulationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    public function test_customer_portal_home_renders_successfully(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('Industrial Lighting');
        $response->assertSee('Direct Importer');
    }

    public function test_company_setting_gracefully_handles_missing_or_corrupt_data(): void
    {
        // Force an invalid setting key and ensure fallback is returned without throwing
        $val = CompanySetting::getSetting('non_existent_key_xyz', 'fallback_val');
        $this->assertEquals('fallback_val', $val);

        // Ensure years in business produces valid positive integer even if cache/db empty
        $years = CompanySetting::getYearsInBusiness();
        $this->assertIsInt($years);
        $this->assertGreaterThanOrEqual(1, $years);
    }

    public function test_customer_portal_controller_index_method_direct_execution(): void
    {
        $controller = app(CustomerPortalController::class);
        $view = $controller->index();

        $this->assertEquals('customer.home', $view->name());
        $data = $view->getData();

        $this->assertArrayHasKey('featuredProducts', $data);
        $this->assertArrayHasKey('categories', $data);
        $this->assertArrayHasKey('totalProductsCount', $data);
        $this->assertArrayHasKey('yearsInBusiness', $data);

        // Assert that the entire Blade template compiles and renders to string without error
        $renderedHtml = $view->render();
        $this->assertNotEmpty($renderedHtml);
        $this->assertStringContainsString('Huenics', $renderedHtml);
    }
}
