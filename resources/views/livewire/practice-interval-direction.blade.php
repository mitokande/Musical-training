
    <!-- Main Content -->
    <main class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <!-- Top Bar: Back Button & Practice Name -->
        <div class="flex items-center justify-between mb-4">
            <a href="/learn" class="inline-flex items-center gap-2 text-gray-600 hover:text-gray-900 transition-colors">
                <i data-lucide="arrow-left" class="w-5 h-5"></i>
                <span class="font-medium">Back</span>
            </a>
            <span class="px-4 py-1.5 bg-pink-100 text-pink-700 rounded-full text-sm font-medium">
                Interval Direction Practice
            </span>
        </div>

        <!-- Progress Bar -->
        <div class="flex items-center justify-between mb-2">
            <span class="text-sm text-gray-600">Question <span id="currentQuestion">{{ $currentPracticeIndex + 1 }}</span> of <span id="totalQuestions">{{ $practices->count() }}</span></span>
            <span class="text-sm text-gray-600"><span id="correctCount">0</span> correct</span>
        </div>
        <div class="w-full bg-gray-200 rounded-full h-2 mb-6">
            <div id="progressBar" class="progress-bar h-2 rounded-full transition-all duration-300" style="width: 100%"></div>
        </div>

        <!-- Practice Card -->
        <div class="card overflow-hidden mb-6">
            <!-- Header -->
            <div class="hero-gradient p-6">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-white/20 flex items-center justify-center">
                        <i data-lucide="music" class="w-6 h-6 text-white"></i>
                    </div>
                    <div>
                        <h1 class="text-xl font-bold text-white">Interval Direction Practice</h1>
                        <p class="text-white/80 text-sm">Listening Exercise</p>
                    </div>
                </div>
            </div>

            <!-- Content -->
            <div class="p-8">
                <!-- Question -->
                <h2 class="text-xl font-semibold text-gray-900 text-center mb-8"></h2>

                <!-- Note Visual Placeholder -->
                <div id="noteVisualPlaceholder" class="w-full h-32 bg-gray-50 border-2 border-dashed border-gray-200 rounded-xl flex items-center justify-center mb-8">
                    <x-notechart :target="strtolower($currentPractice->note1) . '/' . $currentPractice->octave . ',' . $currentPractice->note2 . '/' . $currentPractice->octave" />
                </div>

                <!-- Play Button Section -->
                <div class="card p-6 mb-8">
                    <div class="flex flex-col items-center">
                        <button 
                            id="playButton" 
                            class="btn-primary text-white font-semibold py-3 px-8 rounded-lg flex items-center gap-2 mb-3 hover:shadow-lg transition-shadow"
                            data-note="{{ strtoupper($currentPractice->note1) . $currentPractice->octave . ',' . strtoupper($currentPractice->note2)  . $currentPractice->octave }}"
                        >
                            <i data-lucide="play" class="w-5 h-5"></i>
                            Play
                        </button>
                        <p id="playStatus" class="text-sm text-gray-500">Listen to the note to start</p>
                    </div>
                </div>

                <!-- Answer Options -->
                <div id="answerOptions" class="grid grid-cols-2 gap-4" data-target="{{ $currentPractice->direction }}">
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

            <script>
            document.addEventListener('DOMContentLoaded', function() {
                const playButton = document.getElementById('playButton');
                const playStatus = document.getElementById('playStatus');
                const answerOptions = document.getElementById('answerOptions');
                const answerButtons = document.querySelectorAll('.answer-btn');
                const feedbackMessage = document.getElementById('feedbackMessage');
                const target = answerOptions.dataset.target;
                let currentAudio = null;
                let isAnswered = false;
                
                // Play button click handler
                playButton.addEventListener('click', function() {
                    const notes = this.dataset.note;
                    const notesParsed = notes.split(',');
                    const audioUrl1 = `https://mithatck.com/music/api/note.php?note=${notesParsed[0]}&duration=1`;
                    const audioUrl2 = `https://mithatck.com/music/api/note.php?note=${notesParsed[1]}&duration=1`;
                    
                    // Stop any currently playing audio
                    if (currentAudio) {
                        currentAudio.pause();
                        currentAudio.currentTime = 0;
                    }
                    
                    // Update button state
                    playButton.disabled = true;
                    playButton.innerHTML = '<i data-lucide="loader-2" class="w-5 h-5 animate-spin"></i> Loading...';
                    playStatus.textContent = 'Loading audio...';
                    
                    // Reinitialize lucide icons for the new icon
                    if (typeof lucide !== 'undefined') {
                        lucide.createIcons();
                    }
                    
                    // Create audio objects
                    const audio1 = new Audio(audioUrl1);
                    const audio2 = new Audio(audioUrl2);
                    currentAudio = audio1;

                    const handleError = () => {
                        playButton.disabled = false;
                        playButton.innerHTML = '<i data-lucide="play" class="w-5 h-5"></i> Retry';
                        playStatus.textContent = 'Error loading audio. Try again.';
                        if (typeof lucide !== 'undefined') {
                            lucide.createIcons();
                        }
                    };
                    
                    // Play first note
                    audio1.addEventListener('canplaythrough', function() {
                        playButton.innerHTML = '<i data-lucide="volume-2" class="w-5 h-5"></i> Playing...';
                        playStatus.textContent = 'Playing notes...';
                        if (typeof lucide !== 'undefined') {
                            lucide.createIcons();
                        }
                        audio1.play();
                    });
                    
                    // When first note ends, play second note
                    audio1.addEventListener('ended', function() {
                        currentAudio = audio2;
                        audio2.play();
                    });

                    // When second note ends, reset button
                    audio2.addEventListener('ended', function() {
                        playButton.disabled = false;
                        playButton.innerHTML = '<i data-lucide="play" class="w-5 h-5"></i> Play Again';
                        playStatus.textContent = 'Click to play again';
                        if (typeof lucide !== 'undefined') {
                            lucide.createIcons();
                        }
                    });
                    
                    audio1.addEventListener('error', handleError);
                    audio2.addEventListener('error', handleError);
                    
                    // Start loading both
                    audio1.load();
                    audio2.load();
                });
                
                // Answer button click handlers
                answerButtons.forEach(btn => {
                    btn.addEventListener('click', async function() {
                        // Prevent multiple answers
                        if (isAnswered) return;
                        
                        const answer = this.dataset.answer;
                        
                        // Disable all buttons while checking
                        answerButtons.forEach(b => b.disabled = true);
                        
                        // Show loading state on clicked button
                        this.innerHTML = '<i data-lucide="loader-2" class="w-5 h-5 animate-spin inline"></i> Checking...';
                        if (typeof lucide !== 'undefined') {
                            lucide.createIcons();
                        }
                        
                        try {
                            const response = await fetch('/api/practice/check-answer', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                                },
                                body: JSON.stringify({
                                    practice_id: {{ $currentPractice->id }},
                                    answer: answer,
                                    target: target
                                })
                            });
                            
                            const data = await response.json();
                            
                            isAnswered = true;
                            
                            // Reset button text
                            this.textContent = answer.charAt(0).toUpperCase() + answer.slice(1);
                            
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
                                feedbackMessage.textContent = `✗ Incorrect. The correct answer is ${data.correctAnswer || target.charAt(0).toUpperCase() + target.slice(1)}.`;
                                feedbackMessage.classList.remove('hidden', 'bg-green-100', 'text-green-700');
                                feedbackMessage.classList.add('bg-red-100', 'text-red-700');
                                
                                // Highlight correct answer
                                answerButtons.forEach(b => {
                                    if (b.dataset.answer.toUpperCase() === target.toUpperCase()) {
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
                            this.textContent = answer;
                            feedbackMessage.textContent = 'Error checking answer. Please try again.';
                            feedbackMessage.classList.remove('hidden', 'bg-green-100', 'text-green-700');
                            feedbackMessage.classList.add('bg-red-100', 'text-red-700');
                            
                            // Re-enable buttons on error
                            answerButtons.forEach(b => b.disabled = false);
                            isAnswered = false;
                        }
                    });
                });
            });
            </script>
            
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
    </main>
