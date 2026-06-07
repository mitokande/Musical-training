    <!-- Main Content -->
    <main wire:id="practice-chord-{{ $currentPracticeIndex }}" class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        @if(!$currentPractice)
            <div class="card p-12 text-center">
                <div class="w-16 h-16 rounded-full bg-purple-100 flex items-center justify-center mx-auto mb-4">
                    <i data-lucide="music" class="w-8 h-8 text-purple-600"></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">No exercises found</h3>
                <p class="text-gray-500 mb-4">No chord exercises match your filter settings.</p>
                <a href="/exercise-setup" class="btn-primary text-white font-semibold py-2.5 px-6 rounded-lg inline-flex items-center gap-2">
                    <i data-lucide="settings-2" class="w-4 h-4"></i> Adjust Settings
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
                        <h1 class="text-xl font-bold text-white">Chord Recognition</h1>
                        <p class="text-white/80 text-sm">Identify the chord type by ear</p>
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
                <!-- Root note display -->
                <div class="w-full bg-gray-50 border-2 border-dashed border-gray-200 rounded-xl flex items-center justify-center mb-8 py-6">
                    <div class="text-center">
                        <p class="text-sm text-gray-500 mb-1">Root Note</p>
                        <div class="text-5xl font-bold text-gray-800">{{ $currentPractice->root_note }}</div>
                        <p class="text-sm text-gray-400 mt-1">Octave {{ $currentPractice->octave }} • {{ ucfirst($currentPractice->voicing) }}</p>
                    </div>
                </div>

                <!-- Play Button -->
                <div class="card p-6 mb-8">
                    <div class="flex flex-col items-center">
                        <div class="flex gap-3">
                            <button
                                id="playButton"
                                class="btn-primary text-white font-semibold py-3 px-8 rounded-lg flex items-center gap-2 mb-3 hover:shadow-lg transition-shadow"
                                data-notes="{{ implode(',', $currentPractice->note_array ?? []) }}"
                                data-voicing="{{ $currentPractice->voicing }}"
                            >
                                <i data-lucide="play" class="w-5 h-5"></i>
                                Play Chord
                            </button>
                            @if ($currentPracticeIndex < (count($practices) - 1))
                                <button id="nextPracticeBtn" wire:click="getNextPractice"
                                    class="font-semibold py-3 px-8 rounded-lg hidden items-center gap-2 mb-3 hover:shadow-lg transition-shadow bg-blue-100 text-blue-700 border-2 border-blue-300 hover:bg-blue-200">
                                    <i data-lucide="arrow-right" class="w-5 h-5"></i> Next
                                </button>
                            @else
                                <a href="/learn" id="nextPracticeBtn"
                                    class="font-semibold py-3 px-8 rounded-lg hidden items-center gap-2 mb-3 hover:shadow-lg transition-shadow bg-blue-100 text-blue-700 border-2 border-blue-300 hover:bg-blue-200">
                                    <i data-lucide="check" class="w-5 h-5"></i> Finish
                                </a>
                            @endif
                        </div>
                        <p id="playStatus" class="text-sm text-gray-500">Listen to the chord</p>
                    </div>
                </div>

                <!-- Answer Options -->
                <div id="answerOptions" class="grid grid-cols-2 gap-3"
                     data-target="{{ strtolower($currentPractice->chord_type) }}"
                     data-practice-id="{{ $currentPractice->id }}">
                    @php
                        $options = array_merge([$currentPractice->chord_type], $currentPractice->other_options ?? []);
                        if (count($options) < 4) {
                            $allChords = ['Major','Minor','Diminished','Augmented','Dominant 7th','Major 7th','Minor 7th','Half Diminished','Diminished 7th','Augmented 7th'];
                            $existing  = array_map('strtolower', $options);
                            $extra     = array_values(array_filter($allChords, fn($c) => !in_array(strtolower($c), $existing)));
                            shuffle($extra);
                            $options   = array_merge($options, array_slice($extra, 0, 4 - count($options)));
                        }
                        shuffle($options);
                    @endphp
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
            <span><span id="scoreCorrect">0</span> / <span id="scoreTotal">0</span> Correct</span>
        </div>

        <script>
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
                const notes = playButton.dataset.notes ? playButton.dataset.notes.split(',') : [];
                const voicing = playButton.dataset.voicing;
                let isAnswered = false;

                playButton.onclick = async function() {
                    await Tone.start();
                    playButton.disabled = true;
                    playButton.innerHTML = '<i data-lucide="volume-2" class="w-5 h-5 inline"></i> Playing...';
                    playStatus.textContent = 'Playing chord...';
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
                        playButton.innerHTML = '<i data-lucide="play" class="w-5 h-5"></i> Play Again';
                        playStatus.textContent = 'Click to play again';
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
