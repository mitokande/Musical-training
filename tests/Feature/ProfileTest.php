<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_page_is_displayed(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get('/profile');

        $response->assertOk();
    }

    public function test_profile_information_can_be_updated(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => 'Test User',
                'email' => 'test@example.com',
            ]);

        $response
            ->assertSessionHasNoErrors()
            // ProfileController::update returns to the general tab of the
            // tabbed profile page.
            ->assertRedirect('/profile?tab=general');

        $user->refresh();

        $this->assertSame('Test User', $user->name);
        $this->assertSame('test@example.com', $user->email);
        $this->assertNull($user->email_verified_at);
    }

    public function test_email_verification_status_is_unchanged_when_the_email_address_is_unchanged(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => 'Test User',
                'email' => $user->email,
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile?tab=general');

        $this->assertNotNull($user->refresh()->email_verified_at);
    }

    /**
     * Self-service deletion is a one-way door for the member: signed out,
     * unable to log back in, e-mail released. The row itself survives as a
     * soft delete for the admin panel (see AccountDeletionTest).
     */
    public function test_user_can_delete_their_own_account(): void
    {
        $user = User::factory()->create(['password' => Hash::make('password')]);

        $response = $this
            ->actingAs($user)
            ->delete('/profile', ['password' => 'password']);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('login', absolute: false));

        $this->assertGuest();
        $this->assertSoftDeleted('users', ['id' => $user->id]);
    }

    public function test_correct_password_must_be_provided_to_delete_account(): void
    {
        $user = User::factory()->create(['password' => Hash::make('password')]);

        $response = $this
            ->actingAs($user)
            ->from('/profile')
            ->delete('/profile', ['password' => 'wrong-password']);

        $response
            ->assertSessionHasErrorsIn('userDeletion', 'password')
            ->assertRedirect('/profile');

        $this->assertAuthenticated();
        $this->assertNull($user->fresh()->deleted_at);
    }

    /**
     * Google-only accounts have no password to confirm with, so they confirm
     * by typing their own address.
     */
    public function test_passwordless_account_confirms_deletion_with_its_email(): void
    {
        $user = User::factory()->create(['password' => null, 'google_id' => 'g-123']);

        $this->actingAs($user)
            ->from('/profile')
            ->delete('/profile', ['confirm_email' => 'someone-else@example.com'])
            ->assertSessionHasErrorsIn('userDeletion', 'confirm_email');

        $this->assertNull($user->fresh()->deleted_at);

        $this->actingAs($user)
            ->delete('/profile', ['confirm_email' => strtoupper($user->email)])
            ->assertRedirect(route('login', absolute: false));

        $this->assertSoftDeleted('users', ['id' => $user->id]);
    }
}
