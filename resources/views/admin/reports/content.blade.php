@extends('admin.layouts.admin')

@section('page-title', 'Content Reports')

@section('content')
<div class="space-y-6">

    {{-- Stat Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        @include('admin.components.stat-card', ['title' => 'Total Content', 'value' => $totalContent ?? 0, 'icon' => 'file-text', 'color' => 'purple'])
        @include('admin.components.stat-card', ['title' => 'Published', 'value' => $published ?? 0, 'icon' => 'check-circle', 'color' => 'green'])
        @include('admin.components.stat-card', ['title' => 'Pending', 'value' => $pending ?? 0, 'icon' => 'clock', 'color' => 'orange'])
        @include('admin.components.stat-card', ['title' => 'Total Views', 'value' => number_format($totalViews ?? 0), 'icon' => 'eye', 'color' => 'blue'])
    </div>

    {{-- Charts --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        @include('admin.components.chart-card', ['title' => 'Content by Type', 'chartId' => 'contentByTypeChart'])
        @include('admin.components.chart-card', ['title' => 'Views Trend', 'chartId' => 'viewsTrendChart'])
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const contentByType = @json($contentByType ?? []);
    if (contentByType.length > 0) {
        new ApexCharts(document.querySelector('#contentByTypeChart'), {
            chart: { type: 'donut', height: 300, fontFamily: 'Plus Jakarta Sans' },
            series: contentByType.map(i => i.count),
            labels: contentByType.map(i => i.label),
            colors: ['#9333ea', '#f97316', '#3b82f6', '#10b981', '#ef4444'],
            legend: { position: 'bottom', fontSize: '12px' },
            plotOptions: { pie: { donut: { size: '65%' } } },
            dataLabels: { enabled: false }
        }).render();
    }

    const viewsTrend = @json($viewsTrend ?? []);
    if (viewsTrend.length > 0) {
        new ApexCharts(document.querySelector('#viewsTrendChart'), {
            chart: { type: 'area', height: 300, toolbar: { show: false }, fontFamily: 'Plus Jakarta Sans' },
            series: [{ name: 'Views', data: viewsTrend.map(i => i.count) }],
            xaxis: { categories: viewsTrend.map(i => i.date), labels: { style: { fontSize: '11px', colors: '#9ca3af' } } },
            yaxis: { labels: { style: { fontSize: '11px', colors: '#9ca3af' } } },
            stroke: { curve: 'smooth', width: 2.5 },
            fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.4, opacityTo: 0.05, stops: [0, 100] } },
            colors: ['#3b82f6'],
            grid: { borderColor: '#f3f4f6', strokeDashArray: 4 },
            dataLabels: { enabled: false }
        }).render();
    }
});
</script>
@endpush
