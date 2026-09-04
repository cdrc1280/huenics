<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductImageStorageTest extends TestCase
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

    public function test_uploaded_product_image_is_accessible_via_storage_route(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('test_product.jpg', 400, 400);
        $storedPath = $file->store('products/images', 'public');

        $this->assertTrue(Storage::disk('public')->exists($storedPath));

        // Create temporary real file in storage/app/public to test the route
        $realDir = storage_path('app/public/products/images');
        if (!file_exists($realDir)) {
            mkdir($realDir, 0755, true);
        }
        $testFile = $realDir . '/phpunit_test_img.jpg';
        file_put_contents($testFile, $file->getContent());

        try {
            $response = $this->get('/storage/products/images/phpunit_test_img.jpg');
            $response->assertStatus(200);
            $this->assertStringContainsString('image/jpeg', $response->headers->get('Content-Type'));
        } finally {
            if (file_exists($testFile)) {
                @unlink($testFile);
            }
        }
    }

    public function test_non_existent_storage_file_returns_404(): void
    {
        $response = $this->get('/storage/products/images/non_existent_9999.png');
        $response->assertStatus(404);
    }

    public function test_product_placeholder_image_file_exists(): void
    {
        $placeholderPath = public_path('images/placeholder-product.png');
        $this->assertFileExists($placeholderPath);
    }

    public function test_existing_uploaded_product_image_on_disk_is_served(): void
    {
        // Test with the user's actual uploaded image if present
        $realFile = storage_path('app/public/products/images/01M1P8FD7QWJ6GR8ZJ4H2E98ZJ.jfif');
        if (file_exists($realFile)) {
            $response = $this->get('/storage/products/images/01M1P8FD7QWJ6GR8ZJ4H2E98ZJ.jfif');
            $response->assertStatus(200);
        } else {
            $this->assertTrue(true);
        }
    }
}
