<?php

namespace Tests\Feature;

use Tests\TestCase;

class HttpFallbackTest extends TestCase
{
    public function test_undefined_web_route_renders_uniform_404_error_page(): void
    {
        $response = $this->get('/nonexistent-page-url');

        $response->assertStatus(404);
        $response->assertSee('Error 404 • Page Not Located');
        $response->assertSee('Looking for Something in Our Catalog?');
        $response->assertSee('Return to Home');
        $response->assertSee('Browse Products');
        $response->assertSee('Quotation Builder');
        $response->assertSee('HUENICS');
    }

    public function test_common_aliases_redirect_to_correct_destinations(): void
    {
        // 1. quotation-builder -> quotation/builder
        $response = $this->get('/quotation-builder');
        $response->assertStatus(302);
        $response->assertRedirect(route('customer.quotation-builder'));

        // 2. catalog -> products
        $catalogResponse = $this->get('/catalog');
        $catalogResponse->assertStatus(302);
        $catalogResponse->assertRedirect(route('customer.products'));

        // 3. contact -> about
        $contactResponse = $this->get('/contact');
        $contactResponse->assertStatus(302);
        $contactResponse->assertRedirect(route('customer.about'));
    }

    public function test_api_or_json_requests_to_undefined_routes_return_json_404(): void
    {
        $response = $this->getJson('/api/undefined-endpoint');

        $response->assertStatus(404);
        $response->assertJson([
            'status' => 404,
            'error' => 'Not Found',
        ]);
    }
}
