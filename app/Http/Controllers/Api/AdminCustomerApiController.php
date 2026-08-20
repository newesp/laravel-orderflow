<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminCustomerApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Customer::query()
            ->withCount('orders')
            ->withSum(['orders as total_spent' => function ($query) {
                $query->whereIn('status', ['processing', 'completed']);
            }], 'total');

        if ($search = $request->input('search')) {
            $clean = strtolower(trim($search));
            $query->where(function ($q) use ($clean) {
                $q->whereRaw('LOWER(display_name) LIKE ?', ["%{$clean}%"])
                  ->orWhereRaw('LOWER(email) LIKE ?', ["%{$clean}%"]);
            });
        }

        $customers = $query->orderBy('created_at', 'desc')->paginate($this->getPerPage($request));

        return response()->json([
            'success' => true,
            'data' => $customers,
        ]);
    }

    public function show(string $id): JsonResponse
    {
        $customer = Customer::with(['orders.orderItems'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $customer->id,
                'email' => $customer->email,
                'display_name' => $customer->display_name,
                'role' => $customer->role,
                'orders_count' => $customer->orders->count(),
                'total_spent' => $customer->total_spent,
                'formatted_total_spent' => $customer->formatted_total_spent,
                'created_at' => $customer->created_at,
                'orders' => $customer->orders,
            ],
        ]);
    }
}
