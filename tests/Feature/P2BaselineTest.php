<?php

namespace Tests\Feature;

use App\Models\AdminSessionUser;
use App\Models\Order;
use App\Models\Product;
use App\Models\Profile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class P2BaselineTest extends TestCase
{
    use RefreshDatabase;

    protected AdminSessionUser $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = new AdminSessionUser(
            id: (string) Str::uuid(),
            email: 'admin@example.com',
            name: 'Test Admin',
            role: 'admin',
            is_demo: false
        );
    }

    protected function createCustomerWithEmail(string $id, string $email, string $displayName): Profile
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::table('auth.users')->insert([
                'id' => $id,
                'email' => $email,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return Profile::create([
            'id' => $id,
            'display_name' => $displayName,
            'role' => 'customer',
        ]);
    }

    public function test_web_order_list_can_search_by_customer_email_case_insensitive()
    {
        $id1 = (string) Str::uuid();
        $this->createCustomerWithEmail($id1, 'john.doe@example.com', 'john.doe');
        Order::create(['user_id' => $id1, 'status' => 'pending', 'total' => 100]);

        $id2 = (string) Str::uuid();
        $this->createCustomerWithEmail($id2, 'jane.smith@example.com', 'jane.smith');
        Order::create(['user_id' => $id2, 'status' => 'pending', 'total' => 200]);

        // Search token contains the email domain, which display_name does not have.
        // Thus, it will only match email, not display_name.
        $response = $this->actingAs($this->admin, 'admin')
            ->get('/admin/orders?search=JOHN.DOE@EXAMPLE.COM');

        $response->assertStatus(200);
        $response->assertSee('john.doe');
        $response->assertDontSee('jane.smith');
    }

    public function test_admin_api_order_list_can_search_by_customer_email_case_insensitive()
    {
        $id1 = (string) Str::uuid();
        $this->createCustomerWithEmail($id1, 'alice.wonder@example.com', 'alice.wonder');
        Order::create(['user_id' => $id1, 'status' => 'pending', 'total' => 100]);

        $id2 = (string) Str::uuid();
        $this->createCustomerWithEmail($id2, 'bob.builder@example.com', 'bob.builder');
        Order::create(['user_id' => $id2, 'status' => 'pending', 'total' => 200]);

        // Same trick for API
        $response = $this->actingAs($this->admin, 'admin')
            ->getJson('/api/admin/orders?search=ALICE.WONDER@EXAMPLE.COM');

        $response->assertStatus(200);
        $response->assertJsonFragment(['user_id' => $id1]);
        $response->assertJsonMissing(['user_id' => $id2]);
    }

    public function test_web_product_update_creates_product_updated_integration_log()
    {
        $product = Product::create([
            'name' => 'Old Name',
            'slug' => 'old-name',
            'price' => 100,
        ]);

        $response = $this->actingAs($this->admin, 'admin')
            ->put("/admin/products/{$product->id}", [
                'name' => 'New Name',
                'slug' => 'new-name',
                'price' => 200,
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('integration_logs', [
            'event_type' => 'product.updated',
            'reference_type' => 'product',
            'reference_id' => $product->id,
            'status' => 'success',
        ]);

        $log = DB::table('integration_logs')->where('event_type', 'product.updated')->first();
        $payload = json_decode($log->payload, true);

        $this->assertEquals($product->id, $payload['product_id']);
        $this->assertEquals('New Name', $payload['changes']['name']);
        $this->assertEquals(200, $payload['changes']['price']);
        $this->assertArrayNotHasKey('updated_at', $payload['changes']);
    }

    public function test_api_product_update_creates_product_updated_integration_log()
    {
        $product = Product::create([
            'name' => 'Old Name',
            'slug' => 'old-name',
            'price' => 100,
        ]);

        $response = $this->actingAs($this->admin, 'admin')
            ->putJson("/api/admin/products/{$product->id}", [
                'name' => 'New Name',
                'slug' => 'old-name', // keeping slug same
                'price' => 100, // keeping price same
            ]);

        $response->assertStatus(200);

        $log = DB::table('integration_logs')->where('event_type', 'product.updated')->first();
        $payload = json_decode($log->payload, true);

        $this->assertArrayHasKey('name', $payload['changes']);
        $this->assertArrayNotHasKey('price', $payload['changes'], 'Unchanged fields should not be reported');
    }

    public function test_web_product_toggle_active_creates_integration_log()
    {
        $product = Product::create([
            'name' => 'Old Name',
            'slug' => 'old-name',
            'price' => 100,
            'active' => true,
        ]);

        $response = $this->actingAs($this->admin, 'admin')
            ->patch("/admin/products/{$product->id}/toggle-active");

        $response->assertRedirect();

        $log = DB::table('integration_logs')->where('event_type', 'product.updated')->first();
        $payload = json_decode($log->payload, true);

        $this->assertArrayHasKey('active', $payload['changes']);
        $this->assertFalse($payload['changes']['active']);
    }

    public function test_api_product_toggle_status_creates_integration_log()
    {
        $product = Product::create([
            'name' => 'Old Name',
            'slug' => 'old-name',
            'price' => 100,
            'active' => true,
        ]);

        $response = $this->actingAs($this->admin, 'admin')
            ->patchJson("/api/admin/products/{$product->id}/status");

        $response->assertStatus(200);

        $log = DB::table('integration_logs')->where('event_type', 'product.updated')->first();
        $payload = json_decode($log->payload, true);

        $this->assertArrayHasKey('active', $payload['changes']);
        $this->assertFalse($payload['changes']['active']);
    }

    public function test_api_product_update_invalid_request_contract()
    {
        $product = Product::create([
            'name' => 'Old Name',
            'slug' => 'old-name',
            'price' => 100,
        ]);

        $response = $this->actingAs($this->admin, 'admin')
            ->putJson("/api/admin/products/{$product->id}", [
                'name' => '', // invalid
                'price' => 'invalid_price' // invalid
            ]);

        $response->assertStatus(422);
        $response->assertJsonStructure([
            'success',
            'message',
            'errors' => [
                'name',
                'price',
            ]
        ]);
        $response->assertJson(['success' => false, 'message' => 'Validation failed.']);
    }

    public function test_api_validation_error_contract()
    {
        $response = $this->actingAs($this->admin, 'admin')
            ->postJson("/api/admin/products", []);

        $response->assertStatus(422);
        $response->assertJsonStructure([
            'success',
            'message',
            'errors' => [
                'name',
                'slug',
                'price',
            ]
        ]);
        $response->assertJson(['success' => false, 'message' => 'Validation failed.']);
    }

    public function test_web_validation_redirects_with_errors()
    {
        $response = $this->actingAs($this->admin, 'admin')
            ->post("/admin/products", []);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['name', 'slug', 'price']);
    }

    public function test_api_order_status_invalid_enum()
    {
        $id1 = (string) Str::uuid();
        $this->createCustomerWithEmail($id1, 'alice.wonder@example.com', 'alice.wonder');
        $order = Order::create(['user_id' => $id1, 'status' => 'pending', 'total' => 100]);

        $response = $this->actingAs($this->admin, 'admin')
            ->patchJson("/api/admin/orders/{$order->id}/status", [
                'status' => 'invalid_status'
            ]);

        $response->assertStatus(422);
        $response->assertJsonStructure([
            'success',
            'message',
            'errors' => [
                'status'
            ]
        ]);
    }
}
