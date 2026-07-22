<?php

namespace Tests\Unit;

use App\Livewire\PracticeIntervalComparison;
use App\Models\LearningPathExercise;
use App\Services\DictationRhythmService;
use App\Services\LearningPathQuestionGenerator;
use App\Services\MusicTheoryService;
use App\Services\RhythmDistractorService;
use App\Services\RhythmGroupingService;
use App\Services\TonalMelodyGenerator;
use PHPUnit\Framework\TestCase;

/**
 * Rules for Learning Path / Exercise Setup interval-comparison questions:
 * every pair uses playable canonical spellings, the two intervals always
 * differ in size, the stored target names the genuinely larger interval,
 * clef-driven octave placement stays inside CLEF_RANGES, and both A/B
 * presentation orders occur. Mirrors the curriculum seeded by
 * LearningPathExerciseSeeder (interval-comparison lessons 1–15).
 */
class IntervalComparisonGeneratorTest extends TestCase
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
            'practice_type' => 'interval-comparison-practice',
        ], $config)]);

        return $this->generator->generate($exercise, $count);
    }

    /** Seeded lesson configs, keyed by a readable label. */
    private function lessonConfigs(): array
    {
        $p = PracticeIntervalComparison::POOL_TO_PAIR;

        return [
            'L1 2nd vs 3rd' => ['allowed_interval_pairs' => [[$p['M2'], $p['M3']], [$p['m2'], $p['m3']]], 'clef' => 'treble'],
            'L3 P4 vs P5' => ['allowed_interval_pairs' => [[$p['P4'], $p['P5']]], 'clef' => 'treble'],
            'L5 m3 vs M3' => ['allowed_interval_pairs' => [[$p['m3'], $p['M3']]], 'clef' => 'treble'],
            'L7 m6 vs M6' => ['allowed_interval_pairs' => [[$p['m6'], $p['M6']]], 'clef' => 'treble'],
            'L8 half vs whole step' => ['allowed_interval_pairs' => [[$p['m2'], $p['M2']]], 'clef' => 'treble'],
            'L9 tritone zone' => ['allowed_interval_pairs' => [[$p['P4'], $p['TT']], [$p['TT'], $p['P5']]], 'clef' => 'treble'],
            'L11 sevenths zone' => ['allowed_interval_pairs' => [[$p['m7'], $p['M7']], [$p['M6'], $p['M7']], [$p['M6'], $p['m7']]], 'clef' => 'treble'],
            'L12 semitone sweep' => ['allowed_interval_pairs' => [[$p['m2'], $p['M2']], [$p['m3'], $p['M3']], [$p['P4'], $p['TT']], [$p['TT'], $p['P5']], [$p['m6'], $p['M6']], [$p['m7'], $p['M7']]], 'clef' => 'treble'],
            'L14 speed drill' => ['allowed_interval_pairs' => PracticeIntervalComparison::buildPairsFromPool(['m2', 'm3', 'P4', 'P5', 'M6', 'M7']), 'clef' => 'treble'],
            'L15 bass master' => ['allowed_interval_pairs' => PracticeIntervalComparison::buildPairsFromPool(array_keys($p)), 'clef' => 'bass'],
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

    public function test_all_notes_are_playable_canonical_spellings(): void
    {
        foreach ($this->lessonConfigs() as $label => $config) {
            foreach ($this->generate($config) as $q) {
                foreach ([$q->interval_a, $q->interval_b] as $pair) {
                    foreach (explode(',', $pair) as $note) {
                        $this->assertArrayHasKey(
                            trim($note),
                            MusicTheoryService::NOTE_SEMITONES,
                            "$label: note '$note' in pair '$pair' is not a playable same-octave spelling"
                        );
                    }
                }
            }
        }
    }

    public function test_target_always_names_the_larger_interval(): void
    {
        foreach ($this->lessonConfigs() as $label => $config) {
            foreach ($this->generate($config) as $q) {
                $expected = $this->music->largerIntervalPair($q->interval_a, $q->interval_b);
                $this->assertNotNull(
                    $expected,
                    "$label: unanswerable comparison {$q->interval_a} vs {$q->interval_b} (equal or invalid)"
                );
                $this->assertSame(
                    $expected,
                    $q->target,
                    "$label: target {$q->target} wrong for {$q->interval_a} vs {$q->interval_b}"
                );
            }
        }
    }

    public function test_all_pitches_stay_inside_the_clef_range(): void
    {
        foreach ($this->lessonConfigs() as $label => $config) {
            [$min, $max] = $this->music->clefRangeMidi($config['clef']);
            foreach ($this->generate($config) as $q) {
                $this->assertSame($config['clef'], $q->clef, "$label: question lost its clef");
                foreach ([$q->interval_a, $q->interval_b] as $pair) {
                    foreach (explode(',', $pair) as $note) {
                        $midi = $this->music->midiNumber(trim($note), (int) $q->octave);
                        $this->assertNotNull($midi, "$label: unplayable pitch $note{$q->octave}");
                        $this->assertGreaterThanOrEqual($min, $midi, "$label: $note{$q->octave} below {$config['clef']} range");
                        $this->assertLessThanOrEqual($max, $midi, "$label: $note{$q->octave} above {$config['clef']} range");
                    }
                }
            }
        }
    }

    public function test_both_presentation_orders_occur(): void
    {
        $targets = $this->generate($this->lessonConfigs()['L8 half vs whole step'], 40)
            ->pluck('target')->unique()->sort()->values()->all();
        $this->assertSame(['a', 'b'], $targets, 'reversed variants missing: larger interval always on the same side');
    }

    public function test_pairs_are_transposed_across_multiple_roots(): void
    {
        $roots = $this->generate($this->lessonConfigs()['L1 2nd vs 3rd'], 40)
            ->map(fn ($q) => explode(',', $q->interval_a)[0])
            ->unique();
        $this->assertGreaterThan(2, $roots->count(), 'questions keep repeating the same root');
    }

    public function test_octave_suffixed_legacy_pairs_are_skipped_not_crashed(): void
    {
        // Old seed data carried pairs like ['C,B','C,C5']; the same-octave
        // engine cannot represent them and must skip them cleanly.
        $questions = $this->generate([
            'allowed_interval_pairs' => [['C,B', 'C,C5'], ['C,D', 'C,E']],
            'clef' => 'treble',
        ], 10);

        $this->assertCount(10, $questions);
        foreach ($questions as $q) {
            $this->assertNotNull($this->music->intervalPairSemitones($q->interval_a));
            $this->assertNotNull($this->music->intervalPairSemitones($q->interval_b));
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
        $rebuilt = $this->generator->reconstructFromSession($serialized, 'interval-comparison-practice');

        foreach ($rebuilt as $i => $q) {
            $this->assertSame('bass', $q->clef);
            $this->assertContains($q->target, ['a', 'b']);
            $this->assertSame(
                $q->target,
                $this->generator->getAnswerFromSessionQuestion($serialized[$i], 'interval-comparison-practice')
            );
        }
    }

    public function test_bass_clef_places_pairs_in_the_low_register(): void
    {
        foreach ($this->generate($this->lessonConfigs()['L15 bass master'], 10) as $q) {
            $this->assertSame('3', (string) $q->octave, 'bass-clef comparison should sit in octave 3 (C3–B3)');
        }
    }
}
