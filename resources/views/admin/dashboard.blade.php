@extends('admin.layouts.admin')

@section('page-title', 'Dashboard')

@section('content')
<div class="space-y-8">
    <!-- Hero Welcome Section -->
    <div class="hero-gradient rounded-2xl p-8 relative overflow-hidden">
        <div class="absolute top-0 right-0 w-64 h-64 bg-white/10 rounded-full -translate-y-1/2 translate-x-1/2"></div>
        <div class="absolute bottom-0 left-0 w-48 h-48 bg-white/10 rounded-full translate-y-1/2 -translate-x-1/2"></div>
        <div class="relative z-10">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-lg bg-white/20 flex items-center justify-center">
                    <i data-lucide="layout-dashboard" class="w-6 h-6 text-white"></i>
                </div>
                <span class="px-3 py-1 bg-purple-500 text-white text-xs font-semibold rounded-full">Admin Panel</span>
            </div>
            <h1 class="text-3xl sm:text-4xl font-bold text-white mb-2">
                Welcome back, {{ Auth::user()->name ?? 'Admin' }}
            </h1>
            <p class="text-white/80">Here's what's happening with Harmoniva today.</p>
        </div>
    </div>

    <!-- Stat Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4">
        <x-admin.stat-card
            title="Total Members"
            :value="$stats['user_count']"
            icon="users"
            color="purple"
            :link="route('admin.users.index')"
        />
        <x-admin.stat-card
            title="Premium"
            :value="$stats['premium_count']"
            icon="crown"
            color="orange"
            :link="route('admin.users.index', ['plan' => 'premium'])"
        />
        <x-admin.stat-card
            title="Active Today"
            :value="$stats['active_users']"
            icon="activity"
            color="green"
        />
        <x-admin.stat-card
            title="Exercises Today"
            :value="$stats['exercise_today']"
            icon="headphones"
            color="blue"
        />
        <x-admin.stat-card
            title="Pending Content"
            :value="$stats['pending_articles']"
            icon="clock"
            color="red"
            :link="route('admin.content.index', ['status' => 'pending'])"
        />
        <x-admin.stat-card
            title="Unread Messages"
            :value="$stats['unread_messages']"
            icon="mail"
            color="indigo"
            :link="route('admin.messages.index')"
        />
    </div>

    <!-- Charts Row -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <x-admin.chart-card title="Member Registration Trend" chartId="registrationChart" />
        <x-admin.chart-card title="Plan Distribution" chartId="planChart" />
    </div>

    <!-- Recent Activity -->
    <div class="card p-6">
        <div class="flex items-center gap-2 mb-6">
            <i data-lucide="activity" class="w-5 h-5 text-purple-600"></i>
            <h2 class="text-lg font-semibold text-gray-900">Recent Activity</h2>
        </div>

        @if(!empty($recentActivities) && count($recentActivities) > 0)
        <div class="space-y-3">
            @foreach($recentActivities as $activity)
            <div class="flex items-start gap-3 p-3 rounded-lg hover:bg-gray-50 transition-colors">
                <div class="w-8 h-8 rounded-full bg-purple-100 flex items-center justify-center shrink-0 mt-0.5">
                    <i data-lucide="file-text" class="w-4 h-4 text-purple-600"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm text-gray-900">{{ $activity->description }}</p>
                    <p class="text-xs text-gray-500 mt-0.5">
                        by {{ $activity->causer?->name ?? 'System' }}
                        &middot;
                        {{ $activity->created_at->diffForHumans() }}
                    </p>
                </div>
            </div>
            @endforeach
        </div>
        @else
        <div class="text-center py-8">
            <div class="w-12 h-12 rounded-full bg-gray-100 flex items-center justify-center mx-auto mb-3">
                <i data-lucide="inbox" class="w-6 h-6 text-gray-400"></i>
            </div>
            <p class="text-sm text-gray-500">No recent activity</p>
        </div>
        @endif
    </div>

    <!-- Quick Actions -->
    <div class="card p-6">
        <div class="flex items-center gap-2 mb-6">
            <i data-lucide="zap" class="w-5 h-5 text-purple-600"></i>
            <h2 class="text-lg font-semibold text-gray-900">Quick Actions</h2>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <a href="{{ route('admin.users.create') }}"
               class="flex items-center gap-4 p-4 rounded-xl border border-gray-200 hover:border-purple-300 hover:bg-purple-50 transition-all group">
                <div class="w-12 h-12 rounded-xl bg-purple-100 flex items-center justify-center group-hover:bg-purple-200 transition-colors">
                    <i data-lucide="user-plus" class="w-6 h-6 text-purple-600"></i>
                </div>
                <div>
                    <p class="font-semibold text-gray-900 group-hover:text-purple-700">Add Member</p>
                    <p class="text-sm text-gray-500">Create new user</p>
                </div>
            </a>

            <a href="{{ route('admin.content.create') }}"
               class="flex items-center gap-4 p-4 rounded-xl border border-gray-200 hover:border-blue-300 hover:bg-blue-50 transition-all group">
                <div class="w-12 h-12 rounded-xl bg-blue-100 flex items-center justify-center group-hover:bg-blue-200 transition-colors">
                    <i data-lucide="file-plus" class="w-6 h-6 text-blue-600"></i>
                </div>
                <div>
                    <p class="font-semibold text-gray-900 group-hover:text-blue-700">Add Content</p>
                    <p class="text-sm text-gray-500">Create new article</p>
                </div>
            </a>

            <a href="{{ route('admin.reports.members') }}"
               class="flex items-center gap-4 p-4 rounded-xl border border-gray-200 hover:border-orange-300 hover:bg-orange-50 transition-all group">
                <div class="w-12 h-12 rounded-xl bg-orange-100 flex items-center justify-center group-hover:bg-orange-200 transition-colors">
                    <i data-lucide="bar-chart-3" class="w-6 h-6 text-orange-600"></i>
                </div>
                <div>
                    <p class="font-semibold text-gray-900 group-hover:text-orange-700">View Reports</p>
                    <p class="text-sm text-gray-500">Analytics & insights</p>
                </div>
            </a>

            <a href="{{ route('admin.settings.index') }}"
               class="flex items-center gap-4 p-4 rounded-xl border border-gray-200 hover:border-green-300 hover:bg-green-50 transition-all group">
                <div class="w-12 h-12 rounded-xl bg-green-100 flex items-center justify-center group-hover:bg-green-200 transition-colors">
                    <i data-lucide="settings" class="w-6 h-6 text-green-600"></i>
                </div>
                <div>
                    <p class="font-semibold text-gray-900 group-hover:text-green-700">System Settings</p>
                    <p class="text-sm text-gray-500">Configure platform</p>
                </div>
            </a>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Registration Trend Line Chart
    const regData = @json($registrationTrend ?? []);
    if (regData.length > 0) {
        new ApexCharts(document.querySelector('#registrationChart'), {
            chart: { type: 'area', height: 300, toolbar: { show: false }, fontFamily: 'Plus Jakarta Sans' },
            series: [{ name: 'New Members', data: regData.map(item => item.count) }],
            xaxis: { categories: regData.map(item => item.date), labels: { style: { fontSize: '11px', colors: '#9ca3af' } } },
            yaxis: { labels: { style: { fontSize: '11px', colors: '#9ca3af' } } },
            stroke: { curve: 'smooth', width: 2.5 },
            fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.4, opacityTo: 0.05, stops: [0, 100] } },
            colors: ['#9333ea'],
            grid: { borderColor: '#f3f4f6', strokeDashArray: 4 },
            tooltip: { theme: 'light' },
            dataLabels: { enabled: false }
        }).render();
    }

    // Plan Distribution Donut Chart
    const planData = @json($planDistribution ?? []);
    if (planData.length > 0) {
        new ApexCharts(document.querySelector('#planChart'), {
            chart: { type: 'donut', height: 300, fontFamily: 'Plus Jakarta Sans' },
            series: planData.map(item => item.count),
            labels: planData.map(item => item.label),
            colors: ['#9333ea', '#f97316', '#3b82f6', '#10b981', '#ef4444'],
            legend: { position: 'bottom', fontSize: '12px' },
            plotOptions: { pie: { donut: { size: '65%', labels: { show: true, total: { show: true, label: 'Total', fontSize: '14px', fontWeight: 600 } } } } },
            dataLabels: { enabled: false },
            tooltip: { theme: 'light' }
        }).render();
    }
});
</script>
@endpush
