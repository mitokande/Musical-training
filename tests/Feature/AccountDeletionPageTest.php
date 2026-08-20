<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * The public /delete-account page and the mobile DELETE /api/v1/me/account
 * endpoint — the two routes app-store review asks for.
 */
class AccountDeletionPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_page_is_public_and_explains_the_journey(): void
    {
        $this->get('/delete-account')
            ->assertOk()
            ->assertSee(__('pages.delete_account.hero_title'))
            ->assertSee(__('pages.delete_account.app_title'))
            ->assertSee(__('pages.delete_account.data_title'))
            ->assertSee('support@harmoniva.app', false)
            // Signed out: the sign-in route, not a delete button.
            ->assertSee(__('pages.delete_account.web_sign_in'));
    }

    public function test_a_signed_in_visitor_can_delete_straight_from_the_page(): void
    {
        $user = User::factory()->create(['password' => Hash::make('password')]);

        $this->actingAs($user)->get('/delete-account')
            ->assertOk()
            ->assertSee($user->email)
            ->assertSee(route('profile.destroy'), false);

        $this->actingAs($user)
            ->from('/delete-account')
            ->delete(route('profile.destroy'), ['password' => 'password'])
            ->assertRedirect(route('login', absolute: false));

        $this->assertSoftDeleted('users', ['id' => $user->id]);
    }

    public function test_a_failed_confirmation_returns_to_the_page_it_came_from(): void
    {
        $user = User::factory()->create(['password' => null, 'google_id' => 'g-1']);

        $this->actingAs($user)
            ->from('/delete-account')
            ->delete(route('profile.destroy'), ['confirm_email' => 'nope@example.com'])
            ->assertRedirect('/delete-account')
            ->assertSessionHasErrorsIn('userDeletion', 'confirm_email');

        $this->assertNull($user->fresh()->deleted_at);
    }

    public function test_the_page_is_localized(): void
    {
        $this->get('/de/delete-account')
            ->assertOk()
            ->assertSee(__('pages.delete_account.hero_title', [], 'de'))
            ->assertSee('<html lang="de">', false);
    }

    // ── Mobile API ────────────────────────────────────────────────────────

    public function test_the_app_can_delete_the_account_over_the_api(): void
    {
        $user = User::factory()->create(['password' => Hash::make('password')]);
        $token = $user->createToken('mobile')->plainTextToken;
        $this->assertDatabaseCount('personal_access_tokens', 1);

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->deleteJson('/api/v1/me/account', ['password' => 'password', 'reason' => 'Done learning'])
            ->assertOk()
            ->assertJsonPath('data.status', 'deleted');

        $this->assertSoftDeleted('users', ['id' => $user->id]);
        $this->assertSame('Done learning', $user->fresh()->deletion_reason);

        // Every token died with the account, so the app cannot call anything
        // else with the credentials it still holds.
        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_the_api_refuses_a_wrong_password(): void
    {
        $user = User::factory()->create(['password' => Hash::make('password')]);

        $this->actingAs($user, 'sanctum')
            ->deleteJson('/api/v1/me/account', ['password' => 'wrong'])
            ->assertStatus(422)
            ->assertJsonValidationErrors('password');

        $this->assertNull($user->fresh()->deleted_at);
    }

    public function test_the_api_confirms_google_accounts_with_their_email(): void
    {
        $user = User::factory()->create(['password' => null, 'google_id' => 'g-2']);

        $this->actingAs($user, 'sanctum')
            ->deleteJson('/api/v1/me/account', ['confirm_email' => 'other@example.com'])
            ->assertStatus(422)
            ->assertJsonValidationErrors('confirm_email');

        $this->actingAs($user, 'sanctum')
            ->deleteJson('/api/v1/me/account', ['confirm_email' => $user->email])
            ->assertOk();

        $this->assertSoftDeleted('users', ['id' => $user->id]);
    }

    /**
     * The app has to know which confirmation to ask for before it draws the
     * screen — otherwise a Google account is shown a password field it can
     * never satisfy, and only learns better from a failed request.
     */
    public function test_the_user_payload_says_whether_the_account_has_a_password(): void
    {
        $withPassword = User::factory()->create(['password' => Hash::make('password')]);

        $this->actingAs($withPassword, 'sanctum')
            ->getJson('/api/v1/auth/me')
            ->assertOk()
            ->assertJsonPath('data.has_password', true);

        $google = User::factory()->create(['password' => null, 'google_id' => 'g-3']);

        $this->actingAs($google, 'sanctum')
            ->getJson('/api/v1/auth/me')
            ->assertOk()
            ->assertJsonPath('data.has_password', false);
    }

    public function test_the_api_endpoint_requires_authentication(): void
    {
        $this->deleteJson('/api/v1/me/account')->assertUnauthorized();
    }
}
