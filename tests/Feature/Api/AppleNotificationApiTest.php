<?php

namespace Tests\Feature\Api;

use App\Models\EmailSuppression;
use App\Models\User;
use App\Services\Auth\AppleJwks;
use Closure;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

/**
 * Apple's server-to-server notifications.
 *
 * The signature is Apple's business and is stubbed; what these cover is what
 * each event does to the account, and the two rules that hold for all four:
 * a payload we cannot verify changes nothing, and a re-delivery does the work
 * once.
 */
class AppleNotificationApiTest extends TestCase
{
    use RefreshDatabase;

    private const URL = '/api/v1/apple/notifications';

    private const AUDIENCE = 'com.mithatcanturan.harmoniva';

    /**
     * What the stubbed AppleJwks hands back. Held on the test rather than on
     * the stub because the router memoises the controller instance per route
     * for the life of the app, so a stub bound after the first request would
     * never reach it.
     *
     * @var array<string, mixed>|null
     */
    private ?array $claims = null;

    private bool $stubbed = false;

    /**
     * Stubs the key handling, leaving `audiences()` and `accepts()` real so the
     * audience check is still the one that ships.
     *
     * @param  array<string, mixed>|null  $claims
     */
    private function appleSends(?array $claims): void
    {
        config()->set('services.apple.client_ids', [self::AUDIENCE]);

        $this->claims = $claims;

        if ($this->stubbed) {
            return;
        }

        $this->stubbed = true;

        $this->instance(AppleJwks::class, new class(fn () => $this->claims) extends AppleJwks
        {
            public function __construct(private readonly Closure $claims) {}

            public function decode(string $jwt): ?array
            {
                return ($this->claims)();
            }
        });
    }

    /**
     * A verified payload carrying one event, in Apple's own shape — `events`
     * is a JSON string nested in the JWT, not an object.
     *
     * @param  array<string, mixed>  $event
     * @return array<string, mixed>
     */
    private function payload(array $event, string $audience = self::AUDIENCE): array
    {
        return [
            'iss' => AppleJwks::ISSUER,
            'aud' => $audience,
            'iat' => now()->timestamp,
            'events' => json_encode($event + ['event_time' => now()->timestamp * 1000]),
        ];
    }

    private function notify(): TestResponse
    {
        return $this->postJson(self::URL, ['payload' => 'stub.jws.signature']);
    }

    public function test_an_account_delete_event_deletes_the_account(): void
    {
        $user = User::factory()->create(['apple_id' => 'apple-000123.abc']);

        $this->appleSends($this->payload(['type' => 'account-delete', 'sub' => 'apple-000123.abc']));

        $this->notify()->assertOk();

        $this->assertSoftDeleted('users', ['id' => $user->id]);
        // The deletion path releases the unique identity columns, apple_id
        // among them, or the same Apple ID could never sign up again. Read
        // through withTrashed: fresh() honours the soft-delete scope and would
        // hand back null.
        $this->assertNull(User::withTrashed()->find($user->id)->apple_id);
    }

    public function test_a_consent_revoked_event_ends_every_session_but_keeps_the_account(): void
    {
        $user = User::factory()->create([
            'apple_id' => 'apple-000123.abc',
            'password' => Hash::make('password123!'),
        ]);
        $user->createToken('iPhone');

        $this->appleSends($this->payload(['type' => 'consent-revoked', 'sub' => 'apple-000123.abc']));

        $this->notify()->assertOk();

        $this->assertSame(0, $user->tokens()->count());
        $this->assertNotNull($user->fresh());
        $this->assertFalse($user->fresh()->isDeleted());
    }

    /**
     * An account created through Apple has no password, so a revoked consent
     * has just shut its only door. The reset link is the way back in.
     */
    public function test_a_passwordless_account_is_sent_a_reset_link_when_consent_is_revoked(): void
    {
        Notification::fake();

        $user = User::factory()->create([
            'apple_id' => 'apple-000123.abc',
            'password' => null,
        ]);

        $this->appleSends($this->payload(['type' => 'consent-revoked', 'sub' => 'apple-000123.abc']));

        $this->notify()->assertOk();

        Notification::assertSentTo($user, ResetPassword::class);
    }

    public function test_an_account_with_a_password_is_not_sent_anything(): void
    {
        Notification::fake();

        User::factory()->create([
            'apple_id' => 'apple-000123.abc',
            'password' => Hash::make('password123!'),
        ]);

        $this->appleSends($this->payload(['type' => 'consent-revoked', 'sub' => 'apple-000123.abc']));

        $this->notify()->assertOk();

        Notification::assertNothingSent();
    }

    public function test_a_disabled_relay_address_is_suppressed_and_enabled_again_lifts_it(): void
    {
        User::factory()->create([
            'apple_id' => 'apple-000123.abc',
            'email' => 'xyz@privaterelay.appleid.com',
        ]);

        $this->appleSends($this->payload([
            'type' => 'email-disabled',
            'sub' => 'apple-000123.abc',
            'email' => 'XYZ@privaterelay.appleid.com',
        ]));

        $this->notify()->assertOk();

        // Lower-cased on the way in, like every other address in this system.
        $this->assertDatabaseHas('email_suppressions', [
            'email' => 'xyz@privaterelay.appleid.com',
            'reason' => 'apple_relay_disabled',
        ]);

        $this->appleSends($this->payload([
            'type' => 'email-enabled',
            'sub' => 'apple-000123.abc',
            'email' => 'xyz@privaterelay.appleid.com',
        ]));

        $this->notify()->assertOk();

        $this->assertDatabaseMissing('email_suppressions', ['email' => 'xyz@privaterelay.appleid.com']);
    }

    /**
     * Apple saying a relay forwards again is not Apple saying the address stopped
     * bouncing. Only our own entry is lifted.
     */
    public function test_an_email_enabled_event_leaves_a_bounce_suppression_alone(): void
    {
        User::factory()->create(['apple_id' => 'apple-000123.abc', 'email' => 'ada@example.com']);

        EmailSuppression::create([
            'email' => 'ada@example.com',
            'reason' => 'hard_bounce',
            'suppressed_at' => now(),
        ]);

        $this->appleSends($this->payload([
            'type' => 'email-enabled',
            'sub' => 'apple-000123.abc',
            'email' => 'ada@example.com',
        ]));

        $this->notify()->assertOk();

        $this->assertDatabaseHas('email_suppressions', ['email' => 'ada@example.com', 'reason' => 'hard_bounce']);
    }

    /**
     * The audience check, which is the whole authentication story here: Apple
     * signs for every app on the store, so a payload minted for someone else's
     * is a valid signature over someone else's business.
     */
    public function test_a_payload_for_another_app_is_refused(): void
    {
        $user = User::factory()->create(['apple_id' => 'apple-000123.abc']);

        $this->appleSends($this->payload(
            ['type' => 'account-delete', 'sub' => 'apple-000123.abc'],
            audience: 'com.someone.else',
        ));

        $this->notify()->assertStatus(400);

        $this->assertNotNull($user->fresh());
        $this->assertFalse($user->fresh()->isDeleted());
    }

    public function test_a_payload_from_another_issuer_is_refused(): void
    {
        User::factory()->create(['apple_id' => 'apple-000123.abc']);

        $claims = $this->payload(['type' => 'account-delete', 'sub' => 'apple-000123.abc']);
        $claims['iss'] = 'https://accounts.google.com';

        $this->appleSends($claims);

        $this->notify()->assertStatus(400);

        $this->assertSame(1, User::count());
    }

    /**
     * A signature that will not check out never will, so it is a 400 and Apple
     * drops it rather than retrying forever.
     */
    public function test_an_unverifiable_payload_is_refused_without_a_retry(): void
    {
        $this->appleSends(null);

        $this->notify()->assertStatus(400);
    }

    public function test_a_request_without_a_payload_field_is_refused(): void
    {
        $this->appleSends($this->payload(['type' => 'account-delete', 'sub' => 'apple-000123.abc']));

        $this->postJson(self::URL, [])->assertStatus(400);
    }

    /**
     * An empty allow-list cannot tell Apple's notification from anyone else's.
     * A 5xx, not a 400: Apple retries, and by then someone should have noticed.
     */
    public function test_a_server_without_apple_configured_asks_for_the_retry(): void
    {
        $this->appleSends($this->payload(['type' => 'account-delete', 'sub' => 'apple-000123.abc']));
        config()->set('services.apple.client_ids', []);

        $this->notify()->assertStatus(503);
    }

    public function test_an_event_for_an_unknown_apple_id_is_acknowledged_and_ignored(): void
    {
        User::factory()->create(['apple_id' => 'apple-000123.abc']);

        $this->appleSends($this->payload(['type' => 'account-delete', 'sub' => 'apple-999.zzz']));

        $this->notify()->assertOk();

        $this->assertSame(1, User::count());
    }

    /** Apple adds event types; an unknown one must not become a retry loop. */
    public function test_an_unknown_event_type_is_acknowledged(): void
    {
        $this->appleSends($this->payload(['type' => 'something-new', 'sub' => 'apple-000123.abc']));

        $this->notify()->assertOk();
    }

    /**
     * Apple retries anything it did not see a 2xx for, including deliveries
     * that in fact succeeded. The second one must not repeat the work.
     */
    public function test_a_redelivered_event_is_handled_once(): void
    {
        $user = User::factory()->create([
            'apple_id' => 'apple-000123.abc',
            'password' => Hash::make('password123!'),
        ]);
        $user->createToken('iPhone');

        $this->appleSends($this->payload(['type' => 'consent-revoked', 'sub' => 'apple-000123.abc']));

        $this->notify()->assertOk();
        $this->assertSame(0, $user->tokens()->count());

        // A session started again in between — a learner who signed back in
        // with a password. The retry must not sign them out a second time.
        $user->createToken('iPad');

        $this->notify()->assertOk();

        $this->assertSame(1, $user->tokens()->count());
    }
}
