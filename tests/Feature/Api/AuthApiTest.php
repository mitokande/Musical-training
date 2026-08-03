<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_issues_a_token_and_fires_registered(): void
    {
        Event::fake([Registered::class]);

        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'Ada',
            'email' => 'ada@example.com',
            'password' => 'password123!',
            'password_confirmation' => 'password123!',
            'device_name' => 'iPhone 15',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.user.email', 'ada@example.com')
            ->assertJsonPath('data.user.role', 'user')
            ->assertJsonStructure(['data' => ['token', 'user' => ['id', 'username', 'plan']]]);

        Event::assertDispatched(Registered::class);
        $this->assertDatabaseHas('personal_access_tokens', ['name' => 'iPhone 15']);
    }

    public function test_register_rejects_a_duplicate_email(): void
    {
        User::factory()->create(['email' => 'taken@example.com']);

        $this->postJson('/api/v1/auth/register', [
            'name' => 'Ada',
            'email' => 'taken@example.com',
            'password' => 'password123!',
            'password_confirmation' => 'password123!',
            'device_name' => 'iPhone',
        ])->assertStatus(422)->assertJsonValidationErrors('email');
    }

    public function test_login_returns_a_token(): void
    {
        User::factory()->create([
            'email' => 'ada@example.com',
            'password' => Hash::make('password123!'),
        ]);

        $this->postJson('/api/v1/auth/login', [
            'email' => 'ada@example.com',
            'password' => 'password123!',
            'device_name' => 'Pixel',
        ])->assertOk()->assertJsonStructure(['data' => ['token', 'user']]);
    }

    public function test_login_rejects_bad_credentials(): void
    {
        User::factory()->create([
            'email' => 'ada@example.com',
            'password' => Hash::make('password123!'),
        ]);

        $this->postJson('/api/v1/auth/login', [
            'email' => 'ada@example.com',
            'password' => 'wrong-password',
            'device_name' => 'Pixel',
        ])->assertStatus(422)->assertJsonValidationErrors('email');
    }

    public function test_suspended_account_cannot_log_in(): void
    {
        User::factory()->create([
            'email' => 'ada@example.com',
            'password' => Hash::make('password123!'),
            'suspended_at' => now(),
        ]);

        $this->postJson('/api/v1/auth/login', [
            'email' => 'ada@example.com',
            'password' => 'password123!',
            'device_name' => 'Pixel',
        ])->assertStatus(403)->assertJsonPath('error.code', 'account_suspended');
    }

    public function test_me_returns_the_authenticated_user(): void
    {
        $user = User::factory()->create(['name' => 'Ada']);
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/auth/me')
            ->assertOk()
            ->assertJsonPath('data.name', 'Ada')
            ->assertJsonPath('data.plan.is_premium', false);
    }

    public function test_logout_revokes_only_the_current_token(): void
    {
        $user = User::factory()->create();
        $keep = $user->createToken('tablet')->plainTextToken;
        $current = $user->createToken('phone')->plainTextToken;

        $this->withHeader('Authorization', 'Bearer '.$current)
            ->postJson('/api/v1/auth/logout')
            ->assertOk();

        $this->assertSame(1, $user->tokens()->count());

        $this->withHeader('Authorization', 'Bearer '.$keep)
            ->getJson('/api/v1/auth/me')
            ->assertOk();
    }

    public function test_forgot_password_never_reveals_whether_the_account_exists(): void
    {
        $this->postJson('/api/v1/auth/forgot-password', ['email' => 'nobody@example.com'])
            ->assertOk()
            ->assertJsonPath('data.status', 'sent');
    }

    public function test_unauthenticated_requests_get_json_not_a_redirect(): void
    {
        $this->getJson('/api/v1/auth/me')
            ->assertStatus(401)
            ->assertHeader('content-type', 'application/json');
    }

    public function test_suspended_user_with_a_valid_token_is_blocked(): void
    {
        $user = User::factory()->create(['suspended_at' => now()]);
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/auth/me')
            ->assertStatus(403)
            ->assertJsonPath('error.code', 'account_suspended');
    }
}
