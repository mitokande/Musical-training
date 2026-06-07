@extends('admin.layouts.admin')

@section('page-title', 'AI Coach Overview')

@section('content')
<div class="space-y-6">

    {{-- Stat Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        @include('admin.components.stat-card', ['title' => 'Total Sessions', 'value' => $totalSessions, 'icon' => 'brain', 'color' => 'purple'])
        @include('admin.components.stat-card', ['title' => 'Total Tokens', 'value' => number_format($totalTokens), 'icon' => 'coins', 'color' => 'orange'])
        @include('admin.components.stat-card', ['title' => 'Active Users', 'value' => $activeUsers, 'icon' => 'users', 'color' => 'green'])
        @include('admin.components.stat-card', ['title' => 'Avg Tokens/Session', 'value' => number_format($avgTokens), 'icon' => 'bar-chart-3', 'color' => 'blue'])
    </div>

    {{-- Charts --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        @include('admin.components.chart-card', ['title' => 'Sessions Trend', 'chartId' => 'sessionsTrendChart'])
        @include('admin.components.chart-card', ['title' => 'Token Usage', 'chartId' => 'tokensTrendChart'])
    </div>

    {{-- Recent Sessions Table --}}
    <div class="card p-6">
        <div class="flex items-center gap-2 mb-6">
            <i data-lucide="history" class="w-5 h-5 text-purple-600"></i>
            <h2 class="text-lg font-semibold text-gray-900">Recent Sessions</h2>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-200">
                        <th class="text-left py-3 px-4 font-semibold text-gray-600">User</th>
                        <th class="text-left py-3 px-4 font-semibold text-gray-600">Date</th>
                        <th class="text-left py-3 px-4 font-semibold text-gray-600">Model</th>
                        <th class="text-right py-3 px-4 font-semibold text-gray-600">Tokens</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($recentSessions ?? [] as $session)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="py-3 px-4">
                            <div class="flex items-center gap-2">
                                <div class="w-7 h-7 rounded-full bg-purple-100 flex items-center justify-center text-xs font-semibold text-purple-700">
                                    {{ substr($session->user->name ?? 'U', 0, 1) }}
                                </div>
                                <span class="font-medium text-gray-900">{{ $session->user->name ?? 'Unknown' }}</span>
                            </div>
                        </td>
                        <td class="py-3 px-4 text-gray-600">{{ $session->created_at->format('M d, Y H:i') }}</td>
                        <td class="py-3 px-4">
                            <span class="px-2 py-1 text-xs font-medium rounded-full bg-purple-100 text-purple-700">{{ $session->model ?? 'N/A' }}</span>
                        </td>
                        <td class="py-3 px-4 text-right font-medium text-gray-900">{{ number_format($session->total_tokens ?? 0) }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="py-8 text-center text-gray-500">No sessions found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const sessionsTrend = @json($sessionsTrend ?? []);
    if (sessionsTrend.length > 0) {
        new ApexCharts(document.querySelector('#sessionsTrendChart'), {
            chart: { type: 'area', height: 300, toolbar: { show: false }, fontFamily: 'Plus Jakarta Sans' },
            series: [{ name: 'Sessions', data: sessionsTrend.map(i => i.count) }],
            xaxis: { categories: sessionsTrend.map(i => i.date), labels: { style: { fontSize: '11px', colors: '#9ca3af' } } },
            yaxis: { labels: { style: { fontSize: '11px', colors: '#9ca3af' } } },
            stroke: { curve: 'smooth', width: 2.5 },
            fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.4, opacityTo: 0.05, stops: [0, 100] } },
            colors: ['#9333ea'],
            grid: { borderColor: '#f3f4f6', strokeDashArray: 4 },
            tooltip: { theme: 'light' },
            dataLabels: { enabled: false }
        }).render();
    }

    const tokensTrend = @json($tokensTrend ?? []);
    if (tokensTrend.length > 0) {
        new ApexCharts(document.querySelector('#tokensTrendChart'), {
            chart: { type: 'bar', height: 300, toolbar: { show: false }, fontFamily: 'Plus Jakarta Sans' },
            series: [{ name: 'Tokens', data: tokensTrend.map(i => i.tokens) }],
            xaxis: { categories: tokensTrend.map(i => i.date), labels: { style: { fontSize: '11px', colors: '#9ca3af' } } },
            yaxis: { labels: { style: { fontSize: '11px', colors: '#9ca3af' } } },
            colors: ['#f97316'],
            plotOptions: { bar: { borderRadius: 6, columnWidth: '60%' } },
            grid: { borderColor: '#f3f4f6', strokeDashArray: 4 },
            tooltip: { theme: 'light' },
            dataLabels: { enabled: false }
        }).render();
    }
});
</script>
@endpush
