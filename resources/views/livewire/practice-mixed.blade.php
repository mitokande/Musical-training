<!-- Main Content -->
<main wire:id="practice-mixed-{{ $currentPracticeIndex }}" class="max-w-2xl mx-auto px-3 sm:px-4 py-4 sm:py-6">

    {{-- Ahead of both branches, and outside them: the results screen and the
         question screen both call pt() and musicLabel(), and the question
         script runs initPracticeMixed() synchronously as soon as it is parsed.
         These two partials used to sit inside the results branch alone, which
         left pt undefined on the question screen — the play handler threw on
         its own first line and the button stayed disabled with no feedback. --}}
    @include('livewire.partials.practice-i18n')
    @include('livewire.partials.music-labels')

    @if($showResults && $coachNotes)
    <!-- AI Coach Results Page -->
    <div class="card overflow-hidden mb-6">
        <!-- Results Header -->
        <div class="bg-gradient-to-r from-purple-600 via-purple-500 to-indigo-500 p-8 text-center">
            <div class="w-20 h-20 mx-auto mb-4 rounded-full bg-white/20 backdrop-blur flex items-center justify-center">
                <i data-lucide="trophy" class="w-10 h-10 text-white"></i>
            </div>
            <h1 class="text-2xl font-bold text-white mb-2">{{ __('app.practice_ui.mixed.complete') }}</h1>
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
                    <p class="text-xs text-gray-500 mt-1">{{ __('app.practice.score') }}</p>
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
                    <p class="text-xs text-green-600/70">{{ __('app.practice_ui.common.correct') }}</p>
                </div>
                <div class="text-center p-4 bg-red-50 rounded-xl">
                    <div class="w-10 h-10 mx-auto mb-2 rounded-full bg-red-100 flex items-center justify-center">
                        <i data-lucide="x" class="w-5 h-5 text-red-600"></i>
                    </div>
                    <span class="text-2xl font-bold text-red-600">{{ $totalQuestions - $correctCount }}</span>
                    <p class="text-xs text-red-600/70">{{ __('app.profile.total_incorrect') }}</p>
                </div>
                <div class="text-center p-4 bg-yellow-50 rounded-xl">
                    <div class="w-10 h-10 mx-auto mb-2 rounded-full bg-yellow-100 flex items-center justify-center">
                        <i data-lucide="sparkles" class="w-5 h-5 text-yellow-600"></i>
                    </div>
                    <span class="text-2xl font-bold text-yellow-600">+{{ $xpEarned }}</span>
                    <p class="text-xs text-yellow-600/70">{{ __('app.practice_ui.mixed.xp_earned') }}</p>
                </div>
            </div>
            
            <!-- AI Coach Summary -->
            <div class="bg-gradient-to-r from-purple-50 to-indigo-50 rounded-xl p-5 mb-6 border border-purple-100">
                <div class="flex items-start gap-3">
                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-purple-500 to-indigo-500 flex items-center justify-center flex-shrink-0">
                        <i data-lucide="bot" class="w-5 h-5 text-white"></i>
                    </div>
                    <div>
                        <h3 class="font-semibold text-purple-900 mb-1">{{ __('app.practice_ui.mixed.ai_summary') }}</h3>
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
                    {{ __('app.practice_ui.mixed.strengths') }}
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
                    {{ __('app.practice_ui.mixed.improve') }}
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
                    {{ __('app.practice_ui.mixed.suggestions') }}
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
                        <h3 class="font-semibold text-amber-900 mb-1">{{ __('app.progress.keep_going') }}</h3>
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
                    {{ __('app.practice_ui.mixed.new_session') }}
                </a>
                <a href="{{ locale_url('/learn') }}" class="flex-1 font-semibold py-3 px-6 rounded-lg flex items-center justify-center gap-2 border-2 border-gray-200 text-gray-700 hover:bg-gray-50 transition-colors">
                    <i data-lucide="book-open" class="w-5 h-5"></i>
                    {{ __('app.learn.title') }}
                </a>
                <a href="/dashboard" class="flex-1 font-semibold py-3 px-6 rounded-lg flex items-center justify-center gap-2 border-2 border-gray-200 text-gray-700 hover:bg-gray-50 transition-colors">
                    <i data-lucide="home" class="w-5 h-5"></i>
                    {{ __('app.nav.dashboard') }}
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
                    @php
                        $hType = $currentPractice['type'] ?? '';
                        $hData = $currentPractice['data'] ?? [];
                        $exerciseNames = [
                            'single_note' => 'Single Note',
                            'interval_direction' => 'Interval Direction',
                            'interval_comparison' => 'Interval Comparison',
                            'melodic_interval' => 'Melodic Interval',
                            'harmonic_interval' => 'Harmonic Interval',
                            'interval_construction' => 'Interval Construction',
                            'chord' => 'Chords',
                            'scale' => 'Scales & Modes',
                            'melodic_dictation' => 'Melodic Dictation',
                        ];
                        if ($hType === 'rhythm') {
                            $exerciseNames['rhythm'] = match ($hData['rhythm_mode'] ?? 'build') {
                                'recognition' => 'Rhythm Recognition',
                                'reading' => 'Rhythm Reading',
                                default => 'Rhythm Dictation',
                            };
                        }
                    @endphp
                    <h1 class="text-base sm:text-lg font-bold text-white leading-tight">{{ $exerciseNames[$hType] ?? $sessionTitle }}</h1>
                    <p class="text-white/85 text-xs sm:text-sm mt-1 font-medium">
                        @if($hType === 'interval_construction')
                            Build a <strong>{{ $hData['interval'] ?? '' }}</strong> {{ ($hData['direction'] ?? 'ascending') === 'descending' ? 'below' : 'above' }} <strong>{{ strtoupper($hData['note1'] ?? '') }}</strong>
                        @elseif($hType === 'chord')
                            Identify the chord type for <strong>{{ \App\Services\MusicTheoryService::toDisplaySymbol($hData['root_note'] ?? '') }}</strong>
                        @elseif($hType === 'scale')
                            Identify the scale starting on <strong>{{ \App\Services\MusicTheoryService::toDisplaySymbol($hData['root_note'] ?? '') }}</strong>
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
                            @php $hRhythmMode = $hData['rhythm_mode'] ?? 'build'; @endphp
                            @if($hRhythmMode === 'recognition')
                                Rhythm Recognition — listen and identify the rhythm pattern
                            @elseif($hRhythmMode === 'reading')
                                Rhythmic Reading — tap the rhythm you see on the staff
                            @else
                                Rhythm Dictation — listen, then build the rhythm you heard
                            @endif
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
                <div class="text-center py-8 text-gray-500">{{ __('app.practice_ui.mixed.no_questions') }}</div>
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
                @php $rhythmMode = $practice['rhythm_mode'] ?? 'build'; @endphp
                @if($rhythmMode === 'recognition')
                    {{-- Recognition: nothing to reveal up top — show meter/tempo info (mirrors
                         the Exercise Setup Rhythm Recognition header panel) --}}
                    <div class="w-full bg-gray-50 border-2 border-dashed border-gray-200 rounded-xl flex items-center justify-center py-5">
                        <div class="text-center">
                            <p class="text-sm text-gray-500 mb-1">{{ __('app.practice_ui.rhythm.time_signature') }}</p>
                            <div class="text-5xl font-bold text-gray-800">{{ $practice['time_signature'] ?? '4/4' }}</div>
                            <p class="text-sm text-gray-400 mt-1">{{ __('app.practice_ui.rhythm.tempo_label') }} {{ $practice['tempo'] ?? 80 }} BPM • {{ $practice['bars'] ?? 1 }} bar(s)</p>
                        </div>
                    </div>
                @elseif($rhythmMode === 'reading')
                    {{-- Reading: the rhythm to tap is always visible on the staff --}}
                    <div id="rhythmReadingStaff"
                         class="min-h-[110px] w-full bg-white border-2 border-gray-200 rounded-xl p-2 overflow-x-auto flex items-center justify-center transition-colors duration-300"></div>
                    <div class="text-center text-sm text-gray-500">
                        <span class="font-bold text-gray-700 text-base">{{ $practice['time_signature'] ?? '4/4' }}</span>
                        <span class="mx-2 text-gray-300">•</span>
                        {{ __('app.practice_ui.rhythm.tempo_label') }} <span class="font-semibold text-gray-700">{{ $practice['tempo'] ?? 80 }} BPM</span>
                    </div>
                @else
                    {{-- Built rhythm on a single-line staff (top of the exercise) --}}
                    <div id="rhythmTable"
                         class="min-h-[110px] w-full bg-white border-2 border-dashed border-gray-300 rounded-xl p-2 overflow-x-auto flex items-center justify-center"></div>
                    {{-- Correct-answer reveal (shown only after an incorrect Check) --}}
                    <div id="rhythmReveal" class="hidden mt-3">
                        <p class="text-xs font-semibold text-gray-500 text-center mb-1">{{ __('app.practice_ui.rhythm.correct_rhythm') }}</p>
                        <div id="rhythmRevealRow"
                             class="w-full bg-green-50 border border-green-200 rounded-xl p-2 overflow-x-auto flex items-center justify-center"></div>
                    </div>
                @endif
            @elseif($type === 'melodic_dictation')
                @php
                    $mdNotes = $practice['notes'] ?? [];
                    if (is_string($mdNotes)) $mdNotes = json_decode($mdNotes, true) ?? [];
                @endphp
                <div class="bg-indigo-50 border border-indigo-200 rounded-xl p-4 flex items-center gap-3">
                    <i data-lucide="info" class="w-4 h-4 text-indigo-500 flex-shrink-0"></i>
                    <div class="text-xs text-indigo-700 leading-relaxed">
                        <strong>{{ __('app.practice_ui.mixed.key_label') }}</strong> {{ $practice['key_signature'] ?? 'C' }} &nbsp;·&nbsp;
                        <strong>{{ __('app.practice_ui.mixed.clef_label') }}</strong> {{ ucfirst($practice['clef'] ?? 'treble') }} &nbsp;·&nbsp;
                        <strong>{{ __('app.practice_ui.rhythm.tempo_label') }}</strong> {{ $practice['tempo'] ?? 60 }} BPM &nbsp;·&nbsp;
                        <strong>{{ __('app.practice_ui.mixed.notes_label') }}</strong> {{ count($mdNotes) }}
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

                // First single-note question of the session: Play sounds the fixed
                // reference note (C4 — "Do", shown on the staff) right before the
                // question note. Later single-note questions skip it — each heard
                // note is the reference for the next, so answers work by comparison.
                $firstSingleNoteIdx = collect($practices)->search(fn ($p) => ($p['type'] ?? '') === 'single_note');
                $playsReference = $type === 'single_note' && $firstSingleNoteIdx === $currentPracticeIndex;
            @endphp
            {{-- Rhythm 'build' mode has its own combined info/play card below; the other
                 two rhythm modes (recognition, reading) use this generic play card. --}}
            @if($type !== 'rhythm' || ($practice['rhythm_mode'] ?? 'build') !== 'build')
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
                            @if($playsReference)
                                data-reference-note="{{ \App\Livewire\PracticeMixed::REFERENCE_NOTE }}"
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
                                {{ __('app.practice_ui.common.next') }}
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
                                <span wire:loading.remove wire:target="generateCoachNotes">{{ __('app.practice_ui.common.finish') }}</span>
                                <span wire:loading wire:target="generateCoachNotes">{{ __('app.practice_ui.mixed.generating_ai') }}</span>
                            </button>
                        @endif
                </div>
                <p id="playStatus" class="text-xs text-gray-400">{{ __('app.practice_ui.mixed.listen_note') }}</p>
            </div>
            @endif

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
                        {{ __('app.practice_ui.direction.ascending') }}
                    </button>
                    <button class="answer-btn rounded-xl p-4 text-center font-semibold text-gray-700 flex items-center justify-center gap-2" data-answer="descending">
                        <i data-lucide="trending-down" class="w-5 h-5 text-red-500"></i>
                        {{ __('app.practice_ui.direction.descending') }}
                    </button>
                </div>
            @elseif($type === 'interval_comparison')
                <div id="answerOptions" class="grid grid-cols-2 gap-4"
                     data-target="{{ $practice['target'] ?? '' }}"
                     data-practice-id="{{ $practice['id'] ?? 0 }}"
                     data-type="{{ $type }}">
                    <button class="answer-btn rounded-xl p-4 text-center font-semibold text-gray-700 flex flex-col items-center justify-center gap-1" data-answer="a">
                        <span class="text-2xl font-bold text-green-600">A</span>
                        <span class="text-sm text-gray-500">{{ __('app.practice_ui.comparison.a_larger') }}</span>
                    </button>
                    <button class="answer-btn rounded-xl p-4 text-center font-semibold text-gray-700 flex flex-col items-center justify-center gap-1" data-answer="b">
                        <span class="text-2xl font-bold text-blue-600">B</span>
                        <span class="text-sm text-gray-500">{{ __('app.practice_ui.comparison.b_larger') }}</span>
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
                            {{ str_replace('##', 'x', $noteOption) }}
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
                        {{-- data-answer stays canonical; only the label is localised. --}}
                        <button class="answer-btn rounded-xl p-4 text-center font-semibold text-gray-700 text-sm"
                                data-answer="{{ strtolower($chordOpt) }}">
                            {{ music_label($chordOpt, 'chord') }}
                        </button>
                    @endforeach
                </div>
            @elseif($type === 'scale')
                @php
                    $scaleTarget = strtolower($practice['scale_type'] ?? '');
                    $scaleCorrect = $practice['scale_type'] ?? 'Major';
                    $allScales = ['Major','Natural Minor','Harmonic Minor','Melodic Minor','Dorian','Phrygian','Lydian','Mixolydian','Locrian','Major Pentatonic','Minor Pentatonic','Blues Scale','Chromatic Scale','Whole Tone Scale'];
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
                        {{-- data-answer stays canonical; only the label is localised. --}}
                        <button class="answer-btn rounded-xl p-4 text-center font-semibold text-gray-700 text-sm"
                                data-answer="{{ strtolower($scaleOpt) }}">
                            {{ music_label($scaleOpt, 'scale') }}
                        </button>
                    @endforeach
                </div>
            @elseif($type === 'rhythm')
                @php
                    $rhythmMode = $practice['rhythm_mode'] ?? 'build';
                    $noteValsArr = $practice['note_values'] ?? [];
                    if (is_string($noteValsArr)) $noteValsArr = json_decode($noteValsArr, true) ?? [];
                    $rhythmAnswerTarget = implode(',', $noteValsArr);
                    $rhythmAllowed = $practice['allowed_values'] ?? [];
                    if (is_string($rhythmAllowed)) $rhythmAllowed = json_decode($rhythmAllowed, true) ?? [];
                    if (empty($rhythmAllowed)) {
                        $rhythmAllowed = ['whole','half','quarter','eighth','sixteenth','dotted-half','dotted-quarter','dotted-eighth','half_rest','quarter_rest','eighth_rest'];
                    }
                @endphp
                @if($rhythmMode === 'recognition')
                    {{-- ── Rhythm Recognition: listen, then pick the pattern among four staves
                         (ported from the Exercise Setup rhythm recognition mode) ── --}}
                    @php
                        $recOther = $practice['other_options'] ?? [];
                        if (is_string($recOther)) $recOther = json_decode($recOther, true) ?? [];
                        $recOptions = [['value' => $rhythmAnswerTarget, 'notes' => array_values($noteValsArr)]];
                        foreach ($recOther as $recOpt) {
                            $recNotes = is_array($recOpt) ? $recOpt : explode(',', (string) $recOpt);
                            $recOptions[] = ['value' => implode(',', $recNotes), 'notes' => array_values($recNotes)];
                        }
                        shuffle($recOptions);
                    @endphp
                    <p class="text-sm text-gray-500 text-center">{{ __('app.practice_ui.rhythm.which_rhythm') }}</p>
                    <div id="answerOptions" class="grid grid-cols-1 sm:grid-cols-2 gap-3"
                         data-target="{{ $rhythmAnswerTarget }}"
                         data-practice-id="{{ $practice['id'] ?? 0 }}"
                         data-type="rhythm-recognition">
                        @foreach($recOptions as $recOpt)
                            <button class="rhythm-rec-btn card p-2 text-left transition-all hover:shadow-md border-2 border-gray-200 rounded-xl bg-white disabled:cursor-not-allowed"
                                    data-answer="{{ $recOpt['value'] }}"
                                    data-notes='@json($recOpt['notes'])'>
                                <div class="staff-container w-full rounded overflow-hidden min-h-[83px] sm:min-h-[104px]"></div>
                            </button>
                        @endforeach
                    </div>
                @elseif($rhythmMode === 'reading')
                    {{-- ── Rhythmic Reading: tap the printed rhythm in time with the metronome
                         (ported from the Exercise Setup rhythm reading mode) ── --}}
                    <div id="answerOptions" class="flex flex-col items-center gap-3"
                         data-target="{{ $rhythmAnswerTarget }}"
                         data-practice-id="{{ $practice['id'] ?? 0 }}"
                         data-type="rhythm-reading">
                        <p class="text-xs text-gray-400">
                            {{-- Same key as practice-rhythm: shortcuts sit mid-sentence
                                 in English, before the verb in Turkish. --}}
                            @php $mxKbd = 'px-1.5 py-0.5 bg-gray-100 border border-gray-300 rounded text-xs font-mono'; @endphp
                            {!! __('app.practice_ui.rhythm.kbd_hint', [
                                'tab' => '<kbd class="'.$mxKbd.'">Tab</kbd>',
                                'space' => '<kbd class="'.$mxKbd.'">'.__('app.practice_ui.common.key_space').'</kbd>',
                            ]) !!}
                        </p>
                        <button id="rhythmTapButton" type="button"
                            class="w-full max-w-[16rem] h-20 rounded-2xl font-bold text-xl select-none transition-all
                                   bg-gradient-to-b from-amber-400 to-amber-500 text-white shadow-md
                                   active:scale-95"
                            style="touch-action: manipulation; opacity: 0.35; cursor: not-allowed;"
                            disabled>
                            <i data-lucide="hand" class="w-6 h-6 inline mr-2"></i>
                            TAP
                        </button>
                    </div>
                @else

                {{-- ── Info / Play card: LEFT = compact info (~38%), RIGHT = play button (~62%) ── --}}
                <div class="bg-gray-50 border border-gray-200 rounded-xl p-4">
                    <div class="flex flex-col sm:flex-row gap-4">

                        {{-- LEFT: time sig (20% smaller) + bar-fill meter (20% larger) --}}
                        <div class="flex flex-col gap-2 justify-center w-full sm:w-[38%]">
                            {{-- Time sig / tempo / bars — 20% smaller (text-sm vs original text-lg) --}}
                            <div class="text-xs text-gray-500">
                                <span class="text-sm font-bold text-gray-800">{{ $practice['time_signature'] ?? '4/4' }}</span>
                                <span class="mx-1" style="color:#d1d5db">·</span>{{ $practice['tempo'] ?? 80 }} BPM
                                <span class="mx-1" style="color:#d1d5db">·</span>{{ $practice['bars'] ?? 1 }} bar(s)
                            </div>
                            {{-- Bar-fill meter — 20% larger (text-sm labels, h-3 bar vs original text-xs, h-2) --}}
                            <div>
                                <p class="text-sm font-semibold text-gray-600 mb-1">Bar &nbsp;&nbsp;<span id="rhythmFillLabel" class="font-bold">0 / 0 beats</span></p>
                                <div class="h-3 rounded-full bg-gray-200 overflow-hidden" style="width:80%">
                                    <div id="rhythmFillBar" class="h-full rounded-full bg-amber-400 transition-all duration-200" style="width:0%"></div>
                                </div>
                            </div>
                        </div>

                        {{-- RIGHT: Play button — 52% width (10% narrower = button shifts ~10% left) --}}
                        <div class="flex flex-col items-center justify-center gap-2 w-full sm:w-[52%] border-t sm:border-t-0 sm:border-l border-gray-200 pt-3 sm:pt-0 sm:pl-4">
                            <div class="flex items-center gap-3">
                                <button
                                    id="playButton"
                                    class="btn-primary text-white font-semibold py-2.5 px-6 rounded-lg flex items-center gap-2 hover:shadow-lg transition-shadow text-base"
                                    data-note="{{ $playNote }}"
                                    data-tempo="{{ $practice['tempo'] ?? 80 }}"
                                    data-time-sig="{{ $practice['time_signature'] ?? '4/4' }}"
                                    data-type="rhythm"
                                    data-play-mode="rhythm"
                                >
                                    <i data-lucide="play" class="w-5 h-5"></i>
                                    Play Rhythm
                                </button>
                                @if ($currentPracticeIndex < ($totalQuestions - 1))
                                    <button
                                        id="nextPracticeBtn"
                                        wire:click="getNextPractice"
                                        class="hidden font-semibold py-2.5 px-5 rounded-lg flex items-center gap-2 hover:shadow-lg transition-shadow text-sm bg-blue-100 text-blue-700 border-2 border-blue-300 hover:bg-blue-200"
                                    >
                                        <i data-lucide="arrow-right" class="w-4 h-4"></i>
                                        {{ __('app.practice_ui.common.next') }}
                                    </button>
                                @else
                                    <button
                                        wire:click="generateCoachNotes"
                                        wire:loading.attr="disabled"
                                        id="finishPracticeBtn"
                                        class="hidden font-semibold py-2.5 px-5 rounded-lg flex items-center gap-2 hover:shadow-lg transition-shadow text-sm bg-green-100 text-green-700 border-2 border-green-300 hover:bg-green-200 disabled:opacity-70"
                                    >
                                        <span wire:loading.remove wire:target="generateCoachNotes">
                                            <i data-lucide="check" class="w-4 h-4"></i>
                                        </span>
                                        <svg wire:loading wire:target="generateCoachNotes" class="animate-spin w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        <span wire:loading.remove wire:target="generateCoachNotes">{{ __('app.practice_ui.common.finish') }}</span>
                                        <span wire:loading wire:target="generateCoachNotes">{{ __('app.practice_ui.mixed.generating_ai') }}</span>
                                    </button>
                                @endif
                            </div>
                            <p id="playStatus" class="text-xs text-gray-400 text-center">{{ __('app.practice_ui.mixed.listen_note') }}</p>
                        </div>
                    </div>
                </div>

                {{-- Instruction text — below card, above palette --}}
                <p class="text-sm text-gray-500 text-center" style="margin-top:15px; margin-bottom:15px;">{{ __('app.practice_ui.rhythm.rebuild_hint') }}</p>

                {{-- Answer options wrapper + note palette + controls (all below the card) --}}
                <div id="answerOptions"
                     data-target="{{ $rhythmAnswerTarget }}"
                     data-practice-id="{{ $practice['id'] ?? 0 }}"
                     data-type="rhythm"
                     data-allowed="{{ implode(',', $rhythmAllowed) }}"
                     data-bars="{{ $practice['bars'] ?? 1 }}">
                    {{-- Single-row note palette — buttons filled by JS (no labels, w-12 h-12) --}}
                    <div id="rhythmPalette" class="flex overflow-x-auto pb-1 gap-2 items-center justify-center" style="flex-wrap:nowrap;"></div>
                    {{-- Controls --}}
                    <div class="flex flex-wrap items-center justify-center gap-2" style="margin-top:35px;">
                        <button type="button" id="rhythmPlayMineBtn"
                                class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-600 hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed">
                            <i data-lucide="volume-2" class="w-4 h-4"></i> {{ __('app.practice_ui.rhythm.play_mine') }}
                        </button>
                        <button type="button" id="rhythmDeleteBtn"
                                class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-600 hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed">
                            <i data-lucide="delete" class="w-4 h-4"></i> {{ __('app.practice_ui.common.delete_last') }}
                        </button>
                        <button type="button" id="rhythmClearBtn"
                                class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-600 hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed">
                            <i data-lucide="eraser" class="w-4 h-4"></i> {{ __('app.practice_ui.common.clear') }}
                        </button>
                        <button type="button" id="rhythmCheckBtn"
                                class="btn-primary inline-flex items-center gap-2 rounded-lg px-6 py-2.5 text-sm font-semibold text-white hover:shadow-lg transition-shadow disabled:opacity-40 disabled:cursor-not-allowed">
                            <i data-lucide="check" class="w-4 h-4"></i> {{ __('app.practice_ui.common.check') }}
                        </button>
                    </div>
                </div>
                @endif
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
            'whole_rest':     { label: 'W. rest',  svg: '<line x1="4" y1="17" x2="22" y2="17" stroke="currentColor" stroke-width="1.2"/><rect x="7" y="17" width="12" height="5.5" fill="currentColor"/>' },
            'half_rest':      { label: '½ rest',   svg: '<line x1="4" y1="22" x2="22" y2="22" stroke="currentColor" stroke-width="1.2"/><rect x="7" y="16.5" width="12" height="5.5" fill="currentColor"/>' },
            'quarter_rest':   { label: 'Qtr rest', svg: '<path d="M9 8 L15 17 L10 21 L16 29 C12 27 9 29 11 33" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round"/>' },
            'eighth_rest':    { label: '8th rest', svg: '<circle cx="9" cy="15" r="2.6" fill="currentColor"/><path d="M11 14 L15 28" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>' },
            // Triplet: three beamed eighths with a "3" above. Clicking adds three triplet-eighths.
            'triplet':        { label: 'Triplet', svg: '<text x="13" y="9" font-size="9" font-weight="700" text-anchor="middle" fill="currentColor">3</text><rect x="3" y="13" width="20" height="2.4" fill="currentColor"/><rect x="3.6" y="14" width="1.6" height="14" fill="currentColor"/><rect x="12.2" y="14" width="1.6" height="14" fill="currentColor"/><rect x="20.8" y="14" width="1.6" height="14" fill="currentColor"/><ellipse cx="4.4" cy="29" rx="3.1" ry="2.2" transform="rotate(-20 4.4 29)" fill="currentColor"/><ellipse cx="13" cy="29" rx="3.1" ry="2.2" transform="rotate(-20 13 29)" fill="currentColor"/><ellipse cx="21.6" cy="29" rx="3.1" ry="2.2" transform="rotate(-20 21.6 29)" fill="currentColor"/>' },
        };

        // Build the inner HTML (glyph only, no label) for a rhythm note value.
        function rhythmGlyphMarkup(value) {
            const g = RHYTHM_GLYPHS[value];
            if (!g) return value;
            return `<svg viewBox="0 0 26 40" width="22" height="36" class="block mx-auto">${g.svg}</svg>`;
        }

        // Map a rhythm note value to a VexFlow base duration code. Dots and rests are
        // applied separately (dotted-* gets a Dot modifier; *_rest renders a rest glyph).
        const RHYTHM_VF_DURATION = {
            'whole': 'w', 'half': 'h', 'quarter': 'q', 'eighth': '8', 'sixteenth': '16',
            'dotted-half': 'h', 'dotted-quarter': 'q', 'dotted-eighth': '8',
            'triplet-eighth': '8',
            'whole_rest': 'wr', 'half_rest': 'hr', 'quarter_rest': 'qr', 'eighth_rest': '8r',
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
            const isOption = !!container.closest('#answerOptions');
            // Use the container's full rendered width so the staff spans edge-to-edge.
            const containerW = Math.max(300, (container.offsetWidth || 500) - 20);
            // Answer options always render at the button's width: glyphs keep the
            // standard VexFlow size on every option and the formatter compresses
            // note spacing to fit, instead of the SVG growing with the note count
            // and being shrunk down (which made note sizes vary between options).
            const width = isOption
                ? Math.max(200, container.offsetWidth || 300)
                : Math.max(containerW, sigWidth + 24 + Math.max(1, n) * noteWidth);
            // Answer-option staves shrink 20% on mobile (single-column layout).
            const height = (window.innerWidth < 640 && isOption) ? 83 : 104;

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

            const notes = sequence.map((v, i) => {
                const dur = RHYTHM_VF_DURATION[v] || 'q';
                const note = new StaveNote({ keys: ['b/4'], duration: dur, auto_stem: true });
                if (v.startsWith('dotted-')) {
                    try { Dot.buildAndAttach([note], { all: true }); } catch (e) {}
                }
                // color: uniform string, or per-token array (reading mode marks each
                // note green/red by tap accuracy; null entries keep the default ink).
                const c = Array.isArray(color) ? color[i] : color;
                if (c) note.setStyle({ fillStyle: c, strokeStyle: c });
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
                    fillLabel.textContent = pt('beats_progress', {done: fmt(sum / beatTicks), total: fmt(totalBeats)})
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
                b.className = 'rhythm-note-btn w-12 h-12 rounded-xl bg-[#2a7898] text-white flex items-center justify-center hover:bg-[#23698a] active:scale-95 transition disabled:opacity-40 disabled:cursor-not-allowed';
                b.dataset.value = v;
                b.innerHTML = rhythmGlyphMarkup(v);
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
                    playMineBtn.innerHTML = '<i data-lucide="volume-2" class="w-4 h-4"></i> ' + pt('playing');
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
                checkBtn.innerHTML = '<i data-lucide="loader-2" class="w-4 h-4 animate-spin"></i> ' + pt('checking');
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
                        checkBtn.innerHTML = '<i data-lucide="check" class="w-4 h-4"></i> ' + pt('correct_label');
                        table.classList.remove('border-gray-300');
                        table.classList.add('border-green-400', 'bg-green-50');
                        feedbackMessage.textContent = pt('correct_well_done');
                        feedbackMessage.classList.remove('hidden', 'bg-red-100', 'text-red-700');
                        feedbackMessage.classList.add('bg-green-100', 'text-green-700');
                        const xpEl = document.getElementById('xpEarned');
                        if (xpEl) xpEl.textContent = (parseInt(xpEl.textContent) || 0) + 10;
                        const sc = document.getElementById('scoreCorrect');
                        if (sc) sc.textContent = (parseInt(sc.textContent) || 0) + 1;
                    } else {
                        checkBtn.innerHTML = '<i data-lucide="x" class="w-4 h-4"></i> ' + pt('incorrect_label');
                        table.classList.remove('border-gray-300');
                        table.classList.add('border-red-400', 'bg-red-50');
                        feedbackMessage.textContent = pt('not_quite_rhythm');
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
                    checkBtn.innerHTML = '<i data-lucide="check" class="w-4 h-4"></i> ' + pt('check_label');
                    if (typeof lucide !== 'undefined') lucide.createIcons();
                }
            };

            renderTable();
            if (typeof lucide !== 'undefined') lucide.createIcons();
        }

        // ── Rhythm Recognition: listen, then pick the heard pattern among four staves ──
        // Ported from the Exercise Setup rhythm recognition mode; scoring goes through
        // the shared answerPractice() Livewire action like every other question type.
        function setupRhythmRecognition(target) {
            const playButton        = document.getElementById('playButton');
            const playStatus        = document.getElementById('playStatus');
            const nextButton        = document.getElementById('nextPracticeBtn');
            const finishPracticeBtn = document.getElementById('finishPracticeBtn');
            const feedbackMessage   = document.getElementById('feedbackMessage');
            const optionBtns        = Array.from(document.querySelectorAll('.rhythm-rec-btn'));
            if (!optionBtns.length) return;

            const timeSig = (playButton && playButton.dataset.timeSig) || '4/4';
            let answered = false;

            const drawOption = (btn, color) => {
                try {
                    const noteArr = JSON.parse(btn.dataset.notes || '[]');
                    const container = btn.querySelector('.staff-container');
                    if (container) drawRhythmStaff(container, noteArr, timeSig, color || null);
                } catch (e) {}
            };
            // Small delay so containers have their final width before VexFlow measures them.
            setTimeout(() => optionBtns.forEach(b => drawOption(b)), 120);

            optionBtns.forEach(btn => {
                btn.onclick = async function() {
                    if (answered) return;
                    const answer = this.dataset.answer;
                    optionBtns.forEach(b => b.disabled = true);
                    try {
                        const data = await @this.call('answerPractice', 'rhythm', answer, target);
                        answered = true;

                        if (playButton) playButton.classList.add('hidden');
                        if (playStatus) playStatus.classList.add('hidden');
                        if (nextButton) nextButton.classList.remove('hidden');
                        if (finishPracticeBtn) finishPracticeBtn.classList.remove('hidden');

                        if (data.is_correct) {
                            this.classList.remove('border-gray-200');
                            this.classList.add('border-green-400', 'bg-green-50');
                            drawOption(this, '#16a34a');
                            feedbackMessage.textContent = pt('correct_well_done');
                            feedbackMessage.classList.remove('hidden', 'bg-red-100', 'text-red-700');
                            feedbackMessage.classList.add('bg-green-100', 'text-green-700');
                            const xpEl = document.getElementById('xpEarned');
                            if (xpEl) xpEl.textContent = (parseInt(xpEl.textContent) || 0) + 10;
                            const sc = document.getElementById('scoreCorrect');
                            if (sc) sc.textContent = (parseInt(sc.textContent) || 0) + 1;
                        } else {
                            this.classList.remove('border-gray-200');
                            this.classList.add('border-red-400', 'bg-red-50');
                            drawOption(this, '#dc2626');
                            optionBtns.forEach(b => {
                                if (b.dataset.answer === target) {
                                    b.classList.remove('border-gray-200');
                                    b.classList.add('border-green-400', 'bg-green-50');
                                    drawOption(b, '#16a34a');
                                }
                            });
                            feedbackMessage.textContent = pt('incorrect_pattern');
                            feedbackMessage.classList.remove('hidden', 'bg-green-100', 'text-green-700');
                            feedbackMessage.classList.add('bg-red-100', 'text-red-700');
                        }
                        const st = document.getElementById('scoreTotal');
                        if (st) st.textContent = (parseInt(st.textContent) || 0) + 1;
                        if (typeof lucide !== 'undefined') lucide.createIcons();
                    } catch (e) {
                        console.error('Error checking rhythm recognition:', e);
                        optionBtns.forEach(b => b.disabled = false);
                        answered = false;
                    }
                };
            });
        }

        // ── Rhythmic Reading: tap the printed rhythm in time with the metronome ──
        // Ported from the Exercise Setup rhythm reading mode. Playback is metronome
        // clicks only (the student reads the staff, so the rhythm itself is never
        // played). Tap timing is evaluated locally (±20% of a quarter note) and the
        // result is recorded through the shared answerPractice() Livewire action.
        function setupRhythmReading(target) {
            if (window._rhythmReadingKeyCleanup) { window._rhythmReadingKeyCleanup(); window._rhythmReadingKeyCleanup = null; }

            const playButton        = document.getElementById('playButton');
            const playStatus        = document.getElementById('playStatus');
            const nextButton        = document.getElementById('nextPracticeBtn');
            const finishPracticeBtn = document.getElementById('finishPracticeBtn');
            const feedbackMessage   = document.getElementById('feedbackMessage');
            const tapButton         = document.getElementById('rhythmTapButton');
            const staffBox          = document.getElementById('rhythmReadingStaff');
            if (!playButton || !tapButton) return;

            const notes   = (playButton.dataset.note || '').split(',').filter(Boolean);
            const tempo   = parseInt(playButton.dataset.tempo) || 80;
            const timeSig = playButton.dataset.timeSig || '4/4';
            const num     = parseInt(timeSig.split('/')[0]) || 4;
            const den     = parseInt(timeSig.split('/')[1]) || 4;
            const beatMs  = 60000 / tempo;                          // quarter-note length
            // Metronome tick mirrors playRhythmAudio: dotted-quarter for x/8, half for x/2.
            const tickMs  = den === 8 ? beatMs * 1.5 : den === 2 ? beatMs * 2 : beatMs;
            const ticksPerBar = den === 8 ? Math.max(1, Math.round(num / 3)) : num;
            const tokenMs = v => ((RHYTHM_TWELFTHS[v] ?? 12) / 12) * beatMs;

            let answered = false, playStarted = false, rhythmStartTime = null, userTaps = [], endTimeout = null;

            const setTapActive = (active) => {
                tapButton.disabled = !active;
                tapButton.style.opacity = active ? '1' : '0.35';
                tapButton.style.cursor  = active ? 'pointer' : 'not-allowed';
            };
            setTapActive(false);

            // The rhythm is visible from the start — that is the point of reading mode.
            setTimeout(() => { if (staffBox) drawRhythmStaff(staffBox, notes, timeSig); }, 120);
            if (playStatus) playStatus.textContent = pt('press_play_metronome');

            playButton.onclick = async function() {
                if (playButton.disabled || answered) return;
                userTaps = [];
                rhythmStartTime = null;
                if (endTimeout) clearTimeout(endTimeout);
                playStarted = true;
                setTapActive(true);

                playButton.disabled = true;
                playButton.innerHTML = '<i data-lucide="loader-2" class="w-5 h-5 animate-spin inline"></i> ' + pt('get_ready');
                if (typeof lucide !== 'undefined') lucide.createIcons();

                await Tone.start();
                await window.HarmonivaAudio.prepare();

                // Count-in bar + metronome ticks for the rhythm duration, scheduled at
                // absolute audio-clock times (same approach as playRhythmAudio, no notes).
                const leadMs = 120;
                const wallNowRef = Date.now();
                const t0 = window.HarmonivaAudio.now() + leadMs / 1000;
                const click = (ms, accent) => _metroClick(accent, t0 + ms / 1000);

                let totalRhythmMs = 0;
                notes.forEach(v => { totalRhythmMs += tokenMs(v); });

                for (let b = 0; b < ticksPerBar; b++) click(b * tickMs, b === 0);
                const startMs = ticksPerBar * tickMs;
                const ticksDuring = Math.ceil(totalRhythmMs / tickMs);
                for (let b = 0; b < ticksDuring; b++) click(startMs + b * tickMs, (b % ticksPerBar) === 0);

                const startOffset = leadMs + startMs;
                // Derive the rhythm's true start instant from the same wall-clock
                // reference the audio clicks are scheduled against, rather than
                // stamping Date.now() inside the setTimeout below — that callback
                // can fire tens of ms late (event-loop/GC/background-tab jitter),
                // which was skewing every tap comparison by that same late amount
                // even when the user tapped in perfect sync with the metronome.
                rhythmStartTime = wallNowRef + startOffset;
                setTimeout(() => {
                    playButton.innerHTML = '<i data-lucide="volume-2" class="w-5 h-5"></i> ' + pt('playing');
                    if (playStatus) playStatus.textContent = pt('tap_along');
                    if (typeof lucide !== 'undefined') lucide.createIcons();
                }, startOffset);

                endTimeout = setTimeout(() => {
                    setTapActive(false);
                    evaluate();
                }, startOffset + totalRhythmMs + 600);
            };

            function recordTap() {
                if (answered || !playStarted) return;
                if (rhythmStartTime !== null) userTaps.push(Date.now() - rhythmStartTime);
                tapButton.style.transform = 'scale(0.93)';
                setTimeout(() => { tapButton.style.transform = ''; }, 90);
            }
            tapButton.addEventListener('click', recordTap);
            tapButton.addEventListener('touchstart', e => { e.preventDefault(); recordTap(); }, { passive: false });

            // Tab starts the rhythm (first press) or taps; Space taps while playing.
            const keyHandler = e => {
                if (answered) return;
                if (e.code === 'Tab') {
                    e.preventDefault();
                    if (!playStarted && !playButton.disabled) playButton.click();
                    else if (playStarted) recordTap();
                } else if (e.code === 'Space' && rhythmStartTime !== null) {
                    e.preventDefault();
                    recordTap();
                }
            };
            document.addEventListener('keydown', keyHandler);
            window._rhythmReadingKeyCleanup = () => document.removeEventListener('keydown', keyHandler);

            async function evaluate() {
                if (answered) return;
                // ±35% of a quarter note. The timing-reference fix above removes the
                // setTimeout-jitter bug, but real taps still carry latency the code
                // can't measure or cancel out: audio output/speaker latency (worse on
                // Bluetooth), tap/touch input dispatch latency, and ordinary human
                // sensorimotor variance. Those add up to more than the original ±20%
                // (Exercise Setup) or ±25% window, so correct-feeling taps were still
                // getting rejected — widened further to actually absorb it.
                const tolerance = beatMs * 0.35;

                // Expected tap time per note token (rests are read but not tapped).
                let posMs = 0;
                const expected = [];
                notes.forEach((v, idx) => {
                    if (!v.includes('_rest')) expected.push({ idx, time: posMs });
                    posMs += tokenMs(v);
                });

                const results = new Array(notes.length).fill('neutral');
                const usedTaps = new Set();
                expected.forEach(exp => {
                    let best = -1, bestDiff = Infinity;
                    userTaps.forEach((t, ti) => {
                        if (usedTaps.has(ti)) return;
                        const d = Math.abs(t - exp.time);
                        if (d < bestDiff) { bestDiff = d; best = ti; }
                    });
                    if (best >= 0 && bestDiff <= tolerance) { results[exp.idx] = 'correct'; usedTaps.add(best); }
                    else { results[exp.idx] = 'wrong'; }
                });

                const allOK = expected.length > 0 && results.every(r => r !== 'wrong');
                const colors = results.map(r => r === 'correct' ? '#16a34a' : (r === 'wrong' ? '#dc2626' : null));
                if (staffBox) {
                    drawRhythmStaff(staffBox, notes, timeSig, colors);
                    staffBox.classList.remove('border-gray-200', 'bg-white');
                    staffBox.classList.add(allOK ? 'border-green-400' : 'border-red-400', allOK ? 'bg-green-50' : 'bg-red-50');
                }

                answered = true;
                playButton.disabled = false;
                try {
                    // Correctness was decided by the tap evaluation; report it through
                    // the shared scorer (answer === target ⇔ correct).
                    await @this.call('answerPractice', 'rhythm', allOK ? target : 'missed-taps', target);
                } catch (e) {
                    console.error('Error recording rhythm reading answer:', e);
                }

                if (feedbackMessage) {
                    feedbackMessage.textContent = allOK
                        ? '✓ Perfect timing! All beats correct.'
                        : '✗ Not quite — green notes were tapped correctly, red ones were missed or mistimed.';
                    feedbackMessage.classList.remove('hidden', 'bg-red-100', 'text-red-700', 'bg-green-100', 'text-green-700');
                    feedbackMessage.classList.add(allOK ? 'bg-green-100' : 'bg-red-100', allOK ? 'text-green-700' : 'text-red-700');
                }

                const st = document.getElementById('scoreTotal');
                if (st) st.textContent = (parseInt(st.textContent) || 0) + 1;
                if (allOK) {
                    const xpEl = document.getElementById('xpEarned');
                    if (xpEl) xpEl.textContent = (parseInt(xpEl.textContent) || 0) + 10;
                    const sc = document.getElementById('scoreCorrect');
                    if (sc) sc.textContent = (parseInt(sc.textContent) || 0) + 1;
                }

                if (playButton) playButton.classList.add('hidden');
                if (playStatus) playStatus.classList.add('hidden');
                if (nextButton) nextButton.classList.remove('hidden');
                if (finishPracticeBtn) finishPracticeBtn.classList.remove('hidden');
                if (typeof lucide !== 'undefined') lucide.createIcons();
            }
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

            // Site-wide staff spacing standard (defined in partials/responsive-notation).
            const HS = window.HarmonivaStaff || { startPad: 40, span: function (n) { n = Math.max(1, n); return n * Math.max(40, Math.min(80, Math.round(160 / n))); } };

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
                playStatus.textContent = pt('listen_note_start');
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
            window._markSingleNoteRefShown = null;

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
                    // clef must also be passed to every StaveNote so VexFlow positions
                    // notes on the correct staff line for bass/alto (defaults to treble)
                    const noteClef = div.dataset.clef || 'treble';

                    if (noteType === 'interval_comparison') {
                        renderer.resize(468, 160);
                        const context = renderer.getContext();
                        const stave = new Stave(10, 30, 442);
                        stave.addClef(noteClef);
                        stave.setNoteStartX(stave.getNoteStartX() + HS.startPad);
                        stave.setContext(context).draw();

                        if (notesFromParams) {
                            const notesParsed = notesFromParams.split(',');
                            const notes = notesParsed.map(note => new StaveNote({ keys: [note], duration: "q", auto_stem: true, clef: noteClef }));
                            const voice = new Voice({ numBeats: 4, beatValue: 4 });
                            voice.addTickables(notes);
                            Accidental.applyAccidentals([voice], 'C');
                            new Formatter().joinVoices([voice]).format([voice], HS.span(notesParsed.length));
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
                            st.addClef(noteClef);
                            st.setNoteStartX(st.getNoteStartX() + HS.startPad);
                            st.setContext(ctx).draw();

                            let voice;
                            if (!showBoth) {
                                voice = new Voice({ numBeats: 4, beatValue: 4 });
                                voice.addTickables([new StaveNote({ keys: [note1Key], duration: "w", auto_stem: true, clef: noteClef })]);
                            } else if (isHarmonic) {
                                voice = new Voice({ numBeats: 4, beatValue: 4 });
                                voice.addTickables([new StaveNote({ keys: [note1Key, note2Key], duration: "w", auto_stem: true, clef: noteClef })]);
                            } else {
                                voice = new Voice({ numBeats: 2, beatValue: 2 });
                                voice.addTickables([
                                    new StaveNote({ keys: [note1Key], duration: "h", auto_stem: true, clef: noteClef }),
                                    new StaveNote({ keys: [note2Key], duration: "h", auto_stem: true, clef: noteClef }),
                                ]);
                            }
                            Accidental.applyAccidentals([voice], 'C');
                            new Formatter().joinVoices([voice]).format([voice], HS.span(showBoth && !isHarmonic ? 2 : 1));
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
                            st.addClef(noteClef);
                            st.setNoteStartX(st.getNoteStartX() + HS.startPad);
                            st.setContext(ctx).draw();
                            const keys = (showAll && allKeys.length) ? allKeys : [rootKey];
                            const voice = new Voice({ numBeats: 4, beatValue: 4 });
                            voice.addTickables([new StaveNote({ keys, duration: "w", auto_stem: true, clef: noteClef })]);
                            Accidental.applyAccidentals([voice], 'C');
                            new Formatter().joinVoices([voice]).format([voice], HS.span(1));
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
                            const staveWidth = Math.max(360, clefWidth + HS.startPad + HS.span(noteCount) + 30);
                            const r = new Renderer(div, Renderer.Backends.SVG);
                            r.resize(staveWidth + 20, 160);
                            const ctx = r.getContext();
                            const st = new Stave(10, 30, staveWidth);
                            st.addClef(noteClef);
                            st.setNoteStartX(st.getNoteStartX() + HS.startPad);
                            st.setContext(ctx).draw();
                            let voice;
                            if (showAll && allKeys.length) {
                                const notes = allKeys.map(k => new StaveNote({ keys: [k], duration: "q", auto_stem: true, clef: noteClef }));
                                voice = new Voice({ numBeats: notes.length, beatValue: 4 }).setMode(Voice.Mode.SOFT);
                                voice.addTickables(notes);
                            } else {
                                voice = new Voice({ numBeats: 4, beatValue: 4 });
                                voice.addTickables([new StaveNote({ keys: [rootKey], duration: "w", auto_stem: true, clef: noteClef })]);
                            }
                            Accidental.applyAccidentals([voice], 'C');
                            new Formatter().joinVoices([voice]).format([voice], HS.span(noteCount));
                            voice.draw(ctx, st);
                            centerVexOutput(div);
                        };
                        drawScale(false);
                        window._revealHarmonicMixed = drawScale;
                    } else if (noteType === 'single_note') {
                        // Single note: keep the staff empty until the user answers.
                        // The note must be identified by ear (compared against the
                        // reference / previous note), so printing it up front would
                        // give the answer away. Revealed via _revealHarmonicMixed.
                        // On the first single-note question the fixed reference note
                        // (data-reference-note on the play button) is drawn in indigo
                        // as soon as it has been played.
                        const singleKey = (notesFromParams || '').split(',')[0] || 'c/4';
                        const playBtnEl = document.getElementById('playButton');
                        const refDataNote = (playBtnEl && playBtnEl.dataset.referenceNote) || '';
                        const refKey = refDataNote
                            ? refDataNote.slice(0, -1).toLowerCase() + '/' + refDataNote.slice(-1)
                            : '';
                        let refShown = false;
                        const drawSingle = (showNote) => {
                            div.innerHTML = '';
                            const r = new Renderer(div, Renderer.Backends.SVG);
                            r.resize(546, 160);
                            const ctx = r.getContext();
                            const st = new Stave(10, 30, 780);
                            st.addClef(noteClef);
                            st.setNoteStartX(st.getNoteStartX() + HS.startPad);
                            st.setContext(ctx).draw();
                            const showRef = refShown && refKey;
                            const noteCount = (showRef ? 1 : 0) + (showNote ? 1 : 0);
                            if (noteCount > 0) {
                                const duration = noteCount > 1 ? "h" : "w";
                                const items = [];
                                if (showRef) {
                                    const rn = new StaveNote({ keys: [refKey], duration: duration, auto_stem: true, clef: noteClef });
                                    rn.setStyle({ fillStyle: '#6366f1', strokeStyle: '#6366f1' });
                                    items.push(rn);
                                }
                                if (showNote) {
                                    items.push(new StaveNote({ keys: [singleKey], duration: duration, auto_stem: true, clef: noteClef }));
                                }
                                const voice = noteCount > 1
                                    ? new Voice({ numBeats: 2, beatValue: 2 })
                                    : new Voice({ numBeats: 4, beatValue: 4 });
                                voice.addTickables(items);
                                Accidental.applyAccidentals([voice], 'C');
                                new Formatter().joinVoices([voice]).format([voice], HS.span(noteCount));
                                voice.draw(ctx, st);
                            }
                            centerVexOutput(div);
                        };
                        drawSingle(false);
                        window._revealHarmonicMixed = drawSingle;
                        // Called by the play handler after the reference note sounds.
                        window._markSingleNoteRefShown = () => { refShown = true; drawSingle(false); };
                    } else {
                        // Interval Direction
                        renderer.resize(546, 160);
                        const context = renderer.getContext();
                        const stave = new Stave(10, 30, 780);
                        stave.addClef(noteClef);
                        stave.setNoteStartX(stave.getNoteStartX() + HS.startPad);
                        stave.setContext(context).draw();

                        if (notesFromParams) {
                            const notesParsed = notesFromParams.split(',');
                            const duration = notesParsed.length > 1 ? "h" : "1";
                            const staveNotes = notesParsed.map(note => new StaveNote({ keys: [note], duration: duration, auto_stem: true, clef: noteClef }));
                            const numBeats = notesParsed.length > 1 ? 2 : 4;
                            const voice = new Voice({ numBeats: numBeats, beatValue: notesParsed.length > 1 ? 2 : 4 });
                            voice.addTickables(staveNotes);
                            Accidental.applyAccidentals([voice], 'C');
                            new Formatter().joinVoices([voice]).format([voice], HS.span(notesParsed.length));
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
                    playButton.innerHTML = '<i data-lucide="volume-2" class="w-5 h-5"></i> ' + pt('playing');
                    playStatus.textContent = pt('playing');
                    if (typeof lucide !== 'undefined') lucide.createIcons();

                    const playMode = this.dataset.playMode;

                    const resetPlayBtn = (label) => {
                        playButton.disabled = false;
                        playButton.innerHTML = `<i data-lucide="play" class="w-5 h-5"></i> ${label || pt('play_again')}`;
                        playStatus.textContent = pt('click_replay');
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
                        playStatus.textContent = pt('playing_a');
                        window.HarmonivaAudio.playSequential(intervalA, 700, 1);
                        setTimeout(() => {
                            playStatus.textContent = pt('playing_b');
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
                        window.HarmonivaAudio.playSequential(notes, 600, 0.8);
                        const totalMs = (window.HarmonivaAudio.totalMs ? window.HarmonivaAudio.totalMs(notes, 600) : notes.length * 600) + 500;
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
                    } else if (practiceType === 'single_note' && this.dataset.referenceNote) {
                        // First single-note question: sound the fixed reference note
                        // (Do), show it on the staff, then play the question note
                        // right after. Later single-note questions have no
                        // data-reference-note and take the plain branch below.
                        const refNote = this.dataset.referenceNote;
                        const questionNote = this.dataset.note;
                        playStatus.textContent = pt('playing_ref');
                        // Wait for the sampler so the ref/question gap stays accurate
                        // even on the first click (sample loading can take seconds).
                        await window.HarmonivaAudio.prepare();
                        window.HarmonivaAudio.playNote(refNote, 1.5);
                        if (typeof window._markSingleNoteRefShown === 'function') window._markSingleNoteRefShown();
                        setTimeout(() => {
                            playStatus.textContent = pt('playing');
                            window.HarmonivaAudio.playNote(questionNote, 1.5);
                        }, 1800);
                        setTimeout(() => resetPlayBtn('Play Again'), 3800);
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
                        this.innerHTML = '<i data-lucide="loader-2" class="w-5 h-5 animate-spin inline"></i> ' + pt('checking');
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
                            // reveals the full stacked chord; scale reveals the full
                            // sequence; single note reveals the heard note).
                            if ((practiceType === 'harmonic_interval' || practiceType === 'melodic_interval' || practiceType === 'interval_construction' || practiceType === 'chord' || practiceType === 'scale' || practiceType === 'single_note')
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
                            } else if (practiceType === 'single_note') {
                                this.textContent = noteToSymbol(answer);
                            } else if (practiceType === 'interval_construction') {
                                this.textContent = answer.replace('##', 'x');
                            } else {
                                this.textContent = answer.charAt(0).toUpperCase() + answer.slice(1);
                            }
                            
                            if (data.is_correct) {
                                // Correct answer
                                this.classList.add('correct');
                                this.classList.remove('text-gray-700');
                                this.classList.add('text-green-700');
                                feedbackMessage.textContent = pt('correct_well_done');
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
                                        : practiceType === 'single_note'
                                            ? noteToSymbol(target)
                                            : practiceType === 'interval_construction'
                                                ? target.replace('##', 'x')
                                                : (practiceType === 'chord' || practiceType === 'scale')
                                                    ? window.musicLabel(target, practiceType)
                                                    : target.charAt(0).toUpperCase() + target.slice(1);
                                feedbackMessage.textContent = pt('incorrect_answer_is', {answer: correctDisplay});
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
                            feedbackMessage.textContent = pt('error_checking');
                            feedbackMessage.classList.remove('hidden', 'bg-green-100', 'text-green-700');
                            feedbackMessage.classList.add('bg-red-100', 'text-red-700');
                            
                            // Re-enable buttons on error
                            answerButtons.forEach(b => b.disabled = false);
                            isAnswered = false;
                            
                            if (typeof lucide !== 'undefined') lucide.createIcons();
                        }
                    };
                });
                // Rhythm modes use dedicated UIs instead of .answer-btn options:
                // 'rhythm' = dictation builder, plus the two Exercise Setup modes.
                if (practiceType === 'rhythm') {
                    setupRhythmBuilder(target);
                } else if (practiceType === 'rhythm-recognition') {
                    setupRhythmRecognition(target);
                } else if (practiceType === 'rhythm-reading') {
                    setupRhythmReading(target);
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
