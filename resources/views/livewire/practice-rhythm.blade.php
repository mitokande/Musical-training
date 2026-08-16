<div>
    <!-- VexFlow -->
    @include('livewire.partials.practice-i18n')
    <script src="https://cdn.jsdelivr.net/npm/vexflow@4.2.2/build/cjs/vexflow.js"></script>

    <!-- Main Content -->
    <main class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-6">

        @if(!$currentPractice)
            <div class="card p-12 text-center">
                <h3 class="text-lg font-semibold text-gray-900 mb-2">{{ __('app.practice_ui.common.no_exercises') }}</h3>
                <p class="text-gray-500 mb-4">{{ __('app.practice_ui.rhythm.empty') }}</p>
                <a href="/exercise-setup" class="btn-primary text-white font-semibold py-2.5 px-6 rounded-lg inline-flex items-center gap-2">
                    <i data-lucide="settings-2" class="w-4 h-4"></i> {{ __('app.practice_ui.common.adjust_settings') }}
                </a>
            </div>
        @else

        @php
            $correctNotes = $currentPractice->note_values ?? [];
            $correctStr   = implode(',', $correctNotes);

            // Build answer options (dictation mode)
            $allOptions = [['value' => $correctStr, 'notes' => $correctNotes]];
            foreach ($currentPractice->other_options ?? [] as $opt) {
                $notes = is_array($opt) ? $opt : explode(',', $opt);
                $allOptions[] = ['value' => implode(',', $notes), 'notes' => $notes];
            }
            shuffle($allOptions);
        @endphp

        <!-- Hidden data element to pass PHP vars to JS -->
        <div id="rhythmModeData"
             data-mode="{{ $rhythmMode }}"
             data-time-sig="{{ $currentPractice->time_signature ?? '4/4' }}"
             data-tempo="{{ $currentPractice->tempo ?? 80 }}"
             data-notes="{{ $correctStr }}"
             data-metronome="{{ $metronome ? '1' : '0' }}"
             class="hidden"></div>

        <div class="card overflow-hidden mb-6">
            <!-- Header -->
            <div class="hero-gradient p-6">
                <div class="relative flex items-center justify-center">
                    <a href="{{ locale_url('/learn') }}" class="absolute left-0 top-1/2 -translate-y-1/2 w-12 h-12 flex items-center justify-center rounded-xl bg-white/10 hover:bg-white/20 text-white transition-all">
                        <i data-lucide="arrow-left" class="w-6 h-6"></i>
                    </a>
                    <div class="text-center">
                        <h1 class="text-xl font-bold text-white">
                            {{ $rhythmMode === 'reading' ? 'Rhythmic Reading' : ($rhythmMode === 'build' ? 'Rhythm Dictation' : 'Rhythm Recognition') }}
                        </h1>
                        <p class="text-white/80 text-sm">
                            {{ $rhythmMode === 'reading' ? 'Tap the rhythm you see on the staff' : ($rhythmMode === 'build' ? 'Listen, then build the rhythm you heard' : 'Listen and identify the rhythm pattern') }}
                        </p>
                    </div>
                    <div class="absolute right-0 top-1/2 -translate-y-1/2">
                        <span class="inline-flex items-center gap-1.5 rounded-xl bg-white/10 px-3 py-2 text-sm font-semibold text-white/90 ring-1 ring-white/20">
                            {{ $currentPracticeIndex + 1 }} / {{ count($practices) }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="p-6 sm:p-8">

                @if($rhythmMode === 'reading')
                {{-- ─────────────────────────────────────────── --}}
                {{-- READING MODE                                --}}
                {{-- ─────────────────────────────────────────── --}}

                <!-- Staff (notes always visible) -->
                <div class="mb-5">
                    <div id="rhythm-staff-container"
                         class="w-full border-2 border-gray-200 rounded-xl bg-white overflow-hidden transition-colors duration-300"
                         style="min-height:160px; display:flex; align-items:center;">
                        <div id="rhythm-staff-output" class="w-full"></div>
                    </div>
                </div>

                <!-- Time / tempo info -->
                <div class="text-center text-sm text-gray-500 mb-6">
                    <span class="font-bold text-gray-700 text-base">{{ $currentPractice->time_signature }}</span>
                    <span class="mx-2 text-gray-300">•</span>
                    {{ __('app.practice_ui.rhythm.tempo_label') }} <span class="font-semibold text-gray-700">{{ $currentPractice->tempo }} BPM</span>
                </div>

                <!-- Play button + Next button -->
                <div class="card p-5 mb-5">
                    <div class="flex flex-col items-center gap-3">
                        <div class="flex flex-wrap justify-center gap-3">
                            <button id="playButton"
                                class="btn-primary text-white font-semibold py-3 px-5 sm:px-8 rounded-lg flex items-center gap-2 hover:shadow-lg transition-shadow"
                                data-notes="{{ $correctStr }}"
                                data-tempo="{{ $currentPractice->tempo }}"
                                data-time-sig="{{ $currentPractice->time_signature }}"
                                data-metronome="1">
                                <i data-lucide="play" class="w-5 h-5"></i>
                                {{ __('app.practice_ui.common.play') }}
                            </button>
                            @if ($currentPracticeIndex < (count($practices) - 1))
                                <button id="nextPracticeBtn" wire:click="getNextPractice"
                                    class="font-semibold py-3 px-5 sm:px-8 rounded-lg flex items-center gap-2 bg-blue-100 text-blue-700 border-2 border-blue-300 hover:bg-blue-200"
                                    style="display:none">
                                    <i data-lucide="arrow-right" class="w-5 h-5"></i> {{ __('app.practice_ui.common.next') }}
                                </button>
                            @else
                                <a href="{{ locale_url('/learn') }}" id="nextPracticeBtn"
                                    class="font-semibold py-3 px-5 sm:px-8 rounded-lg flex items-center gap-2 bg-blue-100 text-blue-700 border-2 border-blue-300 hover:bg-blue-200"
                                    style="display:none">
                                    <i data-lucide="check" class="w-5 h-5"></i> {{ __('app.practice_ui.common.finish') }}
                                </a>
                            @endif
                        </div>
                        <p id="playStatus" class="text-sm text-gray-500">{{ __('app.practice_js.press_play_metronome') }}</p>
                        <p class="text-xs text-gray-400 -mt-1">
                            {{-- One key: the two shortcuts sit mid-sentence in English
                                 but before the verb in Turkish. --}}
                            @php $rhKbd = 'px-1.5 py-0.5 bg-gray-100 border border-gray-300 rounded text-xs font-mono'; @endphp
                            {!! __('app.practice_ui.rhythm.kbd_hint', [
                                'tab' => '<kbd class="'.$rhKbd.'">Tab</kbd>',
                                'space' => '<kbd class="'.$rhKbd.'">'.__('app.practice_ui.common.key_space').'</kbd>',
                            ]) !!}
                        </p>

                        <!-- Tap Button — visible from start, enabled only when rhythm is playing -->
                        <button id="tapButton"
                            class="w-full max-w-[16rem] h-20 rounded-2xl font-bold text-xl select-none transition-all
                                   bg-gradient-to-b from-amber-400 to-amber-500 text-white shadow-md
                                   active:scale-95"
                            style="touch-action: manipulation; opacity: 0.35; cursor: not-allowed;"
                            disabled>
                            <i data-lucide="hand" class="w-6 h-6 inline mr-2"></i>
                            TAP
                        </button>
                    </div>
                </div>

                <!-- Feedback -->
                <div id="feedbackMessage" class="p-4 rounded-lg text-center font-medium" style="display:none"></div>

                @elseif($rhythmMode === 'build')
                {{-- ─────────────────────────────────────────── --}}
                {{-- BUILD MODE (interactive builder)            --}}
                {{-- ─────────────────────────────────────────── --}}

                {{-- Built rhythm on a single-line staff --}}
                <div id="rhythmTable"
                     class="min-h-[110px] w-full bg-white border-2 border-dashed border-gray-300 rounded-xl p-2 overflow-x-auto flex items-center justify-center mb-3"></div>
                {{-- Correct-answer reveal (shown only after an incorrect Check) --}}
                <div id="rhythmReveal" class="hidden mb-4">
                    <p class="text-xs font-semibold text-gray-500 text-center mb-1">{{ __('app.practice_ui.rhythm.correct_rhythm') }}</p>
                    <div id="rhythmRevealRow"
                         class="w-full bg-green-50 border border-green-200 rounded-xl p-2 overflow-x-auto flex items-center justify-center"></div>
                </div>

                {{-- Time signature / tempo line --}}
                <div class="text-center text-sm text-gray-500 mb-2">
                    <span class="text-lg font-bold text-gray-800">{{ $currentPractice->time_signature ?? '4/4' }}</span>
                    <span class="mx-1">·</span>{{ $currentPractice->tempo ?? 80 }} BPM
                    <span class="mx-1">·</span>{{ $currentPractice->bars ?? 1 }} bar(s)
                </div>

                {{-- Play button --}}
                <div class="card p-5 mb-4">
                    <div class="flex flex-col items-center">
                        <div class="flex flex-wrap justify-center gap-3 mb-3">
                            <button id="playButton"
                                class="btn-primary text-white font-semibold py-3 px-5 sm:px-8 rounded-lg flex items-center gap-2 hover:shadow-lg transition-shadow"
                                data-notes="{{ $correctStr }}"
                                data-tempo="{{ $currentPractice->tempo ?? 80 }}"
                                data-time-sig="{{ $currentPractice->time_signature ?? '4/4' }}">
                                <i data-lucide="play" class="w-5 h-5"></i>
                                {{ __('app.practice_ui.rhythm.play_rhythm') }}
                            </button>
                            @if ($currentPracticeIndex < (count($practices) - 1))
                                <button id="nextPracticeBtn" wire:click="getNextPractice"
                                    class="font-semibold py-3 px-5 sm:px-8 rounded-lg flex items-center gap-2 bg-blue-100 text-blue-700 border-2 border-blue-300 hover:bg-blue-200"
                                    style="display:none">
                                    <i data-lucide="arrow-right" class="w-5 h-5"></i> {{ __('app.practice_ui.common.next') }}
                                </button>
                            @else
                                <a href="{{ locale_url('/learn') }}" id="nextPracticeBtn"
                                    class="font-semibold py-3 px-5 sm:px-8 rounded-lg flex items-center gap-2 bg-blue-100 text-blue-700 border-2 border-blue-300 hover:bg-blue-200"
                                    style="display:none">
                                    <i data-lucide="check" class="w-5 h-5"></i> {{ __('app.practice_ui.common.finish') }}
                                </a>
                            @endif
                        </div>
                        <p id="playStatus" class="text-sm text-gray-500">{{ __('app.practice_ui.rhythm.listen_build') }}</p>
                    </div>
                </div>

                <p class="text-sm text-gray-500 mb-3 text-center">{{ __('app.practice_ui.rhythm.rebuild_hint') }}</p>

                {{-- Bar-fill meter --}}
                <div class="max-w-xs mx-auto mb-4">
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-xs font-medium text-gray-500">{{ __('app.practice_ui.rhythm.bar') }}</span>
                        <span id="rhythmFillLabel" class="text-xs font-semibold text-gray-600">0 / 0 beats</span>
                    </div>
                    <div class="h-2 w-full rounded-full bg-gray-200 overflow-hidden">
                        <div id="rhythmFillBar" class="h-full rounded-full bg-amber-400 transition-all duration-200" style="width:0%"></div>
                    </div>
                </div>

                {{-- Builder palette + controls --}}
                <div id="answerOptions"
                     data-target="{{ $correctStr }}"
                     data-practice-id="{{ $currentPractice->id }}"
                     data-bars="{{ $currentPractice->bars ?? 1 }}"
                     data-allowed="{{ implode(',', $currentPractice->allowed_values ?? []) }}">
                    {{-- Note palette (filled by JS from RHYTHM_GLYPHS) --}}
                    <div id="rhythmPalette" class="flex flex-wrap items-stretch justify-center gap-2 mb-4"></div>
                    {{-- Controls --}}
                    <div class="flex flex-wrap items-center justify-center gap-2">
                        <button type="button" id="rhythmPlayMineBtn"
                                class="inline-flex items-center gap-1.5 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-600 hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed">
                            <i data-lucide="volume-2" class="w-4 h-4"></i> {{ __('app.practice_ui.rhythm.play_mine') }}
                        </button>
                        <button type="button" id="rhythmDeleteBtn"
                                class="inline-flex items-center gap-1.5 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-600 hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed">
                            <i data-lucide="delete" class="w-4 h-4"></i> {{ __('app.practice_ui.common.delete_last') }}
                        </button>
                        <button type="button" id="rhythmClearBtn"
                                class="inline-flex items-center gap-1.5 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-600 hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed">
                            <i data-lucide="eraser" class="w-4 h-4"></i> {{ __('app.practice_ui.common.clear') }}
                        </button>
                        <button type="button" id="rhythmCheckBtn"
                                class="btn-primary inline-flex items-center gap-1.5 rounded-lg px-6 py-2.5 text-sm font-semibold text-white hover:shadow-lg transition-shadow disabled:opacity-40 disabled:cursor-not-allowed">
                            <i data-lucide="check" class="w-4 h-4"></i> {{ __('app.practice_ui.common.check') }}
                        </button>
                    </div>
                </div>

                <div id="feedbackMessage" class="mt-4 p-4 rounded-lg text-center font-medium hidden"></div>

                @else
                {{-- ─────────────────────────────────────────── --}}
                {{-- DICTATION MODE                              --}}
                {{-- ─────────────────────────────────────────── --}}

                <!-- Time signature display -->
                <div class="w-full bg-gray-50 border-2 border-dashed border-gray-200 rounded-xl flex items-center justify-center mb-6 py-5">
                    <div class="text-center">
                        <p class="text-sm text-gray-500 mb-1">{{ __('app.practice_ui.rhythm.time_signature') }}</p>
                        <div class="text-5xl font-bold text-gray-800">{{ $currentPractice->time_signature }}</div>
                        <p class="text-sm text-gray-400 mt-1">{{ __('app.practice_ui.rhythm.tempo_label') }} {{ $currentPractice->tempo }} BPM • {{ trans_choice('app.practice_ui.rhythm.bars_count', $currentPractice->bars, ['count' => $currentPractice->bars]) }}</p>
                    </div>
                </div>

                <!-- Play Button -->
                <div class="card p-5 mb-6">
                    <div class="flex flex-col items-center">
                        <div class="flex flex-wrap justify-center gap-3 mb-3">
                            <button id="playButton"
                                class="btn-primary text-white font-semibold py-3 px-5 sm:px-8 rounded-lg flex items-center gap-2 hover:shadow-lg transition-shadow"
                                data-notes="{{ $correctStr }}"
                                data-tempo="{{ $currentPractice->tempo }}"
                                data-time-sig="{{ $currentPractice->time_signature }}"
                                data-metronome="{{ $metronome ? '1' : '0' }}">
                                <i data-lucide="play" class="w-5 h-5"></i>
                                {{ __('app.practice_ui.rhythm.play_rhythm') }}
                            </button>
                            @if ($currentPracticeIndex < (count($practices) - 1))
                                <button id="nextPracticeBtn" wire:click="getNextPractice"
                                    class="font-semibold py-3 px-5 sm:px-8 rounded-lg flex items-center gap-2 bg-blue-100 text-blue-700 border-2 border-blue-300 hover:bg-blue-200"
                                    style="display:none">
                                    <i data-lucide="arrow-right" class="w-5 h-5"></i> {{ __('app.practice_ui.common.next') }}
                                </button>
                            @else
                                <a href="{{ locale_url('/learn') }}" id="nextPracticeBtn"
                                    class="font-semibold py-3 px-5 sm:px-8 rounded-lg flex items-center gap-2 bg-blue-100 text-blue-700 border-2 border-blue-300 hover:bg-blue-200"
                                    style="display:none">
                                    <i data-lucide="check" class="w-5 h-5"></i> {{ __('app.practice_ui.common.finish') }}
                                </a>
                            @endif
                        </div>
                        <p id="playStatus" class="text-sm text-gray-500">{{ __('app.practice_ui.rhythm.listen_pattern') }}</p>
                    </div>
                </div>

                <!-- Answer Options: 4 VexFlow staves in 2×2 grid -->
                <p class="text-sm text-gray-500 mb-3 text-center">{{ __('app.practice_ui.rhythm.which_rhythm') }}</p>
                <div id="answerOptions" class="grid grid-cols-1 sm:grid-cols-2 gap-3"
                     data-target="{{ $correctStr }}"
                     data-practice-id="{{ $currentPractice->id }}">
                    @foreach($allOptions as $optIdx => $opt)
                        <button class="answer-btn card p-3 text-left transition-all hover:shadow-md border-2 border-transparent rounded-xl"
                                data-answer="{{ $opt['value'] }}"
                                data-notes='{{ json_encode(array_values($opt['notes'])) }}'>
                            <div class="staff-container w-full rounded overflow-hidden h-[104px] min-h-[104px] sm:h-[130px] sm:min-h-[130px]"></div>
                        </button>
                    @endforeach
                </div>

                <div id="feedbackMessage" class="mt-4 p-4 rounded-lg text-center font-medium" style="display:none"></div>

                @endif

            </div>
        </div>

        <!-- Score / XP row -->
        <div class="flex items-center justify-center gap-4 text-sm text-gray-500">
            <span class="flex items-center gap-1"><i data-lucide="sparkles" class="w-4 h-4 text-yellow-500"></i>+<span id="xpEarned">0</span> XP</span>
            <span>•</span>
            <span><span id="scoreCorrect">0</span> / <span id="scoreTotal">0</span> {{ __('app.practice_ui.common.correct') }}</span>
        </div>

        <script>
        // ── Shared helpers ────────────────────────────────────────────────────────

        const noteDurations = {
            whole: 4, whole_rest: 4,
            'dotted-half': 3,
            half: 2, half_rest: 2,
            'dotted-quarter': 1.5,
            quarter: 1, quarter_rest: 1,
            'dotted-eighth': 0.75,
            eighth: 0.5, eighth_rest: 0.5,
            sixteenth: 0.25,
            'triplet-quarter': 2,   // legacy: one token = full 3-note group (2 beats total)
            'triplet-eighth' : 1/3, // one token = one note; three consecutive tokens = 1 beat
        };

        // VexFlow duration codes (key: note value string, value: VexFlow duration)
        const vfDurations = {
            'whole': 'w',       'half': 'h',       'quarter': 'q',
            'eighth': '8',      'sixteenth': '16',
            'whole_rest': 'wr', 'half_rest': 'hr', 'quarter_rest': 'qr',
            'eighth_rest': '8r','dotted-half': 'hd','dotted-quarter': 'qd',
            'dotted-eighth': '8d',
        };

        // ── Beat-aligned beam builder ─────────────────────────────────────────────
        // Shared by renderRhythmStave (recognition/reading) so both modes use
        // identical grouping logic.  expandedNames is parallel to staveNotes — one
        // name per VF.StaveNote object (triplet-quarter expands to 3 'triplet-quarter-note').
        //
        // Grouping rules per time signature (VISUAL groups, not metric beats):
        //   x/4 simple   → group by quarter note    (12 twelfths)
        //   x/8 compound → group by dotted-quarter  (18 twelfths)
        //   x/2          → group by quarter note    (12 twelfths) — same as x/4,
        //                  beams never cross a quarter-note boundary in alla breve.
        function buildRhythmBeams(VF, staveNotes, expandedNames, timeSig) {
            const den = parseInt((timeSig || '4/4').split('/')[1]) || 4;
            const beatTicks = den === 8 ? 18 : 12; // visual group: always 12 for x/4 AND x/2
            const tw = {
                'whole': 48, 'dotted-half': 36, 'half': 24, 'dotted-quarter': 18,
                'quarter': 12, 'dotted-eighth': 9, 'eighth': 6, 'triplet-eighth': 4,
                'sixteenth': 3, 'whole_rest': 48, 'half_rest': 24,
                'quarter_rest': 12, 'eighth_rest': 6,
                'triplet-quarter-note': 8, // each of 3 expanded triplet-quarter notes
                '__bar__': 0,              // inserted barline tickable — zero duration
            };
            const isBeamable = v => v === 'eighth' || v === 'dotted-eighth' ||
                                    v === 'sixteenth' || v === 'triplet-eighth';
            const beamGroups = [];
            let run = [], tripletRun = [], curBeat = -1, pos = 0;
            const flushRun = () => { if (run.length >= 2) beamGroups.push(run.slice()); run = []; };

            expandedNames.forEach((v, i) => {
                const dur  = tw[v] ?? 12;
                const beat = Math.floor(pos / beatTicks);
                if (beat !== curBeat) flushRun();
                if (v === 'triplet-eighth') {
                    tripletRun.push(staveNotes[i]);
                    if (tripletRun.length === 3) {
                        beamGroups.push(tripletRun.slice());
                        tripletRun = [];
                    }
                } else if (isBeamable(v)) {
                    run.push(staveNotes[i]);
                } else {
                    flushRun();
                }
                curBeat = beat;
                pos += dur;
            });
            flushRun();

            return beamGroups
                .map(g => { try { return new VF.Beam(g); } catch(e) { return null; } })
                .filter(Boolean);
        }

        // renderRhythmStave(container, noteArray, timeSig, colorMap, h)
        // h: optional SVG/container height (default 130). For reading mode pass 161.
        function renderRhythmStave(container, noteArray, timeSig, colorMap, h) {
            if (typeof Vex === 'undefined' || !container || !noteArray.length) return null;
            container.innerHTML = '';

            const VF     = Vex.Flow;
            let width = Math.max(container.clientWidth || 400, 200);
            if (window.innerWidth < 640 && container.id === 'rhythm-staff-output') {
                // Mobile: the main staff gets per-note room instead of being squeezed —
                // the responsive-notation fitter makes the container horizontally
                // swipeable. Answer-option / reveal mini staves keep container width.
                width = Math.max(width, 90 + noteArray.length * 50);
            }
            // Answer-option staves shrink 20% on mobile (single-column layout).
            const height = h || ((window.innerWidth < 640 && container.closest('#answerOptions')) ? 104 : 130);
            // B4 (line 2) sits at staveY + space_above*10 + 2*10 = staveY+60. Centre it:
            const staveY = Math.max(Math.round(height / 2 - 60), 5);

            const renderer = new VF.Renderer(container, VF.Renderer.Backends.SVG);
            renderer.resize(width, height);
            const context = renderer.getContext();

            // Full 5-line stave so barlines are visible; extra lines hidden via SVG after draw.
            const stave = new VF.Stave(5, staveY, width - 10);
            stave.addTimeSignature(timeSig);
            stave.setContext(context).draw();

            // Hide stave lines 0,1,3,4 — keep only line 2 (B4, y = staveY+60).
            const keepLineY = staveY + 60;
            const svgEl = container.querySelector('svg');
            if (svgEl) {
                svgEl.querySelectorAll('path').forEach(path => {
                    const d = path.getAttribute('d') || '';
                    const m = d.match(/[Mm][\s,]*([-\d.]+)[\s,]+([-\d.]+)[\s,]*[Ll][\s,]*([-\d.]+)[\s,]+([-\d.]+)/);
                    if (!m) return;
                    const y1 = parseFloat(m[2]), y2 = parseFloat(m[4]);
                    const x1 = parseFloat(m[1]), x2 = parseFloat(m[3]);
                    // Horizontal line spanning > half the stave width → a stave line
                    if (Math.abs(y1 - y2) < 1 && Math.abs(x2 - x1) > (width * 0.4) && Math.abs(y1 - keepLineY) > 2) {
                        path.style.display = 'none';
                    }
                });
            }

            const parts = timeSig.split('/');
            const numBeats  = parseInt(parts[0]) || 4;
            const beatValue = parseInt(parts[1]) || 4;

            // Expand note array: triplet tokens become 3 StaveNotes + track Tuplet groups.
            // expandedNames mirrors staveNotes (1 entry per VF note) for beat-aligned beaming.
            const staveNotes    = [];
            const expandedNames = [];
            const tuplets       = [];
            let tripletRun      = [];
            // Draw a barline at every bar boundary so multi-bar options and reveals
            // show their measure divisions — VexFlow only renders the terminal barline
            // of a stave, so a 2-bar answer would otherwise read as one long measure.
            const barTw = beatValue > 0 ? (48 * numBeats / beatValue) : 0;
            let cumTw = 0;
            noteArray.forEach((noteName, idx) => {
                const color = colorMap && colorMap[idx] ? colorMap[idx] : null;
                if (barTw > 0 && cumTw > 0 && cumTw % barTw === 0) {
                    try {
                        staveNotes.push(new VF.BarNote());
                        expandedNames.push('__bar__');
                    } catch(e) {}
                }
                cumTw += (RHYTHM_TWELFTHS[noteName] ?? (noteName === 'triplet-quarter' ? 24 : 0));
                if (noteName === 'triplet-eighth') {
                    // Generator emits 3 individual triplet-eighth tokens per beat group;
                    // collect them and create one Tuplet when the group is complete.
                    const n = new VF.StaveNote({ keys: ['b/4'], duration: '8', stem_direction: -1 });
                    if (color) n.setStyle({ fillStyle: color, strokeStyle: color });
                    tripletRun.push(n);
                    staveNotes.push(n);
                    expandedNames.push('triplet-eighth');
                    if (tripletRun.length === 3) {
                        tuplets.push(new VF.Tuplet(tripletRun.slice(), { numNotes: 3, notesOccupied: 2, bracketed: true, ratioed: false }));
                        tripletRun = [];
                    }
                } else if (noteName === 'triplet-quarter') {
                    // Legacy: one token = full 3-note group filling 2 beats
                    const group = [];
                    for (let t = 0; t < 3; t++) {
                        const n = new VF.StaveNote({ keys: ['b/4'], duration: 'q', stem_direction: -1 });
                        if (color) n.setStyle({ fillStyle: color, strokeStyle: color });
                        group.push(n);
                        staveNotes.push(n);
                        expandedNames.push('triplet-quarter-note'); // 8 twelfths each (2 beats ÷ 3)
                    }
                    tuplets.push(new VF.Tuplet(group, { numNotes: 3, notesOccupied: 2, bracketed: true, ratioed: false }));
                } else {
                    // Mirror the drawRhythmStaff approach (confirmed working in build mode):
                    // base VexFlow duration + explicit Dot.buildAndAttach for dotted notes.
                    // In VexFlow 4.x the 'd'-suffix alone may not render the visual dot.
                    const isDotted = noteName.startsWith('dotted-');
                    const baseKey = isDotted ? noteName.slice(7) : noteName; // 'dotted-quarter' → 'quarter'
                    const dur = vfDurations[baseKey] || vfDurations[noteName] || 'q';
                    const note = new VF.StaveNote({ keys: ['b/4'], duration: dur, stem_direction: -1 });
                    if (isDotted) {
                        try {
                            if (VF.Dot && VF.Dot.buildAndAttach) {
                                VF.Dot.buildAndAttach([note], { all: true });
                            } else {
                                note.addDot(0);
                            }
                        } catch(e) {}
                    }
                    if (color) note.setStyle({ fillStyle: color, strokeStyle: color });
                    staveNotes.push(note);
                    expandedNames.push(noteName);
                }
            });

            if (!staveNotes.length) return null;

            const voice = new VF.Voice({ numBeats, beatValue });
            voice.setMode(VF.Voice.Mode.SOFT);
            voice.addTickables(staveNotes);

            try {
                const beams = buildRhythmBeams(VF, staveNotes, expandedNames, timeSig);
                new VF.Formatter().joinVoices([voice]).format([voice], width - 70);
                voice.draw(context, stave);
                beams.forEach(b => { try { b.setContext(context).draw(); } catch(e) {} });
                tuplets.forEach(t => { try { t.setContext(context).draw(); } catch(e) {} });
            } catch(e) {
                try {
                    new VF.Formatter().joinVoices([voice]).format([voice], width - 70);
                    voice.draw(context, stave);
                    tuplets.forEach(t => t.setContext(context).draw());
                } catch(e2) {}
            }
            return staveNotes;
        }

        function playMetronomeClick(accent) {
            try {
                const actx = Tone.context.rawContext || (Tone.getContext && Tone.getContext().rawContext);
                if (!actx) return;
                const now = actx.currentTime;
                const osc  = actx.createOscillator();
                const gain = actx.createGain();
                osc.connect(gain);
                gain.connect(actx.destination);
                osc.frequency.value = accent ? 1500 : 1000;
                osc.type = 'sine';
                gain.gain.setValueAtTime(accent ? 0.28 : 0.20, now);
                gain.gain.exponentialRampToValueAtTime(0.0001, now + 0.055);
                osc.start(now);
                osc.stop(now + 0.06);
            } catch(e) {}
        }

        // ── Init dispatcher ───────────────────────────────────────────────────────

        window.initPracticeRhythm = function() {
            window._practiceGen = (window._practiceGen || 0) + 1;
            const myGen = window._practiceGen;
            if (typeof lucide !== 'undefined') lucide.createIcons();

            const modeEl = document.getElementById('rhythmModeData');
            const mode = modeEl ? modeEl.dataset.mode : 'dictation';

            if (mode === 'reading') {
                initReadingMode(myGen);
            } else if (mode === 'build') {
                initBuildMode(myGen);
            } else {
                initDictationMode(myGen);
            }
        };

        // ── DICTATION MODE ────────────────────────────────────────────────────────

        function initDictationMode(myGen) {
            const playButton      = document.getElementById('playButton');
            const playStatus      = document.getElementById('playStatus');
            const nextButton      = document.getElementById('nextPracticeBtn');
            const answerOptions   = document.getElementById('answerOptions');
            const feedbackMessage = document.getElementById('feedbackMessage');
            if (!playButton || !answerOptions) return;

            const target     = answerOptions.dataset.target;
            const practiceId = answerOptions.dataset.practiceId;
            const notes      = playButton.dataset.notes ? playButton.dataset.notes.split(',') : [];
            const tempo      = parseInt(playButton.dataset.tempo) || 80;
            const beatMs     = Math.round(60000 / tempo);
            const timeSig    = playButton.dataset.timeSig || '4/4';
            const beatsPerBar= parseInt(timeSig.split('/')[0]) || 4;
            const useMetronome = playButton.dataset.metronome === '1';
            let isAnswered   = false;

            // Render VexFlow stave inside each answer option button
            setTimeout(() => {
                if (window._practiceGen !== myGen) return;
                document.querySelectorAll('.answer-btn').forEach(btn => {
                    try {
                        const noteArr   = JSON.parse(btn.dataset.notes || '[]');
                        const container = btn.querySelector('.staff-container');
                        if (container) renderRhythmStave(container, noteArr, timeSig, null);
                    } catch(e) {}
                });
            }, 120);

            // ── Play button ──
            playButton.onclick = async function() {
                if (playButton.disabled) return;
                playButton.disabled = true;
                playButton.innerHTML = '<i data-lucide="loader-2" class="w-5 h-5 animate-spin inline"></i> ' + pt('playing');
                if (typeof lucide !== 'undefined') lucide.createIcons();

                // Audio-clock scheduling: loads sampler first, then schedules every click
                // and note at an absolute Tone.js time — no setTimeout drift or async gaps.
                const totalMs = await playRhythmAudio(notes, tempo, timeSig, useMetronome);

                setTimeout(() => {
                    if (window._practiceGen !== myGen) return;
                    playButton.disabled = false;
                    playButton.innerHTML = '<i data-lucide="play" class="w-5 h-5"></i> ' + pt('play_again');
                    if (playStatus) playStatus.textContent = pt('click_replay');
                    if (typeof lucide !== 'undefined') lucide.createIcons();
                }, totalMs + 200);
            };

            // ── Answer buttons ──
            document.querySelectorAll('.answer-btn').forEach(btn => {
                btn.onclick = async function() {
                    if (isAnswered) return;
                    const answer = this.dataset.answer;
                    document.querySelectorAll('.answer-btn').forEach(b => b.disabled = true);

                    try {
                        const response = await fetch('/api/practice/check-answer', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            },
                            body: JSON.stringify({
                                practice_id: null,
                                question_id: parseInt(practiceId),
                                answer: answer,
                                slug: 'rhythm-practice'
                            })
                        });
                        const data = await response.json();
                        isAnswered = true;

                        if (playButton) playButton.style.display = 'none';
                        if (playStatus) playStatus.style.display = 'none';
                        if (nextButton) nextButton.style.display = 'flex';

                        const scoreTotal   = document.getElementById('scoreTotal');
                        const scoreCorrect = document.getElementById('scoreCorrect');
                        const xpEarned     = document.getElementById('xpEarned');
                        if (scoreTotal) scoreTotal.textContent = parseInt(scoreTotal.textContent || 0) + 1;

                        if (data.is_correct) {
                            this.classList.add('border-green-400', 'bg-green-50');
                            if (feedbackMessage) {
                                feedbackMessage.textContent = pt('correct_short');
                                feedbackMessage.className = 'mt-4 p-4 rounded-lg text-center font-medium bg-green-100 text-green-700';
                                feedbackMessage.style.display = 'block';
                            }
                            if (scoreCorrect) scoreCorrect.textContent = parseInt(scoreCorrect.textContent || 0) + 1;
                            if (xpEarned) xpEarned.textContent = parseInt(xpEarned.textContent || 0) + 10;

                            // Re-render correct option in green
                            const colorMap = {};
                            const noteArr = JSON.parse(this.dataset.notes || '[]');
                            noteArr.forEach((_, i) => { colorMap[i] = '#16a34a'; });
                            const c = this.querySelector('.staff-container');
                            if (c) renderRhythmStave(c, noteArr, timeSig, colorMap);
                        } else {
                            this.classList.add('border-red-400', 'bg-red-50');
                            if (feedbackMessage) {
                                feedbackMessage.textContent = pt('incorrect_pattern');
                                feedbackMessage.className = 'mt-4 p-4 rounded-lg text-center font-medium bg-red-100 text-red-700';
                                feedbackMessage.style.display = 'block';
                            }

                            // Re-render selected option in red
                            const wrongNotes = JSON.parse(this.dataset.notes || '[]');
                            const redMap = {}; wrongNotes.forEach((_, i) => { redMap[i] = '#dc2626'; });
                            const wc = this.querySelector('.staff-container');
                            if (wc) renderRhythmStave(wc, wrongNotes, timeSig, redMap);

                            // Highlight correct answer in green
                            document.querySelectorAll('.answer-btn').forEach(b => {
                                if (b.dataset.answer === target) {
                                    b.classList.add('border-green-400', 'bg-green-50');
                                    const correctNotes = JSON.parse(b.dataset.notes || '[]');
                                    const greenMap = {}; correctNotes.forEach((_, i) => { greenMap[i] = '#16a34a'; });
                                    const cc = b.querySelector('.staff-container');
                                    if (cc) renderRhythmStave(cc, correctNotes, timeSig, greenMap);
                                }
                            });
                        }
                    } catch(e) {
                        document.querySelectorAll('.answer-btn').forEach(b => b.disabled = false);
                        isAnswered = false;
                    }
                };
            });
        }

        // ── READING MODE ──────────────────────────────────────────────────────────

        function setTapActive(btn, active) {
            if (!btn) return;
            btn.disabled = !active;
            btn.style.opacity  = active ? '1' : '0.35';
            btn.style.cursor   = active ? 'pointer' : 'not-allowed';
            btn.style.boxShadow = active ? '' : 'none';
        }

        function initReadingMode(myGen) {
            // Clean up previous Space-key handler if any
            if (window._rhythmSpaceCleanup) { window._rhythmSpaceCleanup(); window._rhythmSpaceCleanup = null; }

            const playButton     = document.getElementById('playButton');
            const tapButton      = document.getElementById('tapButton');
            const playStatus     = document.getElementById('playStatus');
            const nextButton     = document.getElementById('nextPracticeBtn');
            const staffOutput    = document.getElementById('rhythm-staff-output');
            const staffContainer = document.getElementById('rhythm-staff-container');
            const feedbackMsg    = document.getElementById('feedbackMessage');
            if (!playButton) return;

            const notes        = playButton.dataset.notes ? playButton.dataset.notes.split(',') : [];
            const tempo        = parseInt(playButton.dataset.tempo) || 80;
            const beatMs       = Math.round(60000 / tempo);
            const timeSig      = playButton.dataset.timeSig || '4/4';
            const rawBeatsR    = parseInt(timeSig.split('/')[0]) || 4;
            const denR         = parseInt(timeSig.split('/')[1] || 4);
            const isCompoundR  = denR === 8;
            const isAllaBreveR = denR === 2;
            // Compound (x/8): the dotted-quarter is the beat, so the displayed tempo is the
            //   dotted-quarter BPM. Click once per beat (rawBeats/3 beats per bar), not per
            //   eighth (which ran at 2× the written tempo).
            // Alla breve (x/2): bpm = half-note BPM; click every half-note beat, rawBeats per bar.
            // Simple (x/4): click every quarter note (beatMs), rawBeats per bar.
            const compoundBeatsR = Math.max(1, Math.round(rawBeatsR / 3));
            const beatsPerBar  = isCompoundR ? compoundBeatsR : rawBeatsR;
            const subdivMsR    = beatMs; // one click per beat in every meter
            const isAccentR    = isCompoundR ? (b => b % compoundBeatsR === 0) : (b => b === 0);
            // quarter-note duration reference: x/2 → beatMs/2, x/8 → 2·beatMs/3
            // (so dotted-quarter = beatMs), x/4 → beatMs.
            const noteBeatMsR  = isAllaBreveR ? beatMs / 2 : (isCompoundR ? beatMs * 2 / 3 : beatMs);

            let isAnswered      = false;
            let rhythmStartTime = null;
            let userTaps        = [];
            let endTimeout      = null;
            let playStarted     = false;

            // Tap button starts inactive until Play is pressed
            setTapActive(tapButton, false);

            // Render staff immediately (notes visible from the start)
            setTimeout(() => {
                if (window._practiceGen !== myGen) return;
                if (staffOutput) renderRhythmStave(staffOutput, notes, timeSig, null, 160);
            }, 120);

            // ── Play button ──
            playButton.onclick = async function() {
                if (playButton.disabled || isAnswered) return;

                // Reset for replay
                userTaps        = [];
                rhythmStartTime = null;
                if (endTimeout) clearTimeout(endTimeout);

                // Enable tap immediately from Play press
                playStarted = true;
                setTapActive(tapButton, true);

                if (staffOutput) renderRhythmStave(staffOutput, notes, timeSig, null, 160);
                if (staffContainer) {
                    staffContainer.style.borderColor = '';
                    staffContainer.style.backgroundColor = '';
                    staffContainer.className = 'w-full border-2 border-gray-200 rounded-xl bg-white overflow-hidden transition-colors duration-300';
                    staffContainer.style.cssText = 'min-height:160px; display:flex; align-items:center;';
                }
                if (feedbackMsg) feedbackMsg.style.display = 'none';

                playButton.disabled = true;
                playButton.innerHTML = '<i data-lucide="loader-2" class="w-5 h-5 animate-spin inline"></i> ' + pt('get_ready');
                if (typeof lucide !== 'undefined') lucide.createIcons();

                await Tone.start();

                let totalDurationMs = 0;
                for (const note of notes) totalDurationMs += (noteDurations[note] || 1) * noteBeatMsR;

                // 1-bar pre-count: beatsPerBar subdivisions at subdivMsR intervals
                for (let b = 0; b < beatsPerBar; b++) {
                    ((t, beat) => setTimeout(() => playMetronomeClick(isAccentR(beat)), t))(b * subdivMsR, b);
                }
                const startOffset = beatsPerBar * subdivMsR;

                // Metronome during rhythm: subdivisions for the full rhythm duration
                const totalSubdivs = Math.ceil(totalDurationMs / subdivMsR);
                for (let b = 0; b < totalSubdivs; b++) {
                    ((t, beat) => setTimeout(() => playMetronomeClick(isAccentR(beat)), t))(startOffset + b * subdivMsR, b);
                }

                // Record rhythm start time after count-in (taps only count from here)
                setTimeout(() => {
                    if (window._practiceGen !== myGen) return;
                    rhythmStartTime = Date.now();
                    playButton.innerHTML = '<i data-lucide="volume-2" class="w-5 h-5"></i> ' + pt('playing');
                    if (playStatus) playStatus.textContent = pt('tap_along');
                    if (typeof lucide !== 'undefined') lucide.createIcons();
                }, startOffset);

                // Deactivate tap & evaluate after rhythm ends
                endTimeout = setTimeout(() => {
                    if (window._practiceGen !== myGen) return;
                    setTapActive(tapButton, false);
                    playButton.disabled = false;
                    evaluate();
                }, startOffset + totalDurationMs + 600);
            };

            // ── Tap recording (button + Tab/Space keys) ──
            function recordTap() {
                if (isAnswered || !playStarted) return;
                if (rhythmStartTime !== null) {
                    userTaps.push(Date.now() - rhythmStartTime);
                }
                // Visual feedback from the moment Play starts (even during count-in)
                if (tapButton) {
                    tapButton.style.transform = 'scale(0.93)';
                    setTimeout(() => { if (tapButton) tapButton.style.transform = ''; }, 90);
                }
            }

            if (tapButton) {
                tapButton.addEventListener('click',      recordTap);
                tapButton.addEventListener('touchstart', e => { e.preventDefault(); recordTap(); }, { passive: false });
            }

            // Tab/Space key handler
            // Tab: starts rhythm (if not yet playing) OR taps (once play has started)
            // Space: taps only when rhythm is actively playing
            const keyHandler = e => {
                if (isAnswered) return;
                if (e.code === 'Tab') {
                    e.preventDefault();
                    if (!playStarted && !playButton.disabled) {
                        playButton.click();         // First Tab: start rhythm
                    } else if (playStarted) {
                        recordTap();                // Tab active from Play press onward
                    }
                } else if (e.code === 'Space' && rhythmStartTime !== null) {
                    e.preventDefault();
                    recordTap();
                }
            };
            document.addEventListener('keydown', keyHandler);
            window._rhythmSpaceCleanup = () => document.removeEventListener('keydown', keyHandler);

            // ── Evaluate taps ──
            function evaluate() {
                if (isAnswered) return;
                const tolerance = noteBeatMsR * 0.20; // ±20% of one quarter-note duration

                let beat = 0;
                const expected = [];
                notes.forEach((note, idx) => {
                    const dur = noteDurations[note] || 1;
                    if (note === 'triplet-quarter') {
                        // Legacy: one token = full 3-note group
                        for (let ti = 0; ti < 3; ti++) expected.push({ idx, time: (beat + ti * (2/3)) * noteBeatMsR });
                    } else if (!note.includes('_rest')) {
                        // Normal notes and individual triplet-eighth tokens: one tap per token
                        expected.push({ idx, time: beat * noteBeatMsR });
                    }
                    beat += dur;
                });

                const results  = new Array(notes.length).fill('neutral');
                const usedTaps = new Set();
                expected.forEach(exp => {
                    let best = -1, bestDiff = Infinity;
                    userTaps.forEach((t, ti) => {
                        if (usedTaps.has(ti)) return;
                        const d = Math.abs(t - exp.time);
                        if (d < bestDiff) { bestDiff = d; best = ti; }
                    });
                    if (best >= 0 && bestDiff <= tolerance) {
                        results[exp.idx] = 'correct'; usedTaps.add(best);
                    } else {
                        results[exp.idx] = 'wrong';
                    }
                });

                const allOK = expected.length > 0 && results.every(r => r !== 'wrong');

                // Re-render staff with note colours
                const colorMap = {};
                results.forEach((r, i) => {
                    if (r === 'correct') colorMap[i] = '#16a34a';
                    else if (r === 'wrong')   colorMap[i] = '#dc2626';
                });
                if (staffOutput) renderRhythmStave(staffOutput, notes, timeSig, colorMap, 160);

                // Staff border
                if (staffContainer) {
                    staffContainer.className = [
                        'w-full border-2 rounded-xl overflow-hidden transition-colors duration-300',
                        allOK ? 'border-green-400 bg-green-50' : 'border-red-400 bg-red-50',
                    ].join(' ');
                    staffContainer.style.cssText = 'min-height:160px; display:flex; align-items:center;';
                }

                // Feedback
                if (feedbackMsg) {
                    feedbackMsg.textContent = allOK
                        ? '✓ Perfect timing! All beats correct.'
                        : '✗ Not quite — green notes were tapped correctly, red ones were missed or mistimed.';
                    feedbackMsg.className = allOK
                        ? 'p-4 rounded-lg text-center font-medium bg-green-100 text-green-700'
                        : 'p-4 rounded-lg text-center font-medium bg-red-100 text-red-700';
                    feedbackMsg.style.display = 'block';
                }

                // Scores
                const scoreTotal   = document.getElementById('scoreTotal');
                const scoreCorrect = document.getElementById('scoreCorrect');
                const xpEarned     = document.getElementById('xpEarned');
                if (scoreTotal) scoreTotal.textContent = parseInt(scoreTotal.textContent || 0) + 1;
                if (allOK) {
                    if (scoreCorrect) scoreCorrect.textContent = parseInt(scoreCorrect.textContent || 0) + 1;
                    if (xpEarned) xpEarned.textContent = parseInt(xpEarned.textContent || 0) + 10;
                }

                isAnswered = true;
                if (playButton) playButton.style.display = 'none';
                if (playStatus) playStatus.style.display = 'none';
                if (nextButton) nextButton.style.display = 'flex';
            }
        }

        // ── BUILD MODE (interactive builder) ───────────────────────────────────────
        // Ported from the AI-exercises Rhythm Dictation player: a note-value palette that
        // appends onto a single-line staff, with synced metronome playback and a Check that
        // exact-matches the built sequence against the generated rhythm.

        // Note-value palette glyphs (inline SVG, inherits button colour via currentColor).
        const RHYTHM_GLYPHS = {
            'whole':          { label: 'Whole',    svg: '<ellipse cx="13" cy="26" rx="8" ry="5" fill="none" stroke="currentColor" stroke-width="2.4"/>' },
            'half':           { label: 'Half',     svg: '<ellipse cx="8" cy="30" rx="6.4" ry="4.5" transform="rotate(-25 8 30)" fill="none" stroke="currentColor" stroke-width="1.9"/><rect x="13.1" y="8" width="1.8" height="22.5" fill="currentColor"/>' },
            'quarter':        { label: 'Quarter',  svg: '<ellipse cx="8" cy="30" rx="6.4" ry="4.5" transform="rotate(-25 8 30)" fill="currentColor"/><rect x="13.1" y="8" width="1.8" height="22.5" fill="currentColor"/>' },
            'eighth':         { label: '8th',      svg: '<ellipse cx="8" cy="30" rx="6.4" ry="4.5" transform="rotate(-25 8 30)" fill="currentColor"/><rect x="13.1" y="8" width="1.8" height="22.5" fill="currentColor"/><path d="M15 8 q8 3 5 13 q3-7-5-9 z" fill="currentColor"/>' },
            'sixteenth':      { label: '16th',     svg: '<ellipse cx="8" cy="30" rx="6.4" ry="4.5" transform="rotate(-25 8 30)" fill="currentColor"/><rect x="13.1" y="6" width="1.8" height="24.5" fill="currentColor"/><path d="M15 6 q8 3 5 12 q3-6-5-8 z" fill="currentColor"/><path d="M15 13 q8 3 5 12 q3-6-5-8 z" fill="currentColor"/>' },
            'dotted-half':    { label: 'Half.',    svg: '<ellipse cx="8" cy="30" rx="6.4" ry="4.5" transform="rotate(-25 8 30)" fill="none" stroke="currentColor" stroke-width="1.9"/><rect x="13.1" y="8" width="1.8" height="22.5" fill="currentColor"/><circle cx="19" cy="30" r="2" fill="currentColor"/>' },
            'dotted-quarter': { label: 'Qtr.',     svg: '<ellipse cx="8" cy="30" rx="6.4" ry="4.5" transform="rotate(-25 8 30)" fill="currentColor"/><rect x="13.1" y="8" width="1.8" height="22.5" fill="currentColor"/><circle cx="19" cy="30" r="2" fill="currentColor"/>' },
            'dotted-eighth':  { label: '8th.',     svg: '<ellipse cx="8" cy="30" rx="6.4" ry="4.5" transform="rotate(-25 8 30)" fill="currentColor"/><rect x="13.1" y="8" width="1.8" height="22.5" fill="currentColor"/><path d="M15 8 q8 3 5 13 q3-7-5-9 z" fill="currentColor"/><circle cx="22" cy="30" r="2" fill="currentColor"/>' },
            'whole_rest':     { label: 'W. rest',  svg: '<line x1="4" y1="17" x2="22" y2="17" stroke="currentColor" stroke-width="1.2"/><rect x="7" y="17" width="12" height="5.5" fill="currentColor"/>' },
            'half_rest':      { label: '½ rest',   svg: '<line x1="4" y1="22" x2="22" y2="22" stroke="currentColor" stroke-width="1.2"/><rect x="7" y="16.5" width="12" height="5.5" fill="currentColor"/>' },
            'quarter_rest':   { label: 'Qtr rest', svg: '<path d="M9 8 L15 17 L10 21 L16 29 C12 27 9 29 11 33" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round"/>' },
            'eighth_rest':    { label: '8th rest', svg: '<circle cx="9" cy="15" r="2.6" fill="currentColor"/><path d="M11 14 L15 28" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>' },
            'triplet':        { label: 'Triplet', svg: '<text x="13" y="9" font-size="9" font-weight="700" text-anchor="middle" fill="currentColor">3</text><rect x="3" y="13" width="20" height="2.4" fill="currentColor"/><rect x="3.6" y="14" width="1.6" height="14" fill="currentColor"/><rect x="12.2" y="14" width="1.6" height="14" fill="currentColor"/><rect x="20.8" y="14" width="1.6" height="14" fill="currentColor"/><ellipse cx="4.4" cy="29" rx="3.1" ry="2.2" transform="rotate(-20 4.4 29)" fill="currentColor"/><ellipse cx="13" cy="29" rx="3.1" ry="2.2" transform="rotate(-20 13 29)" fill="currentColor"/><ellipse cx="21.6" cy="29" rx="3.1" ry="2.2" transform="rotate(-20 21.6 29)" fill="currentColor"/>' },
        };

        function rhythmGlyphMarkup(value) {
            const g = RHYTHM_GLYPHS[value];
            if (!g) return value;
            return `<svg viewBox="0 0 26 40" width="22" height="36" class="block mx-auto">${g.svg}</svg>`;
        }

        const RHYTHM_VF_DURATION = {
            'whole': 'w', 'half': 'h', 'quarter': 'q', 'eighth': '8', 'sixteenth': '16',
            'dotted-half': 'h', 'dotted-quarter': 'q', 'dotted-eighth': '8',
            'triplet-eighth': '8',
            'whole_rest': 'wr', 'half_rest': 'hr', 'quarter_rest': 'qr', 'eighth_rest': '8r',
        };

        // Note value → length in twelfths-of-a-quarter (triplet-eighth = 4, three = 12 = quarter).
        const RHYTHM_TWELFTHS = {
            'whole': 48, 'half': 24, 'dotted-half': 36, 'quarter': 12, 'dotted-quarter': 18,
            'eighth': 6, 'dotted-eighth': 9, 'sixteenth': 3, 'triplet-eighth': 4,
            'whole_rest': 48, 'half_rest': 24, 'quarter_rest': 12, 'eighth_rest': 6,
        };

        // Metric beat tick (in twelfths): used for audio/metronome timing.
        //   x/8 compound = dotted-quarter (18), x/4 = quarter (12), x/2 = half (24).
        function rhythmBeatTicks(den) {
            return den === 8 ? 18 : Math.round(48 / den);
        }

        // Visual grouping tick (in twelfths): used for beaming and beat-counter display.
        //   x/2 uses quarter-note groups (12) so beams never cross a quarter boundary.
        //   All other denominators match the metric beat tick.
        function rhythmVisualGroupTicks(den) {
            return den === 2 ? 12 : rhythmBeatTicks(den);
        }

        // Draw a rhythm sequence onto a single-line rhythm staff (time signature, no clef).
        function drawRhythmStaff(container, sequence, timeSig, color) {
            if (!container || typeof Vex === 'undefined') return;
            const { Renderer, Stave, StaveNote, Voice, Formatter, Dot, Beam, Tuplet } = Vex.Flow;
            container.innerHTML = '';

            const sig = timeSig || '4/4';
            const num = parseInt(sig.split('/')[0]) || 4;
            const den = parseInt(sig.split('/')[1]) || 4;
            const n = sequence.length;

            const sigWidth = 34;
            const noteWidth = 48;
            const containerW = Math.max(300, (container.offsetWidth || 500) - 20);
            const width = Math.max(containerW, sigWidth + 24 + Math.max(1, n) * noteWidth);
            const height = 104;

            const renderer = new Renderer(container, Renderer.Backends.SVG);
            renderer.resize(width, height);
            const ctx = renderer.getContext();

            const stave = new Stave(6, 14, width - 16);
            try {
                stave.setConfigForLines([
                    { visible: false }, { visible: false },
                    { visible: true },
                    { visible: false }, { visible: false },
                ]);
            } catch (e) {}
            stave.addTimeSignature(sig);
            stave.setContext(ctx).draw();

            const centerRhythm = () => requestAnimationFrame(() => {
                const svg = container.querySelector('svg');
                if (!svg) return;
                try {
                    const bbox = svg.getBBox();
                    if (!bbox || bbox.height < 5) return;
                    const containerH = Math.max(container.offsetHeight || 110, 80);
                    const pad = 10;
                    const totalH = bbox.height + pad * 2;
                    const topPad = Math.max(0, (containerH - totalH) / 2);
                    const viewY = bbox.y - pad - topPad;
                    const vw = parseFloat(svg.getAttribute('width')) || width;
                    svg.setAttribute('viewBox', `0 ${viewY} ${vw} ${containerH}`);
                    svg.setAttribute('height', String(containerH));
                } catch(e) {}
            });

            if (n === 0) { centerRhythm(); return; }

            const notes = sequence.map(v => {
                const dur = RHYTHM_VF_DURATION[v] || 'q';
                const note = new StaveNote({ keys: ['b/4'], duration: dur, auto_stem: true });
                if (v.startsWith('dotted-')) {
                    try { Dot.buildAndAttach([note], { all: true }); } catch (e) {}
                }
                if (color) note.setStyle({ fillStyle: color, strokeStyle: color });
                return note;
            });

            const beatTicks = rhythmVisualGroupTicks(den); // quarter-note boundary for x/2
            const beamable = v => v === 'eighth' || v === 'dotted-eighth' || v === 'sixteenth' || v === 'triplet-eighth';
            const beamGroups = [];
            const tripletGroups = [];
            let run = [], tripletRun = [], curBeat = -1, pos = 0;
            const flushRun = () => { if (run.length >= 2) beamGroups.push(run); run = []; };

            sequence.forEach((v, i) => {
                const dur = RHYTHM_TWELFTHS[v] ?? 12;
                const beat = Math.floor(pos / beatTicks);
                if (v === 'triplet-eighth') {
                    flushRun();
                    tripletRun.push(notes[i]);
                    if (tripletRun.length === 3) {
                        beamGroups.push(tripletRun.slice());
                        tripletGroups.push(tripletRun.slice());
                        tripletRun = [];
                    }
                } else if (beamable(v)) {
                    if (beat !== curBeat) flushRun();
                    run.push(notes[i]);
                } else {
                    flushRun();
                }
                curBeat = beat;
                pos += dur;
            });
            flushRun();

            const tuplets = tripletGroups.map(g => {
                try { return new Tuplet(g, { num_notes: 3, notes_occupied: 2 }); } catch (e) { return null; }
            }).filter(Boolean);
            const beams = beamGroups.map(g => {
                try { return new Beam(g); } catch (e) { return null; }
            }).filter(Boolean);

            const voice = new Voice({ numBeats: num, beatValue: den }).setMode(Voice.Mode.SOFT);
            voice.addTickables(notes);
            new Formatter().joinVoices([voice]).format([voice], Math.max(120, width - sigWidth - 50));

            voice.draw(ctx, stave);
            beams.forEach(b => { try { b.setContext(ctx).draw(); } catch (e) {} });
            tuplets.forEach(t => { try { t.setContext(ctx).draw(); } catch (e) {} });
            centerRhythm();
        }

        // Crisp Web Audio metronome click, scheduled at an absolute audio-clock time.
        function _metroClick(accent, atTime) {
            try {
                const rawCtx = (typeof Tone !== 'undefined' && Tone.getContext)
                    ? Tone.getContext().rawContext
                    : new (window.AudioContext || window.webkitAudioContext)();
                const osc  = rawCtx.createOscillator();
                const gain = rawCtx.createGain();
                osc.connect(gain);
                gain.connect(rawCtx.destination);
                osc.type = 'sine';
                osc.frequency.value = accent ? 1047 : 784;
                const t   = (typeof atTime === 'number') ? atTime : rawCtx.currentTime;
                const dur = accent ? 0.018 : 0.013;
                gain.gain.setValueAtTime(accent ? 0.9 : 0.55, t);
                gain.gain.exponentialRampToValueAtTime(0.0001, t + dur);
                osc.start(t);
                osc.stop(t + dur);
            } catch(e) {}
        }

        // One-bar metronome count-in, then the rhythm hits with the metronome under them.
        // Clicks and notes are both scheduled at absolute times on the shared audio clock.
        async function playRhythmAudio(sequence, tempo, timeSig, useMetronome) {
            const t = parseInt(tempo) || 80;
            const sig = timeSig || '4/4';
            const num = parseInt(sig.split('/')[0]) || 4;
            const den = parseInt(sig.split('/')[1]) || 4;
            const beatMs = 60000 / t;
            const noteDur = {
                whole: 4, half: 2, quarter: 1, eighth: 0.5, sixteenth: 0.25,
                'dotted-half': 3, 'dotted-quarter': 1.5, 'dotted-eighth': 0.75,
                'triplet-eighth': 1 / 3,
                whole_rest: 4, half_rest: 2, quarter_rest: 1, eighth_rest: 0.5,
            };

            // Compound (x/8): the BEAT is the dotted-quarter, so the displayed tempo is the
            //   dotted-quarter BPM. Click once per beat (num/3 beats per bar) and time notes
            //   so a dotted-quarter lasts exactly one beat. (Previously the click ran at every
            //   eighth — 2× the written tempo — which is why 6/8 & 9/8 lessons felt too fast.)
            // Alla breve (x/2): bpm = half-note BPM; click every half-note beat, num per bar.
            // Simple (x/4): click every quarter note (beatMs), num per bar.
            const isCompound    = den === 8;
            const isAllaBreve   = den === 2;
            const compoundBeats = Math.max(1, Math.round(num / 3)); // dotted-quarter beats per bar
            const subdivMs      = beatMs; // one click per beat in every meter
            const subdivsPerBar = isCompound ? compoundBeats : num;
            const isAccent      = isCompound ? (b => b % compoundBeats === 0) : (b => b === 0);
            // quarter-note duration reference: x/2 → beatMs/2 (half-note beat),
            // x/8 → 2·beatMs/3 (so dotted-quarter = beatMs), x/4 → beatMs.
            const noteBeatMs    = isAllaBreve ? beatMs / 2 : (isCompound ? beatMs * 2 / 3 : beatMs);

            await window.HarmonivaAudio.prepare();
            const leadMs = 120;
            const t0 = window.HarmonivaAudio.now() + leadMs / 1000;
            const click = (ms, accent) => _metroClick(accent, t0 + ms / 1000);
            const hit = (ms, ringSec) => window.HarmonivaAudio.playNoteAt('C4', ringSec, t0 + ms / 1000);

            // Count-in: one full bar of subdivisions
            for (let b = 0; b < subdivsPerBar; b++) click(b * subdivMs, isAccent(b));
            const startMs = subdivsPerBar * subdivMs;

            // Metronome under the rhythm: span the full rhythm duration in subdivisions
            if (useMetronome !== false) {
                const rhythmMs = sequence.reduce((a, n) => a + (noteDur[n] || 1) * noteBeatMs, 0);
                const rhythmSubdivs = Math.ceil(rhythmMs / subdivMs);
                for (let b = 0; b < rhythmSubdivs; b++) click(startMs + b * subdivMs, isAccent(b));
            }

            let elapsed = 0;
            for (const note of sequence) {
                const dur = noteDur[note] || 1;
                const noteMs = dur * noteBeatMs;
                if (!note.includes('rest')) {
                    hit(startMs + elapsed, Math.max(0.08, (noteMs / 1000) * 0.85));
                }
                elapsed += noteMs;
            }
            return leadMs + startMs + elapsed;
        }

        // Wire the builder: palette → staff, delete/clear, beats meter + Check gating, and a
        // Check that exact-matches the built sequence via the shared check-answer endpoint.
        function setupRhythmBuilder(target) {
            const palette   = document.getElementById('rhythmPalette');
            const table     = document.getElementById('rhythmTable');
            const deleteBtn = document.getElementById('rhythmDeleteBtn');
            const clearBtn  = document.getElementById('rhythmClearBtn');
            const checkBtn  = document.getElementById('rhythmCheckBtn');
            const feedbackMessage = document.getElementById('feedbackMessage');
            const playButton = document.getElementById('playButton');
            const playStatus = document.getElementById('playStatus');
            const nextButton = document.getElementById('nextPracticeBtn');
            if (!palette || !table || !checkBtn) return;

            const answerOptions = document.getElementById('answerOptions');
            const playMineBtn   = document.getElementById('rhythmPlayMineBtn');
            const fillBar       = document.getElementById('rhythmFillBar');
            const fillLabel     = document.getElementById('rhythmFillLabel');

            const timeSig = (playButton && playButton.dataset.timeSig) || '4/4';
            const num = parseInt(timeSig.split('/')[0]) || 4;
            const den = parseInt(timeSig.split('/')[1]) || 4;
            const bars = parseInt((answerOptions && answerOptions.dataset.bars) || '1') || 1;
            const practiceId = (answerOptions && answerOptions.dataset.practiceId) || 0;

            const allowedAttr = (answerOptions && answerOptions.dataset.allowed) || '';
            const PALETTE = allowedAttr
                ? allowedAttr.split(',').filter(Boolean)
                : ['whole', 'half', 'dotted-half', 'quarter', 'dotted-quarter',
                    'eighth', 'dotted-eighth', 'sixteenth',
                    'whole_rest', 'half_rest', 'quarter_rest', 'eighth_rest'];

            const TWELFTHS = RHYTHM_TWELFTHS;
            const beatTicks = rhythmVisualGroupTicks(den); // quarter-note groups for x/2
            const capacity = Math.round((48 * num / den) * bars);
            const totalBeats = beatTicks > 0 ? capacity / beatTicks : 0;

            let built = [];
            let answered = false;

            const fmt = (x) => Number.isInteger(x) ? String(x) : x.toFixed(2).replace(/\.?0+$/, '');
            const sumTwelfths = () => built.reduce((a, v) => a + (TWELFTHS[v] || 0), 0);

            const updateState = () => {
                const sum  = sumTwelfths();
                const full = capacity > 0 && sum === capacity;
                const over = capacity > 0 && sum > capacity;

                if (fillBar) {
                    fillBar.style.width = (capacity > 0 ? Math.min(100, (sum / capacity) * 100) : 0) + '%';
                    fillBar.classList.remove('bg-amber-400', 'bg-green-500', 'bg-red-500');
                    fillBar.classList.add(over ? 'bg-red-500' : full ? 'bg-green-500' : 'bg-amber-400');
                }
                if (fillLabel) {
                    fillLabel.textContent = pt('beats_progress', {done: fmt(sum / beatTicks), total: fmt(totalBeats)})
                        + (over ? ' (too long)' : '');
                    fillLabel.classList.remove('text-gray-600', 'text-green-600', 'text-red-600');
                    fillLabel.classList.add(over ? 'text-red-600' : full ? 'text-green-600' : 'text-gray-600');
                }

                deleteBtn.disabled = answered || built.length === 0;
                clearBtn.disabled  = answered || built.length === 0;
                checkBtn.disabled  = answered || !full;
                if (playMineBtn) playMineBtn.disabled = built.length === 0;
            };

            const renderTable = () => {
                drawRhythmStaff(table, built, timeSig);
                updateState();
            };

            palette.innerHTML = '';
            PALETTE.forEach(v => {
                const b = document.createElement('button');
                b.type = 'button';
                b.className = 'rhythm-note-btn w-12 h-12 rounded-xl bg-[#2a7898] text-white flex items-center justify-center hover:bg-[#23698a] active:scale-95 transition disabled:opacity-40 disabled:cursor-not-allowed';
                b.dataset.value = v;
                b.innerHTML = rhythmGlyphMarkup(v);
                b.onclick = () => {
                    if (answered) return;
                    if (v === 'triplet') built.push('triplet-eighth', 'triplet-eighth', 'triplet-eighth');
                    else built.push(v);
                    renderTable();
                };
                palette.appendChild(b);
            });

            deleteBtn.onclick = () => {
                if (answered) return;
                if (built[built.length - 1] === 'triplet-eighth') built.splice(-3, 3);
                else built.pop();
                renderTable();
            };
            clearBtn.onclick = () => { if (answered) return; built = []; renderTable(); };

            if (playMineBtn) {
                playMineBtn.onclick = async () => {
                    if (built.length === 0) return;
                    if (typeof Tone !== 'undefined') await Tone.start();
                    const tempo = (playButton && playButton.dataset.tempo) || 80;
                    const original = playMineBtn.innerHTML;
                    playMineBtn.disabled = true;
                    playMineBtn.innerHTML = '<i data-lucide="volume-2" class="w-4 h-4"></i> ' + pt('playing');
                    if (typeof lucide !== 'undefined') lucide.createIcons();
                    const totalMs = await playRhythmAudio(built, tempo, timeSig);
                    setTimeout(() => {
                        playMineBtn.innerHTML = original;
                        playMineBtn.disabled = built.length === 0;
                        if (typeof lucide !== 'undefined') lucide.createIcons();
                    }, totalMs + 300);
                };
            }

            checkBtn.onclick = async () => {
                if (answered || built.length === 0) return;
                const answer = built.join(',');
                checkBtn.disabled = true;
                checkBtn.innerHTML = '<i data-lucide="loader-2" class="w-4 h-4 animate-spin"></i> ' + pt('checking');
                if (typeof lucide !== 'undefined') lucide.createIcons();
                try {
                    const response = await fetch('/api/practice/check-answer', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') },
                        body: JSON.stringify({ practice_id: null, question_id: parseInt(practiceId), answer: answer, slug: 'rhythm-practice' })
                    });
                    const data = await response.json();
                    answered = true;
                    palette.querySelectorAll('button').forEach(b => b.disabled = true);
                    deleteBtn.disabled = true;
                    clearBtn.disabled = true;
                    checkBtn.disabled = true;

                    if (playButton) playButton.style.display = 'none';
                    if (playStatus) playStatus.style.display = 'none';
                    if (nextButton) nextButton.style.display = 'flex';

                    const st = document.getElementById('scoreTotal');
                    if (st) st.textContent = (parseInt(st.textContent) || 0) + 1;

                    if (data.is_correct) {
                        checkBtn.innerHTML = '<i data-lucide="check" class="w-4 h-4"></i> ' + pt('correct_label');
                        table.classList.remove('border-gray-300');
                        table.classList.add('border-green-400', 'bg-green-50');
                        feedbackMessage.textContent = pt('correct_well_done');
                        feedbackMessage.className = 'mt-4 p-4 rounded-lg text-center font-medium bg-green-100 text-green-700';
                        const xpEl = document.getElementById('xpEarned');
                        if (xpEl) xpEl.textContent = (parseInt(xpEl.textContent) || 0) + 10;
                        const sc = document.getElementById('scoreCorrect');
                        if (sc) sc.textContent = (parseInt(sc.textContent) || 0) + 1;
                    } else {
                        checkBtn.innerHTML = '<i data-lucide="x" class="w-4 h-4"></i> ' + pt('incorrect_label');
                        table.classList.remove('border-gray-300');
                        table.classList.add('border-red-400', 'bg-red-50');
                        feedbackMessage.textContent = pt('not_quite_rhythm');
                        feedbackMessage.className = 'mt-4 p-4 rounded-lg text-center font-medium bg-red-100 text-red-700';
                        const reveal = document.getElementById('rhythmReveal');
                        const revealRow = document.getElementById('rhythmRevealRow');
                        if (reveal && revealRow) {
                            drawRhythmStaff(revealRow, target.split(',').filter(Boolean), timeSig, '#15803d');
                            reveal.classList.remove('hidden');
                        }
                    }
                    if (typeof lucide !== 'undefined') lucide.createIcons();
                } catch (e) {
                    console.error('Error checking rhythm:', e);
                    answered = false;
                    checkBtn.disabled = false;
                    checkBtn.innerHTML = '<i data-lucide="check" class="w-4 h-4"></i> ' + pt('check_label');
                    if (typeof lucide !== 'undefined') lucide.createIcons();
                }
            };

            renderTable();
            if (typeof lucide !== 'undefined') lucide.createIcons();
        }

        function initBuildMode(myGen) {
            const playButton = document.getElementById('playButton');
            const playStatus = document.getElementById('playStatus');
            const answerOptions = document.getElementById('answerOptions');
            if (!playButton || !answerOptions) return;

            const tempo = playButton.dataset.tempo || 80;
            const timeSig = playButton.dataset.timeSig || '4/4';
            const notes = playButton.dataset.notes ? playButton.dataset.notes.split(',').filter(Boolean) : [];
            const target = answerOptions.dataset.target || '';

            playButton.onclick = async function() {
                if (playButton.disabled) return;
                playButton.disabled = true;
                playButton.innerHTML = '<i data-lucide="loader-2" class="w-5 h-5 animate-spin inline"></i> ' + pt('playing');
                if (typeof lucide !== 'undefined') lucide.createIcons();
                const totalMs = await playRhythmAudio(notes, tempo, timeSig);
                setTimeout(() => {
                    if (window._practiceGen !== myGen) return;
                    playButton.disabled = false;
                    playButton.innerHTML = '<i data-lucide="play" class="w-5 h-5"></i> ' + pt('play_again');
                    if (playStatus) playStatus.textContent = pt('click_replay');
                    if (typeof lucide !== 'undefined') lucide.createIcons();
                }, totalMs + 400);
            };

            setupRhythmBuilder(target);
        }

        // ── Bootstrap ─────────────────────────────────────────────────────────────

        document.addEventListener('livewire:init', function() {
            window.initPracticeRhythm();
            Livewire.on('practice-updated', () => {
                // Clean up any leftover Space handler before re-init
                if (window._rhythmSpaceCleanup) { window._rhythmSpaceCleanup(); window._rhythmSpaceCleanup = null; }
                setTimeout(() => window.initPracticeRhythm(), 80);
            });
        });
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof Livewire === 'undefined') window.initPracticeRhythm();
        });
        </script>
        @endif
    </main>
</div>
