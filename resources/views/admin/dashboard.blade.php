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

    <!-- Engagement Row -->
    <div class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-6 gap-4">
        <x-admin.stat-card title="Active Today (DAU)" :value="$stats['dau']" icon="sun" color="green" />
        <x-admin.stat-card title="Weekly Active" :value="$stats['wau']" icon="calendar-days" color="blue" />
        <x-admin.stat-card title="Monthly Active" :value="$stats['mau']" icon="calendar" color="purple" />
        <x-admin.stat-card title="Signups Today" :value="$stats['registrations_today']" icon="user-plus" color="orange" />
        <x-admin.stat-card title="Premium Rate" :value="$stats['premium_rate'] . '%'" icon="crown" color="indigo" />
        <x-admin.stat-card title="Inactive 30d+" :value="$stats['inactive_users']" icon="user-x" color="red" />
    </div>

    <!-- Feature Usage Row (last 7 days) -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <x-admin.stat-card title="Exercises (7d)" :value="$stats['exercise_week']" icon="headphones" color="purple" />
        <x-admin.stat-card title="AI Sessions (7d)" :value="$stats['ai_sessions_week']" icon="brain" color="indigo" :link="route('admin.ai-coach-admin.index')" />
        <x-admin.stat-card title="Game Plays (7d)" :value="$stats['game_plays_week']" icon="gamepad-2" color="orange" />
        <x-admin.stat-card title="Feed Posts (7d)" :value="$stats['feed_items_week']" icon="rss" color="green" />
    </div>

    <!-- Charts Row -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <x-admin.chart-card title="Member Registration Trend" chartId="registrationChart" />
        <x-admin.chart-card title="Plan Distribution" chartId="planChart" />
    </div>
    <div class="grid grid-cols-1 gap-6">
        <x-admin.chart-card title="Exercise Volume (14 days)" chartId="exerciseVolumeChart" />
    </div>

    <!-- Members Row -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="card p-6">
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center gap-2">
                    <i data-lucide="user-plus" class="w-5 h-5 text-purple-600"></i>
                    <h2 class="text-lg font-semibold text-gray-900">Latest Members</h2>
                </div>
                <a href="{{ route('admin.users.index') }}" class="text-sm text-purple-600 hover:text-purple-700 font-medium">View all</a>
            </div>
            <div class="space-y-3">
                @forelse($recentUsers as $u)
                <a href="{{ route('admin.users.show', $u) }}" class="flex items-center gap-3 p-2.5 rounded-lg hover:bg-gray-50 transition-colors">
                    <div class="w-9 h-9 rounded-full bg-gradient-to-br from-purple-500 to-pink-500 flex items-center justify-center text-white font-semibold text-sm shrink-0">
                        {{ strtoupper(substr($u->name ?? '?', 0, 1)) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900 truncate">{{ $u->name }}</p>
                        <p class="text-xs text-gray-500 truncate">{{ $u->email }}</p>
                    </div>
                    <span class="px-2 py-0.5 text-xs font-medium rounded-full {{ $u->plan === 'premium' ? 'bg-orange-100 text-orange-700' : 'bg-gray-100 text-gray-600' }}">{{ ucfirst($u->plan ?? 'free') }}</span>
                    <span class="text-xs text-gray-400 shrink-0">{{ $u->created_at->diffForHumans(null, true) }}</span>
                </a>
                @empty
                <p class="text-sm text-gray-500 text-center py-6">No members yet.</p>
                @endforelse
            </div>
        </div>

        <div class="card p-6">
            <div class="flex items-center gap-2 mb-6">
                <i data-lucide="flame" class="w-5 h-5 text-orange-500"></i>
                <h2 class="text-lg font-semibold text-gray-900">Most Active (7 days)</h2>
            </div>
            <div class="space-y-3">
                @forelse($topUsers as $row)
                <a href="{{ route('admin.users.show', $row->user) }}" class="flex items-center gap-3 p-2.5 rounded-lg hover:bg-gray-50 transition-colors">
                    <div class="w-9 h-9 rounded-full bg-gradient-to-br from-orange-400 to-pink-500 flex items-center justify-center text-white font-semibold text-sm shrink-0">
                        {{ strtoupper(substr($row->user->name ?? '?', 0, 1)) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900 truncate">{{ $row->user->name }}</p>
                        <p class="text-xs text-gray-500 truncate">{{ $row->user->email }}</p>
                    </div>
                    <span class="text-sm font-semibold text-gray-900">{{ $row->total }}</span>
                    <span class="text-xs text-gray-400">exercises</span>
                </a>
                @empty
                <p class="text-sm text-gray-500 text-center py-6">No exercise activity in the last 7 days.</p>
                @endforelse
            </div>
        </div>
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

    // Exercise Volume Bar Chart
    const volData = @json($exerciseVolume ?? []);
    if (volData.length > 0) {
        new ApexCharts(document.querySelector('#exerciseVolumeChart'), {
            chart: { type: 'bar', height: 280, toolbar: { show: false }, fontFamily: 'Plus Jakarta Sans' },
            series: [{ name: 'Exercises', data: volData.map(item => item.total) }],
            xaxis: { categories: volData.map(item => item.date), labels: { style: { fontSize: '11px', colors: '#9ca3af' } } },
            yaxis: { labels: { style: { fontSize: '11px', colors: '#9ca3af' } } },
            colors: ['#f97316'],
            plotOptions: { bar: { borderRadius: 6, columnWidth: '55%' } },
            grid: { borderColor: '#f3f4f6', strokeDashArray: 4 },
            tooltip: { theme: 'light' },
            dataLabels: { enabled: false }
        }).render();
    }
});
</script>
@endpush
