<?php

namespace Tests\Feature\Api;

use App\Models\AppEvent;
use App\Models\AppInstall;
use App\Models\User;
use App\Services\Account\AccountDeletionService;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * The mobile app's event outbox arriving at `POST /api/v1/telemetry`.
 *
 * The shape of the batch is the app's `TelemetryBatch` (src/api/endpoints.ts):
 * a device block, the device clock when it left, and up to a hundred events.
 * Most of what is worth testing is about what must *not* happen — a bad event
 * sinking a batch, a replay counting twice, a dead token signing someone out.
 */
class TelemetryApiTest extends TestCase
{
    use RefreshDatabase;

    private const INSTALL = '6f1c2d3e-4a5b-4c6d-8e7f-9a0b1c2d3e4f';

    protected function setUp(): void
    {
        parent::setUp();

        $this->travelTo(CarbonImmutable::parse('2026-09-23 12:00:00'));
    }

    private function nowMs(): int
    {
        return now()->getTimestampMs();
    }

    private function event(array $overrides = []): array
    {
        return array_merge([
            'uuid' => (string) Str::uuid(),
            'name' => 'screen',
            'props' => ['name' => '(tabs)', 'path' => '/'],
            'at' => $this->nowMs() - 60_000,
            'session_id' => (string) Str::uuid(),
        ], $overrides);
    }

    private function batch(array $events, array $overrides = []): array
    {
        return array_merge([
            'device' => [
                'install_id' => self::INSTALL,
                'platform' => 'ios',
                'os_version' => '18.2',
                'app_version' => '1.0.0',
                'build' => '2',
                'locale' => 'tr-TR',
                'timezone' => 'Europe/Istanbul',
            ],
            'sent_at' => $this->nowMs(),
            'events' => $events,
        ], $overrides);
    }

    public function test_an_anonymous_batch_is_stored_against_its_install(): void
    {
        $this->postJson('/api/v1/telemetry', $this->batch([$this->event(), $this->event(['name' => 'onboarding_step'])]))
            ->assertStatus(202)
            ->assertJsonPath('data.accepted', 2)
            ->assertJsonPath('data.duplicates', 0)
            ->assertJsonPath('data.rejected', 0);

        $this->assertSame(2, AppEvent::whereNull('user_id')->where('install_id', self::INSTALL)->count());

        $install = AppInstall::where('install_id', self::INSTALL)->sole();
        $this->assertNull($install->user_id);
        $this->assertSame('ios', $install->platform);
        $this->assertSame('Europe/Istanbul', $install->timezone);
    }

    public function test_a_signed_in_batch_belongs_to_the_account_and_claims_the_onboarding_before_it(): void
    {
        // The visitor answers the questions first, with no account…
        $this->postJson('/api/v1/telemetry', $this->batch([$this->event(['name' => 'onboarding_complete'])]))
            ->assertStatus(202);

        // …then signs up, and the next batch carries a token.
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $this->postJson('/api/v1/telemetry', $this->batch([$this->event(['name' => 'auth_success'])]))
            ->assertStatus(202)
            ->assertJsonPath('data.accepted', 1);

        $this->assertSame(2, AppEvent::where('user_id', $user->id)->count());
        $this->assertSame(0, AppEvent::whereNull('user_id')->count());
        $this->assertSame($user->id, AppInstall::where('install_id', self::INSTALL)->value('user_id'));
    }

    public function test_an_anonymous_batch_does_not_unlink_the_install(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $this->postJson('/api/v1/telemetry', $this->batch([$this->event()]))->assertStatus(202);

        $this->app['auth']->forgetGuards();
        $this->postJson('/api/v1/telemetry', $this->batch([$this->event()]), ['Authorization' => ''])
            ->assertStatus(202);

        // The second batch really was anonymous, and the link survived it.
        $this->assertSame(1, AppEvent::whereNull('user_id')->count());
        $this->assertSame($user->id, AppInstall::where('install_id', self::INSTALL)->value('user_id'));
    }

    public function test_a_dead_token_is_anonymous_rather_than_a_sign_out(): void
    {
        $this->postJson(
            '/api/v1/telemetry',
            $this->batch([$this->event()]),
            ['Authorization' => 'Bearer 999|not-a-real-token'],
        )->assertStatus(202);

        $this->assertSame(1, AppEvent::whereNull('user_id')->count());
    }

    public function test_a_replayed_batch_is_counted_once(): void
    {
        $batch = $this->batch([$this->event(), $this->event()]);

        $this->postJson('/api/v1/telemetry', $batch)->assertJsonPath('data.accepted', 2);
        $this->postJson('/api/v1/telemetry', $batch)
            ->assertStatus(202)
            ->assertJsonPath('data.accepted', 0)
            ->assertJsonPath('data.duplicates', 2);

        $this->assertSame(2, AppEvent::count());
    }

    public function test_a_bad_event_is_skipped_without_sinking_the_batch(): void
    {
        $events = [
            $this->event(),
            $this->event(['uuid' => 'not-a-uuid']),
            $this->event(['name' => 'Has Spaces']),
            $this->event(['at' => 'yesterday']),
            $this->event(['props' => 'a string']),
            $this->event(['props' => ['blob' => str_repeat('x', 5000)]]),
            'not even an object',
        ];

        $this->postJson('/api/v1/telemetry', $this->batch($events))
            ->assertStatus(202)
            ->assertJsonPath('data.accepted', 1)
            ->assertJsonPath('data.rejected', 6);
    }

    public function test_the_device_clock_is_corrected_by_the_batch_skew(): void
    {
        // The phone is an hour slow: it thinks it is 11:00 when it is noon.
        $hour = 3_600_000;
        $event = $this->event(['at' => $this->nowMs() - $hour - 60_000]);

        $this->postJson('/api/v1/telemetry', $this->batch([$event], ['sent_at' => $this->nowMs() - $hour]))
            ->assertStatus(202);

        $this->assertSame(
            '2026-09-23 11:59:00',
            AppEvent::sole()->occurred_at->format('Y-m-d H:i:s'),
        );
    }

    public function test_an_event_is_never_dated_in_the_future(): void
    {
        $this->postJson('/api/v1/telemetry', $this->batch([$this->event(['at' => $this->nowMs() + 60_000])]))
            ->assertStatus(202);

        $this->assertTrue(AppEvent::sole()->occurred_at->lte(now()));
    }

    public function test_the_props_come_back_as_they_were_sent(): void
    {
        $props = ['nodeId' => 'u1-l2', 'correct' => false, 'chosen' => 'P4', 'replays' => 2];

        $this->postJson('/api/v1/telemetry', $this->batch([$this->event(['name' => 'task_answered', 'props' => $props])]))
            ->assertStatus(202);

        $this->assertSame($props, AppEvent::sole()->props);
    }

    public function test_every_event_keeps_the_screen_it_happened_on(): void
    {
        $this->postJson('/api/v1/telemetry', $this->batch([
            $this->event(['name' => 'tap', 'props' => ['id' => 'common:action.back', 'kind' => 'press'], 'screen' => 'learn/[lesson]/run']),
            $this->event(['name' => 'tap', 'props' => ['id' => null, 'kind' => 'press'], 'screen' => 'onboarding/goals']),
            // Not a route: the event still lands, without the screen.
            $this->event(['name' => 'tap', 'props' => ['id' => 'x', 'kind' => 'press'], 'screen' => "'; DROP TABLE app_events; --"]),
            // A build from before the stamp: a screen event is its own screen.
            $this->event(['name' => 'screen', 'props' => ['name' => '(tabs)/profile', 'path' => '/profile']]),
            $this->event(['name' => 'app_open', 'props' => ['cold' => true]]),
        ]))->assertStatus(202)->assertJsonPath('data.accepted', 5);

        $this->assertSame(
            ['learn/[lesson]/run', 'onboarding/goals', null, '(tabs)/profile', null],
            AppEvent::orderBy('id')->pluck('screen')->all(),
        );
    }

    public function test_a_batch_without_a_device_is_refused_whole(): void
    {
        $this->postJson('/api/v1/telemetry', ['sent_at' => $this->nowMs(), 'events' => [$this->event()]])
            ->assertStatus(422);

        $this->postJson('/api/v1/telemetry', $this->batch([$this->event()], [
            'device' => ['install_id' => 'nope', 'platform' => 'ios'],
        ]))->assertStatus(422);

        $this->assertSame(0, AppEvent::count());
    }

    public function test_an_oversized_batch_is_refused(): void
    {
        $events = array_map(fn () => $this->event(), range(1, 201));

        $this->postJson('/api/v1/telemetry', $this->batch($events))->assertStatus(422);
    }

    public function test_deleting_the_account_deletes_its_events_and_unlinks_the_install(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $this->postJson('/api/v1/telemetry', $this->batch([$this->event(), $this->event()]))->assertStatus(202);

        $other = User::factory()->create();
        AppEvent::create([
            'uuid' => (string) Str::uuid(),
            'user_id' => $other->id,
            'install_id' => (string) Str::uuid(),
            'name' => 'screen',
            'props' => [],
            'platform' => 'android',
            'occurred_at' => now(),
            'received_at' => now(),
        ]);

        app(AccountDeletionService::class)->delete($user);

        $this->assertSame(0, AppEvent::where('user_id', $user->id)->count());
        $this->assertNull(AppInstall::where('install_id', self::INSTALL)->value('user_id'));
        $this->assertSame(1, AppEvent::where('user_id', $other->id)->count());
    }

    public function test_events_past_retention_are_pruned(): void
    {
        $this->postJson('/api/v1/telemetry', $this->batch([$this->event()]))->assertStatus(202);

        AppEvent::create([
            'uuid' => (string) Str::uuid(),
            'install_id' => self::INSTALL,
            'name' => 'screen',
            'props' => [],
            'platform' => 'ios',
            'occurred_at' => now()->subDays(AppEvent::RETENTION_DAYS + 1),
            'received_at' => now()->subDays(AppEvent::RETENTION_DAYS + 1),
        ]);

        Artisan::call('model:prune', ['--model' => [AppEvent::class]]);

        $this->assertSame(1, AppEvent::count());
    }
}
