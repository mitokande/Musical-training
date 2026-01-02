<!-- Content -->
<div class="p-8">
    <!-- Question -->
    <h2 class="text-xl font-semibold text-gray-900 text-center mb-8"></h2>

    <!-- Note Visual Placeholder -->
    <div id="noteVisualPlaceholder" class="w-full h-32 bg-gray-50 border-2 border-dashed border-gray-200 rounded-xl flex items-center justify-center mb-8">
        <x-notechart :target="strtolower($practices->target) . '/' . $practices->octave" />
    </div>

    <!-- Play Button Section -->
    <div class="card p-6 mb-8">
        <div class="flex flex-col items-center">
            <button 
                id="playButton" 
                class="btn-primary text-white font-semibold py-3 px-8 rounded-lg flex items-center gap-2 mb-3 hover:shadow-lg transition-shadow"
                data-note="{{ $practices->target }}{{ $practices->octave }}"
            >
                <i data-lucide="play" class="w-5 h-5"></i>
                Play
            </button>
            <p id="playStatus" class="text-sm text-gray-500">Listen to the note to start</p>
        </div>
    </div>

    <!-- Answer Options -->
    <div id="answerOptions" class="grid grid-cols-2 gap-4" data-target="{{ $practices->target }}">
        @foreach(explode(',', $practices->other_options) as $option)
            <button class="answer-btn card p-6 text-center font-semibold text-gray-700 hover:shadow-md transition-all" data-answer="{{ trim($option) }}">
                {{ trim($option) }}
            </button>
        @endforeach
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
        const note = this.dataset.note;
        const audioUrl = `https://mithatck.com/music/api/note.php?note=${note}&duration=2`;
        
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
        
        // Create and play audio
        currentAudio = new Audio(audioUrl);
        
        currentAudio.addEventListener('canplaythrough', function() {
            playButton.innerHTML = '<i data-lucide="volume-2" class="w-5 h-5"></i> Playing...';
            playStatus.textContent = 'Playing note...';
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
            currentAudio.play();
        });
        
        currentAudio.addEventListener('ended', function() {
            playButton.disabled = false;
            playButton.innerHTML = '<i data-lucide="play" class="w-5 h-5"></i> Play Again';
            playStatus.textContent = 'Click to play again';
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        });
        
        currentAudio.addEventListener('error', function() {
            playButton.disabled = false;
            playButton.innerHTML = '<i data-lucide="play" class="w-5 h-5"></i> Retry';
            playStatus.textContent = 'Error loading audio. Try again.';
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        });
        
        // Start loading
        currentAudio.load();
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
                        practice_id: {{ $practices->id }},
                        answer: answer,
                        target: target
                    })
                });
                
                const data = await response.json();
                
                isAnswered = true;
                
                // Reset button text
                this.textContent = answer;
                
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
                    feedbackMessage.textContent = `✗ Incorrect. The correct answer is ${data.correctAnswer || target}.`;
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