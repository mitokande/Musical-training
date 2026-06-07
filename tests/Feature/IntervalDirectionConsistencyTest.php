<?php

namespace Tests\Feature;

use App\Models\LearningPathExercise;
use App\Services\LearningPathQuestionGenerator;
use App\Services\MusicTheoryService;
use Tests\TestCase;

class IntervalDirectionConsistencyTest extends TestCase
{
    private LearningPathQuestionGenerator $generator;
    private MusicTheoryService $music;

    protected function setUp(): void
    {
        parent::setUp();
        $this->music     = new MusicTheoryService();
        $this->generator = new LearningPathQuestionGenerator($this->music);
    }

    public function test_generated_interval_direction_questions_have_consistent_direction(): void
    {
        $exercise = new LearningPathExercise(['config_json' => [
            'practice_type'               => 'interval-direction-practice',
            'allowed_intervals_semitones' => range(1, 12),
            'allowed_notes'               => ['C', 'D', 'E', 'F', 'G', 'A', 'B'],
            'octave'                      => 4,
            'clef'                        => 'treble',
        ]]);

        $questions = $this->generator->generate($exercise, 50);

        $this->assertGreaterThan(0, $questions->count());

        foreach ($questions as $q) {
            $octave1     = (int) $q->octave;
            $octave2     = (int) ($q->note2_octave ?? $octave1);
            $derived     = $this->music->getDirection($q->note1, $octave1, $q->note2, $octave2);

            $this->assertSame(
                $derived,
                $q->direction,
                "Question direction '{$q->direction}' does not match derived direction '{$derived}' for {$q->note1}{$octave1} → {$q->note2}{$octave2}"
            );

            // Derived direction should never be 'unison' for an interval practice
            $this->assertNotSame(
                'unison',
                $derived,
                "Generated question {$q->note1}{$octave1} → {$q->note2}{$octave2} has zero semitones (unison), should not appear"
            );
        }
    }

    public function test_generated_melodic_interval_questions_store_correct_note2_octave(): void
    {
        $exercise = new LearningPathExercise(['config_json' => [
            'practice_type'     => 'melodic-interval-practice',
            'allowed_intervals' => ['Major 3rd', 'Perfect 5th', 'Major 7th', 'Perfect Octave'],
            'allowed_notes'     => ['B', 'A', 'G'],
            'octave_range'      => ['4'],
        ]]);

        $questions = $this->generator->generate($exercise, 30);

        foreach ($questions as $q) {
            $this->assertNotNull($q->note2_octave, "note2_octave must be set on generated questions");

            // Recalculate expected note2 from service
            $expected = $this->music->noteAboveByInterval($q->note1, (int) $q->octave, $q->interval);
            $this->assertNotNull($expected);

            $this->assertSame(
                $expected['note'],
                $q->note2,
                "note2 mismatch for {$q->note1}{$q->octave} + {$q->interval}: expected {$expected['note']}, got {$q->note2}"
            );

            $this->assertSame(
                $expected['octave'],
                (int) $q->note2_octave,
                "note2_octave mismatch for {$q->note1}{$q->octave} + {$q->interval}: expected {$expected['octave']}, got {$q->note2_octave}"
            );
        }
    }

    public function test_cross_octave_intervals_do_not_produce_wrong_direction(): void
    {
        // B4 + Major 3rd should produce D#5 (ascending), not D#4 (descending)
        $exercise = new LearningPathExercise(['config_json' => [
            'practice_type'               => 'interval-direction-practice',
            'allowed_intervals_semitones' => [4], // Major 3rd
            'allowed_notes'               => ['B'],
            'octave'                      => 4,
            'clef'                        => 'treble',
        ]]);

        $questions = $this->generator->generate($exercise, 10);

        // Filter to ascending questions only
        $ascending = $questions->filter(fn($q) => $q->direction === 'ascending');

        foreach ($ascending as $q) {
            $this->assertGreaterThan(
                (int) $q->octave,
                (int) $q->note2_octave,
                "Ascending cross-octave interval should have note2_octave > note1_octave for {$q->note1}{$q->octave} → {$q->note2}{$q->note2_octave}"
            );
        }
    }

    public function test_answer_extraction_is_consistent_with_question_data(): void
    {
        $exercise = new LearningPathExercise(['config_json' => [
            'practice_type'               => 'interval-direction-practice',
            'allowed_intervals_semitones' => [3, 4, 7],
            'allowed_notes'               => ['C', 'E', 'G'],
            'octave'                      => 4,
            'clef'                        => 'treble',
        ]]);

        $questions = $this->generator->generate($exercise, 20);
        $serialized = $this->generator->serializeForSession($questions);

        foreach ($serialized as $data) {
            $answer = $this->music->getAnswerFromQuestion($data, 'interval-direction-practice');

            // Answer from service must match derived direction from note pitches
            $octave1 = (int) ($data['octave'] ?? 4);
            $octave2 = (int) ($data['note2_octave'] ?? $octave1);
            $derived = $this->music->getDirection($data['note1'], $octave1, $data['note2'], $octave2);

            $this->assertSame(
                $derived,
                $answer,
                'getAnswerFromQuestion must return the pitch-derived direction'
            );
        }
    }
}
