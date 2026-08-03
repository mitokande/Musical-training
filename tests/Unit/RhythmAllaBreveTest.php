<?php

namespace Tests\Unit;

use App\Models\LearningPathExercise;
use App\Services\LearningPathQuestionGenerator;
use Tests\TestCase;

/**
 * Tests for 2/2, 3/2, and 4/2 (alla breve) time signature handling
 * in the rhythm question generator.
 *
 * Metronome click timing and beat unit are implemented in JavaScript
 * (playRhythmAudio / initReadingMode in practice-rhythm.blade.php)
 * and require a browser test environment for full verification.
 * These PHP tests cover the server-side tempo storage and question
 * generation for x/2 meters.
 */
class RhythmAllaBreveTest extends TestCase
{
    private LearningPathQuestionGenerator $generator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->generator = app(LearningPathQuestionGenerator::class);
    }

    /** 2/2 questions are generated and store tempo = 50 */
    public function test_generated_2_2_questions_store_correct_tempo(): void
    {
        $exercise = new LearningPathExercise(['config_json' => [
            'practice_type' => 'rhythm-practice',
            'time_signatures' => ['2/2'],
            'allowed_note_values' => ['quarter', 'half'],
            'tempo_range' => [50, 50],
            'bars' => 1,
        ]]);

        $questions = $this->generator->generate($exercise, 5);

        $this->assertGreaterThan(0, $questions->count());
        foreach ($questions as $q) {
            $this->assertSame('2/2', $q->time_signature, '2/2 time signature must be preserved');
            $this->assertSame(50, (int) $q->tempo, '2/2 default tempo must be 50');
        }
    }

    /** 3/2 questions are generated and store tempo = 50 */
    public function test_generated_3_2_questions_store_correct_tempo(): void
    {
        $exercise = new LearningPathExercise(['config_json' => [
            'practice_type' => 'rhythm-practice',
            'time_signatures' => ['3/2'],
            'allowed_note_values' => ['quarter', 'half'],
            'tempo_range' => [50, 50],
            'bars' => 1,
        ]]);

        $questions = $this->generator->generate($exercise, 5);

        $this->assertGreaterThan(0, $questions->count());
        foreach ($questions as $q) {
            $this->assertSame('3/2', $q->time_signature);
            $this->assertSame(50, (int) $q->tempo);
        }
    }

    /** 4/2 questions are generated and store tempo = 50 */
    public function test_generated_4_2_questions_store_correct_tempo(): void
    {
        $exercise = new LearningPathExercise(['config_json' => [
            'practice_type' => 'rhythm-practice',
            'time_signatures' => ['4/2'],
            'allowed_note_values' => ['quarter', 'half'],
            'tempo_range' => [50, 50],
            'bars' => 1,
        ]]);

        $questions = $this->generator->generate($exercise, 5);

        $this->assertGreaterThan(0, $questions->count());
        foreach ($questions as $q) {
            $this->assertSame('4/2', $q->time_signature);
            $this->assertSame(50, (int) $q->tempo);
        }
    }

    /** note_values for a 2/2 bar must sum to exactly 4 quarter-note beats */
    public function test_2_2_bar_fills_exactly_four_quarter_beats(): void
    {
        $quarterBeats = [
            'whole' => 4, 'dotted-half' => 3, 'half' => 2, 'dotted-quarter' => 1.5,
            'quarter' => 1, 'dotted-eighth' => 0.75, 'eighth' => 0.5, 'sixteenth' => 0.25,
            'whole_rest' => 4, 'half_rest' => 2, 'quarter_rest' => 1, 'eighth_rest' => 0.5,
            'triplet-quarter' => 2 / 3, 'triplet-eighth' => 1 / 3,
        ];

        $exercise = new LearningPathExercise(['config_json' => [
            'practice_type' => 'rhythm-practice',
            'time_signatures' => ['2/2'],
            'allowed_note_values' => ['quarter', 'half', 'whole'],
            'tempo_range' => [50, 50],
            'bars' => 1,
        ]]);

        $questions = $this->generator->generate($exercise, 10);

        foreach ($questions as $q) {
            $noteValues = $q->note_values;
            if (is_string($noteValues)) {
                $noteValues = json_decode($noteValues, true) ?? [];
            }
            $total = array_reduce($noteValues, fn ($carry, $v) => $carry + ($quarterBeats[$v] ?? 0), 0.0);
            $this->assertEqualsWithDelta(
                4.0, $total, 0.001,
                "2/2 bar should fill exactly 4 quarter-note beats (2 half-note beats), got {$total} for: ".implode(',', $noteValues)
            );
        }
    }

    /** note_values for a 3/2 bar must sum to exactly 6 quarter-note beats */
    public function test_3_2_bar_fills_exactly_six_quarter_beats(): void
    {
        $quarterBeats = [
            'whole' => 4, 'dotted-half' => 3, 'half' => 2, 'dotted-quarter' => 1.5,
            'quarter' => 1, 'dotted-eighth' => 0.75, 'eighth' => 0.5, 'sixteenth' => 0.25,
            'whole_rest' => 4, 'half_rest' => 2, 'quarter_rest' => 1, 'eighth_rest' => 0.5,
        ];

        $exercise = new LearningPathExercise(['config_json' => [
            'practice_type' => 'rhythm-practice',
            'time_signatures' => ['3/2'],
            'allowed_note_values' => ['quarter', 'half'],
            'tempo_range' => [50, 50],
            'bars' => 1,
        ]]);

        $questions = $this->generator->generate($exercise, 10);

        foreach ($questions as $q) {
            $noteValues = $q->note_values;
            if (is_string($noteValues)) {
                $noteValues = json_decode($noteValues, true) ?? [];
            }
            $total = array_reduce($noteValues, fn ($carry, $v) => $carry + ($quarterBeats[$v] ?? 0), 0.0);
            $this->assertEqualsWithDelta(
                6.0, $total, 0.001,
                "3/2 bar should fill exactly 6 quarter-note beats (3 half-note beats), got {$total}"
            );
        }
    }

    /** note_values for a 4/2 bar must sum to exactly 8 quarter-note beats */
    public function test_4_2_bar_fills_exactly_eight_quarter_beats(): void
    {
        $quarterBeats = [
            'whole' => 4, 'dotted-half' => 3, 'half' => 2, 'dotted-quarter' => 1.5,
            'quarter' => 1, 'dotted-eighth' => 0.75, 'eighth' => 0.5, 'sixteenth' => 0.25,
            'whole_rest' => 4, 'half_rest' => 2, 'quarter_rest' => 1, 'eighth_rest' => 0.5,
        ];

        $exercise = new LearningPathExercise(['config_json' => [
            'practice_type' => 'rhythm-practice',
            'time_signatures' => ['4/2'],
            'allowed_note_values' => ['quarter', 'half'],
            'tempo_range' => [50, 50],
            'bars' => 1,
        ]]);

        $questions = $this->generator->generate($exercise, 10);

        foreach ($questions as $q) {
            $noteValues = $q->note_values;
            if (is_string($noteValues)) {
                $noteValues = json_decode($noteValues, true) ?? [];
            }
            $total = array_reduce($noteValues, fn ($carry, $v) => $carry + ($quarterBeats[$v] ?? 0), 0.0);
            $this->assertEqualsWithDelta(
                8.0, $total, 0.001,
                "4/2 bar should fill exactly 8 quarter-note beats (4 half-note beats), got {$total}"
            );
        }
    }

    /** Existing x/4 and x/8 meters are not affected */
    public function test_existing_meters_unaffected(): void
    {
        foreach (['4/4' => 80, '3/4' => 80, '6/8' => 80] as $ts => $bpm) {
            $exercise = new LearningPathExercise(['config_json' => [
                'practice_type' => 'rhythm-practice',
                'time_signatures' => [$ts],
                'allowed_note_values' => ['quarter', 'half'],
                'tempo_range' => [$bpm, $bpm],
                'bars' => 1,
            ]]);

            $questions = $this->generator->generate($exercise, 3);
            $this->assertGreaterThan(0, $questions->count(), "{$ts} should generate questions");
            foreach ($questions as $q) {
                $this->assertSame($ts, $q->time_signature);
                $this->assertSame($bpm, (int) $q->tempo);
            }
        }
    }
}
