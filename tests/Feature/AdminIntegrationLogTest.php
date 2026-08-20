<?php

namespace Tests\Feature;

use App\Models\AdminSessionUser;
use App\Models\IntegrationLog;
use App\Models\Order;
use App\Models\Profile;
use App\Services\OrderStatusService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Tests\TestCase;

class AdminIntegrationLogTest extends TestCase
{
    use RefreshDatabase;

    protected AdminSessionUser $admin;
    protected Profile $customer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = new AdminSessionUser(
            id: (string) Str::uuid(),
            email: 'admin@example.com',
            name: 'Audit Admin',
            role: 'admin',
            is_demo: false
        );

        $this->customer = Profile::create([
            'id' => (string) Str::uuid(),
            'display_name' => 'Log Customer',
            'role' => 'customer',
        ]);
    }

    public function test_order_status_change_creates_audit_log(): void
    {
        $order = Order::create([
            'user_id' => $this->customer->id,
            'status' => 'pending',
            'total' => 3500,
        ]);

        $service = app(OrderStatusService::class);
        $service->transition($order, 'processing');

        $this->assertDatabaseHas('integration_logs', [
            'event_type' => 'order.status_changed',
            'reference_type' => 'order',
            'reference_id' => $order->id,
            'status' => 'success',
        ]);
    }

    public function test_webhook_dispatch_logs_success_when_configured(): void
    {
        config(['services.webhook.url' => 'https://webhook.example.com/events']);

        Http::fake([
            'https://webhook.example.com/*' => Http::response(['received' => true], 200),
        ]);

        $order = Order::create([
            'user_id' => $this->customer->id,
            'status' => 'pending',
            'total' => 1200,
        ]);

        $service = app(OrderStatusService::class);
        $service->transition($order, 'processing');

        $this->assertDatabaseHas('integration_logs', [
            'event_type' => 'webhook.dispatched',
            'reference_id' => $order->id,
            'target' => 'https://webhook.example.com/events',
            'status' => 'success',
        ]);
    }

    public function test_webhook_failure_is_resilient_and_logged(): void
    {
        config(['services.webhook.url' => 'https://webhook.example.com/events']);

        Http::fake([
            'https://webhook.example.com/*' => Http::response('Server error', 500),
        ]);

        $order = Order::create([
            'user_id' => $this->customer->id,
            'status' => 'pending',
            'total' => 1200,
        ]);

        $service = app(OrderStatusService::class);
        $updatedOrder = $service->transition($order, 'processing');

        $this->assertSame('processing', $updatedOrder->status);
        $this->assertDatabaseHas('integration_logs', [
            'event_type' => 'webhook.failed',
            'reference_id' => $order->id,
            'status' => 'failed',
        ]);
    }

    public function test_admin_can_view_integration_logs_page(): void
    {
        IntegrationLog::create([
            'event_type' => 'order.status_changed',
            'reference_type' => 'order',
            'reference_id' => (string) Str::uuid(),
            'target' => 'internal-audit',
            'status' => 'success',
            'payload' => ['sample' => 'data'],
        ]);

        $response = $this->actingAs($this->admin, 'admin')->get('/admin/integration-logs');

        $response->assertStatus(200);
        $response->assertSee('Integration Telemetry');
        $response->assertSee('order.status_changed');
        $response->assertSee('internal-audit');
    }

    public function test_admin_api_can_fetch_integration_logs(): void
    {
        IntegrationLog::create([
            'event_type' => 'order.status_changed',
            'reference_type' => 'order',
            'reference_id' => (string) Str::uuid(),
            'target' => 'internal-audit',
            'status' => 'success',
        ]);

        $response = $this->actingAs($this->admin, 'admin')
            ->getJson('/api/admin/integration-logs');

        $response->assertStatus(200);
        $response->assertJsonPath('data.total', 1);
    }

    public function test_integration_event_is_dispatched_after_commit(): void
    {
        $order = Order::create([
            'user_id' => $this->customer->id,
            'status' => 'pending',
            'total' => 1200,
        ]);

        $service = app(OrderStatusService::class);
        $initialLevel = \Illuminate\Support\Facades\DB::transactionLevel();

        $eventFired = false;
        \Illuminate\Support\Facades\Event::listen(\App\Events\OrderStatusChanged::class, function () use (&$eventFired, $initialLevel) {
            $eventFired = true;
            // Assert we are back to the initial transaction level (1 due to RefreshDatabase)
            $this->assertEquals($initialLevel, \Illuminate\Support\Facades\DB::transactionLevel());
        });

        $service->transition($order, 'processing');

        $this->assertTrue($eventFired);
    }
}
