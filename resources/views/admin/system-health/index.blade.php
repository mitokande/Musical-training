@extends('admin.layouts.admin')

@section('page-title', 'System Health')

@section('content')
<div class="space-y-6">

    {{-- Status cards --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="card p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 font-medium">Database</p>
                    <p class="text-lg font-bold mt-1 {{ $dbOk ? 'text-green-600' : 'text-red-600' }}">{{ $dbOk ? 'Connected' : 'Error' }}</p>
                </div>
                <div class="w-10 h-10 rounded-lg {{ $dbOk ? 'bg-green-50' : 'bg-red-50' }} flex items-center justify-center">
                    <i data-lucide="database" class="w-5 h-5 {{ $dbOk ? 'text-green-500' : 'text-red-500' }}"></i>
                </div>
            </div>
        </div>
        <x-admin.stat-card title="Pending Jobs" :value="$pendingJobs" icon="list-todo" :color="$pendingJobs > 50 ? 'orange' : 'blue'" />
        <x-admin.stat-card title="Failed Jobs" :value="$failedJobs" icon="alert-triangle" :color="$failedJobs > 0 ? 'red' : 'green'" />
        <x-admin.stat-card title="Log File Size" :value="$storage['log_size']" icon="file-text" color="purple" />
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- System info --}}
        <div class="card p-6">
            <div class="flex items-center gap-2 mb-5">
                <i data-lucide="server" class="w-5 h-5 text-purple-600"></i>
                <h2 class="text-lg font-semibold text-gray-900">System</h2>
            </div>
            <dl class="divide-y divide-gray-100 text-sm">
                @foreach([
                    'PHP Version' => $system['php_version'],
                    'Laravel Version' => $system['laravel_version'],
                    'Environment' => $system['environment'],
                    'Debug Mode' => $system['debug_mode'] ? 'ON (should be OFF in production!)' : 'Off',
                    'Timezone' => $system['timezone'],
                    'Server Time' => $system['server_time'],
                    'Cache Driver' => $system['cache_driver'],
                    'Queue Driver' => $system['queue_driver'],
                    'Disk Usage' => $storage['disk_used_pct'] !== null ? $storage['disk_used_pct'].'% used ('.$storage['disk_free'].' free of '.$storage['disk_total'].')' : 'n/a',
                ] as $label => $value)
                <div class="flex items-center justify-between py-2.5">
                    <dt class="text-gray-500">{{ $label }}</dt>
                    <dd class="font-medium {{ str_contains((string)$value, 'should be OFF') ? 'text-red-600' : 'text-gray-900' }}">{{ $value }}</dd>
                </div>
                @endforeach
                @if($oldestPendingJob)
                <div class="flex items-center justify-between py-2.5">
                    <dt class="text-gray-500">Oldest Pending Job</dt>
                    <dd class="font-medium text-orange-600">{{ \Carbon\Carbon::createFromTimestamp($oldestPendingJob)->diffForHumans() }}</dd>
                </div>
                @endif
            </dl>
        </div>

        {{-- Table counts --}}
        <div class="card p-6">
            <div class="flex items-center gap-2 mb-5">
                <i data-lucide="table" class="w-5 h-5 text-purple-600"></i>
                <h2 class="text-lg font-semibold text-gray-900">Data Overview</h2>
            </div>
            <dl class="divide-y divide-gray-100 text-sm">
                @foreach($tableCounts as $table => $count)
                <div class="flex items-center justify-between py-2.5">
                    <dt class="text-gray-500">{{ ucwords(str_replace('_', ' ', $table)) }}</dt>
                    <dd class="font-semibold text-gray-900">{{ number_format($count) }}</dd>
                </div>
                @endforeach
            </dl>
        </div>
    </div>

    {{-- Recent errors --}}
    <div class="card p-6">
        <div class="flex items-center gap-2 mb-5">
            <i data-lucide="bug" class="w-5 h-5 text-red-500"></i>
            <h2 class="text-lg font-semibold text-gray-900">Recent Log Errors</h2>
            <span class="text-xs text-gray-400 ml-1">(grouped, from the tail of laravel.log)</span>
        </div>

        @if(count($logErrors) === 0)
        <div class="text-center py-8">
            <div class="w-12 h-12 rounded-full bg-green-50 flex items-center justify-center mx-auto mb-3">
                <i data-lucide="check-circle" class="w-6 h-6 text-green-500"></i>
            </div>
            <p class="text-sm text-gray-500">No recent errors found. All clear!</p>
        </div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-200 text-left">
                        <th class="py-3 px-3 font-semibold text-gray-600">Level</th>
                        <th class="py-3 px-3 font-semibold text-gray-600 w-full">Message</th>
                        <th class="py-3 px-3 font-semibold text-gray-600 text-right whitespace-nowrap">Count</th>
                        <th class="py-3 px-3 font-semibold text-gray-600 whitespace-nowrap">Last Seen</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($logErrors as $error)
                    <tr class="hover:bg-gray-50">
                        <td class="py-3 px-3">
                            <span class="px-2 py-0.5 text-xs font-bold rounded-full {{ $error['level'] === 'ERROR' ? 'bg-red-100 text-red-700' : 'bg-orange-100 text-orange-700' }}">{{ $error['level'] }}</span>
                        </td>
                        <td class="py-3 px-3 text-gray-800 font-mono text-xs break-all">{{ $error['message'] }}</td>
                        <td class="py-3 px-3 text-right font-semibold text-gray-900">{{ $error['count'] }}</td>
                        <td class="py-3 px-3 text-gray-500 whitespace-nowrap">{{ $error['last_seen'] }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
</div>
@endsection
