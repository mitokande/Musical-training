@extends('admin.layouts.admin')

@section('page-title', 'Subscription Reports')

@section('content')
<div class="space-y-6">

    {{-- Export --}}
    <div class="flex justify-end">
        <a href="{{ route('admin.reports.export', ['type' => 'subscriptions']) }}" class="px-4 py-2 bg-green-50 hover:bg-green-100 text-sm font-medium text-green-700 rounded-lg transition-colors">
            <i data-lucide="download" class="w-4 h-4 inline mr-1"></i> Export
        </a>
    </div>

    {{-- Stat Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        @include('admin.components.stat-card', ['title' => 'Active', 'value' => $active ?? 0, 'icon' => 'check-circle', 'color' => 'green'])
        @include('admin.components.stat-card', ['title' => 'Cancelled', 'value' => $cancelled ?? 0, 'icon' => 'x-circle', 'color' => 'red'])
        @include('admin.components.stat-card', ['title' => 'Conversion Rate', 'value' => ($conversionRate ?? 0) . '%', 'icon' => 'trending-up', 'color' => 'purple'])
        @include('admin.components.stat-card', ['title' => 'Avg Duration', 'value' => ($avgDuration ?? 0) . ' days', 'icon' => 'clock', 'color' => 'orange'])
    </div>

    {{-- Charts --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        @include('admin.components.chart-card', ['title' => 'Subscription Trend', 'chartId' => 'subscriptionTrendChart'])
        @include('admin.components.chart-card', ['title' => 'Status Distribution', 'chartId' => 'statusDistributionChart'])
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const subTrend = @json($subscriptionTrend ?? []);
    if (subTrend.length > 0) {
        new ApexCharts(document.querySelector('#subscriptionTrendChart'), {
            chart: { type: 'area', height: 300, toolbar: { show: false }, fontFamily: 'Plus Jakarta Sans' },
            series: [{ name: 'Subscriptions', data: subTrend.map(i => i.count) }],
            xaxis: { categories: subTrend.map(i => i.date), labels: { style: { fontSize: '11px', colors: '#9ca3af' } } },
            yaxis: { labels: { style: { fontSize: '11px', colors: '#9ca3af' } } },
            stroke: { curve: 'smooth', width: 2.5 },
            fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.4, opacityTo: 0.05, stops: [0, 100] } },
            colors: ['#9333ea'],
            grid: { borderColor: '#f3f4f6', strokeDashArray: 4 },
            dataLabels: { enabled: false }
        }).render();
    }

    const statusDist = @json($statusDistribution ?? []);
    if (statusDist.length > 0) {
        new ApexCharts(document.querySelector('#statusDistributionChart'), {
            chart: { type: 'donut', height: 300, fontFamily: 'Plus Jakarta Sans' },
            series: statusDist.map(i => i.count),
            labels: statusDist.map(i => i.label),
            colors: ['#10b981', '#ef4444', '#f97316', '#6b7280'],
            legend: { position: 'bottom', fontSize: '12px' },
            plotOptions: { pie: { donut: { size: '65%' } } },
            dataLabels: { enabled: false }
        }).render();
    }
});
</script>
@endpush
