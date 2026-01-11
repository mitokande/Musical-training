<!-- Main Content -->
<main wire:id="practice-mixed-{{ $currentPracticeIndex }}" class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    
    @if($showResults && $coachNotes)
    <!-- AI Coach Results Page -->
    <div class="card overflow-hidden mb-6">
        <!-- Results Header -->
        <div class="bg-gradient-to-r from-purple-600 via-purple-500 to-indigo-500 p-8 text-center">
            <div class="w-20 h-20 mx-auto mb-4 rounded-full bg-white/20 backdrop-blur flex items-center justify-center">
                <i data-lucide="trophy" class="w-10 h-10 text-white"></i>
            </div>
            <h1 class="text-2xl font-bold text-white mb-2">Practice Complete!</h1>
            <p class="text-white/80">{{ $sessionTitle }}</p>
        </div>
        
        <!-- Score Circle -->
        <div class="relative -mt-8 mb-6">
            <div class="w-32 h-32 mx-auto rounded-full bg-white shadow-xl flex items-center justify-center border-4 
                @if($coachNotes['score_percentage'] >= 80) border-green-400
                @elseif($coachNotes['score_percentage'] >= 60) border-yellow-400
                @else border-red-400
                @endif">
                <div class="text-center">
                    <span class="text-4xl font-bold 
                        @if($coachNotes['score_percentage'] >= 80) text-green-600
                        @elseif($coachNotes['score_percentage'] >= 60) text-yellow-600
                        @else text-red-600
                        @endif">{{ round($coachNotes['score_percentage']) }}%</span>
                    <p class="text-xs text-gray-500 mt-1">Score</p>
                </div>
            </div>
        </div>
        
        <!-- Stats Row -->
        <div class="px-8 pb-6">
            <div class="grid grid-cols-3 gap-4 mb-8">
                <div class="text-center p-4 bg-green-50 rounded-xl">
                    <div class="w-10 h-10 mx-auto mb-2 rounded-full bg-green-100 flex items-center justify-center">
                        <i data-lucide="check" class="w-5 h-5 text-green-600"></i>
                    </div>
                    <span class="text-2xl font-bold text-green-600">{{ $correctCount }}</span>
                    <p class="text-xs text-green-600/70">Correct</p>
                </div>
                <div class="text-center p-4 bg-red-50 rounded-xl">
                    <div class="w-10 h-10 mx-auto mb-2 rounded-full bg-red-100 flex items-center justify-center">
                        <i data-lucide="x" class="w-5 h-5 text-red-600"></i>
                    </div>
                    <span class="text-2xl font-bold text-red-600">{{ $totalQuestions - $correctCount }}</span>
                    <p class="text-xs text-red-600/70">Incorrect</p>
                </div>
                <div class="text-center p-4 bg-yellow-50 rounded-xl">
                    <div class="w-10 h-10 mx-auto mb-2 rounded-full bg-yellow-100 flex items-center justify-center">
                        <i data-lucide="sparkles" class="w-5 h-5 text-yellow-600"></i>
                    </div>
                    <span class="text-2xl font-bold text-yellow-600">+{{ $xpEarned }}</span>
                    <p class="text-xs text-yellow-600/70">XP Earned</p>
                </div>
            </div>
            
            <!-- AI Coach Summary -->
            <div class="bg-gradient-to-r from-purple-50 to-indigo-50 rounded-xl p-5 mb-6 border border-purple-100">
                <div class="flex items-start gap-3">
                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-purple-500 to-indigo-500 flex items-center justify-center flex-shrink-0">
                        <i data-lucide="bot" class="w-5 h-5 text-white"></i>
                    </div>
                    <div>
                        <h3 class="font-semibold text-purple-900 mb-1">AI Coach Summary</h3>
                        <p class="text-gray-700 text-sm leading-relaxed">{{ $coachNotes['summary'] }}</p>
                    </div>
                </div>
            </div>
            
            <!-- Strengths -->
            @if(!empty($coachNotes['strengths']))
            <div class="mb-6">
                <h3 class="flex items-center gap-2 font-semibold text-gray-900 mb-3">
                    <div class="w-6 h-6 rounded-full bg-green-100 flex items-center justify-center">
                        <i data-lucide="thumbs-up" class="w-3.5 h-3.5 text-green-600"></i>
                    </div>
                    Your Strengths
                </h3>
                <ul class="space-y-2">
                    @foreach($coachNotes['strengths'] as $strength)
                    <li class="flex items-start gap-2 text-sm text-gray-700 bg-green-50 p-3 rounded-lg">
                        <i data-lucide="check-circle" class="w-4 h-4 text-green-500 mt-0.5 flex-shrink-0"></i>
                        {{ $strength }}
                    </li>
                    @endforeach
                </ul>
            </div>
            @endif
            
            <!-- Weak Areas -->
            @if(!empty($coachNotes['weak_areas']))
            <div class="mb-6">
                <h3 class="flex items-center gap-2 font-semibold text-gray-900 mb-3">
                    <div class="w-6 h-6 rounded-full bg-orange-100 flex items-center justify-center">
                        <i data-lucide="target" class="w-3.5 h-3.5 text-orange-600"></i>
                    </div>
                    Areas to Improve
                </h3>
                <ul class="space-y-2">
                    @foreach($coachNotes['weak_areas'] as $area)
                    <li class="flex items-start gap-2 text-sm text-gray-700 bg-orange-50 p-3 rounded-lg">
                        <i data-lucide="alert-circle" class="w-4 h-4 text-orange-500 mt-0.5 flex-shrink-0"></i>
                        {{ $area }}
                    </li>
                    @endforeach
                </ul>
            </div>
            @endif
            
            <!-- Suggestions -->
            @if(!empty($coachNotes['suggestions']))
            <div class="mb-6">
                <h3 class="flex items-center gap-2 font-semibold text-gray-900 mb-3">
                    <div class="w-6 h-6 rounded-full bg-blue-100 flex items-center justify-center">
                        <i data-lucide="lightbulb" class="w-3.5 h-3.5 text-blue-600"></i>
                    </div>
                    Practice Suggestions
                </h3>
                <ul class="space-y-2">
                    @foreach($coachNotes['suggestions'] as $suggestion)
                    <li class="flex items-start gap-2 text-sm text-gray-700 bg-blue-50 p-3 rounded-lg">
                        <i data-lucide="arrow-right" class="w-4 h-4 text-blue-500 mt-0.5 flex-shrink-0"></i>
                        {{ $suggestion }}
                    </li>
                    @endforeach
                </ul>
            </div>
            @endif
            
            <!-- Encouragement -->
            <div class="bg-gradient-to-r from-amber-50 to-yellow-50 rounded-xl p-5 border border-amber-200">
                <div class="flex items-start gap-3">
                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-amber-400 to-yellow-400 flex items-center justify-center flex-shrink-0">
                        <i data-lucide="heart" class="w-5 h-5 text-white"></i>
                    </div>
                    <div>
                        <h3 class="font-semibold text-amber-900 mb-1">Keep Going!</h3>
                        <p class="text-amber-800 text-sm leading-relaxed">{{ $coachNotes['encouragement'] }}</p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Action Buttons -->
        <div class="px-8 pb-8">
            <div class="flex flex-col sm:flex-row gap-3">
                <a href="/ai-exercises" class="flex-1 btn-primary text-white font-semibold py-3 px-6 rounded-lg flex items-center justify-center gap-2 hover:shadow-lg transition-shadow">
                    <i data-lucide="sparkles" class="w-5 h-5"></i>
                    New AI Session
                </a>
                <a href="/learn" class="flex-1 font-semibold py-3 px-6 rounded-lg flex items-center justify-center gap-2 border-2 border-gray-200 text-gray-700 hover:bg-gray-50 transition-colors">
                    <i data-lucide="book-open" class="w-5 h-5"></i>
                    Learning Path
                </a>
                <a href="/dashboard" class="flex-1 font-semibold py-3 px-6 rounded-lg flex items-center justify-center gap-2 border-2 border-gray-200 text-gray-700 hover:bg-gray-50 transition-colors">
                    <i data-lucide="home" class="w-5 h-5"></i>
                    Dashboard
                </a>
            </div>
        </div>
    </div>
    
    <script>
        // Re-initialize Lucide icons for results page
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        });
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    </script>
    @else
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
                            @elseif($currentPractice['type'] === 'interval_comparison')
                                Interval Comparison
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
                @elseif($type === 'interval_comparison')
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-700">
                        <i data-lucide="git-compare" class="w-3 h-3"></i>
                        Interval Comparison
                    </span>
                @endif
            </div>

            <!-- Note Visual -->
            @if($type === 'interval_comparison')
                @php
                    $intervalANotes = explode(',', $practice['interval_a']);
                    $intervalBNotes = explode(',', $practice['interval_b']);
                @endphp
                <div class="grid grid-cols-2 gap-4 mb-8">
                    <!-- Interval A -->
                    <div class="bg-gray-50 border-2 border-gray-200 rounded-xl p-4">
                        <h3 class="text-center font-semibold text-gray-700 mb-2">Interval A</h3>
                        <div id="outputA" style="width: 100%; height: 150px;" 
                             data-notes="{{ strtolower(trim($intervalANotes[0])) . '/' . $practice['octave'] . ',' . strtolower(trim($intervalANotes[1])) . '/' . $practice['octave'] }}">
                        </div>
                    </div>
                    <!-- Interval B -->
                    <div class="bg-gray-50 border-2 border-gray-200 rounded-xl p-4">
                        <h3 class="text-center font-semibold text-gray-700 mb-2">Interval B</h3>
                        <div id="outputB" style="width: 100%; height: 150px;" 
                             data-notes="{{ strtolower(trim($intervalBNotes[0])) . '/' . $practice['octave'] . ',' . strtolower(trim($intervalBNotes[1])) . '/' . $practice['octave'] }}">
                        </div>
                    </div>
                </div>
            @else
                <div id="noteVisualPlaceholder" class="w-full h-32 bg-gray-50 border-2 border-dashed border-gray-200 rounded-xl flex items-center justify-center mb-8">
                    <div>
                        <div id="output" style="width: 400px; height: 200px;" 
                             data-notes="@if($type === 'single_note'){{ strtolower($practice['target']) . '/' . $practice['octave'] }}@elseif($type === 'interval_direction'){{ strtolower($practice['note1']) . '/' . $practice['octave'] . ',' . strtolower($practice['note2']) . '/' . $practice['octave'] }}@endif"
                             data-type="{{ $type }}">
                        </div>
                    </div>
                </div>
            @endif

            <!-- Play Button Section -->
            <div class="card p-6 mb-8">
                <div class="flex flex-col items-center">
                    <div class="flex gap-3">
                        <button 
                            id="playButton" 
                            class="btn-primary text-white font-semibold py-3 px-8 rounded-lg flex items-center gap-2 mb-3 hover:shadow-lg transition-shadow"
                            data-note="@if($type === 'single_note'){{ ucfirst(strtolower($practice['target'])) . $practice['octave'] }}@elseif($type === 'interval_direction'){{ ucfirst(strtolower($practice['note1'])) . $practice['octave'] . ',' . ucfirst(strtolower($practice['note2'])) . $practice['octave'] }}@endif"
                            @if($type === 'interval_comparison')
                                data-interval-a="{{ strtoupper(trim($intervalANotes[0])) . $practice['octave'] . ',' . strtoupper(trim($intervalANotes[1])) . $practice['octave'] }}"
                                data-interval-b="{{ strtoupper(trim($intervalBNotes[0])) . $practice['octave'] . ',' . strtoupper(trim($intervalBNotes[1])) . $practice['octave'] }}"
                            @endif
                            data-type="{{ $type }}"
                        >
                            <i data-lucide="play" class="w-5 h-5"></i>
                            @if($type === 'interval_comparison')
                                Play Both Intervals
                            @else
                                Play
                            @endif
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
                            <button 
                                wire:click="generateCoachNotes"
                                wire:loading.attr="disabled"
                                id="finishPracticeBtn"
                                class="font-semibold py-3 px-8 hidden rounded-lg flex items-center gap-2 mb-3 hover:shadow-lg transition-shadow disabled:opacity-70 disabled:cursor-not-allowed
                                    bg-green-100 text-green-700 border border-green-300 hover:bg-green-200 hover:text-green-800 hover:border-green-400"
                                style="border-width: 2px;"
                            >
                                <span wire:loading.remove wire:target="generateCoachNotes">
                                    <i data-lucide="check" class="w-5 h-5"></i>
                                </span>
                                <svg wire:loading wire:target="generateCoachNotes" class="animate-spin w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span wire:loading.remove wire:target="generateCoachNotes">Finish</span>
                                <span wire:loading wire:target="generateCoachNotes">Generating AI Feedback...</span>
                            </button>
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
                     
                     <button wire:click.prevent="saveAnswerPractice('{{ trim($option) }}', '{{ $practice['target'] }}')" class="answer-btn card p-6 text-center font-semibold text-gray-700 hover:shadow-md transition-all" data-answer="{{ trim($option) }}">
                            {{ trim($option) }}
                        </button>
                    @endforeach
                </div>
            @elseif($type === 'interval_direction')
                <div id="answerOptions" class="grid grid-cols-2 gap-4" 
                     data-target="{{ $practice['direction'] }}"
                     data-practice-id="{{ $practice['id'] }}"
                     data-type="{{ $type }}">
                    <button wire:click.prevent="saveAnswerPractice('ascending', '{{ $practice['direction'] }}')" class="answer-btn card p-6 text-center font-semibold text-gray-700 hover:shadow-md transition-all flex items-center justify-center gap-2" data-answer="ascending">
                        <i data-lucide="trending-up" class="w-5 h-5 text-green-500"></i>
                        Ascending
                    </button>
                    <button class="answer-btn card p-6 text-center font-semibold text-gray-700 hover:shadow-md transition-all flex items-center justify-center gap-2" data-answer="descending">
                        <i data-lucide="trending-down" class="w-5 h-5 text-red-500"></i>
                        Descending
                    </button>
                </div>
            @elseif($type === 'interval_comparison')
                <div id="answerOptions" class="grid grid-cols-2 gap-4" 
                     data-target="{{ $practice['target'] }}"
                     data-practice-id="{{ $practice['id'] }}"
                     data-type="{{ $type }}">
                    <button wire:click.prevent="saveAnswerPractice('a', '{{ $practice['target'] }}')" class="answer-btn card p-6 text-center font-semibold text-gray-700 hover:shadow-md transition-all flex flex-col items-center justify-center gap-1" data-answer="a">
                        <span class="text-2xl font-bold text-green-600">A</span>
                        <span class="text-sm text-gray-500">Interval A is larger</span>
                    </button>
                    <button wire:click.prevent="saveAnswerPractice('b', '{{ $practice['target'] }}')" class="answer-btn card p-6 text-center font-semibold text-gray-700 hover:shadow-md transition-all flex flex-col items-center justify-center gap-1" data-answer="b">
                        <span class="text-2xl font-bold text-blue-600">B</span>
                        <span class="text-sm text-gray-500">Interval B is larger</span>
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
        
                // Check if this is interval comparison (has outputA and outputB)
                const divA = document.getElementById("outputA");
                const divB = document.getElementById("outputB");
                
                if (divA && divB) {
                    // Interval Comparison: Render two staves
                    [divA, divB].forEach(div => {
                        div.innerHTML = '';
                        const renderer = new Renderer(div, Renderer.Backends.SVG);
                        renderer.resize(180, 150);
                        const context = renderer.getContext();
                        const stave = new Stave(10, 20, 160);
                        stave.addClef("treble");
                        stave.setContext(context).draw();
                        
                        const notesFromParams = div.dataset.notes;
                        if (notesFromParams) {
                            const notesParsed = notesFromParams.split(',');
                            const notes = notesParsed.map(note => new StaveNote({ keys: [note], duration: "h" }));
                            const voice = new Voice({ numBeats: 2, beatValue: 2 });
                            voice.addTickables(notes);
                            new Formatter().joinVoices([voice]).format([voice], 100);
                            voice.draw(context, stave);
                        }
                    });
                } else {
                    // Single Note or Interval Direction: Single stave
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
            }

            // Initialize Interactions
            const playButton = document.getElementById('playButton');
            const playStatus = document.getElementById('playStatus');
            const nextButton = document.getElementById('nextPracticeBtn');
            const finishPracticeBtn = document.getElementById('finishPracticeBtn');
            const answerOptions = document.getElementById('answerOptions');
            const answerButtons = document.querySelectorAll('.answer-btn');
            const feedbackMessage = document.getElementById('feedbackMessage');
            const totalQuestions = {{ $totalQuestions }};
            const currentPracticeIndex = {{ $currentPracticeIndex }};
            
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
                    
                    const handleError = () => {
                        playButton.disabled = false;
                        playButton.innerHTML = '<i data-lucide="play" class="w-5 h-5"></i> Retry';
                        playStatus.textContent = 'Error loading audio. Try again.';
                        if (typeof lucide !== 'undefined') lucide.createIcons();
                    };
                    
                    // Check if this is interval comparison (has data-interval-a and data-interval-b)
                    if (practiceType === 'interval_comparison') {
                        // Interval Comparison: 4 notes with 1-second delay between intervals
                        const intervalA = this.dataset.intervalA.split(',');
                        const intervalB = this.dataset.intervalB.split(',');
                        
                        const audioUrls = [
                            `https://mithatck.com/music/api/note.php?note=${intervalA[0].replace(/#/g, '%23')}&duration=1`,
                            `https://mithatck.com/music/api/note.php?note=${intervalA[1].replace(/#/g, '%23')}&duration=1`,
                            `https://mithatck.com/music/api/note.php?note=${intervalB[0].replace(/#/g, '%23')}&duration=1`,
                            `https://mithatck.com/music/api/note.php?note=${intervalB[1].replace(/#/g, '%23')}&duration=1`,
                        ];
                        
                        const audios = audioUrls.map(url => new Audio(url));
                        let currentIndex = 0;
                        
                        const playNext = () => {
                            if (currentIndex >= audios.length) {
                                playButton.disabled = false;
                                playButton.innerHTML = '<i data-lucide="play" class="w-5 h-5"></i> Play Again';
                                playStatus.textContent = 'Click to play again';
                                if (typeof lucide !== 'undefined') lucide.createIcons();
                                return;
                            }
                            
                            currentAudio = audios[currentIndex];
                            
                            if (currentIndex < 2) {
                                playStatus.textContent = 'Playing Interval A...';
                            } else {
                                playStatus.textContent = 'Playing Interval B...';
                            }
                            
                            currentAudio.addEventListener('ended', function() {
                                currentIndex++;
                                // Add 1 second delay between intervals (after note 2, before note 3)
                                if (currentIndex === 2) {
                                    playStatus.textContent = 'Pause...';
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
                    } else {
                        // Single note or Interval Direction playback
                        const notes = this.dataset.note;
                        const notesParsed = notes.split(',');
                        
                        if (notesParsed.length === 1) {
                            console.log('single note playback', notesParsed);
                            // Single note playback
                            const sanitizedNotesParsed = notesParsed.map(n => n.replace(/#/g, '%23'));
                            const audioUrl = `https://mithatck.com/music/api/note.php?note=${sanitizedNotesParsed[0]}&duration=1`;
                            const audio = new Audio(audioUrl);
                            currentAudio = audio;
                            
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
                            const data = await @this.call('answerPractice', practiceType, answer, target);
                            console.log(data);
                            isAnswered = true;
                            
                            // Toggle buttons: Hide Play, Show Next
                            if (playButton) playButton.classList.add('hidden');
                            if (playStatus) playStatus.classList.add('hidden');
                            if (nextButton) nextButton.classList.remove('hidden');
                            if (finishPracticeBtn) finishPracticeBtn.classList.remove('hidden');

                            // Reset button text
                            if (practiceType === 'interval_direction') {
                                const icon = answer === 'ascending' 
                                    ? '<i data-lucide="trending-up" class="w-5 h-5 text-green-500"></i>'
                                    : '<i data-lucide="trending-down" class="w-5 h-5 text-red-500"></i>';
                                this.innerHTML = icon + ' ' + answer.charAt(0).toUpperCase() + answer.slice(1);
                            } else if (practiceType === 'interval_comparison') {
                                const label = answer.toUpperCase();
                                const desc = answer === 'a' ? 'Interval A is larger' : 'Interval B is larger';
                                this.innerHTML = `<span class="text-2xl font-bold">${label}</span><span class="text-sm text-gray-500">${desc}</span>`;
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
                                const correctDisplay = practiceType === 'interval_comparison' 
                                    ? target.toUpperCase()
                                    : target.charAt(0).toUpperCase() + target.slice(1);
                                feedbackMessage.textContent = `✗ Incorrect. The correct answer is ${correctDisplay}.`;
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
    @endif

</main>

