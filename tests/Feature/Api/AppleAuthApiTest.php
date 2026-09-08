<?php

namespace Tests\Feature\Api;

use App\Models\User;
use App\Services\Auth\AppleIdentity;
use App\Services\Auth\AppleIdTokenVerifier;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * The mobile Apple sign-in. Verifying the token is Apple's business and is
 * stubbed here; what these cover is the account side — which case a token
 * lands in, and the two things Apple does that Google does not: it hands over
 * the name exactly once, and it may not hand over an address at all.
 */
class AppleAuthApiTest extends TestCase
{
    use RefreshDatabase;

    private function appleReturns(AppleIdentity $identity): void
    {
        $this->instance(AppleIdTokenVerifier::class, new class($identity) extends AppleIdTokenVerifier
        {
            public function __construct(private readonly AppleIdentity $identity) {}

            public function verify(string $identityToken): AppleIdentity
            {
                return $this->identity;
            }
        });
    }

    private function identity(?string $email = 'ada@example.com', string $id = 'apple-000123.abc', bool $private = false): AppleIdentity
    {
        return new AppleIdentity(id: $id, email: $email, isPrivateEmail: $private);
    }

    public function test_a_new_apple_account_is_created_from_the_name_the_app_forwards(): void
    {
        Event::fake([Registered::class]);
        $this->appleReturns($this->identity());

        $response = $this->postJson('/api/v1/auth/apple', [
            'identity_token' => 'stub',
            // The one and only time Apple will ever send this.
            'full_name' => 'Ada Lovelace',
            'device_name' => 'iPhone 15',
            'locale' => 'en',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.created', true)
            ->assertJsonPath('data.user.name', 'Ada Lovelace')
            ->assertJsonPath('data.user.email', 'ada@example.com')
            ->assertJsonPath('data.user.locale', 'en')
            // No password was set, and the app branches on this for both the
            // change-password and delete-account challenges.
            ->assertJsonPath('data.user.has_password', false)
            ->assertJsonPath('data.user.email_verified', true)
            ->assertJsonStructure(['data' => ['token', 'user' => ['id', 'username', 'plan']]]);

        $this->assertDatabaseHas('users', ['email' => 'ada@example.com', 'apple_id' => 'apple-000123.abc']);
        $this->assertDatabaseHas('personal_access_tokens', ['name' => 'iPhone 15']);
        Event::assertDispatched(Registered::class);
    }

    public function test_an_account_created_without_a_name_falls_back_to_the_address(): void
    {
        $this->appleReturns($this->identity());

        $this->postJson('/api/v1/auth/apple', ['identity_token' => 'stub', 'device_name' => 'iPhone'])
            ->assertCreated()
            ->assertJsonPath('data.user.name', 'ada');
    }

    public function test_a_later_sign_in_without_a_name_does_not_blank_the_one_on_file(): void
    {
        $user = User::factory()->create([
            'email' => 'ada@example.com',
            'apple_id' => 'apple-000123.abc',
            'name' => 'Ada Lovelace',
        ]);

        // Apple sends the name once; every sign-in after the first has none.
        $this->appleReturns($this->identity());

        $this->postJson('/api/v1/auth/apple', ['identity_token' => 'stub', 'device_name' => 'iPhone'])
            ->assertOk()
            ->assertJsonPath('data.created', false)
            ->assertJsonPath('data.user.id', $user->id)
            ->assertJsonPath('data.user.name', 'Ada Lovelace');
    }

    public function test_an_existing_password_account_is_linked_rather_than_duplicated(): void
    {
        $user = User::factory()->create([
            'email' => 'ada@example.com',
            'password' => Hash::make('password123!'),
        ]);

        $this->appleReturns($this->identity());

        $this->postJson('/api/v1/auth/apple', ['identity_token' => 'stub', 'device_name' => 'iPhone'])
            ->assertOk()
            ->assertJsonPath('data.created', false)
            ->assertJsonPath('data.user.id', $user->id)
            // Linking adds a way in; it must not take the password away.
            ->assertJsonPath('data.user.has_password', true);

        $this->assertSame(1, User::where('email', 'ada@example.com')->count());
        $this->assertSame('apple-000123.abc', $user->fresh()->apple_id);
    }

    public function test_an_account_that_already_signed_in_with_google_gains_apple_too(): void
    {
        $user = User::factory()->create(['email' => 'ada@example.com', 'google_id' => 'google-123']);

        $this->appleReturns($this->identity());

        $this->postJson('/api/v1/auth/apple', ['identity_token' => 'stub', 'device_name' => 'iPhone'])
            ->assertOk()
            ->assertJsonPath('data.user.id', $user->id);

        $this->assertSame('google-123', $user->fresh()->google_id);
        $this->assertSame('apple-000123.abc', $user->fresh()->apple_id);
    }

    /**
     * The case the whole `apple_id` column exists for. A learner who used
     * "Hide My Email" and later turned the alias off, or who changed their
     * Apple address, arrives with a different address and the same `sub` —
     * and must land on the account they already had rather than a new one.
     */
    public function test_the_account_is_matched_on_apple_id_even_when_the_address_changed(): void
    {
        $user = User::factory()->create(['email' => 'old@example.com', 'apple_id' => 'apple-000123.abc']);

        $this->appleReturns($this->identity(email: 'new@privaterelay.appleid.com', private: true));

        $this->postJson('/api/v1/auth/apple', ['identity_token' => 'stub', 'device_name' => 'iPhone'])
            ->assertOk()
            ->assertJsonPath('data.user.id', $user->id)
            // Matched, not re-addressed: the account keeps the address it has.
            ->assertJsonPath('data.user.email', 'old@example.com');

        $this->assertSame(1, User::count());
    }

    /**
     * A relay alias is a real address. Nothing may treat it as second class —
     * it is deliverable, Apple stands behind it, and refusing it would lock
     * out everyone who values their privacy.
     */
    public function test_a_private_relay_address_creates_an_ordinary_account(): void
    {
        $this->appleReturns($this->identity(email: 'xyz@privaterelay.appleid.com', private: true));

        $this->postJson('/api/v1/auth/apple', ['identity_token' => 'stub', 'device_name' => 'iPhone'])
            ->assertCreated()
            ->assertJsonPath('data.user.email', 'xyz@privaterelay.appleid.com')
            ->assertJsonPath('data.user.email_verified', true);
    }

    /**
     * No address and no account to match: the only failure this endpoint has
     * that the learner can actually do something about, so it gets its own
     * code and a message that says what.
     */
    public function test_a_first_sign_in_without_an_address_is_refused_with_its_own_code(): void
    {
        $this->appleReturns($this->identity(email: null));

        $this->postJson('/api/v1/auth/apple', ['identity_token' => 'stub', 'device_name' => 'iPhone'])
            ->assertStatus(422)
            ->assertJsonPath('error.code', 'apple_email_unavailable');

        $this->assertSame(0, User::count());
    }

    /** The same token, once the account exists, needs no address at all. */
    public function test_a_returning_learner_signs_in_without_an_address(): void
    {
        $user = User::factory()->create(['apple_id' => 'apple-000123.abc']);

        $this->appleReturns($this->identity(email: null));

        $this->postJson('/api/v1/auth/apple', ['identity_token' => 'stub', 'device_name' => 'iPhone'])
            ->assertOk()
            ->assertJsonPath('data.user.id', $user->id);
    }

    public function test_signing_in_with_apple_verifies_an_unverified_address(): void
    {
        $user = User::factory()->create([
            'email' => 'ada@example.com',
            'email_verified_at' => null,
        ]);

        $this->appleReturns($this->identity());

        $this->postJson('/api/v1/auth/apple', ['identity_token' => 'stub', 'device_name' => 'iPhone'])
            ->assertOk()
            ->assertJsonPath('data.user.email_verified', true);

        $this->assertNotNull($user->fresh()->email_verified_at);
    }

    public function test_a_suspended_account_is_refused(): void
    {
        User::factory()->create(['email' => 'ada@example.com', 'suspended_at' => now()]);

        $this->appleReturns($this->identity());

        $this->postJson('/api/v1/auth/apple', ['identity_token' => 'stub', 'device_name' => 'iPhone'])
            ->assertStatus(403)
            ->assertJsonPath('error.code', 'account_suspended');
    }

    public function test_an_identity_token_is_required(): void
    {
        $this->postJson('/api/v1/auth/apple', ['device_name' => 'iPhone'])
            ->assertStatus(422)
            ->assertJsonValidationErrors('identity_token');
    }

    /**
     * The real verifier, with no bundle id to check an audience against. It
     * must refuse rather than wave the token through — an empty allow-list is
     * the shape a misconfigured deploy takes, and accepting everything there
     * would mean any app's Apple token could sign in as anyone here.
     */
    public function test_a_server_without_apple_configured_refuses_the_token(): void
    {
        config()->set('services.apple.client_ids', []);

        $this->postJson('/api/v1/auth/apple', ['identity_token' => 'stub', 'device_name' => 'iPhone'])
            ->assertStatus(503)
            ->assertJsonPath('error.code', 'apple_unavailable');

        $this->assertSame(0, User::count());
    }
}
