@extends('layouts.admin')

@section('content')
<div class="space-y-6">

    <!-- Header -->
    <div class="sm:flex sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Integration Telemetry & Logs</h1>
            <p class="mt-1 text-sm text-slate-500">Audit trail of order lifecycle events, domain mutations, and outbound webhook delivery attempts.</p>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs">
        <form method="GET" action="{{ route('admin.integration_logs.index') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-4">
            <div class="sm:col-span-2">
                <input type="text"
                       name="search"
                       value="{{ request('search') }}"
                       placeholder="Search by Reference UUID, Event, or Target..."
                       class="w-full px-3.5 py-2 bg-slate-50 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white transition" />
            </div>

            <div>
                <select name="status" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white transition">
                    <option value="">All Statuses</option>
                    <option value="success" {{ request('status') === 'success' ? 'selected' : '' }}>Success Only</option>
                    <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Failed Only</option>
                </select>
            </div>

            <div>
                <button type="submit" class="w-full py-2 bg-slate-800 hover:bg-slate-700 text-white text-sm font-medium rounded-lg transition">
                    Filter Logs
                </button>
            </div>
        </form>
    </div>

    <!-- Logs Table -->
    <div class="bg-white shadow-xs rounded-xl border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                <thead class="bg-slate-50 text-slate-500 font-medium text-xs uppercase tracking-wider">
                    <tr>
                        <th class="px-6 py-3.5">ID</th>
                        <th class="px-6 py-3.5">Event Type</th>
                        <th class="px-6 py-3.5">Reference</th>
                        <th class="px-6 py-3.5">Target</th>
                        <th class="px-6 py-3.5">Status</th>
                        <th class="px-6 py-3.5">Payload & Details</th>
                        <th class="px-6 py-3.5">Timestamp</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse ($logs as $log)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="px-6 py-4 font-mono text-xs text-slate-400">
                                #{{ $log->id }}
                            </td>
                            <td class="px-6 py-4">
                                <span class="font-mono text-xs font-semibold text-slate-800 bg-slate-100 px-2 py-0.5 rounded border border-slate-200">
                                    {{ $log->event_type }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-xs text-slate-600 font-medium">{{ ucfirst($log->reference_type) }}</div>
                                <div class="font-mono text-[11px] text-slate-400">#{{ substr($log->reference_id, 0, 8) }}...</div>
                            </td>
                            <td class="px-6 py-4 text-xs text-slate-600 font-mono">
                                {{ $log->target }}
                            </td>
                            <td class="px-6 py-4">
                                @if ($log->status === 'success')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-emerald-100 text-emerald-800">
                                        Success
                                    </span>
                                @elseif ($log->status === 'failed')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-red-100 text-red-800">
                                        Failed
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-slate-100 text-slate-700">
                                        {{ ucfirst($log->status) }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-xs">
                                @if ($log->error_message)
                                    <div class="text-red-600 font-medium mb-1">{{ $log->error_message }}</div>
                                @endif
                                @if ($log->payload)
                                    <details class="cursor-pointer">
                                        <summary class="text-indigo-600 hover:text-indigo-800 font-semibold select-none">View Payload JSON</summary>
                                        <pre class="mt-2 p-2 bg-slate-900 text-slate-200 rounded text-[10px] font-mono overflow-x-auto max-w-xs">{{ json_encode($log->payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                                    </details>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-xs text-slate-500 font-mono">
                                {{ $log->created_at->format('Y-m-d H:i:s') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-8 text-center text-slate-400">
                                No telemetry logs recorded yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($logs->hasPages())
            <div class="px-6 py-4 border-t border-slate-200 bg-slate-50">
                {{ $logs->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
