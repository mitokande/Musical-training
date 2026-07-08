    <!-- Main Content -->
    <main wire:id="practice-scale-{{ $currentPracticeIndex }}" class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        @if(!$currentPractice)
            <div class="card p-12 text-center">
                <h3 class="text-lg font-semibold text-gray-900 mb-2">No exercises found</h3>
                <p class="text-gray-500 mb-4">No scale exercises match your filter settings.</p>
                <a href="/exercise-setup" class="btn-primary text-white font-semibold py-2.5 px-6 rounded-lg inline-flex items-center gap-2">
                    <i data-lucide="settings-2" class="w-4 h-4"></i> Adjust Settings
                </a>
            </div>
        @else
        <div class="card overflow-hidden mb-6">
            <div class="hero-gradient p-6">
                <div class="relative flex items-center justify-center">
                    <a href="/learn" class="absolute left-0 top-1/2 -translate-y-1/2 w-12 h-12 flex items-center justify-center rounded-xl bg-white/10 hover:bg-white/20 text-white transition-all">
                        <i data-lucide="arrow-left" class="w-6 h-6"></i>
                    </a>
                    <div class="text-center">
                        <h1 class="text-xl font-bold text-white">Scale & Mode Recognition</h1>
                        <p class="text-white/80 text-sm">Identify the scale type by ear</p>
                    </div>
                    <div class="absolute right-0 top-1/2 -translate-y-1/2">
                        <span class="inline-flex items-center gap-1.5 rounded-xl bg-white/10 px-3 py-2 text-sm font-semibold text-white/90 ring-1 ring-white/20">
                            {{ $currentPracticeIndex + 1 }} / {{ count($practices) }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="p-4 sm:p-8">
                @php
                    // note_array already accounts for direction (descending reverses); convert
                    // each note ("Eb4","G4") to a VexFlow key ("eb/4","g/4"), accepting flats.
                    $scaleKeys = collect($currentPractice->note_array ?? [])->map(function ($n) {
                        if (preg_match('/^([A-Ga-g](?:#{1,2}|b{1,2})?)(\d+)$/', $n, $m)) {
                            return strtolower($m[1]) . '/' . $m[2];
                        }
                        return strtolower($n);
                    })->implode(',');
                    $scaleRootKey = strtolower($currentPractice->root_note) . '/' . $currentPractice->octave;
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

                <!-- Scale name label – hidden until the user answers -->
                <p id="scaleLabel" class="text-center text-sm font-bold tracking-widest text-purple-700 mb-3 invisible">&nbsp;</p>

                <!-- VexFlow staff: shows only the starting note until the user answers, then
                     reveals the full scale as a note sequence (matches the AI-exercise scale view). -->
                <div id="noteDisplayContainer" class="w-full bg-gray-50 border-2 border-dashed border-gray-200 rounded-xl flex flex-col items-center justify-center mb-8" style="min-height:130px;">
                    <div id="output"
                         style="width:100%; height:180px; display:flex; justify-content:center;"
                         data-notes="{{ $scaleKeys }}"
                         data-root="{{ $scaleRootKey }}"
                         data-clef="{{ $staffClef }}">
                    </div>
                </div>

                <!-- Play Button -->
                <div class="card p-4 sm:p-6 mb-4 sm:mb-8">
                    <div class="flex flex-col items-center">
                        <div class="flex flex-wrap justify-center gap-3 mb-3">
                            <button id="playButton"
                                class="btn-primary text-white font-semibold py-3 px-5 sm:px-8 rounded-lg flex items-center gap-2 hover:shadow-lg transition-shadow"
                                data-notes="{{ implode(',', $currentPractice->note_array ?? []) }}"
                                data-tempo="{{ $scaleTempo ?? 'normal' }}">
                                <i data-lucide="play" class="w-5 h-5"></i>
                                Play Scale
                            </button>
                            @if ($currentPracticeIndex < (count($practices) - 1))
                                <button id="nextPracticeBtn" wire:click="getNextPractice"
                                    class="font-semibold py-3 px-5 sm:px-8 rounded-lg hidden items-center gap-2 bg-blue-100 text-blue-700 border-2 border-blue-300 hover:bg-blue-200">
                                    <i data-lucide="arrow-right" class="w-5 h-5"></i> Next
                                </button>
                            @else
                                <a href="/learn" id="nextPracticeBtn"
                                    class="font-semibold py-3 px-5 sm:px-8 rounded-lg hidden items-center gap-2 bg-blue-100 text-blue-700 border-2 border-blue-300 hover:bg-blue-200">
                                    <i data-lucide="check" class="w-5 h-5"></i> Finish
                                </a>
                            @endif
                        </div>
                        <p id="playStatus" class="text-sm text-gray-500">Listen to the scale</p>
                    </div>
                </div>

                <!-- Answer Options -->
                <div id="answerOptions" class="grid grid-cols-2 gap-3"
                     data-target="{{ strtolower($currentPractice->scale_type) }}"
                     data-scale-name="{{ $currentPractice->root_note }} {{ $currentPractice->scale_type }}"
                     data-practice-id="{{ $currentPractice->id }}">
                    @php
                        $options = array_merge([$currentPractice->scale_type], $currentPractice->other_options ?? []);
                        // When coming from exercise-setup, the target count equals the number of
                        // selected scale types (max 4). For DB-sourced questions scaleTypes is
                        // empty, so we default to 4.
                        $targetCount = !empty($scaleTypes) ? min(count($scaleTypes), 4) : 4;
                        if (count($options) < $targetCount) {
                            $pool = !empty($scaleTypes)
                                ? $scaleTypes
                                : ['Major','Natural Minor','Harmonic Minor','Melodic Minor','Ionian','Dorian','Phrygian','Lydian','Mixolydian','Aeolian','Locrian','Major Pentatonic','Minor Pentatonic','Blues Scale','Chromatic Scale','Whole Tone Scale'];
                            $existing = array_map('strtolower', $options);
                            $extra    = array_values(array_filter($pool, fn($s) => !in_array(strtolower($s), $existing)));
                            shuffle($extra);
                            $options  = array_merge($options, array_slice($extra, 0, $targetCount - count($options)));
                        }
                        $options = array_slice($options, 0, $targetCount);
                        shuffle($options);
                    @endphp
                    @foreach($options as $option)
                        <button class="answer-btn card p-4 text-center font-semibold text-gray-700 hover:shadow-md transition-all text-sm"
                                data-answer="{{ strtolower($option) }}">
                            {{ $option }}
                        </button>
                    @endforeach
                </div>

                <div id="feedbackMessage" class="mt-4 p-4 rounded-lg text-center font-medium hidden"></div>
            </div>
        </div>

        <div class="flex items-center justify-center gap-4 text-sm text-gray-500">
            <span class="flex items-center gap-1"><i data-lucide="sparkles" class="w-4 h-4 text-yellow-500"></i>+<span id="xpEarned">0</span> XP</span>
            <span>•</span>
            <span><span id="scoreCorrect">0</span> / <span id="scoreTotal">0</span> Correct</span>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/vexflow@4.2.2/build/cjs/vexflow.js"></script>
        <script>
            // Draw the scale on a staff. showAll=false shows only the starting note (so the
            // notation doesn't give away the answer); showAll=true reveals the full scale as a
            // quarter-note sequence. Accidentals (incl. flats) are applied so e.g. C–Eb–F–Gb…
            // spells correctly, and auto_stem flips stems for notes on/above the middle line.
            function drawScaleStave(allKeys, rootKey, showAll, clef) {
                if (typeof Vex === 'undefined') return;
                const { Renderer, Stave, StaveNote, Voice, Formatter, Accidental } = Vex.Flow;
                const div = document.getElementById('output');
                if (!div) return;
                div.innerHTML = '';
                // Size the staff to the full scale so every note stays on screen.
                const HS = window.HarmonivaStaff || { startPad: 40, span: function (n) { n = Math.max(1, n); return n * Math.max(40, Math.min(80, Math.round(160 / n))); } };
                const noteCount = (showAll && allKeys.length) ? allKeys.length : 1;
                const clefWidth = 60;
                const staveWidth = Math.max(360, clefWidth + HS.startPad + HS.span(noteCount) + 30);
                const renderer = new Renderer(div, Renderer.Backends.SVG);
                renderer.resize(staveWidth + 20, 180);
                const context = renderer.getContext();
                const stave = new Stave(10, 30, staveWidth);
                stave.addClef(clef || 'treble');
                stave.setNoteStartX(stave.getNoteStartX() + HS.startPad);
                stave.setContext(context).draw();

                let voice;
                // clef must be passed so VexFlow positions the notes on the
                // correct staff line for bass/alto (defaults to treble otherwise)
                if (showAll && allKeys.length) {
                    const notes = allKeys.map(k => new StaveNote({ keys: [k], duration: 'q', auto_stem: true, clef: clef || 'treble' }));
                    voice = new Voice({ numBeats: notes.length, beatValue: 4 }).setMode(Voice.Mode.SOFT);
                    voice.addTickables(notes);
                } else {
                    voice = new Voice({ numBeats: 4, beatValue: 4 });
                    voice.addTickables([new StaveNote({ keys: [rootKey], duration: 'w', auto_stem: true, clef: clef || 'treble' })]);
                }
                Accidental.applyAccidentals([voice], 'C');
                new Formatter().joinVoices([voice]).format([voice], HS.span(noteCount));
                voice.draw(context, stave);
            }

            window.initPracticeScale = function() {
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
                const scaleName = answerOptions.dataset.scaleName;
                const practiceId = answerOptions.dataset.practiceId;
                const notes = playButton.dataset.notes ? playButton.dataset.notes.split(',') : [];
                const tempoMs = { slow: 650, normal: 450, fast: 270 }[playButton.dataset.tempo || 'normal'] || 450;
                let isAnswered = false;

                // ── Scale staff (start-note only until answered, then full scale) ──
                const outputDiv = document.getElementById('output');
                const staffClef = outputDiv ? (outputDiv.dataset.clef || 'treble') : 'treble';
                const scaleKeys = outputDiv && outputDiv.dataset.notes ? outputDiv.dataset.notes.split(',').filter(n => n.length) : [];
                const scaleRootKey = outputDiv ? (scaleKeys[0] || outputDiv.dataset.root || 'c/4') : 'c/4';
                if (typeof Vex !== 'undefined' && outputDiv) {
                    drawScaleStave(scaleKeys, scaleRootKey, false, staffClef);
                }

                playButton.onclick = async function() {
                    await Tone.start();
                    playButton.disabled = true;
                    playButton.innerHTML = '<i data-lucide="volume-2" class="w-5 h-5 inline"></i> Playing...';
                    playStatus.textContent = 'Playing scale...';
                    if (typeof lucide !== 'undefined') lucide.createIcons();
                    window.HarmonivaAudio.playSequential(notes, tempoMs, 1);
                    setTimeout(() => {
                        if (window._practiceGen !== myGen) return;
                        playButton.disabled = false;
                        playButton.innerHTML = '<i data-lucide="play" class="w-5 h-5"></i> Play Again';
                        playStatus.textContent = 'Click to play again';
                        if (typeof lucide !== 'undefined') lucide.createIcons();
                    }, notes.length * tempoMs + 500);
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
                                body: JSON.stringify({ practice_id: null, question_id: parseInt(practiceId), answer: answer, slug: 'scale-practice' })
                            });
                            const data = await response.json();
                            isAnswered = true;
                            // Reveal the full scale on the staff after answering.
                            if (typeof Vex !== 'undefined' && outputDiv) {
                                drawScaleStave(scaleKeys, scaleRootKey, true, staffClef);
                            }
                            // Show scale name above the staff.
                            const scaleLabelEl = document.getElementById('scaleLabel');
                            if (scaleLabelEl && scaleName) {
                                scaleLabelEl.textContent = window.HarmonivaNotation.toDisplaySymbol(scaleName);
                                scaleLabelEl.classList.remove('invisible');
                            }
                            if (playButton) playButton.classList.add('hidden');
                            if (playStatus) playStatus.classList.add('hidden');
                            if (nextButton) nextButton.classList.remove('hidden');
                            if (data.is_correct) {
                                this.classList.add('correct', 'text-green-700');
                                feedbackMessage.textContent = '✓ Correct! Well done!';
                                feedbackMessage.className = 'mt-4 p-4 rounded-lg text-center font-medium bg-green-100 text-green-700';
                            } else {
                                this.classList.add('incorrect', 'text-red-700');
                                feedbackMessage.textContent = `✗ Incorrect. The correct answer is ${target}.`;
                                feedbackMessage.className = 'mt-4 p-4 rounded-lg text-center font-medium bg-red-100 text-red-700';
                                answerButtons.forEach(b => { if (b.dataset.answer === target) b.classList.add('correct', 'text-green-700'); });
                            }
                        } catch(e) {
                            answerButtons.forEach(b => b.disabled = false);
                            isAnswered = false;
                        }
                    };
                });
            };

            document.addEventListener('livewire:init', function() {
                window.initPracticeScale();
                Livewire.on('practice-updated', () => setTimeout(() => window.initPracticeScale(), 50));
            });
            document.addEventListener('DOMContentLoaded', function() {
                if (typeof Livewire === 'undefined') window.initPracticeScale();
            });
        </script>
        @endif
    </main>
