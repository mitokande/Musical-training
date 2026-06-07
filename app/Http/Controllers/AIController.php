<?php

namespace App\Http\Controllers;

use App\Models\IntervalDirectionPractice;
use App\Models\LearningPathExercise;
use App\Models\Practice;
use App\Models\SingleNotePractice;
use App\Models\UserIntervalStat;
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

        try {
            $client = OpenAI::client($apikey);

            $response = $client->chat()->create([
                'model' => 'gpt-4.1-mini',
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
            \Log::error('OpenAI API error in generateIntervalDirectionPractice: '.$e->getMessage());

            return response()->json(['error' => 'Failed to generate practice. Please try again.'], 500);
        }
    }

    public function generatePractices(Request $request)
    {
        $validated = $request->validate([
            'exercise_types' => 'required|array|min:1',
            'exercise_types.*' => 'integer|exists:practices,id',
            'num_questions' => 'required|integer|min:1|max:50',
            'difficulty' => 'required|string|in:easy,medium,hard,adaptive',
            // Rhythm Dictation: user-chosen time signature + tempo (optional; only
            // submitted when the Rhythm Dictation type is selected in the form).
            'rhythm_time_signature' => 'nullable|in:2/4,3/4,4/4,6/8,9/8,2/2,3/2,4/2',
            'rhythm_tempo' => 'nullable|integer|min:40|max:208',
        ]);

        $practiceTypes = Practice::whereIn('id', $validated['exercise_types'])->get();

        // Types handled via OpenAI structured output
        $aiPracticeClasses = [
            'single-note-practice' => SingleNotePractice::class,
        ];

        // Types generated locally (no OpenAI needed) — all interval types are now
        // deterministic (root note + semitones) via LearningPathQuestionGenerator.
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
        ];

        $aiPracticeTypes = $practiceTypes->filter(fn ($p) => isset($aiPracticeClasses[$p->slug]));
        $localPracticeTypes = $practiceTypes->filter(fn ($p) => in_array($p->slug, $localTypeSlugs));

        if ($aiPracticeTypes->isEmpty() && $localPracticeTypes->isEmpty()) {
            return back()->with('error', 'No valid practice types selected.');
        }

        $numQuestions = (int) $validated['num_questions'];
        $difficulty = $validated['difficulty'];
        $totalTypes = $aiPracticeTypes->count() + $localPracticeTypes->count();
        $perType = max(1, (int) ceil($numQuestions / max(1, $totalTypes)));

        // ── Local question generation (chord / scale / rhythm / melodic-dictation) ──
        $localQuestions = [];
        if ($localPracticeTypes->isNotEmpty()) {
            $generator = app(LearningPathQuestionGenerator::class);

            foreach ($localPracticeTypes as $practiceType) {
                $typeConfig = $difficulty === 'adaptive'
                    ? $this->buildAdaptiveConfig($practiceType->slug, (int) auth()->id())
                    : $this->buildLocalConfig($practiceType->slug, $difficulty);

                // Rhythm Dictation drives generation from the user's form choices
                // (time signature + tempo). Bars are assembled from a beat-aligned cell
                // pool keyed by difficulty (see LearningPathQuestionGenerator::rhythmCells).
                $rhythmValues = null;
                if ($practiceType->slug === 'rhythm-practice') {
                    $tempo = (int) ($validated['rhythm_tempo'] ?? 90);
                    $rhythmValues = $this->rhythmPaletteForDifficulty($difficulty);
                    $typeConfig['time_signatures'] = [$validated['rhythm_time_signature'] ?? '4/4'];
                    $typeConfig['tempo_range'] = [$tempo, $tempo];
                    $typeConfig['rhythm_difficulty'] = $difficulty;
                    $typeConfig['bars'] = 1;
                }

                $config = array_merge(
                    ['practice_type' => $practiceType->slug],
                    $typeConfig
                );
                $exercise = new LearningPathExercise;
                $exercise->config_json = $config;

                try {
                    $generated = $generator->generate($exercise, $perType);
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
                        // Construction stores its answer options as the `_options`
                        // relation (not a model attribute), so promote it so the
                        // options survive into the session question array.
                        if (! isset($attrs['options']) && $q->relationLoaded('_options')) {
                            $attrs['options'] = $q->getRelation('_options');
                        }
                        // Add type discriminator matching convertAIQuestionsToPractices() cases
                        $attrs['type'] = $this->slugToQuestionType($practiceType->slug);
                        // Carry the allowed note-value pool so the builder palette matches
                        // what could actually have been generated for this difficulty.
                        if ($rhythmValues !== null) {
                            $attrs['allowed_values'] = $rhythmValues;
                        }
                        $localQuestions[] = $attrs;
                    }
                } catch (\Exception $e) {
                    \Log::error("Local question generation failed for {$practiceType->slug}: ".$e->getMessage());
                }
            }
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

            try {
                $practiceNames = $aiPracticeTypes->pluck('name')->toArray();
                $client = OpenAI::client($apikey);
                $response = $client->chat()->create([
                    'model' => 'gpt-4.1-mini',
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

                $decoded = json_decode($response->choices[0]->message->content, true);
                $aiQuestions = $this->sanitizeAIQuestions($decoded['questions'] ?? []);
            } catch (\Exception $e) {
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
            default => $slug,
        };
    }

    /**
     * The builder palette (button set) offered for Rhythm Dictation at a given difficulty.
     * Generation itself uses a beat-cell pool (LearningPathQuestionGenerator::rhythmCells);
     * this is just which note buttons the student can place. Rests are always available
     * (the generated rhythm never uses them, but the student may). Hard adds a triplet button.
     */
    private function rhythmPaletteForDifficulty(string $difficulty): array
    {
        $rests = ['half_rest', 'quarter_rest', 'eighth_rest'];

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
        $allIntervalNames = array_keys(MusicTheoryService::INTERVAL_SEMITONES);

        return match ($slug) {
            'melodic-interval-practice', 'harmonic-interval-practice' => match ($difficulty) {
                'easy' => ['allowed_intervals' => ['Major 3rd', 'Perfect 5th', 'Perfect Octave'], 'allowed_notes' => ['C', 'D', 'E', 'F', 'G'], 'octave_range' => ['4'], 'distractor_count' => 2, 'distractor_mode' => 'far'],
                'hard' => ['allowed_intervals' => $allIntervalNames, 'allowed_notes' => ['C', 'C#', 'D', 'D#', 'E', 'F', 'F#', 'G', 'G#', 'A', 'A#', 'B'], 'octave_range' => ['3', '4', '5'], 'distractor_count' => 5, 'distractor_mode' => 'near'],
                default => ['allowed_intervals' => ['Minor 2nd', 'Major 2nd', 'Minor 3rd', 'Major 3rd', 'Perfect 4th', 'Perfect 5th', 'Major 6th', 'Perfect Octave'], 'allowed_notes' => ['C', 'D', 'E', 'F', 'G', 'A', 'B'], 'octave_range' => ['4'], 'distractor_count' => 3, 'distractor_mode' => 'mixed'],
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
            'interval-construction-practice' => match ($difficulty) {
                'easy' => ['allowed_intervals' => ['Major 3rd', 'Perfect 5th', 'Perfect Octave'], 'allowed_root_notes' => ['C', 'D', 'E', 'F', 'G'], 'octave' => 4, 'distractor_count' => 2, 'distractor_mode' => 'far'],
                'hard' => ['allowed_intervals' => $allIntervalNames, 'allowed_root_notes' => ['C', 'C#', 'D', 'D#', 'E', 'F', 'F#', 'G', 'G#', 'A', 'A#', 'B'], 'octave' => 4, 'distractor_count' => 5, 'distractor_mode' => 'near'],
                default => ['allowed_intervals' => ['Minor 2nd', 'Major 2nd', 'Minor 3rd', 'Major 3rd', 'Perfect 4th', 'Perfect 5th', 'Major 6th', 'Perfect Octave'], 'allowed_root_notes' => ['C', 'D', 'E', 'F', 'G', 'A', 'B'], 'octave' => 4, 'distractor_count' => 3, 'distractor_mode' => 'mixed'],
            },
            'interval-comparison-practice' => match ($difficulty) {
                'easy' => ['allowed_interval_pairs' => [['C,D', 'C,G'], ['C,E', 'C,C'], ['C,F', 'C,A']], 'octave' => '4', 'clef' => 'treble'],
                'hard' => ['allowed_interval_pairs' => [['C,C#', 'C,D'], ['C,F#', 'C,G'], ['C,A', 'C,A#'], ['C,D#', 'C,E'], ['C,G#', 'C,A']], 'octave' => '4', 'clef' => 'treble'],
                default => ['allowed_interval_pairs' => [['C,D', 'C,F'], ['C,E', 'C,A'], ['C,G', 'C,C'], ['C,D', 'C,E']], 'octave' => '4', 'clef' => 'treble'],
            },
            'chord-practice' => match ($difficulty) {
                'easy' => ['allowed_chord_types' => ['Major', 'Minor'], 'allowed_root_notes' => ['C', 'D', 'E', 'F', 'G'], 'voicing' => 'block', 'include_inversions' => false, 'distractor_pool' => ['Diminished', 'Augmented', 'Dominant 7th']],
                'hard' => ['allowed_chord_types' => ['Major', 'Minor', 'Diminished', 'Augmented', 'Dominant 7th', 'Major 7th', 'Minor 7th'], 'allowed_root_notes' => ['C', 'C#', 'D', 'D#', 'E', 'F', 'F#', 'G', 'G#', 'A', 'A#', 'B'], 'voicing' => 'block', 'include_inversions' => true, 'distractor_pool' => ['Half Diminished', 'Diminished 7th', 'Augmented 7th']],
                default => ['allowed_chord_types' => ['Major', 'Minor', 'Diminished', 'Dominant 7th'], 'allowed_root_notes' => ['C', 'D', 'E', 'F', 'G', 'A', 'B'], 'voicing' => 'block', 'include_inversions' => false, 'distractor_pool' => ['Augmented', 'Major 7th', 'Minor 7th']],
            },
            'scale-practice' => match ($difficulty) {
                'easy' => ['allowed_scale_types' => ['Major', 'Natural Minor'], 'allowed_root_notes' => ['C', 'D', 'E', 'F', 'G'], 'direction' => 'ascending', 'distractor_pool' => ['Harmonic Minor', 'Pentatonic', 'Blues']],
                'hard' => ['allowed_scale_types' => ['Major', 'Natural Minor', 'Harmonic Minor', 'Dorian', 'Phrygian', 'Lydian', 'Mixolydian'], 'allowed_root_notes' => ['C', 'D', 'E', 'F', 'G', 'A', 'B'], 'direction' => 'ascending', 'distractor_pool' => ['Melodic Minor', 'Pentatonic', 'Blues', 'Locrian']],
                default => ['allowed_scale_types' => ['Major', 'Natural Minor', 'Harmonic Minor', 'Pentatonic'], 'allowed_root_notes' => ['C', 'D', 'E', 'F', 'G', 'A'], 'direction' => 'ascending', 'distractor_pool' => ['Blues', 'Dorian', 'Mixolydian']],
            },
            'rhythm-practice' => match ($difficulty) {
                'easy' => ['time_signatures' => ['4/4'], 'allowed_note_values' => ['quarter', 'half'], 'tempo_range' => [70, 80], 'bars' => 1],
                'hard' => ['time_signatures' => ['4/4', '3/4', '6/8'], 'allowed_note_values' => ['quarter', 'eighth', 'half', 'sixteenth', 'quarter_rest'], 'tempo_range' => [88, 100], 'bars' => 2],
                default => ['time_signatures' => ['4/4', '3/4'], 'allowed_note_values' => ['quarter', 'eighth', 'half', 'quarter_rest'], 'tempo_range' => [76, 88], 'bars' => 1],
            },
            'melodic-dictation' => match ($difficulty) {
                'easy' => ['note_pool' => ['C4', 'D4', 'E4', 'F4', 'G4'], 'melody_length' => 4, 'clef' => 'treble', 'key_signatures' => ['C'], 'tempo_range' => [56, 64], 'bars' => 1],
                'hard' => ['note_pool' => ['C4', 'D4', 'E4', 'F4', 'G4', 'A4', 'B4', 'C5', 'D5'], 'melody_length' => 6, 'clef' => 'treble', 'key_signatures' => ['C', 'G', 'F'], 'tempo_range' => [66, 76], 'bars' => 2],
                default => ['note_pool' => ['C4', 'D4', 'E4', 'F4', 'G4', 'A4', 'B4'], 'melody_length' => 5, 'clef' => 'treble', 'key_signatures' => ['C', 'G'], 'tempo_range' => [60, 70], 'bars' => 1],
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
     * Weight-expanded list of comparison pairs. Each weak interval becomes the
     * larger member of a pair, paired against a randomly chosen smaller one
     * (both rooted at C within a single octave). Octave (12) is skipped since it
     * cannot be expressed as a same-letter single-octave note pair.
     */
    private function weightedComparisonPairs(array $intervals, callable $copies): array
    {
        $chroma = MusicTheoryService::CHROMATIC_NOTES; // indices 0-11
        $out = [];
        foreach ($intervals as $iv) {
            $s = $iv['semitones'];
            if ($s < 1 || $s > 11) {
                continue;
            }
            for ($i = 0, $n = $copies($iv['multiplier']); $i < $n; $i++) {
                $smaller = random_int(0, $s - 1);
                $out[] = ['C,'.$chroma[$smaller], 'C,'.$chroma[$s]];
            }
        }

        return $out ?: [['C,D', 'C,F'], ['C,E', 'C,A'], ['C,G', 'C,C']];
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

        try {
            $client = OpenAI::client($apikey);

            $response = $client->chat()->create([
                'model' => 'gpt-4.1-mini',
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

            return json_decode($response->choices[0]->message->content, true);
        } catch (\Exception $e) {
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
