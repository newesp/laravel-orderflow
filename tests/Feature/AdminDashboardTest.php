<?php

namespace Tests\Feature;

use App\Models\AdminSessionUser;
use App\Models\Order;
use App\Models\Product;
use App\Models\Profile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_dashboard(): void
    {
        $response = $this->get('/admin');

        $response->assertRedirect(route('admin.login'));
    }

    public function test_admin_can_access_dashboard_with_aggregated_metrics(): void
    {
        $admin = new AdminSessionUser(
            id: (string) Str::uuid(),
            email: 'admin@example.com',
            name: 'Dashboard Admin',
            role: 'admin',
            is_demo: false
        );

        Product::create([
            'name' => 'Test Item 1',
            'slug' => 'test-item-1',
            'price' => 1000,
            'active' => true,
        ]);

        Product::create([
            'name' => 'Test Item 2',
            'slug' => 'test-item-2',
            'price' => 2000,
            'active' => false,
        ]);

        $profile = Profile::create([
            'id' => (string) Str::uuid(),
            'display_name' => 'Customer Bob',
            'role' => 'customer',
        ]);

        Order::create([
            'user_id' => $profile->id,
            'status' => 'pending',
            'total' => 1000,
        ]);

        Order::create([
            'user_id' => $profile->id,
            'status' => 'completed',
            'total' => 3000,
        ]);

        $response = $this->actingAs($admin, 'admin')->get('/admin');

        $response->assertStatus(200);
        $response->assertSee('Executive Dashboard');
        $response->assertSee('NT$ 3,000');
        $response->assertSee('Customer Bob');
    }
}
