<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\ExerciseCategorySeeder;
use Database\Seeders\LearningPathExerciseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Regression: Livewire injects public component properties into the view
 * AFTER explicit render() data, so view keys sharing a name with a public
 * property ($clef, $answerMode) were silently clobbered by their defaults
 * in the Learning Path flow. Non-treble LP lessons rendered a treble staff
 * and 'note-names' lessons lost their key labels.
 */
class LpBassClefFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_lp_lessons_render_their_own_clef_and_answer_mode(): void
    {
        $this->seed(ExerciseCategorySeeder::class);
        $this->seed(LearningPathExerciseSeeder::class);

        $user = User::factory()->create(['plan' => 'premium']);

        $cases = [
            // [lesson slug, practice type, expected data-clef, expected data-answer-mode]
            ['harmonic-intervals-lesson-15', 'harmonic-interval-practice', 'bass', null],
            ['melodic-intervals-lesson-15', 'melodic-interval-practice', 'bass', null],
            ['interval-construction-lesson-15', 'interval-construction-practice', 'bass', null],
            ['single-note-lesson-12', 'single-note-practice', 'bass', 'keyboard'],
            ['single-note-lesson-15', 'single-note-practice', 'alto', 'keyboard'],
            ['single-note-lesson-1', 'single-note-practice', 'treble', 'note-names'],
        ];

        foreach ($cases as [$slug, $practiceType, $expectedClef, $expectedAnswerMode]) {
            $start = $this->actingAs($user)->post(route('learning-path.start', $slug), [
                'question_count' => 5,
            ]);
            $start->assertRedirect();

            $lp = session('learning_path_session');
            $this->assertNotNull($lp, "$slug: learning_path_session missing");
            $this->assertSame($practiceType, $lp['practice_type']);

            $page = $this->actingAs($user)->get('/practice/'.$practiceType);
            $page->assertOk();
            $html = $page->getContent();

            $this->assertStringContainsString(
                'data-clef="'.$expectedClef.'"',
                $html,
                "$slug: page does not render {$expectedClef} clef"
            );
            if ($expectedClef !== 'treble') {
                $this->assertStringNotContainsString(
                    'data-clef="treble"',
                    $html,
                    "$slug: page renders treble clef instead of {$expectedClef}"
                );
            }
            if ($expectedAnswerMode !== null) {
                $this->assertStringContainsString(
                    'data-answer-mode="'.$expectedAnswerMode.'"',
                    $html,
                    "$slug: answer mode not honoured"
                );
            }

            session()->forget('learning_path_session');
        }
    }
}
