@extends('admin.layouts.admin')

@section('page-title', 'Mobile App — Activity')

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
                <i data-lucide="search" class="w-4 h-4 inline mr-1"></i> Show activity
            </button>
            @if ($q !== '')
                <a href="{{ route('admin.app-analytics.activity') }}" class="text-sm text-gray-500 hover:text-purple-600">Clear</a>
            @endif
        </form>
    </div>

    @if ($q !== '' && ! $user && $installs->isEmpty())
        <div class="card p-5 text-sm text-gray-500">Nobody found for “{{ $q }}”.</div>
    @endif

    @if ($user || $installs->isNotEmpty())
        <div class="card p-5">
            @if ($user)
                <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
                    <div>
                        <p class="font-semibold text-gray-900">{{ $user->name }}</p>
                        <p class="text-sm text-gray-500">#{{ $user->id }} · {{ $user->email }}</p>
                    </div>
                    <a href="{{ route('admin.users.show', $user) }}" class="text-sm text-purple-600 hover:underline">Member details →</a>
                </div>
            @endif
            <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Installs</h4>
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-xs text-gray-500 border-b">
                        <th class="py-2 pr-4 font-medium">Install</th>
                        <th class="py-2 pr-4 font-medium">Device</th>
                        <th class="py-2 pr-4 font-medium">App</th>
                        <th class="py-2 pr-4 font-medium">Locale</th>
                        <th class="py-2 pr-4 font-medium">First seen</th>
                        <th class="py-2 font-medium">Last seen</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($installs as $install)
                        <tr class="border-b border-gray-50">
                            <td class="py-2 pr-4 font-mono text-xs">
                                <a href="{{ route('admin.app-analytics.activity', ['q' => $install->install_id]) }}" class="text-purple-600 hover:underline">{{ Str::limit($install->install_id, 13, '…') }}</a>
                            </td>
                            <td class="py-2 pr-4">{{ $install->platform }} {{ $install->os_version }}</td>
                            <td class="py-2 pr-4 font-mono text-xs">{{ $install->app_version }} ({{ $install->build }})</td>
                            <td class="py-2 pr-4">{{ $install->locale }} · {{ $install->timezone }}</td>
                            <td class="py-2 pr-4 text-gray-600">{{ $install->first_seen_at->format('M j, Y H:i') }}</td>
                            <td class="py-2 text-gray-600">{{ $install->last_seen_at->diffForHumans() }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="py-4 text-center text-gray-400">No app installs for this member.</td></tr>
                    @endforelse
                </tbody>
            </table>
            @if (! $user && $installs->first()?->user)
                <p class="mt-3 text-sm text-gray-600">
                    Last signed in as
                    <a href="{{ route('admin.app-analytics.activity', ['q' => $installs->first()->user->id]) }}" class="text-purple-600 hover:underline">{{ $installs->first()->user->name }}</a>.
                </p>
            @endif
        </div>
    @endif

    <div class="card p-5">
        <h3 class="text-sm font-semibold text-gray-700 mb-4">{{ $q === '' ? 'Latest events from everyone' : 'Events, newest first' }}</h3>

        @php $session = false; @endphp
        <div class="space-y-1">
            @forelse ($events as $event)
                @if ($event->session_id !== $session)
                    @php $session = $event->session_id; @endphp
                    <div class="pt-3 pb-1 flex items-center gap-2 text-xs text-gray-400">
                        <i data-lucide="layers" class="w-3 h-3"></i>
                        session {{ $session ? Str::limit($session, 8, '') : '—' }}
                        <span class="flex-1 border-t border-dashed border-gray-200"></span>
                    </div>
                @endif
                <div class="grid grid-cols-12 gap-3 text-sm py-1 px-2 rounded hover:bg-gray-50">
                    <div class="col-span-12 sm:col-span-2 text-xs text-gray-500 font-mono">{{ $event->occurred_at->format('M j H:i:s') }}</div>
                    <div class="col-span-12 sm:col-span-2 font-semibold text-gray-800">{{ $event->name }}</div>
                    <div class="col-span-12 sm:col-span-6 text-xs text-gray-600 font-mono break-all">
                        @foreach (($event->props ?? []) as $key => $value)
                            <span class="mr-2"><span class="text-gray-400">{{ $key }}=</span>{{ is_scalar($value) || $value === null ? var_export($value, true) : json_encode($value) }}</span>
                        @endforeach
                    </div>
                    <div class="col-span-12 sm:col-span-2 text-xs text-gray-500 sm:text-right">
                        @if ($q === '' && $event->user)
                            <a href="{{ route('admin.app-analytics.activity', ['q' => $event->user->id]) }}" class="text-purple-600 hover:underline">{{ Str::limit($event->user->name, 18) }}</a>
                        @elseif ($q === '')
                            <a href="{{ route('admin.app-analytics.activity', ['q' => $event->install_id]) }}" class="hover:underline">anonymous</a>
                        @endif
                        <span class="ml-1">{{ $event->platform }} {{ $event->app_version }}</span>
                    </div>
                </div>
            @empty
                <p class="py-6 text-center text-gray-400 text-sm">No events.</p>
            @endforelse
        </div>

        <div class="mt-4">{{ $events->links() }}</div>
    </div>
</div>
@endsection
