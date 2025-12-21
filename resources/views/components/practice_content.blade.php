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
            <button id="playButton" class="btn-primary text-white font-semibold py-3 px-8 rounded-lg flex items-center gap-2 mb-3 hover:shadow-lg transition-shadow">
                <i data-lucide="play" class="w-5 h-5"></i>
                Play 
            </button>
            <p class="text-sm text-gray-500">Listen to the interval to start</p>
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