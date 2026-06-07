    <!-- Main Content -->
    <main wire:id="practice-melodic-dictation-{{ $currentPracticeIndex }}" class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        @if(!$currentPractice)
            <div class="card p-12 text-center">
                <h3 class="text-lg font-semibold text-gray-900 mb-2">No exercises found</h3>
                <p class="text-gray-500 mb-4">No melodic dictation exercises match your settings.</p>
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
                        <h1 class="text-xl font-bold text-white">Melodic Dictation</h1>
                        <p class="text-white/80 text-sm">Listen and write the melody</p>
                    </div>
                    <div class="absolute right-0 top-1/2 -translate-y-1/2">
                        <span class="inline-flex items-center gap-1.5 rounded-xl bg-white/10 px-3 py-2 text-sm font-semibold text-white/90 ring-1 ring-white/20">
                            {{ $currentPracticeIndex + 1 }} / {{ count($practices) }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="p-8">
                <!-- Info banner -->
                <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 mb-6 flex items-center gap-3">
                    <i data-lucide="info" class="w-5 h-5 text-blue-500 flex-shrink-0"></i>
                    <div class="text-sm text-blue-700">
                        <strong>Key:</strong> {{ $currentPractice->key_signature }} •
                        <strong>Clef:</strong> {{ ucfirst($currentPractice->clef) }} •
                        <strong>Bars:</strong> {{ $currentPractice->bars }} •
                        <strong>Tempo:</strong> {{ $currentPractice->tempo }} BPM
                    </div>
                </div>

                <!-- Play Button -->
                <div class="card p-6 mb-8">
                    <div class="flex flex-col items-center">
                        <div class="flex gap-3 mb-3">
                            <button id="playButton"
                                class="btn-primary text-white font-semibold py-3 px-8 rounded-lg flex items-center gap-2 hover:shadow-lg transition-shadow"
                                data-notes="{{ implode(',', $currentPractice->notes ?? []) }}"
                                data-tempo="{{ $currentPractice->tempo }}"
                                data-listen-limit="{{ $dictationListenCount ?? 'unlimited' }}">
                                <i data-lucide="play" class="w-5 h-5"></i>
                                Play Melody
                            </button>
                        </div>
                        <p id="playStatus" class="text-sm text-gray-500">
                            @if(($dictationListenCount ?? 'unlimited') !== 'unlimited')
                                You can listen {{ $dictationListenCount }} time(s)
                            @else
                                Listen carefully – replay as many times as you like
                            @endif
                        </p>
                        <p id="listenCounter" class="text-xs text-purple-600 font-semibold mt-1 hidden"></p>
                    </div>
                </div>

                <!-- Answer Section -->
                <div class="card p-6 mb-6"
                     data-target="{{ implode(',', $currentPractice->notes ?? []) }}"
                     data-practice-id="{{ $currentPractice->id }}"
                     data-answer-mode="{{ $dictationAnswerMode ?? 'keyboard' }}"
                     id="answerSection">
                    <h3 class="font-semibold text-gray-900 mb-3 flex items-center gap-2">
                        <i data-lucide="pencil" class="w-4 h-4 text-purple-600"></i>
                        Write the melody you heard
                    </h3>

                    {{-- Keyboard mode: text input --}}
                    @if(($dictationAnswerMode ?? 'keyboard') === 'keyboard')
                    <p class="text-sm text-gray-500 mb-3">Enter notes separated by commas (e.g. C4, E4, G4, C5)</p>
                    <input
                        id="dictationAnswer"
                        type="text"
                        placeholder="C4, D4, E4, F4, G4..."
                        class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent font-mono text-gray-800 mb-4"
                    >
                    @else
                    {{-- Choices mode: note button piano --}}
                    <p class="text-sm text-gray-500 mb-3">Select notes in order</p>
                    <div id="noteSequenceDisplay" class="min-h-12 px-4 py-3 rounded-xl border-2 border-purple-200 bg-purple-50 font-mono text-gray-800 mb-3 text-sm flex flex-wrap gap-1 items-center">
                        <span class="text-gray-400 text-xs" id="sequencePlaceholder">No notes selected yet...</span>
                    </div>
                    <div class="grid grid-cols-7 gap-1 mb-2">
                        @foreach(['C','D','E','F','G','A','B'] as $n)
                            <button class="note-pick-btn py-2 text-sm font-bold rounded-lg border border-gray-200 bg-white hover:bg-purple-100 hover:border-purple-400 transition-all" data-note="{{ $n }}">{{ $n }}</button>
                        @endforeach
                    </div>
                    <div class="grid grid-cols-5 gap-1 mb-3">
                        @foreach(['C#','D#','F#','G#','A#'] as $n)
                            <button class="note-pick-btn py-1.5 text-xs font-bold rounded-lg border border-gray-300 bg-gray-100 hover:bg-purple-100 hover:border-purple-400 transition-all" data-note="{{ $n }}">{{ $n }}</button>
                        @endforeach
                    </div>
                    <div class="flex gap-2 mb-2">
                        <button id="octaveDown" class="px-3 py-1 text-xs font-semibold rounded-lg border border-gray-200 hover:bg-gray-100">Oct -</button>
                        <span class="px-3 py-1 text-xs font-semibold rounded-lg bg-gray-100 border border-gray-200" id="octaveDisplay">4</span>
                        <button id="octaveUp" class="px-3 py-1 text-xs font-semibold rounded-lg border border-gray-200 hover:bg-gray-100">Oct +</button>
                        <button id="undoNote" class="px-3 py-1 text-xs font-semibold rounded-lg border border-red-200 text-red-600 hover:bg-red-50">↩ Undo</button>
                        <button id="clearNotes" class="px-3 py-1 text-xs font-semibold rounded-lg border border-gray-200 hover:bg-gray-100">Clear</button>
                    </div>
                    <input type="hidden" id="dictationAnswer" value="">
                    @endif

                    <div class="flex gap-3">
                        <button id="submitAnswer"
                            class="btn-primary text-white font-semibold py-2.5 px-6 rounded-lg flex items-center gap-2">
                            <i data-lucide="check" class="w-4 h-4"></i>
                            Submit Answer
                        </button>
                        @if ($currentPracticeIndex < (count($practices) - 1))
                            <button id="nextPracticeBtn" wire:click="getNextPractice"
                                class="font-semibold py-2.5 px-6 rounded-lg hidden items-center gap-2 bg-blue-100 text-blue-700 border-2 border-blue-300 hover:bg-blue-200">
                                <i data-lucide="arrow-right" class="w-4 h-4"></i> Next
                            </button>
                        @else
                            <a href="/learn" id="nextPracticeBtn"
                                class="font-semibold py-2.5 px-6 rounded-lg hidden items-center gap-2 bg-blue-100 text-blue-700 border-2 border-blue-300 hover:bg-blue-200">
                                <i data-lucide="check" class="w-4 h-4"></i> Finish
                            </a>
                        @endif
                    </div>
                </div>

                <div id="feedbackMessage" class="mt-4 p-4 rounded-lg text-center font-medium hidden"></div>
            </div>
        </div>

        <div class="flex items-center justify-center gap-4 text-sm text-gray-500">
            <span class="flex items-center gap-1"><i data-lucide="sparkles" class="w-4 h-4 text-yellow-500"></i>+<span id="xpEarned">0</span> XP</span>
            <span>•</span>
            <span><span id="scoreCorrect">0</span> / <span id="scoreTotal">0</span> Correct</span>
        </div>

        <script>
            window.initPracticeMelodicDictation = function() {
                window._practiceGen = (window._practiceGen || 0) + 1;
                const myGen = window._practiceGen;
                const playButton = document.getElementById('playButton');
                const playStatus = document.getElementById('playStatus');
                const listenCounter = document.getElementById('listenCounter');
                const submitAnswer = document.getElementById('submitAnswer');
                const nextButton = document.getElementById('nextPracticeBtn');
                const answerSection = document.getElementById('answerSection');
                const dictationInput = document.getElementById('dictationAnswer');
                const feedbackMessage = document.getElementById('feedbackMessage');
                if (typeof lucide !== 'undefined') lucide.createIcons();
                if (!playButton || !answerSection) return;

                const target = answerSection.dataset.target;
                const practiceId = answerSection.dataset.practiceId;
                const answerMode = answerSection.dataset.answerMode || 'keyboard';
                const notes = playButton.dataset.notes ? playButton.dataset.notes.split(',') : [];
                const tempo = parseInt(playButton.dataset.tempo) || 60;
                const noteGapMs = Math.round(60000 / tempo);
                const listenLimitRaw = playButton.dataset.listenLimit || 'unlimited';
                const listenLimit = listenLimitRaw === 'unlimited' ? Infinity : parseInt(listenLimitRaw);
                let listenCount = 0;
                let isAnswered = false;

                // Note picker state
                let pickedNotes = [];
                let currentOctave = 4;

                playButton.onclick = async function() {
                    if (listenCount >= listenLimit) return;
                    listenCount++;
                    await Tone.start();
                    playButton.disabled = true;
                    playButton.innerHTML = '<i data-lucide="volume-2" class="w-5 h-5 inline"></i> Playing...';
                    if (typeof lucide !== 'undefined') lucide.createIcons();
                    const cleanNotes = notes.map(n => n.trim());
                    window.HarmonivaAudio.playSequential(cleanNotes, noteGapMs, 1);
                    setTimeout(() => {
                        if (window._practiceGen !== myGen) return;
                        const remaining = listenLimit === Infinity ? null : listenLimit - listenCount;
                        playButton.disabled = (remaining !== null && remaining <= 0);
                        playButton.innerHTML = '<i data-lucide="play" class="w-5 h-5"></i> Play Again';
                        if (remaining !== null) {
                            listenCounter.textContent = `${remaining} listen(s) remaining`;
                            listenCounter.classList.remove('hidden');
                            if (remaining <= 0) {
                                playButton.disabled = true;
                                playButton.classList.add('opacity-50', 'cursor-not-allowed');
                            }
                        }
                        if (typeof lucide !== 'undefined') lucide.createIcons();
                    }, notes.length * noteGapMs + 500);
                };

                // Note picker mode
                if (answerMode === 'choices') {
                    const seqDisplay = document.getElementById('noteSequenceDisplay');
                    const placeholder = document.getElementById('sequencePlaceholder');
                    const octaveDisplay = document.getElementById('octaveDisplay');

                    function updateSeqDisplay() {
                        if (dictationInput) dictationInput.value = pickedNotes.join(',');
                        if (seqDisplay) {
                            if (pickedNotes.length === 0) {
                                seqDisplay.innerHTML = '<span class="text-gray-400 text-xs" id="sequencePlaceholder">No notes selected yet...</span>';
                            } else {
                                seqDisplay.innerHTML = pickedNotes.map((n, i) =>
                                    `<span class="px-2 py-0.5 bg-purple-600 text-white rounded text-xs font-bold cursor-pointer hover:bg-red-500" data-idx="${i}">${n}</span>`
                                ).join('');
                                seqDisplay.querySelectorAll('[data-idx]').forEach(el => {
                                    el.onclick = () => { pickedNotes.splice(parseInt(el.dataset.idx), 1); updateSeqDisplay(); };
                                });
                            }
                        }
                    }

                    document.querySelectorAll('.note-pick-btn').forEach(btn => {
                        btn.onclick = () => {
                            const note = btn.dataset.note + currentOctave;
                            pickedNotes.push(note);
                            window.HarmonivaAudio.playNote(note, 0.4);
                            updateSeqDisplay();
                        };
                    });

                    document.getElementById('octaveUp')?.addEventListener('click', () => {
                        if (currentOctave < 6) { currentOctave++; if (octaveDisplay) octaveDisplay.textContent = currentOctave; }
                    });
                    document.getElementById('octaveDown')?.addEventListener('click', () => {
                        if (currentOctave > 2) { currentOctave--; if (octaveDisplay) octaveDisplay.textContent = currentOctave; }
                    });
                    document.getElementById('undoNote')?.addEventListener('click', () => { pickedNotes.pop(); updateSeqDisplay(); });
                    document.getElementById('clearNotes')?.addEventListener('click', () => { pickedNotes = []; updateSeqDisplay(); });
                }

                if (submitAnswer) {
                    submitAnswer.onclick = async function() {
                        if (isAnswered || !dictationInput) return;
                        const answer = dictationInput.value.trim().toUpperCase().replace(/\s+/g, '');
                        if (!answer) return;

                        submitAnswer.disabled = true;
                        try {
                            const response = await fetch('/api/practice/check-answer', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') },
                                body: JSON.stringify({ practice_id: null, question_id: parseInt(practiceId), answer: answer, slug: 'melodic-dictation' })
                            });
                            const data = await response.json();
                            isAnswered = true;
                            submitAnswer.classList.add('hidden');
                            if (nextButton) nextButton.classList.remove('hidden');
                            if (data.is_correct) {
                                dictationInput.classList.add('border-green-400', 'bg-green-50');
                                feedbackMessage.textContent = '✓ Correct! Perfect melody!';
                                feedbackMessage.className = 'mt-4 p-4 rounded-lg text-center font-medium bg-green-100 text-green-700';
                            } else {
                                dictationInput.classList.add('border-red-400', 'bg-red-50');
                                feedbackMessage.textContent = `✗ Wrong. Correct answer: ${target}`;
                                feedbackMessage.className = 'mt-4 p-4 rounded-lg text-center font-medium bg-red-100 text-red-700';
                            }
                        } catch(e) {
                            submitAnswer.disabled = false;
                            isAnswered = false;
                        }
                    };
                }
            };

            document.addEventListener('livewire:init', function() {
                window.initPracticeMelodicDictation();
                Livewire.on('practice-updated', () => setTimeout(() => window.initPracticeMelodicDictation(), 50));
            });
            document.addEventListener('DOMContentLoaded', function() {
                if (typeof Livewire === 'undefined') window.initPracticeMelodicDictation();
            });
        </script>
        @endif
    </main>
