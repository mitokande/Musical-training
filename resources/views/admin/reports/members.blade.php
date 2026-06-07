@extends('admin.layouts.admin')

@section('page-title', 'Member Reports')

@section('content')
<div class="space-y-6">

    {{-- Date Range & Export --}}
    <div class="card p-4">
        <form method="GET" action="{{ route('admin.reports.members') }}" class="flex flex-wrap items-end gap-4">
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
            <a href="{{ route('admin.reports.export', ['type' => 'members']) }}" class="ml-auto px-4 py-2 bg-green-50 hover:bg-green-100 text-sm font-medium text-green-700 rounded-lg transition-colors">
                <i data-lucide="download" class="w-4 h-4 inline mr-1"></i> Export
            </a>
        </form>
    </div>

    {{-- Stat Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        @include('admin.components.stat-card', ['title' => 'Total Members', 'value' => $totalMembers ?? 0, 'icon' => 'users', 'color' => 'purple'])
        @include('admin.components.stat-card', ['title' => 'New This Month', 'value' => $newThisMonth ?? 0, 'icon' => 'user-plus', 'color' => 'green'])
        @include('admin.components.stat-card', ['title' => 'Active Rate', 'value' => ($activeRate ?? 0) . '%', 'icon' => 'activity', 'color' => 'blue'])
        @include('admin.components.stat-card', ['title' => 'Churn Rate', 'value' => ($churnRate ?? 0) . '%', 'icon' => 'trending-down', 'color' => 'red'])
    </div>

    {{-- Charts --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        @include('admin.components.chart-card', ['title' => 'Registration Trend', 'chartId' => 'registrationTrendChart'])
        @include('admin.components.chart-card', ['title' => 'Role Distribution', 'chartId' => 'roleDistributionChart'])
    </div>

    <div class="grid grid-cols-1 gap-6">
        @include('admin.components.chart-card', ['title' => 'Plan Distribution', 'chartId' => 'planDistributionChart'])
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const regTrend = @json($registrationTrend ?? []);
    if (regTrend.length > 0) {
        new ApexCharts(document.querySelector('#registrationTrendChart'), {
            chart: { type: 'area', height: 300, toolbar: { show: false }, fontFamily: 'Plus Jakarta Sans' },
            series: [{ name: 'Registrations', data: regTrend.map(i => i.count) }],
            xaxis: { categories: regTrend.map(i => i.date), labels: { style: { fontSize: '11px', colors: '#9ca3af' } } },
            yaxis: { labels: { style: { fontSize: '11px', colors: '#9ca3af' } } },
            stroke: { curve: 'smooth', width: 2.5 },
            fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.4, opacityTo: 0.05, stops: [0, 100] } },
            colors: ['#9333ea'],
            grid: { borderColor: '#f3f4f6', strokeDashArray: 4 },
            dataLabels: { enabled: false }
        }).render();
    }

    const roleData = @json($roleDistribution ?? []);
    if (roleData.length > 0) {
        new ApexCharts(document.querySelector('#roleDistributionChart'), {
            chart: { type: 'donut', height: 300, fontFamily: 'Plus Jakarta Sans' },
            series: roleData.map(i => i.count),
            labels: roleData.map(i => i.label),
            colors: ['#9333ea', '#f97316', '#3b82f6', '#10b981'],
            legend: { position: 'bottom', fontSize: '12px' },
            plotOptions: { pie: { donut: { size: '65%' } } },
            dataLabels: { enabled: false }
        }).render();
    }

    const planData = @json($planDistribution ?? []);
    if (planData.length > 0) {
        new ApexCharts(document.querySelector('#planDistributionChart'), {
            chart: { type: 'bar', height: 300, toolbar: { show: false }, fontFamily: 'Plus Jakarta Sans' },
            series: [{ name: 'Users', data: planData.map(i => i.count) }],
            xaxis: { categories: planData.map(i => i.label), labels: { style: { fontSize: '11px', colors: '#9ca3af' } } },
            yaxis: { labels: { style: { fontSize: '11px', colors: '#9ca3af' } } },
            colors: ['#9333ea'],
            plotOptions: { bar: { borderRadius: 6, columnWidth: '50%' } },
            grid: { borderColor: '#f3f4f6', strokeDashArray: 4 },
            dataLabels: { enabled: false }
        }).render();
    }
});
</script>
@endpush
