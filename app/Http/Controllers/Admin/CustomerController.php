<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function index(Request $request): View
    {
        $query = Customer::query()->withCount('orders');

        if ($search = $request->input('search')) {
            $clean = strtolower(trim($search));
            $query->where(function ($q) use ($clean) {
                $q->whereRaw('LOWER(display_name) LIKE ?', ["%{$clean}%"])
                  ->orWhereRaw('LOWER(email) LIKE ?', ["%{$clean}%"])
                  ->orWhereRaw('LOWER(id) LIKE ?', ["%{$clean}%"]);
            });
        }

        $customers = $query->orderBy('created_at', 'desc')->paginate(10)->withQueryString();

        return view('admin.customers.index', compact('customers'));
    }

    public function show(string $id): View
    {
        $customer = Customer::findOrFail($id);

        $orders = $customer->orders()
            ->with('orderItems')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('admin.customers.show', compact('customer', 'orders'));
    }
}
