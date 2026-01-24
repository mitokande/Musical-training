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
                <!-- VexFlow Note Display - Visible from start -->
                <div id="noteDisplayContainer" class="w-full h-32 bg-gray-50 border-2 border-dashed border-gray-200 rounded-xl flex items-center justify-center mb-8">
                    <div class="flex flex-col items-center">
                        <div id="output" style="width: 100%; height: 180px; display: flex; justify-content: center;" 
                             data-notes="{{ strtolower($currentPractice->note1) . '/' . $currentPractice->octave . ',' . strtolower($currentPractice->note2) . '/' . $currentPractice->octave }}">
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
                            data-note="{{ strtoupper($currentPractice->note1) . $currentPractice->octave . ',' . strtoupper($currentPractice->note2) . $currentPractice->octave }}"
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

                <!-- Answer Options - Interval Types -->
                <div id="answerOptions" class="grid grid-cols-3 sm:grid-cols-4 gap-3" 
                     data-target="{{ $currentPractice->interval }}"
                     data-practice-id="{{ $currentPractice->id }}">
                    <button class="answer-btn card p-4 text-center font-semibold text-gray-700 hover:shadow-md transition-all text-sm" data-answer="minor 2nd">
                        Minor 2nd
                    </button>
                    <button class="answer-btn card p-4 text-center font-semibold text-gray-700 hover:shadow-md transition-all text-sm" data-answer="major 2nd">
                        Major 2nd
                    </button>
                    <button class="answer-btn card p-4 text-center font-semibold text-gray-700 hover:shadow-md transition-all text-sm" data-answer="minor 3rd">
                        Minor 3rd
                    </button>
                    <button class="answer-btn card p-4 text-center font-semibold text-gray-700 hover:shadow-md transition-all text-sm" data-answer="major 3rd">
                        Major 3rd
                    </button>
                    <button class="answer-btn card p-4 text-center font-semibold text-gray-700 hover:shadow-md transition-all text-sm" data-answer="perfect 4th">
                        Perfect 4th
                    </button>
                    <button class="answer-btn card p-4 text-center font-semibold text-gray-700 hover:shadow-md transition-all text-sm" data-answer="tritone">
                        Tritone
                    </button>
                    <button class="answer-btn card p-4 text-center font-semibold text-gray-700 hover:shadow-md transition-all text-sm" data-answer="perfect 5th">
                        Perfect 5th
                    </button>
                    <button class="answer-btn card p-4 text-center font-semibold text-gray-700 hover:shadow-md transition-all text-sm" data-answer="minor 6th">
                        Minor 6th
                    </button>
                    <button class="answer-btn card p-4 text-center font-semibold text-gray-700 hover:shadow-md transition-all text-sm" data-answer="major 6th">
                        Major 6th
                    </button>
                    <button class="answer-btn card p-4 text-center font-semibold text-gray-700 hover:shadow-md transition-all text-sm" data-answer="minor 7th">
                        Minor 7th
                    </button>
                    <button class="answer-btn card p-4 text-center font-semibold text-gray-700 hover:shadow-md transition-all text-sm" data-answer="major 7th">
                        Major 7th
                    </button>
                    <button class="answer-btn card p-4 text-center font-semibold text-gray-700 hover:shadow-md transition-all text-sm" data-answer="perfect octave">
                        Perfect Octave
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
            window.initPracticeHarmonicInterval = function() {
                // Initialize VexFlow
                if (typeof Vex !== 'undefined') {
                    console.log("VexFlow Build:", Vex.Flow.BUILD);
                    const { Renderer, Stave, StaveNote, Voice, Formatter } = Vex.Flow;
            
                    const div = document.getElementById("output");
                    if (div) {
                        div.innerHTML = '';
                        const renderer = new Renderer(div, Renderer.Backends.SVG);
                        renderer.resize(300, 180);
                        const context = renderer.getContext();
                        const stave = new Stave(10, 30, 280);
                        stave.addClef("treble");
                        stave.setContext(context).draw();
                        
                        const notesFromParams = div.dataset.notes;
                        if (notesFromParams) {
                            const notesParsed = notesFromParams.split(',');
                            const notes = notesParsed.map(note => new StaveNote({ keys: [note], duration: "h" }));
                            const voice = new Voice({ numBeats: 2, beatValue: 2 });
                            voice.addTickables(notes);
                            new Formatter().joinVoices([voice]).format([voice], 220);
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
                    let currentAudios = [];
                    let isAnswered = false;

                    // Re-initialize icons
                    if (typeof lucide !== 'undefined') {
                        lucide.createIcons();
                    }
                    
                    // Play button click handler - plays notes SIMULTANEOUSLY (harmonic)
                    playButton.onclick = function() {
                        const notes = this.dataset.note;
                        const notesParsed = notes.split(',');
                        const audioUrl1 = `https://mithatck.com/music/api/note.php?note=${notesParsed[0]}&duration=2`;
                        const audioUrl2 = `https://mithatck.com/music/api/note.php?note=${notesParsed[1]}&duration=2`;
                        
                        // Stop any currently playing audio
                        currentAudios.forEach(audio => {
                            audio.pause();
                            audio.currentTime = 0;
                        });
                        currentAudios = [];
                        
                        // Update button state
                        playButton.disabled = true;
                        playButton.innerHTML = '<i data-lucide="loader-2" class="w-5 h-5 animate-spin"></i> Loading...';
                        playStatus.textContent = 'Loading audio...';
                        if (typeof lucide !== 'undefined') lucide.createIcons();
                        
                        // Create audio objects
                        const audio1 = new Audio(audioUrl1);
                        const audio2 = new Audio(audioUrl2);
                        currentAudios = [audio1, audio2];
                        
                        let loadedCount = 0;
                        let endedCount = 0;
        
                        const handleError = () => {
                            playButton.disabled = false;
                            playButton.innerHTML = '<i data-lucide="play" class="w-5 h-5"></i> Retry';
                            playStatus.textContent = 'Error loading audio. Try again.';
                            if (typeof lucide !== 'undefined') lucide.createIcons();
                        };
                        
                        const handleLoaded = () => {
                            loadedCount++;
                            if (loadedCount === 2) {
                                // Both loaded, play simultaneously
                                playButton.innerHTML = '<i data-lucide="volume-2" class="w-5 h-5"></i> Playing...';
                                playStatus.textContent = 'Playing harmonic interval...';
                                if (typeof lucide !== 'undefined') lucide.createIcons();
                                audio1.play();
                                audio2.play();
                            }
                        };
                        
                        const handleEnded = () => {
                            endedCount++;
                            if (endedCount === 2) {
                                // Both finished
                                playButton.disabled = false;
                                playButton.innerHTML = '<i data-lucide="play" class="w-5 h-5"></i> Play Again';
                                playStatus.textContent = 'Click to play again';
                                if (typeof lucide !== 'undefined') lucide.createIcons();
                            }
                        };
                        
                        audio1.addEventListener('canplaythrough', handleLoaded, { once: true });
                        audio2.addEventListener('canplaythrough', handleLoaded, { once: true });
                        
                        audio1.addEventListener('ended', handleEnded);
                        audio2.addEventListener('ended', handleEnded);
                        
                        audio1.addEventListener('error', handleError);
                        audio2.addEventListener('error', handleError);
                        
                        // Start loading both
                        audio1.load();
                        audio2.load();
                    };
                    
                    // Answer button click handlers
                    answerButtons.forEach(btn => {
                        btn.onclick = async function() {
                            // Prevent multiple answers
                            if (isAnswered) return;
                            
                            const answer = this.dataset.answer;
                            const originalContent = this.innerHTML;
                            
                            // Disable all buttons while checking
                            answerButtons.forEach(b => b.disabled = true);
                            
                            // Show loading state on clicked button
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
                                        practice_id: 5, // Harmonic Interval Practice ID
                                        answer: answer,
                                        target: target.toLowerCase()
                                    })
                                });
                                
                                const data = await response.json();
                                
                                isAnswered = true;
                                
                                // Toggle buttons: Hide Play, Show Next
                                if (playButton) playButton.classList.add('hidden');
                                if (playStatus) playStatus.classList.add('hidden');
                                if (nextButton) nextButton.classList.remove('hidden');

                                // Reset button text
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
                                    feedbackMessage.textContent = `✗ Incorrect. The correct answer is ${target}.`;
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
                window.initPracticeHarmonicInterval();
                
                // Re-run when practice is updated (Next clicked)
                Livewire.on('practice-updated', () => {
                    // Small delay to ensure DOM is updated
                    setTimeout(() => {
                        window.initPracticeHarmonicInterval();
                    }, 50);
                });
            });
            
            // Fallback for non-Livewire loads (standard page load)
            document.addEventListener('DOMContentLoaded', function() {
                if (typeof Livewire === 'undefined') {
                     window.initPracticeHarmonicInterval();
                }
            });
        </script>

    </main>
