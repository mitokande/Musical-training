    <!-- Main Content -->
    <main wire:id="practice-harmonic-interval-{{ $currentPracticeIndex }}" class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <!-- Practice Card -->
        <div class="card overflow-hidden mb-6">
            <!-- Header -->
            <div class="hero-gradient p-6">
                <div class="relative flex items-center justify-center">
                    <a href="/learn" class="absolute left-0 top-1/2 -translate-y-1/2 w-12 h-12 flex items-center justify-center rounded-xl bg-white/10 hover:bg-white/20 text-white transition-all hover:scale-105 active:scale-95">
                        <i data-lucide="arrow-left" class="w-6 h-6"></i>
                    </a>

                    <div class="flex items-center gap-4 text-center">
                        <div>
                            <h1 class="text-xl font-bold text-white">Harmonic Interval Practice</h1>
                            <p class="text-white/80 text-sm">Identify the interval (notes played together)</p>
                        </div>
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
            <div class="p-8">
                <!-- Exercise title -->
                <p class="text-center text-xs font-semibold uppercase tracking-widest text-gray-400 mb-3">Harmonic Interval</p>

                <!-- VexFlow Note Display -->
                <div id="noteDisplayContainer" class="w-full bg-gray-50 border-2 border-dashed border-gray-200 rounded-xl flex items-center justify-center mb-8" style="min-height:130px;">
                    <div id="output"
                         style="width:100%; height:180px; display:flex; justify-content:center;"
                         data-note1="{{ strtolower($currentPractice->note1) . '/' . $currentPractice->octave }}"
                         data-note2="{{ strtolower($currentPractice->note2) . '/' . ($currentPractice->note2_octave ?? $currentPractice->octave) }}"
                         data-clef="{{ $clef }}">
                    </div>
                </div>

                <!-- Play Button Section -->
                <div class="card p-6 mb-8">
                    <div class="flex flex-col items-center">
                        <div class="flex">
                            <button
                            id="playButton"
                            class="btn-primary text-white font-semibold py-3 px-8 rounded-lg flex items-center gap-2 mb-3 hover:shadow-lg transition-shadow"
                            data-note="{{ strtoupper($currentPractice->note1) . $currentPractice->octave . ',' . strtoupper($currentPractice->note2) . ($currentPractice->note2_octave ?? $currentPractice->octave) }}"
                        >
                            <i data-lucide="play" class="w-5 h-5"></i>
                            Play Interval
                        </button>
                        @if ($currentPracticeIndex < (count($practices) - 1))
                            <button
                                id="nextPracticeBtn"
                                wire:click="getNextPractice"
                                class="font-semibold py-3 px-8 rounded-lg hidden flex items-center gap-2 mb-3 hover:shadow-lg transition-shadow disabled:opacity-50 disabled:cursor-not-allowed
                                    bg-blue-100 text-blue-700 border border-blue-300 hover:bg-blue-200 hover:text-blue-800 hover:border-blue-400"
                                style="border-width: 2px;"
                            >
                                <i data-lucide="arrow-right" class="w-5 h-5"></i>
                                Next
                            </button>
                        @else
                            <a
                                href="/learn"
                                id="nextPracticeBtn"
                                class="font-semibold py-3 px-8 rounded-lg hidden flex items-center gap-2 mb-3 hover:shadow-lg transition-shadow disabled:opacity-50 disabled:cursor-not-allowed
                                    bg-blue-100 text-blue-700 border border-blue-300 hover:bg-blue-200 hover:text-blue-800 hover:border-blue-400"
                                style="border-width: 2px;"
                            >
                                <i data-lucide="check" class="w-5 h-5"></i>
                                Finish
                            </a>
                        @endif
                        </div>
                        <p id="playStatus" class="text-sm text-gray-500">Listen to the interval</p>
                    </div>
                </div>

                <!-- Answer Options -->
                <div id="answerOptions" class="grid grid-cols-2 gap-3"
                     data-target="{{ $currentPractice->interval }}"
                     data-practice-id="{{ $currentPractice->id }}">
                    @php
                        $displayOptions = !empty($intervalOptions) ? $intervalOptions : (function() use ($currentPractice) {
                            $all = ['Minor 2nd','Major 2nd','Minor 3rd','Major 3rd','Perfect 4th','Perfect 5th','Minor 6th','Major 6th','Minor 7th','Major 7th','Perfect Octave'];
                            $correct = $currentPractice->interval;
                            $others = array_values(array_filter($all, fn($i) => strtolower($i) !== strtolower($correct)));
                            shuffle($others);
                            $opts = array_merge([$correct], array_slice($others, 0, 3));
                            shuffle($opts);
                            return $opts;
                        })();
                    @endphp
                    @foreach ($displayOptions as $option)
                        <button class="answer-btn card p-4 text-center font-semibold text-gray-700 hover:shadow-md transition-all text-sm"
                                data-answer="{{ strtolower($option) }}">{{ $option }}</button>
                    @endforeach
                </div>

                <!-- Feedback Message -->
                <div id="feedbackMessage" class="mt-4 p-4 rounded-lg text-center font-medium hidden"></div>
            </div>

        </div>

        <!-- XP & Score -->
        <div class="flex items-center justify-center gap-4 text-sm text-gray-500">
            <span class="flex items-center gap-1">
                <i data-lucide="sparkles" class="w-4 h-4 text-yellow-500"></i>
                +<span id="xpEarned">0</span> XP
            </span>
            <span>•</span>
            <span><span id="scoreCorrect">0</span> / <span id="scoreTotal">0</span> Correct</span>
        </div>


        <script src="https://cdn.jsdelivr.net/npm/vexflow@4.2.2/build/cjs/vexflow.js"></script>
        <script>
            // ── VexFlow helpers ──────────────────────────────────────────────────────
            function vfStemDirH(noteKey, clef) {
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
                const mid = clef === 'bass' ? 50 : 71;
                return midi >= mid ? -1 : 1;
            }

            // Draw either just bottom note (showBoth=false) or both as chord (showBoth=true)
            function drawHarmonicStave(note1Key, note2Key, showBoth, clef) {
                const { Renderer, Stave, StaveNote, Voice, Formatter, Accidental } = Vex.Flow;
                const div = document.getElementById('output');
                if (!div) return;
                div.innerHTML = '';
                const renderer = new Renderer(div, Renderer.Backends.SVG);
                renderer.resize(490, 180);
                const context = renderer.getContext();
                const stave = new Stave(10, 30, 464);
                stave.addClef(clef || 'treble');
                stave.setNoteStartX(stave.getNoteStartX() + 100);
                stave.setContext(context).draw();

                // For stem direction: use the topmost note
                const topKey = showBoth ? note2Key : note1Key;
                const sd = vfStemDirH(topKey, clef);
                const keys = showBoth ? [note1Key, note2Key] : [note1Key];
                const chord = new StaveNote({ keys, duration: 'w', stem_direction: sd });

                const voice = new Voice({ numBeats: 4, beatValue: 4 });
                voice.addTickables([chord]);
                Accidental.applyAccidentals([voice], 'C');
                new Formatter().joinVoices([voice]).format([voice], 200);
                voice.draw(context, stave);
            }

            window.initPracticeHarmonicInterval = function() {
                window._practiceGen = (window._practiceGen || 0) + 1;
                const myGen = window._practiceGen;

                const div = document.getElementById('output');
                const clef     = div ? (div.dataset.clef  || 'treble') : 'treble';
                const note1Key = div ? div.dataset.note1 : '';
                const note2Key = div ? div.dataset.note2 : '';

                if (typeof Vex !== 'undefined' && note1Key) {
                    drawHarmonicStave(note1Key, note2Key, false, clef);
                }

                const playButton    = document.getElementById('playButton');
                const playStatus    = document.getElementById('playStatus');
                const nextButton    = document.getElementById('nextPracticeBtn');
                const answerOptions = document.getElementById('answerOptions');
                const answerButtons = document.querySelectorAll('.answer-btn');
                const feedbackMessage = document.getElementById('feedbackMessage');

                if (playButton && answerOptions) {
                    const target     = answerOptions.dataset.target;
                    const practiceId = answerOptions.dataset.practiceId;
                    let isAnswered   = false;

                    if (typeof lucide !== 'undefined') lucide.createIcons();

                    playButton.onclick = async function() {
                        await Tone.start();
                        const notes = this.dataset.note.split(',');
                        playButton.disabled = true;
                        playButton.innerHTML = '<i data-lucide="volume-2" class="w-5 h-5"></i> Playing...';
                        playStatus.textContent = 'Playing harmonic interval...';
                        if (typeof lucide !== 'undefined') lucide.createIcons();
                        window.HarmonivaAudio.playSimultaneous(notes, 2);
                        setTimeout(() => {
                            if (window._practiceGen !== myGen) return;
                            playButton.disabled = false;
                            playButton.innerHTML = '<i data-lucide="play" class="w-5 h-5"></i> Play Again';
                            playStatus.textContent = 'Click to play again';
                            if (typeof lucide !== 'undefined') lucide.createIcons();
                        }, 2500);
                    };

                    answerButtons.forEach(btn => {
                        btn.onclick = async function() {
                            if (isAnswered) return;

                            const answer = this.dataset.answer;
                            const originalContent = this.innerHTML;

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
                                        practice_id: 5,
                                        question_id: parseInt(practiceId),
                                        answer: answer
                                    })
                                });

                                const data = await response.json();
                                isAnswered = true;

                                // Reveal second note after answer
                                if (typeof Vex !== 'undefined' && note1Key && note2Key) {
                                    drawHarmonicStave(note1Key, note2Key, true, clef);
                                }

                                if (playButton) playButton.classList.add('hidden');
                                if (playStatus) playStatus.classList.add('hidden');
                                if (nextButton) nextButton.classList.remove('hidden');

                                this.innerHTML = originalContent;

                                if (data.is_correct) {
                                    this.classList.add('correct');
                                    this.classList.remove('text-gray-700');
                                    this.classList.add('text-green-700');
                                    feedbackMessage.textContent = '✓ Correct! Well done!';
                                    feedbackMessage.classList.remove('hidden', 'bg-red-100', 'text-red-700');
                                    feedbackMessage.classList.add('bg-green-100', 'text-green-700');
                                } else {
                                    this.classList.add('incorrect');
                                    this.classList.remove('text-gray-700');
                                    this.classList.add('text-red-700');
                                    feedbackMessage.textContent = `✗ Incorrect. The correct answer is ${target}.`;
                                    feedbackMessage.classList.remove('hidden', 'bg-green-100', 'text-green-700');
                                    feedbackMessage.classList.add('bg-red-100', 'text-red-700');

                                    answerButtons.forEach(b => {
                                        if (b.dataset.answer.toLowerCase() === target.toLowerCase()) {
                                            b.classList.add('correct');
                                            b.classList.remove('text-gray-700');
                                            b.classList.add('text-green-700');
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
                window.initPracticeHarmonicInterval();
                Livewire.on('practice-updated', () => {
                    setTimeout(() => window.initPracticeHarmonicInterval(), 50);
                });
            });

            document.addEventListener('DOMContentLoaded', function() {
                if (typeof Livewire === 'undefined') {
                    window.initPracticeHarmonicInterval();
                }
            });
        </script>

    </main>
