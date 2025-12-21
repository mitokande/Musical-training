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
    <div id="answerOptions" class="grid grid-cols-2 gap-4">
        <button class="answer-btn card p-6 text-center font-semibold text-gray-700 hover:shadow-md transition-all" data-answer="aa">
            aa
        </button>
        <button class="answer-btn card p-6 text-center font-semibold text-gray-700 hover:shadow-md transition-all" data-answer="bb">
            bb
        </button>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const playButton = document.getElementById('playButton');
    const playStatus = document.getElementById('playStatus');
    let currentAudio = null;
    
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
});
</script>