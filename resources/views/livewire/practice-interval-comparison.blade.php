    <!-- Main Content -->
    <main wire:id="practice-interval-comparison-{{ $currentPracticeIndex }}" class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
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
                            <h1 class="text-xl font-bold text-white">Interval Comparison Practice</h1>
                            <p class="text-white/80 text-sm">Which interval is larger?</p>
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
                <!-- Question -->
                <!-- Two Intervals Visual Display -->
                @php
                    $intervalANotes = explode(',', $currentPractice->interval_a ?? '');
                    $intervalBNotes = explode(',', $currentPractice->interval_b ?? '');
                    $intervalANote0 = strtolower(trim($intervalANotes[0] ?? ''));
                    $intervalANote1 = strtolower(trim($intervalANotes[1] ?? $intervalANotes[0] ?? ''));
                    $intervalBNote0 = strtolower(trim($intervalBNotes[0] ?? ''));
                    $intervalBNote1 = strtolower(trim($intervalBNotes[1] ?? $intervalBNotes[0] ?? ''));
                    $octave = $currentPractice->octave ?? '4';
                @endphp

                <div id="noteDisplayContainer" class="w-full h-32 bg-gray-50 border-2 border-dashed border-gray-200 rounded-xl flex items-center justify-center mb-8 hidden">
                    <div class="flex flex-col items-center">
                        <div id="output" style="width: 100%; height: 180px; display: flex; justify-content: center;"
                             data-notes="{{ $intervalANote0 . '/' . $octave . ',' . $intervalANote1 . '/' . $octave . ',' . $intervalBNote0 . '/' . $octave . ',' . $intervalBNote1 . '/' . $octave }}">
                        </div>
                    </div>
                </div>

                <!-- Play Button Section -->
                <div class="card p-6 mb-8">
                    <div class="flex flex-col items-center">
                        <div class="flex">
                            <button 
                            id="playButton" 
                            class="btn-primary text-white font-semibold py-3 px-8 rounded-lg flex items-center gap-2 mb-3 hover:shadow-lg transition-shadow"
                            data-interval-a="{{ strtoupper($intervalANote0) . $octave . ',' . strtoupper($intervalANote1) . $octave }}"
                            data-interval-b="{{ strtoupper($intervalBNote0) . $octave . ',' . strtoupper($intervalBNote1) . $octave }}"
                        >
                            <i data-lucide="play" class="w-5 h-5"></i>
                            Play Both Intervals
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
                        <p id="playStatus" class="text-sm text-gray-500">Listen to both intervals to start</p>
                    </div>
                </div>

                <!-- Answer Options -->
                <div id="answerOptions" class="grid grid-cols-2 gap-4" 
                     data-target="{{ $currentPractice->target }}"
                     data-practice-id="{{ $currentPractice->id }}">
                    <button class="answer-btn card p-6 text-center font-semibold text-gray-700 hover:shadow-md transition-all" data-answer="a">
                        <!-- <span class="text-2xl mb-2 block">A</span> -->
                        <span class="text-md text-gray-500">Interval A is larger</span>
                    </button>
                    <button class="answer-btn card p-6 text-center font-semibold text-gray-700 hover:shadow-md transition-all" data-answer="b">
                        <!-- <span class="text-2xl mb-2 block">B</span> -->
                        <span class="text-md text-gray-500">Interval B is larger</span>
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

        
        <script src="https://cdn.jsdelivr.net/npm/vexflow@4.2.2/build/cjs/vexflow.js"></script>
        <script>
            function vfStemDirCmp(noteKey) {
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
                return midi >= 71 ? -1 : 1;
            }

            // Define global init function
            window.initPracticeIntervalComparison = function() {
                window._practiceGen = (window._practiceGen || 0) + 1;
                const myGen = window._practiceGen;
                // Initialize VexFlow for both intervals
                if (typeof Vex !== 'undefined') {
                    const { Renderer, Stave, StaveNote, Voice, Formatter, Accidental } = Vex.Flow;

                    const div = document.getElementById("output");
                    if (div) {
                        div.innerHTML = '';
                        const renderer = new Renderer(div, Renderer.Backends.SVG);
                        renderer.resize(568, 180);
                        const context = renderer.getContext();
                        const stave = new Stave(10, 30, 542);
                        stave.addClef("treble");
                        stave.setNoteStartX(stave.getNoteStartX() + 100);
                        stave.setContext(context).draw();

                        const notesFromParams = div.dataset.notes;
                        if (notesFromParams) {
                            const notesParsed = notesFromParams.split(',');
                            const notes = notesParsed.map(note => {
                                const sd = vfStemDirCmp(note);
                                return new StaveNote({ keys: [note], duration: 'q', stem_direction: sd });
                            });
                            const voice = new Voice({ numBeats: 4, beatValue: 4 });
                            voice.addTickables(notes);
                            Accidental.applyAccidentals([voice], 'C');
                            new Formatter().joinVoices([voice]).format([voice], 300);
                            voice.draw(context, stave);
                        }
                    }
                }

                // Initialize Interactions
                const playButton = document.getElementById('playButton');
                const playStatus = document.getElementById('playStatus');
                const nextButton = document.getElementById('nextPracticeBtn');
                const answerOptions = document.getElementById('answerOptions');
                const answerButtons = document.querySelectorAll('.answer-btn');
                const feedbackMessage = document.getElementById('feedbackMessage');
                
                if (playButton && answerOptions) {
                    const target = answerOptions.dataset.target;
                    const practiceId = answerOptions.dataset.practiceId;
                    let isAnswered = false;

                    if (typeof lucide !== 'undefined') {
                        lucide.createIcons();
                    }

                    // Play button click handler - plays interval A then pause then interval B
                    playButton.onclick = async function() {
                        await Tone.start();
                        const intervalA = this.dataset.intervalA.split(',');
                        const intervalB = this.dataset.intervalB.split(',');
                        playButton.disabled = true;
                        playButton.innerHTML = '<i data-lucide="volume-2" class="w-5 h-5"></i> Playing...';
                        playStatus.textContent = 'Playing Interval A...';
                        if (typeof lucide !== 'undefined') lucide.createIcons();
                        // Play interval A (2 sequential notes)
                        window.HarmonivaAudio.playSequential(intervalA, 700, 1);
                        // After ~2s play interval B
                        setTimeout(() => {
                            if (window._practiceGen !== myGen) return;
                            playStatus.textContent = 'Playing Interval B...';
                            window.HarmonivaAudio.playSequential(intervalB, 700, 1);
                        }, 2000);
                        setTimeout(() => {
                            if (window._practiceGen !== myGen) return;
                            playButton.disabled = false;
                            playButton.innerHTML = '<i data-lucide="play" class="w-5 h-5"></i> Play Again';
                            playStatus.textContent = 'Click to play again';
                            if (typeof lucide !== 'undefined') lucide.createIcons();
                        }, 4200);
                    };
                    
                    // Answer button click handlers
                    answerButtons.forEach(btn => {
                        btn.onclick = async function() {
                            // Prevent multiple answers
                            if (isAnswered) return;
                            
                            const answer = this.dataset.answer;
                            
                            // Disable all buttons while checking
                            answerButtons.forEach(b => b.disabled = true);
                            
                            // Show loading state on clicked button
                            const originalContent = this.innerHTML;
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
                                        practice_id: 3,
                                        question_id: parseInt(practiceId),
                                        answer: answer
                                    })
                                });
                                
                                const data = await response.json();
                                
                                isAnswered = true;
                                
                                // Show the note display when answer is submitted
                                const noteDisplayContainer = document.getElementById('noteDisplayContainer');
                                if (noteDisplayContainer) noteDisplayContainer.classList.remove('hidden');
                                
                                // Toggle buttons: Hide Play, Show Next
                                if (playButton) playButton.classList.add('hidden');
                                if (playStatus) playStatus.classList.add('hidden');
                                if (nextButton) nextButton.classList.remove('hidden');

                                // Reset button content
                                this.innerHTML = originalContent;
                                
                                if (data.is_correct) {
                                    // Correct answer
                                    this.classList.add('correct');
                                    this.classList.remove('text-gray-700');
                                    this.classList.add('text-green-700');
                                    feedbackMessage.textContent = '✓ Correct! Well done!';
                                    feedbackMessage.classList.remove('hidden', 'bg-red-100', 'text-red-700');
                                    feedbackMessage.classList.add('bg-green-100', 'text-green-700');
                                } else {
                                    // Incorrect answer
                                    this.classList.add('incorrect');
                                    this.classList.remove('text-gray-700');
                                    this.classList.add('text-red-700');
                                    feedbackMessage.textContent = `✗ Incorrect. The correct answer is ${target.toUpperCase()}.`;
                                    feedbackMessage.classList.remove('hidden', 'bg-green-100', 'text-green-700');
                                    feedbackMessage.classList.add('bg-red-100', 'text-red-700');
                                    
                                    // Highlight correct answer
                                    answerButtons.forEach(b => {
                                        if (b.dataset.answer.toLowerCase() === target.toLowerCase()) {
                                            b.classList.add('correct');
                                            b.classList.remove('text-gray-700');
                                            b.classList.add('text-green-700');
                                        }
                                    });
                                }
                                
                                // Update score displays if they exist
                                if (data.xp !== undefined) {
                                    const xpElement = document.getElementById('xpEarned');
                                    if (xpElement) xpElement.textContent = data.xp;
                                }
                                if (data.correctCount !== undefined) {
                                    const correctElement = document.getElementById('correctCount');
                                    const scoreCorrect = document.getElementById('scoreCorrect');
                                    if (correctElement) correctElement.textContent = data.correctCount;
                                    if (scoreCorrect) scoreCorrect.textContent = data.correctCount;
                                }
                                if (data.totalCount !== undefined) {
                                    const scoreTotal = document.getElementById('scoreTotal');
                                    if (scoreTotal) scoreTotal.textContent = data.totalCount;
                                }
                                
                            } catch (error) {
                                console.error('Error checking answer:', error);
                                this.innerHTML = originalContent;
                                feedbackMessage.textContent = 'Error checking answer. Please try again.';
                                feedbackMessage.classList.remove('hidden', 'bg-green-100', 'text-green-700');
                                feedbackMessage.classList.add('bg-red-100', 'text-red-700');
                                
                                // Re-enable buttons on error
                                answerButtons.forEach(b => b.disabled = false);
                                isAnswered = false;
                            }
                        };
                    });
                }
            };

            // Initialize immediately if DOM already ready, else wait.
            if (document.readyState !== 'loading') {
                window.initPracticeIntervalComparison();
            } else {
                document.addEventListener('DOMContentLoaded', function() {
                    window.initPracticeIntervalComparison();
                });
            }

            document.addEventListener('livewire:init', function() {
                window.initPracticeIntervalComparison();
            });

            if (!window._practiceIntervalComparisonUpdatedRegistered) {
                window._practiceIntervalComparisonUpdatedRegistered = true;
                document.addEventListener('livewire:init', function() {
                    Livewire.on('practice-updated', () => {
                        setTimeout(() => {
                            window.initPracticeIntervalComparison();
                        }, 50);
                    });
                }, { once: true });
            }
        </script>

    </main>
