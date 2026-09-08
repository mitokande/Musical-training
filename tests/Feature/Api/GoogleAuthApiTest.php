<?php

namespace Tests\Feature\Api;

use App\Models\User;
use App\Services\Auth\GoogleIdentity;
use App\Services\Auth\GoogleIdTokenVerifier;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * The mobile Google sign-in. The token verification itself is Google's
 * business and is stubbed here; what these cover is the account side — which
 * of the three cases a token lands in, and that none of them can quietly
 * hand out someone else's account.
 */
class GoogleAuthApiTest extends TestCase
{
    use RefreshDatabase;

    private function googleReturns(GoogleIdentity $identity): void
    {
        $this->instance(GoogleIdTokenVerifier::class, new class($identity) extends GoogleIdTokenVerifier
        {
            public function __construct(private readonly GoogleIdentity $identity) {}

            public function verify(string $idToken): GoogleIdentity
            {
                return $this->identity;
            }
        });
    }

    private function identity(string $email = 'ada@example.com', string $id = 'google-123'): GoogleIdentity
    {
        return new GoogleIdentity(id: $id, email: $email, name: 'Ada Lovelace', avatar: 'https://lh3.example/a.png');
    }

    public function test_a_new_google_account_is_created_and_verified(): void
    {
        Event::fake([Registered::class]);
        $this->googleReturns($this->identity());

        $response = $this->postJson('/api/v1/auth/google', [
            'id_token' => 'stub',
            'device_name' => 'iPhone 15',
            'locale' => 'en',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.created', true)
            ->assertJsonPath('data.user.email', 'ada@example.com')
            ->assertJsonPath('data.user.locale', 'en')
            // No password was set, and the app branches on this for both the
            // change-password and delete-account challenges.
            ->assertJsonPath('data.user.has_password', false)
            ->assertJsonPath('data.user.email_verified', true)
            ->assertJsonStructure(['data' => ['token', 'user' => ['id', 'username', 'plan']]]);

        $this->assertDatabaseHas('users', ['email' => 'ada@example.com', 'google_id' => 'google-123']);
        $this->assertDatabaseHas('personal_access_tokens', ['name' => 'iPhone 15']);
        Event::assertDispatched(Registered::class);
    }

    public function test_an_existing_password_account_is_linked_rather_than_duplicated(): void
    {
        $user = User::factory()->create([
            'email' => 'ada@example.com',
            'password' => Hash::make('password123!'),
        ]);

        $this->googleReturns($this->identity());

        $this->postJson('/api/v1/auth/google', ['id_token' => 'stub', 'device_name' => 'Pixel'])
            ->assertOk()
            ->assertJsonPath('data.created', false)
            ->assertJsonPath('data.user.id', $user->id)
            // Linking adds a way in; it must not take the password away.
            ->assertJsonPath('data.user.has_password', true);

        $this->assertSame(1, User::where('email', 'ada@example.com')->count());
        $this->assertSame('google-123', $user->fresh()->google_id);
    }

    public function test_the_account_is_matched_on_google_id_even_after_the_address_changed(): void
    {
        $user = User::factory()->create(['email' => 'old@example.com', 'google_id' => 'google-123']);

        $this->googleReturns($this->identity(email: 'new@example.com'));

        $this->postJson('/api/v1/auth/google', ['id_token' => 'stub', 'device_name' => 'Pixel'])
            ->assertOk()
            ->assertJsonPath('data.user.id', $user->id);

        $this->assertSame(1, User::count());
    }

    public function test_signing_in_with_google_verifies_an_unverified_address(): void
    {
        $user = User::factory()->create([
            'email' => 'ada@example.com',
            'email_verified_at' => null,
        ]);

        $this->googleReturns($this->identity());

        $this->postJson('/api/v1/auth/google', ['id_token' => 'stub', 'device_name' => 'Pixel'])
            ->assertOk()
            ->assertJsonPath('data.user.email_verified', true);

        $this->assertNotNull($user->fresh()->email_verified_at);
    }

    public function test_a_suspended_account_is_refused(): void
    {
        User::factory()->create(['email' => 'ada@example.com', 'suspended_at' => now()]);

        $this->googleReturns($this->identity());

        $this->postJson('/api/v1/auth/google', ['id_token' => 'stub', 'device_name' => 'Pixel'])
            ->assertStatus(403)
            ->assertJsonPath('error.code', 'account_suspended');
    }

    public function test_an_id_token_is_required(): void
    {
        $this->postJson('/api/v1/auth/google', ['device_name' => 'Pixel'])
            ->assertStatus(422)
            ->assertJsonValidationErrors('id_token');
    }

    /**
     * The real verifier, with nothing to check an audience against. It must
     * refuse rather than wave the token through — an empty allow-list is the
     * shape a misconfigured deploy takes, and accepting everything there would
     * be the worst possible failure mode.
     */
    public function test_a_server_without_google_configured_refuses_the_token(): void
    {
        config()->set('services.google.client_id', null);
        config()->set('services.google.mobile_client_ids', []);

        $this->postJson('/api/v1/auth/google', ['id_token' => 'stub', 'device_name' => 'Pixel'])
            ->assertStatus(503)
            ->assertJsonPath('error.code', 'google_unavailable');

        $this->assertSame(0, User::count());
    }
}
