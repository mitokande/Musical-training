<?php

namespace Tests\Feature\Api;

use App\Models\DailyExerciseCount;
use App\Models\User;
use App\Models\UserPractice;
use App\Services\Practice\PracticeCatalog;
use Database\Seeders\NewPracticeTypeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class StatsApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(NewPracticeTypeSeeder::class);
        app(PracticeCatalog::class)->flushCache();
    }

    public function test_the_dashboard_reports_totals_and_accuracy(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        UserPractice::create([
            'user_id' => $user->id,
            'practice_id' => 4,
            'total_questions' => 10,
            'correct_answers' => 8,
            'incorrect_answers' => 2,
            'score' => 80,
        ]);

        $this->getJson('/api/v1/me/dashboard')
            ->assertOk()
            ->assertJsonPath('data.total_sessions', 1)
            ->assertJsonPath('data.total_questions', 10)
            ->assertJsonPath('data.total_correct', 8)
            ->assertJsonPath('data.accuracy', 80)
            ->assertJsonPath('data.resume_session', null);
    }

    public function test_the_dashboard_offers_an_active_session_to_resume(): void
    {
        Sanctum::actingAs(User::factory()->create(['plan' => 'premium']));

        $uuid = $this->postJson('/api/v1/sessions', [
            'source' => 'studio',
            'practice_type' => 'chord-practice',
            'question_count' => 5,
        ])->json('data.session.uuid');

        $this->getJson('/api/v1/me/dashboard')
            ->assertOk()
            ->assertJsonPath('data.resume_session.uuid', $uuid);
    }

    public function test_the_streak_follows_daily_exercise_counts(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        foreach ([0, 1, 2] as $daysAgo) {
            DailyExerciseCount::create([
                'user_id' => $user->id,
                'practice_id' => 4,
                'date' => now()->subDays($daysAgo)->toDateString(),
                'count' => 3,
            ]);
        }

        $this->getJson('/api/v1/me/dashboard')
            ->assertOk()
            ->assertJsonPath('data.streak', 3);
    }

    public function test_stats_cover_every_practice_type(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $response = $this->getJson('/api/v1/me/stats')->assertOk();

        $this->assertCount(10, $response->json('data.per_practice'));
        $this->assertSame(
            app(PracticeCatalog::class)->slugs(),
            array_column($response->json('data.per_practice'), 'slug'),
        );
    }

    public function test_interval_breakdown_is_locked_on_the_free_plan(): void
    {
        Sanctum::actingAs(User::factory()->create(['plan' => 'free']));

        $this->getJson('/api/v1/me/stats')
            ->assertOk()
            ->assertJsonPath('data.intervals_locked', true)
            ->assertJsonPath('data.intervals', []);
    }

    public function test_interval_breakdown_is_available_on_premium(): void
    {
        Sanctum::actingAs(User::factory()->create(['plan' => 'premium']));

        $this->getJson('/api/v1/me/stats')
            ->assertOk()
            ->assertJsonPath('data.intervals_locked', false);
    }

    public function test_practice_types_expose_a_config_schema(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $response = $this->getJson('/api/v1/catalog/practice-types')->assertOk();

        $this->assertCount(10, $response->json('data'));

        $chord = collect($response->json('data'))->firstWhere('slug', 'chord-practice');
        $this->assertSame('choice', $chord['answer_mode']);
        $this->assertArrayHasKey('chord_types', $chord['config_schema']);
        $this->assertContains('Major', $chord['config_schema']['chord_types']['values']);

        $single = collect($response->json('data'))->firstWhere('slug', 'single-note-practice');
        $this->assertSame('note_name', $single['answer_mode']);
    }

    public function test_the_profile_can_be_updated(): void
    {
        $user = User::factory()->create(['name' => 'Ada']);
        Sanctum::actingAs($user);

        $this->putJson('/api/v1/me/profile', ['name' => 'Ada L', 'city' => 'London'])
            ->assertOk()
            ->assertJsonPath('data.name', 'Ada L')
            ->assertJsonPath('data.city', 'London');
    }

    public function test_changing_the_email_clears_verification(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        Sanctum::actingAs($user);

        $this->putJson('/api/v1/me/profile', ['email' => 'new@example.com'])
            ->assertOk()
            ->assertJsonPath('data.email_verified', false);
    }
}
