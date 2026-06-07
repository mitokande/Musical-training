    <!-- Main Content -->
    <main wire:id="practice-interval-direction-{{ $currentPracticeIndex }}" class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-6">

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
                            <h1 class="text-xl font-bold text-white">Interval Direction Practice</h1>
                            <p class="text-white/80 text-sm">Listening Exercise</p>
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

                <!-- Play Button Section -->
                <div class="card p-8 mb-8 flex flex-col items-center">
                    @php $note2Oct = $currentPractice->note2_octave ?? $currentPractice->octave; @endphp
                    <p class="text-gray-500 text-sm mb-4">Listen to the two notes and decide the direction</p>
                    <div class="flex gap-3">
                        <button
                            id="playButton"
                            class="btn-primary text-white font-semibold py-3 px-8 rounded-lg flex items-center gap-2 mb-3 hover:shadow-lg transition-shadow"
                            data-note="{{ strtoupper($currentPractice->note1) . $currentPractice->octave . ',' . strtoupper($currentPractice->note2) . $note2Oct }}"
                        >
                            <i data-lucide="play" class="w-5 h-5"></i>
                            Play
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
                    <p id="playStatus" class="text-sm text-gray-500">Listen to the note to start</p>
                </div>

                <!-- Answer Options -->
                @php
                    $derivedDirection = app(\App\Services\MusicTheoryService::class)
                        ->getDirection($currentPractice->note1, (int)$currentPractice->octave, $currentPractice->note2, (int)$note2Oct);
                @endphp
                <div id="answerOptions" class="grid grid-cols-2 gap-4"
                     data-target="{{ $derivedDirection }}"
                     data-practice-id="{{ $currentPractice->id }}">
                    <button class="answer-btn card p-6 text-center font-semibold text-gray-700 hover:shadow-md transition-all" data-answer="ascending">
                        Ascending
                    </button>
                    <button class="answer-btn card p-6 text-center font-semibold text-gray-700 hover:shadow-md transition-all" data-answer="descending">
                        Descending
                    </button>
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

        <script>
            window.initPracticeIntervalDirection = function() {
                window._practiceGen = (window._practiceGen || 0) + 1;
                const myGen = window._practiceGen;

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
                        playStatus.textContent = 'Playing notes...';
                        if (typeof lucide !== 'undefined') lucide.createIcons();
                        window.HarmonivaAudio.playSequential(notes, 700, 1);
                        setTimeout(() => {
                            if (window._practiceGen !== myGen) return;
                            playButton.disabled = false;
                            playButton.innerHTML = '<i data-lucide="play" class="w-5 h-5"></i> Play Again';
                            playStatus.textContent = 'Click to play again';
                            if (typeof lucide !== 'undefined') lucide.createIcons();
                        }, 2000);
                    };

                    answerButtons.forEach(btn => {
                        btn.onclick = async function() {
                            if (isAnswered) return;

                            const answer = this.dataset.answer;

                            answerButtons.forEach(b => b.disabled = true);
                            this.innerHTML = '<i data-lucide="loader-2" class="w-5 h-5 animate-spin inline"></i> Checking...';
                            if (typeof lucide !== 'undefined') lucide.createIcons();

                            try {
                                const response = await fetch('/api/practice/check-answer', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                                    },
                                    body: JSON.stringify({
                                        practice_id: 2,
                                        question_id: parseInt(practiceId),
                                        answer: answer
                                    })
                                });

                                const data = await response.json();
                                isAnswered = true;

                                if (playButton) playButton.classList.add('hidden');
                                if (playStatus) playStatus.classList.add('hidden');
                                if (nextButton) nextButton.classList.remove('hidden');

                                this.textContent = answer.charAt(0).toUpperCase() + answer.slice(1);

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
                                    feedbackMessage.textContent = `✗ Incorrect. The correct answer is ${data.correctAnswer || target.charAt(0).toUpperCase() + target.slice(1)}.`;
                                    feedbackMessage.classList.remove('hidden', 'bg-green-100', 'text-green-700');
                                    feedbackMessage.classList.add('bg-red-100', 'text-red-700');

                                    answerButtons.forEach(b => {
                                        if (b.dataset.answer.toUpperCase() === target.toUpperCase()) {
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
                                this.textContent = answer;
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

            if (document.readyState !== 'loading') {
                window.initPracticeIntervalDirection();
            } else {
                document.addEventListener('DOMContentLoaded', window.initPracticeIntervalDirection);
            }

            document.addEventListener('livewire:init', function() {
                window.initPracticeIntervalDirection();
            });

            if (!window._practiceIntervalDirectionUpdatedRegistered) {
                window._practiceIntervalDirectionUpdatedRegistered = true;
                document.addEventListener('livewire:init', function() {
                    Livewire.on('practice-updated', () => {
                        setTimeout(() => window.initPracticeIntervalDirection(), 50);
                    });
                }, { once: true });
            }
        </script>

    </main>
