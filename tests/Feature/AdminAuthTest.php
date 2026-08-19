<?php

namespace Tests\Feature;

use App\Models\AdminUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login_when_accessing_admin(): void
    {
        $response = $this->get('/admin');

        $response->assertRedirect(route('admin.login'));
    }

    public function test_guest_receives_401_when_accessing_protected_api(): void
    {
        $response = $this->getJson('/api/admin/products');

        $response->assertStatus(401);
    }

    public function test_admin_can_view_login_page(): void
    {
        $response = $this->get('/admin/login');

        $response->assertStatus(200);
        $response->assertSee('OrderFlow Lite');
        $response->assertSee('demo@example.com');
    }

    public function test_admin_can_login_with_valid_credentials(): void
    {
        $admin = AdminUser::create([
            'name' => 'Demo Admin',
            'email' => 'demo@example.com',
            'password' => Hash::make('demo1234'),
            'is_demo' => true,
        ]);

        $response = $this->post('/admin/login', [
            'email' => 'demo@example.com',
            'password' => 'demo1234',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($admin, 'admin');
    }

    public function test_admin_login_fails_with_invalid_credentials(): void
    {
        AdminUser::create([
            'name' => 'Demo Admin',
            'email' => 'demo@example.com',
            'password' => Hash::make('demo1234'),
        ]);

        $response = $this->post('/admin/login', [
            'email' => 'demo@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest('admin');
    }

    public function test_admin_can_logout(): void
    {
        $admin = AdminUser::create([
            'name' => 'Demo Admin',
            'email' => 'demo@example.com',
            'password' => Hash::make('demo1234'),
        ]);

        $response = $this->actingAs($admin, 'admin')->post('/admin/logout');

        $response->assertRedirect(route('admin.login'));
        $this->assertGuest('admin');
    }

    public function test_admin_can_login_via_api(): void
    {
        AdminUser::create([
            'name' => 'Demo Admin',
            'email' => 'demo@example.com',
            'password' => Hash::make('demo1234'),
        ]);

        $response = $this->postJson('/api/admin/login', [
            'email' => 'demo@example.com',
            'password' => 'demo1234',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'data' => [
                'email' => 'demo@example.com',
            ],
        ]);
    }
}
