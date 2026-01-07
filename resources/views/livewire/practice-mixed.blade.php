<!-- Main Content -->
<main wire:id="practice-mixed-{{ $currentPracticeIndex }}" class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    
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
                        <h1 class="text-xl font-bold text-white">{{ $sessionTitle }}</h1>
                        <p class="text-white/80 text-sm">
                            @if($currentPractice['type'] === 'single_note')
                                Single Note Recognition
                            @elseif($currentPractice['type'] === 'interval_direction')
                                Interval Direction
                            @else
                                Mixed Exercise
                            @endif
                        </p>
                    </div>
                </div>

                <div class="absolute right-0 top-1/2 -translate-y-1/2">
                    <span class="inline-flex items-center gap-1.5 rounded-xl bg-white/10 px-3 py-2 text-sm font-semibold text-white/90 ring-1 ring-white/20 backdrop-blur">
                        <span id="currentQuestion">{{ $currentPracticeIndex + 1 }}</span>
                        <span class="text-white font-medium">/</span>
                        <span id="totalQuestions">{{ $totalQuestions }}</span>
                    </span>
                </div>
            </div>
            
            <!-- Progress Bar -->
            <div class="mt-4">
                <div class="h-2 bg-white/20 rounded-full overflow-hidden">
                    <div class="h-full bg-white/80 rounded-full transition-all duration-300" 
                         style="width: {{ (($currentPracticeIndex + 1) / $totalQuestions) * 100 }}%"></div>
                </div>
            </div>
        </div>

        <!-- Content -->
        <div class="p-8">
            @php
                $practice = $currentPractice['data'];
                $type = $currentPractice['type'];
            @endphp

            <!-- Question Type Badge -->
            <div class="flex justify-center mb-4">
                @if($type === 'single_note')
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-700">
                        <i data-lucide="music-2" class="w-3 h-3"></i>
                        Single Note
                    </span>
                @elseif($type === 'interval_direction')
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-700">
                        <i data-lucide="arrow-up-down" class="w-3 h-3"></i>
                        Interval Direction
                    </span>
                @endif
            </div>

            <!-- Note Visual -->
            <div id="noteVisualPlaceholder" class="w-full h-32 bg-gray-50 border-2 border-dashed border-gray-200 rounded-xl flex items-center justify-center mb-8">
                <div>
                    <div id="output" style="width: 400px; height: 200px;" 
                         data-notes="@if($type === 'single_note'){{ strtolower($practice['target']) . '/' . $practice['octave'] }}@elseif($type === 'interval_direction'){{ strtolower($practice['note1']) . '/' . $practice['octave'] . ',' . strtolower($practice['note2']) . '/' . $practice['octave'] }}@endif"
                         data-type="{{ $type }}">
                    </div>
                </div>
            </div>

            <!-- Play Button Section -->
            <div class="card p-6 mb-8">
                <div class="flex flex-col items-center">
                    <div class="flex gap-3">
                        <button 
                            id="playButton" 
                            class="btn-primary text-white font-semibold py-3 px-8 rounded-lg flex items-center gap-2 mb-3 hover:shadow-lg transition-shadow"
                            data-note="@if($type === 'single_note'){{ ucfirst(strtolower($practice['target'])) . $practice['octave'] }}@elseif($type === 'interval_direction'){{ ucfirst(strtolower($practice['note1'])) . $practice['octave'] . ',' . ucfirst(strtolower($practice['note2'])) . $practice['octave'] }}@endif"
                            data-type="{{ $type }}"
                        >
                            <i data-lucide="play" class="w-5 h-5"></i>
                            Play
                        </button>
                        
                        @if ($currentPracticeIndex < ($totalQuestions - 1))
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
                                    bg-green-100 text-green-700 border border-green-300 hover:bg-green-200 hover:text-green-800 hover:border-green-400"
                                style="border-width: 2px;"
                            >
                                <i data-lucide="check" class="w-5 h-5"></i>
                                Finish
                            </a>
                        @endif
                    </div>
                    <p id="playStatus" class="text-sm text-gray-500">Listen to the note to start</p>
                </div>
            </div>

            <!-- Answer Options - Dynamic based on type -->
            @if($type === 'single_note')
                <div id="answerOptions" class="grid grid-cols-2 gap-4" 
                     data-target="{{ $practice['target'] }}"
                     data-practice-id="{{ $practice['id'] }}"
                     data-type="{{ $type }}">
                    @foreach(explode(',', $practice['other_options']) as $option)
                        <button class="answer-btn card p-6 text-center font-semibold text-gray-700 hover:shadow-md transition-all" data-answer="{{ trim($option) }}">
                            {{ trim($option) }}
                        </button>
                    @endforeach
                </div>
            @elseif($type === 'interval_direction')
                <div id="answerOptions" class="grid grid-cols-2 gap-4" 
                     data-target="{{ $practice['direction'] }}"
                     data-practice-id="{{ $practice['id'] }}"
                     data-type="{{ $type }}">
                    <button class="answer-btn card p-6 text-center font-semibold text-gray-700 hover:shadow-md transition-all flex items-center justify-center gap-2" data-answer="ascending">
                        <i data-lucide="trending-up" class="w-5 h-5 text-green-500"></i>
                        Ascending
                    </button>
                    <button class="answer-btn card p-6 text-center font-semibold text-gray-700 hover:shadow-md transition-all flex items-center justify-center gap-2" data-answer="descending">
                        <i data-lucide="trending-down" class="w-5 h-5 text-red-500"></i>
                        Descending
                    </button>
                </div>
            @endif

            <!-- Feedback Message -->
            <div id="feedbackMessage" class="mt-4 p-4 rounded-lg text-center font-medium hidden"></div>
        </div>
    </div>

    <!-- XP & Score -->
    <div class="flex items-center justify-center gap-4 text-sm text-gray-500">
        <span class="flex items-center gap-1">
            <i data-lucide="sparkles" class="w-4 h-4 text-yellow-500"></i>
            +<span id="xpEarned">{{ $xpEarned }}</span> XP
        </span>
        <span>•</span>
        <span><span id="scoreCorrect">{{ $correctCount }}</span> / <span id="scoreTotal">{{ $currentPracticeIndex }}</span> Correct</span>
    </div>

    
    <script src="https://cdn.jsdelivr.net/npm/vexflow@4.2.2/build/cjs/vexflow.js"></script>
    <script>
        // Define global init function for mixed practice
        window.initPracticeMixed = function() {
            // Initialize VexFlow
            if (typeof Vex !== 'undefined') {
                console.log("VexFlow Build:", Vex.Flow.BUILD);
                const { Renderer, Stave, StaveNote, Voice, Formatter } = Vex.Flow;
        
                // Create an SVG renderer and attach it to the DIV element named "output".
                const div = document.getElementById("output");
                if (div) {
                    div.innerHTML = ''; // Clear previous content
                    
                    const renderer = new Renderer(div, Renderer.Backends.SVG);
            
                    // Configure the rendering context.
                    renderer.resize(420, 200);
                    const context = renderer.getContext();
            
                    // Create a stave of width 400 at position 10, 40 on the canvas.
                    const stave = new Stave(10, 40, 600);
            
                    // Add a clef and time signature.
                    stave.addClef("treble");
            
                    stave.setNoteStartX(stave.getNoteStartX() + 40); // Adds padding after clef
            
                    // Connect it to the rendering context and draw!
                    stave.setContext(context).draw();
            
                    // Create the notes
                    const notesFromParams = div.dataset.notes;
                    if (notesFromParams) {
                        const notesParsed = notesFromParams.split(',');
                        console.log("Notes parsed:", notesParsed);
                        const duration = notesParsed.length > 1 ? "h" : "1";
                        const notes = notesParsed.map(note => new StaveNote({ keys: [note], duration: duration }));
                        
                        // Create a voice in 4/4 and add above notes
                        const voice = new Voice({ numBeats: 2, beatValue: 2 });
                        voice.addTickables(notes);
                
                        // Format and justify the notes to 400 pixels.
                        new Formatter().joinVoices([voice]).format([voice], 300);
                
                        // Render voice
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
                const practiceType = answerOptions.dataset.type;
                const noteType = playButton.dataset.type;
                let currentAudio = null;
                let isAnswered = false;

                // Re-initialize icons
                if (typeof lucide !== 'undefined') {
                    lucide.createIcons();
                }
                
                // Play button click handler
                playButton.onclick = function() {
                    const notes = this.dataset.note;
                    const notesParsed = notes.split(',');
                    
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
                    
                    if (notesParsed.length === 1) {
                        console.log('single note playback', notesParsed);
                        // Single note playback
                        const sanitizedNotesParsed = notesParsed.map(n => n.replace(/#/g, '%23'));
                        const audioUrl = `https://mithatck.com/music/api/note.php?note=${sanitizedNotesParsed[0]}&duration=1`;
                        const audio = new Audio(audioUrl);
                        currentAudio = audio;

                        const handleError = () => {
                            playButton.disabled = false;
                            playButton.innerHTML = '<i data-lucide="play" class="w-5 h-5"></i> Retry';
                            playStatus.textContent = 'Error loading audio. Try again.';
                            if (typeof lucide !== 'undefined') lucide.createIcons();
                        };
                        
                        audio.addEventListener('canplaythrough', function() {
                            if (currentAudio !== audio) return;
                            playButton.innerHTML = '<i data-lucide="volume-2" class="w-5 h-5"></i> Playing...';
                            playStatus.textContent = 'Playing note...';
                            if (typeof lucide !== 'undefined') lucide.createIcons();
                            audio.play();
                        }, { once: true });
                        
                        audio.addEventListener('ended', function() {
                            playButton.disabled = false;
                            playButton.innerHTML = '<i data-lucide="play" class="w-5 h-5"></i> Play Again';
                            playStatus.textContent = 'Click to play again';
                            if (typeof lucide !== 'undefined') lucide.createIcons();
                        });
                        
                        audio.addEventListener('error', handleError);
                        audio.load();
                    } else {
                        // Two notes (interval) playback
                        const sanitizedNotesParsed = notesParsed.map(n => n.replace(/#/g, '%23'));
                        const audioUrl1 = `https://mithatck.com/music/api/note.php?note=${sanitizedNotesParsed[0]}&duration=1`;
                        const audioUrl2 = `https://mithatck.com/music/api/note.php?note=${sanitizedNotesParsed[1]}&duration=1`;
                        
                        const audio1 = new Audio(audioUrl1);
                        const audio2 = new Audio(audioUrl2);
                        currentAudio = audio1;

                        const handleError = () => {
                            playButton.disabled = false;
                            playButton.innerHTML = '<i data-lucide="play" class="w-5 h-5"></i> Retry';
                            playStatus.textContent = 'Error loading audio. Try again.';
                            if (typeof lucide !== 'undefined') lucide.createIcons();
                        };
                        
                        audio1.addEventListener('canplaythrough', function() {
                            if (currentAudio !== audio1) return;
                            playButton.innerHTML = '<i data-lucide="volume-2" class="w-5 h-5"></i> Playing...';
                            playStatus.textContent = 'Playing notes...';
                            if (typeof lucide !== 'undefined') lucide.createIcons();
                            audio1.play();
                        }, { once: true });
                        
                        audio1.addEventListener('ended', function() {
                            currentAudio = audio2;
                            audio2.play();
                        });

                        audio2.addEventListener('ended', function() {
                            playButton.disabled = false;
                            playButton.innerHTML = '<i data-lucide="play" class="w-5 h-5"></i> Play Again';
                            playStatus.textContent = 'Click to play again';
                            if (typeof lucide !== 'undefined') lucide.createIcons();
                        });
                        
                        audio1.addEventListener('error', handleError);
                        audio2.addEventListener('error', handleError);
                        
                        audio1.load();
                        audio2.load();
                    }
                };
                
                // Answer button click handlers
                answerButtons.forEach(btn => {
                    btn.onclick = async function() {
                        // Prevent multiple answers
                        if (isAnswered) return;
                        
                        const answer = this.dataset.answer;
                        const originalText = this.innerHTML;
                        
                        // Disable all buttons while checking
                        answerButtons.forEach(b => b.disabled = true);
                        
                        // Show loading state on clicked button
                        this.innerHTML = '<i data-lucide="loader-2" class="w-5 h-5 animate-spin inline"></i> Checking...';
                        if (typeof lucide !== 'undefined') lucide.createIcons();
                        
                        try {
                            // Determine practice_id based on type
                            let practiceIdNum = practiceType === 'single_note' ? 1 : 2;
                            
                            const response = await fetch('/api/practice/check-answer', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                                },
                                body: JSON.stringify({
                                    practice_id: practiceIdNum,
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

                            // Reset button text
                            if (practiceType === 'interval_direction') {
                                const icon = answer === 'ascending' 
                                    ? '<i data-lucide="trending-up" class="w-5 h-5 text-green-500"></i>'
                                    : '<i data-lucide="trending-down" class="w-5 h-5 text-red-500"></i>';
                                this.innerHTML = icon + ' ' + answer.charAt(0).toUpperCase() + answer.slice(1);
                            } else {
                                this.textContent = answer.charAt(0).toUpperCase() + answer.slice(1);
                            }
                            
                            if (data.is_correct) {
                                // Correct answer
                                this.classList.add('correct');
                                this.classList.remove('text-gray-700');
                                this.classList.add('text-green-700');
                                feedbackMessage.textContent = '✓ Correct! Well done!';
                                feedbackMessage.classList.remove('hidden', 'bg-red-100', 'text-red-700');
                                feedbackMessage.classList.add('bg-green-100', 'text-green-700');
                                
                                // Update XP
                                const xpElement = document.getElementById('xpEarned');
                                if (xpElement) {
                                    const currentXP = parseInt(xpElement.textContent) || 0;
                                    xpElement.textContent = currentXP + 10;
                                }
                                
                                // Update correct count
                                const scoreCorrect = document.getElementById('scoreCorrect');
                                if (scoreCorrect) {
                                    const currentCorrect = parseInt(scoreCorrect.textContent) || 0;
                                    scoreCorrect.textContent = currentCorrect + 1;
                                }
                            } else {
                                // Incorrect answer
                                this.classList.add('incorrect');
                                this.classList.remove('text-gray-700');
                                this.classList.add('text-red-700');
                                feedbackMessage.textContent = `✗ Incorrect. The correct answer is ${target.charAt(0).toUpperCase() + target.slice(1)}.`;
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
                            
                            // Update total count
                            const scoreTotal = document.getElementById('scoreTotal');
                            if (scoreTotal) {
                                const currentTotal = parseInt(scoreTotal.textContent) || 0;
                                scoreTotal.textContent = currentTotal + 1;
                            }
                            
                            if (typeof lucide !== 'undefined') lucide.createIcons();
                            
                        } catch (error) {
                            console.error('Error checking answer:', error);
                            this.innerHTML = originalText;
                            feedbackMessage.textContent = 'Error checking answer. Please try again.';
                            feedbackMessage.classList.remove('hidden', 'bg-green-100', 'text-green-700');
                            feedbackMessage.classList.add('bg-red-100', 'text-red-700');
                            
                            // Re-enable buttons on error
                            answerButtons.forEach(b => b.disabled = false);
                            isAnswered = false;
                            
                            if (typeof lucide !== 'undefined') lucide.createIcons();
                        }
                    };
                });
            }
        };

        // Run on initial load
        document.addEventListener('livewire:init', function() {
            window.initPracticeMixed();
            
            // Re-run when practice is updated (Next clicked)
            Livewire.on('practice-updated', () => {
                // Small delay to ensure DOM is updated
                setTimeout(() => {
                    window.initPracticeMixed();
                }, 50);
            });
        });
        
        // Fallback for non-Livewire loads (standard page load)
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof Livewire === 'undefined') {
                 window.initPracticeMixed();
            }
        });
    </script>

</main>

