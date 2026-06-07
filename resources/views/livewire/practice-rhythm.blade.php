<div>
    <!-- VexFlow -->
    <script src="https://cdn.jsdelivr.net/npm/vexflow@4.2.2/build/cjs/vexflow.js"></script>

    <!-- Main Content -->
    <main class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-6">

        @if(!$currentPractice)
            <div class="card p-12 text-center">
                <h3 class="text-lg font-semibold text-gray-900 mb-2">No exercises found</h3>
                <p class="text-gray-500 mb-4">No rhythm exercises match your settings.</p>
                <a href="/exercise-setup" class="btn-primary text-white font-semibold py-2.5 px-6 rounded-lg inline-flex items-center gap-2">
                    <i data-lucide="settings-2" class="w-4 h-4"></i> Adjust Settings
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
                    <a href="/learn" class="absolute left-0 top-1/2 -translate-y-1/2 w-12 h-12 flex items-center justify-center rounded-xl bg-white/10 hover:bg-white/20 text-white transition-all">
                        <i data-lucide="arrow-left" class="w-6 h-6"></i>
                    </a>
                    <div class="text-center">
                        <h1 class="text-xl font-bold text-white">
                            {{ $rhythmMode === 'reading' ? 'Rhythmic Reading' : 'Rhythm Dictation' }}
                        </h1>
                        <p class="text-white/80 text-sm">
                            {{ $rhythmMode === 'reading' ? 'Tap the rhythm you see on the staff' : 'Listen and identify the rhythm pattern' }}
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
                    Tempo: <span class="font-semibold text-gray-700">{{ $currentPractice->tempo }} BPM</span>
                </div>

                <!-- Play button + Next button -->
                <div class="card p-5 mb-5">
                    <div class="flex flex-col items-center gap-3">
                        <div class="flex gap-3 items-center">
                            <button id="playButton"
                                class="btn-primary text-white font-semibold py-3 px-8 rounded-lg flex items-center gap-2 hover:shadow-lg transition-shadow"
                                data-notes="{{ $correctStr }}"
                                data-tempo="{{ $currentPractice->tempo }}"
                                data-time-sig="{{ $currentPractice->time_signature }}"
                                data-metronome="1">
                                <i data-lucide="play" class="w-5 h-5"></i>
                                Play
                            </button>
                            @if ($currentPracticeIndex < (count($practices) - 1))
                                <button id="nextPracticeBtn" wire:click="getNextPractice"
                                    class="font-semibold py-3 px-8 rounded-lg flex items-center gap-2 bg-blue-100 text-blue-700 border-2 border-blue-300 hover:bg-blue-200"
                                    style="display:none">
                                    <i data-lucide="arrow-right" class="w-5 h-5"></i> Next
                                </button>
                            @else
                                <a href="/learn" id="nextPracticeBtn"
                                    class="font-semibold py-3 px-8 rounded-lg flex items-center gap-2 bg-blue-100 text-blue-700 border-2 border-blue-300 hover:bg-blue-200"
                                    style="display:none">
                                    <i data-lucide="check" class="w-5 h-5"></i> Finish
                                </a>
                            @endif
                        </div>
                        <p id="playStatus" class="text-sm text-gray-500">Press Play — the metronome will count you in</p>
                        <p class="text-xs text-gray-400 -mt-1">
                            Use <kbd class="px-1.5 py-0.5 bg-gray-100 border border-gray-300 rounded text-xs font-mono">Tab</kbd>
                            to start &amp; tap, or <kbd class="px-1.5 py-0.5 bg-gray-100 border border-gray-300 rounded text-xs font-mono">Space</kbd> to tap
                        </p>

                        <!-- Tap Button — visible from start, enabled only when rhythm is playing -->
                        <button id="tapButton"
                            class="w-64 h-20 rounded-2xl font-bold text-xl select-none transition-all
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

                @else
                {{-- ─────────────────────────────────────────── --}}
                {{-- DICTATION MODE                              --}}
                {{-- ─────────────────────────────────────────── --}}

                <!-- Time signature display -->
                <div class="w-full bg-gray-50 border-2 border-dashed border-gray-200 rounded-xl flex items-center justify-center mb-6 py-5">
                    <div class="text-center">
                        <p class="text-sm text-gray-500 mb-1">Time Signature</p>
                        <div class="text-5xl font-bold text-gray-800">{{ $currentPractice->time_signature }}</div>
                        <p class="text-sm text-gray-400 mt-1">Tempo: {{ $currentPractice->tempo }} BPM • {{ $currentPractice->bars }} bar(s)</p>
                    </div>
                </div>

                <!-- Play Button -->
                <div class="card p-5 mb-6">
                    <div class="flex flex-col items-center">
                        <div class="flex gap-3">
                            <button id="playButton"
                                class="btn-primary text-white font-semibold py-3 px-8 rounded-lg flex items-center gap-2 mb-3 hover:shadow-lg transition-shadow"
                                data-notes="{{ $correctStr }}"
                                data-tempo="{{ $currentPractice->tempo }}"
                                data-time-sig="{{ $currentPractice->time_signature }}"
                                data-metronome="{{ $metronome ? '1' : '0' }}">
                                <i data-lucide="play" class="w-5 h-5"></i>
                                Play Rhythm
                            </button>
                            @if ($currentPracticeIndex < (count($practices) - 1))
                                <button id="nextPracticeBtn" wire:click="getNextPractice"
                                    class="font-semibold py-3 px-8 rounded-lg flex items-center gap-2 mb-3 bg-blue-100 text-blue-700 border-2 border-blue-300 hover:bg-blue-200"
                                    style="display:none">
                                    <i data-lucide="arrow-right" class="w-5 h-5"></i> Next
                                </button>
                            @else
                                <a href="/learn" id="nextPracticeBtn"
                                    class="font-semibold py-3 px-8 rounded-lg flex items-center gap-2 mb-3 bg-blue-100 text-blue-700 border-2 border-blue-300 hover:bg-blue-200"
                                    style="display:none">
                                    <i data-lucide="check" class="w-5 h-5"></i> Finish
                                </a>
                            @endif
                        </div>
                        <p id="playStatus" class="text-sm text-gray-500">Listen to the rhythm pattern</p>
                    </div>
                </div>

                <!-- Answer Options: 4 VexFlow staves in 2×2 grid -->
                <p class="text-sm text-gray-500 mb-3 text-center">Which rhythm did you hear?</p>
                <div id="answerOptions" class="grid grid-cols-2 gap-3"
                     data-target="{{ $correctStr }}"
                     data-practice-id="{{ $currentPractice->id }}">
                    @foreach($allOptions as $optIdx => $opt)
                        <button class="answer-btn card p-3 text-left transition-all hover:shadow-md border-2 border-transparent rounded-xl"
                                data-answer="{{ $opt['value'] }}"
                                data-notes='{{ json_encode(array_values($opt['notes'])) }}'>
                            <div class="staff-container w-full rounded overflow-hidden" style="height:90px;min-height:90px;"></div>
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
            <span><span id="scoreCorrect">0</span> / <span id="scoreTotal">0</span> Correct</span>
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
            // Triplet groups — token represents the ENTIRE 3-note group
            'triplet-quarter': 2,   // 3 quarter-note triplets filling 2 quarter-note beats
            'triplet-eighth' : 1,   // 3 eighth-note  triplets filling 1 quarter-note beat
        };

        // VexFlow duration codes (key: note value string, value: VexFlow duration)
        const vfDurations = {
            'whole': 'w',       'half': 'h',       'quarter': 'q',
            'eighth': '8',      'sixteenth': '16',
            'whole_rest': 'wr', 'half_rest': 'hr', 'quarter_rest': 'qr',
            'eighth_rest': '8r','dotted-half': 'hd','dotted-quarter': 'qd',
            'dotted-eighth': '8d',
        };

        // renderRhythmStave(container, noteArray, timeSig, colorMap, h)
        // h: optional SVG/container height (default 105). For reading mode pass 161.
        function renderRhythmStave(container, noteArray, timeSig, colorMap, h) {
            if (typeof Vex === 'undefined' || !container || !noteArray.length) return null;
            container.innerHTML = '';

            const VF     = Vex.Flow;
            const width  = Math.max(container.clientWidth || 400, 200);
            const height = h || 105;
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

            // Expand note array: triplet tokens become 3 StaveNotes + track Tuplet groups
            const staveNotes = [];
            const tuplets    = [];
            noteArray.forEach((noteName, idx) => {
                const color = colorMap && colorMap[idx] ? colorMap[idx] : null;
                if (noteName === 'triplet-quarter' || noteName === 'triplet-eighth') {
                    const dur   = noteName === 'triplet-quarter' ? 'q' : '8';
                    const group = [];
                    for (let t = 0; t < 3; t++) {
                        const n = new VF.StaveNote({ keys: ['b/4'], duration: dur, stem_direction: -1 });
                        if (color) n.setStyle({ fillStyle: color, strokeStyle: color });
                        group.push(n);
                        staveNotes.push(n);
                    }
                    tuplets.push(new VF.Tuplet(group, { numNotes: 3, notesOccupied: 2, bracketed: true, ratioed: false }));
                } else {
                    const dur  = vfDurations[noteName] || 'q';
                    const note = new VF.StaveNote({ keys: ['b/4'], duration: dur, stem_direction: -1 });
                    if (color) note.setStyle({ fillStyle: color, strokeStyle: color });
                    staveNotes.push(note);
                }
            });

            if (!staveNotes.length) return null;

            const voice = new VF.Voice({ numBeats, beatValue });
            voice.setMode(VF.Voice.Mode.SOFT);
            voice.addTickables(staveNotes);

            try {
                const beams = VF.Beam.generateBeams(staveNotes);
                new VF.Formatter().joinVoices([voice]).format([voice], width - 70);
                voice.draw(context, stave);
                beams.forEach(b => b.setContext(context).draw());
                tuplets.forEach(t => t.setContext(context).draw());
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
                playButton.innerHTML = '<i data-lucide="loader-2" class="w-5 h-5 animate-spin inline"></i> Playing...';
                if (typeof lucide !== 'undefined') lucide.createIcons();

                await Tone.start();

                let totalDurationMs = 0;
                for (const note of notes) totalDurationMs += (noteDurations[note] || 1) * beatMs;
                const totalRhythmBeats = Math.ceil(totalDurationMs / beatMs);

                // 1-bar pre-count (always)
                for (let b = 0; b < beatsPerBar; b++) {
                    ((t, beat) => setTimeout(() => playMetronomeClick(beat === 0), t))(b * beatMs, b);
                }
                const startOffset = beatsPerBar * beatMs;

                // Metronome during rhythm (when enabled)
                if (useMetronome) {
                    for (let b = 0; b < totalRhythmBeats; b++) {
                        ((t, beat) => setTimeout(() => playMetronomeClick(beat % beatsPerBar === 0), t))(startOffset + b * beatMs, b);
                    }
                }

                // Rhythm notes (triplet tokens play 3 sub-notes)
                let noteOffset = startOffset;
                for (const note of notes) {
                    const durBeats = noteDurations[note] || 1;
                    if (note === 'triplet-quarter') {
                        // 3 notes evenly in 2 beats (interval = 2/3 beat)
                        for (let ti = 0; ti < 3; ti++) {
                            ((t) => setTimeout(() => window.HarmonivaAudio.playNote('C4', 0.2), t))(noteOffset + Math.round(ti * (2/3) * beatMs));
                        }
                    } else if (note === 'triplet-eighth') {
                        // 3 notes evenly in 1 beat (interval = 1/3 beat)
                        for (let ti = 0; ti < 3; ti++) {
                            ((t) => setTimeout(() => window.HarmonivaAudio.playNote('C4', 0.2), t))(noteOffset + Math.round(ti * (1/3) * beatMs));
                        }
                    } else if (!note.includes('rest')) {
                        ((t) => setTimeout(() => window.HarmonivaAudio.playNote('C4', 0.2), t))(noteOffset);
                    }
                    noteOffset += durBeats * beatMs;
                }

                setTimeout(() => {
                    if (window._practiceGen !== myGen) return;
                    playButton.disabled = false;
                    playButton.innerHTML = '<i data-lucide="play" class="w-5 h-5"></i> Play Again';
                    if (playStatus) playStatus.textContent = 'Click to play again';
                    if (typeof lucide !== 'undefined') lucide.createIcons();
                }, startOffset + totalDurationMs + 200);
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
                                feedbackMessage.textContent = '✓ Correct!';
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
                                feedbackMessage.textContent = '✗ Incorrect — the correct pattern is highlighted in green.';
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

            const notes       = playButton.dataset.notes ? playButton.dataset.notes.split(',') : [];
            const tempo       = parseInt(playButton.dataset.tempo) || 80;
            const beatMs      = Math.round(60000 / tempo);
            const timeSig     = playButton.dataset.timeSig || '4/4';
            const beatsPerBar = parseInt(timeSig.split('/')[0]) || 4;

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
                playButton.innerHTML = '<i data-lucide="loader-2" class="w-5 h-5 animate-spin inline"></i> Get Ready...';
                if (typeof lucide !== 'undefined') lucide.createIcons();

                await Tone.start();

                let totalDurationMs = 0;
                for (const note of notes) totalDurationMs += (noteDurations[note] || 1) * beatMs;
                const totalRhythmBeats = Math.ceil(totalDurationMs / beatMs);

                // 1-bar pre-count (always)
                for (let b = 0; b < beatsPerBar; b++) {
                    ((t, beat) => setTimeout(() => playMetronomeClick(beat === 0), t))(b * beatMs, b);
                }
                const startOffset = beatsPerBar * beatMs;

                // Metronome during rhythm
                for (let b = 0; b < totalRhythmBeats; b++) {
                    ((t, beat) => setTimeout(() => playMetronomeClick(beat % beatsPerBar === 0), t))(startOffset + b * beatMs, b);
                }

                // Record rhythm start time after count-in (taps only count from here)
                setTimeout(() => {
                    if (window._practiceGen !== myGen) return;
                    rhythmStartTime = Date.now();
                    playButton.innerHTML = '<i data-lucide="volume-2" class="w-5 h-5"></i> Playing...';
                    if (playStatus) playStatus.textContent = 'Tap or press Tab/Space along with the rhythm!';
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
                const tolerance = beatMs * 0.20; // ±20%

                let beat = 0;
                const expected = [];
                notes.forEach((note, idx) => {
                    const dur = noteDurations[note] || 1;
                    if (note === 'triplet-quarter') {
                        for (let ti = 0; ti < 3; ti++) expected.push({ idx, time: (beat + ti * (2/3)) * beatMs });
                    } else if (note === 'triplet-eighth') {
                        for (let ti = 0; ti < 3; ti++) expected.push({ idx, time: (beat + ti * (1/3)) * beatMs });
                    } else if (!note.includes('_rest')) {
                        expected.push({ idx, time: beat * beatMs });
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
