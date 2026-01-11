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
                <h2 class="text-xl font-semibold text-gray-900 text-center mb-6">Listen and select which interval has a larger distance</h2>

                <!-- Two Intervals Visual Display -->
                @php
                    $intervalANotes = explode(',', $currentPractice->interval_a);
                    $intervalBNotes = explode(',', $currentPractice->interval_b);
                @endphp
                
                <div class="grid grid-cols-2 gap-6 mb-8">
                    <!-- Interval A -->
                    <div class="bg-gray-50 border-2 border-gray-200 rounded-xl p-4">
                        <h3 class="text-center font-semibold text-gray-700 mb-2">Interval A</h3>
                        <div id="outputA" style="width: 100%; height: 150px;" 
                             data-notes="{{ strtolower($intervalANotes[0]) . '/' . $currentPractice->octave . ',' . strtolower($intervalANotes[1]) . '/' . $currentPractice->octave }}">
                        </div>
                    </div>
                    
                    <!-- Interval B -->
                    <div class="bg-gray-50 border-2 border-gray-200 rounded-xl p-4">
                        <h3 class="text-center font-semibold text-gray-700 mb-2">Interval B</h3>
                        <div id="outputB" style="width: 100%; height: 150px;" 
                             data-notes="{{ strtolower($intervalBNotes[0]) . '/' . $currentPractice->octave . ',' . strtolower($intervalBNotes[1]) . '/' . $currentPractice->octave }}">
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
                            data-interval-a="{{ strtoupper($intervalANotes[0]) . $currentPractice->octave . ',' . strtoupper($intervalANotes[1]) . $currentPractice->octave }}"
                            data-interval-b="{{ strtoupper($intervalBNotes[0]) . $currentPractice->octave . ',' . strtoupper($intervalBNotes[1]) . $currentPractice->octave }}"
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
                        <span class="text-2xl mb-2 block">A</span>
                        <span class="text-sm text-gray-500">Interval A is larger</span>
                    </button>
                    <button class="answer-btn card p-6 text-center font-semibold text-gray-700 hover:shadow-md transition-all" data-answer="b">
                        <span class="text-2xl mb-2 block">B</span>
                        <span class="text-sm text-gray-500">Interval B is larger</span>
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
            // Define global init function
            window.initPracticeIntervalComparison = function() {
                // Initialize VexFlow for both intervals
                if (typeof Vex !== 'undefined') {
                    console.log("VexFlow Build:", Vex.Flow.BUILD);
                    const { Renderer, Stave, StaveNote, Voice, Formatter } = Vex.Flow;
            
                    // Render Interval A
                    const divA = document.getElementById("outputA");
                    if (divA) {
                        divA.innerHTML = '';
                        const rendererA = new Renderer(divA, Renderer.Backends.SVG);
                        rendererA.resize(180, 150);
                        const contextA = rendererA.getContext();
                        const staveA = new Stave(10, 20, 160);
                        staveA.addClef("treble");
                        staveA.setContext(contextA).draw();
                        
                        const notesFromParamsA = divA.dataset.notes;
                        if (notesFromParamsA) {
                            const notesParsedA = notesFromParamsA.split(',');
                            const notesA = notesParsedA.map(note => new StaveNote({ keys: [note], duration: "h" }));
                            const voiceA = new Voice({ numBeats: 2, beatValue: 2 });
                            voiceA.addTickables(notesA);
                            new Formatter().joinVoices([voiceA]).format([voiceA], 100);
                            voiceA.draw(contextA, staveA);
                        }
                    }

                    // Render Interval B
                    const divB = document.getElementById("outputB");
                    if (divB) {
                        divB.innerHTML = '';
                        const rendererB = new Renderer(divB, Renderer.Backends.SVG);
                        rendererB.resize(180, 150);
                        const contextB = rendererB.getContext();
                        const staveB = new Stave(10, 20, 160);
                        staveB.addClef("treble");
                        staveB.setContext(contextB).draw();
                        
                        const notesFromParamsB = divB.dataset.notes;
                        if (notesFromParamsB) {
                            const notesParsedB = notesFromParamsB.split(',');
                            const notesB = notesParsedB.map(note => new StaveNote({ keys: [note], duration: "h" }));
                            const voiceB = new Voice({ numBeats: 2, beatValue: 2 });
                            voiceB.addTickables(notesB);
                            new Formatter().joinVoices([voiceB]).format([voiceB], 100);
                            voiceB.draw(contextB, staveB);
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
                    let currentAudio = null;
                    let isAnswered = false;

                    // Re-initialize icons
                    if (typeof lucide !== 'undefined') {
                        lucide.createIcons();
                    }
                    
                    // Play button click handler - plays both intervals with 1 second delay
                    playButton.onclick = function() {
                        const intervalA = this.dataset.intervalA.split(',');
                        const intervalB = this.dataset.intervalB.split(',');
                        
                        // Create audio URLs for all 4 notes
                        const audioUrls = [
                            `https://mithatck.com/music/api/note.php?note=${intervalA[0]}&duration=1`,
                            `https://mithatck.com/music/api/note.php?note=${intervalA[1]}&duration=1`,
                            `https://mithatck.com/music/api/note.php?note=${intervalB[0]}&duration=1`,
                            `https://mithatck.com/music/api/note.php?note=${intervalB[1]}&duration=1`,
                        ];
                        
                        // Stop any currently playing audio
                        if (currentAudio) {
                            currentAudio.pause();
                            currentAudio.currentTime = 0;
                        }
                        
                        // Update button state
                        playButton.disabled = true;
                        playButton.innerHTML = '<i data-lucide="loader-2" class="w-5 h-5 animate-spin"></i> Loading...';
                        playStatus.textContent = 'Loading audio...';
                        if (typeof lucide !== 'undefined') lucide.createIcons();
                        
                        // Create audio objects
                        const audios = audioUrls.map(url => new Audio(url));
                        let currentIndex = 0;
                        
                        const handleError = () => {
                            playButton.disabled = false;
                            playButton.innerHTML = '<i data-lucide="play" class="w-5 h-5"></i> Retry';
                            playStatus.textContent = 'Error loading audio. Try again.';
                            if (typeof lucide !== 'undefined') lucide.createIcons();
                        };
                        
                        // Sequential playback function
                        const playNext = () => {
                            if (currentIndex >= audios.length) {
                                // All notes played
                                playButton.disabled = false;
                                playButton.innerHTML = '<i data-lucide="play" class="w-5 h-5"></i> Play Again';
                                playStatus.textContent = 'Click to play again';
                                if (typeof lucide !== 'undefined') lucide.createIcons();
                                return;
                            }
                            
                            currentAudio = audios[currentIndex];
                            
                            // Update status based on which interval is playing
                            if (currentIndex < 2) {
                                playStatus.textContent = 'Playing Interval A...';
                            } else {
                                playStatus.textContent = 'Playing Interval B...';
                            }
                            
                            currentAudio.addEventListener('ended', function() {
                                currentIndex++;
                                
                                // Add 1 second delay between intervals (after note 2, before note 3)
                                if (currentIndex === 2) {
                                    playStatus.textContent = 'Pause between intervals...';
                                    setTimeout(playNext, 1000);
                                } else {
                                    playNext();
                                }
                            }, { once: true });
                            
                            currentAudio.addEventListener('error', handleError, { once: true });
                            currentAudio.play();
                        };
                        
                        // Preload all audio files then start playing
                        let loadedCount = 0;
                        audios.forEach((audio, index) => {
                            audio.addEventListener('canplaythrough', function() {
                                loadedCount++;
                                if (loadedCount === audios.length) {
                                    playButton.innerHTML = '<i data-lucide="volume-2" class="w-5 h-5"></i> Playing...';
                                    if (typeof lucide !== 'undefined') lucide.createIcons();
                                    playNext();
                                }
                            }, { once: true });
                            audio.addEventListener('error', handleError, { once: true });
                            audio.load();
                        });
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
                                        answer: answer,
                                        target: target
                                    })
                                });
                                
                                const data = await response.json();
                                
                                isAnswered = true;
                                
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

            // Run on initial load
            document.addEventListener('livewire:init', function() {
                window.initPracticeIntervalComparison();
                
                // Re-run when practice is updated (Next clicked)
                Livewire.on('practice-updated', () => {
                    // Small delay to ensure DOM is updated
                    setTimeout(() => {
                        window.initPracticeIntervalComparison();
                    }, 50);
                });
            });
            
            // Fallback for non-Livewire loads (standard page load)
            document.addEventListener('DOMContentLoaded', function() {
                if (typeof Livewire === 'undefined') {
                     window.initPracticeIntervalComparison();
                }
            });
        </script>

    </main>

