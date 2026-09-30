@extends('admin.layouts.admin')

@section('page-title', 'Mobile App — Live')

@php
    use App\Services\Telemetry\EventPresenter;
    $tones = EventPresenter::TONES;
@endphp

@section('content')
<div class="space-y-6">
    @include('admin.app-analytics._header')

    <div class="card p-4">
        <form method="GET" class="flex flex-wrap items-end gap-4">
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Event</label>
                <select name="name" class="px-3 py-2 border border-gray-300 rounded-lg text-sm font-mono focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                    <option value="">everything</option>
                    @foreach ($names as $option)
                        <option value="{{ $option }}" @selected($name === $option)>{{ $option }}</option>
                    @endforeach
                </select>
            </div>
            <label class="inline-flex items-center gap-2 text-sm text-gray-600 pb-2">
                <input type="checkbox" name="hide_taps" value="1" @checked($hideTaps) class="rounded text-purple-600"> Hide taps
            </label>
            <button type="submit" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-sm font-medium text-gray-700 rounded-lg transition-colors">
                <i data-lucide="filter" class="w-4 h-4 inline mr-1"></i> Show
            </button>
            <p class="ml-auto text-xs text-gray-400">Newest first, as batches land. One person's stream is their <a href="{{ route('admin.app-analytics.users') }}" class="text-purple-600 hover:underline">journey</a>.</p>
        </form>
    </div>

    <div class="card p-5">
        <div class="space-y-1">
            @forelse ($events as $event)
                @php
                    $d = EventPresenter::describe($event->name, $event->props ?? []);
                    [$dot, $text] = $tones[$d['tone']] ?? $tones['system'];
                @endphp
                <div class="grid grid-cols-12 gap-3 items-start text-sm py-1 px-2 rounded hover:bg-gray-50">
                    <div class="col-span-12 sm:col-span-2 text-xs text-gray-500 font-mono">{{ $event->occurred_at->format('M j H:i:s') }}</div>
                    <div class="col-span-12 sm:col-span-6 flex items-start gap-2">
                        <span class="flex-none w-5 h-5 rounded-full flex items-center justify-center {{ $dot }}"><i data-lucide="{{ $d['icon'] }}" class="w-3 h-3"></i></span>
                        <span class="{{ $text }} {{ $d['tone'] === 'tap' ? 'font-mono text-xs pt-0.5' : 'font-medium' }}">{{ $d['title'] }}</span>
                        @if ($d['detail'])<span class="text-xs text-gray-500 pt-0.5">{{ $d['detail'] }}</span>@endif
                    </div>
                    <div class="col-span-6 sm:col-span-2 text-xs font-mono text-gray-400 truncate">{{ $event->screen }}</div>
                    <div class="col-span-6 sm:col-span-2 text-xs text-gray-500 sm:text-right">
                        <a href="{{ route('admin.app-analytics.journey', ['q' => $event->user?->id ?? $event->install_id, 'session' => $event->session_id]) }}" class="{{ $event->user ? 'text-purple-600' : '' }} hover:underline">{{ $event->user ? Str::limit($event->user->name, 18) : 'anonymous' }}</a>
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
