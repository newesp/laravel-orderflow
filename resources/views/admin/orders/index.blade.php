@extends('layouts.admin')

@section('content')
<div class="space-y-6">

    <!-- Header -->
    <div class="sm:flex sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Order Operations</h1>
            <p class="mt-1 text-sm text-slate-500">Monitor customer transactions, verify immutable line items, and progress fulfillment lifecycle.</p>
        </div>
    </div>

    <!-- Filters & Search -->
    <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs">
        <form method="GET" action="{{ route('admin.orders.index') }}" class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="sm:col-span-2">
                <label for="search" class="sr-only">Search orders</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <input type="text"
                           name="search"
                           id="search"
                           value="{{ request('search') }}"
                           placeholder="Search by Order ID, Customer Name, or Product Name..."
                           class="w-full pl-9 pr-4 py-2 bg-slate-50 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white transition" />
                </div>
            </div>

            <div class="flex space-x-2">
                <select name="status" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white transition">
                    <option value="">All Statuses</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending Only</option>
                    <option value="processing" {{ request('status') === 'processing' ? 'selected' : '' }}>Processing Only</option>
                    <option value="received" {{ request('status') === 'received' ? 'selected' : '' }}>Received Only</option>
                    <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed Only</option>
                    <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled Only</option>
                </select>

                <button type="submit" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-white text-sm font-medium rounded-lg transition">
                    Filter
                </button>
            </div>
        </form>
    </div>

    <!-- Orders Table -->
    <div class="bg-white shadow-xs rounded-xl border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                <thead class="bg-slate-50 text-slate-500 font-medium text-xs uppercase tracking-wider">
                    <tr>
                        <th class="px-6 py-3.5">Order ID</th>
                        <th class="px-6 py-3.5">Customer</th>
                        <th class="px-6 py-3.5">Status</th>
                        <th class="px-6 py-3.5">Items Snapshot</th>
                        <th class="px-6 py-3.5 text-right">Total Amount</th>
                        <th class="px-6 py-3.5">Date Placed</th>
                        <th class="px-6 py-3.5 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse ($orders as $order)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="px-6 py-4 font-mono text-xs font-semibold text-slate-700">
                                #{{ substr($order->id, 0, 8) }}
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-semibold text-slate-900">{{ $order->profile->display_name ?? 'Customer' }}</div>
                                <div class="text-xs text-slate-400 font-mono">{{ substr($order->user_id, 0, 8) }}...</div>
                            </td>
                            <td class="px-6 py-4">
                                @if ($order->status === 'pending')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-800">Pending</span>
                                @elseif ($order->status === 'processing')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-800">Processing</span>
                                @elseif ($order->status === 'received')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-teal-100 text-teal-800">Received</span>
                                @elseif ($order->status === 'completed')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">Completed</span>
                                @elseif ($order->status === 'cancelled')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-800">Cancelled</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-xs text-slate-600">
                                <span class="font-medium text-slate-800">{{ $order->orderItems->count() }} items</span>
                                <div class="text-slate-400 truncate max-w-xs">
                                    {{ $order->orderItems->pluck('product_name')->implode(', ') }}
                                </div>
                            </td>
                            <td class="px-6 py-4 text-right font-bold text-slate-900">
                                {{ $order->formatted_total }}
                            </td>
                            <td class="px-6 py-4 text-xs text-slate-500">
                                {{ $order->created_at->format('Y-m-d H:i') }}
                            </td>
                            <td class="px-6 py-4 text-right">
                                <a href="{{ route('admin.orders.show', $order) }}" class="text-xs font-semibold text-indigo-600 hover:text-indigo-900">
                                    Manage &rarr;
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-8 text-center text-slate-400">
                                No orders found matching filter criteria.
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
