<?php

namespace Tests\Unit;

use App\Models\LearningPathExercise;
use App\Services\LearningPathQuestionGenerator;
use Tests\TestCase;

class SingleNoteOctaveMappingTest extends TestCase
{
    private LearningPathQuestionGenerator $generator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->generator = app(LearningPathQuestionGenerator::class);
    }

    private function makeExercise(array $notes, array $octaves): LearningPathExercise
    {
        return new LearningPathExercise(['config_json' => [
            'practice_type'    => 'single-note-practice',
            'allowed_notes'    => $notes,
            'octave_range'     => $octaves,
            'distractor_count' => 2,
        ]]);
    }

    public function test_generated_questions_store_octave_3_below_c4(): void
    {
        $questions = $this->generator->generate($this->makeExercise(['C', 'F', 'G'], ['3']), 15);

        foreach ($questions as $q) {
            $this->assertSame('3', $q->octave,
                "Expected octave 3 (below C4), got {$q->octave} for note {$q->target}");
        }
    }

    public function test_generated_questions_store_octave_5_above_c4(): void
    {
        $questions = $this->generator->generate($this->makeExercise(['C', 'F', 'A'], ['5']), 15);

        foreach ($questions as $q) {
            $this->assertSame('5', $q->octave,
                "Expected octave 5 (above C4), got {$q->octave} for note {$q->target}");
        }
    }

    public function test_mixed_octave_range_covers_both_octaves(): void
    {
        $questions = $this->generator->generate(
            $this->makeExercise(['C', 'D', 'E', 'F', 'G', 'A', 'B'], ['4', '5']),
            50
        );

        $octaves = $questions->pluck('octave')->unique()->sort()->values()->all();
        $this->assertContains('4', $octaves, 'octave 4 should appear in mixed range');
        $this->assertContains('5', $octaves, 'octave 5 should appear in mixed range');
    }

    public function test_vexflow_key_format_preserves_octave_for_staff_rendering(): void
    {
        // Simulate what the blade does: strtolower($n['target']) . '/' . $n['octave']
        $questions = $this->generator->generate($this->makeExercise(['F'], ['5']), 1);
        $first = $questions->first();

        $vfKey = strtolower($first->target) . '/' . $first->octave;

        $this->assertSame('f/5', $vfKey,
            "F5 question must produce VexFlow key 'f/5', not 'f/4'");
        [$pitchClass, $octave] = explode('/', $vfKey);
        $this->assertSame('f', $pitchClass);
        $this->assertSame('5', $octave);
    }

    public function test_vexflow_key_format_for_c3_below_middle_c(): void
    {
        $questions = $this->generator->generate($this->makeExercise(['C'], ['3']), 1);
        $first = $questions->first();

        $vfKey = strtolower($first->target) . '/' . $first->octave;

        $this->assertSame('c/3', $vfKey,
            "C3 question must produce VexFlow key 'c/3', not 'c/4'");
    }

    public function test_accidental_note_preserves_octave_in_vf_key(): void
    {
        $questions = $this->generator->generate($this->makeExercise(['A#'], ['3']), 1);
        $first = $questions->first();

        $vfKey = strtolower($first->target) . '/' . $first->octave;

        $this->assertSame('a#/3', $vfKey,
            "A#3 (Bb3) question must produce VexFlow key 'a#/3', not 'a#/4'");
    }

    public function test_question_target_and_octave_are_separate_fields(): void
    {
        $questions = $this->generator->generate($this->makeExercise(['G'], ['5']), 5);

        foreach ($questions as $q) {
            $this->assertSame('G', $q->target, 'target should be pitch class only, no octave suffix');
            $this->assertSame('5', $q->octave, 'octave should be stored separately');
            $this->assertStringNotContainsString('/', $q->target, 'target must not include slash notation');
        }
    }
}
