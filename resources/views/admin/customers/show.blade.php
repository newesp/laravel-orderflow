@extends('layouts.admin')

@section('content')
<div class="space-y-6">

    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <div class="flex items-center space-x-3">
                <h1 class="text-2xl font-bold text-slate-900 tracking-tight">{{ $customer->display_name ?? 'Customer Profile' }}</h1>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $customer->role === 'admin' ? 'bg-indigo-100 text-indigo-800' : 'bg-slate-100 text-slate-700' }}">
                    {{ ucfirst($customer->role) }}
                </span>
            </div>
            <p class="mt-1 text-sm text-slate-500 font-mono">{{ $customer->email }} &bull; User ID: {{ $customer->id }}</p>
        </div>
        <a href="{{ route('admin.customers.index') }}" class="text-sm font-semibold text-slate-600 hover:text-slate-900">
            &larr; Back to Directory
        </a>
    </div>

    <!-- Customer Overview Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
        <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-xs">
            <span class="text-xs font-medium text-slate-500 uppercase tracking-wider">Lifetime Spending</span>
            <div class="text-2xl font-bold text-slate-900 mt-1">{{ $customer->formatted_total_spent }}</div>
            <p class="text-xs text-slate-400 mt-1">Processed and completed orders</p>
        </div>

        <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-xs">
            <span class="text-xs font-medium text-slate-500 uppercase tracking-wider">Total Orders Placed</span>
            <div class="text-2xl font-bold text-slate-900 mt-1">{{ $orders->total() }}</div>
            <p class="text-xs text-slate-400 mt-1">All lifecycle states</p>
        </div>

        <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-xs">
            <span class="text-xs font-medium text-slate-500 uppercase tracking-wider">Registration Date</span>
            <div class="text-2xl font-bold text-slate-900 mt-1">{{ $customer->created_at ? $customer->created_at->format('Y-m-d') : 'Unknown' }}</div>
            <p class="text-xs text-slate-400 mt-1">Supabase Auth created_at</p>
        </div>
    </div>

    <!-- Customer Orders History Table -->
    <div class="bg-white shadow-xs rounded-xl border border-slate-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-200">
            <h3 class="text-base font-semibold text-slate-900">Order Purchase History</h3>
            <p class="text-xs text-slate-500 mt-0.5">Chronological record of orders placed by this customer</p>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                <thead class="bg-slate-50 text-slate-500 font-medium text-xs uppercase tracking-wider">
                    <tr>
                        <th class="px-6 py-3.5">Order ID</th>
                        <th class="px-6 py-3.5">Items Purchased</th>
                        <th class="px-6 py-3.5">Status</th>
                        <th class="px-6 py-3.5 text-right">Total</th>
                        <th class="px-6 py-3.5">Placed On</th>
                        <th class="px-6 py-3.5 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse ($orders as $order)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="px-6 py-4 font-mono text-xs text-slate-600 font-semibold">
                                #{{ substr($order->id, 0, 8) }}
                            </td>
                            <td class="px-6 py-4">
                                <ul class="text-xs text-slate-700 space-y-1">
                                    @foreach ($order->orderItems as $item)
                                        <li>{{ $item->quantity }}x {{ $item->product_name }}</li>
                                    @endforeach
                                </ul>
                            </td>
                            <td class="px-6 py-4">
                                @if ($order->status === 'pending')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-800">Pending</span>
                                @elseif ($order->status === 'processing')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-800">Processing</span>
                                @elseif ($order->status === 'completed')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">Completed</span>
                                @elseif ($order->status === 'cancelled')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-800">Cancelled</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right font-bold text-slate-900">
                                {{ $order->formatted_total }}
                            </td>
                            <td class="px-6 py-4 text-xs text-slate-500">
                                {{ $order->created_at->format('Y-m-d H:i') }}
                            </td>
                            <td class="px-6 py-4 text-right">
                                <a href="{{ route('admin.orders.show', $order) }}" class="text-xs font-semibold text-indigo-600 hover:text-indigo-900">
                                    Details &rarr;
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-slate-400">
                                This customer has not placed any orders yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($orders->hasPages())
            <div class="px-6 py-4 border-t border-slate-200 bg-slate-50">
                {{ $orders->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
