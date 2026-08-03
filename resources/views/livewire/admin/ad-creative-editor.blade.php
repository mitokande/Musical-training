@php
    $template = $this->template();
    $busy = $creative->isBusy();
    $timings = $creative->timings ?? [];
@endphp

{{-- While the drainer owns this row the page polls so the operator sees it
     move. Polling stops the moment it settles — there is nothing to watch. --}}
<div class="space-y-6" @if ($busy) wire:poll.3s="poll" @endif>

    {{-- Header --}}
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div class="min-w-0">
            <a href="{{ route('admin.ad-studio.index') }}" class="text-xs text-gray-400 hover:text-gray-600 inline-flex items-center gap-1">
                <i data-lucide="arrow-left" class="w-3 h-3"></i> All creatives
            </a>
            <h2 class="text-2xl font-bold text-gray-800 mt-1">{{ $creative->name }}</h2>
            <p class="text-sm text-gray-500 mt-1">
                {{ $template['label'] }} · {{ $template['aspect'] }} · target {{ $template['target_duration'] }}s
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            @php $color = $creative->statusColor(); @endphp
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs bg-{{ $color }}-50 text-{{ $color }}-700 border border-{{ $color }}-200">
                @if ($busy)<span class="w-1.5 h-1.5 rounded-full bg-{{ $color }}-500 animate-pulse"></span>@endif
                {{ $creative->statusLabel() }}
            </span>

            <button wire:click="save" @disabled($busy)
                    class="px-4 py-2 rounded-lg border border-gray-200 text-sm text-gray-700 hover:bg-gray-50 disabled:opacity-40">
                Save
            </button>
            <button wire:click="build(false)" @disabled($busy)
                    class="px-4 py-2 rounded-lg border border-purple-200 bg-purple-50 text-sm text-purple-700 hover:bg-purple-100 disabled:opacity-40">
                Build
            </button>
            <button wire:click="build(true)" @disabled($busy)
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-purple-600 text-white text-sm hover:bg-purple-700 disabled:opacity-40">
                <i data-lucide="play" class="w-4 h-4"></i> Build &amp; render
            </button>
        </div>
    </div>

    @if (session('success'))
        <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">{{ session('error') }}</div>
    @endif

    @if ($creative->error)
        <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3">
            <p class="text-sm font-semibold text-red-800">Last attempt failed</p>
            <pre class="mt-1 text-xs text-red-700 whitespace-pre-wrap font-mono">{{ $creative->error }}</pre>
        </div>
    @endif

    @unless ($this->voiceConfigured())
        <div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
            No <code>GEMINI_API_KEY</code> is configured, so narration cannot be generated and nothing can be built.
        </div>
    @endunless

    @foreach ($timings['warnings'] ?? [] as $warning)
        <div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">{{ $warning }}</div>
    @endforeach

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

        {{-- ---------------------------------------------------------------- --}}
        {{-- Script                                                            --}}
        {{-- ---------------------------------------------------------------- --}}
        <div class="xl:col-span-2 space-y-6">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                    <div>
                        <h3 class="font-semibold text-gray-800">Script</h3>
                        <p class="text-xs text-gray-500 mt-0.5">
                            Each line owns one frame. Its window is cut to the measured take, so length here is length on screen.
                        </p>
                    </div>
                    {{-- Compared against the NARRATION budget, not the target:
                         the quiz tones and countdowns spend seconds outside the
                         voice, so a 29s script is already several seconds long
                         for a 30s cut. --}}
                    <div class="text-right shrink-0">
                        <div class="text-xs text-gray-400">Estimated narration</div>
                        <div class="text-sm font-semibold {{ $this->estimatedSeconds() > $this->narrationBudget() ? 'text-amber-600' : 'text-gray-700' }}">
                            ~{{ $this->estimatedSeconds() }}s <span class="font-normal text-gray-400">/ {{ $this->narrationBudget() }}s</span>
                        </div>
                    </div>
                </div>

                <div class="divide-y divide-gray-100">
                    @foreach ($template['lines'] as $line)
                        @php
                            $key = $line['key'];
                            $measured = $creative->vo_manifest[$key]['seconds'] ?? null;
                        @endphp
                        <div class="px-5 py-4">
                            <div class="flex items-baseline justify-between gap-3 mb-1.5">
                                <label class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                                    {{ $line['label'] }}
                                    <span class="ml-1 font-normal normal-case text-gray-400">{{ $line['frame'] }}</span>
                                </label>
                                @if ($measured)
                                    <span class="text-xs text-gray-400 shrink-0" title="Measured from the actual take, silence-trimmed">
                                        {{ number_format($measured, 2) }}s measured
                                    </span>
                                @endif
                            </div>

                            <input type="text" wire:model.blur="lines.{{ $key }}" maxlength="{{ $line['max'] }}" @disabled($busy)
                                   class="w-full rounded-lg border-gray-300 text-sm focus:border-purple-500 focus:ring-purple-500 disabled:bg-gray-50" />

                            <p class="text-xs text-gray-400 mt-1.5 leading-relaxed">{{ $line['hint'] }}</p>
                            @error("lines.$key") <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Options, grouped as the registry declares them --}}
            @foreach ($this->optionGroups() as $group => $definitions)
                <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                    <div class="px-5 py-3 border-b border-gray-100">
                        <h3 class="font-semibold text-gray-800 text-sm">{{ $group }}</h3>
                    </div>
                    <div class="p-5 grid grid-cols-1 md:grid-cols-2 gap-5">
                        @foreach ($definitions as $key => $definition)
                            <div class="{{ in_array($definition['type'], ['textarea', 'shots'], true) ? 'md:col-span-2' : '' }}">
                                <label class="block text-xs font-medium text-gray-500 mb-1">{{ $definition['label'] }}</label>

                                @switch ($definition['type'])
                                    @case ('interval')
                                        <select wire:model.blur="options.{{ $key }}" @disabled($busy)
                                                class="w-full rounded-lg border-gray-300 text-sm focus:border-purple-500 focus:ring-purple-500 disabled:bg-gray-50">
                                            @foreach ($this->registry()->intervalOptions() as $value => $label)
                                                <option value="{{ $value }}">{{ $label }} ({{ \App\Services\AdStudio\AdTemplateRegistry::INTERVALS[$value]['semitones'] }} st)</option>
                                            @endforeach
                                        </select>
                                        @break

                                    @case ('shot')
                                        <select wire:model.blur="options.{{ $key }}" @disabled($busy)
                                                class="w-full rounded-lg border-gray-300 text-sm focus:border-purple-500 focus:ring-purple-500 disabled:bg-gray-50">
                                            @foreach (\App\Services\AdStudio\AdTemplateRegistry::SHOTS as $file => $shot)
                                                <option value="{{ $file }}">
                                                    {{ $shot['label'] }}@if (! $shot['press_target']) — no tap target @endif
                                                </option>
                                            @endforeach
                                        </select>
                                        @break

                                    @case ('shots')
                                        <div class="flex flex-wrap gap-3">
                                            @foreach (\App\Services\AdStudio\AdTemplateRegistry::SHOTS as $file => $shot)
                                                <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                                                    <input type="checkbox" value="{{ $file }}" wire:model.blur="options.{{ $key }}" @disabled($busy)
                                                           class="rounded border-gray-300 text-purple-600 focus:ring-purple-500" />
                                                    {{ $shot['label'] }}
                                                </label>
                                            @endforeach
                                        </div>
                                        @break

                                    @case ('voice')
                                        <select wire:model.blur="options.{{ $key }}" @disabled($busy)
                                                class="w-full rounded-lg border-gray-300 text-sm focus:border-purple-500 focus:ring-purple-500 disabled:bg-gray-50">
                                            @foreach (config('ad_studio.tts.voices') as $value => $label)
                                                <option value="{{ $value }}">{{ $label }}</option>
                                            @endforeach
                                        </select>
                                        @break

                                    @case ('color')
                                        <div class="flex items-center gap-2">
                                            <input type="color" wire:model.blur="options.{{ $key }}" @disabled($busy)
                                                   class="h-9 w-12 rounded border border-gray-300 bg-white p-1 disabled:opacity-50" />
                                            <input type="text" wire:model.blur="options.{{ $key }}" @disabled($busy)
                                                   class="flex-1 rounded-lg border-gray-300 text-sm font-mono focus:border-purple-500 focus:ring-purple-500 disabled:bg-gray-50" />
                                        </div>
                                        @break

                                    @case ('textarea')
                                        <textarea wire:model.blur="options.{{ $key }}" rows="4" @disabled($busy)
                                                  class="w-full rounded-lg border-gray-300 text-sm focus:border-purple-500 focus:ring-purple-500 disabled:bg-gray-50"></textarea>
                                        @break

                                    @default
                                        <input type="text" wire:model.blur="options.{{ $key }}" @disabled($busy)
                                               @if (isset($definition['max'])) maxlength="{{ $definition['max'] }}" @endif
                                               class="w-full rounded-lg border-gray-300 text-sm focus:border-purple-500 focus:ring-purple-500 disabled:bg-gray-50" />
                                @endswitch

                                @isset ($definition['hint'])
                                    <p class="text-xs text-gray-400 mt-1.5 leading-relaxed">{{ $definition['hint'] }}</p>
                                @endisset
                                @error("options.$key") <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach

            <div>
                <button wire:click="resetToTemplate" @disabled($busy)
                        wire:confirm="Reset every line and option to the shipped template copy?"
                        class="text-xs text-gray-500 hover:text-red-600 disabled:opacity-40">
                    Reset to the shipped template copy
                </button>
            </div>
        </div>

        {{-- ---------------------------------------------------------------- --}}
        {{-- Preview + the cut                                                 --}}
        {{-- ---------------------------------------------------------------- --}}
        <div class="space-y-6">

            @if ($creative->hasRender())
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="px-5 py-3 border-b border-gray-100 flex items-center justify-between">
                        <h3 class="font-semibold text-gray-800 text-sm">Render</h3>
                        <a href="{{ route('admin.ad-studio.download', $creative) }}"
                           class="inline-flex items-center gap-1.5 text-xs text-purple-700 hover:text-purple-900">
                            <i data-lucide="download" class="w-3.5 h-3.5"></i> Download
                        </a>
                    </div>
                    <video controls preload="metadata" class="w-full bg-black"
                           src="{{ route('admin.ad-studio.watch', $creative) }}"></video>
                    <div class="px-5 py-3 text-xs text-gray-500">
                        {{ $creative->duration_seconds }}s ·
                        {{ number_format(($creative->render_bytes ?? 0) / 1048576, 1) }} MB ·
                        rendered {{ $creative->rendered_at?->diffForHumans() }}
                        @if ($creative->status === \App\Models\AdCreative::STATUS_DRAFT)
                            <span class="block mt-1 text-amber-600">Edited since this render — rebuild to pick up the changes.</span>
                        @endif
                    </div>
                </div>
            @endif

            @if ($creative->isRenderable() && ! $busy)
                <button wire:click="queueRender"
                        class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-lg border border-purple-200 bg-purple-50 text-sm text-purple-700 hover:bg-purple-100">
                    <i data-lucide="clapperboard" class="w-4 h-4"></i>
                    Render without rebuilding
                </button>
            @endif

            {{-- Snapshot strip: one frame per beat of the cut. --}}
            @if ($this->snapshots())
                <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                    <div class="px-5 py-3 border-b border-gray-100">
                        <h3 class="font-semibold text-gray-800 text-sm">Frames</h3>
                        <p class="text-xs text-gray-500 mt-0.5">One still per beat, straight from the composition.</p>
                    </div>
                    <div class="p-4 grid grid-cols-3 gap-2">
                        @foreach ($this->snapshots() as $file)
                            <img src="{{ route('admin.ad-studio.snapshot', [$creative, $file]) }}"
                                 alt="Frame preview" loading="lazy"
                                 class="w-full rounded border border-gray-200 bg-black" />
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- The plan. This is the panel's most useful readout: it shows the
                 operator exactly where their words landed. --}}
            @if (! empty($timings['frames']))
                <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                    <div class="px-5 py-3 border-b border-gray-100">
                        <h3 class="font-semibold text-gray-800 text-sm">The cut</h3>
                        <p class="text-xs text-gray-500 mt-0.5">
                            Script runs {{ $timings['natural'] ?? '—' }}s naturally; planned to {{ $timings['total'] ?? '—' }}s.
                        </p>
                    </div>
                    <div class="divide-y divide-gray-100">
                        @foreach ($timings['frames'] as $frame)
                            <div class="px-5 py-2.5 flex items-center gap-3">
                                <div class="w-16 shrink-0 text-xs font-mono text-gray-400">{{ number_format($frame['start'], 2) }}s</div>
                                <div class="flex-1 min-w-0">
                                    <div class="text-xs text-gray-700 truncate">{{ $frame['id'] }}</div>
                                    <div class="mt-1 h-1.5 rounded-full bg-purple-100 overflow-hidden">
                                        <div class="h-full bg-purple-500"
                                             style="width: {{ min(100, ($frame['duration'] / max(0.01, $timings['total'])) * 100 * 6) }}%"></div>
                                    </div>
                                </div>
                                <div class="w-12 shrink-0 text-right text-xs font-mono text-gray-500">{{ number_format($frame['duration'], 2) }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Where the artefact actually is, for anyone who wants to open it
                 in the CLI, snapshot it, or fork it by hand. --}}
            @if ($creative->project_dir)
                <div class="rounded-xl border border-gray-200 bg-gray-50 px-5 py-4 text-xs text-gray-500 leading-relaxed">
                    <span class="font-semibold text-gray-700">Project on disk</span><br />
                    <code class="text-gray-600 break-all">{{ $creative->project_dir }}</code>
                    <p class="mt-2">
                        An ordinary HyperFrames project — open it with the CLI to preview, snapshot or hand-edit.
                        Rebuilding from this panel overwrites it.
                    </p>
                </div>
            @endif
        </div>
    </div>
</div>
