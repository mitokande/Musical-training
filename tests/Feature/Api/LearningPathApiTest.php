<?php

namespace Tests\Feature\Api;

use App\Models\ExerciseCategory;
use App\Models\LearningPathExercise;
use App\Models\PracticeSession;
use App\Models\User;
use App\Services\Practice\PracticeAnswerGrader;
use App\Services\Practice\PracticeCatalog;
use Database\Seeders\NewPracticeTypeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class LearningPathApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(NewPracticeTypeSeeder::class);
        app(PracticeCatalog::class)->flushCache();
    }

    private function lesson(array $overrides = []): LearningPathExercise
    {
        $category = ExerciseCategory::firstOrCreate(
            ['slug' => 'intervals-test'],
            ['name' => 'Intervals', 'sort_order' => 1, 'is_active' => true],
        );

        return LearningPathExercise::create(array_merge([
            'category_id' => $category->id,
            'title' => 'Major and Minor 3rds',
            'description' => 'Hear the difference.',
            'translations' => [],
            'tags' => [],
            'skills_trained' => [],
            'slug' => 'major-minor-thirds',
            'level' => 'beginner',
            'sort_order' => 1,
            'estimated_duration_minutes' => 5,
            'is_active' => true,
            'config_json' => [
                'practice_type' => 'melodic-interval-practice',
                'allowed_intervals' => ['Major 3rd', 'Minor 3rd'],
                'allowed_notes' => ['C', 'D', 'E', 'F', 'G'],
                'clef' => 'treble',
            ],
        ], $overrides));
    }

    public function test_the_catalog_lists_categories_with_their_lessons(): void
    {
        $this->lesson();
        Sanctum::actingAs(User::factory()->create());

        $this->getJson('/api/v1/catalog/learning-path')
            ->assertOk()
            ->assertJsonPath('data.0.slug', 'intervals-test')
            ->assertJsonPath('data.0.exercises.0.slug', 'major-minor-thirds')
            ->assertJsonPath('data.0.exercises.0.practice_type', 'melodic-interval-practice')
            ->assertJsonPath('data.0.exercises.0.progress.completed', false);
    }

    public function test_a_single_lesson_reports_its_neighbours(): void
    {
        $this->lesson();
        $this->lesson(['slug' => 'perfect-fifths', 'title' => 'Perfect 5ths', 'sort_order' => 2]);
        Sanctum::actingAs(User::factory()->create());

        $this->getJson('/api/v1/catalog/learning-path/major-minor-thirds')
            ->assertOk()
            ->assertJsonPath('data.previous_slug', null)
            ->assertJsonPath('data.next_slug', 'perfect-fifths');
    }

    public function test_an_unknown_lesson_404s(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->getJson('/api/v1/catalog/learning-path/nope')
            ->assertStatus(404)
            ->assertJsonPath('error.code', 'not_found');
    }

    public function test_a_learning_path_session_records_progress(): void
    {
        $lesson = $this->lesson();
        $user = User::factory()->create(['plan' => 'premium']);
        Sanctum::actingAs($user);

        $created = $this->postJson('/api/v1/sessions', [
            'source' => 'learning_path',
            'exercise_slug' => 'major-minor-thirds',
            'question_count' => 5,
        ])->assertCreated()->json('data');

        $this->assertSame('learning_path', $created['session']['source']);
        $this->assertCount(5, $created['questions']);

        $session = PracticeSession::where('uuid', $created['session']['uuid'])->first();
        $grader = app(PracticeAnswerGrader::class);

        for ($i = 0; $i < 5; $i++) {
            $this->postJson("/api/v1/sessions/{$created['session']['uuid']}/answers", [
                'index' => $i,
                'answer' => $grader->correctAnswerFor($session->questionAt($i), 'melodic-interval-practice'),
            ])->assertOk();
        }

        $this->assertDatabaseHas('user_learning_path_progress', [
            'user_id' => $user->id,
            'learning_path_exercise_id' => $lesson->id,
            'correct_answers' => 5,
            'completed' => true,
        ]);
    }

    public function test_a_lesson_the_generator_cannot_build_is_rejected_cleanly(): void
    {
        $this->lesson([
            'slug' => 'broken-lesson',
            'config_json' => ['practice_type' => 'not-a-real-type'],
        ]);
        Sanctum::actingAs(User::factory()->create(['plan' => 'premium']));

        $this->postJson('/api/v1/sessions', [
            'source' => 'learning_path',
            'exercise_slug' => 'broken-lesson',
            'question_count' => 5,
        ])
            ->assertStatus(422)
            ->assertJsonPath('error.code', 'generation_failed');
    }

    public function test_an_inactive_lesson_cannot_be_started(): void
    {
        $this->lesson(['slug' => 'hidden-lesson', 'is_active' => false]);
        Sanctum::actingAs(User::factory()->create(['plan' => 'premium']));

        $this->postJson('/api/v1/sessions', [
            'source' => 'learning_path',
            'exercise_slug' => 'hidden-lesson',
            'question_count' => 5,
        ])->assertStatus(404);
    }
}
