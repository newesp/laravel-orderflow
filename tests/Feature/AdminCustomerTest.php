<?php

namespace Tests\Feature;

use App\Models\AdminUser;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Profile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class AdminCustomerTest extends TestCase
{
    use RefreshDatabase;

    protected AdminUser $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = AdminUser::create([
            'name' => 'Demo Admin',
            'email' => 'demo@example.com',
            'password' => 'demo1234',
        ]);
    }

    public function test_admin_can_view_customers_list(): void
    {
        $userId = (string) Str::uuid();

        Profile::create([
            'id' => $userId,
            'display_name' => 'John Customer',
            'role' => 'customer',
        ]);

        $response = $this->actingAs($this->admin, 'admin')->get('/admin/customers');

        $response->assertStatus(200);
        $response->assertSee('Customer Directory');
        $response->assertSee('John Customer');
    }

    public function test_admin_can_search_customers(): void
    {
        Profile::create([
            'id' => (string) Str::uuid(),
            'display_name' => 'Jane Doe',
            'role' => 'customer',
        ]);

        Profile::create([
            'id' => (string) Str::uuid(),
            'display_name' => 'Alex Smith',
            'role' => 'customer',
        ]);

        $response = $this->actingAs($this->admin, 'admin')
            ->get('/admin/customers?search=Jane');

        $response->assertStatus(200);
        $response->assertSee('Jane Doe');
        $response->assertDontSee('Alex Smith');
    }

    public function test_admin_can_view_customer_detail_and_spending(): void
    {
        $userId = (string) Str::uuid();

        $profile = Profile::create([
            'id' => $userId,
            'display_name' => 'Alice VIP',
            'role' => 'customer',
        ]);

        $product = Product::create([
            'name' => 'Premium Headset',
            'slug' => 'premium-headset',
            'price' => 5000,
            'active' => true,
        ]);

        $order1 = Order::create([
            'user_id' => $profile->id,
            'status' => 'completed',
            'total' => 5000,
        ]);

        OrderItem::create([
            'order_id' => $order1->id,
            'product_id' => $product->id,
            'product_name' => 'Premium Headset',
            'unit_price' => 5000,
            'quantity' => 1,
            'line_total' => 5000,
        ]);

        $order2 = Order::create([
            'user_id' => $profile->id,
            'status' => 'pending',
            'total' => 2000,
        ]);

        $response = $this->actingAs($this->admin, 'admin')
            ->get("/admin/customers/{$userId}");

        $response->assertStatus(200);
        $response->assertSee('Alice VIP');
        $response->assertSee('NT$ 5,000'); // Completed spend
        $response->assertSee('Premium Headset');
    }

    public function test_admin_api_can_fetch_customer_detail(): void
    {
        $userId = (string) Str::uuid();

        Profile::create([
            'id' => $userId,
            'display_name' => 'API Customer',
            'role' => 'customer',
        ]);

        $response = $this->actingAs($this->admin, 'admin')
            ->getJson("/api/admin/customers/{$userId}");

        $response->assertStatus(200);
        $response->assertJsonPath('data.display_name', 'API Customer');
        $response->assertJsonPath('data.role', 'customer');
    }
}
