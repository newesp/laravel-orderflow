@extends('layouts.admin')

@section('content')
<div class="space-y-6">

    <!-- Page Header -->
    <div class="sm:flex sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Executive Dashboard</h1>
            <p class="mt-1 text-sm text-slate-500">Live operational overview of storefront orders, catalog health, and revenue.</p>
        </div>
        <div class="mt-4 sm:mt-0 flex space-x-3">
            <a href="{{ route('admin.products.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-lg shadow-sm transition">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Add Product
            </a>
            <a href="{{ route('admin.orders.index') }}" class="inline-flex items-center px-4 py-2 bg-white hover:bg-slate-50 text-slate-700 text-sm font-semibold rounded-lg border border-slate-300 shadow-xs transition">
                View Orders
            </a>
        </div>
    </div>

    <!-- Stat Cards Grid -->
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
        <!-- Revenue -->
        <div class="bg-white overflow-hidden shadow-xs rounded-xl border border-slate-200 p-5">
            <div class="flex items-center">
                <div class="p-3 rounded-lg bg-emerald-50 text-emerald-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="ml-4 w-0 flex-1">
                    <dl>
                        <dt class="text-xs font-medium text-slate-500 uppercase tracking-wider">Total Revenue</dt>
                        <dd class="text-xl font-bold text-slate-900 mt-0.5">{{ $metrics['formatted_total_revenue'] }}</dd>
                    </dl>
                </div>
            </div>
            <div class="mt-3 pt-3 border-t border-slate-100 text-xs text-slate-500">
                Processed & completed orders
            </div>
        </div>

        <!-- Pending Orders -->
        <div class="bg-white overflow-hidden shadow-xs rounded-xl border border-slate-200 p-5">
            <div class="flex items-center">
                <div class="p-3 rounded-lg bg-amber-50 text-amber-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="ml-4 w-0 flex-1">
                    <dl>
                        <dt class="text-xs font-medium text-slate-500 uppercase tracking-wider">Pending Orders</dt>
                        <dd class="text-xl font-bold text-slate-900 mt-0.5">{{ $metrics['pending_orders'] }}</dd>
                    </dl>
                </div>
            </div>
            <div class="mt-3 pt-3 border-t border-slate-100 text-xs text-slate-500">
                Requires warehouse processing
            </div>
        </div>

        <!-- Active Products -->
        <div class="bg-white overflow-hidden shadow-xs rounded-xl border border-slate-200 p-5">
            <div class="flex items-center">
                <div class="p-3 rounded-lg bg-blue-50 text-blue-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                    </svg>
                </div>
                <div class="ml-4 w-0 flex-1">
                    <dl>
                        <dt class="text-xs font-medium text-slate-500 uppercase tracking-wider">Products Active</dt>
                        <dd class="text-xl font-bold text-slate-900 mt-0.5">{{ $metrics['active_products'] }} <span class="text-xs font-normal text-slate-400">/ {{ $metrics['total_products'] }} total</span></dd>
                    </dl>
                </div>
            </div>
            <div class="mt-3 pt-3 border-t border-slate-100 text-xs text-slate-500">
                Visible on modern-storefront
            </div>
        </div>

        <!-- Total Customers -->
        <div class="bg-white overflow-hidden shadow-xs rounded-xl border border-slate-200 p-5">
            <div class="flex items-center">
                <div class="p-3 rounded-lg bg-purple-50 text-purple-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </div>
                <div class="ml-4 w-0 flex-1">
                    <dl>
                        <dt class="text-xs font-medium text-slate-500 uppercase tracking-wider">Registered Customers</dt>
                        <dd class="text-xl font-bold text-slate-900 mt-0.5">{{ $metrics['total_customers'] }}</dd>
                    </dl>
                </div>
            </div>
            <div class="mt-3 pt-3 border-t border-slate-100 text-xs text-slate-500">
                Supabase Auth profiles
            </div>
        </div>
    </div>

    <!-- Secondary Status Counts -->
    <div class="grid grid-cols-2 sm:grid-cols-5 gap-4">
        <div class="bg-white p-4 rounded-xl border border-slate-200 text-center">
            <span class="text-xs font-medium text-slate-500 uppercase">Processing</span>
            <div class="text-lg font-bold text-blue-600 mt-1">{{ $metrics['processing_orders'] }}</div>
        </div>
        <div class="bg-white p-4 rounded-xl border border-slate-200 text-center">
            <span class="text-xs font-medium text-slate-500 uppercase">Received</span>
            <div class="text-lg font-bold text-teal-600 mt-1">{{ $metrics['received_orders'] }}</div>
        </div>
        <div class="bg-white p-4 rounded-xl border border-slate-200 text-center">
            <span class="text-xs font-medium text-slate-500 uppercase">Completed</span>
            <div class="text-lg font-bold text-emerald-600 mt-1">{{ $metrics['completed_orders'] }}</div>
        </div>
        <div class="bg-white p-4 rounded-xl border border-slate-200 text-center">
            <span class="text-xs font-medium text-slate-500 uppercase">Cancelled</span>
            <div class="text-lg font-bold text-red-500 mt-1">{{ $metrics['cancelled_orders'] }}</div>
        </div>
        <div class="bg-white p-4 rounded-xl border border-slate-200 text-center">
            <span class="text-xs font-medium text-slate-500 uppercase">All-time Orders</span>
            <div class="text-lg font-bold text-slate-800 mt-1">{{ $metrics['total_orders'] }}</div>
        </div>
    </div>

    <!-- Recent Orders Table -->
    <div class="bg-white shadow-xs rounded-xl border border-slate-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-200 flex justify-between items-center">
            <div>
                <h3 class="text-base font-semibold text-slate-900">Recent Customer Orders</h3>
                <p class="text-xs text-slate-500 mt-0.5">Latest purchase records received from storefront RPC</p>
            </div>
            <a href="{{ route('admin.orders.index') }}" class="text-xs font-semibold text-indigo-600 hover:text-indigo-700">
                View all orders &rarr;
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                <thead class="bg-slate-50 text-slate-500 font-medium text-xs uppercase tracking-wider">
                    <tr>
                        <th class="px-6 py-3">Order ID</th>
                        <th class="px-6 py-3">Customer</th>
                        <th class="px-6 py-3">Status</th>
                        <th class="px-6 py-3 text-right">Total</th>
                        <th class="px-6 py-3">Date</th>
                        <th class="px-6 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse ($metrics['recent_orders'] as $order)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="px-6 py-4 font-mono text-xs text-slate-600 font-semibold">
                                #{{ substr($order->id, 0, 8) }}
                            </td>
                            <td class="px-6 py-4 text-slate-800 font-medium">
                                {{ $order->profile->display_name ?? 'Customer ' . substr($order->user_id, 0, 6) }}
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
                            <td class="px-6 py-4 text-right font-bold text-slate-900">
                                {{ $order->formatted_total }}
                            </td>
                            <td class="px-6 py-4 text-xs text-slate-500">
                                {{ $order->created_at->format('Y-m-d H:i') }}
                            </td>
                            <td class="px-6 py-4 text-right">
                                <a href="{{ route('admin.orders.show', $order) }}" class="text-xs font-semibold text-indigo-600 hover:text-indigo-900">
                                    Details
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-slate-400 text-sm">
                                No orders have been placed yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
