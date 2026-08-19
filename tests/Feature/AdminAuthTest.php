<?php

namespace Tests\Feature;

use App\Models\AdminSessionUser;
use App\Models\Product;
use App\Models\Profile;
use Firebase\JWT\JWT;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Tests\TestCase;

class AdminAuthTest extends TestCase
{
    use RefreshDatabase;

    protected string $privateKeyPem = "-----BEGIN PRIVATE KEY-----\nMIIEvAIBADANBgkqhkiG9w0BAQEFAASCBKYwggSiAgEAAoIBAQC8kyOztGDoEe0s\nHSOZzkF1qg/GnEyh3P8gYRG8sFvxy4L5oAU+ce5BCHoX6qeraSTUYMSumwCGvkip\nsxLm+RBjSA/hW7+6iAdztsNIsgNMwRhArsjepn5hNE+zSR1nVahW+rvX9HSWN6/x\nRk/hmOeZrM7Y+5gbiLhdzW5gFbqgYCCHeadM4BUE+qwgfbTGyQpF8Fyr6XJWredH\num96ji2E2RMraNWSgPKHqcrA2UKuhVX84FB/zCnT55uhYlp0bg7tH26/Gd1GZJbH\n39SRjAnL+5Src6s17HGB1tXD0GHknNO0KweFP11dZk5sMpmVREbGiP4QUJl5byhd\nqWz8i/Q9AgMBAAECggEAM7Et7zv5+IO5fQc71BSbExMcIfiEdTZsZUbWot/BRIXi\nEGWSKmz2v7MALQAxGCbJZsJkB+0LduRAbOxeuRv7JwwA9mi9JPfW+xxVad8KUob4\nC/sdqxL+v8ykMGRRgBsV+0neJjOnpen7++qnSRMuIY0iYo6NQrb2uxfuMr6iGwXm\nTpu1Qm5ikRvDBrHT/zEyb/QvkSK0rYGXd94TnMvets30YI+9yjitMFoHV5TID1DM\ndkkqnegU4biRWRdqOdFwZm0X3NPFLFnmOmfKkpFnaYYA/pxyaTHzWr8jts8lAnal\njpZ0I7eQ1KUhbHKq3je+W5mzj8iSoGWeTsySrjEOLQKBgQDdRLN6yUYcZRkyZhaM\nq2418eJ72//RN5xSqYKnHmSWVL90R3qjKjEwoN93hVCkhLGkVE0J4zjw/FDTMbAg\nz/ORLzVtPDTe/OV6Vbw9AEGTa/CXiW0DsV96SI/plBIi4nneGEHPQ11nxA3q4P9T\nJwtW1OrH9/Ivc92vfB6tLj7fCwKBgQDaLLNk9MA/HKYCNRx2gnqRvHuqnXbHEN81\nGB5IXnMn25pLgbQ5IkuE9ESFy/9pDXFt+//rLtrcBVp2RqyMlohWyNKPhvmn3Aqx\n903qJ69aKU2SQ5Bk1UzDDx5jpPcQLKYk28PKANJZD33zX9J8LtR7N723MVGKZeTw\nMJLks8km1wKBgH4o6MjOsBoKjsZMrPjB2hIJ+5+xfXfV5FzBZ8xPqPyKD6uGAee8\na9WVNDUanzNesUbIBjDoDJRi2NbCEvFygCa8qxLAbEjkGxeYgL6rQbiDp+dPJQgg\n/xZi/yMoGPso9GFspUE+4KgEggb1CL9pmK6GseMYfU8PGkwvUfJeVtynAoGAS7Vz\nUzczdzMj0GRJyj4g9m2npF8cFpweOLhz2b0czNoBwu3xcloaRrrVBHDz4qqNkBMA\njcYmoG8jIyQHQIoEKclqd+/otn0/IN6mpPi8etcWWgkkFDmId6/JZd6a9Xvo86Vn\nXbPHSqx7knbP/dPqXA/Nv8JXf2U4erkAYGibBuUCgYA0hWqwgLVdLEVbPrlbqS7G\nyMMFThHyc0vSnSRb9FG6XfS+zAjP7Ejs90XUB3nLYRFhnry5PZPws6cFfnuyQ9uK\nTCEp90OLrnIEpHD/EZq9RQT4gom/6FuW8rBVBeTcovGuZTPspJ2KIvoYEiU+YHgE\nMaUh9H3ZdbyOiX75aQkYgA==\n-----END PRIVATE KEY-----\n";

    protected array $jwks = [
        'keys' => [
            [
                'kty' => 'RSA',
                'alg' => 'RS256',
                'use' => 'sig',
                'kid' => 'test-jwk-1',
                'n' => 'vJMjs7Rg6BHtLB0jmc5BdaoPxpxModz_IGERvLBb8cuC-aAFPnHuQQh6F-qnq2kk1GDErpsAhr5IqbMS5vkQY0gP4Vu_uogHc7bDSLIDTMEYQK7I3qZ-YTRPs0kdZ1WoVvq71_R0ljev8UZP4ZjnmazO2PuYG4i4Xc1uYBW6oGAgh3mnTOAVBPqsIH20xskKRfBcq-lyVq3nR7pveo4thNkTK2jVkoDyh6nKwNlCroVV_OBQf8wp0-eboWJadG4O7R9uvxndRmSWx9_UkYwJy_uUq3OrNexxgdbVw9Bh5JzTtCsHhT9dXWZObDKZlURGxoj-EFCZeW8oXals_Iv0PQ',
                'e' => 'AQAB',
            ],
        ],
    ];

    protected function setUp(): void
    {
        parent::setUp();

        putenv('SUPABASE_URL=https://test-project.supabase.co');
        putenv('SUPABASE_ANON_KEY=test-anon-key');
    }

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

    public function test_valid_supabase_admin_token_allows_access(): void
    {
        Http::fake([
            'https://test-project.supabase.co/auth/v1/.well-known/jwks.json' => Http::response($this->jwks, 200),
        ]);

        $adminUserId = (string) Str::uuid();

        Profile::create([
            'id' => $adminUserId,
            'display_name' => 'Alice SSO Admin',
            'role' => 'admin',
        ]);

        $payload = [
            'sub' => $adminUserId,
            'email' => 'alice@example.com',
            'aud' => 'authenticated',
            'iss' => 'https://test-project.supabase.co/auth/v1',
            'exp' => time() + 3600,
        ];

        $token = JWT::encode($payload, $this->privateKeyPem, 'RS256', 'test-jwk-1');

        $response = $this->postJson('/api/admin/login', [
            'access_token' => $token,
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('data.id', $adminUserId);
        $response->assertJsonPath('data.is_demo', false);
    }

    public function test_supabase_customer_token_returns_403_forbidden(): void
    {
        Http::fake([
            'https://test-project.supabase.co/auth/v1/.well-known/jwks.json' => Http::response($this->jwks, 200),
        ]);

        $customerUserId = (string) Str::uuid();

        Profile::create([
            'id' => $customerUserId,
            'display_name' => 'Regular Customer',
            'role' => 'customer',
        ]);

        $payload = [
            'sub' => $customerUserId,
            'email' => 'customer@example.com',
            'aud' => 'authenticated',
            'iss' => 'https://test-project.supabase.co/auth/v1',
            'exp' => time() + 3600,
        ];

        $token = JWT::encode($payload, $this->privateKeyPem, 'RS256', 'test-jwk-1');

        $response = $this->postJson('/api/admin/login', [
            'access_token' => $token,
        ]);

        $response->assertStatus(403);
        $response->assertJsonPath('success', false);
    }

    public function test_invalid_or_expired_token_is_rejected(): void
    {
        Http::fake([
            'https://test-project.supabase.co/auth/v1/.well-known/jwks.json' => Http::response($this->jwks, 200),
        ]);

        $payload = [
            'sub' => (string) Str::uuid(),
            'exp' => time() - 3600, // Expired
        ];

        $token = JWT::encode($payload, $this->privateKeyPem, 'RS256', 'test-jwk-1');

        $response = $this->postJson('/api/admin/login', [
            'access_token' => $token,
        ]);

        $response->assertStatus(401);
    }

    public function test_demo_admin_login_with_valid_env_credentials(): void
    {
        putenv('DEMO_ADMIN_ENABLED=true');
        putenv('DEMO_ADMIN_EMAIL=eval@example.com');
        putenv('DEMO_ADMIN_PASSWORD=secret-eval-pass');

        $response = $this->post('/admin/login', [
            'email' => 'eval@example.com',
            'password' => 'secret-eval-pass',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticated('admin');
        $this->assertTrue(auth('admin')->user()->is_demo);
    }

    public function test_demo_admin_login_fails_with_wrong_password(): void
    {
        putenv('DEMO_ADMIN_ENABLED=true');
        putenv('DEMO_ADMIN_EMAIL=eval@example.com');
        putenv('DEMO_ADMIN_PASSWORD=secret-eval-pass');

        $response = $this->post('/admin/login', [
            'email' => 'eval@example.com',
            'password' => 'wrong-pass',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest('admin');
    }

    public function test_demo_login_fails_when_demo_disabled(): void
    {
        putenv('DEMO_ADMIN_ENABLED=false');
        putenv('DEMO_ADMIN_EMAIL=eval@example.com');
        putenv('DEMO_ADMIN_PASSWORD=secret-eval-pass');

        $response = $this->post('/admin/login', [
            'email' => 'eval@example.com',
            'password' => 'secret-eval-pass',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest('admin');
    }

    public function test_demo_admin_is_denied_destructive_operations(): void
    {
        $demoUser = new AdminSessionUser(
            id: 'demo-admin-id',
            email: 'demo@example.com',
            name: 'Demo Admin',
            role: 'admin',
            is_demo: true
        );

        $product = Product::create([
            'name' => 'Demo Protected Item',
            'slug' => 'demo-protected-item',
            'price' => 1000,
            'active' => true,
        ]);

        // Attempt deletion as Demo Admin
        $response = $this->actingAs($demoUser, 'admin')
            ->deleteJson("/api/admin/products/{$product->id}");

        $response->assertStatus(403);
        $this->assertDatabaseHas('products', ['id' => $product->id]);
    }

    public function test_admin_can_logout_and_invalidates_session(): void
    {
        $admin = new AdminSessionUser(
            id: (string) Str::uuid(),
            email: 'sso@example.com',
            name: 'SSO Admin',
            role: 'admin',
            is_demo: false
        );

        $response = $this->actingAs($admin, 'admin')->post('/admin/logout');

        $response->assertRedirect(route('admin.login'));
        $this->assertGuest('admin');
    }
}
