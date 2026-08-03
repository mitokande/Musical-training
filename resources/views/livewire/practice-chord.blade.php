    <!-- Main Content -->
    <main wire:id="practice-chord-{{ $currentPracticeIndex }}" class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        @if(!$currentPractice)
            <div class="card p-12 text-center">
                <div class="w-16 h-16 rounded-full bg-purple-100 flex items-center justify-center mx-auto mb-4">
                    <i data-lucide="music" class="w-8 h-8 text-purple-600"></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">{{ __('app.practice_ui.common.no_exercises') }}</h3>
                <p class="text-gray-500 mb-4">{{ __('app.practice_ui.chord.empty') }}</p>
                <a href="/exercise-setup" class="btn-primary text-white font-semibold py-2.5 px-6 rounded-lg inline-flex items-center gap-2">
                    <i data-lucide="settings-2" class="w-4 h-4"></i> {{ __('app.practice_ui.common.adjust_settings') }}
                </a>
            </div>
        @else
        <!-- Practice Card -->
        <div class="card overflow-hidden mb-6">
            <!-- Header -->
            <div class="hero-gradient p-6">
                <div class="relative flex items-center justify-center">
                    <a href="/learn" class="absolute left-0 top-1/2 -translate-y-1/2 w-12 h-12 flex items-center justify-center rounded-xl bg-white/10 hover:bg-white/20 text-white transition-all hover:scale-105 active:scale-95">
                        <i data-lucide="arrow-left" class="w-6 h-6"></i>
                    </a>
                    <div class="text-center">
                        <h1 class="text-xl font-bold text-white">{{ __('app.practice_ui.chord.title') }}</h1>
                        <p class="text-white/80 text-sm">{{ __('app.practice_ui.chord.subtitle') }}</p>
                    </div>
                    <div class="absolute right-0 top-1/2 -translate-y-1/2">
                        <span class="inline-flex items-center gap-1.5 rounded-xl bg-white/10 px-3 py-2 text-sm font-semibold text-white/90 ring-1 ring-white/20 backdrop-blur">
                            <span id="currentQuestion">{{ $currentPracticeIndex + 1 }}</span>
                            <span class="text-white font-medium">/</span>
                            <span id="totalQuestions">{{ count($practices) }}</span>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Content -->
            <div class="p-4 sm:p-8">
                @php
                    // Convert note_array ("Eb4","G4","Bb4") to VexFlow keys ("eb/4","g/4","bb/4"),
                    // accepting single/double flats and sharps so flat chords spell correctly.
                    $chordKeys = collect($currentPractice->note_array ?? [])->map(function ($n) {
                        if (preg_match('/^([A-Ga-g](?:#{1,2}|b{1,2})?)(\d+)$/', $n, $m)) {
                            return strtolower($m[1]) . '/' . $m[2];
                        }
                        return strtolower($n);
                    })->implode(',');
                    $chordRootKey = strtolower($currentPractice->root_note) . '/' . $currentPractice->octave;
                    // Bass clef when the root sits below G3, otherwise treble.
                    $clefFor = function ($note, $octave) {
                        $base = ['C' => 0, 'D' => 2, 'E' => 4, 'F' => 5, 'G' => 7, 'A' => 9, 'B' => 11];
                        $letter = strtoupper(substr((string) $note, 0, 1));
                        $rest = substr((string) $note, 1);
                        $acc = str_contains($rest, '#') ? 1 : (str_contains($rest, 'b') ? -1 : 0);
                        $pitch = ((int) $octave) * 12 + (($base[$letter] ?? 0) + $acc);
                        return $pitch < (3 * 12 + 7) ? 'bass' : 'treble';
                    };
                    // Exercise-setup questions carry the user-selected clef; fall back to auto.
                    $staffClef = $currentPractice->clef ?? $clefFor($currentPractice->root_note, $currentPractice->octave);
                @endphp

                <!-- Chord name label – hidden until the user answers -->
                <p id="chordLabel" class="text-center text-sm font-bold tracking-widest text-purple-700 mb-3 invisible">&nbsp;</p>

                <!-- VexFlow staff: shows only the root note until the user answers, then
                     reveals the full stacked chord (matches the AI-exercise chord view). -->
                <div id="noteDisplayContainer" class="w-full bg-gray-50 border-2 border-dashed border-gray-200 rounded-xl flex items-center justify-center mb-8" style="min-height:130px;">
                    <div id="output"
                         style="width:100%; height:180px; display:flex; justify-content:center;"
                         data-notes="{{ $chordKeys }}"
                         data-root="{{ $chordRootKey }}"
                         data-clef="{{ $staffClef }}">
                    </div>
                </div>

                <!-- Play Button -->
                <div class="card p-4 sm:p-6 mb-4 sm:mb-8">
                    <div class="flex flex-col items-center">
                        <div class="flex flex-wrap justify-center gap-3 mb-3">
                            <button
                                id="playButton"
                                class="btn-primary text-white font-semibold py-3 px-5 sm:px-8 rounded-lg flex items-center gap-2 hover:shadow-lg transition-shadow"
                                data-notes="{{ implode(',', $currentPractice->note_array ?? []) }}"
                                data-voicing="{{ $currentPractice->voicing }}"
                            >
                                <i data-lucide="play" class="w-5 h-5"></i>
                                {{ __('app.practice_ui.chord.play') }}
                            </button>
                            @if ($currentPracticeIndex < (count($practices) - 1))
                                <button id="nextPracticeBtn" wire:click="getNextPractice"
                                    class="font-semibold py-3 px-5 sm:px-8 rounded-lg hidden items-center gap-2 hover:shadow-lg transition-shadow bg-blue-100 text-blue-700 border-2 border-blue-300 hover:bg-blue-200">
                                    <i data-lucide="arrow-right" class="w-5 h-5"></i> {{ __('app.practice_ui.common.next') }}
                                </button>
                            @else
                                <a href="/learn" id="nextPracticeBtn"
                                    class="font-semibold py-3 px-5 sm:px-8 rounded-lg hidden items-center gap-2 hover:shadow-lg transition-shadow bg-blue-100 text-blue-700 border-2 border-blue-300 hover:bg-blue-200">
                                    <i data-lucide="check" class="w-5 h-5"></i> {{ __('app.practice_ui.common.finish') }}
                                </a>
                            @endif
                        </div>
                        <p id="playStatus" class="text-sm text-gray-500">{{ __('app.practice_ui.chord.listen') }}</p>
                    </div>
                </div>

                <!-- Answer Options -->
                @php
                    $allChordTypes = [
                        'Major','Minor','Diminished','Augmented','Sus2','Sus4',
                        'Major 7th','Dominant 7th','Minor 7th','Minor Major 7th',
                        'Half-Diminished 7th','Diminished 7th','Augmented 7th',
                        'Major 6th','Minor 6th','Add9','Minor Add9',
                    ];
                    // Generated questions (Learning Path + exercise-setup) carry their own
                    // pedagogically chosen distractors in other_options; use them instead of
                    // drawing from the full vocabulary. When coming from exercise-setup the
                    // target count equals the number of selected chord types (max 4);
                    // otherwise default to 4 buttons.
                    $options = array_merge([$currentPractice->chord_type], $currentPractice->other_options ?? []);
                    $targetCount = !empty($chordTypes) && count($chordTypes) >= 2 ? min(count($chordTypes), 4) : 4;
                    if (count($options) < $targetCount) {
                        $pool = array_unique(array_merge(!empty($chordTypes) ? $chordTypes : [], $allChordTypes));
                        $existing = array_map('strtolower', $options);
                        $extra    = array_values(array_filter($pool, fn($c) => !in_array(strtolower($c), $existing)));
                        shuffle($extra);
                        $options  = array_merge($options, array_slice($extra, 0, $targetCount - count($options)));
                    }
                    $options = array_slice($options, 0, $targetCount);
                    shuffle($options);
                    $gridCols = count($options) === 3 ? 'grid-cols-3' : 'grid-cols-2';
                @endphp
                <div id="answerOptions" class="grid {{ $gridCols }} gap-3"
                     data-target="{{ strtolower($currentPractice->chord_type) }}"
                     data-practice-id="{{ $currentPractice->id }}"
                     data-chord-root="{{ $currentPractice->root_note }}"
                     data-chord-type="{{ $currentPractice->chord_type }}"
                     data-inversion="{{ $currentPractice->inversion ?? 0 }}">
                    @foreach($options as $option)
                        <button class="answer-btn card p-4 text-center font-semibold text-gray-700 hover:shadow-md transition-all text-sm"
                                data-answer="{{ strtolower($option) }}">
                            {{ $option }}
                        </button>
                    @endforeach
                </div>

                <!-- Feedback -->
                <div id="feedbackMessage" class="mt-4 p-4 rounded-lg text-center font-medium hidden"></div>
            </div>
        </div>

        <!-- Score -->
        <div class="flex items-center justify-center gap-4 text-sm text-gray-500">
            <span class="flex items-center gap-1">
                <i data-lucide="sparkles" class="w-4 h-4 text-yellow-500"></i>
                +<span id="xpEarned">0</span> XP
            </span>
            <span>•</span>
            <span><span id="scoreCorrect">0</span> / <span id="scoreTotal">0</span> {{ __('app.practice_ui.common.correct') }}</span>
        </div>

        @include('livewire.partials.practice-i18n')
    <script src="https://cdn.jsdelivr.net/npm/vexflow@4.2.2/build/cjs/vexflow.js"></script>
        <script>
            // Draw the chord on a staff. showAll=false shows only the root note (so the
            // notation doesn't give away the answer); showAll=true reveals the full stacked
            // chord. Accidentals (incl. flats) are applied so e.g. Eb–G–Bb spells correctly,
            // and auto_stem flips the stem down for notes on/above the middle line.
            function drawChordStave(allKeys, rootKey, showAll, clef) {
                if (typeof Vex === 'undefined') return;
                const { Renderer, Stave, StaveNote, Voice, Formatter, Accidental } = Vex.Flow;
                const div = document.getElementById('output');
                if (!div) return;
                div.innerHTML = '';
                const renderer = new Renderer(div, Renderer.Backends.SVG);
                renderer.resize(490, 180);
                const context = renderer.getContext();
                const HS = window.HarmonivaStaff || { startPad: 40, span: function (n) { n = Math.max(1, n); return n * Math.max(40, Math.min(80, Math.round(160 / n))); } };
                const stave = new Stave(10, 30, 464);
                stave.addClef(clef || 'treble');
                stave.setNoteStartX(stave.getNoteStartX() + HS.startPad);
                stave.setContext(context).draw();

                const keys = (showAll && allKeys.length) ? allKeys : [rootKey];
                // clef must be passed so VexFlow positions the notes on the
                // correct staff line for bass/alto (defaults to treble otherwise)
                const chord = new StaveNote({ keys, duration: 'w', auto_stem: true, clef: clef || 'treble' });
                const voice = new Voice({ numBeats: 4, beatValue: 4 });
                voice.addTickables([chord]);
                Accidental.applyAccidentals([voice], 'C');
                new Formatter().joinVoices([voice]).format([voice], HS.span(1));
                voice.draw(context, stave);
            }

            window.initPracticeChord = function() {
                window._practiceGen = (window._practiceGen || 0) + 1;
                const myGen = window._practiceGen;
                const playButton = document.getElementById('playButton');
                const playStatus = document.getElementById('playStatus');
                const nextButton = document.getElementById('nextPracticeBtn');
                const answerOptions = document.getElementById('answerOptions');
                const answerButtons = document.querySelectorAll('.answer-btn');
                const feedbackMessage = document.getElementById('feedbackMessage');
                if (typeof lucide !== 'undefined') lucide.createIcons();
                if (!playButton || !answerOptions) return;

                const target = answerOptions.dataset.target;
                const practiceId = answerOptions.dataset.practiceId;
                const chordRoot = answerOptions.dataset.chordRoot || '';
                const chordType = answerOptions.dataset.chordType || '';
                const inversionNum = parseInt(answerOptions.dataset.inversion || '0');
                const chordAbbr = {
                    // Triads & Sus
                    'major': 'Maj.', 'minor': 'min.', 'diminished': 'dim.',
                    'augmented': 'aug.', 'sus2': 'sus2', 'sus4': 'sus4',
                    // 7th Chords
                    'major 7th': 'Maj7', 'dominant 7th': 'Dom7', 'minor 7th': 'min7',
                    'minor major 7th': 'mMaj7', 'half-diminished 7th': 'hdim7',
                    'half diminished': 'hdim7',
                    'diminished 7th': 'dim7', 'augmented 7th': 'aug7',
                    // Color Chords
                    'major 6th': 'Maj6', 'minor 6th': 'min6',
                    'add9': 'add9', 'minor add9': 'madd9',
                };
                const invLabels = [' {{ __('app.exercises.chord_inversion_root') }}', ' {{ __('app.exercises.chord_inversion_1st') }}', ' {{ __('app.exercises.chord_inversion_2nd') }}'];
                const notes = playButton.dataset.notes ? playButton.dataset.notes.split(',') : [];
                const voicing = playButton.dataset.voicing;
                let isAnswered = false;

                // ── Chord staff (root-only until answered, then full chord) ──
                const outputDiv = document.getElementById('output');
                const staffClef = outputDiv ? (outputDiv.dataset.clef || 'treble') : 'treble';
                const chordKeys = outputDiv && outputDiv.dataset.notes ? outputDiv.dataset.notes.split(',').filter(n => n.length) : [];
                const chordRootKey = outputDiv ? (outputDiv.dataset.root || chordKeys[0] || 'c/4') : 'c/4';
                if (typeof Vex !== 'undefined' && outputDiv) {
                    drawChordStave(chordKeys, chordRootKey, false, staffClef);
                }

                playButton.onclick = async function() {
                    await Tone.start();
                    playButton.disabled = true;
                    playButton.innerHTML = '<i data-lucide="volume-2" class="w-5 h-5 inline"></i> ' + pt('playing');
                    playStatus.textContent = pt('playing_chord');
                    if (typeof lucide !== 'undefined') lucide.createIcons();

                    if (voicing === 'arpeggiated') {
                        window.HarmonivaAudio.playArpeggio(notes, 400, 1.5);
                    } else {
                        window.HarmonivaAudio.playSimultaneous(notes, 2);
                    }
                    const totalMs = voicing === 'arpeggiated' ? window.HarmonivaAudio.totalMs(notes, 400) : 2500;
                    setTimeout(() => {
                        if (window._practiceGen !== myGen) return;
                        playButton.disabled = false;
                        playButton.innerHTML = '<i data-lucide="play" class="w-5 h-5"></i> ' + pt('play_again');
                        playStatus.textContent = pt('click_replay');
                        if (typeof lucide !== 'undefined') lucide.createIcons();
                    }, totalMs);
                };

                answerButtons.forEach(btn => {
                    btn.onclick = async function() {
                        if (isAnswered) return;
                        const answer = this.dataset.answer;
                        answerButtons.forEach(b => b.disabled = true);
                        try {
                            const response = await fetch('/api/practice/check-answer', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') },
                                body: JSON.stringify({ practice_id: null, question_id: parseInt(practiceId), answer: answer, slug: 'chord-practice' })
                            });
                            const data = await response.json();
                            isAnswered = true;
                            // Reveal the full stacked chord on the staff after answering.
                            if (typeof Vex !== 'undefined' && outputDiv) {
                                drawChordStave(chordKeys, chordRootKey, true, staffClef);
                            }
                            // Show chord name with inversion label above the staff.
                            const chordLabelEl = document.getElementById('chordLabel');
                            if (chordLabelEl) {
                                const abbr = chordAbbr[chordType.toLowerCase()] || chordType;
                                chordLabelEl.textContent = window.HarmonivaNotation.toDisplaySymbol(chordRoot) + abbr + (invLabels[inversionNum] || '');
                                chordLabelEl.classList.remove('invisible');
                            }
                            if (playButton) playButton.classList.add('hidden');
                            if (playStatus) playStatus.classList.add('hidden');
                            if (nextButton) nextButton.classList.remove('hidden');
                            if (data.is_correct) {
                                this.classList.add('correct', 'text-green-700');
                                feedbackMessage.textContent = pt('correct_well_done');
                                feedbackMessage.className = 'mt-4 p-4 rounded-lg text-center font-medium bg-green-100 text-green-700';
                            } else {
                                this.classList.add('incorrect', 'text-red-700');
                                feedbackMessage.textContent = pt('incorrect_answer_is', {answer: target});
                                feedbackMessage.className = 'mt-4 p-4 rounded-lg text-center font-medium bg-red-100 text-red-700';
                                answerButtons.forEach(b => { if (b.dataset.answer === target) b.classList.add('correct', 'text-green-700'); });
                            }
                            if (data.xp !== undefined) { const el = document.getElementById('xpEarned'); if (el) el.textContent = data.xp; }
                            if (data.correctCount !== undefined) { const el = document.getElementById('scoreCorrect'); if (el) el.textContent = data.correctCount; }
                            if (data.totalCount !== undefined) { const el = document.getElementById('scoreTotal'); if (el) el.textContent = data.totalCount; }
                        } catch(e) {
                            answerButtons.forEach(b => b.disabled = false);
                            isAnswered = false;
                        }
                    };
                });
            };

            document.addEventListener('livewire:init', function() {
                window.initPracticeChord();
                Livewire.on('practice-updated', () => setTimeout(() => window.initPracticeChord(), 50));
            });
            document.addEventListener('DOMContentLoaded', function() {
                if (typeof Livewire === 'undefined') window.initPracticeChord();
            });
        </script>
        @endif
    </main>
