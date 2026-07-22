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
 * Rules for Learning Path / Exercise Setup interval-construction questions:
 * playable single-accidental answers, options that always contain the answer
 * with one spelling per pitch class, clef-range pitch placement, and correct
 * direction semantics. Mirrors the curriculum seeded by
 * LearningPathExerciseSeeder (interval-construction lessons 1–15).
 */
class IntervalConstructionGeneratorTest extends TestCase
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
            'practice_type' => 'interval-construction-practice',
        ], $config)]);

        return $this->generator->generate($exercise, $count);
    }

    /** Seeded lesson configs, keyed by a readable label. */
    private function lessonConfigs(): array
    {
        $naturals = ['C', 'D', 'E', 'F', 'G', 'A', 'B'];
        $allTwelve = ['Minor 2nd', 'Major 2nd', 'Minor 3rd', 'Major 3rd', 'Perfect 4th', 'Tritone', 'Perfect 5th', 'Minor 6th', 'Major 6th', 'Minor 7th', 'Major 7th', 'Perfect Octave'];
        $near = ['distractor_mode' => 'near', 'distractor_count' => 3];

        return [
            'L1 major 2nds' => ['allowed_intervals' => ['Major 2nd'], 'allowed_root_notes' => $naturals, 'clef' => 'treble', 'direction' => 'ascending'],
            'L2 minor 2nds' => ['allowed_intervals' => ['Minor 2nd'], 'allowed_root_notes' => $naturals, 'clef' => 'treble', 'direction' => 'ascending'],
            'L8 tritone' => ['allowed_intervals' => ['Tritone'], 'allowed_root_notes' => $naturals, 'clef' => 'treble', 'direction' => 'ascending'],
            'L9 descending steps' => ['allowed_intervals' => ['Minor 2nd', 'Major 2nd', 'Minor 3rd', 'Major 3rd'], 'allowed_root_notes' => $naturals, 'clef' => 'treble', 'direction' => 'descending'],
            'L10 descending wide' => ['allowed_intervals' => ['Perfect 4th', 'Perfect 5th', 'Minor 6th', 'Major 6th', 'Minor 7th', 'Major 7th'], 'allowed_root_notes' => $naturals, 'clef' => 'treble', 'direction' => 'descending'],
            'L11 sharp roots' => array_merge(['allowed_intervals' => ['Major 2nd', 'Minor 3rd', 'Perfect 4th', 'Perfect 5th', 'Minor 7th'], 'allowed_root_notes' => ['C#', 'F#', 'G#'], 'clef' => 'treble', 'direction' => 'ascending'], $near),
            'L12 flat roots' => array_merge(['allowed_intervals' => ['Major 2nd', 'Major 3rd', 'Perfect 4th', 'Perfect 5th', 'Major 6th', 'Minor 7th'], 'allowed_root_notes' => ['Bb', 'Eb', 'Ab'], 'clef' => 'treble', 'direction' => 'ascending'], $near),
            'L13 full twelve' => array_merge(['allowed_intervals' => $allTwelve, 'allowed_root_notes' => $naturals, 'clef' => 'treble', 'direction' => 'ascending'], $near),
            'L14 mixed direction' => array_merge(['allowed_intervals' => $allTwelve, 'allowed_root_notes' => $naturals, 'clef' => 'treble', 'direction' => 'mixed'], $near),
            'L15 bass master' => array_merge(['allowed_intervals' => $allTwelve, 'allowed_root_notes' => $naturals, 'clef' => 'bass', 'direction' => 'mixed'], $near),
        ];
    }

    public function test_every_lesson_config_generates_questions(): void
    {
        foreach ($this->lessonConfigs() as $label => $config) {
            $questions = $this->generate($config, 15);
            $this->assertCount(15, $questions, "$label produced too few questions");
        }
    }

    public function test_answers_are_playable_single_accidental_spellings(): void
    {
        foreach ($this->lessonConfigs() as $label => $config) {
            foreach ($this->generate($config) as $q) {
                $this->assertArrayHasKey(
                    $q->note2,
                    MusicTheoryService::NOTE_SEMITONES,
                    "$label: answer {$q->note2} (root {$q->note1}, {$q->interval}) is not a playable spelling"
                );
            }
        }
    }

    public function test_answer_matches_the_stated_interval_and_direction(): void
    {
        foreach ($this->lessonConfigs() as $label => $config) {
            foreach ($this->generate($config) as $q) {
                $expected = MusicTheoryService::INTERVAL_SEMITONES[$q->interval];
                $actual = $this->music->semitonesBetween(
                    $q->note1, (int) $q->octave, $q->note2, (int) $q->note2_octave
                );
                $signed = $q->direction === 'descending' ? -$expected : $expected;
                $this->assertSame(
                    $signed,
                    $actual,
                    "$label: {$q->interval} {$q->direction} from {$q->note1}{$q->octave} gave {$q->note2}{$q->note2_octave}"
                );
            }
        }
    }

    public function test_options_contain_answer_and_one_spelling_per_pitch_class(): void
    {
        foreach ($this->lessonConfigs() as $label => $config) {
            foreach ($this->generate($config) as $q) {
                $options = $q->options;
                $this->assertIsArray($options, "$label: question carries no options");
                $this->assertContains($q->note2, $options, "$label: options miss the answer {$q->note2}");
                $this->assertCount(4, $options, "$label: expected 4 options");

                $pitchClasses = array_map(
                    fn ($n) => $this->music->parseNoteChromatic($n),
                    $options
                );
                $this->assertNotContains(null, $pitchClasses, "$label: unparseable option among ".implode(',', $options));
                $this->assertSame(
                    count($pitchClasses),
                    count(array_unique($pitchClasses)),
                    "$label: enharmonic duplicate among options ".implode(',', $options)
                );
            }
        }
    }

    public function test_near_mode_options_hug_the_correct_answer(): void
    {
        $config = $this->lessonConfigs()['L13 full twelve'];
        foreach ($this->generate($config) as $q) {
            $correctPc = $this->music->parseNoteChromatic($q->note2);
            foreach ($q->options as $option) {
                if ($option === $q->note2) {
                    continue;
                }
                $pc = $this->music->parseNoteChromatic($option);
                $d = abs($pc - $correctPc);
                $this->assertLessThanOrEqual(
                    2,
                    min($d, 12 - $d),
                    "near distractor $option is more than two semitones from {$q->note2}"
                );
            }
        }
    }

    public function test_all_pitches_stay_inside_the_clef_range(): void
    {
        foreach ($this->lessonConfigs() as $label => $config) {
            [$min, $max] = $this->music->clefRangeMidi($config['clef']);
            foreach ($this->generate($config) as $q) {
                foreach ([[$q->note1, (int) $q->octave], [$q->note2, (int) $q->note2_octave]] as [$note, $octave]) {
                    $midi = $this->music->midiNumber($note, $octave);
                    $this->assertNotNull($midi, "$label: unplayable pitch $note$octave");
                    $this->assertGreaterThanOrEqual($min, $midi, "$label: $note$octave below {$config['clef']} range");
                    $this->assertLessThanOrEqual($max, $midi, "$label: $note$octave above {$config['clef']} range");
                }
            }
        }
    }

    public function test_questions_carry_their_clef(): void
    {
        foreach (['treble', 'bass'] as $clef) {
            $config = array_merge($this->lessonConfigs()['L13 full twelve'], ['clef' => $clef]);
            foreach ($this->generate($config, 10) as $q) {
                $this->assertSame($clef, $q->clef);
            }
        }
    }

    public function test_mixed_direction_yields_both_directions(): void
    {
        $directions = $this->generate($this->lessonConfigs()['L14 mixed direction'], 40)
            ->pluck('direction')->unique()->sort()->values()->all();
        $this->assertSame(['ascending', 'descending'], $directions);
    }

    public function test_options_survive_session_round_trip(): void
    {
        $questions = $this->generate($this->lessonConfigs()['L13 full twelve'], 10);
        $serialized = $this->generator->serializeForSession($questions);
        $rebuilt = $this->generator->reconstructFromSession($serialized, 'interval-construction-practice');

        foreach ($rebuilt as $q) {
            $this->assertIsArray($q->options);
            $this->assertContains($q->note2, $q->options);
            $this->assertSame('treble', $q->clef);
        }
    }

    public function test_legacy_octave_config_still_wins_over_clef(): void
    {
        $questions = $this->generate([
            'allowed_intervals' => ['Major 3rd'],
            'allowed_root_notes' => ['C'],
            'octave' => '4',
            'clef' => 'bass',
        ], 5);

        foreach ($questions as $q) {
            $this->assertSame(4, (int) $q->octave);
        }
    }
}
