<?php

namespace Tests\Feature\Api;

use App\Models\User;
use App\Services\Practice\PracticeCatalog;
use App\Services\UsageQuotaService;
use Database\Seeders\NewPracticeTypeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class QuotaApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(NewPracticeTypeSeeder::class);
        app(PracticeCatalog::class)->flushCache();
    }

    public function test_free_plan_clamps_the_question_count_instead_of_erroring(): void
    {
        Sanctum::actingAs(User::factory()->create(['plan' => 'free']));

        $cap = (int) config('plans.user.free.session_question_cap');

        $this->postJson('/api/v1/sessions', [
            'source' => 'studio',
            'practice_type' => 'chord-practice',
            'question_count' => 20,
        ])
            ->assertCreated()
            ->assertJsonPath('data.session.question_count', $cap)
            ->assertJsonPath('meta.clamped', true)
            ->assertJsonPath('meta.requested_question_count', 20);
    }

    public function test_premium_keeps_the_requested_question_count(): void
    {
        Sanctum::actingAs(User::factory()->create(['plan' => 'premium']));

        $this->postJson('/api/v1/sessions', [
            'source' => 'studio',
            'practice_type' => 'chord-practice',
            'question_count' => 15,
        ])
            ->assertCreated()
            ->assertJsonPath('data.session.question_count', 15)
            ->assertJsonPath('meta.clamped', false);
    }

    public function test_exhausting_the_daily_studio_quota_returns_403(): void
    {
        $user = User::factory()->create(['plan' => 'free']);
        Sanctum::actingAs($user);

        $limit = (int) config('plans.user.free.studio_daily_sessions');
        $usage = app(UsageQuotaService::class);

        for ($i = 0; $i < $limit; $i++) {
            $usage->userIncrement($user, UsageQuotaService::FEATURE_STUDIO_SESSIONS);
        }

        $this->postJson('/api/v1/sessions', [
            'source' => 'studio',
            'practice_type' => 'chord-practice',
            'question_count' => 5,
        ])
            ->assertStatus(403)
            ->assertJsonPath('error.code', 'quota_exceeded')
            ->assertJsonPath('error.details.limit', $limit)
            ->assertJsonStructure(['error' => ['details' => ['reset_at', 'upgrade_url']]]);
    }

    public function test_creating_a_session_consumes_studio_quota(): void
    {
        $user = User::factory()->create(['plan' => 'free']);
        Sanctum::actingAs($user);

        $this->postJson('/api/v1/sessions', [
            'source' => 'studio',
            'practice_type' => 'chord-practice',
            'question_count' => 5,
        ])->assertCreated();

        $this->assertSame(
            1,
            app(UsageQuotaService::class)->userUsed($user, UsageQuotaService::FEATURE_STUDIO_SESSIONS),
        );
    }

    public function test_premium_quota_is_not_consumed(): void
    {
        $user = User::factory()->create(['plan' => 'premium']);
        Sanctum::actingAs($user);

        $this->postJson('/api/v1/sessions', [
            'source' => 'studio',
            'practice_type' => 'chord-practice',
            'question_count' => 5,
        ])->assertCreated();

        $this->assertSame(
            0,
            app(UsageQuotaService::class)->userUsed($user, UsageQuotaService::FEATURE_STUDIO_SESSIONS),
        );
    }

    public function test_plan_endpoint_reports_limits_and_usage(): void
    {
        $user = User::factory()->create(['plan' => 'free']);
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/me/plan')
            ->assertOk()
            ->assertJsonPath('data.plan', 'free')
            ->assertJsonPath('data.is_premium', false)
            ->assertJsonPath('data.limits.session_question_cap.unlimited', false)
            ->assertJsonPath('data.usage.studio_sessions.used', 0)
            ->assertJsonStructure(['data' => ['usage' => ['learning_path_sessions' => ['limit', 'remaining']]]]);
    }

    public function test_premium_limits_are_reported_as_unlimited_not_minus_one(): void
    {
        Sanctum::actingAs(User::factory()->create(['plan' => 'premium']));

        $this->getJson('/api/v1/me/plan')
            ->assertOk()
            ->assertJsonPath('data.limits.session_question_cap.unlimited', true)
            ->assertJsonPath('data.limits.session_question_cap.value', null)
            ->assertJsonPath('data.usage.studio_sessions.unlimited', true);
    }
}
