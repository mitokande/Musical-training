@extends('admin.layouts.admin')

@section('page-title', 'Mobile App — Journey')

@php
    use App\Services\Telemetry\EventPresenter;
    $tones = EventPresenter::TONES;
@endphp

@section('content')
<div class="space-y-6">
    @include('admin.app-analytics._header')

    <div class="card p-4">
        <form method="GET" class="flex flex-wrap items-end gap-4">
            <div class="flex-1 min-w-[240px]">
                <label class="block text-xs font-medium text-gray-500 mb-1">Member id, e-mail or install id</label>
                <input type="text" name="q" value="{{ $q }}" placeholder="42, someone@example.com, or 6f1c2d3e-…"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
            </div>
            <button type="submit" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-sm font-medium text-gray-700 rounded-lg transition-colors">
                <i data-lucide="search" class="w-4 h-4 inline mr-1"></i> Show journey
            </button>
            <a href="{{ route('admin.app-analytics.users') }}" class="text-sm text-gray-500 hover:text-purple-600">Browse users →</a>
        </form>
    </div>

    @if (! $user && ! $install)
        <div class="card p-8 text-center text-sm text-gray-500">
            @if ($q !== '')
                Nobody found for “{{ $q }}”.
            @else
                Look someone up above, or pick them from <a href="{{ route('admin.app-analytics.users') }}" class="text-purple-600 hover:underline">Users</a>.
            @endif
        </div>
    @else
        {{-- Who --}}
        <div class="card p-5">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    @if ($user)
                        <p class="text-lg font-semibold text-gray-900">{{ $user->name }}</p>
                        <p class="text-sm text-gray-500">#{{ $user->id }} · {{ $user->email }} @if ($user->trashed())<span class="ml-1 px-1.5 py-0.5 text-[10px] rounded bg-red-50 text-red-700">deleted</span>@endif</p>
                    @else
                        <p class="text-lg font-semibold text-gray-900">Anonymous install</p>
                        <p class="text-sm text-gray-500 font-mono">{{ $install->install_id }}</p>
                        @if ($install->user)
                            <p class="text-sm mt-1">Last signed in as
                                <a href="{{ route('admin.app-analytics.journey', ['q' => $install->user->id]) }}" class="text-purple-600 hover:underline">{{ $install->user->name }}</a>
                                — their journey includes this device.</p>
                        @endif
                    @endif
                </div>
                @if ($user)
                    <a href="{{ route('admin.users.show', $user) }}" class="text-sm text-purple-600 hover:underline">Member details →</a>
                @endif
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 mt-5">
                @foreach ([
                    ['Sessions', number_format($totals->sessions ?? 0), 'layers'],
                    ['Time in app', ($totals->foreground_ms ?? 0) > 0 ? EventPresenter::duration((int) $totals->foreground_ms) : '—', 'timer'],
                    ['Screens', number_format($totals->screens ?? 0), 'smartphone'],
                    ['Taps', number_format($totals->taps ?? 0), 'mouse-pointer-click'],
                    ['Lessons finished', number_format($totals->lessons ?? 0), 'flag'],
                    ['Errors', number_format($totals->errors ?? 0), 'bug'],
                ] as [$label, $value, $icon])
                    <div class="rounded-lg border border-gray-100 p-3">
                        <p class="text-xs text-gray-500 flex items-center gap-1"><i data-lucide="{{ $icon }}" class="w-3.5 h-3.5"></i> {{ $label }}</p>
                        <p class="text-lg font-semibold text-gray-900 mt-0.5">{{ $value }}</p>
                    </div>
                @endforeach
            </div>

            {{-- The turns the story took, in order, and how long after the first open each came. --}}
            <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wide mt-6 mb-3">Milestones</h4>
            <ol class="flex flex-wrap gap-2">
                @foreach ($milestones as $milestone)
                    <li class="flex items-center gap-2 px-3 py-2 rounded-lg border {{ $milestone['at'] ? 'border-purple-200 bg-purple-50/50' : 'border-dashed border-gray-200 text-gray-400' }}">
                        <i data-lucide="{{ $milestone['icon'] }}" class="w-4 h-4 {{ $milestone['at'] ? 'text-purple-600' : '' }}"></i>
                        <div class="leading-tight">
                            <p class="text-xs font-medium {{ $milestone['at'] ? 'text-gray-800' : '' }}">{{ $milestone['label'] }}</p>
                            <p class="text-[11px] {{ $milestone['at'] ? 'text-gray-500' : '' }}">
                                @if ($milestone['at'])
                                    {{ \Illuminate\Support\Carbon::parse($milestone['at'])->format('M j, H:i') }}
                                    @if ($milestone['after'] && $milestone['label'] !== 'First seen') · +{{ $milestone['after'] }} @endif
                                @else
                                    not yet
                                @endif
                            </p>
                        </div>
                    </li>
                @endforeach
            </ol>

            @if ($answers)
                <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wide mt-6 mb-2">Onboarding answers</h4>
                <div class="flex flex-wrap gap-1.5 text-xs">
                    @foreach (['goals' => 'Goal', 'instruments' => 'Plays'] as $key => $label)
                        @foreach ((array) ($answers[$key] ?? []) as $value)
                            <span class="px-2 py-1 rounded-full bg-gray-100 text-gray-700"><span class="text-gray-400">{{ $label }}:</span> {{ $value }}</span>
                        @endforeach
                    @endforeach
                    @foreach (['intervals' => 'Intervals', 'notation' => 'Notation', 'minutesPerDay' => 'Min/day', 'startNodeId' => 'Starts at'] as $key => $label)
                        @if (($answers[$key] ?? null) !== null)
                            <span class="px-2 py-1 rounded-full bg-gray-100 text-gray-700"><span class="text-gray-400">{{ $label }}:</span> {{ $answers[$key] }}</span>
                        @endif
                    @endforeach
                </div>
            @endif

            @if ($installs->isNotEmpty())
                <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wide mt-6 mb-2">Devices</h4>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <tbody>
                            @foreach ($installs as $device)
                                <tr class="border-b border-gray-50">
                                    <td class="py-1.5 pr-4 font-mono text-xs">
                                        <a href="{{ route('admin.app-analytics.journey', ['q' => $device->install_id]) }}" class="text-purple-600 hover:underline">{{ Str::limit($device->install_id, 13, '…') }}</a>
                                    </td>
                                    <td class="py-1.5 pr-4 text-xs">{{ $device->platform }} {{ $device->os_version }}</td>
                                    <td class="py-1.5 pr-4 font-mono text-xs">v{{ $device->app_version }} ({{ $device->build }})</td>
                                    <td class="py-1.5 pr-4 text-xs">{{ $device->locale }} · {{ $device->timezone }}</td>
                                    <td class="py-1.5 text-xs text-gray-500">first {{ $device->first_seen_at->format('M j, Y') }} · last {{ $device->last_seen_at->diffForHumans() }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        {{-- Sessions --}}
        <div x-data="{ taps: true, system: false }" class="space-y-3">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <h3 class="text-sm font-semibold text-gray-700">Sessions <span class="font-normal text-gray-400">· {{ number_format($sessions->total()) }}, newest first</span></h3>
                <div class="flex items-center gap-4 text-xs text-gray-600">
                    <label class="inline-flex items-center gap-1.5 cursor-pointer"><input type="checkbox" x-model="taps" class="rounded text-purple-600"> Taps</label>
                    <label class="inline-flex items-center gap-1.5 cursor-pointer"><input type="checkbox" x-model="system" class="rounded text-purple-600"> Opens &amp; closes</label>
                </div>
            </div>

            @forelse ($sessions as $i => $session)
                @php
                    $timeline = $timelines[$session->session_id] ?? ['visits' => [], 'path' => [], 'truncated' => 0];
                    $open = $focus !== '' ? $focus === $session->session_id : ($sessions->currentPage() === 1 && $i === 0);
                @endphp
                <div id="session-{{ $session->session_id }}" x-data="{ open: {{ $open ? 'true' : 'false' }} }" class="card {{ $focus === $session->session_id ? 'ring-2 ring-purple-300' : '' }}">
                    <button type="button" @click="open = ! open" class="w-full text-left p-4">
                        <div class="flex flex-wrap items-center gap-x-4 gap-y-1">
                            <i data-lucide="chevron-right" class="w-4 h-4 text-gray-400 transition-transform" :class="open ? 'rotate-90' : ''"></i>
                            <span class="font-semibold text-gray-900">{{ \Illuminate\Support\Carbon::parse($session->first_at)->format('D, M j · H:i') }}</span>
                            <span class="text-xs text-gray-500">{{ EventPresenter::duration((int) $session->span_ms) }}</span>
                            <span class="text-xs text-gray-400">{{ $session->platform }} v{{ $session->app_version }}</span>
                            <span class="text-xs text-gray-500">{{ $session->screens }} screens · {{ $session->taps }} taps</span>
                            <span class="flex flex-wrap gap-1 ml-auto">
                                @if ($session->lessons > 0)<span class="px-1.5 py-0.5 text-[10px] rounded bg-blue-50 text-blue-700">{{ $session->lessons }} lesson{{ $session->lessons > 1 ? 's' : '' }}</span>@endif
                                @if ($session->paywalls > 0)<span class="px-1.5 py-0.5 text-[10px] rounded bg-green-50 text-green-700">paywall</span>@endif
                                @if ($session->purchases > 0)<span class="px-1.5 py-0.5 text-[10px] rounded bg-green-600 text-white">bought</span>@endif
                                @if ($session->errors > 0)<span class="px-1.5 py-0.5 text-[10px] rounded bg-red-50 text-red-700">{{ $session->errors }} errors</span>@endif
                            </span>
                        </div>
                        {{-- The path at a glance: every screen, in order. --}}
                        <div class="flex flex-wrap items-center gap-1 mt-2 pl-8 text-[11px] font-mono">
                            @foreach (array_slice($timeline['path'], 0, 14) as $j => $step)
                                @if ($j > 0)<span class="text-gray-300">→</span>@endif
                                <span class="px-1.5 py-0.5 rounded bg-gray-100 text-gray-700">{{ $step }}</span>
                            @endforeach
                            @if (count($timeline['path']) > 14)<span class="text-gray-400">+{{ count($timeline['path']) - 14 }} more</span>@endif
                        </div>
                    </button>

                    <div x-show="open" x-collapse class="border-t border-gray-100 px-4 pb-4">
                        <ol class="relative mt-3 ml-3 border-l-2 border-purple-100">
                            @foreach ($timeline['visits'] as $visit)
                                <li class="mb-3 ml-5">
                                    <span class="absolute -left-[9px] mt-1 w-4 h-4 rounded-full bg-purple-500 ring-4 ring-white"></span>
                                    <div class="flex flex-wrap items-baseline gap-x-3">
                                        <span class="font-mono text-sm font-semibold text-purple-800">{{ $visit['screen'] }}</span>
                                        <span class="text-xs text-gray-400">{{ $visit['at']->format('H:i:s') }}</span>
                                        @if ($visit['timed'])
                                            <span class="text-xs text-gray-500">{{ EventPresenter::duration($visit['ms']) }} on screen</span>
                                        @endif
                                        <a href="{{ route('admin.app-analytics.screen', ['name' => $visit['screen']]) }}" class="text-[11px] text-gray-400 hover:text-purple-600">screen stats</a>
                                    </div>
                                    @if ($visit['items'])
                                        <ul class="mt-1 space-y-0.5">
                                            @foreach ($visit['items'] as $item)
                                                @php
                                                    $kind = $item['tone'] === 'tap' ? 'taps' : ($item['tone'] === 'system' ? 'system' : 'always');
                                                    [$dot, $text] = $tones[$item['tone']] ?? $tones['system'];
                                                @endphp
                                                <li @if ($kind !== 'always') x-show="{{ $kind }}" @endif class="flex items-start gap-2 text-sm py-0.5">
                                                    <span class="flex-none w-5 h-5 rounded-full flex items-center justify-center {{ $dot }}"><i data-lucide="{{ $item['icon'] }}" class="w-3 h-3"></i></span>
                                                    <span class="{{ $text }} {{ $item['tone'] === 'tap' ? 'font-mono text-xs pt-0.5' : 'font-medium' }}">{{ $item['title'] }}</span>
                                                    @if ($item['detail'])<span class="text-xs text-gray-500 pt-0.5">{{ $item['detail'] }}</span>@endif
                                                    <span class="ml-auto text-[11px] text-gray-300 font-mono pt-0.5">{{ $item['at']->format('H:i:s') }}</span>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </li>
                            @endforeach
                        </ol>
                        @if ($timeline['truncated'] > 0)
                            <p class="text-xs text-gray-400 ml-8">… and {{ number_format($timeline['truncated']) }} more events in this session.</p>
                        @endif
                    </div>
                </div>
            @empty
                <div class="card p-6 text-center text-sm text-gray-400">No sessions recorded.</div>
            @endforelse

            <div>{{ $sessions->links() }}</div>
        </div>
    @endif
</div>
@endsection
