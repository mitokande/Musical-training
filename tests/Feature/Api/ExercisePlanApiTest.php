<?php

namespace Tests\Feature\Api;

use App\Models\ExerciseSetupTemplate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Saved practice plans over /api/v1/plans — the same rows the website's
 * Exercise Setup Studio writes, reachable from the app.
 */
class ExercisePlanApiTest extends TestCase
{
    use RefreshDatabase;

    private function plan(User $user, array $attributes = []): ExerciseSetupTemplate
    {
        return ExerciseSetupTemplate::create($attributes + [
            'user_id' => $user->id,
            'name' => 'Slow 6/8',
            'category' => 'rhythm',
            'exercise_type' => 'rhythm-practice',
            'settings_json' => ['time_signature' => '6/8', 'tempo' => 60],
            'is_ai_generated' => false,
            'is_favorite' => false,
        ]);
    }

    public function test_a_plan_round_trips_with_its_settings_untouched(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $settings = [
            'time_signature' => '2/2',
            'note_values' => ['quarter', 'eighth', 'dotted-quarter', 'triplet-eighth'],
            'metronome' => false,
            'nested' => ['anything' => [1, 2, 3]],
        ];

        $created = $this->postJson('/api/v1/plans', [
            'name' => 'Alla breve drill',
            'category' => 'rhythm',
            'exercise_type' => 'rhythm-practice',
            'settings' => $settings,
        ]);

        $created->assertCreated()
            ->assertJsonPath('data.name', 'Alla breve drill')
            ->assertJsonPath('data.is_favorite', false);

        // Nothing server-side interprets a plan's settings, so whatever the
        // client stored is what it gets back — keys the server has never heard
        // of included.
        $this->assertSame($settings, $created->json('data.settings'));

        $this->getJson('/api/v1/plans')
            ->assertOk()
            ->assertJsonPath('data.0.settings', $settings)
            ->assertJsonPath('meta.used', 1);
    }

    public function test_plans_are_listed_favourites_first_then_most_recent(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $old = $this->plan($user, ['name' => 'Oldest']);
        $old->forceFill(['updated_at' => now()->subDay()])->save();
        $recent = $this->plan($user, ['name' => 'Recent']);
        $favourite = $this->plan($user, ['name' => 'Favourite', 'is_favorite' => true]);
        $favourite->forceFill(['updated_at' => now()->subWeek()])->save();

        $names = collect($this->getJson('/api/v1/plans')->json('data'))->pluck('name')->all();

        // A favourite outranks recency; that is the website's order too.
        $this->assertSame(['Favourite', 'Recent', 'Oldest'], $names);
        $this->assertSame([$favourite->id, $recent->id, $old->id], collect(
            $this->getJson('/api/v1/plans')->json('data')
        )->pluck('id')->all());
    }

    public function test_the_free_allowance_is_enforced_and_explains_itself(): void
    {
        $user = User::factory()->create(['plan' => 'free']);
        Sanctum::actingAs($user);

        $limit = (int) $user->getPlanLimit('saved_plans_limit');
        $this->assertSame(3, $limit, 'the free plan is the one being tested');

        for ($i = 0; $i < $limit; $i++) {
            $this->postJson('/api/v1/plans', [
                'name' => "Plan {$i}",
                'category' => 'rhythm',
                'exercise_type' => 'rhythm-practice',
                'settings' => ['tempo' => 80],
            ])->assertCreated();
        }

        $this->postJson('/api/v1/plans', [
            'name' => 'One too many',
            'category' => 'rhythm',
            'exercise_type' => 'rhythm-practice',
            'settings' => ['tempo' => 80],
        ])
            ->assertStatus(403)
            ->assertJsonPath('error.code', 'premium_required')
            ->assertJsonPath('error.details.limit', 3)
            ->assertJsonPath('error.details.used', 3);

        $this->assertSame(3, ExerciseSetupTemplate::where('user_id', $user->id)->count());
    }

    public function test_premium_saves_without_a_ceiling(): void
    {
        $user = User::factory()->create(['plan' => 'premium']);
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/plans')->assertJsonPath('meta.limit', -1);

        foreach (range(1, 5) as $i) {
            $this->postJson('/api/v1/plans', [
                'name' => "Plan {$i}",
                'category' => 'rhythm',
                'exercise_type' => 'rhythm-practice',
                'settings' => ['tempo' => 80],
            ])->assertCreated();
        }
    }

    public function test_a_plan_can_be_renamed_favourited_and_deleted(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $plan = $this->plan($user);

        $this->putJson("/api/v1/plans/{$plan->id}", ['name' => 'Renamed', 'is_favorite' => true])
            ->assertOk()
            ->assertJsonPath('data.name', 'Renamed')
            ->assertJsonPath('data.is_favorite', true)
            // An untouched field stays as it was.
            ->assertJsonPath('data.settings.time_signature', '6/8');

        $this->deleteJson("/api/v1/plans/{$plan->id}")
            ->assertOk()
            ->assertJsonPath('data.status', 'deleted');

        $this->assertDatabaseMissing('exercise_setup_templates', ['id' => $plan->id]);
    }

    public function test_another_persons_plan_is_invisible_rather_than_forbidden(): void
    {
        $owner = User::factory()->create();
        $plan = $this->plan($owner);

        Sanctum::actingAs(User::factory()->create());

        // 403 would confirm the id exists; a stranger has no business knowing.
        $this->getJson('/api/v1/plans')->assertOk()->assertJsonCount(0, 'data');
        $this->putJson("/api/v1/plans/{$plan->id}", ['name' => 'Mine now'])
            ->assertStatus(404)
            ->assertJsonPath('error.code', 'not_found');
        $this->deleteJson("/api/v1/plans/{$plan->id}")->assertStatus(404);

        $this->assertDatabaseHas('exercise_setup_templates', ['id' => $plan->id, 'name' => 'Slow 6/8']);
    }

    public function test_the_endpoints_need_a_token(): void
    {
        $this->getJson('/api/v1/plans')->assertStatus(401);
        $this->postJson('/api/v1/plans', [])->assertStatus(401);
    }

    public function test_a_plan_needs_a_name_a_type_and_settings(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->postJson('/api/v1/plans', ['settings' => 'not-an-array'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'category', 'exercise_type', 'settings']);
    }
}
