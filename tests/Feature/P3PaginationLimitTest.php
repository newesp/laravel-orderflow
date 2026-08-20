<?php

namespace Tests\Feature;

use App\Models\Product;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\AdminSessionUser;

class P3PaginationLimitTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['auth.demo.enabled' => true, 'auth.demo.email' => 'demo@example.com']);
    }

    public function test_api_pagination_respects_upper_and_lower_bounds()
    {
        $admin = new AdminSessionUser(
            id: '123',
            email: 'admin@example.com',
            name: 'Admin',
            role: 'admin',
            is_demo: false
        );

        // 1. Omitted per_page defaults to 15
        $response = $this->actingAs($admin, 'admin')->getJson('/api/admin/products');
        $response->assertStatus(200);
        $this->assertEquals(15, $response->json('data.per_page'));

        // 2. per_page=1 is respected
        $response = $this->actingAs($admin, 'admin')->getJson('/api/admin/products?per_page=1');
        $response->assertStatus(200);
        $this->assertEquals(1, $response->json('data.per_page'));

        // 3. per_page=100 is respected
        $response = $this->actingAs($admin, 'admin')->getJson('/api/admin/products?per_page=100');
        $response->assertStatus(200);
        $this->assertEquals(100, $response->json('data.per_page'));

        // 4. per_page=9999 is capped at 100
        $response = $this->actingAs($admin, 'admin')->getJson('/api/admin/products?per_page=9999');
        $response->assertStatus(200);
        $this->assertEquals(100, $response->json('data.per_page'));

        // 5. per_page=0 or negative is raised to 1
        $response = $this->actingAs($admin, 'admin')->getJson('/api/admin/products?per_page=0');
        $response->assertStatus(200);
        $this->assertEquals(1, $response->json('data.per_page'));

        $response = $this->actingAs($admin, 'admin')->getJson('/api/admin/products?per_page=-5');
        $response->assertStatus(200);
        $this->assertEquals(1, $response->json('data.per_page'));
    }
}
