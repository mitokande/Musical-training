<?php

namespace Tests\Feature;

use App\Models\Practice;
use App\Models\SingleNotePractice;
use App\Models\User;
use App\Services\Practice\PracticeAnswerGrader;
use App\Services\Practice\PracticeCatalog;
use Database\Seeders\NewPracticeTypeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Regression guard for the PracticeController extraction into
 * App\Services\Practice. The important invariant is the asymmetry that has
 * always existed: only the legacy DB answer path counts toward the daily
 * exercise quota (and therefore the streak).
 */
class PracticeProgressRecorderTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedLegacyPractices();
        $this->seed(NewPracticeTypeSeeder::class);
        app(PracticeCatalog::class)->flushCache();
    }

    /**
     * DatabaseSeeder creates these six rows with fixed ids in production; the
     * exercise-setup answer path resolves practice_id from them by slug.
     */
    private function seedLegacyPractices(): void
    {
        foreach (PracticeCatalog::LEGACY_IDS as $slug => $id) {
            Practice::create([
                'id' => $id,
                'name' => ucwords(str_replace('-', ' ', $slug)),
                'slug' => $slug,
                'description' => 'Test fixture',
                'type' => 'Recognition',
                'is_premium' => false,
            ]);
        }
    }

    public function test_the_legacy_db_path_counts_toward_the_daily_quota(): void
    {
        $user = User::factory()->create();

        $question = SingleNotePractice::create([
            'target' => 'C',
            'target_type' => 'note',
            'other_options' => 'C,D,E,F',
            'octave' => '4',
        ]);

        $this->actingAs($user)->postJson('/api/practice/check-answer', [
            'practice_id' => 1,
            'question_id' => $question->id,
            'answer' => 'C',
        ])->assertOk()->assertJsonPath('is_correct', true);

        $this->assertDatabaseHas('user_practices', [
            'user_id' => $user->id,
            'practice_id' => 1,
            'correct_answers' => 1,
        ]);
        $this->assertDatabaseHas('daily_exercise_counts', [
            'user_id' => $user->id,
            'practice_id' => 1,
            'count' => 1,
        ]);
    }

    public function test_the_exercise_setup_path_does_not_count_toward_the_daily_quota(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->withSession(['exercise_practice_session' => [
            'practice_type' => 'melodic-interval-practice',
            'question_count' => 1,
            'questions' => [[
                'interval' => 'Major 3rd',
                'note1' => 'C',
                'note2' => 'E',
                'octave' => 4,
                'note2_octave' => 4,
                'direction' => 'ascending',
            ]],
        ]])->postJson('/api/practice/check-answer', [
            'question_id' => 1,
            'answer' => 'Major 3rd',
        ])->assertOk()->assertJsonPath('is_correct', true);

        $this->assertDatabaseHas('user_practices', [
            'user_id' => $user->id,
            'practice_id' => 4,
            'correct_answers' => 1,
        ]);
        $this->assertDatabaseMissing('daily_exercise_counts', ['user_id' => $user->id]);
    }

    public function test_interval_stats_are_recorded_for_interval_types_only(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->withSession(['exercise_practice_session' => [
            'practice_type' => 'melodic-interval-practice',
            'question_count' => 1,
            'questions' => [[
                'interval' => 'Perfect 5th',
                'note1' => 'C',
                'note2' => 'G',
                'octave' => 4,
                'note2_octave' => 4,
                'direction' => 'ascending',
            ]],
        ]])->postJson('/api/practice/check-answer', [
            'question_id' => 1,
            'answer' => 'Perfect 5th',
        ])->assertOk();

        $this->assertDatabaseHas('user_interval_stats', [
            'user_id' => $user->id,
            'practice_id' => 4,
            'interval_name' => 'Perfect 5th',
            'correct_answers' => 1,
        ]);
    }

    public function test_chord_answers_record_no_interval_stat(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->withSession(['exercise_practice_session' => [
            'practice_type' => 'chord-practice',
            'question_count' => 1,
            'questions' => [[
                'chord_type' => 'Major',
                'root_note' => 'C',
                'voicing' => 'block',
                'inversion' => 0,
                'octave' => '4',
            ]],
        ]])->postJson('/api/practice/check-answer', [
            'question_id' => 1,
            'answer' => 'Major',
        ])->assertOk()->assertJsonPath('is_correct', true);

        $this->assertDatabaseCount('user_interval_stats', 0);
    }

    public function test_note_answers_accept_enharmonic_equivalents(): void
    {
        $grader = app(PracticeAnswerGrader::class);

        $this->assertTrue($grader->isCorrect('A#', 'Bb', 'single-note-practice'));
        $this->assertTrue($grader->isCorrect('A#', 'Bb', 'interval-construction-practice'));
        $this->assertFalse($grader->isCorrect('A#', 'Bb', 'chord-practice'));
    }

    public function test_grading_ignores_case_and_whitespace(): void
    {
        $grader = app(PracticeAnswerGrader::class);

        $this->assertTrue($grader->isCorrect('major 3RD', 'Major 3rd', 'melodic-interval-practice'));
        $this->assertTrue($grader->isCorrect('  Major 3rd  ', 'Major 3rd', 'melodic-interval-practice'));
    }
}
