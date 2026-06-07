<!-- Main Content -->
<main wire:id="practice-mixed-{{ $currentPracticeIndex }}" class="max-w-2xl mx-auto px-3 sm:px-4 py-4 sm:py-6">
    
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
    <div wire:key="practice-{{ $currentPracticeIndex }}" class="overflow-hidden rounded-2xl shadow-lg border border-purple-100" style="background:white;">

        <!-- ── Header ── -->
        <div class="px-4 sm:px-6 pt-5 pb-5" style="background: linear-gradient(135deg, #1a5f78 0%, #2a7898 55%, #3490b0 100%);">
            <div class="flex items-start justify-between">

                <!-- Back button -->
                <a href="/ai-exercises"
                   class="flex-shrink-0 w-10 h-10 flex items-center justify-center rounded-xl bg-white/10 hover:bg-white/20 text-white transition-all hover:scale-105 active:scale-95 mt-0.5">
                    <i data-lucide="arrow-left" class="w-5 h-5"></i>
                </a>

                <!-- Title + task description -->
                <div class="flex-1 text-center px-2">
                    <h1 class="text-base sm:text-lg font-bold text-white leading-tight">{{ $sessionTitle }}</h1>
                    <p class="text-white/85 text-xs sm:text-sm mt-1 font-medium">
                        @php $hType = $currentPractice['type'] ?? ''; $hData = $currentPractice['data'] ?? []; @endphp
                        @if($hType === 'interval_construction')
                            Build a <strong>{{ $hData['interval'] ?? '' }}</strong> above <strong>{{ strtoupper($hData['note1'] ?? '') }}</strong>
                        @elseif($hType === 'chord')
                            Identify the chord type for <strong>{{ $hData['root_note'] ?? '' }}</strong>
                        @elseif($hType === 'scale')
                            Identify the scale starting on <strong>{{ $hData['root_note'] ?? '' }}</strong>
                        @elseif($hType === 'interval_comparison')
                            Which interval is larger — A or B?
                        @elseif($hType === 'interval_direction')
                            Ascending or descending?
                        @elseif($hType === 'harmonic_interval')
                            Identify the harmonic interval
                        @elseif($hType === 'melodic_interval')
                            Identify the melodic interval
                        @elseif($hType === 'single_note')
                            Which note did you hear?
                        @elseif($hType === 'rhythm')
                            Rhythm Dictation — listen, then build the rhythm you heard
                        @elseif($hType === 'melodic_dictation')
                            Identify the melodic sequence
                        @else
                            Listen and select the correct answer
                        @endif
                    </p>
                </div>

                <!-- Right: counter + XP only -->
                <div class="flex-shrink-0 flex flex-col items-end gap-1.5">
                    <span class="inline-flex items-center gap-1 rounded-xl bg-white/10 px-3 py-1.5 text-sm font-semibold text-white ring-1 ring-white/20 backdrop-blur">
                        <span id="currentQuestion">{{ $currentPracticeIndex + 1 }}</span>
                        <span class="text-white/60 font-medium">/</span>
                        <span id="totalQuestions">{{ $totalQuestions }}</span>
                    </span>
                    <span id="scoreBox"
                          class="inline-flex items-center gap-1 rounded-xl bg-white/10 px-3 py-1.5 text-xs font-semibold text-white ring-1 ring-white/20 backdrop-blur whitespace-nowrap">
                        <i data-lucide="sparkles" class="w-3 h-3 text-yellow-300"></i>
                        +<span id="xpEarned">{{ $xpEarned }}</span>&nbsp;XP
                    </span>
                </div>
            </div>

            <!-- Progress bar -->
            <div class="mt-4 h-1.5 rounded-full bg-white/20 overflow-hidden">
                <div class="h-full rounded-full bg-white/70 transition-all duration-500"
                     style="width: {{ $totalQuestions > 0 ? round(($currentPracticeIndex / $totalQuestions) * 100) : 0 }}%"></div>
            </div>
        </div>
        <!-- ── /Header ── -->

        <!-- ── Content ── -->
        <div class="p-4 sm:p-6 flex flex-col gap-4">
            @php
                $practice = $currentPractice['data'] ?? [];
                $type = $currentPractice['type'] ?? 'unknown';
                // Display helper: render accidentals as proper music symbols (C# -> C♯, Db -> D♭).
                // Only the displayed label is symbolized; data-answer stays ASCII for matching.
                $noteSymbol = fn ($n) => str_replace(['#', 'b'], ['♯', '♭'], (string) $n);

                // Clef helper: use bass clef when the root note is below G3
                // (3rd octave and lower than G), otherwise treble.
                $clefFor = function ($note, $octave) {
                    $base = ['C' => 0, 'D' => 2, 'E' => 4, 'F' => 5, 'G' => 7, 'A' => 9, 'B' => 11];
                    $letter = strtoupper(substr((string) $note, 0, 1));
                    $rest = substr((string) $note, 1);
                    $acc = str_contains($rest, '#') ? 1 : (str_contains($rest, 'b') ? -1 : 0);
                    $pitch = ((int) $octave) * 12 + (($base[$letter] ?? 0) + $acc);
                    return $pitch < (3 * 12 + 7) ? 'bass' : 'treble'; // below G3 -> bass
                };
                $clefRoot = match ($type) {
                    'single_note'         => [$practice['target'] ?? 'C', $practice['octave'] ?? 4],
                    'chord', 'scale'      => [$practice['root_note'] ?? 'C', $practice['octave'] ?? 4],
                    'interval_comparison' => [trim(explode(',', $practice['interval_a'] ?? 'C,E')[0]), $practice['octave'] ?? 4],
                    default               => [$practice['note1'] ?? 'C', $practice['octave'] ?? 4],
                };
                $staffClef = $clefFor($clefRoot[0], $clefRoot[1]);
            @endphp
            @if(!$currentPractice)
                <div class="text-center py-8 text-gray-500">No practice questions available.</div>
            @else

            <!-- Note Visual -->
            @php $note2Oct = $practice['note2_octave'] ?? $practice['octave'] ?? '4'; @endphp
            @if($type === 'interval_comparison')
                @php
                    $intervalANotes = explode(',', $practice['interval_a'] ?? ',');
                    $intervalBNotes = explode(',', $practice['interval_b'] ?? ',');
                @endphp
                <div id="noteDisplayContainer"
                     class="w-full bg-gray-50 border border-gray-200 rounded-xl overflow-hidden hidden"
                     style="height:160px;">
                    <div id="output" style="width:100%; height:160px; display:flex; justify-content:center; align-items:center;"
                         data-notes="{{ strtolower(trim($intervalANotes[0] ?? 'c')) . '/' . ($practice['octave'] ?? '4') . ',' . strtolower(trim($intervalANotes[1] ?? 'e')) . '/' . ($practice['octave'] ?? '4') . ',' . strtolower(trim($intervalBNotes[0] ?? 'c')) . '/' . ($practice['octave'] ?? '4') . ',' . strtolower(trim($intervalBNotes[1] ?? 'g')) . '/' . ($practice['octave'] ?? '4') }}"
                         data-clef="{{ $staffClef }}"
                         data-type="{{ $type }}">
                    </div>
                </div>
            @elseif($type === 'interval_construction')
                <div class="w-full bg-gray-50 border border-gray-200 rounded-xl overflow-hidden"
                     style="height:160px;">
                    <div id="output" style="width:100%; height:160px; display:flex; justify-content:center; align-items:center;"
                         data-notes="{{ strtolower($practice['note1'] ?? 'c') . '/' . ($practice['octave'] ?? '4') . ',' . strtolower($practice['note2'] ?? 'e') . '/' . $note2Oct }}"
                         data-clef="{{ $staffClef }}"
                         data-type="{{ $type }}">
                    </div>
                </div>
            @elseif($type === 'chord')
                @php
                    // Convert note_array ("C4","E4","G#4"...) to VexFlow keys ("c/4","e/4","g#/4").
                    $chordKeys = collect($practice['note_array'] ?? [])->map(function ($n) {
                        if (preg_match('/^([A-Ga-g](?:#{1,2}|b{1,2})?)(\d+)$/', $n, $m)) {
                            return strtolower($m[1]) . '/' . $m[2];
                        }
                        return strtolower($n);
                    })->implode(',');
                    $chordRootKey = strtolower($practice['root_note'] ?? 'c') . '/' . ($practice['octave'] ?? '4');
                @endphp
                <div class="w-full bg-gray-50 border border-gray-200 rounded-xl overflow-hidden"
                     style="height:160px;">
                    <div id="output" style="width:100%; height:160px; display:flex; justify-content:center; align-items:center;"
                         data-notes="{{ $chordKeys }}"
                         data-root="{{ $chordRootKey }}"
                         data-clef="{{ $staffClef }}"
                         data-type="{{ $type }}">
                    </div>
                </div>
            @elseif($type === 'scale')
                @php
                    // Convert note_array ("C4","D4","E4"...) to VexFlow keys ("c/4","d/4","e/4").
                    $scaleKeys = collect($practice['note_array'] ?? [])->map(function ($n) {
                        if (preg_match('/^([A-Ga-g](?:#{1,2}|b{1,2})?)(\d+)$/', $n, $m)) {
                            return strtolower($m[1]) . '/' . $m[2];
                        }
                        return strtolower($n);
                    })->implode(',');
                    $scaleRootKey = strtolower($practice['root_note'] ?? 'c') . '/' . ($practice['octave'] ?? '4');
                @endphp
                <div class="w-full bg-gray-50 border border-gray-200 rounded-xl overflow-hidden"
                     style="height:160px;">
                    <div id="output" style="width:100%; height:160px; display:flex; justify-content:center; align-items:center;"
                         data-notes="{{ $scaleKeys }}"
                         data-root="{{ $scaleRootKey }}"
                         data-clef="{{ $staffClef }}"
                         data-type="{{ $type }}">
                    </div>
                </div>
            @elseif($type === 'rhythm')
                {{-- Built rhythm on a single-line staff (top of the exercise) --}}
                <div id="rhythmTable"
                     class="min-h-[110px] w-full bg-white border-2 border-dashed border-gray-300 rounded-xl p-2 overflow-x-auto flex items-center justify-center"></div>
                {{-- Correct-answer reveal (shown only after an incorrect Check) --}}
                <div id="rhythmReveal" class="hidden mt-3">
                    <p class="text-xs font-semibold text-gray-500 text-center mb-1">Correct rhythm</p>
                    <div id="rhythmRevealRow"
                         class="w-full bg-green-50 border border-green-200 rounded-xl p-2 overflow-x-auto flex items-center justify-center"></div>
                </div>
            @elseif($type === 'melodic_dictation')
                @php
                    $mdNotes = $practice['notes'] ?? [];
                    if (is_string($mdNotes)) $mdNotes = json_decode($mdNotes, true) ?? [];
                @endphp
                <div class="bg-indigo-50 border border-indigo-200 rounded-xl p-4 flex items-center gap-3">
                    <i data-lucide="info" class="w-4 h-4 text-indigo-500 flex-shrink-0"></i>
                    <div class="text-xs text-indigo-700 leading-relaxed">
                        <strong>Key:</strong> {{ $practice['key_signature'] ?? 'C' }} &nbsp;·&nbsp;
                        <strong>Clef:</strong> {{ ucfirst($practice['clef'] ?? 'treble') }} &nbsp;·&nbsp;
                        <strong>Tempo:</strong> {{ $practice['tempo'] ?? 60 }} BPM &nbsp;·&nbsp;
                        <strong>Notes:</strong> {{ count($mdNotes) }}
                    </div>
                </div>
            @else
                @php
                    $visualNotes = match($type) {
                        'single_note'      => strtolower($practice['target'] ?? 'c') . '/' . ($practice['octave'] ?? '4'),
                        'interval_direction','harmonic_interval','melodic_interval'
                                           => strtolower($practice['note1'] ?? 'c') . '/' . ($practice['octave'] ?? '4') . ',' . strtolower($practice['note2'] ?? 'e') . '/' . $note2Oct,
                        default            => '',
                    };
                @endphp
                <div class="w-full bg-gray-50 border border-gray-200 rounded-xl overflow-hidden"
                     style="height:160px;">
                    <div id="output" style="width:100%; height:160px; display:flex; justify-content:center; align-items:center;"
                         data-notes="{{ $visualNotes }}"
                         data-clef="{{ $staffClef }}"
                         data-type="{{ $type }}">
                    </div>
                </div>
            @endif


            <!-- Play Button Section -->
            @php
                // Ensure $note2Oct is always defined, regardless of which branch rendered the staff above.
                $note2Oct = $note2Oct ?? $practice['note2_octave'] ?? $practice['octave'] ?? '4';

                // Only compute direction for interval types to avoid MusicTheoryService errors on missing fields
                $intervalTypes = ['interval_direction', 'interval_comparison', 'melodic_interval', 'harmonic_interval'];
                $directionTarget = in_array($type, $intervalTypes)
                    ? app(\App\Services\MusicTheoryService::class)->getDirection(
                        $practice['note1'] ?? 'C', (int)($practice['octave'] ?? 4),
                        $practice['note2'] ?? 'E', (int)$note2Oct
                    )
                    : '';

                // Compute note_array for chord/scale (may be pre-set in data, otherwise empty)
                $noteArrayStr = implode(',', $practice['note_array'] ?? []);

                // Compute rhythm/dictation note strings
                $rhythmNoteValues = $practice['note_values'] ?? [];
                if (is_string($rhythmNoteValues)) $rhythmNoteValues = json_decode($rhythmNoteValues, true) ?? [];
                $rhythmNotesStr = implode(',', $rhythmNoteValues);

                $dictationNotes = $practice['notes'] ?? [];
                if (is_string($dictationNotes)) $dictationNotes = json_decode($dictationNotes, true) ?? [];
                $dictationNotesStr = implode(',', $dictationNotes);

                $playNote = match($type) {
                    'single_note'        => ucfirst(strtolower($practice['target'] ?? 'C')) . ($practice['octave'] ?? '4'),
                    'interval_direction' => strtoupper($practice['note1'] ?? 'C') . ($practice['octave'] ?? '4') . ',' . strtoupper($practice['note2'] ?? 'E') . $note2Oct,
                    'harmonic_interval', 'melodic_interval' => ucfirst(strtolower($practice['note1'] ?? 'C')) . ($practice['octave'] ?? '4') . ',' . ucfirst(strtolower($practice['note2'] ?? 'E')) . $note2Oct,
                    'interval_construction' => ucfirst(strtolower($practice['note1'] ?? 'C')) . ($practice['octave'] ?? '4'),
                    'chord'              => $noteArrayStr,
                    'scale'              => $noteArrayStr,
                    'rhythm'             => $rhythmNotesStr,
                    'melodic_dictation'  => $dictationNotesStr,
                    default              => '',
                };
                $playMode = match($type) {
                    'harmonic_interval'  => 'harmonic',
                    'interval_comparison'=> 'comparison',
                    'chord'              => 'chord',
                    'scale'              => 'scale',
                    'rhythm'             => 'rhythm',
                    'melodic_dictation'  => 'melodic_dictation',
                    default              => 'melodic',
                };
            @endphp
            <div class="bg-gray-50 border border-gray-200 rounded-xl p-4 flex flex-col items-center gap-2">
                <div class="flex items-center gap-3">
                        <button
                            id="playButton"
                            class="btn-primary text-white font-semibold py-2.5 px-6 rounded-lg flex items-center gap-2 hover:shadow-lg transition-shadow text-sm sm:text-base"
                            data-note="{{ $playNote }}"
                            @if($type === 'interval_comparison')
                                data-interval-a="{{ strtoupper(trim($intervalANotes[0] ?? 'C')) . ($practice['octave'] ?? '4') . ',' . strtoupper(trim($intervalANotes[1] ?? 'E')) . ($practice['octave'] ?? '4') }}"
                                data-interval-b="{{ strtoupper(trim($intervalBNotes[0] ?? 'C')) . ($practice['octave'] ?? '4') . ',' . strtoupper(trim($intervalBNotes[1] ?? 'G')) . ($practice['octave'] ?? '4') }}"
                            @elseif($type === 'chord')
                                data-voicing="{{ $practice['voicing'] ?? 'block' }}"
                            @elseif($type === 'rhythm')
                                data-tempo="{{ $practice['tempo'] ?? 80 }}"
                                data-time-sig="{{ $practice['time_signature'] ?? '4/4' }}"
                            @elseif($type === 'melodic_dictation')
                                data-tempo="{{ $practice['tempo'] ?? 60 }}"
                            @endif
                            data-type="{{ $type }}"
                            data-play-mode="{{ $playMode }}"
                        >
                            <i data-lucide="play" class="w-5 h-5"></i>
                            @if($type === 'interval_comparison')
                                Play Both Intervals
                            @elseif($type === 'harmonic_interval')
                                Play Interval
                            @elseif($type === 'interval_construction')
                                Play Starting Note
                            @elseif($type === 'chord')
                                Play Chord
                            @elseif($type === 'scale')
                                Play Scale
                            @elseif($type === 'rhythm')
                                Play Rhythm
                            @elseif($type === 'melodic_dictation')
                                Play Melody
                            @else
                                Play
                            @endif
                        </button>
                        
                        @if ($currentPracticeIndex < ($totalQuestions - 1))
                            <button
                                id="nextPracticeBtn"
                                wire:click="getNextPractice"
                                class="hidden font-semibold py-2.5 px-6 rounded-lg flex items-center gap-2 hover:shadow-lg transition-shadow text-sm sm:text-base bg-blue-100 text-blue-700 border-2 border-blue-300 hover:bg-blue-200"
                            >
                                <i data-lucide="arrow-right" class="w-4 h-4"></i>
                                Next
                            </button>
                        @else
                            <button
                                wire:click="generateCoachNotes"
                                wire:loading.attr="disabled"
                                id="finishPracticeBtn"
                                class="hidden font-semibold py-2.5 px-6 rounded-lg flex items-center gap-2 hover:shadow-lg transition-shadow text-sm sm:text-base bg-green-100 text-green-700 border-2 border-green-300 hover:bg-green-200 disabled:opacity-70"
                            >
                                <span wire:loading.remove wire:target="generateCoachNotes">
                                    <i data-lucide="check" class="w-4 h-4"></i>
                                </span>
                                <svg wire:loading wire:target="generateCoachNotes" class="animate-spin w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span wire:loading.remove wire:target="generateCoachNotes">Finish</span>
                                <span wire:loading wire:target="generateCoachNotes">Generating AI Feedback...</span>
                            </button>
                        @endif
                </div>
                <p id="playStatus" class="text-xs text-gray-400">Listen to the note, then select your answer</p>
            </div>

            <!-- Answer Options - Dynamic based on type -->
            @if($type === 'single_note')
                @php
                    $snTarget    = $practice['target'] ?? '';
                    $snAll       = ['C','D','E','F','G','A','B'];
                    $snRaw       = array_map('trim', explode(',', $practice['other_options'] ?? ''));
                    $snFiltered  = array_values(array_filter($snRaw, fn($n) => $n !== ''));
                    // Keep only up to 4; if short, pad with notes not already chosen
                    if (count($snFiltered) < 4) {
                        $extra = array_values(array_filter($snAll, fn($n) => !in_array($n, $snFiltered)));
                        shuffle($extra);
                        $snFiltered = array_merge($snFiltered, array_slice($extra, 0, 4 - count($snFiltered)));
                    }
                    $snOptions = array_slice($snFiltered, 0, 4);
                @endphp
                <div id="answerOptions" class="grid grid-cols-2 gap-4"
                     data-target="{{ $snTarget }}"
                     data-practice-id="{{ $practice['id'] ?? 0 }}"
                     data-type="{{ $type }}">
                    @foreach($snOptions as $option)
                        <button class="answer-btn rounded-xl p-4 text-center font-semibold text-gray-700" data-answer="{{ trim($option) }}">
                            {{ $noteSymbol(trim($option)) }}
                        </button>
                    @endforeach
                </div>
            @elseif($type === 'interval_direction')
                <div id="answerOptions" class="grid grid-cols-2 gap-4"
                     data-target="{{ $directionTarget }}"
                     data-practice-id="{{ $practice['id'] ?? 0 }}"
                     data-type="{{ $type }}">
                    <button class="answer-btn rounded-xl p-4 text-center font-semibold text-gray-700 flex items-center justify-center gap-2" data-answer="ascending">
                        <i data-lucide="trending-up" class="w-5 h-5 text-green-500"></i>
                        Ascending
                    </button>
                    <button class="answer-btn rounded-xl p-4 text-center font-semibold text-gray-700 flex items-center justify-center gap-2" data-answer="descending">
                        <i data-lucide="trending-down" class="w-5 h-5 text-red-500"></i>
                        Descending
                    </button>
                </div>
            @elseif($type === 'interval_comparison')
                <div id="answerOptions" class="grid grid-cols-2 gap-4"
                     data-target="{{ $practice['target'] ?? '' }}"
                     data-practice-id="{{ $practice['id'] ?? 0 }}"
                     data-type="{{ $type }}">
                    <button class="answer-btn rounded-xl p-4 text-center font-semibold text-gray-700 flex flex-col items-center justify-center gap-1" data-answer="a">
                        <span class="text-2xl font-bold text-green-600">A</span>
                        <span class="text-sm text-gray-500">Interval A is larger</span>
                    </button>
                    <button class="answer-btn rounded-xl p-4 text-center font-semibold text-gray-700 flex flex-col items-center justify-center gap-1" data-answer="b">
                        <span class="text-2xl font-bold text-blue-600">B</span>
                        <span class="text-sm text-gray-500">Interval B is larger</span>
                    </button>
                </div>
            @elseif($type === 'harmonic_interval' || $type === 'melodic_interval')
                @php
                    $intervalTarget = strtolower($practice['interval'] ?? '');
                    // Prefer generator-supplied options (count + distractor difficulty
                    // driven by the exercise's configured difficulty); fall back to a
                    // fixed 4-option pool for legacy / non-AI sessions.
                    $providedOptions = $practice['options'] ?? null;
                    if (is_string($providedOptions)) $providedOptions = json_decode($providedOptions, true);
                    if (!empty($providedOptions) && is_array($providedOptions)) {
                        $intervalOptions4 = array_values($providedOptions);
                    } else {
                        $allIntervals    = ['Minor 2nd','Major 2nd','Minor 3rd','Major 3rd','Perfect 4th','Tritone','Perfect 5th','Minor 6th','Major 6th','Minor 7th','Major 7th','Perfect Octave'];
                        $correctInterval = ucwords($intervalTarget);
                        $distractors     = array_values(array_filter($allIntervals, fn($i) => strtolower($i) !== strtolower($correctInterval)));
                        shuffle($distractors);
                        $intervalOptions4 = array_merge([$correctInterval], array_slice($distractors, 0, 3));
                        shuffle($intervalOptions4);
                    }
                    $intervalGridCols = count($intervalOptions4) > 4 ? 'grid-cols-3' : 'grid-cols-2';
                @endphp
                <div id="answerOptions" class="grid {{ $intervalGridCols }} gap-3"
                     data-target="{{ $intervalTarget }}"
                     data-practice-id="{{ $practice['id'] ?? 0 }}"
                     data-type="{{ $type }}">
                    @foreach($intervalOptions4 as $intervalOption)
                        <button class="answer-btn rounded-xl p-4 text-center font-semibold text-gray-700 text-sm"
                                data-answer="{{ strtolower($intervalOption) }}">
                            {{ $intervalOption }}
                        </button>
                    @endforeach
                </div>
            @elseif($type === 'interval_construction')
                @php
                    // Display note names with diatonic spelling preserved ("Eb", "F#"),
                    // not forced-uppercase ("EB"). Answer matching is case-insensitive.
                    $noteDisplay = fn($n) => $n === '' ? '' : strtoupper(substr($n, 0, 1)) . substr($n, 1);
                    $constructionTarget = $noteDisplay($practice['note2'] ?? '');
                    $providedOptions = $practice['options'] ?? null;
                    if (is_string($providedOptions)) $providedOptions = json_decode($providedOptions, true);
                    if (!empty($providedOptions) && is_array($providedOptions)) {
                        $noteOptions4 = array_values(array_map($noteDisplay, $providedOptions));
                    } else {
                        $allNotes        = ['C','C#','D','D#','E','F','F#','G','G#','A','A#','B'];
                        $noteDistractors = array_values(array_filter($allNotes, fn($n) => strtolower($n) !== strtolower($constructionTarget)));
                        shuffle($noteDistractors);
                        $noteOptions4 = array_merge([$constructionTarget], array_slice($noteDistractors, 0, 3));
                        shuffle($noteOptions4);
                    }
                    $noteGridCols = count($noteOptions4) > 4 ? 'grid-cols-3' : 'grid-cols-2';
                @endphp
                <div id="answerOptions" class="grid {{ $noteGridCols }} gap-3"
                     data-target="{{ $constructionTarget }}"
                     data-practice-id="{{ $practice['id'] ?? 0 }}"
                     data-type="{{ $type }}">
                    @foreach($noteOptions4 as $noteOption)
                        <button class="answer-btn rounded-xl p-4 text-center font-bold text-gray-700 text-lg"
                                data-answer="{{ $noteOption }}">
                            {{ $noteSymbol($noteOption) }}
                        </button>
                    @endforeach
                </div>
            @elseif($type === 'chord')
                @php
                    $chordTarget = strtolower($practice['chord_type'] ?? '');
                    $chordCorrect = $practice['chord_type'] ?? 'Major';
                    $allChords = ['Major','Minor','Diminished','Augmented','Dominant 7th','Major 7th','Minor 7th'];
                    $chordOtherOpts = $practice['other_options'] ?? [];
                    if (is_string($chordOtherOpts)) $chordOtherOpts = json_decode($chordOtherOpts, true) ?? [];
                    $chordDistractors = array_values(array_filter($allChords, fn($c) => strtolower($c) !== $chordTarget));
                    $chordOptions = array_merge([$chordCorrect], !empty($chordOtherOpts) ? array_slice($chordOtherOpts, 0, 3) : array_slice($chordDistractors, 0, 3));
                    if (count($chordOptions) < 4) {
                        $extra = array_values(array_filter($chordDistractors, fn($c) => !in_array(strtolower($c), array_map('strtolower', $chordOptions))));
                        $chordOptions = array_merge($chordOptions, array_slice($extra, 0, 4 - count($chordOptions)));
                    }
                    shuffle($chordOptions);
                @endphp
                <div id="answerOptions" class="grid grid-cols-2 gap-3"
                     data-target="{{ $chordTarget }}"
                     data-practice-id="{{ $practice['id'] ?? 0 }}"
                     data-type="{{ $type }}">
                    @foreach($chordOptions as $chordOpt)
                        <button class="answer-btn rounded-xl p-4 text-center font-semibold text-gray-700 text-sm"
                                data-answer="{{ strtolower($chordOpt) }}">
                            {{ $chordOpt }}
                        </button>
                    @endforeach
                </div>
            @elseif($type === 'scale')
                @php
                    $scaleTarget = strtolower($practice['scale_type'] ?? '');
                    $scaleCorrect = $practice['scale_type'] ?? 'Major';
                    $allScales = ['Major','Natural Minor','Harmonic Minor','Melodic Minor','Dorian','Phrygian','Lydian','Mixolydian','Pentatonic','Blues'];
                    $scaleOtherOpts = $practice['other_options'] ?? [];
                    if (is_string($scaleOtherOpts)) $scaleOtherOpts = json_decode($scaleOtherOpts, true) ?? [];
                    $scaleDistractors = array_values(array_filter($allScales, fn($s) => strtolower($s) !== $scaleTarget));
                    $scaleOptions = array_merge([$scaleCorrect], !empty($scaleOtherOpts) ? array_slice($scaleOtherOpts, 0, 3) : array_slice($scaleDistractors, 0, 3));
                    if (count($scaleOptions) < 4) {
                        $extra = array_values(array_filter($scaleDistractors, fn($s) => !in_array(strtolower($s), array_map('strtolower', $scaleOptions))));
                        $scaleOptions = array_merge($scaleOptions, array_slice($extra, 0, 4 - count($scaleOptions)));
                    }
                    shuffle($scaleOptions);
                @endphp
                <div id="answerOptions" class="grid grid-cols-2 gap-3"
                     data-target="{{ $scaleTarget }}"
                     data-practice-id="{{ $practice['id'] ?? 0 }}"
                     data-type="{{ $type }}">
                    @foreach($scaleOptions as $scaleOpt)
                        <button class="answer-btn rounded-xl p-4 text-center font-semibold text-gray-700 text-sm"
                                data-answer="{{ strtolower($scaleOpt) }}">
                            {{ $scaleOpt }}
                        </button>
                    @endforeach
                </div>
            @elseif($type === 'rhythm')
                @php
                    $noteValsArr = $practice['note_values'] ?? [];
                    if (is_string($noteValsArr)) $noteValsArr = json_decode($noteValsArr, true) ?? [];
                    $rhythmAnswerTarget = implode(',', $noteValsArr);
                    $rhythmAllowed = $practice['allowed_values'] ?? [];
                    if (is_string($rhythmAllowed)) $rhythmAllowed = json_decode($rhythmAllowed, true) ?? [];
                    if (empty($rhythmAllowed)) {
                        $rhythmAllowed = ['whole','half','quarter','eighth','sixteenth','dotted-half','dotted-quarter','dotted-eighth','half_rest','quarter_rest','eighth_rest'];
                    }
                @endphp
                {{-- Compact time-signature / tempo line (sits below Play, above the note buttons) --}}
                <div class="text-center text-sm text-gray-500 mb-2">
                    <span class="text-lg font-bold text-gray-800">{{ $practice['time_signature'] ?? '4/4' }}</span>
                    <span class="mx-1">·</span>{{ $practice['tempo'] ?? 80 }} BPM
                    <span class="mx-1">·</span>{{ $practice['bars'] ?? 1 }} bar(s)
                </div>
                <p class="text-sm text-gray-500 mb-3 text-center">Click the notes below to rebuild the rhythm you heard.</p>
                {{-- Bar-fill meter --}}
                <div class="max-w-xs mx-auto mb-4">
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-xs font-medium text-gray-500">Bar</span>
                        <span id="rhythmFillLabel" class="text-xs font-semibold text-gray-600">0 / 0 beats</span>
                    </div>
                    <div class="h-2 w-full rounded-full bg-gray-200 overflow-hidden">
                        <div id="rhythmFillBar" class="h-full rounded-full bg-amber-400 transition-all duration-200" style="width:0%"></div>
                    </div>
                </div>
                {{-- Wrapper keeps id="answerOptions" so the shared play-button setup wires up;
                     the builder is driven by setupRhythmBuilder() in the script below. --}}
                <div id="answerOptions"
                     data-target="{{ $rhythmAnswerTarget }}"
                     data-practice-id="{{ $practice['id'] ?? 0 }}"
                     data-type="rhythm"
                     data-allowed="{{ implode(',', $rhythmAllowed) }}"
                     data-bars="{{ $practice['bars'] ?? 1 }}">
                    {{-- Note palette (filled by JS from RHYTHM_GLYPHS) --}}
                    <div id="rhythmPalette" class="flex flex-wrap items-stretch justify-center gap-2 mb-4"></div>
                    {{-- Controls --}}
                    <div class="flex flex-wrap items-center justify-center gap-2">
                        <button type="button" id="rhythmPlayMineBtn"
                                class="inline-flex items-center gap-1.5 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-600 hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed">
                            <i data-lucide="volume-2" class="w-4 h-4"></i> Play my rhythm
                        </button>
                        <button type="button" id="rhythmDeleteBtn"
                                class="inline-flex items-center gap-1.5 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-600 hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed">
                            <i data-lucide="delete" class="w-4 h-4"></i> Delete last
                        </button>
                        <button type="button" id="rhythmClearBtn"
                                class="inline-flex items-center gap-1.5 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-600 hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed">
                            <i data-lucide="eraser" class="w-4 h-4"></i> Clear
                        </button>
                        <button type="button" id="rhythmCheckBtn"
                                class="btn-primary inline-flex items-center gap-1.5 rounded-lg px-6 py-2.5 text-sm font-semibold text-white hover:shadow-lg transition-shadow disabled:opacity-40 disabled:cursor-not-allowed">
                            <i data-lucide="check" class="w-4 h-4"></i> Check
                        </button>
                    </div>
                </div>
            @elseif($type === 'melodic_dictation')
                @php
                    $mdAnswerNotes = $practice['notes'] ?? [];
                    if (is_string($mdAnswerNotes)) $mdAnswerNotes = json_decode($mdAnswerNotes, true) ?? [];
                    $mdTarget = implode(',', $mdAnswerNotes);
                    // Generate 3 alternative shuffled sequences as distractors
                    $mdDistractors = [];
                    for ($mdI = 0; $mdI < 9 && count($mdDistractors) < 3; $mdI++) {
                        $shuffled = $mdAnswerNotes;
                        shuffle($shuffled);
                        $candidate = implode(',', $shuffled);
                        if ($candidate !== $mdTarget && !in_array($candidate, $mdDistractors)) {
                            $mdDistractors[] = $candidate;
                        }
                    }
                    $mdOptions = array_merge([$mdTarget], $mdDistractors);
                    $mdOptions = array_slice($mdOptions, 0, 4);
                    shuffle($mdOptions);
                @endphp
                <div id="answerOptions" class="grid grid-cols-2 gap-3"
                     data-target="{{ $mdTarget }}"
                     data-practice-id="{{ $practice['id'] ?? 0 }}"
                     data-type="{{ $type }}">
                    @foreach($mdOptions as $mdOpt)
                        <button class="answer-btn rounded-xl p-4 text-center font-semibold text-gray-700 text-xs font-mono"
                                data-answer="{{ $mdOpt }}">
                            {{ str_replace(',', ' → ', $mdOpt) }}
                        </button>
                    @endforeach
                </div>
            @endif

            <!-- Feedback Message -->
            <div id="feedbackMessage" class="rounded-xl p-3 text-center font-medium text-sm hidden"></div>

            @endif {{-- end null $currentPractice guard --}}
        </div>
        <!-- ── /Content ── -->
    </div>

    
    <script src="https://cdn.jsdelivr.net/npm/vexflow@4.2.2/build/cjs/vexflow.js"></script>
    <script>
        // Render accidentals as proper music symbols for display (C# -> C♯, Db -> D♭).
        function noteToSymbol(note) {
            if (!note) return note;
            const s = note.charAt(0).toUpperCase() + note.slice(1);
            return s.replace(/#/g, '♯').replace(/b/g, '♭');
        }

        // ── Rhythm Dictation: note-value palette glyphs ───────────────────────────
        // Each entry has a short label and an inline SVG note glyph (uses currentColor
        // so it inherits the button text colour). The SVG is the visual; the label
        // disambiguates. Order here is the palette order shown to the user.
        const RHYTHM_GLYPHS = {
            'whole':          { label: 'Whole',    svg: '<ellipse cx="13" cy="26" rx="8" ry="5" fill="none" stroke="currentColor" stroke-width="2.4"/>' },
            'half':           { label: 'Half',     svg: '<ellipse cx="8" cy="30" rx="6.4" ry="4.5" transform="rotate(-25 8 30)" fill="none" stroke="currentColor" stroke-width="1.9"/><rect x="13.1" y="8" width="1.8" height="22.5" fill="currentColor"/>' },
            'quarter':        { label: 'Quarter',  svg: '<ellipse cx="8" cy="30" rx="6.4" ry="4.5" transform="rotate(-25 8 30)" fill="currentColor"/><rect x="13.1" y="8" width="1.8" height="22.5" fill="currentColor"/>' },
            'eighth':         { label: '8th',      svg: '<ellipse cx="8" cy="30" rx="6.4" ry="4.5" transform="rotate(-25 8 30)" fill="currentColor"/><rect x="13.1" y="8" width="1.8" height="22.5" fill="currentColor"/><path d="M15 8 q8 3 5 13 q3-7-5-9 z" fill="currentColor"/>' },
            'sixteenth':      { label: '16th',     svg: '<ellipse cx="8" cy="30" rx="6.4" ry="4.5" transform="rotate(-25 8 30)" fill="currentColor"/><rect x="13.1" y="6" width="1.8" height="24.5" fill="currentColor"/><path d="M15 6 q8 3 5 12 q3-6-5-8 z" fill="currentColor"/><path d="M15 13 q8 3 5 12 q3-6-5-8 z" fill="currentColor"/>' },
            'dotted-half':    { label: 'Half.',    svg: '<ellipse cx="8" cy="30" rx="6.4" ry="4.5" transform="rotate(-25 8 30)" fill="none" stroke="currentColor" stroke-width="1.9"/><rect x="13.1" y="8" width="1.8" height="22.5" fill="currentColor"/><circle cx="19" cy="30" r="2" fill="currentColor"/>' },
            'dotted-quarter': { label: 'Qtr.',     svg: '<ellipse cx="8" cy="30" rx="6.4" ry="4.5" transform="rotate(-25 8 30)" fill="currentColor"/><rect x="13.1" y="8" width="1.8" height="22.5" fill="currentColor"/><circle cx="19" cy="30" r="2" fill="currentColor"/>' },
            'dotted-eighth':  { label: '8th.',     svg: '<ellipse cx="8" cy="30" rx="6.4" ry="4.5" transform="rotate(-25 8 30)" fill="currentColor"/><rect x="13.1" y="8" width="1.8" height="22.5" fill="currentColor"/><path d="M15 8 q8 3 5 13 q3-7-5-9 z" fill="currentColor"/><circle cx="22" cy="30" r="2" fill="currentColor"/>' },
            'half_rest':      { label: '½ rest',   svg: '<line x1="4" y1="22" x2="22" y2="22" stroke="currentColor" stroke-width="1.2"/><rect x="7" y="16.5" width="12" height="5.5" fill="currentColor"/>' },
            'quarter_rest':   { label: 'Qtr rest', svg: '<path d="M9 8 L15 17 L10 21 L16 29 C12 27 9 29 11 33" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round"/>' },
            'eighth_rest':    { label: '8th rest', svg: '<circle cx="9" cy="15" r="2.6" fill="currentColor"/><path d="M11 14 L15 28" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>' },
            // Triplet: three beamed eighths with a "3" above. Clicking adds three triplet-eighths.
            'triplet':        { label: 'Triplet', svg: '<text x="13" y="9" font-size="9" font-weight="700" text-anchor="middle" fill="currentColor">3</text><rect x="3" y="13" width="20" height="2.4" fill="currentColor"/><rect x="3.6" y="14" width="1.6" height="14" fill="currentColor"/><rect x="12.2" y="14" width="1.6" height="14" fill="currentColor"/><rect x="20.8" y="14" width="1.6" height="14" fill="currentColor"/><ellipse cx="4.4" cy="29" rx="3.1" ry="2.2" transform="rotate(-20 4.4 29)" fill="currentColor"/><ellipse cx="13" cy="29" rx="3.1" ry="2.2" transform="rotate(-20 13 29)" fill="currentColor"/><ellipse cx="21.6" cy="29" rx="3.1" ry="2.2" transform="rotate(-20 21.6 29)" fill="currentColor"/>' },
        };

        // Build the inner HTML (glyph + label) for a rhythm note value.
        function rhythmGlyphMarkup(value, big) {
            const g = RHYTHM_GLYPHS[value];
            if (!g) return value;
            const h = big ? 38 : 30;
            return `<svg viewBox="0 0 26 40" width="${big ? 24 : 20}" height="${h}" class="block mx-auto">${g.svg}</svg>`
                 + `<span class="block text-center ${big ? 'text-[11px] mt-1' : 'text-[10px]'} font-medium leading-none">${g.label}</span>`;
        }

        // Map a rhythm note value to a VexFlow base duration code. Dots and rests are
        // applied separately (dotted-* gets a Dot modifier; *_rest renders a rest glyph).
        const RHYTHM_VF_DURATION = {
            'whole': 'w', 'half': 'h', 'quarter': 'q', 'eighth': '8', 'sixteenth': '16',
            'dotted-half': 'h', 'dotted-quarter': 'q', 'dotted-eighth': '8',
            'triplet-eighth': '8',
            'half_rest': 'hr', 'quarter_rest': 'qr', 'eighth_rest': '8r',
        };

        // Note value → length in twelfths-of-a-quarter (integer math that also fits triplets:
        // a triplet-eighth = 4, three of them = 12 = one quarter).
        const RHYTHM_TWELFTHS = {
            'whole': 48, 'half': 24, 'dotted-half': 36, 'quarter': 12, 'dotted-quarter': 18,
            'eighth': 6, 'dotted-eighth': 9, 'sixteenth': 3, 'triplet-eighth': 4,
            'whole_rest': 48, 'half_rest': 24, 'quarter_rest': 12, 'eighth_rest': 6,
        };

        // The grouping beat (in twelfths) for beaming: x/8 compound = dotted-quarter (18),
        // x/2 = half note (24), x/4 = quarter (12).
        function rhythmBeatTicks(den) {
            return den === 8 ? 18 : Math.round(48 / den);
        }

        // Draw a rhythm sequence onto a single-line rhythm staff (time signature, no
        // clef — these are durations, not pitches) using VexFlow. Noteheads sit on the
        // line (b/4) and rests render as rest glyphs. Uses SOFT voice mode so partial /
        // over-full bars draw without throwing while the user is still building.
        // `color` optionally tints the notes (e.g. the correct-answer reveal).
        function drawRhythmStaff(container, sequence, timeSig, color) {
            if (!container || typeof Vex === 'undefined') return;
            const { Renderer, Stave, StaveNote, Voice, Formatter, Dot, Beam, Tuplet } = Vex.Flow;
            container.innerHTML = '';

            const sig = timeSig || '4/4';
            const num = parseInt(sig.split('/')[0]) || 4;
            const den = parseInt(sig.split('/')[1]) || 4;
            const n = sequence.length;

            const sigWidth = 34;
            const noteWidth = 48;
            // Use the container's full rendered width so the staff spans edge-to-edge.
            const containerW = Math.max(300, (container.offsetWidth || 500) - 20);
            const width = Math.max(containerW, sigWidth + 24 + Math.max(1, n) * noteWidth);
            const height = 104;

            const renderer = new Renderer(container, Renderer.Backends.SVG);
            renderer.resize(width, height);
            const ctx = renderer.getContext();

            const stave = new Stave(6, 14, width - 16);
            // Single-line "rhythm" staff: keep the normal 5-line geometry (so notes,
            // stems and the time signature position correctly) but show only the
            // middle line, on which the noteheads (b/4) sit.
            try {
                stave.setConfigForLines([
                    { visible: false }, { visible: false },
                    { visible: true },
                    { visible: false }, { visible: false },
                ]);
            } catch (e) {}
            stave.addTimeSignature(sig);
            stave.setContext(ctx).draw();

            // Center the rhythm staff vertically within its container after every render,
            // including the empty-bar case (early return path).
            const centerRhythm = () => requestAnimationFrame(() => {
                const svg = container.querySelector('svg');
                if (!svg) return;
                try {
                    const bbox = svg.getBBox();
                    if (!bbox || bbox.height < 5) return;
                    const containerH = Math.max(container.offsetHeight || 110, 80);
                    const pad = 10;
                    const totalH = bbox.height + pad * 2;
                    const topPad = Math.max(0, (containerH - totalH) / 2);
                    const viewY = bbox.y - pad - topPad;
                    const vw = parseFloat(svg.getAttribute('width')) || width;
                    svg.setAttribute('viewBox', `0 ${viewY} ${vw} ${containerH}`);
                    svg.setAttribute('height', String(containerH));
                } catch(e) {}
            });

            if (n === 0) { centerRhythm(); return; }

            const notes = sequence.map(v => {
                const dur = RHYTHM_VF_DURATION[v] || 'q';
                const note = new StaveNote({ keys: ['b/4'], duration: dur, auto_stem: true });
                if (v.startsWith('dotted-')) {
                    try { Dot.buildAndAttach([note], { all: true }); } catch (e) {}
                }
                if (color) note.setStyle({ fillStyle: color, strokeStyle: color });
                return note;
            });

            // ── Group notes per beat for correct beaming + triplet tuplets ──
            // Walk the sequence in twelfths so each beam stays within one beat; a triplet
            // (3 consecutive triplet-eighths) forms its own beam + a "3" tuplet bracket.
            const beatTicks = rhythmBeatTicks(den);
            const beamable = v => v === 'eighth' || v === 'dotted-eighth' || v === 'sixteenth' || v === 'triplet-eighth';
            const beamGroups = [];
            const tripletGroups = [];
            let run = [], tripletRun = [], curBeat = -1, pos = 0;
            const flushRun = () => { if (run.length >= 2) beamGroups.push(run); run = []; };

            sequence.forEach((v, i) => {
                const dur = RHYTHM_TWELFTHS[v] ?? 12;
                const beat = Math.floor(pos / beatTicks);
                if (v === 'triplet-eighth') {
                    flushRun();
                    tripletRun.push(notes[i]);
                    if (tripletRun.length === 3) {
                        beamGroups.push(tripletRun.slice());
                        tripletGroups.push(tripletRun.slice());
                        tripletRun = [];
                    }
                } else if (beamable(v)) {
                    if (beat !== curBeat) flushRun();
                    run.push(notes[i]);
                } else {
                    flushRun();
                }
                curBeat = beat;
                pos += dur;
            });
            flushRun();

            // Tuplets must exist before formatting so the formatter compresses their spacing.
            const tuplets = tripletGroups.map(g => {
                try { return new Tuplet(g, { num_notes: 3, notes_occupied: 2 }); } catch (e) { return null; }
            }).filter(Boolean);
            const beams = beamGroups.map(g => {
                try { return new Beam(g); } catch (e) { return null; }
            }).filter(Boolean);

            const voice = new Voice({ numBeats: num, beatValue: den }).setMode(Voice.Mode.SOFT);
            voice.addTickables(notes);
            new Formatter().joinVoices([voice]).format([voice], Math.max(120, width - sigWidth - 50));

            voice.draw(ctx, stave);
            beams.forEach(b => { try { b.setContext(ctx).draw(); } catch (e) {} });
            tuplets.forEach(t => { try { t.setContext(ctx).draw(); } catch (e) {} });
            centerRhythm();
        }

        // Crisp Web Audio API metronome click — no piano sampler, no sustain.
        // Accent (beat 1): 1047 Hz sine, 18 ms decay  → Google-metronome "tick"
        // Regular beat  :  784 Hz sine, 13 ms decay  → softer off-beat tick
        // `atTime` (optional) is an absolute audio-context time (seconds, same clock as
        // Tone.now()) so the click lands exactly with a scheduled note. Falls back to "now".
        function _metroClick(accent, atTime) {
            try {
                const rawCtx = (typeof Tone !== 'undefined' && Tone.getContext)
                    ? Tone.getContext().rawContext
                    : new (window.AudioContext || window.webkitAudioContext)();
                const osc  = rawCtx.createOscillator();
                const gain = rawCtx.createGain();
                osc.connect(gain);
                gain.connect(rawCtx.destination);
                osc.type = 'sine';
                osc.frequency.value = accent ? 1047 : 784;
                const t   = (typeof atTime === 'number') ? atTime : rawCtx.currentTime;
                const dur = accent ? 0.018 : 0.013;
                gain.gain.setValueAtTime(accent ? 0.9 : 0.55, t);
                gain.gain.exponentialRampToValueAtTime(0.0001, t + dur);
                osc.start(t);
                osc.stop(t + dur);
            } catch(e) {}
        }

        // Schedule a rhythm: one-bar metronome count-in, then the rhythm hits with a
        // metronome ticking under them. Both the clicks (oscillator) and the notes (piano
        // sampler) are scheduled at ABSOLUTE times on the shared audio-context clock — no
        // setTimeout — so they stay sample-accurately in sync. Returns total duration (ms).
        async function playRhythmAudio(sequence, tempo, timeSig) {
            const t = parseInt(tempo) || 80;
            const sig = timeSig || '4/4';
            const num = parseInt(sig.split('/')[0]) || 4;
            const den = parseInt(sig.split('/')[1]) || 4;
            const beatMs = 60000 / t;                          // quarter-note length (tempo = quarter BPM)
            // Main beat: dotted-quarter (×1.5) for x/8 compound, half note (×2) for x/2, else quarter.
            const tickMs = den === 8 ? beatMs * 1.5 : den === 2 ? beatMs * 2 : beatMs;
            const ticksPerBar = den === 8 ? Math.max(1, Math.round(num / 3)) : num;
            const noteDurations = {
                whole: 4, half: 2, quarter: 1, eighth: 0.5, sixteenth: 0.25,
                'dotted-half': 3, 'dotted-quarter': 1.5, 'dotted-eighth': 0.75,
                'triplet-eighth': 1 / 3,
                whole_rest: 4, half_rest: 2, quarter_rest: 1, eighth_rest: 0.5,
            };

            // Wait for the sampler, then anchor every event to one clock with a small lead-in.
            await window.HarmonivaAudio.prepare();
            const leadMs = 120;
            const t0 = window.HarmonivaAudio.now() + leadMs / 1000; // audio-context seconds
            const click = (ms, accent) => _metroClick(accent, t0 + ms / 1000);
            const hit = (ms, ringSec) => window.HarmonivaAudio.playNoteAt('C4', ringSec, t0 + ms / 1000);

            for (let b = 0; b < ticksPerBar; b++) click(b * tickMs, b === 0); // count-in
            const startMs = ticksPerBar * tickMs;
            for (let b = 0; b < ticksPerBar; b++) click(startMs + b * tickMs, b === 0); // bar metronome

            let elapsed = 0;
            for (const note of sequence) {
                const dur = noteDurations[note] || 1;
                const noteMs = dur * beatMs;
                if (!note.includes('rest')) {
                    hit(startMs + elapsed, Math.max(0.08, (noteMs / 1000) * 0.85));
                }
                elapsed += noteMs;
            }
            // Include the lead-in so callers reset the button after playback truly ends.
            return leadMs + startMs + elapsed;
        }

        // Wire up the rhythm-dictation builder: a note palette that appends into a
        // table, delete-last/clear controls, and a Check button that submits the built
        // sequence to the shared answerPractice() Livewire action (exact-match scoring).
        function setupRhythmBuilder(target) {
            const palette   = document.getElementById('rhythmPalette');
            const table     = document.getElementById('rhythmTable');
            const deleteBtn = document.getElementById('rhythmDeleteBtn');
            const clearBtn  = document.getElementById('rhythmClearBtn');
            const checkBtn  = document.getElementById('rhythmCheckBtn');
            const feedbackMessage   = document.getElementById('feedbackMessage');
            const playButton        = document.getElementById('playButton');
            const playStatus        = document.getElementById('playStatus');
            const nextButton        = document.getElementById('nextPracticeBtn');
            const finishPracticeBtn = document.getElementById('finishPracticeBtn');
            if (!palette || !table || !checkBtn) return;

            const answerOptions = document.getElementById('answerOptions');
            const playMineBtn   = document.getElementById('rhythmPlayMineBtn');
            const fillBar       = document.getElementById('rhythmFillBar');
            const fillLabel     = document.getElementById('rhythmFillLabel');

            const timeSig = (playButton && playButton.dataset.timeSig) || '4/4';
            const num = parseInt(timeSig.split('/')[0]) || 4;
            const den = parseInt(timeSig.split('/')[1]) || 4;
            const bars = parseInt((answerOptions && answerOptions.dataset.bars) || '1') || 1;

            // Palette comes from the difficulty's allowed pool (data-allowed), so the
            // buttons match what could actually have been generated.
            const allowedAttr = (answerOptions && answerOptions.dataset.allowed) || '';
            const PALETTE = allowedAttr
                ? allowedAttr.split(',').filter(Boolean)
                : ['whole', 'half', 'quarter', 'eighth', 'sixteenth',
                    'dotted-half', 'dotted-quarter', 'dotted-eighth',
                    'half_rest', 'quarter_rest', 'eighth_rest'];

            // Note value → twelfths-of-a-quarter (integer math that also fits triplets:
            // triplet-eighth = 4, three = 12 = one quarter). Capacity = one bar × bar count.
            const TWELFTHS = RHYTHM_TWELFTHS;
            const beatTicks = rhythmBeatTicks(den);            // grouping beat in twelfths (12 / 18 / 24)
            const capacity = Math.round((48 * num / den) * bars);
            const totalBeats = beatTicks > 0 ? capacity / beatTicks : 0;

            let built = [];
            let answered = false;

            const fmt = (x) => Number.isInteger(x) ? String(x) : x.toFixed(2).replace(/\.?0+$/, '');
            const sumTwelfths = () => built.reduce((a, v) => a + (TWELFTHS[v] || 0), 0);

            const updateState = () => {
                const sum  = sumTwelfths();
                const full = capacity > 0 && sum === capacity;
                const over = capacity > 0 && sum > capacity;

                if (fillBar) {
                    fillBar.style.width = (capacity > 0 ? Math.min(100, (sum / capacity) * 100) : 0) + '%';
                    fillBar.classList.remove('bg-amber-400', 'bg-green-500', 'bg-red-500');
                    fillBar.classList.add(over ? 'bg-red-500' : full ? 'bg-green-500' : 'bg-amber-400');
                }
                if (fillLabel) {
                    fillLabel.textContent = `${fmt(sum / beatTicks)} / ${fmt(totalBeats)} beats`
                        + (over ? ' (too long)' : '');
                    fillLabel.classList.remove('text-gray-600', 'text-green-600', 'text-red-600');
                    fillLabel.classList.add(over ? 'text-red-600' : full ? 'text-green-600' : 'text-gray-600');
                }

                deleteBtn.disabled = answered || built.length === 0;
                clearBtn.disabled  = answered || built.length === 0;
                checkBtn.disabled  = answered || !full;          // only a complete bar can be checked
                if (playMineBtn) playMineBtn.disabled = built.length === 0;
            };

            const renderTable = () => {
                drawRhythmStaff(table, built, timeSig);
                updateState();
            };

            // Build the clickable note palette.
            palette.innerHTML = '';
            PALETTE.forEach(v => {
                const b = document.createElement('button');
                b.type = 'button';
                b.className = 'rhythm-note-btn w-14 h-16 rounded-xl bg-[#2a7898] text-white flex flex-col items-center justify-center hover:bg-[#23698a] active:scale-95 transition disabled:opacity-40 disabled:cursor-not-allowed';
                b.dataset.value = v;
                b.innerHTML = rhythmGlyphMarkup(v, true);
                b.onclick = () => {
                    if (answered) return;
                    // The triplet button inserts a whole triplet (three triplet-eighths).
                    if (v === 'triplet') built.push('triplet-eighth', 'triplet-eighth', 'triplet-eighth');
                    else built.push(v);
                    renderTable();
                };
                palette.appendChild(b);
            });

            // Delete last: a triplet is removed as a unit (its three tokens), else one token.
            deleteBtn.onclick = () => {
                if (answered) return;
                if (built[built.length - 1] === 'triplet-eighth') built.splice(-3, 3);
                else built.pop();
                renderTable();
            };
            clearBtn.onclick  = () => { if (answered) return; built = []; renderTable(); };

            // "Play my rhythm" — hear what you built (same metronome + click voice as Play).
            if (playMineBtn) {
                playMineBtn.onclick = async () => {
                    if (built.length === 0) return;
                    if (typeof Tone !== 'undefined') await Tone.start();
                    const tempo = (playButton && playButton.dataset.tempo) || 80;
                    const original = playMineBtn.innerHTML;
                    playMineBtn.disabled = true;
                    playMineBtn.innerHTML = '<i data-lucide="volume-2" class="w-4 h-4"></i> Playing...';
                    if (typeof lucide !== 'undefined') lucide.createIcons();
                    const totalMs = await playRhythmAudio(built, tempo, timeSig);
                    setTimeout(() => {
                        playMineBtn.innerHTML = original;
                        playMineBtn.disabled = built.length === 0;
                        if (typeof lucide !== 'undefined') lucide.createIcons();
                    }, totalMs + 300);
                };
            }

            checkBtn.onclick = async () => {
                if (answered || built.length === 0) return;
                const answer = built.join(',');
                checkBtn.disabled = true;
                checkBtn.innerHTML = '<i data-lucide="loader-2" class="w-4 h-4 animate-spin"></i> Checking...';
                if (typeof lucide !== 'undefined') lucide.createIcons();
                try {
                    const data = await @this.call('answerPractice', 'rhythm', answer, target);
                    answered = true;
                    palette.querySelectorAll('button').forEach(b => b.disabled = true);
                    deleteBtn.disabled = true;
                    clearBtn.disabled = true;
                    checkBtn.disabled = true;

                    if (playButton) playButton.classList.add('hidden');
                    if (playStatus) playStatus.classList.add('hidden');
                    if (nextButton) nextButton.classList.remove('hidden');
                    if (finishPracticeBtn) finishPracticeBtn.classList.remove('hidden');

                    if (data.is_correct) {
                        checkBtn.innerHTML = '<i data-lucide="check" class="w-4 h-4"></i> Correct';
                        table.classList.remove('border-gray-300');
                        table.classList.add('border-green-400', 'bg-green-50');
                        feedbackMessage.textContent = '✓ Correct! Well done!';
                        feedbackMessage.classList.remove('hidden', 'bg-red-100', 'text-red-700');
                        feedbackMessage.classList.add('bg-green-100', 'text-green-700');
                        const xpEl = document.getElementById('xpEarned');
                        if (xpEl) xpEl.textContent = (parseInt(xpEl.textContent) || 0) + 10;
                        const sc = document.getElementById('scoreCorrect');
                        if (sc) sc.textContent = (parseInt(sc.textContent) || 0) + 1;
                    } else {
                        checkBtn.innerHTML = '<i data-lucide="x" class="w-4 h-4"></i> Incorrect';
                        table.classList.remove('border-gray-300');
                        table.classList.add('border-red-400', 'bg-red-50');
                        feedbackMessage.textContent = '✗ Not quite — the correct rhythm is shown below.';
                        feedbackMessage.classList.remove('hidden', 'bg-green-100', 'text-green-700');
                        feedbackMessage.classList.add('bg-red-100', 'text-red-700');
                        const reveal = document.getElementById('rhythmReveal');
                        const revealRow = document.getElementById('rhythmRevealRow');
                        if (reveal && revealRow) {
                            drawRhythmStaff(revealRow, target.split(',').filter(Boolean), timeSig, '#15803d');
                            reveal.classList.remove('hidden');
                        }
                    }
                    const st = document.getElementById('scoreTotal');
                    if (st) st.textContent = (parseInt(st.textContent) || 0) + 1;
                    if (typeof lucide !== 'undefined') lucide.createIcons();
                } catch (e) {
                    console.error('Error checking rhythm:', e);
                    answered = false;
                    checkBtn.disabled = false;
                    checkBtn.innerHTML = '<i data-lucide="check" class="w-4 h-4"></i> Check';
                    if (typeof lucide !== 'undefined') lucide.createIcons();
                }
            };

            renderTable();
            if (typeof lucide !== 'undefined') lucide.createIcons();
        }

        // Define global init function for mixed practice
        window.initPracticeMixed = function() {

            // Centers a VexFlow SVG inside its container by measuring the real
            // bounding box after render and adjusting the viewBox accordingly.
            const centerVexOutput = (el, containerH = 160) => requestAnimationFrame(() => {
                if (!el) return;
                const svg = el.querySelector('svg');
                if (!svg) return;
                try {
                    const bbox = svg.getBBox();
                    if (!bbox || bbox.height < 5) return;
                    const pad = 8;
                    const vw = parseFloat(svg.getAttribute('width')) || 546;
                    const totalH = bbox.height + pad * 2;
                    const topPad = Math.max(0, (containerH - totalH) / 2);
                    svg.setAttribute('viewBox', `0 ${bbox.y - pad - topPad} ${vw} ${containerH}`);
                    svg.setAttribute('height', String(containerH));
                } catch(e) {}
            });

            // ---- Get DOM elements ----
            const playButton = document.getElementById('playButton');
            const playStatus = document.getElementById('playStatus');
            const nextButton = document.getElementById('nextPracticeBtn');
            const finishPracticeBtn = document.getElementById('finishPracticeBtn');
            const answerOptions = document.getElementById('answerOptions');
            const answerButtons = document.querySelectorAll('.answer-btn');
            const feedbackMessage = document.getElementById('feedbackMessage');

            // ---- ALWAYS RESET STATE at the start ----
            if (playButton) {
                playButton.disabled = false;
                playButton.classList.remove('hidden');
                const playMode = playButton.dataset.playMode;
                const playType = playButton.dataset.type;
                let btnText = 'Play';
                if (playMode === 'comparison') btnText = 'Play Both Intervals';
                else if (playMode === 'harmonic') btnText = 'Play Interval';
                else if (playType === 'interval_construction') btnText = 'Play Starting Note';
                else if (playType === 'chord') btnText = 'Play Chord';
                else if (playType === 'scale') btnText = 'Play Scale';
                else if (playType === 'rhythm') btnText = 'Play Rhythm';
                else if (playType === 'melodic_dictation') btnText = 'Play Melody';
                playButton.innerHTML = `<i data-lucide="play" class="w-5 h-5"></i> ${btnText}`;
                if (typeof lucide !== 'undefined') lucide.createIcons();
            }
            if (playStatus) {
                playStatus.classList.remove('hidden');
                playStatus.textContent = 'Listen to the note to start';
            }
            if (nextButton) nextButton.classList.add('hidden');
            if (finishPracticeBtn) finishPracticeBtn.classList.add('hidden');
            if (feedbackMessage) {
                feedbackMessage.classList.add('hidden');
                feedbackMessage.textContent = '';
                feedbackMessage.className = 'mt-4 p-4 rounded-lg text-center font-medium hidden';
            }
            // Re-enable all answer buttons and reset classes
            answerButtons.forEach(b => {
                b.disabled = false;
                b.classList.remove('correct', 'incorrect', 'text-green-700', 'text-red-700');
                b.classList.add('text-gray-700');
            });

            // Clear any stale interval reveal handler from a previous question.
            window._revealHarmonicMixed = null;
            window._revealConstructionPlay = null;

            // ---- Initialize VexFlow ----
            if (typeof Vex !== 'undefined') {
                console.log("VexFlow Build:", Vex.Flow.BUILD);
                const { Renderer, Stave, StaveNote, Voice, Formatter, Accidental } = Vex.Flow;

                const div = document.getElementById("output");
                if (div) {
                    div.innerHTML = ''; // Clear previous content

                    const renderer = new Renderer(div, Renderer.Backends.SVG);
                    const notesFromParams = div.dataset.notes;
                    const noteType = div.dataset.type;

                    if (noteType === 'interval_comparison') {
                        renderer.resize(468, 160);
                        const context = renderer.getContext();
                        const stave = new Stave(10, 30, 442);
                        stave.addClef(div.dataset.clef || 'treble');
                        stave.setContext(context).draw();

                        if (notesFromParams) {
                            const notesParsed = notesFromParams.split(',');
                            const notes = notesParsed.map(note => new StaveNote({ keys: [note], duration: "q", auto_stem: true }));
                            const voice = new Voice({ numBeats: 4, beatValue: 4 });
                            voice.addTickables(notes);
                            Accidental.applyAccidentals([voice], 'C');
                            new Formatter().joinVoices([voice]).format([voice], 280);
                            voice.draw(context, stave);
                            centerVexOutput(div);
                        }
                    } else if (noteType === 'harmonic_interval' || noteType === 'melodic_interval' || noteType === 'interval_construction') {
                        // Interval practice: reveal only the root (bottom) note at first.
                        // The second note is added after the user answers, so the printed
                        // staff doesn't give away the interval. Harmonic intervals draw
                        // both notes as a chord; melodic & construction draw them sequentially.
                        const isHarmonic = noteType === 'harmonic_interval';
                        const isConstruction = noteType === 'interval_construction';
                        const notesParsed = (notesFromParams || '').split(',').filter(n => n.length > 0);
                        const note1Key = notesParsed[0] || 'c/4';
                        const note2Key = notesParsed[1] || note1Key;
                        const drawInterval = (showBoth) => {
                            div.innerHTML = '';
                            const r = new Renderer(div, Renderer.Backends.SVG);
                            r.resize(546, 160);
                            const ctx = r.getContext();
                            const st = new Stave(10, 30, 780);
                            st.addClef(div.dataset.clef || 'treble');
                            st.setNoteStartX(st.getNoteStartX() + 40);
                            st.setContext(ctx).draw();

                            let voice;
                            if (!showBoth) {
                                voice = new Voice({ numBeats: 4, beatValue: 4 });
                                voice.addTickables([new StaveNote({ keys: [note1Key], duration: "w", auto_stem: true })]);
                            } else if (isHarmonic) {
                                voice = new Voice({ numBeats: 4, beatValue: 4 });
                                voice.addTickables([new StaveNote({ keys: [note1Key, note2Key], duration: "w", auto_stem: true })]);
                            } else {
                                voice = new Voice({ numBeats: 2, beatValue: 2 });
                                voice.addTickables([
                                    new StaveNote({ keys: [note1Key], duration: "h", auto_stem: true }),
                                    new StaveNote({ keys: [note2Key], duration: "h", auto_stem: true }),
                                ]);
                            }
                            Accidental.applyAccidentals([voice], 'C');
                            new Formatter().joinVoices([voice]).format([voice], 300);
                            voice.draw(ctx, st);
                            centerVexOutput(div);
                        };
                        drawInterval(false);
                        window._revealHarmonicMixed = drawInterval;

                        // For construction, remember the correct second note (e.g. "C#4")
                        // so we can play it back when revealing the answer.
                        if (isConstruction && notesParsed[1]) {
                            const [ltr, oct] = note2Key.split('/');
                            // Tone-friendly casing: "eb" -> "Eb", "f#" -> "F#".
                            const noteName = ltr.charAt(0).toUpperCase() + ltr.slice(1);
                            window._revealConstructionPlay = noteName + (oct || '4');
                        } else {
                            window._revealConstructionPlay = null;
                        }
                    } else if (noteType === 'chord') {
                        // Chord: show only the root note on the staff first; reveal the
                        // full stacked chord after the user answers.
                        const allKeys = (notesFromParams || '').split(',').filter(n => n.length > 0);
                        const rootKey = div.dataset.root || allKeys[0] || 'c/4';
                        const drawChord = (showAll) => {
                            div.innerHTML = '';
                            const r = new Renderer(div, Renderer.Backends.SVG);
                            r.resize(546, 160);
                            const ctx = r.getContext();
                            const st = new Stave(10, 30, 780);
                            st.addClef(div.dataset.clef || 'treble');
                            st.setNoteStartX(st.getNoteStartX() + 40);
                            st.setContext(ctx).draw();
                            const keys = (showAll && allKeys.length) ? allKeys : [rootKey];
                            const voice = new Voice({ numBeats: 4, beatValue: 4 });
                            voice.addTickables([new StaveNote({ keys, duration: "w", auto_stem: true })]);
                            Accidental.applyAccidentals([voice], 'C');
                            new Formatter().joinVoices([voice]).format([voice], 300);
                            voice.draw(ctx, st);
                            centerVexOutput(div);
                        };
                        drawChord(false);
                        window._revealHarmonicMixed = drawChord;
                    } else if (noteType === 'scale') {
                        // Scale: show only the starting note on the staff first; reveal the
                        // full scale (drawn as a sequence) after the user answers.
                        const allKeys = (notesFromParams || '').split(',').filter(n => n.length > 0);
                        const rootKey = allKeys[0] || div.dataset.root || 'c/4';
                        const drawScale = (showAll) => {
                            div.innerHTML = '';
                            // Size the staff to the full scale so every note stays on screen.
                            const noteCount = (showAll && allKeys.length) ? allKeys.length : 1;
                            const clefWidth = 60;
                            const staveWidth = Math.max(360, clefWidth + 40 + noteCount * 46);
                            const r = new Renderer(div, Renderer.Backends.SVG);
                            r.resize(staveWidth + 20, 160);
                            const ctx = r.getContext();
                            const st = new Stave(10, 30, staveWidth);
                            st.addClef(div.dataset.clef || 'treble');
                            st.setNoteStartX(st.getNoteStartX() + 20);
                            st.setContext(ctx).draw();
                            let voice;
                            if (showAll && allKeys.length) {
                                const notes = allKeys.map(k => new StaveNote({ keys: [k], duration: "q", auto_stem: true }));
                                voice = new Voice({ numBeats: notes.length, beatValue: 4 }).setMode(Voice.Mode.SOFT);
                                voice.addTickables(notes);
                            } else {
                                voice = new Voice({ numBeats: 4, beatValue: 4 });
                                voice.addTickables([new StaveNote({ keys: [rootKey], duration: "w", auto_stem: true })]);
                            }
                            Accidental.applyAccidentals([voice], 'C');
                            const formatWidth = Math.max(120, staveWidth - clefWidth - 50);
                            new Formatter().joinVoices([voice]).format([voice], formatWidth);
                            voice.draw(ctx, st);
                            centerVexOutput(div);
                        };
                        drawScale(false);
                        window._revealHarmonicMixed = drawScale;
                    } else {
                        // Single Note, Interval Direction, Melodic Interval, Construction
                        renderer.resize(546, 160);
                        const context = renderer.getContext();
                        const stave = new Stave(10, 30, 780);
                        stave.addClef(div.dataset.clef || 'treble');
                        stave.setNoteStartX(stave.getNoteStartX() + 40);
                        stave.setContext(context).draw();

                        if (notesFromParams) {
                            const notesParsed = notesFromParams.split(',');
                            const duration = notesParsed.length > 1 ? "h" : "1";
                            const staveNotes = notesParsed.map(note => new StaveNote({ keys: [note], duration: duration, auto_stem: true }));
                            const numBeats = notesParsed.length > 1 ? 2 : 4;
                            const voice = new Voice({ numBeats: numBeats, beatValue: notesParsed.length > 1 ? 2 : 4 });
                            voice.addTickables(staveNotes);
                            Accidental.applyAccidentals([voice], 'C');
                            new Formatter().joinVoices([voice]).format([voice], 300);
                            voice.draw(context, stave);
                            centerVexOutput(div);
                        }
                    }
                }
            }

            // ---- Initialize Interactions ----
            const totalQuestions = {{ $totalQuestions }};
            const currentPracticeIndex = {{ $currentPracticeIndex }};

            if (playButton && answerOptions) {
                const target = answerOptions.dataset.target;
                const practiceId = answerOptions.dataset.practiceId;
                const practiceType = answerOptions.dataset.type;
                const noteType = playButton.dataset.type;
                let isAnswered = false;

                if (typeof lucide !== 'undefined') {
                    lucide.createIcons();
                }

                // Play button click handler
                playButton.onclick = async function() {
                    await Tone.start();
                    playButton.disabled = true;
                    playButton.innerHTML = '<i data-lucide="volume-2" class="w-5 h-5"></i> Playing...';
                    playStatus.textContent = 'Playing...';
                    if (typeof lucide !== 'undefined') lucide.createIcons();

                    const playMode = this.dataset.playMode;

                    const resetPlayBtn = (label) => {
                        playButton.disabled = false;
                        playButton.innerHTML = `<i data-lucide="play" class="w-5 h-5"></i> ${label || 'Play Again'}`;
                        playStatus.textContent = 'Click to play again';
                        if (typeof lucide !== 'undefined') lucide.createIcons();
                    };

                    if (playMode === 'harmonic') {
                        // Harmonic interval: play two notes simultaneously
                        const notes = this.dataset.note.split(',');
                        window.HarmonivaAudio.playSimultaneous(notes, 2);
                        setTimeout(() => resetPlayBtn('Play Again'), 2500);
                    } else if (practiceType === 'interval_comparison') {
                        // Interval A then pause then Interval B
                        const intervalA = this.dataset.intervalA.split(',');
                        const intervalB = this.dataset.intervalB.split(',');
                        playStatus.textContent = 'Playing Interval A...';
                        window.HarmonivaAudio.playSequential(intervalA, 700, 1);
                        setTimeout(() => {
                            playStatus.textContent = 'Playing Interval B...';
                            window.HarmonivaAudio.playSequential(intervalB, 700, 1);
                        }, 2000);
                        setTimeout(() => resetPlayBtn('Play Again'), 4200);
                    } else if (playMode === 'chord') {
                        const notes = this.dataset.note.split(',').filter(n => n.length > 0);
                        const voicing = this.dataset.voicing || 'block';
                        if (voicing === 'arpeggiated') {
                            window.HarmonivaAudio.playArpeggio(notes, 300, 1.5);
                            const totalMs = (window.HarmonivaAudio.totalMs ? window.HarmonivaAudio.totalMs(notes, 300) : notes.length * 300) + 800;
                            setTimeout(() => resetPlayBtn('Play Again'), totalMs);
                        } else {
                            window.HarmonivaAudio.playSimultaneous(notes, 2);
                            setTimeout(() => resetPlayBtn('Play Again'), 2500);
                        }
                    } else if (playMode === 'scale') {
                        const notes = this.dataset.note.split(',').filter(n => n.length > 0);
                        window.HarmonivaAudio.playSequential(notes, 250, 0.8);
                        const totalMs = (window.HarmonivaAudio.totalMs ? window.HarmonivaAudio.totalMs(notes, 250) : notes.length * 250) + 500;
                        setTimeout(() => resetPlayBtn('Play Again'), totalMs);
                    } else if (playMode === 'rhythm') {
                        const rhythmNotes = this.dataset.note.split(',').filter(n => n.length > 0);
                        const totalMs = await playRhythmAudio(rhythmNotes, this.dataset.tempo, this.dataset.timeSig);
                        setTimeout(() => resetPlayBtn('Play Again'), totalMs + 400);
                    } else if (playMode === 'melodic_dictation') {
                        const notes = this.dataset.note.split(',').filter(n => n.length > 0);
                        const tempo = parseInt(this.dataset.tempo) || 60;
                        const noteMs = Math.round(60000 / tempo);
                        window.HarmonivaAudio.playSequential(notes, noteMs, 0.9);
                        const totalMs = notes.length * noteMs + 600;
                        setTimeout(() => resetPlayBtn('Play Again'), totalMs);
                    } else {
                        const notesParsed = this.dataset.note.split(',');
                        if (notesParsed.length === 1) {
                            window.HarmonivaAudio.playNote(notesParsed[0], 1.5);
                            setTimeout(() => resetPlayBtn('Play Again'), 2000);
                        } else {
                            window.HarmonivaAudio.playSequential(notesParsed, 700, 1);
                            setTimeout(() => resetPlayBtn('Play Again'), 2000);
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
                            isAnswered = true;
                            
                            // Show the note display when answer is submitted (for interval_comparison)
                            if (practiceType === 'interval_comparison') {
                                const noteDisplayContainer = document.getElementById('noteDisplayContainer');
                                if (noteDisplayContainer) {
                                    noteDisplayContainer.classList.remove('hidden');
                                    centerVexOutput(document.getElementById('output'));
                                }
                            }

                            // Reveal hidden staff notes after answering (harmonic, melodic,
                            // and construction intervals reveal the second note; chord
                            // reveals the full stacked chord; scale reveals the full sequence).
                            if ((practiceType === 'harmonic_interval' || practiceType === 'melodic_interval' || practiceType === 'interval_construction' || practiceType === 'chord' || practiceType === 'scale')
                                && typeof window._revealHarmonicMixed === 'function') {
                                window._revealHarmonicMixed(true);
                            }

                            // For construction, also play the correct second note on reveal.
                            if (practiceType === 'interval_construction' && window._revealConstructionPlay && window.HarmonivaAudio) {
                                window.HarmonivaAudio.playNote(window._revealConstructionPlay, 1.5);
                            }
                            
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
                            } else if (practiceType === 'rhythm' || practiceType === 'melodic_dictation') {
                                this.textContent = answer.replace(/,/g, ' → ');
                            } else if (practiceType === 'single_note' || practiceType === 'interval_construction') {
                                this.textContent = noteToSymbol(answer);
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
                                    : (practiceType === 'rhythm' || practiceType === 'melodic_dictation')
                                        ? target.replace(/,/g, ' → ')
                                        : (practiceType === 'single_note' || practiceType === 'interval_construction')
                                            ? noteToSymbol(target)
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
                // Rhythm Dictation uses a build-it-yourself UI instead of .answer-btn options.
                if (practiceType === 'rhythm') {
                    setupRhythmBuilder(target);
                }
            }
        };

        // Initialize immediately if DOM already ready, else wait for DOMContentLoaded.
        // This covers the case where livewire:init already fired before the script ran.
        if (document.readyState !== 'loading') {
            window.initPracticeMixed();
        } else {
            document.addEventListener('DOMContentLoaded', function() {
                window.initPracticeMixed();
            });
        }

        // Also run on livewire:init in case Livewire hasn't fired yet.
        document.addEventListener('livewire:init', function() {
            window.initPracticeMixed();
        });

        // Re-run when practice is updated (Next clicked). Registered once globally.
        if (!window._practiceMixedUpdatedRegistered) {
            window._practiceMixedUpdatedRegistered = true;
            document.addEventListener('livewire:init', function() {
                Livewire.on('practice-updated', () => {
                    setTimeout(() => {
                        window.initPracticeMixed();
                    }, 100);
                });
            }, { once: true });
        }
    </script>
    @endif

</main>
