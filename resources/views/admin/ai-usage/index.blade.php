@extends('admin.layouts.admin')

@section('page-title', 'AI Usage')

@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <h2 class="text-2xl font-bold text-gray-800">AI Usage</h2>
        <a href="{{ route('admin.ai-usage.export', request()->query()) }}" class="inline-flex items-center gap-2 px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition text-sm">
            <i data-lucide="download" class="w-4 h-4"></i> Export CSV
        </a>
    </div>

    {{-- Filters --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
        <form method="GET" action="{{ route('admin.ai-usage.index') }}" class="flex flex-wrap items-end gap-4">
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">From</label>
                <input type="date" name="from" value="{{ $from->format('Y-m-d') }}"
                    class="rounded-lg border-gray-300 focus:border-purple-500 focus:ring-purple-500 text-sm">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">To</label>
                <input type="date" name="to" value="{{ $to->format('Y-m-d') }}"
                    class="rounded-lg border-gray-300 focus:border-purple-500 focus:ring-purple-500 text-sm">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Request Type</label>
                <select name="feature" class="rounded-lg border-gray-300 focus:border-purple-500 focus:ring-purple-500 text-sm">
                    <option value="">All Types</option>
                    @foreach($features as $feature)
                        <option value="{{ $feature }}" {{ request('feature') === $feature ? 'selected' : '' }}>{{ str($feature)->headline() }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Model</label>
                <select name="model" class="rounded-lg border-gray-300 focus:border-purple-500 focus:ring-purple-500 text-sm">
                    <option value="">All Models</option>
                    @foreach($models as $model)
                        <option value="{{ $model }}" {{ request('model') === $model ? 'selected' : '' }}>{{ $model }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Status</label>
                <select name="status" class="rounded-lg border-gray-300 focus:border-purple-500 focus:ring-purple-500 text-sm">
                    <option value="">All</option>
                    <option value="success" {{ request('status') === 'success' ? 'selected' : '' }}>Success</option>
                    <option value="error" {{ request('status') === 'error' ? 'selected' : '' }}>Error</option>
                </select>
            </div>
            <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition text-sm">
                <i data-lucide="filter" class="w-4 h-4"></i> Filter
            </button>
            <a href="{{ route('admin.ai-usage.index') }}" class="px-4 py-2 text-gray-500 hover:text-gray-700 transition text-sm">Reset</a>
        </form>
    </div>

    {{-- Stat Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
        @include('admin.components.stat-card', ['title' => 'Requests', 'value' => number_format($totalRequests), 'icon' => 'activity', 'color' => 'purple'])
        @include('admin.components.stat-card', ['title' => 'Tokens', 'value' => number_format($totalTokens), 'icon' => 'coins', 'color' => 'orange'])
        @include('admin.components.stat-card', ['title' => 'Estimated Cost', 'value' => '$' . number_format($totalCost, 4), 'icon' => 'dollar-sign', 'color' => 'green'])
        @include('admin.components.stat-card', ['title' => 'Active Users', 'value' => number_format($activeUsers), 'icon' => 'users', 'color' => 'blue'])
        @include('admin.components.stat-card', ['title' => 'Error Rate', 'value' => $errorRate . '%', 'icon' => 'alert-triangle', 'color' => $errorRate > 5 ? 'red' : 'teal'])
    </div>

    {{-- Charts --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        @include('admin.components.chart-card', ['title' => 'Requests & Cost Trend', 'chartId' => 'aiUsageTrendChart'])
        @include('admin.components.chart-card', ['title' => 'Requests by Type', 'chartId' => 'aiUsageFeatureChart'])
    </div>
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        @include('admin.components.chart-card', ['title' => 'Requests by Model', 'chartId' => 'aiUsageModelChart'])

        {{-- Top Users --}}
        <div class="card p-5">
            <h3 class="text-sm font-semibold text-gray-700 mb-4">Top Users by Cost</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-xs font-semibold text-gray-500 uppercase border-b border-gray-100">
                            <th class="py-2 pr-2">User</th>
                            <th class="py-2 pr-2 text-right">Requests</th>
                            <th class="py-2 pr-2 text-right">Tokens</th>
                            <th class="py-2 text-right">Cost</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($topUsers as $row)
                            <tr>
                                <td class="py-2 pr-2 text-gray-800">{{ $row->user?->name ?? 'Unknown' }}</td>
                                <td class="py-2 pr-2 text-right text-gray-600">{{ number_format($row->requests) }}</td>
                                <td class="py-2 pr-2 text-right text-gray-600">{{ number_format($row->tokens) }}</td>
                                <td class="py-2 text-right text-gray-800 font-medium">${{ number_format($row->cost, 4) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="py-6 text-center text-gray-400">No data for this period.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Recent Requests --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Date</th>
                        <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase">User</th>
                        <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Type</th>
                        <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Model</th>
                        <th class="text-right px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Input Tokens</th>
                        <th class="text-right px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Output Tokens</th>
                        <th class="text-right px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Total</th>
                        <th class="text-right px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Cost</th>
                        <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($logs as $log)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4 text-gray-500">{{ $log->created_at?->format('M d, Y H:i') }}</td>
                            <td class="px-6 py-4">
                                <div class="text-gray-800">{{ $log->user?->name ?? 'N/A' }}</div>
                                <div class="text-xs text-gray-400">{{ $log->user?->email ?? '' }}</div>
                            </td>
                            <td class="px-6 py-4 text-gray-600">{{ str($log->feature)->headline() }}</td>
                            <td class="px-6 py-4 text-gray-600">{{ $log->model }}</td>
                            <td class="px-6 py-4 text-right text-gray-600">{{ $log->prompt_tokens !== null ? number_format($log->prompt_tokens) : '-' }}</td>
                            <td class="px-6 py-4 text-right text-gray-600">{{ $log->completion_tokens !== null ? number_format($log->completion_tokens) : '-' }}</td>
                            <td class="px-6 py-4 text-right text-gray-500">{{ $log->total_tokens !== null ? number_format($log->total_tokens) : '-' }}</td>
                            <td class="px-6 py-4 text-right text-gray-800 font-medium">{{ $log->cost_usd !== null ? '$' . number_format($log->cost_usd, 4) : '-' }}</td>
                            <td class="px-6 py-4">
                                @if($log->status === 'success')
                                    <span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-700">Success</span>
                                @else
                                    <span class="px-2 py-1 text-xs font-medium rounded-full bg-red-100 text-red-700" title="{{ $log->error_message }}">Error</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center text-gray-400">
                                <i data-lucide="cpu" class="w-10 h-10 mx-auto mb-2 opacity-50"></i>
                                <p>No AI usage recorded for this period.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($logs->hasPages())
            <div class="px-6 py-4 border-t border-gray-100">
                {{ $logs->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const usageTrend = @json($usageTrend ?? []);
    if (usageTrend.length > 0) {
        new ApexCharts(document.querySelector('#aiUsageTrendChart'), {
            chart: { type: 'line', height: 300, toolbar: { show: false }, fontFamily: 'Plus Jakarta Sans' },
            series: [
                { name: 'Requests', type: 'column', data: usageTrend.map(i => i.requests) },
                { name: 'Cost ($)', type: 'line', data: usageTrend.map(i => parseFloat(i.cost || 0)) },
            ],
            xaxis: { categories: usageTrend.map(i => i.date), labels: { style: { fontSize: '11px', colors: '#9ca3af' } } },
            yaxis: [
                { title: { text: 'Requests' }, labels: { style: { fontSize: '11px', colors: '#9ca3af' } } },
                { opposite: true, title: { text: 'Cost ($)' }, labels: { style: { fontSize: '11px', colors: '#9ca3af' } } },
            ],
            stroke: { curve: 'smooth', width: [0, 2.5] },
            plotOptions: { bar: { borderRadius: 4, columnWidth: '50%' } },
            colors: ['#9333ea', '#f97316'],
            grid: { borderColor: '#f3f4f6', strokeDashArray: 4 },
            dataLabels: { enabled: false }
        }).render();
    }

    const byFeature = @json($byFeature ?? []);
    if (byFeature.length > 0) {
        new ApexCharts(document.querySelector('#aiUsageFeatureChart'), {
            chart: { type: 'donut', height: 300, fontFamily: 'Plus Jakarta Sans' },
            series: byFeature.map(i => i.requests),
            labels: byFeature.map(i => i.feature),
            colors: ['#9333ea', '#f97316', '#3b82f6', '#10b981', '#ec4899', '#14b8a6', '#f59e0b', '#6366f1'],
            legend: { position: 'bottom', fontSize: '12px' },
            dataLabels: { enabled: false }
        }).render();
    }

    const byModel = @json($byModel ?? []);
    if (byModel.length > 0) {
        new ApexCharts(document.querySelector('#aiUsageModelChart'), {
            chart: { type: 'bar', height: 300, toolbar: { show: false }, fontFamily: 'Plus Jakarta Sans' },
            series: [{ name: 'Requests', data: byModel.map(i => i.requests) }],
            xaxis: { categories: byModel.map(i => i.model), labels: { style: { fontSize: '11px', colors: '#9ca3af' } } },
            yaxis: { labels: { style: { fontSize: '11px', colors: '#9ca3af' } } },
            colors: ['#3b82f6'],
            plotOptions: { bar: { borderRadius: 6, columnWidth: '50%' } },
            grid: { borderColor: '#f3f4f6', strokeDashArray: 4 },
            dataLabels: { enabled: false }
        }).render();
    }
});
</script>
@endpush
