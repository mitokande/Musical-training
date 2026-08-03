<div>
    <!-- VexFlow -->
    @include('livewire.partials.practice-i18n')
    <script src="https://cdn.jsdelivr.net/npm/vexflow@4.2.2/build/cjs/vexflow.js"></script>

    <style>
        .md-dur-btn {
            width:46px; height:46px; border-radius:10px; flex-shrink:0;
            background:{{ $mdDurBg }}; color:white; border:2px solid transparent;
            display:flex; align-items:center; justify-content:center;
            cursor:pointer; transition:background 0.12s, border-color 0.12s, box-shadow 0.12s;
        }
        .md-dur-btn:hover { background:{{ $mdDurHover }}; }
        .md-dur-btn:active { background:{{ $mdDurActive }}; }
        .md-dur-btn.md-selected {
            background:{{ $mdDurSelBg }}; border-color:{{ $mdDurSelBorder }};
            box-shadow:0 0 0 3px {{ $mdDurSelRing }};
        }
        .md-note-btn {
            border-radius:10px; border:2px solid #e2e8f0; background:white;
            font-weight:700; font-size:0.875rem; color:#1e293b;
            display:flex; align-items:center; justify-content:center;
            cursor:pointer; transition:all 0.15s; height:48px;
        }
        .md-note-btn:hover { border-color:{{ $mdNoteHoverBorder }}; background:{{ $mdNoteHoverBg }}; }
        .md-note-btn.md-flash { background:{{ $mdNoteFlash }}; color:white; border-color:{{ $mdNoteFlash }}; transform:scale(0.93); }
        .md-oct-btn {
            border-radius:10px; border:2px solid #9a3412; background:#c2410c;
            font-weight:700; font-size:0.9rem; color:white;
            display:flex; align-items:center; justify-content:center;
            cursor:pointer; transition:background 0.12s, transform 0.1s;
        }
        .md-oct-btn:hover { background:#9a3412; }
        @keyframes oct-flash { 0%{background:#c2410c;transform:scale(1)} 40%{background:#fb923c;transform:scale(1.12)} 100%{background:#c2410c;transform:scale(1)} }
        @media(max-width:640px) { .md-action-bar > button { min-width:calc(50% - 4px); } }
        .md-oct-btn.oct-flashing { animation:oct-flash 0.28s ease; }
        .md-acc-btn {
            height:46px; min-width:46px; border-radius:9px;
            background:#1e293b; border:2px solid #334155; color:white;
            font-size:2.2rem; font-weight:700;
            display:flex; align-items:center; justify-content:center;
            cursor:pointer; transition:all 0.15s; padding:0 4px;
        }
        .md-acc-btn:hover { background:#334155; }
        .md-acc-btn.md-acc-active { background:{{ $mdAccActiveBg }}; border-color:{{ $mdAccActiveBorder }}; box-shadow:0 0 0 2px {{ $mdAccActiveRing }}; }
        .md-act-btn {
            flex:1; padding:10px 8px; border-radius:12px; border:2px solid #e2e8f0;
            background:white; font-size:0.78rem; font-weight:600; color:#475569;
            display:flex; align-items:center; justify-content:center; gap:5px;
            cursor:pointer; transition:all 0.15s; white-space:nowrap;
        }
        .md-act-btn:hover { border-color:#94a3b8; background:#f8fafc; }
        .md-check-btn {
            flex:1; padding:10px 8px; border-radius:12px; border:2px solid transparent;
            background:linear-gradient(135deg,#16a34a,#22c55e); color:white;
            font-size:0.78rem; font-weight:700;
            display:flex; align-items:center; justify-content:center; gap:5px;
            cursor:pointer; transition:all 0.15s; white-space:nowrap;
        }
        .md-check-btn:hover:not(:disabled) { background:linear-gradient(135deg,#15803d,#16a34a); }
        .md-check-btn:disabled { opacity:0.45; cursor:not-allowed; }
        .md-seg-indicator { display:flex; gap:4px; }
        .md-seg-dot { width:8px; height:8px; border-radius:50%; background:#cbd5e1; transition:background 0.2s; }
        .md-seg-dot.active { background:{{ $mdSegActive }}; }
        .md-seg-dot.done-ok { background:#16a34a; }
        .md-seg-dot.done-err { background:#dc2626; }
    </style>

    <main class="max-w-4xl mx-auto px-1 sm:px-6 lg:px-8 pt-3.5 pb-6">

        @if(!$currentPractice)
            <div class="card p-12 text-center">
                <h3 class="text-lg font-semibold text-gray-900 mb-2">{{ __('app.practice_ui.common.no_exercises') }}</h3>
                <p class="text-gray-500 mb-4">{{ $mdEmptyDesc }}</p>
                <a href="{{ $mdEmptyUrl }}" class="btn-primary text-white font-semibold py-2.5 px-6 rounded-lg inline-flex items-center gap-2">
                    <i data-lucide="{{ $mdEmptyIcon }}" class="w-4 h-4"></i> {{ $mdEmptyCta }}
                </a>
            </div>
        @else

        @php
        $allNoteValDefs = [
            ['dur'=>'whole',          'title'=>'Whole',
             'svg'=>'<svg viewBox="0 0 46 26" width="31" height="18"><ellipse cx="23" cy="13" rx="19" ry="10" fill="none" stroke="currentColor" stroke-width="3.8"/></svg>'],
            ['dur'=>'half',           'title'=>'Half',
             'svg'=>'<svg viewBox="0 0 30 54" width="17" height="29"><ellipse cx="12" cy="43" rx="11" ry="8" fill="none" stroke="currentColor" stroke-width="3" transform="rotate(-20 12 43)"/><line x1="22" y1="37" x2="22" y2="4" stroke="currentColor" stroke-width="3" stroke-linecap="round"/></svg>'],
            ['dur'=>'dotted-half',    'title'=>'Dotted Half',
             'svg'=>'<svg viewBox="0 0 40 54" width="23" height="29"><ellipse cx="12" cy="43" rx="11" ry="8" fill="none" stroke="currentColor" stroke-width="3" transform="rotate(-20 12 43)"/><line x1="22" y1="37" x2="22" y2="4" stroke="currentColor" stroke-width="3" stroke-linecap="round"/><circle cx="34" cy="41" r="4" fill="currentColor"/></svg>'],
            ['dur'=>'quarter',        'title'=>'Quarter',
             'svg'=>'<svg viewBox="0 0 30 54" width="17" height="29"><ellipse cx="12" cy="43" rx="11" ry="8" fill="currentColor" transform="rotate(-20 12 43)"/><line x1="22" y1="37" x2="22" y2="4" stroke="currentColor" stroke-width="3" stroke-linecap="round"/></svg>'],
            ['dur'=>'dotted-quarter', 'title'=>'Dotted Quarter',
             'svg'=>'<svg viewBox="0 0 40 54" width="23" height="29"><ellipse cx="12" cy="43" rx="11" ry="8" fill="currentColor" transform="rotate(-20 12 43)"/><line x1="22" y1="37" x2="22" y2="4" stroke="currentColor" stroke-width="3" stroke-linecap="round"/><circle cx="34" cy="41" r="4" fill="currentColor"/></svg>'],
            ['dur'=>'eighth',         'title'=>'Eighth',
             'svg'=>'<svg viewBox="0 0 36 54" width="19" height="29"><ellipse cx="12" cy="43" rx="11" ry="8" fill="currentColor" transform="rotate(-20 12 43)"/><line x1="22" y1="37" x2="22" y2="4" stroke="currentColor" stroke-width="3" stroke-linecap="round"/><path d="M22 4 C33 11 33 27 22 33" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"/></svg>'],
            ['dur'=>'dotted-eighth',  'title'=>'Dotted Eighth',
             'svg'=>'<svg viewBox="0 0 46 54" width="25" height="29"><ellipse cx="12" cy="43" rx="11" ry="8" fill="currentColor" transform="rotate(-20 12 43)"/><line x1="22" y1="37" x2="22" y2="4" stroke="currentColor" stroke-width="3" stroke-linecap="round"/><path d="M22 4 C33 11 33 27 22 33" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"/><circle cx="38" cy="41" r="4" fill="currentColor"/></svg>'],
            ['dur'=>'sixteenth',      'title'=>'Sixteenth',
             'svg'=>'<svg viewBox="0 0 36 54" width="19" height="29"><ellipse cx="12" cy="43" rx="11" ry="8" fill="currentColor" transform="rotate(-20 12 43)"/><line x1="22" y1="37" x2="22" y2="2" stroke="currentColor" stroke-width="3" stroke-linecap="round"/><path d="M22 2 C33 9 33 25 22 31" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"/><path d="M22 16 C33 23 33 39 22 45" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"/></svg>'],
            ['dur'=>'half_rest',      'title'=>'Half Rest',
             'svg'=>'<svg viewBox="0 0 36 22" width="26" height="16"><rect x="4" y="3" width="28" height="8" fill="currentColor" rx="2"/></svg>'],
            ['dur'=>'quarter_rest',   'title'=>'Quarter Rest',
             'svg'=>'<svg viewBox="0 0 24 46" width="14" height="27"><path d="M17 3 L8 14 L17 22 L6 30 L14 36 L8 44" fill="none" stroke="currentColor" stroke-width="3.2" stroke-linecap="round" stroke-linejoin="round"/></svg>'],
            ['dur'=>'eighth_rest',    'title'=>'Eighth Rest',
             'svg'=>'<svg viewBox="0 0 28 42" width="17" height="25"><circle cx="19" cy="31" r="5.5" fill="currentColor"/><line x1="7" y1="8" x2="20" y2="34" stroke="currentColor" stroke-width="3" stroke-linecap="round"/><circle cx="8" cy="7" r="5" fill="currentColor"/></svg>'],
        ];
        $noteValDefs = $allNoteValDefs;

        $practiceNotes      = is_array($currentPractice->notes ?? null)      ? $currentPractice->notes      : (json_decode($currentPractice->notes ?? '[]', true) ?? []);
        $practiceNoteValues = is_array($currentPractice->note_values ?? null) ? $currentPractice->note_values : (json_decode($currentPractice->note_values ?? '[]', true) ?? []);
        $practiceTimeSig    = $currentPractice->time_signature ?? $dictationTimeSignature ?? '4/4';
        $practiceKeyRoot    = $currentPractice->key_signature ?? 'C';
        $practiceTonic      = $currentPractice->tonic ?? $practiceKeyRoot;
        // Learning Path questions carry their own mode; the component-level
        // $dictationMode only reflects Exercise Setup Studio settings.
        $practiceMode       = $currentPractice->mode ?? $dictationMode;
        $practiceClef       = $currentPractice->clef ?? 'treble';
        $practiceTempo      = $currentPractice->tempo ?? $dictationTempo ?? 50;
        $practiceBars       = $currentPractice->bars ?? 2;

        // Reference note: always the first note of the melody
        $refNote = $practiceNotes[0] ?? ($practiceTonic . (str_contains($practiceClef ?? '', 'bass') ? '3' : '4'));
        @endphp

        <div class="card overflow-hidden mb-6">
            <!-- Header -->
            <div class="hero-gradient py-5 px-6">
                <div class="relative flex items-center justify-center">
                    <a href="{{ $mdBackUrl }}" class="absolute left-0 top-1/2 -translate-y-1/2 w-12 h-12 flex items-center justify-center rounded-xl bg-white/10 hover:bg-white/20 text-white transition-all">
                        <i data-lucide="arrow-left" class="w-6 h-6"></i>
                    </a>
                    <div class="text-center">
                        {!! $mdTitleHtml !!}
                        <p class="text-white/80 text-sm">
                            {{ $practiceMode === 'minor' ? $practiceTonic : $practiceKeyRoot }} {{ $practiceMode === 'minor' ? __('app.practice_ui.dictation.mode_minor') : __('app.practice_ui.dictation.mode_major') }}
                            &middot; {{ $practiceTimeSig }}
                            &middot; {{ $practiceTempo }} BPM
                        </p>
                    </div>
                    <div class="absolute right-0 top-1/2 -translate-y-1/2">
                        <span class="inline-flex items-center gap-1.5 rounded-xl bg-white/10 px-3 py-2 text-sm font-semibold text-white/90 ring-1 ring-white/20">
                            {{ $currentPracticeIndex + 1 }} / {{ count($practices) }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="p-2.5 sm:p-6">

                <!-- ── Staff (answer area) ─────────────────────────────── -->
                <div class="mb-5">
                    <div id="melody-staff-container"
                         class="w-full border-2 border-gray-200 rounded-xl bg-white overflow-hidden transition-colors duration-300"
                         style="min-height:160px;display:flex;align-items:center;">
                        <div id="melody-staff-output" class="w-full"></div>
                    </div>
                    {{-- Correct-melody reveal (shown only after an incorrect Check) --}}
                    <div id="correctMelodyReveal" class="hidden mt-3">
                        <p class="text-xs font-semibold text-green-700 text-center mb-1">{{ __('app.practice_ui.dictation.correct_melody') }}</p>
                        <div class="w-full border-2 border-green-400 bg-green-50 rounded-xl overflow-hidden"
                             style="min-height:160px;display:flex;align-items:center;">
                            <div id="correctMelodyStaffOutput" class="w-full"></div>
                        </div>
                    </div>
                </div>

                <!-- ── Play box ──────────────────────────────────────────── -->
                <div class="card px-5 py-3 mb-5">
                    <div class="flex flex-wrap items-center gap-4">

                        <!-- Left: segment indicator + progress -->
                        <div class="min-w-0 flex-1" style="min-width:42%">
                            <!-- Segment indicator -->
                            <div class="flex items-center gap-3 mb-2">
                                <div class="md-seg-indicator" id="segIndicator"></div>
                            </div>
                            <p class="text-sm font-bold text-gray-800 mb-2" id="beatDisplay">
                                {{ __('app.practice_ui.dictation.bar_beats', ['bar' => 1, 'beats' => explode('/', $practiceTimeSig)[0]]) }}
                            </p>
                            <div class="bg-gray-200 rounded-full overflow-hidden" style="height:6px;width:50%">
                                <div id="beatProgress" class="{{ $mdBeatBarClass }} h-full rounded-full transition-all duration-150" style="width:0%"></div>
                            </div>
                        </div>

                        <!-- Right: Play / Next buttons -->
                        <div class="flex-shrink-0" style="min-width:150px;">
                            <button id="playButton"
                                style="background:{{ $mdPlayGrad }};transition:all 0.2s;width:100%;"
                                class="text-white font-bold py-3 px-6 rounded-xl flex items-center justify-center gap-2 hover:shadow-lg"
                                data-notes="{{ implode(',', $practiceNotes) }}"
                                data-note-values="{{ implode(',', $practiceNoteValues) }}"
                                data-tempo="{{ $practiceTempo }}">
                                <i data-lucide="play" class="w-5 h-5"></i>
                                {{ __('app.practice_ui.common.play') }}
                            </button>
                            @if ($currentPracticeIndex < (count($practices) - 1))
                                <button id="nextPracticeBtn" x-on:click="$wire.getNextPractice()"
                                    class="font-semibold py-3 px-6 rounded-xl items-center justify-center gap-2 {{ $mdNextBtnClass }}"
                                    style="display:none;width:100%;">
                                    <i data-lucide="arrow-right" class="w-5 h-5"></i> {{ __('app.practice_ui.common.next') }}
                                </button>
                            @else
                                <a href="{{ $mdBackUrl }}" id="nextPracticeBtn"
                                    class="font-semibold py-3 px-6 rounded-xl items-center justify-center gap-2 {{ $mdNextBtnClass }}"
                                    style="display:none;width:100%;">
                                    <i data-lucide="check" class="w-5 h-5"></i> {{ __('app.practice_ui.common.finish') }}
                                </a>
                            @endif
                        </div>

                    </div>
                </div>

                <!-- ── Instruction ────────────────────────────────────────── -->
                <p class="text-sm text-gray-600 text-center mb-4">
                    Listen to the melody segment, then click notes below to rebuild it.
                </p>

                <!-- ── Note input frame ───────────────────────────────────── -->
                <div class="bg-white border-2 border-gray-200 rounded-2xl p-2 sm:p-4 mb-4"
                     id="answerSection"
                     data-target="{{ implode(',', $practiceNotes) }}"
                     data-note-values="{{ implode(',', $practiceNoteValues) }}"
                     data-practice-id="{{ $currentPractice->id }}"
                     data-clef="{{ $practiceClef }}"
                     data-time-sig="{{ $practiceTimeSig }}"
                     data-bars="{{ $practiceBars }}"
                     data-key-root="{{ $practiceKeyRoot }}"
                     data-tonic="{{ $practiceTonic }}"
                     data-ref-note="{{ $refNote }}"
                     data-metronome="{{ ($dictationMetronome ?? true) ? '1' : '0' }}"
                     data-total-bars="{{ $practiceBars }}">

                    <!-- Row 1: Note value selector -->
                    <div class="flex gap-1.5 mb-4 flex-wrap justify-center">
                        @foreach($noteValDefs as $nv)
                        <button class="md-dur-btn" data-dur="{{ $nv['dur'] }}" title="{{ $nv['title'] }}">
                            {!! $nv['svg'] !!}
                        </button>
                        @endforeach
                    </div>

                    <!-- Note names + octave + accidentals -->
                    <div class="bg-gray-50 rounded-xl p-2.5 flex flex-col gap-1.5">
                        <!-- Row 1: C D E F G A B — tek satır, flex:1 ile genişliği paylaşır -->
                        <div class="flex" style="gap:3px;">
                            @foreach(['C','D','E','F','G','A','B'] as $n)
                            <button class="md-note-btn note-name-btn" data-note="{{ $n }}" style="flex:1;height:42px;min-width:0;">{{ $n }}</button>
                            @endforeach
                        </div>
                        <!-- Row 2: [↓ oct] [arızalar] [↑ oct] -->
                        <div class="flex items-center justify-center" style="gap:3px;">
                            <div class="flex flex-col items-center" style="gap:2px;flex-shrink:0;">
                                <button id="octaveDown" class="md-oct-btn" style="width:42px;height:42px;">↓</button>
                                <span id="octaveDownVal" class="text-xs font-bold" style="color:#ea580c;line-height:1;"></span>
                            </div>
                            <div class="flex" style="gap:3px;">
                                <button class="md-acc-btn" data-acc="##" style="width:40px;height:42px;min-width:0;font-size:1.4rem;">𝄪</button>
                                <button class="md-acc-btn" data-acc="bb" style="width:40px;height:42px;min-width:0;font-size:1.4rem;">𝄫</button>
                                <button class="md-acc-btn" data-acc="b"  style="width:40px;height:42px;min-width:0;font-size:1.4rem;">♭</button>
                                <button class="md-acc-btn" data-acc=""   style="width:40px;height:42px;min-width:0;font-size:1.4rem;">♮</button>
                                <button class="md-acc-btn" data-acc="#"  style="width:40px;height:42px;min-width:0;font-size:1.4rem;">♯</button>
                            </div>
                            <div class="flex flex-col items-center" style="gap:2px;flex-shrink:0;">
                                <button id="octaveUp" class="md-oct-btn" style="width:42px;height:42px;">↑</button>
                                <span id="octaveUpVal" class="text-xs font-bold" style="color:#ea580c;line-height:1;"></span>
                            </div>
                        </div>
                        <span id="octaveDisplay" class="hidden">{{ $practiceClef === 'bass' ? '2' : ($practiceClef === 'alto' ? '3' : '4') }}</span>
                    </div>
                </div>

                <!-- ── Action bar ──────────────────────────────────────────── -->
                <div class="flex flex-wrap gap-2 mb-1 md-action-bar">
                    <button id="playMyAnswer" class="md-act-btn">
                        <i data-lucide="volume-2" class="w-4 h-4"></i> {{ __('app.practice_ui.dictation.play_mine') }}
                    </button>
                    <button id="deleteLastNote" class="md-act-btn">
                        <i data-lucide="delete" class="w-4 h-4"></i> {{ __('app.practice_ui.common.delete_last') }}
                    </button>
                    <button id="clearAllNotes" class="md-act-btn">
                        <i data-lucide="eraser" class="w-4 h-4"></i> {{ __('app.practice_ui.common.clear') }}
                    </button>
                    <button id="submitAnswer" class="md-check-btn" disabled>
                        <i data-lucide="check" class="w-4 h-4"></i> {{ __('app.practice_ui.common.check') }}
                    </button>
                    <!-- Next Segment (shown between segments) -->
                    <button id="nextSegBtn" class="md-act-btn" style="display:none;background:{{ $mdNextSegGrad }};color:white;border-color:transparent;">
                        <i data-lucide="arrow-right" class="w-4 h-4"></i> {{ __('app.practice_ui.dictation.next_segment') }}
                    </button>
                    <!-- Play from start (shown after all segments answered) -->
                    <button id="playFullPiece" class="md-act-btn" style="display:none;background:{{ $mdFullGrad }};color:white;border-color:transparent;">
                        <i data-lucide="rewind" class="w-4 h-4"></i> {{ __('app.practice_ui.dictation.play_from_start') }}
                    </button>
                </div>

                <div id="feedbackMessage" class="mt-4 p-4 rounded-xl text-center font-medium" style="display:none"></div>
                <input type="hidden" id="dictationAnswer" value="">

            </div>
        </div>

        <!-- Score / XP row -->
        <div class="flex items-center justify-center gap-4 text-sm text-gray-500">
            <span class="flex items-center gap-1"><i data-lucide="sparkles" class="w-4 h-4 text-yellow-500"></i>+<span id="xpEarned">0</span> XP</span>
            <span>•</span>
            <span><span id="scoreCorrect">0</span> / <span id="scoreTotal">0</span> {{ __('app.practice_ui.common.correct') }}</span>
        </div>

        <script>
        // ── VexFlow helpers ──────────────────────────────────────────────────

        const vfDurFull = {
            'whole':'w','half':'h','dotted-half':'hd',
            'quarter':'q','dotted-quarter':'qd',
            'eighth':'8','dotted-eighth':'8d','sixteenth':'16',
            'whole_rest':'wr','half_rest':'hr','quarter_rest':'qr','eighth_rest':'8r',
        };

        // Key signature accidental maps: note letter → '#' or 'b'
        const KEY_SIG_ACCIDENTALS = {
            'C':  {},
            'G':  { 'F':'#' },
            'D':  { 'F':'#','C':'#' },
            'A':  { 'F':'#','C':'#','G':'#' },
            'E':  { 'F':'#','C':'#','G':'#','D':'#' },
            'B':  { 'F':'#','C':'#','G':'#','D':'#','A':'#' },
            'F#': { 'F':'#','C':'#','G':'#','D':'#','A':'#','E':'#' },
            'C#': { 'F':'#','C':'#','G':'#','D':'#','A':'#','E':'#','B':'#' },
            'F':  { 'B':'b' },
            'Bb': { 'B':'b','E':'b' },
            'Eb': { 'B':'b','E':'b','A':'b' },
            'Ab': { 'B':'b','E':'b','A':'b','D':'b' },
            'Db': { 'B':'b','E':'b','A':'b','D':'b','G':'b' },
            'Gb': { 'B':'b','E':'b','A':'b','D':'b','G':'b','C':'b' },
            'Cb': { 'B':'b','E':'b','A':'b','D':'b','G':'b','C':'b','F':'b' },
        };

        /**
         * Returns the VexFlow accidental string to render inline before a note,
         * or null if the accidental is already covered by the key signature.
         *  '#' | 'b' | '##' | 'bb' | 'n' (natural sign to cancel key-sig) | null
         */
        function getInlineAccidental(noteName, keyRoot) {
            const m = noteName.match(/^([A-G])(bb|##|b|#)?/i);
            if (!m) return null;
            const letter    = m[1].toUpperCase();
            const canonical = m[2] || '';           // '', '#', 'b', '##', 'bb'
            const keySigAcc = (KEY_SIG_ACCIDENTALS[keyRoot] || {})[letter] || '';
            if (canonical === keySigAcc) return null;           // already in key sig
            if (keySigAcc !== '' && canonical === '') return 'n'; // natural cancels key-sig
            return { '#':'#', 'b':'b', '##':'##', 'bb':'bb' }[canonical] || null;
        }

        const beatDurations = {
            'whole':4,'half':2,'dotted-half':3,'quarter':1,'dotted-quarter':1.5,
            'eighth':0.5,'dotted-eighth':0.75,'sixteenth':0.25,
            'whole_rest':4,'half_rest':2,'quarter_rest':1,'eighth_rest':0.5,
        };

        const restTypes = new Set(['whole_rest','half_rest','quarter_rest','eighth_rest']);

        // ── Metronome click — Tone.js AudioContext ile tam senkron ──────────
        // Ayrı bir AudioContext kullanmak clock kaymasına yol açar; bunun yerine
        // Tone.js'in kendi context'ini kullanarak mutlak zamanla schedule ediyoruz.
        function scheduleMetronomeClick(toneTime, isFirstBeat = false) {
            try {
                const ctx  = Tone.getContext().rawContext;
                const osc  = ctx.createOscillator();
                const gain = ctx.createGain();
                osc.connect(gain);
                gain.connect(ctx.destination);
                osc.frequency.value = isFirstBeat ? 1200 : 800;
                osc.type = 'sine';
                gain.gain.setValueAtTime(isFirstBeat ? 0.5 : 0.3, toneTime);
                gain.gain.exponentialRampToValueAtTime(0.001, toneTime + 0.08);
                osc.start(toneTime);
                osc.stop(toneTime + 0.08);
            } catch(e) {}
        }

        function noteNameToVfKey(noteStr) {
            const m = noteStr.match(/^([A-G])(bb|##|b|#)?(\d+)$/i);
            if (!m) return null;
            return m[1].toLowerCase() + (m[2] || '') + '/' + m[3];
        }

        /**
         * Render one or two bars on a stave.
         * @param {HTMLElement} container
         * @param {Array}  notes      – array of {name, dur, isRest}
         * @param {string} clef
         * @param {string} keyRoot    – e.g. 'G'
         * @param {string} timeSig    – e.g. '4/4'
         * @param {boolean} showHeader – show clef / key / time sig
         * @param {string|null} refNote – single note name to display before melody as reference
         * @param {Object|null} colorMap – { index: color }
         */
        function renderMelodyStaff(container, notes, clef, keyRoot, timeSig, showHeader, refNote, colorMap) {
            if (typeof Vex === 'undefined' || !container) return;
            container.innerHTML = '';
            const VF  = Vex.Flow;
            const HS = window.HarmonivaStaff || { startPad: 40, span: function (n) { n = Math.max(1, n); return n * Math.max(40, Math.min(80, Math.round(160 / n))); } };
            let w = Math.max(container.clientWidth || 500, 300);
            // Site-wide spacing standard: 40-100px between notes depending on count.
            const glyphCount = (notes ? notes.length : 0) + (refNote ? 1 : 0);
            const noteSpanW = HS.span(glyphCount);
            if (window.innerWidth < 640) {
                // Mobile: widen the SVG to fit the span instead of squeezing —
                // the responsive-notation fitter makes the container swipeable.
                w = Math.max(w, 140 + noteSpanW);
            }
            const fmtW = Math.min(w - 120, noteSpanW);
            // Two rows if notes given, else single row
            const h = 160;
            const renderer = new VF.Renderer(container, VF.Renderer.Backends.SVG);
            renderer.resize(w, h);
            const ctx = renderer.getContext();

            // Parse time sig
            const [tNum, tDen] = (timeSig || '4/4').split('/').map(Number);
            const beatsPerBar  = tNum * (4 / (tDen || 4));

            // Build stave
            const staveX = 5;
            const staveY = 20;
            const stave  = new VF.Stave(staveX, staveY, w - staveX - 5);
            if (showHeader !== false) {
                stave.addClef(clef || 'treble');
                try { stave.addKeySignature(keyRoot || 'C'); } catch(e) {}
                try { stave.addTimeSignature(timeSig || '4/4'); } catch(e) {}
            } else {
                stave.addClef(clef || 'treble');
            }
            // Breathing room between the clef/signatures and the first note.
            stave.setNoteStartX(stave.getNoteStartX() + HS.startPad);
            stave.setContext(ctx).draw();

            if ((!notes || !notes.length) && !refNote) return;

            // Build StaveNotes
            const staveNotes = [];
            const refColor = '#9ca3af';

            // Reference note (shown first, gray, not part of answer)
            if (refNote) {
                const key = noteNameToVfKey(refNote);
                if (key) {
                    try {
                        // Stem direction: DOWN (-1) on/above middle line, UP (1) below.
                        // Middle line: treble=B4, bass=D3, alto=C4 (diatonic index octave*7+note)
                        const _kp = key.split('/');
                        const _ni = {c:0,d:1,e:2,f:3,g:4,a:5,b:6}[(_kp[0]||'').replace(/[#b]/g,'').toLowerCase()] ?? 0;
                        const _pos = (parseInt(_kp[1])||4) * 7 + _ni;
                        const _mid = (clef==='bass') ? 22 : (clef==='alto') ? 28 : 34; // B4=34,D3=22,C4=28
                        const refStemDir = (_pos >= _mid) ? -1 : 1;
                        const rn = new VF.StaveNote({ keys: [key], duration: 'q', clef: clef || 'treble', stem_direction: refStemDir });
                        rn.setStyle({ fillStyle: refColor, strokeStyle: refColor });
                        // Add "Ref" annotation
                        const ann = new VF.Annotation('Ref')
                            .setVerticalJustification(VF.Annotation.VerticalJustify.BOTTOM)
                            .setFont('Arial', 8);
                        rn.addModifier(ann, 0);
                        staveNotes.push({ note: rn, isRef: true });
                    } catch(e) {}
                }
            }

            // Bar-line tracking (16th-note units to avoid floating-point drift)
            let accBeats16 = 0;
            const beatsPerBar16 = Math.round(beatsPerBar * 4);

            notes.forEach((nd, idx) => {
                // Use the full VexFlow duration string (e.g. 'qd', '8d') so that
                // dotted notes get the correct internal tick count.  If we strip
                // the dot and add VF.Dot() manually the tick count stays at the
                // base-note value, which breaks beam-group boundary calculations.
                const dur   = vfDurFull[nd.dur] || 'q';
                const color = colorMap ? colorMap[idx] : null;
                let n;
                try {
                    if (nd.isRest || restTypes.has(nd.dur)) {
                        n = new VF.StaveNote({ keys: ['b/4'], duration: dur, clef: clef || 'treble' });
                    } else {
                        const key = noteNameToVfKey(nd.name);
                        if (!key) return;
                        // 'qd' / '8d' / 'hd' — the 'd' suffix sets the correct
                        // tick count so beam-group calculations respect dotted
                        // durations.  In VexFlow 4.x the 'd' suffix does NOT
                        // auto-render the augmentation dot — that still requires
                        // an explicit Dot modifier.
                        n = new VF.StaveNote({ keys: [key], duration: dur, clef: clef || 'treble' });
                        if (nd.dur && nd.dur.startsWith('dotted-')) {
                            try { n.addModifier(new VF.Dot(), 0); } catch(e) {}
                        }
                        const inlineAcc = getInlineAccidental(nd.name, keyRoot);
                        if (inlineAcc) try { n.addModifier(new VF.Accidental(inlineAcc), 0); } catch(e) {}
                    }
                    if (color) n.setStyle({ fillStyle: color, strokeStyle: color });
                    staveNotes.push({ note: n, isRef: false, isBar: false });

                    // Insert bar line when a bar is completed (not after the last note)
                    accBeats16 += Math.round((beatDurations[nd.dur] || 1) * 4);
                    if (beatsPerBar16 > 0 && accBeats16 % beatsPerBar16 === 0 && idx < notes.length - 1) {
                        try {
                            const barNote = new VF.BarNote();
                            staveNotes.push({ note: barNote, isRef: false, isBar: true });
                        } catch(e) {}
                    }
                } catch(e) {}
            });

            if (!staveNotes.length) return;

            const voice = new VF.Voice({ numBeats: tNum, beatValue: tDen });
            voice.setMode(VF.Voice.Mode.SOFT);
            voice.addTickables(staveNotes.map(s => s.note));

            try {
                // Compound meters group by dotted-quarter (3 eighth-notes);
                // simple meters group by quarter-note beat (1 quarter each).
                // 3/8 is treated as one dotted-quarter beat group for the full bar.
                const isCompound = ['6/8','9/8','12/8'].includes(timeSig);
                const is3_8 = timeSig === '3/8';
                // Visual beam grouping is always quarter-note based for simple meters
                // (including /2 meters). Metronome beat unit (half note for /2) is
                // a separate concept and must not influence beam boundaries.
                const beatGroupFrac = (isCompound || is3_8)
                    ? new VF.Fraction(3, 8)
                    : new VF.Fraction(1, 4);

                // Generate beams bar-by-bar so a beam never crosses a barline.
                // BarNote entries in staveNotes act as bar separators.
                const allBeams = [];
                let barNotes = [];
                for (const s of staveNotes) {
                    if (s.isRef) continue;
                    if (s.isBar) {
                        if (barNotes.length > 0) {
                            try {
                                VF.Beam.generateBeams(barNotes, { groups: [beatGroupFrac] })
                                       .forEach(b => allBeams.push(b));
                            } catch(be) {}
                            barNotes = [];
                        }
                    } else {
                        barNotes.push(s.note);
                    }
                }
                if (barNotes.length > 0) {
                    try {
                        VF.Beam.generateBeams(barNotes, { groups: [beatGroupFrac] })
                               .forEach(b => allBeams.push(b));
                    } catch(be) {}
                }

                new VF.Formatter().joinVoices([voice]).format([voice], fmtW);
                voice.draw(ctx, stave);
                allBeams.forEach(b => b.setContext(ctx).draw());
            } catch(e) {
                try {
                    new VF.Formatter().joinVoices([voice]).format([voice], fmtW);
                    voice.draw(ctx, stave);
                } catch(e2) {}
            }
        }

        // ── Segment helpers ─────────────────────────────────────────────────

        function splitIntoBarGroups(allNotes, allNoteValues, beatsPerBar, barsPerSegment) {
            // Returns array of segments, each segment = array of {name,dur,isRest}
            const segments = [];
            let curSeg    = [];
            let curBeats  = 0;
            let barCount  = 0;

            for (let i = 0; i < allNotes.length; i++) {
                const dur    = allNoteValues[i] || 'quarter';
                const beats  = beatDurations[dur] || 1;
                const isRest = restTypes.has(dur);
                curSeg.push({ name: allNotes[i] || null, dur, isRest });
                curBeats += beats;

                // Check if we've completed a bar
                if (Math.abs(curBeats % beatsPerBar) < 0.01) {
                    barCount++;
                    if (barCount >= barsPerSegment) {
                        segments.push(curSeg);
                        curSeg    = [];
                        curBeats  = 0;
                        barCount  = 0;
                    }
                }
            }
            if (curSeg.length > 0) segments.push(curSeg);
            return segments;
        }

        // ── Main init ────────────────────────────────────────────────────────

        window.{{ $mdInitFn }} = function() {
            window._practiceGen = (window._practiceGen || 0) + 1;
            const myGen = window._practiceGen;
            if (typeof lucide !== 'undefined') lucide.createIcons();

            const playButton     = document.getElementById('playButton');
            const nextButton     = document.getElementById('nextPracticeBtn');
            const answerSection  = document.getElementById('answerSection');
            const dictInput      = document.getElementById('dictationAnswer');
            const feedbackMsg    = document.getElementById('feedbackMessage');
            const staffContainer = document.getElementById('melody-staff-container');
            const staffOutput    = document.getElementById('melody-staff-output');
            const beatDisplay    = document.getElementById('beatDisplay');
            const beatProgress   = document.getElementById('beatProgress');
            const octDisplay     = document.getElementById('octaveDisplay');
            const submitBtn      = document.getElementById('submitAnswer');
            const nextSegBtn     = document.getElementById('nextSegBtn');
            const segIndicator   = document.getElementById('segIndicator');
            const segLabel       = document.getElementById('segLabel');

            if (!playButton || !answerSection) return;

            document.getElementById('correctMelodyReveal')?.classList.add('hidden');

            const clef      = answerSection.dataset.clef    || 'treble';
            const keyRoot   = answerSection.dataset.keyRoot || 'C';
            const timeSig   = answerSection.dataset.timeSig || '4/4';
            const totalBars    = parseInt(answerSection.dataset.bars) || 2;
            const refNote      = answerSection.dataset.refNote || null;
            const useMetronome = answerSection.dataset.metronome !== '0';

            const allNotes      = playButton.dataset.notes      ? playButton.dataset.notes.split(',').map(s=>s.trim())      : [];
            const allNoteValues = playButton.dataset.noteValues  ? playButton.dataset.noteValues.split(',').map(s=>s.trim()) : allNotes.map(()=>'quarter');
            const tempo         = parseInt(playButton.dataset.tempo) || 50;
            const [tNum, tDen] = timeSig.split('/').map(Number);
            const isDenomTwo   = tDen === 2;
            // /2 meters: BPM = half-note speed, so quarter-note gap = 30s/BPM
            const noteGapMs    = Math.round((isDenomTwo ? 30000 : 60000) / tempo);
            const beatsPerBar  = tNum * (4 / (tDen || 4));

            // Build segments (always 2 bars per segment)
            const barsPerSeg  = 2;
            const segments    = splitIntoBarGroups(allNotes, allNoteValues, beatsPerBar, barsPerSeg);
            const totalSegs   = segments.length;

            // Per-segment state
            let currentSegIdx    = 0;
            let segAnswers       = new Array(totalSegs).fill(null); // null=unanswered
            let segCorrectness   = new Array(totalSegs).fill(null);
            let pickedNotes      = [];
            let currentOctave    = clef === 'bass' ? 2 : (clef === 'alto' ? 3 : 4);
            let selectedDur      = 'quarter';
            let selectedAcc      = null; // null = nothing chosen; '' = natural explicitly chosen
            let isSegAnswered    = false;
            let isAllAnswered    = false;

            // ── Segment indicator ────────────────────────────────────────────
            function buildSegIndicator() {
                if (!segIndicator) return;
                segIndicator.innerHTML = '';
                for (let i = 0; i < totalSegs; i++) {
                    const dot = document.createElement('div');
                    dot.className = 'md-seg-dot';
                    segIndicator.appendChild(dot);
                }
                updateSegIndicator();
            }

            function updateSegIndicator() {
                const dots = segIndicator ? segIndicator.querySelectorAll('.md-seg-dot') : [];
                dots.forEach((dot, i) => {
                    dot.classList.remove('active','done-ok','done-err');
                    if (i === currentSegIdx)      dot.classList.add('active');
                    else if (segCorrectness[i] === true)  dot.classList.add('done-ok');
                    else if (segCorrectness[i] === false) dot.classList.add('done-err');
                });
                const startBar = currentSegIdx * barsPerSeg + 1;
                const endBar   = Math.min(startBar + barsPerSeg - 1, totalBars);
                if (segLabel) segLabel.textContent = totalSegs > 1 ? `Bars ${startBar}–${endBar}` : `${totalBars} bars`;
            }

            buildSegIndicator();

            // Current segment notes (pitches only, for answer)
            function segPitchNotes() {
                return (segments[currentSegIdx] || []).filter(n => !n.isRest).map(n => n.name);
            }

            // ── Duration selector ────────────────────────────────────────────
            const durBtns = document.querySelectorAll('.md-dur-btn');
            function setDur(dur) {
                selectedDur = dur;
                durBtns.forEach(b => b.classList.toggle('md-selected', b.dataset.dur === dur));
                if (restTypes.has(dur)) {
                    addNote(null, dur, true);
                    setTimeout(() => setDur('quarter'), 200);
                }
            }
            durBtns.forEach(b => b.addEventListener('click', () => setDur(b.dataset.dur)));
            setDur('quarter');

            // ── Accidental selector ──────────────────────────────────────────
            // selectedAcc distinguishes "nothing picked yet" (null) from "natural
            // explicitly picked" (''), so the ♮ button can highlight like the
            // others — both still produce a plain, unmodified note letter.
            const accBtns = document.querySelectorAll('.md-acc-btn');
            function setAcc(acc) {
                selectedAcc = (selectedAcc === acc) ? null : acc;
                accBtns.forEach(b => b.classList.toggle('md-acc-active', selectedAcc !== null && b.dataset.acc === selectedAcc));
            }
            accBtns.forEach(b => b.addEventListener('click', () => setAcc(b.dataset.acc)));

            // ── Octave controls ──────────────────────────────────────────────
            const octMin = clef === 'bass' ? 2 : (clef === 'alto' ? 3 : 3);
            const octMax = clef === 'bass' ? 4 : (clef === 'alto' ? 5 : 5);
            const octDownValEl = document.getElementById('octaveDownVal');
            const octUpValEl   = document.getElementById('octaveUpVal');

            function updateOctDisplay() {
                const v = String(currentOctave);
                if (octDisplay)    octDisplay.textContent    = v;
                if (octDownValEl)  octDownValEl.textContent  = v;
                if (octUpValEl)    octUpValEl.textContent    = v;
            }
            function flashOctBtn(id) {
                const btn = document.getElementById(id);
                if (!btn) return;
                btn.classList.remove('oct-flashing');
                void btn.offsetWidth; // reflow to restart animation
                btn.classList.add('oct-flashing');
                setTimeout(() => btn.classList.remove('oct-flashing'), 300);
            }

            updateOctDisplay(); // set initial value

            document.getElementById('octaveDown')?.addEventListener('click', () => {
                if (currentOctave > octMin) { currentOctave--; flashOctBtn('octaveDown'); updateOctDisplay(); }
            });
            document.getElementById('octaveUp')?.addEventListener('click', () => {
                if (currentOctave < octMax) { currentOctave++; flashOctBtn('octaveUp'); updateOctDisplay(); }
            });

            // ── Beat progress ─────────────────────────────────────────────────
            function updateProgress() {
                let total = 0;
                pickedNotes.forEach(nd => { total += (beatDurations[nd.dur] || 1); });
                const barIdx     = Math.floor(total / beatsPerBar);
                const curBar     = Math.min(barIdx + 1, totalBars);
                const inBar      = total - barIdx * beatsPerBar;
                const pct        = Math.min(inBar / beatsPerBar, 1) * 100;
                const beatsRound = Math.round(inBar * 4) / 4;
                if (beatDisplay) beatDisplay.textContent = pt('bar_beats', {bar: curBar, done: beatsRound, total: tNum});
                if (beatProgress) beatProgress.style.width = pct + '%';
            }

            // ── Staff refresh ─────────────────────────────────────────────────
            // refNote sadece Play'e basıldıktan sonra gösterilir
            let hasPlayed = false;

            function refreshStaff(colorMap) {
                const activeRef = hasPlayed ? refNote : null;
                if (staffOutput) renderMelodyStaff(staffOutput, pickedNotes, clef, keyRoot, timeSig, true, activeRef, colorMap || null);
            }
            setTimeout(() => { if (window._practiceGen === myGen) refreshStaff(); }, 120);

            // ── Add note / rest ──────────────────────────────────────────────
            function addNote(noteLetter, dur, isRest) {
                if (isSegAnswered) return;
                const name = isRest ? null : noteLetter + (selectedAcc || '') + currentOctave;
                pickedNotes.push({ name, dur, isRest: !!isRest });
                const pitches = pickedNotes.filter(n => !n.isRest).map(n => n.name);
                if (dictInput) dictInput.value = pitches.join(',');
                if (submitBtn) submitBtn.disabled = pitches.length === 0;
                updateProgress();
                refreshStaff();
            }

            document.querySelectorAll('.note-name-btn').forEach(btn => {
                btn.addEventListener('click', () => {
                    if (isSegAnswered) return;
                    addNote(btn.dataset.note, selectedDur, false);
                    btn.classList.add('md-flash');
                    setTimeout(() => btn.classList.remove('md-flash'), 160);
                    try { window.HarmonivaAudio.playNote(btn.dataset.note + (selectedAcc || '') + currentOctave, 0.35); } catch(e) {}
                    setAcc(null);
                });
            });

            document.getElementById('deleteLastNote')?.addEventListener('click', () => {
                if (isSegAnswered) return;
                pickedNotes.pop();
                const pitches = pickedNotes.filter(n => !n.isRest).map(n => n.name);
                if (dictInput) dictInput.value = pitches.join(',');
                if (submitBtn) submitBtn.disabled = pitches.length === 0;
                updateProgress();
                refreshStaff();
            });

            document.getElementById('clearAllNotes')?.addEventListener('click', () => {
                if (isSegAnswered) return;
                pickedNotes = [];
                if (dictInput) dictInput.value = '';
                if (submitBtn) submitBtn.disabled = true;
                updateProgress();
                refreshStaff();
            });

            // ── Play my melody ────────────────────────────────────────────────
            document.getElementById('playMyAnswer')?.addEventListener('click', async () => {
                const toPlay = pickedNotes.filter(n => !n.isRest);
                if (!toPlay.length) return;
                await Tone.start();
                let offset = 0;
                toPlay.forEach(nd => {
                    const durBeats  = beatDurations[nd.dur] || 1;
                    const durMs     = durBeats * noteGapMs;
                    const noteDurS  = Math.min(durMs / 1000 * 0.85, 2.0);
                    ((t, note, d) => setTimeout(() => {
                        try { window.HarmonivaAudio.playNote(note, d); } catch(e) {}
                    }, t))(offset, nd.name, noteDurS);
                    offset += durMs;
                });
            });

            // ── Count-in + melodi oynatma (tam Tone.js zamanlama) ────────────
            async function playCurrentSegment() {
                if (playButton.disabled) return;
                playButton.disabled = true;
                playButton.innerHTML = '<i data-lucide="loader" class="w-5 h-5 inline animate-spin"></i> ' + pt('loading');
                if (typeof lucide !== 'undefined') lucide.createIcons();

                // 1) AudioContext'i başlat VE sampler'ın tamamen yüklenmesini bekle.
                //    Bu olmadan ilk basışta sesler zamanında çalınamaz.
                await Tone.start();
                await window.HarmonivaAudio.prepare();

                if (window._practiceGen !== myGen) return;
                hasPlayed = true;
                refreshStaff();

                // /2 meters: BPM = half-note speed; quarter-note = 30/BPM seconds.
                // Metronome always clicks at BPM-beat intervals (60/BPM = half-note for /2).
                const quarterS       = isDenomTwo ? (30 / tempo) : (60 / tempo);
                const metroBeatS     = 60 / tempo;
                const metroBeatsPerBar = isDenomTwo ? tNum : Math.round(beatsPerBar);

                const bufS       = 0.05;
                const now        = window.HarmonivaAudio.now() + bufS;
                const countBeats = tNum;                 // 1 ölçü count-in
                const segNotes   = segments[currentSegIdx] || [];

                // Count-in: referans nota + metronom (metroBeatS aralıklarla)
                if (refNote) {
                    window.HarmonivaAudio.playNoteAt(refNote, 0.6, now);
                }
                if (useMetronome) {
                    for (let b = 0; b < countBeats; b++) {
                        scheduleMetronomeClick(now + b * metroBeatS, b === 0);
                    }
                }

                // Melodi count-in bittikten hemen sonra başlar
                const melodyStart = now + countBeats * metroBeatS;

                // Metronom melodi boyunca devam eder; her ölçü başı vurgulanır
                if (useMetronome) {
                    const segBeats = Math.round(barsPerSeg * metroBeatsPerBar);
                    for (let b = 0; b < segBeats; b++) {
                        scheduleMetronomeClick(melodyStart + b * metroBeatS, b % metroBeatsPerBar === 0);
                    }
                }

                // Melodi notaları tek tek schedule edilir
                let offS = 0;
                segNotes.forEach(nd => {
                    const durS     = (beatDurations[nd.dur] || 1) * quarterS;
                    const noteDurS = Math.min(durS * 0.85, 2.0);
                    if (!nd.isRest) {
                        window.HarmonivaAudio.playNoteAt(nd.name, noteDurS, melodyStart + offS);
                    }
                    offS += durS;
                });

                const totalS = countBeats * metroBeatS + offS;
                playButton.innerHTML = '<i data-lucide="loader" class="w-5 h-5 inline animate-spin"></i> ' + pt('count_in');
                if (typeof lucide !== 'undefined') lucide.createIcons();

                setTimeout(() => {
                    if (window._practiceGen !== myGen) return;
                    playButton.disabled = false;
                    playButton.innerHTML = '<i data-lucide="play" class="w-5 h-5"></i> ' + pt('play_again');
                    if (typeof lucide !== 'undefined') lucide.createIcons();
                }, (totalS + 0.2) * 1000);
            }

            playButton.onclick = playCurrentSegment;

            // ── Check answer ──────────────────────────────────────────────────
            if (submitBtn) {
                submitBtn.addEventListener('click', async function() {
                    if (isSegAnswered || !dictInput) return;
                    const userPitches   = dictInput.value.trim().toUpperCase().replace(/\s+/g, '');
                    if (!userPitches) return;

                    const correctPitches = segPitchNotes().join(',').toUpperCase().replace(/\s+/g,'');
                    const isCorrect      = userPitches === correctPitches;

                    isSegAnswered = true;
                    segAnswers[currentSegIdx]     = userPitches;
                    segCorrectness[currentSegIdx] = isCorrect;
                    updateSegIndicator();

                    submitBtn.disabled = true;

                    // Color staff
                    const cm = {};
                    pickedNotes.filter(n => !n.isRest).forEach((_, i) => { cm[i] = isCorrect ? '#16a34a' : '#dc2626'; });
                    refreshStaff(cm);

                    if (staffContainer) {
                        staffContainer.className = `w-full border-2 rounded-xl overflow-hidden transition-colors duration-300 ${isCorrect ? 'border-green-400 bg-green-50' : 'border-red-400 bg-red-50'}`;
                        staffContainer.style.cssText = 'min-height:160px;display:flex;align-items:center;';
                    }

                    // On a wrong answer, reveal the correct segment on a green staff below
                    if (!isCorrect) {
                        const reveal       = document.getElementById('correctMelodyReveal');
                        const revealOutput = document.getElementById('correctMelodyStaffOutput');
                        if (reveal && revealOutput) {
                            reveal.classList.remove('hidden');
                            const segNotes = segments[currentSegIdx] || [];
                            const greenMap = {};
                            segNotes.forEach((_, i) => { greenMap[i] = '#16a34a'; });
                            renderMelodyStaff(revealOutput, segNotes, clef, keyRoot, timeSig, true, null, greenMap);
                        }
                    }

                    feedbackMsg.textContent = isCorrect
                        ? `✓ Correct! ${totalSegs > 1 ? 'Segment ' + (currentSegIdx + 1) + ' done.' : 'Perfect melody!'}`
                        : `✗ Incorrect. The correct melody is shown below your answer.`;
                    feedbackMsg.className = `mt-4 p-4 rounded-xl text-center font-medium ${isCorrect ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'}`;
                    feedbackMsg.style.display = 'block';

                    const isLastSeg = currentSegIdx >= totalSegs - 1;

                    if (!isLastSeg) {
                        // Show "Next Segment" button
                        if (nextSegBtn) nextSegBtn.style.display = 'flex';
                        submitBtn.style.display = 'none';
                    } else {
                        // All segments done — send full answer to server for stats
                        const fullAnswer = allNotes.join(',');
                        try {
                            const resp = await fetch('/api/practice/check-answer', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                },
                                body: JSON.stringify({
                                    practice_id: null,
                                    question_id: parseInt(answerSection.dataset.practiceId),
                                    answer: fullAnswer,
                                    slug: 'melodic-dictation',
                                }),
                            });
                            const data = await resp.json();
                            isAllAnswered = true;
                            playButton.style.display  = 'none';
                            submitBtn.style.display   = 'none';
                            if (nextSegBtn) nextSegBtn.style.display = 'none';
                            const playFullBtn = document.getElementById('playFullPiece');
                            if (playFullBtn && totalSegs > 1) playFullBtn.style.display = 'flex';
                            if (nextButton) nextButton.style.display = 'flex';

                            const allOk = segCorrectness.every(Boolean);
                            const sTotal   = document.getElementById('scoreTotal');
                            const sCorrect = document.getElementById('scoreCorrect');
                            const xp       = document.getElementById('xpEarned');
                            if (sTotal) sTotal.textContent = parseInt(sTotal.textContent || 0) + 1;
                            if (allOk) {
                                if (sCorrect) sCorrect.textContent = parseInt(sCorrect.textContent || 0) + 1;
                                if (xp) xp.textContent = parseInt(xp.textContent || 0) + 10;
                            }
                        } catch(e) {
                            isAllAnswered = true;
                            playButton.style.display = 'none';
                            submitBtn.style.display  = 'none';
                            const playFullBtnC = document.getElementById('playFullPiece');
                            if (playFullBtnC && totalSegs > 1) playFullBtnC.style.display = 'flex';
                            if (nextButton) nextButton.style.display = 'flex';
                        }
                    }
                });
            }

            // ── Next Segment ──────────────────────────────────────────────────
            if (nextSegBtn) {
                nextSegBtn.addEventListener('click', () => {
                    currentSegIdx++;
                    isSegAnswered = false;
                    pickedNotes   = [];
                    selectedAcc   = null;
                    if (dictInput) dictInput.value = '';
                    if (submitBtn) { submitBtn.disabled = true; submitBtn.style.display = 'flex'; }
                    nextSegBtn.style.display = 'none';
                    if (feedbackMsg) feedbackMsg.style.display = 'none';
                    if (staffContainer) {
                        staffContainer.className = 'w-full border-2 border-gray-200 rounded-xl bg-white overflow-hidden transition-colors duration-300';
                        staffContainer.style.cssText = 'min-height:160px;display:flex;align-items:center;';
                    }
                    document.getElementById('correctMelodyReveal')?.classList.add('hidden');
                    updateProgress();
                    updateSegIndicator();
                    refreshStaff();
                    setDur('quarter');
                    setAcc('');
                });
            }

            // ── Play from start ──────────────────────────────────────────────
            document.getElementById('playFullPiece')?.addEventListener('click', async () => {
                await Tone.start();
                await window.HarmonivaAudio.prepare();
                if (window._practiceGen !== myGen) return;

                const quarterS_fp = isDenomTwo ? (30 / tempo) : (60 / tempo);
                const now         = window.HarmonivaAudio.now() + 0.05;

                // Referans nota + 1 BPM-beat boşluk, sonra tüm melodi
                if (refNote) {
                    window.HarmonivaAudio.playNoteAt(refNote, 0.6, now);
                }
                let offS = 60 / tempo; // 1 BPM-beat gap after ref note
                allNotes.forEach((note, i) => {
                    const dur      = allNoteValues[i] || 'quarter';
                    const durS     = (beatDurations[dur] || 1) * quarterS_fp;
                    const noteDurS = Math.min(durS * 0.85, 2.0);
                    window.HarmonivaAudio.playNoteAt(note, noteDurS, now + offS);
                    offS += durS;
                });
            });

            // Show initial empty staff with reference note
            setTimeout(() => { if (window._practiceGen === myGen) refreshStaff(); }, 120);

            // ── Pre-warm: ilk kullanıcı etkileşiminde sampler'ı arka planda yükle ──
            // Böylece Play'e ilk basışta yükleme gecikmesi yaşanmaz.
            document.addEventListener('click', function mdWarmUp() {
                document.removeEventListener('click', mdWarmUp, true);
                Tone.start().then(() => {
                    try { window.HarmonivaAudio.prepare(); } catch(e) {}
                });
            }, { once: true, capture: true });

        }; // end {{ $mdInitFn }}

        document.addEventListener('livewire:init', function() {
            window.{{ $mdInitFn }}();
            Livewire.on('practice-updated', () => setTimeout(() => window.{{ $mdInitFn }}(), 50));
        });
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof Livewire === 'undefined') window.{{ $mdInitFn }}();
        });
        </script>

        @endif
    </main>
</div>
