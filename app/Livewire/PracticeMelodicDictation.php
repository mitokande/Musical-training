<?php

namespace App\Livewire;

use App\Livewire\Concerns\HandlesPracticeData;
use App\Models\MelodicDictationPractice;
use App\Models\Practice;
use App\Models\UserPractice;
use App\Services\LearningPathQuestionGenerator;
use App\Services\TonalMelodyGenerator;
use Livewire\Component;

class PracticeMelodicDictation extends Component
{
    use HandlesPracticeData;

    public $currentPracticeIndex = 0;

    public $settings = [];

    public $replayLimit = null;

    public $feedbackMode = 'immediate';

    public $timeLimitSeconds = 0;

    public $dictationTimeSignature = '4/4';

    public $dictationKeySignature = 'C Major / A minor';

    public $dictationTempo = 50;

    public $dictationMetronome = true;

    public $dictationNoteValues = null;

    public $dictationMode = 'major';

    public $difficulty = 'intermediate';

    // Beat values in 16th-note units
    protected const BEAT16 = [
        'whole' => 16, 'half' => 8, 'dotted-half' => 12,
        'quarter' => 4, 'dotted-quarter' => 6,
        'eighth' => 2, 'dotted-eighth' => 3, 'sixteenth' => 1,
        'whole_rest' => 16, 'half_rest' => 8, 'quarter_rest' => 4, 'eighth_rest' => 2,
    ];

    // Rhythm pattern pools for simple time (each sub-array fills the stated beat count)
    private const SIMPLE_ONE_BEAT = [
        ['quarter'],
        ['eighth', 'eighth'],
        ['sixteenth', 'sixteenth', 'sixteenth', 'sixteenth'],
        ['eighth', 'sixteenth', 'sixteenth'],
        ['sixteenth', 'sixteenth', 'eighth'],
        ['sixteenth', 'eighth', 'sixteenth'],
        ['dotted-eighth', 'sixteenth'],
        ['sixteenth', 'dotted-eighth'],
    ];

    private const SIMPLE_TWO_BEAT = [
        ['half'],
        ['dotted-quarter', 'eighth'],
        ['eighth', 'quarter', 'eighth'],
        ['eighth', 'dotted-quarter'],
    ];

    private const SIMPLE_THREE_BEAT = [
        ['dotted-half'],
    ];

    private const SIMPLE_FOUR_BEAT = [
        ['whole'],
    ];

    // Rhythm pattern pools for compound time (each sub-array fills one dotted-quarter beat group)
    private const COMPOUND_ONE_BEAT = [
        ['dotted-quarter'],
        ['quarter', 'eighth'],
        ['eighth', 'quarter'],
        ['eighth', 'eighth', 'eighth'],
        ['eighth', 'eighth', 'sixteenth', 'sixteenth'],
        ['sixteenth', 'sixteenth', 'eighth', 'eighth'],
        ['eighth', 'sixteenth', 'sixteenth', 'eighth'],
        ['sixteenth', 'sixteenth', 'sixteenth', 'sixteenth', 'eighth'],
        ['sixteenth', 'sixteenth', 'eighth', 'sixteenth', 'sixteenth'],
        ['eighth', 'sixteenth', 'sixteenth', 'sixteenth', 'sixteenth'],
        ['sixteenth', 'sixteenth', 'sixteenth', 'sixteenth', 'sixteenth', 'sixteenth'],
        ['dotted-eighth', 'sixteenth', 'eighth'],
        ['sixteenth', 'dotted-eighth', 'eighth'],
        ['eighth', 'dotted-eighth', 'sixteenth'],
        ['eighth', 'sixteenth', 'dotted-eighth'],
    ];

    private const COMPOUND_TWO_BEAT = [
        ['dotted-half'],
    ];

    /**
     * Level-filtered rhythm pattern pools, straight from the pedagogy spec.
     * Keys are the pattern length in beats (simple meters: quarter beats;
     * compound meters: dotted-quarter groups). Higher levels extend the
     * previous level's list, so every beginner pattern stays available.
     */
    private function rhythmPatterns(bool $isCompound, string $difficulty): array
    {
        if ($isCompound) {
            $patterns = [
                1 => [
                    ['dotted-quarter'],
                    ['quarter', 'eighth'],
                    ['eighth', 'quarter'],
                    ['eighth', 'eighth', 'eighth'],
                    ['eighth', 'eighth', 'sixteenth', 'sixteenth'],
                ],
                2 => [
                    ['dotted-half'],
                ],
            ];
            if ($difficulty === 'intermediate' || $difficulty === 'advanced') {
                $patterns[1] = array_merge($patterns[1], [
                    ['sixteenth', 'sixteenth', 'eighth', 'eighth'],
                    ['eighth', 'sixteenth', 'sixteenth', 'eighth'],
                    ['sixteenth', 'sixteenth', 'sixteenth', 'sixteenth', 'eighth'],
                    ['sixteenth', 'sixteenth', 'eighth', 'sixteenth', 'sixteenth'],
                    ['eighth', 'sixteenth', 'sixteenth', 'sixteenth', 'sixteenth'],
                    ['sixteenth', 'sixteenth', 'sixteenth', 'sixteenth', 'sixteenth', 'sixteenth'],
                ]);
            }
            if ($difficulty === 'advanced') {
                $patterns[1] = array_merge($patterns[1], [
                    ['dotted-eighth', 'sixteenth', 'eighth'],
                    ['sixteenth', 'dotted-eighth', 'eighth'],
                    ['eighth', 'dotted-eighth', 'sixteenth'],
                    ['eighth', 'sixteenth', 'dotted-eighth'],
                ]);
            }

            return $patterns;
        }

        $patterns = [
            1 => [
                ['quarter'],
                ['eighth', 'eighth'],
                ['sixteenth', 'sixteenth', 'sixteenth', 'sixteenth'],
            ],
            2 => [
                ['half'],
            ],
            3 => [
                ['dotted-half'],
            ],
            4 => [
                ['whole'],
            ],
        ];
        if ($difficulty === 'intermediate' || $difficulty === 'advanced') {
            $patterns[1] = array_merge($patterns[1], [
                ['eighth', 'sixteenth', 'sixteenth'],
                ['sixteenth', 'sixteenth', 'eighth'],
            ]);
            $patterns[2] = array_merge($patterns[2], [
                ['dotted-quarter', 'eighth'],
                ['eighth', 'quarter', 'eighth'],
            ]);
        }
        if ($difficulty === 'advanced') {
            $patterns[1] = array_merge($patterns[1], [
                ['sixteenth', 'eighth', 'sixteenth'],
                ['dotted-eighth', 'sixteenth'],
                ['sixteenth', 'dotted-eighth'],
            ]);
            $patterns[2] = array_merge($patterns[2], [
                ['eighth', 'dotted-quarter'],
            ]);
        }

        return $patterns;
    }

    public function mount($practices)
    {
        $settings = session('exercise_settings', []);
        session()->forget('exercise_settings');

        if (! empty($settings)) {
            $this->settings = $settings;
            $this->replayLimit = $settings['replay_limit'] ?? null;
            $this->feedbackMode = $settings['feedback_mode'] ?? 'immediate';
            $this->timeLimitSeconds = (int) ($settings['time_limit_seconds'] ?? 0);
            $this->dictationTimeSignature = $settings['dictation_time_signature'] ?? '4/4';
            $this->dictationKeySignature = $settings['dictation_key_signature'] ?? 'C Major / A minor';
            $this->dictationTempo = (int) ($settings['dictation_tempo'] ?? 50);
            $this->dictationMetronome = $settings['dictation_metronome'] ?? true;
            $this->dictationMode = $settings['dictation_mode'] ?? 'major';
            $this->difficulty = $settings['dictation_difficulty'] ?? 'intermediate';

            // Expand allowed note value buttons from setup settings
            $baseVals = $settings['dictation_note_values'] ?? ['quarter', 'eighth'];
            $addDotted = ! empty($settings['dictation_dotted']);
            $addRests = ! empty($settings['dictation_rests']);
            $allowedDurs = $baseVals;

            if ($addDotted) {
                foreach (['half' => 'dotted-half', 'quarter' => 'dotted-quarter', 'eighth' => 'dotted-eighth'] as $base => $dotted) {
                    if (in_array($base, $baseVals)) {
                        $allowedDurs[] = $dotted;
                    }
                }
            }

            if ($addRests) {
                foreach (['half' => 'half_rest', 'quarter' => 'quarter_rest', 'eighth' => 'eighth_rest'] as $base => $rest) {
                    if (in_array($base, $baseVals)) {
                        $allowedDurs[] = $rest;
                    }
                }
            }
            $this->dictationNoteValues = $allowedDurs;

            $count = (int) ($settings['question_count'] ?? 10);
            $bars = 2;
            $clef = $settings['clef'] ?? 'treble';
            $tempo = (int) ($settings['dictation_tempo'] ?? 50);
            $timeSig = $settings['dictation_time_signature'] ?? '4/4';
            $mode = $this->dictationMode;

            $majorKeyRoot = $this->parseMajorRoot($this->dictationKeySignature);
            $minorKeyRoot = $this->parseMinorRoot($this->dictationKeySignature);
            $tonicRoot = $mode === 'minor' ? $minorKeyRoot : $majorKeyRoot;

            $melodyGenerator = app(TonalMelodyGenerator::class);
            $context = $melodyGenerator->contextForKey($majorKeyRoot, $mode, $clef);

            $generator = app(LearningPathQuestionGenerator::class);

            $rhythmValues = array_values(array_filter($allowedDurs, fn ($v) => isset(self::BEAT16[$v]) && ! $this->isRest($v)));

            if (empty($rhythmValues)) {
                $rhythmValues = ['quarter'];
            }

            $generated = collect();
            for ($qi = 0; $qi < $count; $qi++) {
                $noteValues = $this->generateBeatPatternRhythm($bars, $timeSig, $rhythmValues);
                $noteCount = count($noteValues);
                $notes = $melodyGenerator->generateMelody($noteCount, $context, $this->difficulty);
                $notes = $melodyGenerator->applyAccidentals($notes, $majorKeyRoot, $mode, $this->difficulty);

                // Inject exactly one rest when the user enabled rests.
                if ($addRests) {
                    [$notes, $noteValues] = $this->injectOneRestIntoMelody($notes, $noteValues);
                }

                $q = new MelodicDictationPractice;
                $q->id = $qi + 1;
                $q->notes = $notes;
                $q->note_values = $noteValues;
                $q->time_signature = $timeSig;
                $q->key_signature = $majorKeyRoot;
                $q->tonic = $tonicRoot;
                $q->bars = $bars;
                $q->clef = $clef;
                $q->tempo = $tempo;
                $generated->push($q);
            }

            session(['exercise_practice_session' => [
                'practice_type' => 'melodic-dictation',
                'question_count' => $generated->count(),
                'questions' => $generator->serializeForSession($generated),
            ]]);

            $this->practiceDataArray = $this->serializePractices($generated->all());
        } else {
            $this->practiceDataArray = $this->serializePractices($practices);
        }
    }

    public function render()
    {
        $currentPractice = $this->buildModelFromData(MelodicDictationPractice::class, $this->getCurrentPracticeData());

        return view('livewire.practice-melodic-dictation', [
            'practices' => $this->practiceDataArray,
            'currentPractice' => $currentPractice,
            'currentPracticeIndex' => $this->currentPracticeIndex,
            'dictationTimeSignature' => $this->dictationTimeSignature,
            'dictationKeySignature' => $this->dictationKeySignature,
            'dictationTempo' => $this->dictationTempo,
            'dictationMetronome' => $this->dictationMetronome,
            'dictationNoteValues' => $this->dictationNoteValues,
            'dictationMode' => $this->dictationMode,
        ]);
    }

    public function getNextPractice()
    {
        $this->currentPracticeIndex++;
        $this->dispatch('practice-updated');
    }

    public function answerPractice($answer)
    {
        $practiceId = Practice::where('slug', 'melodic-dictation')->value('id');
        $userPractice = UserPractice::firstOrCreate(
            ['user_id' => auth()->id(), 'practice_id' => $practiceId],
            ['total_questions' => 0, 'correct_answers' => 0, 'incorrect_answers' => 0, 'score' => 0]
        );

        $data = $this->getCurrentPracticeData();
        $notes = $data['notes'] ?? [];

        if (is_string($notes)) {
            $notes = json_decode($notes, true) ?? [];
        }

        $correctNotes = implode(',', $notes);
        $userAnswer = strtoupper(preg_replace('/\s+/', '', trim($answer)));
        $correctAnswer = strtoupper(preg_replace('/\s+/', '', $correctNotes));
        $isCorrect = $userAnswer === $correctAnswer;

        $userPractice->total_questions++;

        if ($isCorrect) {
            $userPractice->correct_answers++;
        } else {
            $userPractice->incorrect_answers++;
        }

        $userPractice->score = $userPractice->total_questions > 0
            ? ($userPractice->correct_answers / $userPractice->total_questions) * 100
            : 0;
        $userPractice->save();

        return $isCorrect;
    }

    // ── Musical helpers ──────────────────────────────────────────────────────

    protected function parseMajorRoot(string $keySig): string
    {
        $part = strtok($keySig, ' ');

        return str_replace(['♯', '♭'], ['#', 'b'], $part);
    }

    protected function parseMinorRoot(string $keySig): string
    {
        $parts = explode(' / ', $keySig);
        $minorPart = $parts[1] ?? '';
        $part = strtok($minorPart, ' ') ?: 'A';

        return str_replace(['♯', '♭'], ['#', 'b'], $part);
    }

    /**
     * Generate a rhythm by selecting beat-level patterns from the pool.
     * Bars are filled one at a time so no pattern ever straddles a barline,
     * and later bars repeat the first bar's rhythm most of the time so the
     * phrase reads as a motif rather than two unrelated bars.
     * The returned array length always equals the required melody note count,
     * so notes[] and note_values[] are guaranteed to be in sync.
     */
    protected function generateBeatPatternRhythm(int $bars, string $timeSig, array $allowedValues): array
    {
        $barRhythms = [];
        for ($bar = 0; $bar < $bars; $bar++) {
            if ($bar > 0 && mt_rand(0, 9) < 6) {
                $barRhythms[] = $barRhythms[$bar - 1];

                continue;
            }
            $barRhythms[] = $this->generateOneBarRhythm($timeSig, $allowedValues);
        }

        return array_merge(...$barRhythms);
    }

    private function generateOneBarRhythm(string $timeSig, array $allowedValues): array
    {
        [$num, $den] = array_map('intval', explode('/', $timeSig));
        $isCompound = ($den === 8 && $num % 3 === 0);

        $canUse = fn (array $p) => empty(array_diff($p, $allowedValues));
        $result = [];

        if ($isCompound) {
            // Dotted-quarter is the fundamental beat unit in compound time — always allow it.
            $ext = array_unique(array_merge($allowedValues, ['dotted-quarter', 'dotted-half']));
            $canUseC = fn (array $p) => empty(array_diff($p, $ext));

            $totalGroups = intdiv($num, 3);

            $oneBeat = array_values(array_filter(self::COMPOUND_ONE_BEAT, $canUseC));
            $twoBeat = array_values(array_filter(self::COMPOUND_TWO_BEAT, $canUseC));
            if (empty($oneBeat)) {
                $oneBeat = [['dotted-quarter']];
            }

            $bg = 0;
            while ($bg < $totalGroups) {
                $rem = $totalGroups - $bg;
                if ($rem >= 2 && ! empty($twoBeat) && mt_rand(0, 5) === 0) {
                    $result = array_merge($result, $twoBeat[array_rand($twoBeat)]);
                    $bg += 2;
                } else {
                    $result = array_merge($result, $oneBeat[array_rand($oneBeat)]);
                    $bg += 1;
                }
            }
        } else {
            // /2 meters: each beat = half note = 2 quarter-note beats
            // 2/2 → 4, 3/2 → 6, 4/2 → 8 quarter-beat units per bar
            $totalBeats = ($den === 2) ? $num * 2 : $num;

            $oneBeat = array_values(array_filter(self::SIMPLE_ONE_BEAT, $canUse));
            $twoBeat = array_values(array_filter(self::SIMPLE_TWO_BEAT, $canUse));
            $threeBeat = array_values(array_filter(self::SIMPLE_THREE_BEAT, $canUse));
            $fourBeat = array_values(array_filter(self::SIMPLE_FOUR_BEAT, $canUse));
            if (empty($oneBeat)) {
                $oneBeat = [['quarter']];
            }

            $beat = 0;
            while ($beat < $totalBeats) {
                $rem = $totalBeats - $beat;
                if ($rem >= 4 && ! empty($fourBeat) && mt_rand(0, 9) === 0) {
                    $result = array_merge($result, $fourBeat[array_rand($fourBeat)]);
                    $beat += 4;
                } elseif ($rem >= 3 && ! empty($threeBeat) && mt_rand(0, 7) === 0) {
                    $result = array_merge($result, $threeBeat[array_rand($threeBeat)]);
                    $beat += 3;
                } elseif ($rem >= 2 && ! empty($twoBeat) && mt_rand(0, 3) === 0) {
                    $result = array_merge($result, $twoBeat[array_rand($twoBeat)]);
                    $beat += 2;
                } else {
                    $result = array_merge($result, $oneBeat[array_rand($oneBeat)]);
                    $beat += 1;
                }
            }
        }

        return $result;
    }

    protected function isRest(string $v): bool
    {
        return str_ends_with($v, '_rest');
    }

    /**
     * Replace one eligible note event with a rest of the same duration.
     * Eligible note values: whole, half, quarter, eighth (≥ 1/8).
     * Never replaces position 0 so the melody does not start on a rest.
     * Sets the pitch at that position to null (rendered as silence).
     *
     * @param  string[]       $notes       Melody pitches (same length as $noteValues).
     * @param  string[]       $noteValues  Rhythm durations.
     * @return array{0: array, 1: array}  [$notes, $noteValues] with one rest injected.
     */
    protected function injectOneRestIntoMelody(array $notes, array $noteValues): array
    {
        $noteToRest = [
            'whole'   => 'whole_rest',
            'half'    => 'half_rest',
            'quarter' => 'quarter_rest',
            'eighth'  => 'eighth_rest',
        ];

        $eligible = [];
        for ($i = 1; $i < count($noteValues); $i++) {
            if (isset($noteToRest[$noteValues[$i]])) {
                $eligible[] = $i;
            }
        }

        if (! empty($eligible)) {
            $pos = $eligible[array_rand($eligible)];
            $noteValues[$pos] = $noteToRest[$noteValues[$pos]];
            $notes[$pos] = null;
        }

        return [$notes, $noteValues];
    }
}
