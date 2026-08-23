<?php

namespace Tests\Unit;

use App\Models\LearningPathExercise;
use App\Models\ScalePractice;
use App\Services\DictationRhythmService;
use App\Services\LearningPathQuestionGenerator;
use App\Services\MusicTheoryService;
use App\Services\RhythmDistractorService;
use App\Services\RhythmGroupingService;
use App\Services\TonalMelodyGenerator;
use PHPUnit\Framework\TestCase;

/**
 * Rules for Learning Path / Exercise Setup scale questions: every scale type
 * (target and options) is a canonical ScalePractice::scaleIntervals() key —
 * legacy lowercase slugs are normalized instead of silently falling back to
 * Major intervals — clef-driven octave placement keeps the whole scale inside
 * CLEF_RANGES, direction 'both' mixes ascending/descending (melodic minor
 * stays ascending: its classical descending form equals natural minor), and
 * no answer set ever pairs two scales with identical interval content
 * (Aeolian = Natural Minor, Ionian = Major). Mirrors the curriculum seeded by
 * LearningPathExerciseSeeder (scales-modes lessons 1–15).
 */
class ScaleGeneratorTest extends TestCase
{
    private LearningPathQuestionGenerator $generator;

    private MusicTheoryService $music;

    protected function setUp(): void
    {
        parent::setUp();
        $this->music = new MusicTheoryService;
        $this->generator = new LearningPathQuestionGenerator(
            $this->music,
            new TonalMelodyGenerator,
            new RhythmDistractorService(new RhythmGroupingService),
            new DictationRhythmService,
        );
    }

    private function generate(array $config, int $count = 30)
    {
        $exercise = new LearningPathExercise(['config_json' => array_merge([
            'practice_type' => 'scale-practice',
        ], $config)]);

        return $this->generator->generate($exercise, $count);
    }

    /** Seeded lesson configs, keyed by a readable label. */
    private function lessonConfigs(): array
    {
        $allModes = ['Ionian', 'Dorian', 'Phrygian', 'Lydian', 'Mixolydian', 'Aeolian', 'Locrian'];
        $masterPool = ['Major', 'Natural Minor', 'Harmonic Minor', 'Melodic Minor',
            'Dorian', 'Phrygian', 'Lydian', 'Mixolydian', 'Locrian',
            'Major Pentatonic', 'Minor Pentatonic', 'Blues Scale', 'Chromatic Scale', 'Whole Tone Scale'];

        return [
            'L1 major foundation' => [
                'allowed_scale_types' => ['Major', 'Natural Minor'],
                'allowed_root_notes' => ['C', 'G', 'F', 'D'],
                'direction' => 'ascending', 'clef' => 'treble',
                'distractor_pool' => ['Major', 'Natural Minor', 'Harmonic Minor', 'Major Pentatonic'],
            ],
            'L4 melodic minor' => [
                'allowed_scale_types' => ['Melodic Minor', 'Harmonic Minor', 'Natural Minor'],
                'allowed_root_notes' => ['A', 'D', 'G'],
                'direction' => 'ascending', 'clef' => 'treble',
                'distractor_pool' => ['Melodic Minor', 'Harmonic Minor', 'Natural Minor', 'Major'],
            ],
            'L10 aeolian & locrian' => [
                'allowed_scale_types' => ['Aeolian', 'Locrian'],
                'allowed_root_notes' => ['B', 'E', 'A'],
                'direction' => 'ascending', 'clef' => 'treble',
                'distractor_pool' => ['Aeolian', 'Locrian', 'Phrygian', 'Dorian'],
            ],
            'L11 all church modes' => [
                'allowed_scale_types' => $allModes,
                'allowed_root_notes' => ['C', 'D', 'E', 'F', 'G', 'A'],
                'direction' => 'both', 'clef' => 'treble',
                'distractor_pool' => $allModes,
            ],
            'L14 symmetric scales' => [
                'allowed_scale_types' => ['Chromatic Scale', 'Whole Tone Scale'],
                'allowed_root_notes' => ['C', 'D', 'E', 'F', 'G'],
                'direction' => 'both', 'clef' => 'treble',
                'distractor_pool' => ['Chromatic Scale', 'Whole Tone Scale', 'Blues Scale', 'Melodic Minor'],
            ],
            'L15 bass master' => [
                'allowed_scale_types' => $masterPool,
                'allowed_root_notes' => ['C', 'D', 'E', 'F', 'G', 'A', 'B'],
                'direction' => 'both', 'clef' => 'bass',
                'distractor_pool' => $masterPool,
            ],
        ];
    }

    public function test_every_lesson_config_generates_the_full_question_count(): void
    {
        foreach ($this->lessonConfigs() as $label => $config) {
            foreach ([5, 10, 15] as $count) {
                $questions = $this->generate($config, $count);
                $this->assertCount($count, $questions, "$label produced too few questions for count $count");
            }
        }
    }

    public function test_all_scale_types_and_options_are_canonical_interval_keys(): void
    {
        $canonical = ScalePractice::scaleIntervals();
        foreach ($this->lessonConfigs() as $label => $config) {
            foreach ($this->generate($config) as $q) {
                foreach (array_merge([$q->scale_type], $q->other_options) as $type) {
                    $this->assertArrayHasKey(
                        $type,
                        $canonical,
                        "$label: '$type' is not a canonical scaleIntervals() key — it would play as Major"
                    );
                }
            }
        }
    }

    public function test_legacy_lowercase_slugs_are_normalized_to_canonical_names(): void
    {
        // Pre-overhaul seed configs carried slugs; they must map to real
        // scaleIntervals() keys instead of silently playing Major.
        $questions = $this->generate([
            'allowed_scale_types' => ['natural-minor', 'pentatonic-major', 'blues', 'whole-tone'],
            'allowed_root_notes' => ['C', 'G'],
            'direction' => 'ascending', 'clef' => 'treble',
            'distractor_pool' => ['major', 'harmonic-minor', 'dorian', 'pentatonic-minor'],
        ], 20);

        $types = $questions->pluck('scale_type')->unique()->sort()->values()->all();
        $this->assertSame(
            ['Blues Scale', 'Major Pentatonic', 'Natural Minor', 'Whole Tone Scale'],
            $types
        );
        foreach ($questions as $q) {
            foreach ($q->other_options as $opt) {
                $this->assertContains($opt, ['Major', 'Harmonic Minor', 'Dorian', 'Minor Pentatonic']);
            }
        }
    }

    public function test_every_scale_degree_spells_and_stays_inside_the_clef_range(): void
    {
        foreach ($this->lessonConfigs() as $label => $config) {
            [$min, $max] = $this->music->clefRangeMidi($config['clef']);
            foreach ($this->generate($config) as $q) {
                $this->assertSame($config['clef'], $q->clef, "$label: question lost its clef");

                $expected = count(ScalePractice::scaleIntervals()[$q->scale_type]);
                $notes = $q->note_array;
                $this->assertCount(
                    $expected,
                    $notes,
                    "$label: {$q->root_note} {$q->scale_type} dropped a degree while spelling"
                );

                foreach ($notes as $spelled) {
                    // Double accidentals are part of a correct spelling — Cdim7 reaches
                    // Bbb, B augmented reaches F## — so the pitch is parsed, not
                    // restricted, and midiNumber below is what proves it playable.
                    $this->assertSame(1, preg_match('/^([A-G](?:#{1,2}|b{1,2})?)(\d)$/', $spelled, $m), "$label: unparsable pitch '$spelled'");
                    $midi = $this->music->midiNumber($m[1], (int) $m[2]);
                    $this->assertNotNull($midi, "$label: unplayable pitch $spelled");
                    $this->assertGreaterThanOrEqual($min, $midi, "$label: $spelled below {$config['clef']} range");
                    $this->assertLessThanOrEqual($max, $midi, "$label: $spelled above {$config['clef']} range");
                }
            }
        }
    }

    public function test_direction_both_mixes_ascending_and_descending(): void
    {
        $directions = $this->generate($this->lessonConfigs()['L11 all church modes'], 40)
            ->pluck('direction')->unique()->sort()->values()->all();
        $this->assertSame(['ascending', 'descending'], $directions);
    }

    public function test_melodic_minor_never_descends_in_mixed_mode(): void
    {
        // The classical melodic minor descends as natural minor, so a
        // descending jazz-form question would be theoretically ambiguous.
        $questions = $this->generate([
            'allowed_scale_types' => ['Melodic Minor', 'Harmonic Minor'],
            'allowed_root_notes' => ['A', 'D', 'E', 'G', 'C'],
            'direction' => 'both', 'clef' => 'treble',
            'distractor_pool' => ['Melodic Minor', 'Harmonic Minor', 'Natural Minor', 'Major'],
        ], 40);

        foreach ($questions as $q) {
            if ($q->scale_type === 'Melodic Minor') {
                $this->assertSame('ascending', $q->direction, 'descending melodic minor question generated');
            }
        }
        $this->assertContains('descending', $questions->where('scale_type', 'Harmonic Minor')->pluck('direction')->all());
    }

    public function test_answer_options_are_distinct_in_sound_and_exclude_the_target(): void
    {
        $intervals = ScalePractice::scaleIntervals();
        foreach ($this->lessonConfigs() as $label => $config) {
            foreach ($this->generate($config) as $q) {
                $this->assertCount(3, $q->other_options, "$label: expected exactly 3 distractors");
                $this->assertNotContains($q->scale_type, $q->other_options, "$label: correct answer duplicated in options");

                // No two options may share interval content (e.g. Aeolian vs
                // Natural Minor) — the student could not tell them apart.
                $all = array_merge([$q->scale_type], $q->other_options);
                $sounds = array_map(fn ($t) => implode(',', $intervals[$t]), $all);
                $this->assertSame(
                    count($sounds),
                    count(array_unique($sounds)),
                    "$label: acoustically identical options in [".implode(', ', $all).']'
                );
            }
        }
    }

    public function test_questions_survive_session_round_trip(): void
    {
        $questions = $this->generate($this->lessonConfigs()['L15 bass master'], 10)
            ->values()->map(function ($q, $i) {
                $q->id = $i + 1;

                return $q;
            });
        $serialized = $this->generator->serializeForSession($questions);
        $rebuilt = $this->generator->reconstructFromSession($serialized, 'scale-practice');

        foreach ($rebuilt as $i => $q) {
            $this->assertSame('bass', $q->clef);
            $this->assertSame(
                $q->scale_type,
                $this->generator->getAnswerFromSessionQuestion($serialized[$i], 'scale-practice')
            );
            $this->assertNotEmpty($q->note_array);
        }
    }
}
