<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use App\Models\Profile;

class DashboardService
{
    public function getMetrics(): array
    {
        $totalProducts = Product::count();
        $activeProducts = Product::where('active', true)->count();
        $totalCustomers = Profile::where('role', 'customer')->count();

        $pendingOrders = Order::where('status', 'pending')->count();
        $processingOrders = Order::where('status', 'processing')->count();
        $receivedOrders = Order::where('status', 'received')->count();
        $completedOrders = Order::where('status', 'completed')->count();
        $cancelledOrders = Order::where('status', 'cancelled')->count();
        $totalOrders = Order::count();

        // Revenue from processing, received, and completed orders
        $totalRevenue = (int) Order::whereIn('status', ['processing', 'received', 'completed'])->sum('total');

        $recentOrders = Order::with(['orderItems', 'profile'])
            ->latest()
            ->take(6)
            ->get();

        return [
            'total_products' => $totalProducts,
            'active_products' => $activeProducts,
            'total_customers' => $totalCustomers,
            'total_orders' => $totalOrders,
            'pending_orders' => $pendingOrders,
            'processing_orders' => $processingOrders,
            'received_orders' => $receivedOrders,
            'completed_orders' => $completedOrders,
            'cancelled_orders' => $cancelledOrders,
            'total_revenue' => $totalRevenue,
            'formatted_total_revenue' => 'NT$ ' . number_format($totalRevenue),
            'recent_orders' => $recentOrders,
        ];
    }
}
