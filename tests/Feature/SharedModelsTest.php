<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Profile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class SharedModelsTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_model_creates_with_uuid_and_casts(): void
    {
        $product = Product::create([
            'name' => 'Ergonomic Chair',
            'slug' => 'ergonomic-chair',
            'description' => 'Great chair for back support.',
            'price' => 5500,
            'image_paths' => ['https://storage.example.com/chair.jpg'],
            'featured' => true,
            'active' => true,
            'is_digital' => false,
        ]);

        $this->assertTrue(Str::isUuid($product->id));
        $this->assertSame(5500, $product->price);
        $this->assertSame('NT$ 5,500', $product->formatted_price);
        $this->assertSame(['https://storage.example.com/chair.jpg'], $product->image_paths);
        $this->assertTrue($product->featured);
        $this->assertTrue($product->active);
    }

    public function test_order_and_order_item_snapshot_integrity(): void
    {
        $userId = (string) Str::uuid();

        $profile = Profile::create([
            'id' => $userId,
            'display_name' => 'Alice Demo',
            'role' => 'customer',
        ]);

        $product = Product::create([
            'name' => 'Mechanical Keyboard',
            'slug' => 'mechanical-keyboard',
            'price' => 2800,
            'active' => true,
        ]);

        $order = Order::create([
            'user_id' => $profile->id,
            'status' => 'pending',
            'total' => 5600,
        ]);

        $item = OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_name' => 'Mechanical Keyboard',
            'unit_price' => 2800,
            'quantity' => 2,
        ]);

        $this->assertTrue(Str::isUuid($order->id));
        $this->assertSame('NT$ 5,600', $order->formatted_total);
        $this->assertCount(1, $order->orderItems);
        $this->assertSame($product->id, $order->orderItems->first()->product_id);
        $this->assertSame('NT$ 2,800', $item->formatted_unit_price);
        $this->assertSame('NT$ 5,600', $item->formatted_line_total);
    }

    public function test_order_item_line_total_is_generated_by_database(): void
    {
        $userId = (string) Str::uuid();
        $profile = Profile::create([
            'id' => $userId,
            'display_name' => 'Tester',
            'role' => 'customer',
        ]);

        $order = Order::create([
            'user_id' => $profile->id,
            'status' => 'pending',
            'total' => 0,
        ]);

        \Illuminate\Support\Facades\DB::table('order_items')->insert([
            'order_id' => $order->id,
            'product_id' => null,
            'product_name' => 'Raw Item',
            'unit_price' => 1500,
            'quantity' => 3,
        ]);

        $item = \Illuminate\Support\Facades\DB::table('order_items')->where('order_id', $order->id)->first();
        
        $this->assertSame(4500, (int) $item->line_total);
    }
}
