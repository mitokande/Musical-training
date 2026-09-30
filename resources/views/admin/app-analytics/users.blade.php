@extends('admin.layouts.admin')

@section('page-title', 'Mobile App — Users')

@section('content')
<div class="space-y-6">
    @include('admin.app-analytics._header')

    <div class="card p-4">
        <form method="GET" class="flex flex-wrap items-end gap-4">
            @foreach ($range->query() as $key => $value)
                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
            @endforeach
            <div class="flex-1 min-w-[240px]">
                <label class="block text-xs font-medium text-gray-500 mb-1">Member id, e-mail, name or install id</label>
                <input type="text" name="q" value="{{ $q }}" placeholder="42, someone@example.com, or 6f1c2d…"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Who</label>
                <select name="segment" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                    @foreach (\App\Services\Telemetry\UserJourneys::SEGMENTS as $key => $label)
                        <option value="{{ $key }}" @selected($segment === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-sm font-medium text-gray-700 rounded-lg transition-colors">
                <i data-lucide="search" class="w-4 h-4 inline mr-1"></i> Find
            </button>
        </form>
    </div>

    <div class="card p-5">
        <div class="flex flex-wrap items-baseline justify-between gap-2 mb-1">
            <h3 class="text-sm font-semibold text-gray-700">Devices active in this range</h3>
            <span class="text-xs text-gray-500">{{ number_format($installs->total()) }} installs</span>
        </div>
        <p class="text-xs text-gray-400 mb-4">
            One row per installation, most recently seen first; the numbers are for the range. Open one to replay its journey —
            a signed-in install opens the account's journey, every device it used included.
        </p>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-xs text-gray-500 border-b">
                        <th class="py-2 pr-4 font-medium">Who</th>
                        <th class="py-2 pr-4 font-medium">Device</th>
                        <th class="py-2 pr-4 font-medium">First seen</th>
                        <th class="py-2 pr-4 font-medium">Last seen</th>
                        <th class="py-2 pr-4 font-medium text-right">Sessions</th>
                        <th class="py-2 pr-4 font-medium text-right">Time in app</th>
                        <th class="py-2 pr-4 font-medium text-right">Screens</th>
                        <th class="py-2 pr-4 font-medium text-right">Taps</th>
                        <th class="py-2 pr-4 font-medium text-right">Lessons</th>
                        <th class="py-2 pr-4 font-medium">Status</th>
                        <th class="py-2 font-medium"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($installs as $install)
                        @php
                            $s = $install->stats;
                            $target = $install->user ? $install->user->id : $install->install_id;
                        @endphp
                        <tr class="border-b border-gray-50 hover:bg-gray-50">
                            <td class="py-2 pr-4">
                                @if ($install->user)
                                    <p class="font-medium text-gray-900">{{ Str::limit($install->user->name, 28) }}</p>
                                    <p class="text-xs text-gray-500">#{{ $install->user->id }} · {{ Str::limit($install->user->email, 30) }}</p>
                                @else
                                    <p class="text-gray-500 italic">Anonymous</p>
                                    <p class="text-xs text-gray-400 font-mono">{{ Str::limit($install->install_id, 13, '…') }}</p>
                                @endif
                            </td>
                            <td class="py-2 pr-4 text-xs text-gray-600">
                                {{ $install->platform }} {{ $install->os_version }}<br>
                                <span class="font-mono text-gray-400">v{{ $install->app_version }} · {{ $install->locale }}</span>
                            </td>
                            <td class="py-2 pr-4 text-xs text-gray-600 whitespace-nowrap">{{ $install->first_seen_at->format('M j, H:i') }}</td>
                            <td class="py-2 pr-4 text-xs text-gray-600 whitespace-nowrap">{{ $install->last_seen_at->diffForHumans() }}</td>
                            <td class="py-2 pr-4 text-right">{{ number_format($s->sessions ?? 0) }}</td>
                            <td class="py-2 pr-4 text-right text-gray-600 whitespace-nowrap">{{ ($s->foreground_ms ?? 0) > 0 ? \App\Services\Telemetry\EventPresenter::duration((int) $s->foreground_ms) : '—' }}</td>
                            <td class="py-2 pr-4 text-right">{{ number_format($s->screens ?? 0) }}</td>
                            <td class="py-2 pr-4 text-right">{{ number_format($s->taps ?? 0) }}</td>
                            <td class="py-2 pr-4 text-right">{{ number_format($s->lessons ?? 0) }}</td>
                            <td class="py-2 pr-4">
                                <div class="flex flex-wrap gap-1">
                                    @if (($s->purchases ?? 0) > 0)
                                        <span class="px-1.5 py-0.5 text-[10px] rounded bg-green-50 text-green-700">Premium</span>
                                    @endif
                                    @if ($install->onboarded)
                                        <span class="px-1.5 py-0.5 text-[10px] rounded bg-purple-50 text-purple-700">Onboarded</span>
                                    @else
                                        <span class="px-1.5 py-0.5 text-[10px] rounded bg-amber-50 text-amber-700">No onboarding</span>
                                    @endif
                                    @if (($s->errors ?? 0) > 0)
                                        <span class="px-1.5 py-0.5 text-[10px] rounded bg-red-50 text-red-700">{{ $s->errors }} errors</span>
                                    @endif
                                </div>
                            </td>
                            <td class="py-2 text-right">
                                <a href="{{ route('admin.app-analytics.journey', ['q' => $target]) }}" class="inline-flex items-center gap-1 text-sm text-purple-600 hover:underline whitespace-nowrap">
                                    Journey <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="11" class="py-6 text-center text-gray-400">Nobody matches.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $installs->links() }}</div>
    </div>
</div>
@endsection
