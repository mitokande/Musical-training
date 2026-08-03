@extends('admin.layouts.admin')

@section('page-title', 'Ad Studio')

@section('content')
<div class="space-y-6" x-data="{ creating: false }">

    {{-- Header --}}
    <div class="flex items-start justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Ad Studio</h2>
            <p class="text-sm text-gray-500 mt-1 max-w-2xl">
                Author a vertical ad from a shipped template, generate the narration, and render it on this server.
                Frame timings are cut to the measured voiceover, so changing the script keeps the cut on length.
            </p>
        </div>
        <button @click="creating = !creating"
                class="shrink-0 inline-flex items-center gap-2 px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition text-sm">
            <i data-lucide="plus" class="w-4 h-4"></i> New creative
        </button>
    </div>

    @if (session('success'))
        <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">{{ session('error') }}</div>
    @endif

    {{-- Create --}}
    <div x-show="creating" x-collapse class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
        <form method="POST" action="{{ route('admin.ad-studio.store') }}" class="flex flex-wrap items-end gap-4">
            @csrf
            <div class="flex-1 min-w-64">
                <label class="block text-xs font-medium text-gray-500 mb-1">Name</label>
                <input type="text" name="name" required maxlength="120"
                       placeholder="e.g. Tritone round — August test"
                       class="w-full rounded-lg border-gray-300 text-sm focus:border-purple-500 focus:ring-purple-500" />
                @error('name') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>
            <div class="min-w-72">
                <label class="block text-xs font-medium text-gray-500 mb-1">Template</label>
                <select name="template" class="w-full rounded-lg border-gray-300 text-sm focus:border-purple-500 focus:ring-purple-500">
                    @foreach ($templates as $key => $template)
                        <option value="{{ $key }}">{{ $template['label'] }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition text-sm">
                Create
            </button>
        </form>

        <div class="mt-4 space-y-2 border-t border-gray-100 pt-4">
            @foreach ($templates as $template)
                <p class="text-xs text-gray-500">
                    <span class="font-semibold text-gray-700">{{ $template['label'] }}</span>
                    · {{ $template['aspect'] }} · {{ $template['target_duration'] }}s — {{ $template['blurb'] }}
                </p>
            @endforeach
        </div>
    </div>

    {{-- Filter --}}
    <div class="flex flex-wrap items-center gap-2">
        <a href="{{ route('admin.ad-studio.index') }}"
           class="px-3 py-1.5 rounded-full text-xs border {{ request()->missing('status') ? 'bg-purple-50 border-purple-300 text-purple-700' : 'border-gray-200 text-gray-600 hover:bg-gray-50' }}">
            All
        </a>
        @foreach ($statuses as $value => $label)
            <a href="{{ route('admin.ad-studio.index', ['status' => $value]) }}"
               class="px-3 py-1.5 rounded-full text-xs border {{ request('status') === $value ? 'bg-purple-50 border-purple-300 text-purple-700' : 'border-gray-200 text-gray-600 hover:bg-gray-50' }}">
                {{ $label }}
            </a>
        @endforeach
    </div>

    {{-- List --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        @if ($creatives->isEmpty())
            <div class="p-10 text-center">
                <i data-lucide="clapperboard" class="w-8 h-8 mx-auto text-gray-300"></i>
                <p class="mt-3 text-sm text-gray-500">No creatives yet. A new one starts as an exact copy of the shipped variant, so it renders a known-good cut before you change a word.</p>
            </div>
        @else
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-left text-xs uppercase tracking-wide text-gray-500">
                    <tr>
                        <th class="px-5 py-3">Creative</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3">Length</th>
                        <th class="px-5 py-3">Updated</th>
                        <th class="px-5 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach ($creatives as $creative)
                        <tr class="hover:bg-gray-50/60">
                            <td class="px-5 py-4">
                                <a href="{{ route('admin.ad-studio.edit', $creative) }}" class="font-medium text-gray-800 hover:text-purple-700">
                                    {{ $creative->name }}
                                </a>
                                <div class="text-xs text-gray-400 mt-0.5">
                                    {{ $templates[$creative->template]['label'] ?? $creative->template }}
                                    @if ($creative->author) · {{ $creative->author->name }} @endif
                                </div>
                                @if ($creative->error)
                                    <div class="text-xs text-red-600 mt-1 max-w-xl">{{ Str::limit($creative->error, 180) }}</div>
                                @endif
                            </td>
                            <td class="px-5 py-4">
                                @php $color = $creative->statusColor(); @endphp
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs bg-{{ $color }}-50 text-{{ $color }}-700 border border-{{ $color }}-200">
                                    @if ($creative->isBusy())
                                        <span class="w-1.5 h-1.5 rounded-full bg-{{ $color }}-500 animate-pulse"></span>
                                    @endif
                                    {{ $creative->statusLabel() }}
                                </span>
                            </td>
                            <td class="px-5 py-4 text-gray-600">
                                {{ $creative->duration_seconds ? $creative->duration_seconds.'s' : '—' }}
                                @if ($creative->render_bytes)
                                    <span class="text-xs text-gray-400">· {{ number_format($creative->render_bytes / 1048576, 1) }} MB</span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-gray-500 text-xs">{{ $creative->updated_at->diffForHumans() }}</td>
                            <td class="px-5 py-4">
                                <div class="flex items-center justify-end gap-2">
                                    @if ($creative->hasRender())
                                        <a href="{{ route('admin.ad-studio.download', $creative) }}"
                                           class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-gray-200 text-xs text-gray-700 hover:bg-gray-50">
                                            <i data-lucide="download" class="w-3.5 h-3.5"></i> MP4
                                        </a>
                                    @endif
                                    <a href="{{ route('admin.ad-studio.edit', $creative) }}"
                                       class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-gray-200 text-xs text-gray-700 hover:bg-gray-50">
                                        <i data-lucide="pencil" class="w-3.5 h-3.5"></i> Edit
                                    </a>
                                    @unless ($creative->isBusy())
                                        <form method="POST" action="{{ route('admin.ad-studio.destroy', $creative) }}"
                                              onsubmit="return confirm('Delete this creative and its generated project? The rendered MP4 goes with it.')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="inline-flex items-center px-2.5 py-1.5 rounded-lg border border-gray-200 text-xs text-red-600 hover:bg-red-50">
                                                <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                            </button>
                                        </form>
                                    @endunless
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    {{ $creatives->links() }}

    {{-- How the work actually runs. Worth stating: the panel looks synchronous
         and is not, and an operator watching a spinner deserves to know why. --}}
    <div class="rounded-xl border border-gray-200 bg-gray-50 px-5 py-4 text-xs text-gray-500 leading-relaxed">
        <span class="font-semibold text-gray-700">How builds run.</span>
        Narration and rendering happen in a scheduled drainer (<code class="text-gray-600">ads:process-queue</code>, every minute),
        not in the shared queue worker — a render takes about three minutes and would exceed that worker's 60-second job timeout
        while blocking transactional email behind the same lock. Builds take roughly half a minute; renders a few minutes.
        Both require the server crontab to be running <code class="text-gray-600">schedule:run</code>.
    </div>
</div>
@endsection
