@extends('layouts.admin')

@section('content')
<div class="space-y-6">

    <!-- Header -->
    <div class="sm:flex sm:items-center sm:justify-between">
        <div>
            <div class="flex items-center space-x-3">
                <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Order #{{ $order->id }}</h1>
                @if ($order->status === 'pending')
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800">Pending</span>
                @elseif ($order->status === 'processing')
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-800">Processing</span>
                @elseif ($order->status === 'received')
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-teal-100 text-teal-800">Received</span>
                @elseif ($order->status === 'completed')
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800">Completed</span>
                @elseif ($order->status === 'cancelled')
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-red-100 text-red-800">Cancelled</span>
                @endif
            </div>
            <p class="mt-1 text-sm text-slate-500">Placed on {{ $order->created_at->format('Y-m-d H:i:s') }}</p>
        </div>
        <a href="{{ route('admin.orders.index') }}" class="text-sm font-semibold text-slate-600 hover:text-slate-900">
            &larr; Back to Orders
        </a>
    </div>

    <!-- Status Progression Controls Card -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-xs p-6">
        <h3 class="text-sm font-semibold text-slate-900 uppercase tracking-wider mb-3">Order Status Progression</h3>

        @if (count($allowedNext) > 0)
            <div class="flex flex-wrap items-center gap-3">
                <span class="text-xs text-slate-500">Allowed next state transitions:</span>
                
                @if ($order->status === 'processing')
                    <span class="px-4 py-2 bg-slate-100 text-slate-500 text-xs font-bold rounded-lg border border-slate-200">
                        Waiting for customer receipt confirmation
                    </span>
                @elseif ($order->status === 'received')
                    <span class="px-4 py-2 bg-slate-100 text-slate-500 text-xs font-bold rounded-lg border border-slate-200">
                        Customer confirmed receipt
                    </span>
                @endif

                @foreach ($allowedNext as $nextStatus)
                    <form method="POST" action="{{ route('admin.orders.update-status', $order) }}" class="inline">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="status" value="{{ $nextStatus }}">

                        @if ($nextStatus === 'processing')
                            <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-lg shadow-sm transition">
                                &rarr; Mark as Processing
                            </button>
                        @elseif ($nextStatus === 'completed')
                            <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-lg shadow-sm transition">
                                &check; Mark as Completed
                            </button>
                        @elseif ($nextStatus === 'cancelled')
                            <button type="submit"
                                    class="px-4 py-2 bg-red-50 hover:bg-red-100 text-red-700 border border-red-200 text-xs font-bold rounded-lg transition"
                                    onclick="return confirm('Are you sure you want to cancel this order? This action cannot be undone.');">
                                &cross; Cancel Order
                            </button>
                        @endif
                    </form>
                @endforeach
            </div>
        @else
            <div class="text-xs text-slate-500 italic bg-slate-50 p-3 rounded-lg border border-slate-100">
                This order is in terminal state <strong class="text-slate-700">"{{ $order->status }}"</strong>. No further state transitions are permitted by the business lifecycle rules.
            </div>
        @endif
    </div>

    <!-- Overview Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

        <!-- Left 2 cols: Order Items Table -->
        <div class="md:col-span-2 bg-white rounded-xl border border-slate-200 shadow-xs overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-200 flex justify-between items-center">
                <div>
                    <h3 class="text-base font-semibold text-slate-900">Purchased Items Snapshot</h3>
                    <p class="text-xs text-slate-500">Immutable snapshot of product name and unit price at checkout</p>
                </div>
            </div>

            <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                <thead class="bg-slate-50 text-slate-500 font-medium text-xs uppercase tracking-wider">
                    <tr>
                        <th class="px-6 py-3">Product Name</th>
                        <th class="px-6 py-3 text-right">Unit Price</th>
                        <th class="px-6 py-3 text-center">Qty</th>
                        <th class="px-6 py-3 text-right">Line Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @foreach ($order->orderItems as $item)
                        <tr>
                            <td class="px-6 py-4">
                                <div class="font-semibold text-slate-900">{{ $item->product_name }}</div>
                                @if ($item->product && $item->product->is_digital)
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-purple-100 text-purple-800 mt-1">Digital Asset</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right text-slate-700">
                                {{ $item->formatted_unit_price }}
                            </td>
                            <td class="px-6 py-4 text-center font-bold text-slate-900">
                                {{ $item->quantity }}
                            </td>
                            <td class="px-6 py-4 text-right font-bold text-slate-900">
                                {{ $item->formatted_line_total }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-slate-50 border-t border-slate-200">
                    <tr>
                        <th colspan="3" class="px-6 py-4 text-right text-sm font-semibold text-slate-700">Total Order Amount:</th>
                        <th class="px-6 py-4 text-right text-lg font-black text-indigo-700">{{ $order->formatted_total }}</th>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Right 1 col: Customer & Metadata Card -->
        <div class="space-y-6">
            <!-- Customer Card -->
            <div class="bg-white rounded-xl border border-slate-200 shadow-xs p-6">
                <h3 class="text-sm font-semibold text-slate-900 uppercase tracking-wider mb-4">Customer Details</h3>
                <div class="space-y-3 text-sm">
                    <div>
                        <span class="text-xs text-slate-400 block">Display Name</span>
                        <span class="font-semibold text-slate-800">{{ $order->profile->display_name ?? 'Customer' }}</span>
                    </div>
                    <div>
                        <span class="text-xs text-slate-400 block">User UUID (Auth)</span>
                        <span class="font-mono text-xs text-slate-700 break-all">{{ $order->user_id }}</span>
                    </div>
                    <div>
                        <span class="text-xs text-slate-400 block">Role</span>
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-slate-100 text-slate-800">
                            {{ ucfirst($order->profile->role ?? 'customer') }}
                        </span>
                    </div>
                </div>

                <div class="mt-5 pt-4 border-t border-slate-100">
                    <a href="{{ route('admin.customers.show', $order->user_id) }}" class="text-xs font-semibold text-indigo-600 hover:text-indigo-900 flex items-center">
                        View Customer Purchase History &rarr;
                    </a>
                </div>
            </div>

            <!-- Storefront RPC Meta -->
            <div class="bg-slate-50 rounded-xl border border-slate-200 p-5 text-xs text-slate-600 space-y-2">
                <div class="font-semibold text-slate-800">Storefront RPC Integrity</div>
                <p>Created atomically via Supabase PostgreSQL <code class="font-mono bg-white px-1.5 py-0.5 rounded border border-slate-200">create_demo_order</code>.</p>
                <p>Monetary representation: <strong class="text-slate-800">Integer NTD</strong>.</p>
            </div>
        </div>

    </div>

</div>
@endsection
