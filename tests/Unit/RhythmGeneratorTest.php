<?php

namespace Tests\Unit;

use App\Models\LearningPathExercise;
use App\Services\DictationRhythmService;
use App\Services\LearningPathQuestionGenerator;
use App\Services\MusicTheoryService;
use App\Services\RhythmDistractorService;
use App\Services\RhythmGroupingService;
use App\Services\TonalMelodyGenerator;
use PHPUnit\Framework\TestCase;

/**
 * Rules for Learning Path / Exercise Setup rhythm questions: every seeded LP
 * rhythm lesson must fill its full premium question count (15) with distinct
 * patterns; every pattern fills its meter exactly (bars × measure twelfths);
 * tokens never leave the lesson vocabulary; bars always open on a sounded
 * note; the notationally wrong within-beat [eighth_rest, eighth_rest] pair is
 * never generated (an eighth rest always pairs with an eighth note — the
 * off-beat figure); exclude_cells keeps out-of-scope figures (e.g. the
 * syncopated eighth-quarter-eighth cell) away from focused lessons; and every
 * question carries 3 duration-preserving distractors. Mirrors the curriculum
 * seeded by LearningPathExerciseSeeder (rhythm lessons 1–16).
 */
class RhythmGeneratorTest extends TestCase
{
    private LearningPathQuestionGenerator $generator;

    private RhythmGroupingService $grouping;

    private RhythmDistractorService $distractor;

    protected function setUp(): void
    {
        parent::setUp();
        $this->grouping = new RhythmGroupingService;
        $this->distractor = new RhythmDistractorService($this->grouping);
        $this->generator = new LearningPathQuestionGenerator(
            new MusicTheoryService,
            new TonalMelodyGenerator,
            $this->distractor,
            new DictationRhythmService,
        );
    }

    private function generate(array $config, int $count = 15)
    {
        $exercise = new LearningPathExercise(['config_json' => array_merge([
            'practice_type' => 'rhythm-practice',
        ], $config)]);

        return $this->generator->generate($exercise, $count);
    }

    /** Seeded LP rhythm lesson configs, keyed by a readable label. */
    private function lessonConfigs(): array
    {
        $noSync = ['eighth,quarter,eighth'];

        return [
            'L1 pulse' => ['time_signatures' => ['2/4', '3/4', '4/4'], 'allowed_note_values' => ['quarter', 'quarter_rest'], 'rhythm_difficulty' => 'easy', 'bars' => 2],
            'L2 half notes' => ['time_signatures' => ['2/4', '3/4', '4/4'], 'allowed_note_values' => ['quarter', 'half'], 'rhythm_difficulty' => 'easy', 'bars' => 2],
            'L3 whole & silences' => ['time_signatures' => ['4/4'], 'allowed_note_values' => ['whole', 'half', 'quarter', 'half_rest'], 'rhythm_difficulty' => 'easy', 'bars' => 2],
            'L4 waltz' => ['time_signatures' => ['3/4'], 'allowed_note_values' => ['quarter', 'half', 'dotted-half', 'quarter_rest'], 'rhythm_difficulty' => 'easy', 'bars' => 2],
            'L5 eighths' => ['time_signatures' => ['2/4', '4/4'], 'allowed_note_values' => ['quarter', 'eighth'], 'rhythm_difficulty' => 'easy', 'bars' => 1, 'exclude_cells' => $noSync],
            'L6 eighth rests' => ['time_signatures' => ['4/4'], 'allowed_note_values' => ['quarter', 'eighth', 'eighth_rest'], 'rhythm_difficulty' => 'medium', 'bars' => 1, 'exclude_cells' => $noSync],
            'L7 dotted quarter' => ['time_signatures' => ['3/4', '4/4'], 'allowed_note_values' => ['quarter', 'eighth', 'half', 'dotted-quarter'], 'rhythm_difficulty' => 'medium', 'bars' => 1, 'exclude_cells' => $noSync],
            'L8 sixteenths' => ['time_signatures' => ['2/4', '4/4'], 'allowed_note_values' => ['quarter', 'eighth', 'sixteenth'], 'rhythm_difficulty' => 'medium', 'bars' => 1,
                'exclude_cells' => array_merge($noSync, ['eighth,sixteenth,sixteenth', 'sixteenth,sixteenth,eighth', 'sixteenth,eighth,sixteenth'])],
            'L9 eighth+sixteenth groups' => ['time_signatures' => ['4/4'], 'allowed_note_values' => ['quarter', 'eighth', 'sixteenth'], 'rhythm_difficulty' => 'medium', 'bars' => 1,
                'exclude_cells' => array_merge($noSync, ['sixteenth,sixteenth,sixteenth,sixteenth', 'sixteenth,eighth,sixteenth'])],
            'L10 snap' => ['time_signatures' => ['2/4', '4/4'], 'allowed_note_values' => ['quarter', 'eighth', 'dotted-eighth', 'sixteenth'], 'rhythm_difficulty' => 'medium', 'bars' => 1,
                'exclude_cells' => array_merge($noSync, ['eighth,sixteenth,sixteenth', 'sixteenth,sixteenth,eighth', 'sixteenth,eighth,sixteenth', 'sixteenth,sixteenth,sixteenth,sixteenth'])],
            'L11 syncopation' => ['time_signatures' => ['4/4'], 'allowed_note_values' => ['quarter', 'eighth', 'half', 'eighth_rest'], 'rhythm_difficulty' => 'hard', 'bars' => 1],
            'L12 6/8' => ['time_signatures' => ['6/8'], 'allowed_note_values' => ['dotted-half', 'dotted-quarter', 'quarter', 'eighth'], 'rhythm_difficulty' => 'hard', 'bars' => 1],
            'L13 compound subdivision' => ['time_signatures' => ['6/8', '9/8'], 'allowed_note_values' => ['dotted-quarter', 'quarter', 'eighth', 'dotted-eighth', 'sixteenth'], 'rhythm_difficulty' => 'hard', 'bars' => 1],
            'L14 triplets' => ['time_signatures' => ['4/4'], 'allowed_note_values' => ['quarter', 'eighth', 'half', 'triplet-eighth'], 'rhythm_difficulty' => 'hard', 'bars' => 1, 'exclude_cells' => $noSync],
            'L15 alla breve' => ['time_signatures' => ['2/2'], 'allowed_note_values' => ['whole', 'half', 'dotted-half', 'quarter', 'eighth'], 'rhythm_difficulty' => 'hard', 'bars' => 1, 'exclude_cells' => $noSync],
            'L16 master' => ['time_signatures' => ['4/4', '6/8'], 'allowed_note_values' => ['whole', 'half', 'dotted-half', 'quarter', 'dotted-quarter', 'eighth', 'dotted-eighth', 'sixteenth', 'quarter_rest', 'eighth_rest'], 'rhythm_difficulty' => 'hard', 'bars' => 2],
        ];
    }

    private function patternTwelfths(array $tokens): int
    {
        return (int) array_sum(array_map([$this->grouping, 'noteTwelfths'], $tokens));
    }

    private function measureTwelfths(string $sig): int
    {
        [$num, $den] = array_map('intval', explode('/', $sig));

        return $this->grouping->measureTwelfths($num, $den);
    }

    // ── Full-curriculum invariants ───────────────────────────────────────────

    public function test_every_lesson_fills_the_premium_question_count_with_distinct_patterns(): void
    {
        foreach ($this->lessonConfigs() as $label => $config) {
            $questions = $this->generate($config);

            $this->assertCount(15, $questions, "$label: expected 15 questions");

            $keys = $questions->map(fn ($q) => $q->time_signature.'|'.implode(',', $q->note_values));
            $this->assertCount(15, $keys->unique(), "$label: duplicate patterns in question set");
        }
    }

    public function test_every_pattern_fills_its_meter_exactly(): void
    {
        foreach ($this->lessonConfigs() as $label => $config) {
            foreach ($this->generate($config) as $q) {
                $expected = $this->measureTwelfths($q->time_signature) * $config['bars'];
                $this->assertSame(
                    $expected,
                    $this->patternTwelfths($q->note_values),
                    "$label: pattern [".implode(',', $q->note_values)."] does not fill {$config['bars']} bar(s) of {$q->time_signature}"
                );
                $this->assertSame($config['bars'], $q->bars, "$label: bars attribute mismatch");
                $this->assertContains($q->time_signature, $config['time_signatures'], "$label: unexpected time signature");
            }
        }
    }

    public function test_tokens_never_leave_the_lesson_vocabulary(): void
    {
        foreach ($this->lessonConfigs() as $label => $config) {
            foreach ($this->generate($config) as $q) {
                $outside = array_diff($q->note_values, $config['allowed_note_values']);
                $this->assertSame([], array_values($outside),
                    "$label: tokens outside lesson vocabulary: ".implode(',', $outside));

                // Answer options (distractors) must also stay inside the taught
                // vocabulary — a lesson never offers a figure it has not introduced
                // (e.g. sixteenths as a distractor in an eighth-note lesson).
                foreach ($q->other_options as $option) {
                    $optOutside = array_diff($option, $config['allowed_note_values']);
                    $this->assertSame([], array_values($optOutside),
                        "$label: distractor tokens outside lesson vocabulary: ".implode(',', $optOutside)
                        .' in ['.implode(',', $option).']');
                }
            }
        }
    }

    public function test_every_bar_opens_on_a_sounded_note(): void
    {
        foreach ($this->lessonConfigs() as $label => $config) {
            foreach ($this->generate($config) as $q) {
                $barLen = $this->measureTwelfths($q->time_signature);
                $elapsed = 0;
                foreach ($q->note_values as $token) {
                    if ($elapsed % $barLen === 0) {
                        $this->assertStringNotContainsString('_rest', $token,
                            "$label: bar opens on a rest in [".implode(',', $q->note_values).']');
                    }
                    $elapsed += $this->grouping->noteTwelfths($token);
                }
            }
        }
    }

    public function test_no_beat_ever_contains_two_eighth_rests(): void
    {
        // Two eighth rests inside one beat are notated as a quarter rest — the
        // generator must always pair an eighth rest with an eighth note.
        foreach ($this->lessonConfigs() as $label => $config) {
            foreach ($this->generate($config) as $q) {
                [, $den] = array_map('intval', explode('/', $q->time_signature));
                $beatT = $this->grouping->visualGroupTwelfths($den);
                foreach ($this->distractor->accumulateGroups($q->note_values, $beatT) as $group) {
                    $this->assertNotSame(['eighth_rest', 'eighth_rest'], $group['tokens'],
                        "$label: within-beat double eighth rest in [".implode(',', $q->note_values).']');
                }
            }
        }
    }

    public function test_excluded_cells_never_appear_as_beat_groups(): void
    {
        foreach ($this->lessonConfigs() as $label => $config) {
            if (empty($config['exclude_cells'])) {
                continue;
            }
            foreach ($this->generate($config) as $q) {
                [, $den] = array_map('intval', explode('/', $q->time_signature));
                $beatT = $this->grouping->visualGroupTwelfths($den);
                foreach ($this->distractor->accumulateGroups($q->note_values, $beatT) as $group) {
                    $this->assertNotContains(implode(',', $group['tokens']), $config['exclude_cells'],
                        "$label: excluded cell [".implode(',', $group['tokens']).'] appeared in ['.implode(',', $q->note_values).']');
                }
            }
        }
    }

    public function test_rests_never_exceed_half_of_the_pattern(): void
    {
        foreach ($this->lessonConfigs() as $label => $config) {
            foreach ($this->generate($config) as $q) {
                $restT = $this->patternTwelfths(array_values(array_filter(
                    $q->note_values, fn ($t) => str_contains($t, '_rest')
                )));
                $this->assertLessThanOrEqual(
                    intdiv($this->patternTwelfths($q->note_values), 2),
                    $restT,
                    "$label: rests exceed half the pattern in [".implode(',', $q->note_values).']'
                );
            }
        }
    }

    public function test_every_question_carries_three_duration_preserving_distractors(): void
    {
        foreach ($this->lessonConfigs() as $label => $config) {
            foreach ($this->generate($config) as $q) {
                $options = $q->other_options;
                $this->assertCount(3, $options, "$label: expected 3 distractors");

                $correctT = $this->patternTwelfths($q->note_values);
                $seen = [implode(',', $q->note_values)];
                foreach ($options as $opt) {
                    $key = implode(',', $opt);
                    $this->assertNotContains($key, $seen, "$label: duplicate/correct-equal distractor");
                    $seen[] = $key;
                    $this->assertSame($correctT, $this->patternTwelfths($opt),
                        "$label: distractor [".$key.'] duration differs from the correct pattern');
                }
            }
        }
    }

    // ── Generator-level rules ────────────────────────────────────────────────

    public function test_eighth_rest_lessons_actually_contain_rests(): void
    {
        // 20 distinct patterns cannot all come from the 16 rest-free {quarter,
        // eighth-pair} sequences of one 4/4 bar, so at least one must carry a rest.
        $questions = $this->generate([
            'time_signatures' => ['4/4'],
            'allowed_note_values' => ['quarter', 'eighth', 'eighth_rest'],
            'rhythm_difficulty' => 'medium',
            'bars' => 1,
            'exclude_cells' => ['eighth,quarter,eighth'],
        ], 20);

        $this->assertCount(20, $questions);
        $withRest = $questions->filter(
            fn ($q) => in_array('eighth_rest', $q->note_values, true)
        );
        $this->assertGreaterThan(0, $withRest->count(), 'no question carries an eighth rest');
    }

    public function test_eighth_rests_pair_with_an_eighth_note_inside_the_beat(): void
    {
        $questions = $this->generate([
            'time_signatures' => ['4/4'],
            'allowed_note_values' => ['eighth', 'eighth_rest'],
            'rhythm_difficulty' => 'medium',
            'bars' => 1,
        ], 15);

        $mixedBeats = 0;
        foreach ($questions as $q) {
            foreach ($this->distractor->accumulateGroups($q->note_values, 12) as $group) {
                $this->assertNotSame(['eighth_rest', 'eighth_rest'], $group['tokens']);
                if (in_array($group['tokens'], [['eighth_rest', 'eighth'], ['eighth', 'eighth_rest']], true)) {
                    $mixedBeats++;
                }
            }
        }
        // Only one fully rest-free pattern exists (all eighth pairs), so mixed
        // rest+note beats must appear across 15 distinct questions.
        $this->assertGreaterThan(0, $mixedBeats, 'no eighth_rest+eighth beat generated');
    }

    public function test_exclude_cells_config_removes_the_cell_from_generation(): void
    {
        $questions = $this->generate([
            'time_signatures' => ['4/4'],
            'allowed_note_values' => ['quarter', 'eighth'],
            'rhythm_difficulty' => 'medium',
            'bars' => 1,
            'exclude_cells' => ['eighth,eighth', 'eighth,quarter,eighth'],
        ], 5);

        foreach ($questions as $q) {
            $this->assertNotContains('eighth', $q->note_values,
                'excluded eighth-pair cell still generated eighth notes');
        }
    }

    public function test_syncopation_pool_contains_the_syncopated_cell(): void
    {
        // Without exclude_cells the hard-pool eighth-quarter-eighth cell is in
        // play; across 30 distinct patterns the rest-free {quarter, eighth-pair,
        // half}-only space (29 patterns) cannot supply them all.
        $questions = $this->generate([
            'time_signatures' => ['4/4'],
            'allowed_note_values' => ['quarter', 'eighth', 'half', 'eighth_rest'],
            'rhythm_difficulty' => 'hard',
            'bars' => 1,
        ], 30);

        $this->assertCount(30, $questions);
        $flavoured = $questions->filter(function ($q) {
            if (in_array('eighth_rest', $q->note_values, true)) {
                return true;
            }
            foreach ($this->distractor->accumulateGroups($q->note_values, 12) as $group) {
                if ($group['tokens'] === ['eighth', 'quarter', 'eighth']) {
                    return true;
                }
            }

            return false;
        });
        $this->assertGreaterThan(0, $flavoured->count(),
            'syncopation lesson produced neither the eighth-quarter-eighth figure nor an off-beat rest');
    }

    public function test_session_serialization_round_trip_preserves_patterns_and_options(): void
    {
        $config = $this->lessonConfigs()['L16 master'];
        $questions = $this->generate($config)->values()->map(function ($q, $i) {
            $q->id = $i + 1;

            return $q;
        });

        $serialized = $this->generator->serializeForSession($questions);
        $rebuilt = $this->generator->reconstructFromSession($serialized, 'rhythm-practice');

        $this->assertCount($questions->count(), $rebuilt);
        foreach ($questions as $i => $q) {
            $this->assertSame($q->note_values, $rebuilt[$i]->note_values);
            $this->assertSame($q->other_options, $rebuilt[$i]->other_options);
            $this->assertSame($q->time_signature, $rebuilt[$i]->time_signature);
        }
    }
}
