<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Foundation\Testing\RefreshDatabase;

class P3AdminLoginThrottleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['admin.demo.enabled' => true, 'admin.demo.email' => 'demo@example.com', 'admin.demo.password' => 'demo1234']);
    }

    public function test_login_is_throttled_after_5_failed_attempts()
    {
        // 5 failed attempts
        for ($i = 0; $i < 5; $i++) {
            $response = $this->postJson('/api/admin/login', [
                'email' => 'demo@example.com',
                'password' => 'wrong',
            ]);
            $response->assertStatus(401);
        }

        // 6th attempt should be throttled
        $response = $this->postJson('/api/admin/login', [
            'email' => 'demo@example.com',
            'password' => 'wrong',
        ]);
        $response->assertStatus(429);

        // Even with correct password, it should still be throttled
        $response = $this->postJson('/api/admin/login', [
            'email' => 'demo@example.com',
            'password' => 'demo1234',
        ]);
        $response->assertStatus(429);
    }

    public function test_successful_login_clears_throttle()
    {
        // 4 failed attempts
        for ($i = 0; $i < 4; $i++) {
            $this->postJson('/api/admin/login', [
                'email' => 'demo@example.com',
                'password' => 'wrong',
            ]);
        }

        // 5th attempt is successful
        $response = $this->postJson('/api/admin/login', [
            'email' => 'demo@example.com',
            'password' => 'demo1234',
        ]);
        $response->assertStatus(200);

        // 6th attempt (fail again) should NOT be throttled because throttle was cleared
        $response = $this->postJson('/api/admin/login', [
            'email' => 'demo@example.com',
            'password' => 'wrong',
        ]);
        $response->assertStatus(401); // not 429
    }

    public function test_different_ip_does_not_share_throttle()
    {
        // 5 failed attempts from IP 1
        for ($i = 0; $i < 5; $i++) {
            $this->withServerVariables(['REMOTE_ADDR' => '1.1.1.1'])
                ->postJson('/api/admin/login', [
                    'email' => 'demo@example.com',
                    'password' => 'wrong',
                ]);
        }

        // 6th attempt from IP 1 should be throttled
        $this->withServerVariables(['REMOTE_ADDR' => '1.1.1.1'])
            ->postJson('/api/admin/login', [
                'email' => 'demo@example.com',
                'password' => 'wrong',
            ])->assertStatus(429);

        // 1st attempt from IP 2 should NOT be throttled
        $this->withServerVariables(['REMOTE_ADDR' => '2.2.2.2'])
            ->postJson('/api/admin/login', [
                'email' => 'demo@example.com',
                'password' => 'wrong',
            ])->assertStatus(401);
    }
}
