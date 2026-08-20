<?php

namespace Tests\Feature;

use App\Models\AdminSessionUser;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class AdminProductTest extends TestCase
{
    use RefreshDatabase;

    protected AdminSessionUser $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = new AdminSessionUser(
            id: (string) Str::uuid(),
            email: 'admin@example.com',
            name: 'Product Manager',
            role: 'admin',
            is_demo: false
        );
    }

    public function test_admin_can_view_products_list(): void
    {
        Product::create([
            'name' => 'Mechanical Keyboard',
            'slug' => 'mechanical-keyboard',
            'price' => 2800,
            'active' => true,
        ]);

        $response = $this->actingAs($this->admin, 'admin')->get('/admin/products');

        $response->assertStatus(200);
        $response->assertSee('Mechanical Keyboard');
        $response->assertSee('NT$ 2,800');
    }

    public function test_admin_can_search_products(): void
    {
        Product::create([
            'name' => 'Ultra-Wide Monitor',
            'slug' => 'ultra-wide-monitor',
            'price' => 12000,
            'active' => true,
        ]);

        Product::create([
            'name' => 'Desk Lamp',
            'slug' => 'desk-lamp',
            'price' => 1500,
            'active' => true,
        ]);

        $response = $this->actingAs($this->admin, 'admin')
            ->get('/admin/products?search=monitor');

        $response->assertStatus(200);
        $response->assertSee('Ultra-Wide Monitor');
        $response->assertDontSee('Desk Lamp');
    }

    public function test_admin_can_create_product(): void
    {
        $response = $this->actingAs($this->admin, 'admin')->post('/admin/products', [
            'name' => 'Noise Cancelling Headphones',
            'slug' => 'noise-cancelling-headphones',
            'description' => 'Great sound isolation.',
            'price' => 4500,
            'featured' => 1,
            'active' => 1,
            'is_digital' => 0,
            'image_url' => 'https://cdn.example.com/headphones.jpg',
        ]);

        $response->assertRedirect(route('admin.products.index'));

        $this->assertDatabaseHas('products', [
            'slug' => 'noise-cancelling-headphones',
            'price' => 4500,
            'featured' => true,
            'active' => true,
        ]);
    }

    public function test_admin_cannot_create_duplicate_slug(): void
    {
        Product::create([
            'name' => 'First Product',
            'slug' => 'product-slug',
            'price' => 1000,
            'active' => true,
        ]);

        $response = $this->actingAs($this->admin, 'admin')->post('/admin/products', [
            'name' => 'Second Product',
            'slug' => 'product-slug',
            'price' => 2000,
        ]);

        $response->assertSessionHasErrors('slug');
    }

    public function test_admin_can_update_product(): void
    {
        $product = Product::create([
            'name' => 'Old Name',
            'slug' => 'old-name',
            'price' => 1000,
            'active' => true,
        ]);

        $response = $this->actingAs($this->admin, 'admin')->put("/admin/products/{$product->id}", [
            'name' => 'New Name',
            'slug' => 'new-name',
            'price' => 1500,
            'active' => 1,
        ]);

        $response->assertRedirect(route('admin.products.index'));

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'New Name',
            'slug' => 'new-name',
            'price' => 1500,
        ]);
    }

    public function test_admin_can_toggle_product_active(): void
    {
        $product = Product::create([
            'name' => 'Test Product',
            'slug' => 'test-product',
            'price' => 1000,
            'active' => true,
        ]);

        $response = $this->actingAs($this->admin, 'admin')
            ->patch("/admin/products/{$product->id}/toggle-active");

        $response->assertRedirect();
        $this->assertFalse($product->fresh()->active);

        // Toggle back to active
        $this->actingAs($this->admin, 'admin')
            ->patch("/admin/products/{$product->id}/toggle-active");

        $this->assertTrue($product->fresh()->active);
    }

    public function test_admin_can_delete_product(): void
    {
        $product = Product::create([
            'name' => 'Product to Delete',
            'slug' => 'product-to-delete',
            'price' => 1000,
            'active' => true,
        ]);

        $response = $this->actingAs($this->admin, 'admin')
            ->delete("/admin/products/{$product->id}");

        $response->assertRedirect(route('admin.products.index'));
        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }

    public function test_admin_api_can_crud_products(): void
    {
        // 1. Create via API
        $createRes = $this->actingAs($this->admin, 'admin')->postJson('/api/admin/products', [
            'name' => 'API Product',
            'slug' => 'api-product',
            'price' => 990,
            'active' => true,
        ]);

        $createRes->assertStatus(201);
        $productId = $createRes->json('data.id');

        // 2. Read via API
        $readRes = $this->actingAs($this->admin, 'admin')->getJson("/api/admin/products/{$productId}");
        $readRes->assertStatus(200);
        $readRes->assertJsonPath('data.name', 'API Product');

        // 3. Update via API
        $updateRes = $this->actingAs($this->admin, 'admin')->putJson("/api/admin/products/{$productId}", [
            'name' => 'API Product Updated',
            'slug' => 'api-product',
            'price' => 1200,
            'active' => true,
        ]);
        $updateRes->assertStatus(200);
        $this->assertDatabaseHas('products', ['id' => $productId, 'price' => 1200]);

        // 4. Delete via API
        $deleteRes = $this->actingAs($this->admin, 'admin')->deleteJson("/api/admin/products/{$productId}");
        $deleteRes->assertStatus(200);
        $this->assertDatabaseMissing('products', ['id' => $productId]);
    }

    public function test_admin_can_upload_product_image(): void
    {
        \Illuminate\Support\Facades\Http::fake([
            '*/storage/v1/object/product-images/*' => \Illuminate\Support\Facades\Http::response([], 200),
        ]);
        \Illuminate\Support\Facades\Storage::fake('public');

        $file = \Illuminate\Http\UploadedFile::fake()->create('test-product.png', 100, 'image/png');

        $response = $this->actingAs($this->admin, 'admin')->postJson('/admin/products/upload-image', [
            'image' => $file,
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'url',
            'path',
            'file_name',
        ]);
        $this->assertTrue($response->json('success'));
    }

    public function test_admin_can_upload_digital_file(): void
    {
        \Illuminate\Support\Facades\Http::fake([
            '*/storage/v1/object/product-files/*' => \Illuminate\Support\Facades\Http::response([], 200),
        ]);
        \Illuminate\Support\Facades\Storage::fake('local');

        $file = \Illuminate\Http\UploadedFile::fake()->create('handbook.zip', 100, 'application/zip');

        $response = $this->actingAs($this->admin, 'admin')->postJson('/admin/products/upload-file', [
            'file' => $file,
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'path',
            'file_name',
        ]);
        $this->assertTrue($response->json('success'));
    }

    public function test_admin_upload_product_image_error_returns_generic_response(): void
    {
        \Illuminate\Support\Facades\Http::fake([
            '*/storage/v1/object/product-images/*' => \Illuminate\Support\Facades\Http::response('Upload failed', 500),
        ]);
        
        $this->mock(\App\Services\SupabaseStorageService::class, function ($mock) {
            $mock->shouldReceive('uploadProductImage')->andThrow(new \Exception('Secret internal error details'));
        });

        $file = \Illuminate\Http\UploadedFile::fake()->create('test-error.png', 100, 'image/png');

        $response = $this->actingAs($this->admin, 'admin')->postJson('/admin/products/upload-image', [
            'image' => $file,
        ]);

        $response->assertStatus(500);
        $response->assertJson([
            'success' => false,
            'message' => 'Internal Server Error',
        ]);
    }

    public function test_admin_upload_digital_file_error_returns_generic_response(): void
    {
        $this->mock(\App\Services\SupabaseStorageService::class, function ($mock) {
            $mock->shouldReceive('uploadProductFile')->andThrow(new \Exception('Secret internal error details'));
        });

        $file = \Illuminate\Http\UploadedFile::fake()->create('handbook-error.zip', 100, 'application/zip');

        $response = $this->actingAs($this->admin, 'admin')->postJson('/admin/products/upload-file', [
            'file' => $file,
        ]);

        $response->assertStatus(500);
        $response->assertJson([
            'success' => false,
            'message' => 'Internal Server Error',
        ]);
    }
}
