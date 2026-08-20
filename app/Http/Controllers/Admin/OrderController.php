<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\OrderStatusService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(Request $request): View
    {
        $query = Order::query()->with(['orderItems', 'profile']);

        if ($search = $request->input('search')) {
            $clean = strtolower(trim($search));
            $query->where(function ($q) use ($clean) {
                $q->whereRaw('LOWER(CAST(id AS TEXT)) LIKE ?', ["%{$clean}%"])
                  ->orWhereRaw('LOWER(CAST(user_id AS TEXT)) LIKE ?', ["%{$clean}%"])
                  ->orWhereHas('profile', function ($pq) use ($clean) {
                      $pq->whereRaw('LOWER(display_name) LIKE ?', ["%{$clean}%"]);
                  })
                  ->orWhereHas('orderItems', function ($iq) use ($clean) {
                      $iq->whereRaw('LOWER(product_name) LIKE ?', ["%{$clean}%"]);
                  })
                  ->orWhereHas('customer', function ($cq) use ($clean) {
                      $cq->whereRaw('LOWER(email) LIKE ?', ["%{$clean}%"]);
                  });
            });
        }

        if ($status = $request->input('status')) {
            if (in_array($status, ['pending', 'processing', 'completed', 'cancelled'])) {
                $query->where('status', $status);
            }
        }

        $orders = $query->orderBy('created_at', 'desc')->paginate(10)->withQueryString();

        return view('admin.orders.index', compact('orders'));
    }

    public function show(Order $order, OrderStatusService $orderStatusService): View
    {
        $order->load(['orderItems.product', 'profile']);
        $allowedNext = $orderStatusService->getAllowedNextStatuses($order->status);

        return view('admin.orders.show', compact('order', 'allowedNext'));
    }

    public function updateStatus(Request $request, Order $order, OrderStatusService $orderStatusService): RedirectResponse
    {
        $request->validate([
            'status' => ['required', 'string', 'in:pending,processing,completed,cancelled'],
        ]);

        $newStatus = $request->input('status');

        $orderStatusService->transition($order, $newStatus);

        return redirect()->route('admin.orders.show', $order)
            ->with('success', "Order #{$order->id} status updated to '{$newStatus}'.");
    }
}
