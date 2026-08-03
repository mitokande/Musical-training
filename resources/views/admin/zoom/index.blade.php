@extends('admin.layouts.admin')
@section('page-title', 'Zoom Hosts')

@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Zoom Hosts</h2>
            <p class="text-sm text-gray-500 mt-0.5">
                Licensed Zoom users that live lessons run on. One licence hosts one lesson at a time.
            </p>
        </div>
        <form method="POST" action="{{ route('admin.zoom.sync') }}">
            @csrf
            <button class="inline-flex items-center gap-2 px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition disabled:opacity-50"
                    @disabled(! $configured)>
                <i data-lucide="refresh-cw" class="w-4 h-4"></i>
                Sync from Zoom
            </button>
        </form>
    </div>

    @if(session('status'))
        <div class="px-4 py-3 bg-green-50 border border-green-200 text-green-800 text-sm rounded-lg">{{ session('status') }}</div>
    @endif

    @unless($configured)
        <div class="px-4 py-3 bg-amber-50 border border-amber-200 text-amber-800 text-sm rounded-lg">
            Zoom Server-to-Server OAuth credentials are missing. Set <code>ZOOM_ACCOUNT_ID</code>,
            <code>ZOOM_CLIENT_ID</code> and <code>ZOOM_CLIENT_SECRET</code> in <code>.env</code>.
            Until then every lesson falls back to a manual meeting link.
        </div>
    @endunless

    @if($configured && ! $enabled)
        <div class="px-4 py-3 bg-amber-50 border border-amber-200 text-amber-800 text-sm rounded-lg">
            Credentials are present but <code>ZOOM_ENABLED</code> is false, so no meetings are being created.
        </div>
    @endif

    {{-- Capacity --}}
    <div class="grid sm:grid-cols-3 gap-4">
        @include('admin.components.stat-card', ['title' => 'Active licences', 'value' => $hosts->where('is_active', true)->count(), 'icon' => 'key-round', 'color' => 'purple'])
        @include('admin.components.stat-card', ['title' => 'Lessons live now', 'value' => $liveNow, 'icon' => 'radio', 'color' => 'green'])
        @include('admin.components.stat-card', ['title' => 'Upcoming meetings', 'value' => $upcoming, 'icon' => 'calendar-clock', 'color' => 'blue'])
    </div>

    {{-- Hosts --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Host</th>
                        <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Email</th>
                        <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Last synced</th>
                        <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Status</th>
                        <th class="text-right px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($hosts as $host)
                        <tr>
                            <td class="px-6 py-3 font-semibold text-gray-800">{{ $host->label() }}</td>
                            <td class="px-6 py-3 text-gray-600">{{ $host->email }}</td>
                            <td class="px-6 py-3 text-gray-500">{{ $host->synced_at?->diffForHumans() ?? '—' }}</td>
                            <td class="px-6 py-3">
                                <span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $host->is_active ? 'bg-green-50 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                                    {{ $host->is_active ? 'Active' : 'Paused' }}
                                </span>
                            </td>
                            <td class="px-6 py-3 text-right">
                                <form method="POST" action="{{ route('admin.zoom.toggle', $host) }}">
                                    @csrf
                                    <button class="text-xs font-semibold text-purple-600 hover:text-purple-800">
                                        {{ $host->is_active ? 'Pause' : 'Activate' }}
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-gray-400">
                                No hosts yet. Buy licensed seats in the Zoom portal, then press “Sync from Zoom”.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
