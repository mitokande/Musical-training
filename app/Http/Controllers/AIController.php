<?php

namespace App\Http\Controllers;

use App\Livewire\PracticeChord;
use App\Livewire\PracticeHarmonicInterval;
use App\Livewire\PracticeIntervalComparison;
use App\Livewire\PracticeIntervalConstruction;
use App\Livewire\PracticeMelodicInterval;
use App\Models\IntervalDirectionPractice;
use App\Models\LearningPathExercise;
use App\Models\Practice;
use App\Models\SystemSetting;
use App\Models\UserIntervalStat;
use App\Models\UserPractice;
use App\Services\AiUsageLogger;
use App\Services\LearningPathQuestionGenerator;
use App\Services\MusicTheoryService;
use Illuminate\Http\Request;
use OpenAI;

class AIController extends Controller
{
    public function generateIntervalDirectionPractice()
    {
        $apikey = config('services.openai.key');
        if (! $apikey) {
            return response()->json(['error' => 'OpenAI API key not configured'], 500);
        }

        $model = SystemSetting::get('ai_model', 'gpt-4.1-mini');
        $start = microtime(true);

        try {
            $client = OpenAI::client($apikey);

            $response = $client->chat()->create([
                'model' => $model,
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'You are a music theory expert generating ear training questions. '
                            .'For interval direction questions: choose two different notes and state whether '
                            .'the second note is ascending (higher pitch) or descending (lower pitch) from the first. '
                            .'Use only natural notes and common accidentals (C, C#, D, D#, E, F, F#, G, G#, A, A#, B). '
                            .'Octave must be 3, 4, or 5. Vary the intervals for pedagogical variety.',
                    ],
                    [
                        'role' => 'user',
                        'content' => 'Generate one interval direction practice question.',
                    ],
                ],
                'response_format' => [
                    'type' => 'json_schema',
                    'json_schema' => IntervalDirectionPractice::schema(),
                ],
            ]);

            AiUsageLogger::logSuccess('interval_direction_question', $model, $response->usage, auth()->id(), [], (int) ((microtime(true) - $start) * 1000));

            $data = json_decode($response->choices[0]->message->content, true);

            // Post-process: derive note2_octave and re-verify direction via MIDI pitch math.
            // OpenAI does not model cross-octave boundaries (e.g. B→D# ascending = D#5, not D#4).
            $music = app(MusicTheoryService::class);
            $note1 = $music->normalizeNote($data['note1'] ?? 'C');
            $note2 = $music->normalizeNote($data['note2'] ?? 'E');
            $octave1 = (int) ($data['octave'] ?? 4);
            $intent = $data['direction'] ?? 'ascending';

            $octave2 = $music->resolveNote2OctaveFromDirection($note1, $octave1, $note2, $intent);
            $data['note1'] = $note1;
            $data['note2'] = $note2;
            $data['note2_octave'] = $octave2;
            $data['direction'] = $music->getDirection($note1, $octave1, $note2, $octave2);

            return $data;
        } catch (\Exception $e) {
            AiUsageLogger::logError('interval_direction_question', $model, $e->getMessage(), auth()->id(), [], (int) ((microtime(true) - $start) * 1000));
            \Log::error('OpenAI API error in generateIntervalDirectionPractice: '.$e->getMessage());

            return response()->json(['error' => 'Failed to generate practice. Please try again.'], 500);
        }
    }

    public function generatePractices(Request $request)
    {
        $validated = $request->validate([
            'exercise_types' => 'required_without:rhythm_modes|array',
            'exercise_types.*' => 'integer|exists:practices,id',
            'num_questions' => 'required|integer|min:1|max:50',
            'difficulty' => 'required|string|in:easy,medium,hard,adaptive',
            // Rhythm Recognition / Rhythm Reading: extra rhythm exercise modes
            // (Exercise Setup Studio's other two rhythm modes) offered as their
            // own selection cards. They have no Practice DB record — they reuse
            // the rhythm-practice generation with a different answer UI.
            'rhythm_modes' => 'nullable|array',
            'rhythm_modes.*' => 'in:recognition,reading',
        ]);

        $rhythmModes = array_values(array_unique($validated['rhythm_modes'] ?? []));

        $practiceTypes = Practice::whereIn('id', $validated['exercise_types'] ?? [])->get();

        // Melodic Dictation alone gets its own dedicated practice page (the AI
        // clone of the Exercise Setup dictation flow). The Livewire component
        // generates the questions from question count + difficulty itself.
        // When mixed with other types it stays in the generic flow below.
        if ($practiceTypes->count() === 1 && $practiceTypes->first()->slug === 'melodic-dictation' && empty($rhythmModes)) {
            session(['ai_dictation_settings' => [
                'question_count' => (int) $validated['num_questions'],
                'difficulty' => $validated['difficulty'],
            ]]);
            session()->forget(['learning_path_session', 'exercise_settings']);

            return redirect()->route('practice.ai.dictation');
        }

        // Types handled via OpenAI structured output (none currently — single-note
        // migrated to deterministic local generation so user note/clef settings apply).
        $aiPracticeClasses = [];

        // Types generated locally via LearningPathQuestionGenerator.
        $localTypeSlugs = [
            'melodic-interval-practice',
            'harmonic-interval-practice',
            'interval-direction-practice',
            'interval-construction-practice',
            'interval-comparison-practice',
            'chord-practice',
            'scale-practice',
            'rhythm-practice',
            'melodic-dictation',
            'single-note-practice',
        ];

        $aiPracticeTypes = $practiceTypes->filter(fn ($p) => isset($aiPracticeClasses[$p->slug]));
        $localPracticeTypes = $practiceTypes->filter(fn ($p) => in_array($p->slug, $localTypeSlugs));

        if ($aiPracticeTypes->isEmpty() && $localPracticeTypes->isEmpty() && empty($rhythmModes)) {
            return back()->with('error', 'No valid practice types selected.');
        }

        $numQuestions = (int) $validated['num_questions'];
        $difficulty = $validated['difficulty'];
        $totalTypes = $aiPracticeTypes->count() + $localPracticeTypes->count() + count($rhythmModes);
        $perType = max(1, (int) ceil($numQuestions / max(1, $totalTypes)));

        // ── Local question generation (chord / scale / rhythm / melodic-dictation) ──
        $localQuestions = [];
        if ($localPracticeTypes->isNotEmpty()) {
            $generator = app(LearningPathQuestionGenerator::class);

            foreach ($localPracticeTypes as $practiceType) {
                // Rhythm Dictation (and the recognition/reading modes below) share a
                // dedicated generation path that mirrors the Exercise Setup rhythm flow.
                if ($practiceType->slug === 'rhythm-practice') {
                    $localQuestions = array_merge($localQuestions, $this->generateRhythmQuestions('build', $difficulty, $perType));

                    continue;
                }

                $typeConfig = $difficulty === 'adaptive'
                    ? $this->buildAdaptiveConfig($practiceType->slug, (int) auth()->id())
                    : $this->buildLocalConfig($practiceType->slug, $difficulty);

                $config = array_merge(
                    ['practice_type' => $practiceType->slug],
                    $typeConfig
                );
                $exercise = new LearningPathExercise;
                $exercise->config_json = $config;

                try {
                    // Scale & Mode with direction 'both': the generator takes a single
                    // direction, so mirror PracticeScale::mount — generate half the
                    // questions ascending and half descending, then mix.
                    if ($practiceType->slug === 'scale-practice' && ($typeConfig['direction'] ?? '') === 'both') {
                        $perDir = max(1, (int) ceil($perType / 2));
                        $generated = collect();
                        foreach (['ascending', 'descending'] as $dir) {
                            $dirExercise = new LearningPathExercise;
                            $dirExercise->config_json = array_merge($config, ['direction' => $dir]);
                            $generated = $generated->merge($generator->generate($dirExercise, $perDir));
                        }
                        $generated = $generated->shuffle()->values()->take($perType);
                    } else {
                        $generated = $generator->generate($exercise, $perType);
                    }
                    foreach ($generated as $i => $q) {
                        $q->id = $i + 1; // temp ID
                        $attrs = $q->getAttributes();
                        // Decode JSON-encoded array fields
                        foreach ($attrs as $key => $value) {
                            if (is_string($value) && strlen($value) > 0 && $value[0] === '[') {
                                $decoded = json_decode($value, true);
                                if (json_last_error() === JSON_ERROR_NONE) {
                                    $attrs[$key] = $decoded;
                                }
                            }
                        }
                        // Harmonic interval answer options mirror the Exercise Setup
                        // grid (PracticeHarmonicInterval::mount): correct interval + 3
                        // distractors from the canonical ES 12-interval palette. The
                        // generator's own fallback draws from INTERVAL_SEMITONES, whose
                        // unison and enharmonic aliases never appear in the ES UI.
                        if ($practiceType->slug === 'harmonic-interval-practice') {
                            $music = app(MusicTheoryService::class);
                            $correct = $attrs['interval'];
                            $distractors = $music->buildOptions($correct, array_values(PracticeHarmonicInterval::INTERVAL_POOL_MAP), 3);
                            $options = array_merge([$correct], $distractors);
                            shuffle($options);
                            $attrs['options'] = $options;
                        }
                        // Interval-construction options come straight from the
                        // generator: single-accidental Exercise Setup palette, one
                        // option per pitch class, never an enharmonic respelling
                        // of the answer.
                        // Hard chord sessions mix voicing per question — Exercise Setup
                        // offers block and arpeggiated playback and the mixed view
                        // already supports both via data-voicing.
                        if ($practiceType->slug === 'chord-practice' && $difficulty === 'hard') {
                            $attrs['voicing'] = random_int(0, 1) === 1 ? 'arpeggiated' : 'block';
                        }
                        // Add type discriminator matching convertAIQuestionsToPractices() cases
                        $attrs['type'] = $this->slugToQuestionType($practiceType->slug);
                        $localQuestions[] = $attrs;
                    }
                } catch (\Exception $e) {
                    \Log::error("Local question generation failed for {$practiceType->slug}: ".$e->getMessage());
                }
            }
        }

        // ── Rhythm Recognition / Rhythm Reading ───────────────────────────────────
        // Extra rhythm modes selected on the AI page. Same generation engine and
        // rules as Rhythm Dictation; only the answer UI differs (rhythm_mode).
        foreach ($rhythmModes as $mode) {
            $localQuestions = array_merge($localQuestions, $this->generateRhythmQuestions($mode, $difficulty, $perType));
        }

        // ── AI question generation (interval types / single-note) ─────────────────
        $aiQuestions = [];
        if ($aiPracticeTypes->isNotEmpty()) {
            $schemas = [];
            foreach ($aiPracticeTypes as $pt) {
                $schemas[] = $aiPracticeClasses[$pt->slug]::schema();
            }

            $apikey = config('services.openai.key');
            if (! $apikey) {
                return back()->with('error', 'OpenAI API key not configured.');
            }

            // Distribute remaining questions evenly across AI types
            $aiNumQuestions = max(1, $numQuestions - count($localQuestions));

            $model = SystemSetting::get('ai_model', 'gpt-4.1-mini');
            $start = microtime(true);

            try {
                $practiceNames = $aiPracticeTypes->pluck('name')->toArray();
                $client = OpenAI::client($apikey);
                $response = $client->chat()->create([
                    'model' => $model,
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => implode(' ', [
                                'You are a music theory expert generating ear training practice questions.',
                                'Rules that MUST be followed:',
                                '- Use only notes: C, C#, D, D#, E, F, F#, G, G#, A, A#, B (no flats like Eb, Bb).',
                                '- Octave values must be 3, 4, or 5.',
                                '- Interval names must be exact: Minor 2nd, Major 2nd, Minor 3rd, Major 3rd, Perfect 4th, Tritone, Perfect 5th, Minor 6th, Major 6th, Minor 7th, Major 7th, Perfect Octave.',
                                '- For interval-direction questions: "ascending" means note2 has a higher pitch than note1; "descending" means lower pitch.',
                                '- For melodic-interval and harmonic-interval questions: the interval field must match the actual pitch distance between note1 and note2.',
                                '- For interval-construction questions: note2 must be exactly the note that is one named interval above note1.',
                                '- Vary note choices and difficulty for pedagogical variety.',
                            ]),
                        ],
                        [
                            'role' => 'user',
                            'content' => 'Generate '.$aiNumQuestions.' '
                                .$difficulty.' difficulty questions of types: '
                                .implode(', ', $practiceNames).'.',
                        ],
                    ],
                    'response_format' => [
                        'type' => 'json_schema',
                        'json_schema' => [
                            'name' => 'practice_questions',
                            'strict' => true,
                            'schema' => [
                                'type' => 'object',
                                'properties' => [
                                    'questions' => [
                                        'type' => 'array',
                                        'items' => [
                                            'anyOf' => $schemas,
                                        ],
                                    ],
                                ],
                                'required' => ['questions'],
                                'additionalProperties' => false,
                            ],
                        ],
                    ],
                ]);

                AiUsageLogger::logSuccess('ai_practice_generation', $model, $response->usage, auth()->id(), ['difficulty' => $difficulty, 'num_questions' => $aiNumQuestions], (int) ((microtime(true) - $start) * 1000));

                $decoded = json_decode($response->choices[0]->message->content, true);
                $aiQuestions = $this->sanitizeAIQuestions($decoded['questions'] ?? []);
            } catch (\Exception $e) {
                AiUsageLogger::logError('ai_practice_generation', $model, $e->getMessage(), auth()->id(), ['difficulty' => $difficulty], (int) ((microtime(true) - $start) * 1000));
                \Log::error('OpenAI API error in generatePractices: '.$e->getMessage());

                $message = 'Failed to generate AI practices. Please try again.';
                if (str_contains($e->getMessage(), 'quota') || str_contains($e->getMessage(), 'rate limit')) {
                    $message = 'OpenAI API quota exceeded. Please check your billing at platform.openai.com or try again later.';
                }

                // If we have local questions, continue with those; otherwise fail
                if (empty($localQuestions)) {
                    return back()->with('error', $message);
                }
            }
        }

        // Merge and shuffle all questions
        $allQuestions = array_merge($aiQuestions, $localQuestions);
        shuffle($allQuestions);

        // Keep single-note questions contiguous at the front: the session opens
        // with a reference note (C4 — PracticeMixed reference intro) and each
        // heard note then anchors the next, so interleaving other types in
        // between would break the comparison chain.
        $singleNote = array_values(array_filter($allQuestions, fn ($q) => ($q['type'] ?? '') === 'single-note'));
        if ($singleNote !== []) {
            $others = array_values(array_filter($allQuestions, fn ($q) => ($q['type'] ?? '') !== 'single-note'));
            $allQuestions = array_merge($singleNote, $others);
        }

        if (empty($allQuestions)) {
            return back()->with('error', 'No practice questions could be generated. Please try again.');
        }

        session(['ai_practice_questions' => $allQuestions]);
        session(['ai_practice_title' => 'AI Generated Practice']);

        return redirect()->route('practice.ai');
    }

    /**
     * Map a practice slug to the type discriminator used in convertAIQuestionsToPractices().
     */
    private function slugToQuestionType(string $slug): string
    {
        return match ($slug) {
            'chord-practice' => 'chord',
            'scale-practice' => 'scale',
            'rhythm-practice' => 'rhythm',
            'melodic-dictation' => 'melodic_dictation',
            'melodic-interval-practice' => 'melodic-interval',
            'harmonic-interval-practice' => 'harmonic-interval',
            'interval-direction-practice' => 'interval-direction',
            'interval-construction-practice' => 'interval-construction',
            'interval-comparison-practice' => 'interval-comparison',
            'single-note-practice' => 'single-note',
            default => $slug,
        };
    }

    /**
     * Generate rhythm questions for one of the three rhythm modes carried over
     * from Exercise Setup Studio: 'build' (Rhythm Dictation — rebuild the rhythm
     * note-by-note), 'recognition' (pick the heard pattern among four staves) and
     * 'reading' (tap the printed rhythm in time). All three share the exact same
     * generation rules; only the answer UI in the mixed view differs, so each
     * question carries a rhythm_mode discriminator.
     *
     * Time signature and tempo are not user inputs on the AI page — they come
     * from the difficulty preset (buildLocalConfig), and 'adaptive' resolves the
     * preset from the user's rhythm practice history (buildAdaptiveConfig).
     *
     * @return array<int, array> question attribute arrays for the mixed session
     */
    private function generateRhythmQuestions(string $mode, string $difficulty, int $count): array
    {
        $typeConfig = $difficulty === 'adaptive'
            ? $this->buildAdaptiveConfig('rhythm-practice', (int) auth()->id())
            : $this->buildLocalConfig('rhythm-practice', $difficulty);

        $exercise = new LearningPathExercise;
        $exercise->config_json = array_merge(['practice_type' => 'rhythm-practice'], $typeConfig);

        $questions = [];
        try {
            $generator = app(LearningPathQuestionGenerator::class);
            // Palette must match the resolved cell-pool difficulty (adaptive may have
            // resolved to easy/medium/hard from history), not the raw request value.
            $palette = $this->rhythmPaletteForDifficulty($typeConfig['rhythm_difficulty'] ?? 'medium');

            foreach ($generator->generate($exercise, $count) as $i => $q) {
                $q->id = $i + 1; // temp ID
                $attrs = $q->getAttributes();
                foreach ($attrs as $key => $value) {
                    if (is_string($value) && strlen($value) > 0 && $value[0] === '[') {
                        $decoded = json_decode($value, true);
                        if (json_last_error() === JSON_ERROR_NONE) {
                            $attrs[$key] = $decoded;
                        }
                    }
                }
                $attrs['type'] = 'rhythm';
                $attrs['rhythm_mode'] = $mode;
                if ($mode === 'build') {
                    // Builder palette: which note buttons the student can place.
                    $attrs['allowed_values'] = $palette;
                }
                $questions[] = $attrs;
            }
        } catch (\Exception $e) {
            \Log::error("Rhythm question generation failed for mode {$mode}: ".$e->getMessage());
        }

        return $questions;
    }

    /**
     * The builder palette (button set) offered for Rhythm Dictation at a given difficulty.
     * Generation itself uses a beat-cell pool (LearningPathQuestionGenerator::rhythmCells);
     * this is just which note buttons the student can place. Rests are always available
     * (the generated rhythm never uses them, but the student may). Hard adds a triplet button.
     */
    private function rhythmPaletteForDifficulty(string $difficulty): array
    {
        $rests = ['whole_rest', 'half_rest', 'quarter_rest', 'eighth_rest'];

        return match ($difficulty) {
            'easy' => array_merge(['whole', 'half', 'dotted-half', 'quarter', 'eighth'], $rests),
            'hard' => array_merge([
                'whole', 'half', 'dotted-half', 'quarter', 'dotted-quarter',
                'eighth', 'dotted-eighth', 'sixteenth', 'triplet',
            ], $rests),
            // medium + adaptive
            default => array_merge([
                'whole', 'half', 'dotted-half', 'quarter', 'dotted-quarter',
                'eighth', 'dotted-eighth', 'sixteenth',
            ], $rests),
        };
    }

    /**
     * Build a default config_json array for LearningPathQuestionGenerator based on difficulty.
     */
    private function buildLocalConfig(string $slug, string $difficulty): array
    {
        // Scale & Mode difficulty palettes (subsets of the Exercise Setup Studio
        // scale palette, PracticeScale::ALL_SCALE_TYPES). Ionian and Aeolian are
        // omitted on purpose: they sound identical to Major and Natural Minor, so
        // sharing an answer pool with them would make questions ambiguous.
        $scaleTypesEasy = ['Major', 'Natural Minor', 'Major Pentatonic', 'Minor Pentatonic'];
        $scaleTypesMedium = array_merge($scaleTypesEasy, ['Harmonic Minor', 'Melodic Minor', 'Blues Scale']);
        $scaleTypesHard = array_merge($scaleTypesMedium, ['Dorian', 'Phrygian', 'Lydian', 'Mixolydian', 'Locrian', 'Chromatic Scale', 'Whole Tone Scale']);

        return match ($slug) {
            // Melodic interval rules mirror the Exercise Setup Studio melodic-interval
            // flow (PracticeMelodicInterval::mount): natural start notes, octave derived
            // from the treble clef's playable range ('clef' instead of octave_range),
            // the ES 12-interval palette (INTERVAL_POOL_MAP — no unison, no enharmonic
            // aliases), and the generator's own ES distractor heuristic (no
            // distractor_count/mode overrides: pools under 4 draw distractors from the
            // full palette, otherwise 3 from the pool). Difficulty stands in for the
            // user-selected pool in Exercise Setup and ramps direction: easy ascending,
            // medium/hard mixed (the ES default).
            'melodic-interval-practice' => match ($difficulty) {
                'easy' => ['allowed_intervals' => ['Major 3rd', 'Perfect 5th', 'Perfect Octave'], 'allowed_notes' => ['C', 'D', 'E', 'F', 'G', 'A', 'B'], 'clef' => 'treble', 'direction' => 'ascending'],
                'hard' => ['allowed_intervals' => array_values(PracticeMelodicInterval::INTERVAL_POOL_MAP), 'allowed_notes' => ['C', 'D', 'E', 'F', 'G', 'A', 'B'], 'clef' => 'treble', 'direction' => 'mixed'],
                default => ['allowed_intervals' => ['Minor 2nd', 'Major 2nd', 'Minor 3rd', 'Major 3rd', 'Perfect 4th', 'Perfect 5th', 'Major 6th', 'Perfect Octave'], 'allowed_notes' => ['C', 'D', 'E', 'F', 'G', 'A', 'B'], 'clef' => 'treble', 'direction' => 'mixed'],
            },
            // Harmonic interval rules mirror the Exercise Setup Studio harmonic-interval
            // flow (PracticeHarmonicInterval::mount): natural start notes, octave derived
            // from the treble clef's playable range ('clef' instead of octave_range), and
            // the ES 12-interval palette (INTERVAL_POOL_MAP — no unison, no enharmonic
            // aliases). No distractor_count/mode overrides — the answer options are
            // rebuilt in generatePractices() exactly like the ES component builds them
            // (correct + 3 distractors from the full ES palette). Difficulty stands in
            // for the user-selected pool in Exercise Setup.
            'harmonic-interval-practice' => match ($difficulty) {
                'easy' => ['allowed_intervals' => ['Major 3rd', 'Perfect 5th', 'Perfect Octave'], 'allowed_notes' => ['C', 'D', 'E', 'F', 'G', 'A', 'B'], 'clef' => 'treble'],
                'hard' => ['allowed_intervals' => array_values(PracticeHarmonicInterval::INTERVAL_POOL_MAP), 'allowed_notes' => ['C', 'D', 'E', 'F', 'G', 'A', 'B'], 'clef' => 'treble'],
                default => ['allowed_intervals' => ['Minor 2nd', 'Major 2nd', 'Minor 3rd', 'Major 3rd', 'Perfect 4th', 'Perfect 5th', 'Major 6th', 'Perfect Octave'], 'allowed_notes' => ['C', 'D', 'E', 'F', 'G', 'A', 'B'], 'clef' => 'treble'],
            },
            // Note: 12 (Perfect Octave) is intentionally omitted here. A direction
            // question for an octave has note1/note2 sharing the same name, which the
            // downstream conversion's unison guard collapses into a same-octave unison.
            // Intervals 1-11 fully cover ascending/descending direction practice.
            'interval-direction-practice' => match ($difficulty) {
                'easy' => ['allowed_intervals_semitones' => [5, 7], 'allowed_notes' => ['C', 'D', 'E', 'F', 'G'], 'octave' => 4, 'clef' => 'treble'],
                'hard' => ['allowed_intervals_semitones' => [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11], 'allowed_notes' => ['C', 'C#', 'D', 'D#', 'E', 'F', 'F#', 'G', 'G#', 'A', 'A#', 'B'], 'octave' => 4, 'clef' => 'treble'],
                default => ['allowed_intervals_semitones' => [2, 3, 4, 5, 7, 9, 11], 'allowed_notes' => ['C', 'D', 'E', 'F', 'G', 'A', 'B'], 'octave' => 4, 'clef' => 'treble'],
            },
            // Interval construction rules mirror the Exercise Setup Studio construction
            // flow (PracticeIntervalConstruction::mount): natural root notes, octave
            // derived from the treble clef's playable range ('clef' instead of a fixed
            // octave), the ES 12-interval palette (INTERVAL_POOL_MAP — no unison, no
            // enharmonic aliases), and diatonic spelling for the correct answer (no
            // distractor_count/mode overrides — the answer options are rebuilt in
            // generatePractices() exactly like the ES component builds them: correct
            // note + 3 distractors from the ES diatonic pool). Difficulty stands in
            // for the user-selected pool in Exercise Setup and ramps direction: easy
            // ascending, medium/hard mixed (the ES default).
            'interval-construction-practice' => match ($difficulty) {
                'easy' => ['allowed_intervals' => ['Major 3rd', 'Perfect 5th', 'Perfect Octave'], 'allowed_root_notes' => ['C', 'D', 'E', 'F', 'G', 'A', 'B'], 'clef' => 'treble', 'direction' => 'ascending'],
                'hard' => ['allowed_intervals' => array_values(PracticeIntervalConstruction::INTERVAL_POOL_MAP), 'allowed_root_notes' => ['C', 'D', 'E', 'F', 'G', 'A', 'B'], 'clef' => 'treble', 'direction' => 'mixed'],
                default => ['allowed_intervals' => ['Minor 2nd', 'Major 2nd', 'Minor 3rd', 'Major 3rd', 'Perfect 4th', 'Perfect 5th', 'Major 6th', 'Perfect Octave'], 'allowed_root_notes' => ['C', 'D', 'E', 'F', 'G', 'A', 'B'], 'clef' => 'treble', 'direction' => 'mixed'],
            },
            // Interval comparison rules mirror the Exercise Setup Studio comparison
            // flow (PracticeIntervalComparison::mount): the interval pool is mapped
            // to canonical same-octave C-root pairs with correct diatonic spelling
            // (POOL_TO_PAIR — flats, no unison, no octave), every pairwise
            // combination of the pool is offered (buildPairsFromPool), and no fixed
            // octave — 'clef' makes the generator place each pair inside the treble
            // range. Difficulty stands in for the user-selected pool in Exercise
            // Setup: easy mirrors the other interval types' easy palette (M7 stands
            // in for the octave, which a same-octave pair cannot express), medium
            // is the shared 8-interval medium palette minus the octave, hard is the
            // full comparison pool.
            'interval-comparison-practice' => match ($difficulty) {
                'easy' => ['allowed_interval_pairs' => PracticeIntervalComparison::buildPairsFromPool(['M3', 'P5', 'M7']), 'clef' => 'treble'],
                'hard' => ['allowed_interval_pairs' => PracticeIntervalComparison::buildPairsFromPool(array_keys(PracticeIntervalComparison::POOL_TO_PAIR)), 'clef' => 'treble'],
                default => ['allowed_interval_pairs' => PracticeIntervalComparison::buildPairsFromPool(['m2', 'M2', 'm3', 'M3', 'P4', 'P5', 'M6']), 'clef' => 'treble'],
            },
            // Chord rules mirror the Exercise Setup Studio chord flow (PracticeChord::mount):
            // natural root notes, distractor options drawn from the full chord-type palette,
            // and no fixed octave — 'clef' makes the generator keep every chord tone inside
            // the treble range. Difficulty narrows the answer palette and gates inversions;
            // hard additionally mixes per-question voicing (see generatePractices()).
            'chord-practice' => match ($difficulty) {
                'easy' => ['allowed_chord_types' => ['Major', 'Minor'], 'allowed_root_notes' => ['C', 'D', 'E', 'F', 'G', 'A', 'B'], 'voicing' => 'block', 'include_inversions' => false, 'distractor_pool' => PracticeChord::ALL_CHORD_TYPES, 'clef' => 'treble'],
                'hard' => ['allowed_chord_types' => ['Major', 'Minor', 'Diminished', 'Augmented', 'Dominant 7th', 'Major 7th', 'Minor 7th'], 'allowed_root_notes' => ['C', 'D', 'E', 'F', 'G', 'A', 'B'], 'voicing' => 'block', 'include_inversions' => true, 'distractor_pool' => PracticeChord::ALL_CHORD_TYPES, 'clef' => 'treble'],
                default => ['allowed_chord_types' => ['Major', 'Minor', 'Diminished', 'Dominant 7th'], 'allowed_root_notes' => ['C', 'D', 'E', 'F', 'G', 'A', 'B'], 'voicing' => 'block', 'include_inversions' => false, 'distractor_pool' => PracticeChord::ALL_CHORD_TYPES, 'clef' => 'treble'],
            },
            // Scale rules mirror the Exercise Setup Studio Scale & Mode flow
            // (PracticeScale::mount): natural root notes, distractor options drawn
            // from the same allowed scale set (not an external pool), and no fixed
            // octave — 'clef' makes the generator keep the whole scale inside the
            // treble range. Difficulty widens the palette; medium/hard use direction
            // 'both', which generatePractices() expands to half ascending / half
            // descending exactly like PracticeScale does.
            'scale-practice' => match ($difficulty) {
                'easy' => ['allowed_scale_types' => $scaleTypesEasy, 'allowed_root_notes' => ['C', 'D', 'E', 'F', 'G', 'A', 'B'], 'direction' => 'ascending', 'distractor_pool' => $scaleTypesEasy, 'clef' => 'treble'],
                'hard' => ['allowed_scale_types' => $scaleTypesHard, 'allowed_root_notes' => ['C', 'D', 'E', 'F', 'G', 'A', 'B'], 'direction' => 'both', 'distractor_pool' => $scaleTypesHard, 'clef' => 'treble'],
                default => ['allowed_scale_types' => $scaleTypesMedium, 'allowed_root_notes' => ['C', 'D', 'E', 'F', 'G', 'A', 'B'], 'direction' => 'both', 'distractor_pool' => $scaleTypesMedium, 'clef' => 'treble'],
            },
            // Rhythm rules mirror the Exercise Setup Studio rhythm flow
            // (PracticeRhythm::mount): the difficulty-keyed beat-cell pool
            // (LearningPathQuestionGenerator::rhythmCells) drives generation
            // directly — no allowed_note_values filter, so dotted values, busier
            // subdivisions and (hard) triplets arrive with the difficulty exactly
            // as in Exercise Setup. Rests follow the include_rests rule: exactly
            // one rest injected post-assembly, never on the first beat (easy has
            // none). Time signature and tempo have no user input on the AI page,
            // so the difficulty also widens the meter pool and speeds the tempo.
            'rhythm-practice' => match ($difficulty) {
                'easy' => ['rhythm_difficulty' => 'easy', 'include_rests' => false, 'time_signatures' => ['4/4'], 'tempo_range' => [56, 64], 'bars' => 1],
                'hard' => ['rhythm_difficulty' => 'hard', 'include_rests' => true, 'time_signatures' => ['4/4', '3/4', '6/8'], 'tempo_range' => [66, 76], 'bars' => 1],
                default => ['rhythm_difficulty' => 'medium', 'include_rests' => true, 'time_signatures' => ['4/4', '3/4'], 'tempo_range' => [60, 70], 'bars' => 1],
            },
            'melodic-dictation' => match ($difficulty) {
                'easy' => ['note_pool' => ['C4', 'D4', 'E4', 'F4', 'G4'], 'melody_length' => 4, 'clef' => 'treble', 'key_signatures' => ['C'], 'tempo_range' => [56, 64], 'bars' => 1],
                'hard' => ['note_pool' => ['C4', 'D4', 'E4', 'F4', 'G4', 'A4', 'B4', 'C5', 'D5'], 'melody_length' => 6, 'clef' => 'treble', 'key_signatures' => ['C', 'G', 'F'], 'tempo_range' => [66, 76], 'bars' => 2],
                default => ['note_pool' => ['C4', 'D4', 'E4', 'F4', 'G4', 'A4', 'B4'], 'melody_length' => 5, 'clef' => 'treble', 'key_signatures' => ['C', 'G'], 'tempo_range' => [60, 70], 'bars' => 1],
            },
            // Single Note: same generation engine + rules as Exercise Setup Studio
            // (PracticeSingleNote::mount) — allowed-note pool, treble octave range
            // (4–5), distractor_count 3 (target + 3 distractors = 4 options, which
            // is exactly what the practice-mixed answer grid renders). Only the
            // note pool and octave spread vary by difficulty.
            'single-note-practice' => match ($difficulty) {
                'easy' => ['allowed_notes' => ['C', 'D', 'E', 'G', 'A'], 'octave_range' => ['4'], 'distractor_count' => 3],
                'hard' => ['allowed_notes' => ['C', 'C#', 'D', 'D#', 'E', 'F', 'F#', 'G', 'G#', 'A', 'A#', 'B'], 'octave_range' => ['4', '5'], 'distractor_count' => 3],
                default => ['allowed_notes' => ['C', 'D', 'E', 'F', 'G', 'A', 'B'], 'octave_range' => ['4'], 'distractor_count' => 3],
            },
            default => [],
        };
    }

    /**
     * Build an adaptive config for a practice type from the user's per-interval
     * accuracy multipliers. Weaker (and untested) intervals are over-represented
     * so they come up more often.
     *
     * Strategy: start from the medium preset (notes/octave/clef + distractor
     * settings) and replace only the content pool with a weight-expanded list,
     * where each interval is repeated round(multiplier * 2) times. For
     * non-interval types, or when the user has no answered questions for the
     * type yet (cold start), the medium preset is returned unchanged.
     */
    private function buildAdaptiveConfig(string $slug, int $userId): array
    {
        $medium = $this->buildLocalConfig($slug, 'medium');

        // Rhythm: pick the difficulty preset from the user's rhythm practice
        // history (UserPractice accuracy across all rhythm modes). Cold start
        // (fewer than 10 answered questions) uses the medium preset, matching
        // Exercise Setup's adaptive → medium mapping.
        if ($slug === 'rhythm-practice') {
            $practiceId = Practice::where('slug', 'rhythm-practice')->value('id');
            $stats = UserPractice::where('user_id', $userId)->where('practice_id', $practiceId)->first();
            if ($stats && $stats->total_questions >= 10) {
                $level = $stats->score >= 85 ? 'hard' : ($stats->score >= 55 ? 'medium' : 'easy');

                return $this->buildLocalConfig($slug, $level);
            }

            return $medium;
        }

        // Adaptive weighting only applies to the interval practice types.
        $intervalSlugs = array_column(UserIntervalStat::INTERVAL_PRACTICE_TYPES, 'slug');
        if (! in_array($slug, $intervalSlugs, true)) {
            return $medium;
        }

        $all = UserIntervalStat::accuracyMultipliersForUser($userId);
        $entry = collect($all)->firstWhere('slug', $slug);
        if ($entry === null || empty($entry['intervals'])) {
            return $medium;
        }

        // Cold start: nothing answered for this type yet -> use the medium preset.
        $hasTested = collect($entry['intervals'])->contains(fn ($iv) => $iv['tested']);
        if (! $hasTested) {
            return $medium;
        }

        $copies = fn (float $multiplier): int => max(1, (int) round($multiplier * 2));

        return match ($slug) {
            'melodic-interval-practice',
            'harmonic-interval-practice',
            'interval-construction-practice' => array_merge($medium, [
                'allowed_intervals' => $this->weightedIntervalNames($entry['intervals'], $copies),
            ]),

            'interval-direction-practice' => array_merge($medium, [
                'allowed_intervals_semitones' => $this->weightedSemitones($entry['intervals'], $copies),
            ]),

            'interval-comparison-practice' => array_merge($medium, [
                'allowed_interval_pairs' => $this->weightedComparisonPairs($entry['intervals'], $copies),
            ]),

            default => $medium,
        };
    }

    /**
     * Weight-expanded list of interval names (each repeated by its multiplier).
     */
    private function weightedIntervalNames(array $intervals, callable $copies): array
    {
        $out = [];
        foreach ($intervals as $iv) {
            for ($i = 0, $n = $copies($iv['multiplier']); $i < $n; $i++) {
                $out[] = $iv['interval'];
            }
        }

        return $out ?: ['Major 3rd'];
    }

    /**
     * Weight-expanded list of semitone distances (direction practice).
     */
    private function weightedSemitones(array $intervals, callable $copies): array
    {
        $out = [];
        foreach ($intervals as $iv) {
            for ($i = 0, $n = $copies($iv['multiplier']); $i < $n; $i++) {
                $out[] = $iv['semitones'];
            }
        }

        return $out ?: [5, 7];
    }

    /**
     * Weight-expanded list of comparison pairs. Each weak interval is paired
     * against a randomly chosen different-sized one, both expressed as the
     * Exercise Setup comparison component's canonical same-octave C-root pairs
     * (PracticeIntervalComparison::POOL_TO_PAIR — diatonic flat spelling, never
     * a unison). Octave (12) is skipped since it cannot be expressed as a
     * same-octave note pair.
     */
    private function weightedComparisonPairs(array $intervals, callable $copies): array
    {
        // Canonical C-root pair per semitone size 1–11 (POOL_TO_PAIR order: m2…M7).
        $pairBySemitone = array_values(PracticeIntervalComparison::POOL_TO_PAIR);

        $out = [];
        foreach ($intervals as $iv) {
            $s = $iv['semitones'];
            if ($s < 1 || $s > 11) {
                continue;
            }
            for ($i = 0, $n = $copies($iv['multiplier']); $i < $n; $i++) {
                // m2 (1 semitone) has no smaller ES interval — pair it against a
                // random larger one instead so the weak interval still appears.
                $other = $s === 1 ? random_int(2, 11) : random_int(1, $s - 1);
                $out[] = [$pairBySemitone[$s - 1], $pairBySemitone[$other - 1]];
            }
        }

        return $out ?: PracticeIntervalComparison::buildPairsFromPool(['M3', 'P5', 'M7']);
    }

    /**
     * Sanitize raw OpenAI-generated questions through MusicTheoryService.
     *
     * This is the single post-processing gate for all AI questions before they
     * are stored in session. It normalises note names, resolves cross-octave
     * boundaries for direction questions, and re-derives direction from actual
     * MIDI pitches so the staff, audio, and answer validator are always consistent.
     */
    private function sanitizeAIQuestions(array $questions): array
    {
        $music = app(MusicTheoryService::class);

        $sanitized = array_map(function (array $q) use ($music): ?array {
            $type = $q['type'] ?? '';

            switch ($type) {
                case 'interval-direction':
                    $note1 = $music->normalizeNote($q['note1'] ?? 'C');
                    $note2 = $music->normalizeNote($q['note2'] ?? 'E');
                    $octave1 = (int) ($q['octave'] ?? 4);
                    $intent = $q['direction'] ?? 'ascending';

                    $octave2 = $music->resolveNote2OctaveFromDirection($note1, $octave1, $note2, $intent);

                    $q['note1'] = $note1;
                    $q['note2'] = $note2;
                    $q['note2_octave'] = $octave2;
                    $q['direction'] = $music->getDirection($note1, $octave1, $note2, $octave2);
                    break;

                case 'melodic-interval':
                case 'harmonic-interval':
                    $note1 = $music->normalizeNote($q['note1'] ?? 'C');
                    $octave1 = (int) ($q['octave'] ?? 4);
                    $interval = $music->normalizeIntervalName($q['interval'] ?? 'Major 3rd');
                    $result = $music->preferredNoteAboveByInterval($note1, $octave1, $interval);

                    $q['note1'] = $note1;
                    $q['interval'] = $interval;
                    $q['note2'] = $result['note'] ?? $music->normalizeNote($q['note2'] ?? 'E');
                    $q['note2_octave'] = $result['octave'] ?? $octave1;
                    break;

                case 'interval-construction':
                    $note1 = $music->normalizeNote($q['note1'] ?? 'C');
                    $octave1 = (int) ($q['octave'] ?? 4);
                    $interval = $music->normalizeIntervalName($q['interval'] ?? 'Major 3rd');
                    $result = $music->preferredNoteAboveByInterval($note1, $octave1, $interval);

                    $q['note1'] = $note1;
                    $q['interval'] = $interval;
                    $q['note2'] = $result['note'] ?? $music->normalizeNote($q['note2'] ?? 'E');
                    $q['note2_octave'] = $result['octave'] ?? $octave1;
                    break;

                case 'interval-comparison':
                    $normalizeIntervalNotes = function (string $pair) use ($music): string {
                        $parts = array_map('trim', explode(',', $pair));
                        $normalized = array_map(fn ($n) => $music->normalizeNote($n), $parts);

                        return implode(',', $normalized);
                    };
                    $q['interval_a'] = $normalizeIntervalNotes($q['interval_a'] ?? 'C,E');
                    $q['interval_b'] = $normalizeIntervalNotes($q['interval_b'] ?? 'C,G');

                    // Re-derive target from the actual same-octave pitch gap (how the
                    // staff/audio present it) — GPT often labels this wrong. Drop the
                    // question entirely when the two intervals are equal in size, since
                    // "which is larger" then has no correct answer.
                    $larger = $music->largerIntervalPair($q['interval_a'], $q['interval_b']);
                    if ($larger === null) {
                        return null;
                    }
                    $q['target'] = $larger;
                    break;

                case 'single-note':
                    $q['target'] = $music->normalizeNote($q['target'] ?? 'C');
                    $rawOptions = array_map('trim', explode(',', $q['other_options'] ?? ''));
                    $normalized = array_values(array_unique(
                        array_map(fn ($n) => $music->normalizeNote($n), array_filter($rawOptions))
                    ));
                    if (! in_array($q['target'], $normalized, true)) {
                        array_unshift($normalized, $q['target']);
                    }
                    $q['other_options'] = implode(',', $normalized);
                    break;
            }

            return $q;
        }, $questions);

        // Drop any questions sanitization rejected (e.g. equal-size interval comparisons).
        return array_values(array_filter($sanitized, fn ($q) => $q !== null));
    }

    public function generateCoachNotes($data)
    {
        $questions = $data['questions'] ?? [];
        $answers = $data['answers'] ?? [];

        $apikey = config('services.openai.key');
        if (! $apikey) {
            return ['error' => 'OpenAI API key not configured'];
        }

        $model = SystemSetting::get('ai_model', 'gpt-4.1-mini');
        $start = microtime(true);

        try {
            $client = OpenAI::client($apikey);

            $response = $client->chat()->create([
                'model' => $model,
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => implode("\n", [
                            'You are an encouraging and supportive ear training coach for Harmoniva.',
                            'Analyze the provided practice session data and generate personalized, helpful feedback for the student.',
                            '',
                            'Rules that MUST be followed:',
                            '- Base all feedback strictly on the provided session data. Do not invent skills, patterns, progress history, or weaknesses that are not present in the input.',
                            '- Describe weak areas as practice opportunities, not failures.',
                            '- Keep all feedback short, clear, and actionable.',
                            '- Calculate score_percentage by comparing the provided answers against the correct answers in the session data.',
                            '- Mention specific patterns when visible: difficulty with ascending vs. descending intervals, harmonic vs. melodic intervals, specific interval types, rhythm, chords, scales, or note recognition.',
                            '- If session data is limited or ambiguous, acknowledge it gently and give practical next-step advice based only on what is available.',
                            '- Suggestions must be specific and practical — avoid generic advice.',
                            '- Use canonical English values only. Do not include translated labels.',
                            '',
                            'Tone: supportive, calm, encouraging, and teacher-like. Avoid exaggeration or criticism.',
                            '',
                            'Output field guidance:',
                            '- summary: 1–2 sentences.',
                            '- strengths: 2–4 short items.',
                            '- weak_areas: 1–4 short items, framed as opportunities.',
                            '- suggestions: 2–4 specific, practical next steps.',
                            '- encouragement: 1 short motivating sentence.',
                        ]),
                    ],
                    [
                        'role' => 'user',
                        'content' => 'Analyze this ear training session:
                            Questions: '.json_encode($questions).'
                            Answers: '.json_encode($answers).'

                            Provide helpful feedback to the student.
                            1. Summarize their performance.
                            2. Identify strengths.
                            3. Identify weak areas (e.g. specific intervals, directions).
                            4. Suggest what to practice next.

                            Keep it encouraging and concise. Address the student directly.',
                    ],
                ],
                'response_format' => [
                    'type' => 'json_schema',
                    'json_schema' => [
                        'name' => 'coach_feedback',
                        'strict' => true,
                        'schema' => [
                            'type' => 'object',
                            'properties' => [
                                'summary' => [
                                    'type' => 'string',
                                    'description' => 'A brief summary of the student performance',
                                ],
                                'score_percentage' => [
                                    'type' => 'number',
                                    'description' => 'The percentage score (0-100)',
                                ],
                                'strengths' => [
                                    'type' => 'array',
                                    'items' => ['type' => 'string'],
                                    'description' => 'List of areas where the student performed well',
                                ],
                                'weak_areas' => [
                                    'type' => 'array',
                                    'items' => ['type' => 'string'],
                                    'description' => 'List of areas that need improvement',
                                ],
                                'suggestions' => [
                                    'type' => 'array',
                                    'items' => ['type' => 'string'],
                                    'description' => 'Specific practice suggestions for improvement',
                                ],
                                'encouragement' => [
                                    'type' => 'string',
                                    'description' => 'An encouraging message for the student',
                                ],
                            ],
                            'required' => ['summary', 'score_percentage', 'strengths', 'weak_areas', 'suggestions', 'encouragement'],
                            'additionalProperties' => false,
                        ],
                    ],
                ],
            ]);

            AiUsageLogger::logSuccess('session_coach_notes', $model, $response->usage, auth()->id(), [], (int) ((microtime(true) - $start) * 1000));

            return json_decode($response->choices[0]->message->content, true);
        } catch (\Exception $e) {
            AiUsageLogger::logError('session_coach_notes', $model, $e->getMessage(), auth()->id(), [], (int) ((microtime(true) - $start) * 1000));
            \Log::error('OpenAI API error in generateCoachNotes: '.$e->getMessage());

            return [
                'summary' => 'Unable to generate coach notes at this time.',
                'score_percentage' => 0,
                'strengths' => [],
                'weak_areas' => [],
                'suggestions' => ['Please try again later.'],
                'encouragement' => 'Keep practicing!',
            ];
        }
    }
}
