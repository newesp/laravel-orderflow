<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\OrderStatusService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminOrderApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Order::query()->with(['orderItems', 'profile']);

        if ($search = $request->input('search')) {
            $clean = strtolower(trim($search));
            $query->where(function ($q) use ($clean) {
                $q->whereRaw('LOWER(CAST(id AS TEXT)) LIKE ?', ["%{$clean}%"])
                  ->orWhereRaw('LOWER(CAST(user_id AS TEXT)) LIKE ?', ["%{$clean}%"])
                  ->orWhereHas('profile', function ($pq) use ($clean) {
                      $pq->whereRaw('LOWER(display_name) LIKE ?', ["%{$clean}%"]);
                  });
            });
        }

        if ($status = $request->input('status')) {
            if (in_array($status, ['pending', 'processing', 'completed', 'cancelled'])) {
                $query->where('status', $status);
            }
        }

        $orders = $query->orderBy('created_at', 'desc')->paginate($request->integer('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $orders,
        ]);
    }

    public function show(Order $order): JsonResponse
    {
        $order->load(['orderItems.product', 'profile']);

        return response()->json([
            'success' => true,
            'data' => $order,
        ]);
    }

    public function updateStatus(Request $request, Order $order, OrderStatusService $orderStatusService): JsonResponse
    {
        $request->validate([
            'status' => ['required', 'string', 'in:pending,processing,completed,cancelled'],
        ]);

        $newStatus = $request->input('status');

        $updatedOrder = $orderStatusService->transition($order, $newStatus);

        return response()->json([
            'success' => true,
            'message' => "Order status updated to {$newStatus}",
            'data' => $updatedOrder,
        ]);
    }
}
