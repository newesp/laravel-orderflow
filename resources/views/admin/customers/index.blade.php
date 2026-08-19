@extends('layouts.admin')

@section('content')
<div class="space-y-6">

    <!-- Header -->
    <div class="sm:flex sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Customer Directory</h1>
            <p class="mt-1 text-sm text-slate-500">Read-only presentation model aggregated from Supabase Auth and Profiles.</p>
        </div>
    </div>

    <!-- Search & Filters -->
    <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs">
        <form method="GET" action="{{ route('admin.customers.index') }}" class="flex space-x-3">
            <div class="flex-1 relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <input type="text"
                       name="search"
                       id="search"
                       value="{{ request('search') }}"
                       placeholder="Search customers by name, email, or User UUID..."
                       class="w-full pl-9 pr-4 py-2 bg-slate-50 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white transition" />
            </div>

            <button type="submit" class="px-5 py-2 bg-slate-800 hover:bg-slate-700 text-white text-sm font-medium rounded-lg transition">
                Search
            </button>
        </form>
    </div>

    <!-- Customers Table -->
    <div class="bg-white shadow-xs rounded-xl border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                <thead class="bg-slate-50 text-slate-500 font-medium text-xs uppercase tracking-wider">
                    <tr>
                        <th class="px-6 py-3.5">Customer Name</th>
                        <th class="px-6 py-3.5">Email</th>
                        <th class="px-6 py-3.5">Role</th>
                        <th class="px-6 py-3.5 text-center">Orders</th>
                        <th class="px-6 py-3.5 text-right">Lifetime Spend</th>
                        <th class="px-6 py-3.5">Member Since</th>
                        <th class="px-6 py-3.5 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse ($customers as $customer)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="px-6 py-4">
                                <div class="font-semibold text-slate-900">{{ $customer->display_name ?? 'Anonymous User' }}</div>
                                <div class="text-xs text-slate-400 font-mono">{{ substr($customer->id, 0, 8) }}...</div>
                            </td>
                            <td class="px-6 py-4 text-slate-600 font-mono text-xs">
                                {{ $customer->email }}
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold {{ $customer->role === 'admin' ? 'bg-indigo-100 text-indigo-800' : 'bg-slate-100 text-slate-700' }}">
                                    {{ ucfirst($customer->role) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center font-semibold text-slate-800">
                                {{ $customer->orders_count }}
                            </td>
                            <td class="px-6 py-4 text-right font-bold text-slate-900">
                                {{ $customer->formatted_total_spent }}
                            </td>
                            <td class="px-6 py-4 text-xs text-slate-500">
                                {{ $customer->created_at ? $customer->created_at->format('Y-m-d') : '-' }}
                            </td>
                            <td class="px-6 py-4 text-right">
                                <a href="{{ route('admin.customers.show', $customer->id) }}" class="text-xs font-semibold text-indigo-600 hover:text-indigo-900">
                                    View History &rarr;
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-8 text-center text-slate-400">
                                No customers found matching search criteria.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($customers->hasPages())
            <div class="px-6 py-4 border-t border-slate-200 bg-slate-50">
                {{ $customers->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
