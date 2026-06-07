    <!-- Main Content -->
    <main wire:id="practice-interval-construction-{{ $currentPracticeIndex }}" class="max-w-2xl mx-auto px-3 sm:px-4 py-4 sm:py-6">
        <!-- Practice Card -->
        <div class="overflow-hidden rounded-2xl shadow-lg border border-purple-100" style="background:white;">

            <!-- ── Header ── -->
            <div style="background: linear-gradient(135deg, #4c1d95 0%, #6d28d9 60%, #7c3aed 100%);" class="px-4 sm:px-6 pt-4 sm:pt-5 pb-3">
                <div class="relative flex items-start justify-between">

                    <!-- Back button -->
                    <a href="/learn"
                       class="flex-shrink-0 w-10 h-10 flex items-center justify-center rounded-xl bg-white/10 hover:bg-white/20 text-white transition-all hover:scale-105 active:scale-95 mt-0.5">
                        <i data-lucide="arrow-left" class="w-5 h-5"></i>
                    </a>

                    <!-- Title -->
                    <div class="flex-1 text-center px-2">
                        <h1 class="text-base sm:text-lg font-bold text-white leading-tight">
                            {{ !empty($settings) ? 'AI Generated Practice' : 'Interval Construction' }}
                        </h1>
                        <p class="text-white/70 text-xs sm:text-sm mt-0.5">Interval Construction</p>
                    </div>

                    <!-- Right: counter + score stacked -->
                    <div class="flex-shrink-0 flex flex-col items-end gap-1.5">
                        <!-- Question counter -->
                        <span class="inline-flex items-center gap-1 rounded-xl bg-white/10 px-3 py-1.5 text-sm font-semibold text-white ring-1 ring-white/20 backdrop-blur">
                            <span id="currentQuestion">{{ $currentPracticeIndex + 1 }}</span>
                            <span class="text-white/60 font-medium">/</span>
                            <span id="totalQuestions">{{ count($practices) }}</span>
                        </span>
                        <!-- Score counter -->
                        <span id="scoreBox"
                              class="inline-flex items-center gap-1 rounded-xl bg-white/10 px-3 py-1.5 text-xs font-semibold text-white ring-1 ring-white/20 backdrop-blur whitespace-nowrap">
                            <i data-lucide="sparkles" class="w-3 h-3 text-yellow-300"></i>
                            +<span id="xpEarned">0</span>&nbsp;XP
                            <span class="text-white/50 mx-0.5">·</span>
                            <span id="scoreCorrect">0</span>/<span id="scoreTotal">0</span>
                        </span>
                    </div>
                </div>

                <!-- Progress bar -->
                @php $progressPct = count($practices) > 0 ? round(($currentPracticeIndex / count($practices)) * 100) : 0; @endphp
                <div class="mt-3 h-1.5 rounded-full bg-white/20 overflow-hidden">
                    <div class="h-full rounded-full bg-white/70 transition-all duration-500"
                         style="width: {{ $progressPct }}%"></div>
                </div>
            </div>
            <!-- ── /Header ── -->

            <!-- ── Content ── -->
            <div class="p-4 sm:p-6 flex flex-col gap-4">

                <!-- Notation display -->
                <div id="noteDisplayContainer"
                     class="w-full bg-gray-50 border border-gray-200 rounded-xl flex items-center justify-center overflow-hidden"
                     style="min-height:120px; max-height:160px;">
                    @php $constructNote2Oct = $currentPractice->note2_octave ?? $currentPractice->octave; @endphp
                    <div id="output"
                         style="width:100%; height:160px; display:flex; justify-content:center;"
                         data-note1="{{ strtolower($currentPractice->note1) . '/' . $currentPractice->octave }}"
                         data-note2="{{ strtolower($currentPractice->note2) . '/' . $constructNote2Oct }}">
                    </div>
                </div>

                <!-- Question prompt -->
                <div class="text-center py-1">
                    <p class="text-xs text-gray-400 uppercase tracking-wide font-semibold mb-0.5">Your task</p>
                    <p class="text-sm sm:text-base font-semibold text-gray-700">
                        Build a <span class="text-purple-700 font-bold">{{ $currentPractice->interval }}</span>
                        above <span class="text-purple-700 font-bold">{{ strtoupper($currentPractice->note1) }}</span>
                    </p>
                </div>

                <!-- Play / Next button section -->
                <div class="bg-gray-50 border border-gray-200 rounded-xl p-4 flex flex-col items-center gap-2">
                    <div class="flex items-center gap-3">
                        <button
                            id="playButton"
                            class="btn-primary text-white font-semibold py-2.5 px-6 rounded-lg flex items-center gap-2 hover:shadow-lg transition-shadow text-sm sm:text-base"
                            data-note1="{{ strtoupper($currentPractice->note1) . $currentPractice->octave }}"
                            data-note2="{{ strtoupper($currentPractice->note2) . $constructNote2Oct }}"
                        >
                            <i data-lucide="play" class="w-4 h-4"></i>
                            Play Starting Note
                        </button>
                        @if ($currentPracticeIndex < (count($practices) - 1))
                            <button
                                id="nextPracticeBtn"
                                wire:click="getNextPractice"
                                class="hidden font-semibold py-2.5 px-6 rounded-lg flex items-center gap-2 hover:shadow-lg transition-shadow text-sm sm:text-base bg-blue-100 text-blue-700 border-2 border-blue-300 hover:bg-blue-200"
                            >
                                <i data-lucide="arrow-right" class="w-4 h-4"></i>
                                Next
                            </button>
                        @else
                            <a
                                href="/learn"
                                id="nextPracticeBtn"
                                class="hidden font-semibold py-2.5 px-6 rounded-lg flex items-center gap-2 hover:shadow-lg transition-shadow text-sm sm:text-base bg-blue-100 text-blue-700 border-2 border-blue-300 hover:bg-blue-200"
                            >
                                <i data-lucide="check" class="w-4 h-4"></i>
                                Finish
                            </a>
                        @endif
                    </div>
                    <p id="playStatus" class="text-xs text-gray-400">Listen to the starting note, then select the correct note</p>
                </div>

                <!-- Answer Options -->
                <div id="answerOptions" class="grid grid-cols-2 gap-3"
                     data-target="{{ $currentPractice->note2 }}"
                     data-practice-id="{{ $currentPractice->id }}"
                     data-octave="{{ $constructNote2Oct }}">
                    @php
                        $displayNoteOptions = !empty($noteOptions) ? $noteOptions : (function() use ($currentPractice) {
                            // Expanded pool including flats and double accidentals
                            $all = [
                                'C','C#','Db','D','D#','Eb','E','E#','Fb',
                                'F','F#','Gb','G','G#','Ab','A','A#','Bb',
                                'B','B#','Cb','C##','D##','F##','G##','A##',
                                'Dbb','Ebb','Gbb','Abb','Bbb',
                            ];
                            $correct = $currentPractice->note2;
                            // Exclude enharmonic equivalents
                            $noteChromatic = function(string $n): ?int {
                                $lt = ['C'=>0,'D'=>2,'E'=>4,'F'=>5,'G'=>7,'A'=>9,'B'=>11];
                                if (!preg_match('/^([A-G])(#{1,2}|b{1,2}|x)?$/i', $n, $m)) return null;
                                $base = $lt[strtoupper($m[1])] ?? null;
                                if ($base === null) return null;
                                $acc = strtolower($m[2] ?? '');
                                $off = match($acc) { '#'=>1,'##'=>2,'x'=>2,'b'=>-1,'bb'=>-2, default=>0 };
                                return ((($base + $off) % 12) + 12) % 12;
                            };
                            $correctSt = $noteChromatic($correct);
                            $others = array_values(array_filter($all, fn($n) => $noteChromatic($n) !== $correctSt));
                            shuffle($others);
                            $opts = array_merge([$correct], array_slice($others, 0, 3));
                            shuffle($opts);
                            return $opts;
                        })();
                    @endphp
                    @foreach ($displayNoteOptions as $note)
                        <button class="answer-btn bg-white border border-gray-200 rounded-xl p-4 text-center font-bold text-gray-700 hover:shadow-md transition-all text-lg hover:border-purple-400 hover:bg-purple-50"
                                data-answer="{{ $note }}">{{ $note }}</button>
                    @endforeach
                </div>

                <!-- Feedback Message -->
                <div id="feedbackMessage" class="rounded-xl p-3 text-center font-medium text-sm hidden"></div>

            </div>
            <!-- ── /Content ── -->

        </div>

        <script src="https://cdn.jsdelivr.net/npm/vexflow@4.2.2/build/cjs/vexflow.js"></script>
        <script>
            // ── VexFlow helpers ──────────────────────────────────────────────────────
            function vfStemDirC(noteKey) {
                const m = noteKey.match(/^([a-g])(#{1,2}|b{1,2}|x)?\/(\d+)$/i);
                if (!m) return 1;
                const letterSt = {c:0,d:2,e:4,f:5,g:7,a:9,b:11};
                let st = letterSt[m[1].toLowerCase()] ?? 0;
                const acc = (m[2] || '').toLowerCase();
                if (acc === '#') st += 1;
                else if (acc === '##' || acc === 'x') st += 2;
                else if (acc === 'b') st -= 1;
                else if (acc === 'bb') st -= 2;
                const midi = (parseInt(m[3]) + 1) * 12 + st;
                return midi >= 71 ? -1 : 1; // Treble clef middle line B4=71
            }

            // Convert note name like 'Db' to VexFlow key like 'db'
            // Double sharp: C## → cx, etc.
            function noteToVfKey(noteName, octave) {
                // VexFlow accepts: c, c#, cb, c##, cbb, cx (double sharp as x)
                // Our format: C, C#, Cb, C##, Cbb
                let n = noteName.toLowerCase();
                // Replace ## with x for VexFlow double sharp
                n = n.replace('##', 'x');
                return n + '/' + octave;
            }

            function drawConstructStave(note1Key, note2Key, showBoth) {
                const { Renderer, Stave, StaveNote, Voice, Formatter, Accidental } = Vex.Flow;
                const div = document.getElementById('output');
                if (!div) return;
                div.innerHTML = '';
                const renderer = new Renderer(div, Renderer.Backends.SVG);
                renderer.resize(490, 160);
                const context = renderer.getContext();
                const stave = new Stave(10, 20, 464);
                stave.addClef('treble');
                stave.setNoteStartX(stave.getNoteStartX() + 100);
                stave.setContext(context).draw();

                if (showBoth) {
                    const sd1 = vfStemDirC(note1Key);
                    const sd2 = vfStemDirC(note2Key);
                    // Use top note for overall stem direction
                    const sdTop = vfStemDirC(note2Key);
                    const notes = [
                        new StaveNote({ keys: [note1Key], duration: 'h', stem_direction: sdTop }),
                        new StaveNote({ keys: [note2Key], duration: 'h', stem_direction: sdTop }),
                    ];
                    const voice = new Voice({ numBeats: 2, beatValue: 2 });
                    voice.addTickables(notes);
                    Accidental.applyAccidentals([voice], 'C');
                    new Formatter().joinVoices([voice]).format([voice], 200);
                    voice.draw(context, stave);
                } else {
                    const sd = vfStemDirC(note1Key);
                    const note = new StaveNote({ keys: [note1Key], duration: 'w', stem_direction: sd });
                    const voice = new Voice({ numBeats: 4, beatValue: 4 });
                    voice.addTickables([note]);
                    Accidental.applyAccidentals([voice], 'C');
                    new Formatter().joinVoices([voice]).format([voice], 120);
                    voice.draw(context, stave);
                }
            }

            let showSecondNote = false;

            window.initPracticeIntervalConstruction = function() {
                window._practiceGen = (window._practiceGen || 0) + 1;
                const myGen = window._practiceGen;
                showSecondNote = false;

                const div = document.getElementById('output');
                const rawNote1 = div ? div.dataset.note1 : '';
                const rawNote2 = div ? div.dataset.note2 : '';

                // Convert note keys for VexFlow (handle double sharps)
                function fixKey(k) {
                    // k is like 'c##/4' → 'cx/4', 'dbb/4' stays 'dbb/4'
                    return k.replace('##', 'x');
                }
                const note1Key = fixKey(rawNote1);
                const note2Key = fixKey(rawNote2);

                if (typeof Vex !== 'undefined' && note1Key) {
                    drawConstructStave(note1Key, note2Key, false);
                }

                window.showSecondNoteOnStave = function(selectedNoteKey) {
                    if (showSecondNote) return;
                    showSecondNote = true;
                    if (typeof Vex !== 'undefined') {
                        // Show the correct note2 (not the selected one if wrong)
                        drawConstructStave(note1Key, note2Key, true);
                    }
                };

                const playButton   = document.getElementById('playButton');
                const playStatus   = document.getElementById('playStatus');
                const nextButton   = document.getElementById('nextPracticeBtn');
                const answerOptions = document.getElementById('answerOptions');
                const answerButtons = document.querySelectorAll('.answer-btn');
                const feedbackMessage = document.getElementById('feedbackMessage');

                if (playButton && answerOptions) {
                    const target     = answerOptions.dataset.target;
                    const practiceId = answerOptions.dataset.practiceId;
                    const octave     = answerOptions.dataset.octave;
                    let isAnswered   = false;

                    if (typeof lucide !== 'undefined') lucide.createIcons();

                    playButton.onclick = async function() {
                        await Tone.start();
                        const note1 = this.dataset.note1;
                        playButton.disabled = true;
                        playButton.innerHTML = '<i data-lucide="volume-2" class="w-4 h-4"></i> Playing...';
                        playStatus.textContent = 'Playing starting note...';
                        if (typeof lucide !== 'undefined') lucide.createIcons();
                        window.HarmonivaAudio.playNote(note1, 2);
                        setTimeout(() => {
                            if (window._practiceGen !== myGen) return;
                            playButton.disabled = false;
                            playButton.innerHTML = '<i data-lucide="play" class="w-4 h-4"></i> Play Again';
                            playStatus.textContent = 'Now select the note to complete the interval';
                            if (typeof lucide !== 'undefined') lucide.createIcons();
                        }, 2500);
                    };

                    answerButtons.forEach(btn => {
                        btn.onclick = async function() {
                            if (isAnswered) return;
                            const answer = this.dataset.answer;
                            const originalContent = this.innerHTML;

                            window.HarmonivaAudio.playNote(answer + octave, 2);
                            window.showSecondNoteOnStave(noteToVfKey(answer, octave));
                            answerButtons.forEach(b => b.disabled = true);
                            this.innerHTML = '<i data-lucide="loader-2" class="w-5 h-5 animate-spin inline"></i>';
                            if (typeof lucide !== 'undefined') lucide.createIcons();

                            try {
                                const response = await fetch('/api/practice/check-answer', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                                    },
                                    body: JSON.stringify({
                                        practice_id: 6,
                                        question_id: parseInt(practiceId),
                                        answer: answer
                                    })
                                });

                                const data = await response.json();
                                isAnswered = true;

                                if (playButton) playButton.classList.add('hidden');
                                if (playStatus) playStatus.classList.add('hidden');
                                if (nextButton) nextButton.classList.remove('hidden');

                                this.innerHTML = originalContent;

                                if (data.is_correct) {
                                    this.classList.add('border-green-500', 'bg-green-50', 'text-green-700');
                                    feedbackMessage.textContent = '✓ Correct! Well done!';
                                    feedbackMessage.classList.remove('hidden', 'bg-red-100', 'text-red-700');
                                    feedbackMessage.classList.add('bg-green-100', 'text-green-700');
                                } else {
                                    this.classList.add('border-red-400', 'bg-red-50', 'text-red-700');
                                    feedbackMessage.textContent = `✗ Incorrect. The correct answer is ${target}.`;
                                    feedbackMessage.classList.remove('hidden', 'bg-green-100', 'text-green-700');
                                    feedbackMessage.classList.add('bg-red-100', 'text-red-700');
                                    answerButtons.forEach(b => {
                                        if (b.dataset.answer.toUpperCase() === target.toUpperCase()) {
                                            b.classList.add('border-green-500', 'bg-green-50', 'text-green-700');
                                        }
                                    });
                                }

                                if (data.xp !== undefined) {
                                    const el = document.getElementById('xpEarned');
                                    if (el) el.textContent = data.xp;
                                }
                                if (data.correctCount !== undefined) {
                                    const el = document.getElementById('scoreCorrect');
                                    if (el) el.textContent = data.correctCount;
                                }
                                if (data.totalCount !== undefined) {
                                    const el = document.getElementById('scoreTotal');
                                    if (el) el.textContent = data.totalCount;
                                }

                            } catch (error) {
                                console.error('Error checking answer:', error);
                                this.innerHTML = originalContent;
                                feedbackMessage.textContent = 'Error checking answer. Please try again.';
                                feedbackMessage.classList.remove('hidden', 'bg-green-100', 'text-green-700');
                                feedbackMessage.classList.add('bg-red-100', 'text-red-700');
                                answerButtons.forEach(b => b.disabled = false);
                                isAnswered = false;
                            }
                        };
                    });
                }
            };

            document.addEventListener('livewire:init', function() {
                window.initPracticeIntervalConstruction();
                Livewire.on('practice-updated', () => {
                    setTimeout(() => window.initPracticeIntervalConstruction(), 50);
                });
            });

            document.addEventListener('DOMContentLoaded', function() {
                if (typeof Livewire === 'undefined') {
                    window.initPracticeIntervalConstruction();
                }
            });
        </script>

    </main>
