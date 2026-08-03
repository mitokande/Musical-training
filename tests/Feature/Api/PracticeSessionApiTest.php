<?php

namespace Tests\Feature\Api;

use App\Models\PracticeSession;
use App\Models\User;
use App\Models\UserPractice;
use App\Services\Practice\PracticeAnswerGrader;
use App\Services\Practice\PracticeCatalog;
use Database\Seeders\NewPracticeTypeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class PracticeSessionApiTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(NewPracticeTypeSeeder::class);
        app(PracticeCatalog::class)->flushCache();

        // Premium keeps quota out of the way; quotas have their own test.
        $this->user = User::factory()->create(['plan' => 'premium']);
        Sanctum::actingAs($this->user);
    }

    public static function practiceTypes(): array
    {
        return array_map(fn ($slug) => [$slug], [
            'single-note-practice',
            'interval-direction-practice',
            'interval-comparison-practice',
            'melodic-interval-practice',
            'harmonic-interval-practice',
            'interval-construction-practice',
            'chord-practice',
            'scale-practice',
            'rhythm-practice',
            'melodic-dictation',
        ]);
    }

    #[DataProvider('practiceTypes')]
    public function test_a_studio_session_can_be_created_for_every_practice_type(string $slug): void
    {
        $response = $this->postJson('/api/v1/sessions', [
            'source' => 'studio',
            'practice_type' => $slug,
            'question_count' => 5,
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.session.practice_type', $slug)
            ->assertJsonPath('data.session.status', 'active')
            ->assertJsonPath('data.session.question_count', 5);

        $questions = $response->json('data.questions');
        $this->assertCount(5, $questions, "No questions generated for {$slug}");

        foreach ($questions as $i => $q) {
            $this->assertSame($i, $q['index']);
            $this->assertSame($slug, $q['practice_type']);
            $this->assertArrayHasKey('audio', $q);
            $this->assertArrayHasKey('notation', $q);
            $this->assertArrayHasKey('mode', $q['answer']);
        }

        $this->assertDatabaseHas('practice_sessions', [
            'practice_type' => $slug,
            'user_id' => $this->user->id,
        ]);
    }

    #[DataProvider('practiceTypes')]
    public function test_choice_types_offer_options_containing_the_correct_answer(string $slug): void
    {
        $questions = $this->createSession($slug)['questions'];
        $session = PracticeSession::latest('id')->first();

        $mode = $questions[0]['answer']['mode'];

        if ($mode !== 'choice' && $slug !== 'rhythm-practice') {
            $this->markTestSkipped("{$slug} uses a constructed answer.");
        }

        foreach ($questions as $i => $q) {
            $values = array_column($q['answer']['options'], 'value');
            $this->assertNotEmpty($values, "No options for {$slug} question {$i}");

            $correct = app(PracticeAnswerGrader::class)
                ->correctAnswerFor($session->questionAt($i), $slug);

            $this->assertContains($correct, $values, "Correct answer missing from options for {$slug}");
        }
    }

    public function test_a_correct_answer_is_graded_and_recorded(): void
    {
        ['session' => $session] = $this->createSession('chord-practice');
        $stored = PracticeSession::where('uuid', $session['uuid'])->first();

        $correct = app(PracticeAnswerGrader::class)
            ->correctAnswerFor($stored->questionAt(0), 'chord-practice');

        $this->postJson("/api/v1/sessions/{$session['uuid']}/answers", [
            'index' => 0,
            'answer' => $correct,
        ])
            ->assertOk()
            ->assertJsonPath('data.is_correct', true)
            ->assertJsonPath('data.correct_answer', $correct)
            ->assertJsonPath('data.correct_count', 1)
            ->assertJsonPath('data.session.current_index', 1);

        $this->assertDatabaseHas('user_practices', [
            'user_id' => $this->user->id,
            'correct_answers' => 1,
        ]);
        $this->assertDatabaseHas('daily_exercise_counts', [
            'user_id' => $this->user->id,
            'count' => 1,
        ]);
    }

    public function test_a_wrong_answer_is_graded_without_credit(): void
    {
        ['session' => $session] = $this->createSession('scale-practice');

        $this->postJson("/api/v1/sessions/{$session['uuid']}/answers", [
            'index' => 0,
            'answer' => 'definitely-not-a-scale',
        ])
            ->assertOk()
            ->assertJsonPath('data.is_correct', false)
            ->assertJsonPath('data.correct_count', 0);

        $this->assertSame(1, UserPractice::where('user_id', $this->user->id)->sum('total_questions'));
    }

    public function test_answering_every_question_completes_the_session(): void
    {
        ['session' => $session] = $this->createSession('melodic-interval-practice', 3);
        $stored = PracticeSession::where('uuid', $session['uuid'])->first();
        $grader = app(PracticeAnswerGrader::class);

        for ($i = 0; $i < 3; $i++) {
            $response = $this->postJson("/api/v1/sessions/{$session['uuid']}/answers", [
                'index' => $i,
                'answer' => $grader->correctAnswerFor($stored->questionAt($i), 'melodic-interval-practice'),
            ])->assertOk();
        }

        $response->assertJsonPath('data.completed', true);
        $this->assertEquals(100, $response->json('data.score'));

        $this->assertDatabaseHas('practice_sessions', [
            'uuid' => $session['uuid'],
            'status' => 'completed',
        ]);
    }

    public function test_an_out_of_order_index_conflicts_and_reports_the_expected_one(): void
    {
        ['session' => $session] = $this->createSession('chord-practice');

        $this->postJson("/api/v1/sessions/{$session['uuid']}/answers", [
            'index' => 3,
            'answer' => 'Major',
        ])
            ->assertStatus(409)
            ->assertJsonPath('error.code', 'session_conflict')
            ->assertJsonPath('error.details.current_index', 0);
    }

    public function test_resubmitting_an_index_replays_the_stored_result(): void
    {
        ['session' => $session] = $this->createSession('chord-practice');

        $first = $this->postJson("/api/v1/sessions/{$session['uuid']}/answers", [
            'index' => 0,
            'answer' => 'Major',
        ])->assertOk()->json('data');

        $second = $this->postJson("/api/v1/sessions/{$session['uuid']}/answers", [
            'index' => 0,
            'answer' => 'Major',
        ])->assertOk()->json('data');

        $this->assertSame($first['is_correct'], $second['is_correct']);
        $this->assertSame($first['answered_count'], $second['answered_count']);
        $this->assertSame(1, PracticeSession::where('uuid', $session['uuid'])->value('answered_count'));
    }

    public function test_a_session_can_be_resumed_after_the_app_restarts(): void
    {
        ['session' => $session] = $this->createSession('scale-practice', 5);

        $this->postJson("/api/v1/sessions/{$session['uuid']}/answers", [
            'index' => 0,
            'answer' => 'Major',
        ])->assertOk();

        $this->getJson("/api/v1/sessions/{$session['uuid']}")
            ->assertOk()
            ->assertJsonPath('data.session.current_index', 1)
            ->assertJsonCount(5, 'data.questions')
            ->assertJsonCount(1, 'data.answers');
    }

    public function test_option_order_is_stable_across_requests(): void
    {
        ['session' => $session] = $this->createSession('chord-practice');

        $first = $this->getJson("/api/v1/sessions/{$session['uuid']}")->json('data.questions.0.answer.options');
        $second = $this->getJson("/api/v1/sessions/{$session['uuid']}")->json('data.questions.0.answer.options');

        $this->assertSame($first, $second);
    }

    public function test_another_users_session_is_not_found(): void
    {
        ['session' => $session] = $this->createSession('chord-practice');

        Sanctum::actingAs(User::factory()->create());

        $this->getJson("/api/v1/sessions/{$session['uuid']}")
            ->assertStatus(404)
            ->assertJsonPath('error.code', 'not_found');
    }

    public function test_an_expired_session_cannot_be_answered(): void
    {
        ['session' => $session] = $this->createSession('chord-practice');

        PracticeSession::where('uuid', $session['uuid'])->update(['expires_at' => now()->subMinute()]);

        $this->postJson("/api/v1/sessions/{$session['uuid']}/answers", [
            'index' => 0,
            'answer' => 'Major',
        ])
            ->assertStatus(409)
            ->assertJsonPath('error.details.status', 'expired');
    }

    public function test_completing_early_returns_a_review(): void
    {
        ['session' => $session] = $this->createSession('chord-practice', 5);

        $this->postJson("/api/v1/sessions/{$session['uuid']}/answers", [
            'index' => 0,
            'answer' => 'Major',
        ])->assertOk();

        $this->postJson("/api/v1/sessions/{$session['uuid']}/complete")
            ->assertOk()
            ->assertJsonPath('data.session.status', 'completed')
            ->assertJsonCount(1, 'data.review');
    }

    public function test_unknown_practice_type_is_rejected(): void
    {
        $this->postJson('/api/v1/sessions', [
            'source' => 'studio',
            'practice_type' => 'trombone-practice',
            'question_count' => 5,
        ])->assertStatus(404)->assertJsonPath('error.code', 'not_found');
    }

    private function createSession(string $slug, int $count = 5): array
    {
        return $this->postJson('/api/v1/sessions', [
            'source' => 'studio',
            'practice_type' => $slug,
            'question_count' => $count,
        ])->assertCreated()->json('data');
    }
}
