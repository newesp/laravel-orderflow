<?php

namespace Tests\Feature;

use App\Models\AdminSessionUser;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Profile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class AdminOrderTest extends TestCase
{
    use RefreshDatabase;

    protected AdminSessionUser $admin;
    protected Profile $customer;
    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = new AdminSessionUser(
            id: (string) Str::uuid(),
            email: 'admin@example.com',
            name: 'Order Manager',
            role: 'admin',
            is_demo: false
        );

        $this->customer = Profile::create([
            'id' => (string) Str::uuid(),
            'display_name' => 'Test Customer',
            'role' => 'customer',
        ]);

        $this->product = Product::create([
            'name' => 'Wireless Keyboard',
            'slug' => 'wireless-keyboard',
            'price' => 2500,
            'active' => true,
        ]);
    }

    public function test_admin_can_view_orders_list(): void
    {
        $order = Order::create([
            'user_id' => $this->customer->id,
            'status' => 'pending',
            'total' => 2500,
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $this->product->id,
            'product_name' => 'Wireless Keyboard',
            'unit_price' => 2500,
            'quantity' => 1,
        ]);

        $response = $this->actingAs($this->admin, 'admin')->get('/admin/orders');

        $response->assertStatus(200);
        $response->assertSee('Order Operations');
        $response->assertSee('Test Customer');
        $response->assertSee('NT$ 2,500');
    }

    public function test_admin_can_view_order_details(): void
    {
        $order = Order::create([
            'user_id' => $this->customer->id,
            'status' => 'pending',
            'total' => 5000,
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $this->product->id,
            'product_name' => 'Wireless Keyboard',
            'unit_price' => 2500,
            'quantity' => 2,
        ]);

        $response = $this->actingAs($this->admin, 'admin')->get("/admin/orders/{$order->id}");

        $response->assertStatus(200);
        $response->assertSee('Wireless Keyboard');
        $response->assertSee('NT$ 5,000');
        $response->assertSee('Mark as Processing');
    }

    public function test_admin_can_transition_order_status_progression(): void
    {
        $order = Order::create([
            'user_id' => $this->customer->id,
            'status' => 'pending',
            'total' => 2500,
        ]);

        // 1. pending -> processing
        $res1 = $this->actingAs($this->admin, 'admin')
            ->patch("/admin/orders/{$order->id}/status", ['status' => 'processing']);

        $res1->assertRedirect();
        $this->assertSame('processing', $order->fresh()->status);

        // 2. processing -> completed
        $res2 = $this->actingAs($this->admin, 'admin')
            ->patch("/admin/orders/{$order->id}/status", ['status' => 'completed']);

        $res2->assertRedirect();
        $this->assertSame('completed', $order->fresh()->status);
    }

    public function test_admin_can_cancel_pending_order(): void
    {
        $order = Order::create([
            'user_id' => $this->customer->id,
            'status' => 'pending',
            'total' => 2500,
        ]);

        $response = $this->actingAs($this->admin, 'admin')
            ->patch("/admin/orders/{$order->id}/status", ['status' => 'cancelled']);

        $response->assertRedirect();
        $this->assertSame('cancelled', $order->fresh()->status);
    }

    public function test_illegal_state_transition_via_web_returns_error_flash(): void
    {
        $order = Order::create([
            'user_id' => $this->customer->id,
            'status' => 'completed',
            'total' => 2500,
        ]);

        // completed -> pending is illegal
        $response = $this->actingAs($this->admin, 'admin')
            ->patch("/admin/orders/{$order->id}/status", ['status' => 'pending']);

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertSame('completed', $order->fresh()->status);
    }

    public function test_admin_api_rejects_illegal_transition_with_http_422(): void
    {
        $order = Order::create([
            'user_id' => $this->customer->id,
            'status' => 'completed',
            'total' => 2500,
        ]);

        $response = $this->actingAs($this->admin, 'admin')
            ->patchJson("/api/admin/orders/{$order->id}/status", [
                'status' => 'processing',
            ]);

        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
        ]);
        $response->assertJsonPath('message', "Cannot transition order status from 'completed' to 'processing'. This state change is not allowed by the order lifecycle rules.");
    }

    public function test_concurrent_status_transition_is_prevented(): void
    {
        $order = Order::create([
            'user_id' => $this->customer->id,
            'status' => 'pending',
            'total' => 2500,
        ]);

        $service = app(\App\Services\OrderStatusService::class);

        $staleOrder = Order::find($order->id);

        // Simulate a concurrent request changing the status to a terminal state in the DB
        Order::where('id', $order->id)->update(['status' => 'completed']);

        // The service should fetch the fresh lock and reject the transition based on the actual DB state,
        // not the stale object's state.
        $this->expectException(\App\Exceptions\InvalidOrderStatusTransitionException::class);

        $service->transition($staleOrder, 'processing');
    }
}
