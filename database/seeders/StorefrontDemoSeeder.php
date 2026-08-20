<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Profile;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class StorefrontDemoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = [
            [
                'name' => 'Ergonomic Office Chair',
                'slug' => 'ergonomic-office-chair',
                'description' => 'A comfortable ergonomic chair for long working hours. Features adjustable lumbar support, 3D armrests, and breathable mesh back.',
                'price' => 5500,
                'featured' => true,
                'active' => true,
                'is_digital' => false,
            ],
            [
                'name' => 'Mechanical Keyboard',
                'slug' => 'mechanical-keyboard',
                'description' => 'Tenkeyless mechanical keyboard with Cherry MX Brown switches. RGB backlighting and durable PBT keycaps.',
                'price' => 2800,
                'featured' => true,
                'active' => true,
                'is_digital' => false,
            ],
            [
                'name' => 'Wireless Noise-Cancelling Headphones',
                'slug' => 'wireless-headphones',
                'description' => 'Over-ear headphones with active noise cancellation, 30-hour battery life, and crystal-clear microphone.',
                'price' => 4200,
                'featured' => true,
                'active' => true,
                'is_digital' => false,
            ],
            [
                'name' => 'Ultra-Wide Monitor',
                'slug' => 'ultra-wide-monitor',
                'description' => '34-inch ultra-wide curved monitor with 144Hz refresh rate, perfect for productivity and gaming.',
                'price' => 12000,
                'featured' => true,
                'active' => true,
                'is_digital' => false,
            ],
            [
                'name' => 'Minimalist Desk Lamp',
                'slug' => 'minimalist-desk-lamp',
                'description' => 'LED desk lamp with adjustable color temperature and brightness. Sleek aluminum finish.',
                'price' => 1500,
                'featured' => false,
                'active' => true,
                'is_digital' => false,
            ],
            [
                'name' => 'Wireless Charging Pad',
                'slug' => 'wireless-charging-pad',
                'description' => '15W fast wireless charging pad compatible with Qi-enabled smartphones and earbuds.',
                'price' => 850,
                'featured' => false,
                'active' => true,
                'is_digital' => false,
            ],
            [
                'name' => 'Laptop Stand',
                'slug' => 'laptop-stand',
                'description' => 'Adjustable aluminum laptop stand for better posture and cooling.',
                'price' => 990,
                'featured' => false,
                'active' => true,
                'is_digital' => false,
            ],
            [
                'name' => 'USB-C Hub',
                'slug' => 'usb-c-hub',
                'description' => '7-in-1 USB-C hub with HDMI, SD card reader, USB 3.0 ports, and 100W PD pass-through charging.',
                'price' => 1250,
                'featured' => false,
                'active' => true,
                'is_digital' => false,
            ],
            [
                'name' => 'Fullstack Mastery E-Book & Code',
                'slug' => 'fullstack-mastery-ebook',
                'description' => 'Comprehensive guide to building modern cloud-native e-commerce applications with React and Laravel.',
                'price' => 990,
                'featured' => true,
                'active' => true,
                'is_digital' => true,
                'digital_file_path' => 'digital-products/sample-ebook.zip',
            ],
        ];

        $createdProducts = [];
        foreach ($products as $p) {
            $createdProducts[] = Product::updateOrCreate(['slug' => $p['slug']], $p);
        }

        // Create Demo Customer Profiles
        $customers = [
            ['name' => 'Alice Johnson', 'email' => 'alice@example.com'],
            ['name' => 'Bob Smith', 'email' => 'bob@example.com'],
            ['name' => 'Charlie Lee', 'email' => 'charlie@example.com'],
        ];

        foreach ($customers as $c) {
            $customerId = (string) Str::uuid();
            Profile::updateOrCreate(
                ['display_name' => $c['name']],
                ['id' => $customerId, 'role' => 'customer']
            );

            // Create demo orders
            $order = Order::create([
                'id' => (string) Str::uuid(),
                'user_id' => $customerId,
                'status' => 'pending',
                'total' => 8300,
            ]);

            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $createdProducts[0]->id,
                'product_name' => $createdProducts[0]->name,
                'unit_price' => $createdProducts[0]->price,
                'quantity' => 1,
            ]);

            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $createdProducts[1]->id,
                'product_name' => $createdProducts[1]->name,
                'unit_price' => $createdProducts[1]->price,
                'quantity' => 1,
            ]);
        }
    }
}
