@extends('admin.layouts.admin')

@section('page-title', 'AI Coach Reports')

@section('content')
<div class="space-y-6">

    {{-- Stat Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        @include('admin.components.stat-card', ['title' => 'Total Sessions', 'value' => number_format($totalSessions ?? 0), 'icon' => 'brain', 'color' => 'purple'])
        @include('admin.components.stat-card', ['title' => 'Total Tokens', 'value' => number_format($totalTokens ?? 0), 'icon' => 'coins', 'color' => 'orange'])
        @include('admin.components.stat-card', ['title' => 'Avg per User', 'value' => number_format($avgPerUser ?? 0), 'icon' => 'user', 'color' => 'blue'])
        @include('admin.components.stat-card', ['title' => 'Cost Estimate', 'value' => '$' . number_format($costEstimate ?? 0, 2), 'icon' => 'dollar-sign', 'color' => 'green'])
    </div>

    {{-- Charts --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        @include('admin.components.chart-card', ['title' => 'Sessions Trend', 'chartId' => 'aiSessionsTrendChart'])
        @include('admin.components.chart-card', ['title' => 'Token Usage Trend', 'chartId' => 'aiTokensTrendChart'])
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const sessionsTrend = @json($sessionsTrend ?? []);
    if (sessionsTrend.length > 0) {
        new ApexCharts(document.querySelector('#aiSessionsTrendChart'), {
            chart: { type: 'area', height: 300, toolbar: { show: false }, fontFamily: 'Plus Jakarta Sans' },
            series: [{ name: 'Sessions', data: sessionsTrend.map(i => i.count) }],
            xaxis: { categories: sessionsTrend.map(i => i.date), labels: { style: { fontSize: '11px', colors: '#9ca3af' } } },
            yaxis: { labels: { style: { fontSize: '11px', colors: '#9ca3af' } } },
            stroke: { curve: 'smooth', width: 2.5 },
            fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.4, opacityTo: 0.05, stops: [0, 100] } },
            colors: ['#9333ea'],
            grid: { borderColor: '#f3f4f6', strokeDashArray: 4 },
            dataLabels: { enabled: false }
        }).render();
    }

    const tokensTrend = @json($tokensTrend ?? []);
    if (tokensTrend.length > 0) {
        new ApexCharts(document.querySelector('#aiTokensTrendChart'), {
            chart: { type: 'bar', height: 300, toolbar: { show: false }, fontFamily: 'Plus Jakarta Sans' },
            series: [{ name: 'Tokens', data: tokensTrend.map(i => i.tokens) }],
            xaxis: { categories: tokensTrend.map(i => i.date), labels: { style: { fontSize: '11px', colors: '#9ca3af' } } },
            yaxis: { labels: { style: { fontSize: '11px', colors: '#9ca3af' } } },
            colors: ['#f97316'],
            plotOptions: { bar: { borderRadius: 6, columnWidth: '60%' } },
            grid: { borderColor: '#f3f4f6', strokeDashArray: 4 },
            dataLabels: { enabled: false }
        }).render();
    }
});
</script>
@endpush
