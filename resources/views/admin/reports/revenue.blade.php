@extends('admin.layouts.admin')

@section('page-title', 'Revenue Reports')

@section('content')
<div class="space-y-6">

    {{-- Date Range & Export --}}
    <div class="card p-4">
        <form method="GET" action="{{ route('admin.reports.revenue') }}" class="flex flex-wrap items-end gap-4">
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Start Date</label>
                <input type="date" name="start_date" value="{{ request('start_date', now()->subDays(30)->format('Y-m-d')) }}"
                       class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">End Date</label>
                <input type="date" name="end_date" value="{{ request('end_date', now()->format('Y-m-d')) }}"
                       class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
            </div>
            <button type="submit" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-sm font-medium text-gray-700 rounded-lg transition-colors">
                <i data-lucide="search" class="w-4 h-4 inline mr-1"></i> Apply
            </button>
            <a href="{{ route('admin.reports.export', ['type' => 'revenue']) }}" class="ml-auto px-4 py-2 bg-green-50 hover:bg-green-100 text-sm font-medium text-green-700 rounded-lg transition-colors">
                <i data-lucide="download" class="w-4 h-4 inline mr-1"></i> Export
            </a>
        </form>
    </div>

    {{-- Stat Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        @include('admin.components.stat-card', ['title' => 'Total Revenue', 'value' => '$' . number_format($totalRevenue ?? 0, 2), 'icon' => 'dollar-sign', 'color' => 'green'])
        @include('admin.components.stat-card', ['title' => 'This Month', 'value' => '$' . number_format($thisMonth ?? 0, 2), 'icon' => 'calendar', 'color' => 'purple'])
        @include('admin.components.stat-card', ['title' => 'Avg Per User', 'value' => '$' . number_format($avgPerUser ?? 0, 2), 'icon' => 'user', 'color' => 'blue'])
        @include('admin.components.stat-card', ['title' => 'Refunds', 'value' => '$' . number_format($refunds ?? 0, 2), 'icon' => 'rotate-ccw', 'color' => 'red'])
    </div>

    {{-- Charts --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        @include('admin.components.chart-card', ['title' => 'Monthly Revenue', 'chartId' => 'monthlyRevenueChart'])
        @include('admin.components.chart-card', ['title' => 'Revenue by Plan', 'chartId' => 'revenueByPlanChart'])
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const monthlyRevenue = @json($monthlyRevenue ?? []);
    if (monthlyRevenue.length > 0) {
        new ApexCharts(document.querySelector('#monthlyRevenueChart'), {
            chart: { type: 'bar', height: 300, toolbar: { show: false }, fontFamily: 'Plus Jakarta Sans' },
            series: [{ name: 'Revenue', data: monthlyRevenue.map(i => i.amount) }],
            xaxis: { categories: monthlyRevenue.map(i => i.month), labels: { style: { fontSize: '11px', colors: '#9ca3af' } } },
            yaxis: { labels: { style: { fontSize: '11px', colors: '#9ca3af' }, formatter: v => '$' + v.toFixed(0) } },
            colors: ['#10b981'],
            plotOptions: { bar: { borderRadius: 6, columnWidth: '55%' } },
            grid: { borderColor: '#f3f4f6', strokeDashArray: 4 },
            dataLabels: { enabled: false }
        }).render();
    }

    const revenueByPlan = @json($revenueByPlan ?? []);
    if (revenueByPlan.length > 0) {
        new ApexCharts(document.querySelector('#revenueByPlanChart'), {
            chart: { type: 'line', height: 300, toolbar: { show: false }, fontFamily: 'Plus Jakarta Sans' },
            series: revenueByPlan,
            xaxis: { categories: @json($revenueMonths ?? []), labels: { style: { fontSize: '11px', colors: '#9ca3af' } } },
            yaxis: { labels: { style: { fontSize: '11px', colors: '#9ca3af' }, formatter: v => '$' + v.toFixed(0) } },
            stroke: { curve: 'smooth', width: 2.5 },
            colors: ['#9333ea', '#f97316', '#3b82f6'],
            grid: { borderColor: '#f3f4f6', strokeDashArray: 4 },
            legend: { position: 'top' },
            dataLabels: { enabled: false }
        }).render();
    }
});
</script>
@endpush
