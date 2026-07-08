<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
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

    // Account deletion was replaced by suspend/reactivate
    // (ProfileController::toggleSuspend); there is no DELETE /profile route.

    public function test_user_can_suspend_their_account(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->post('/profile/suspend');

        $response
            ->assertRedirect(route('profile.edit', absolute: false))
            ->assertSessionHas('status', 'account-suspended');

        $this->assertNotNull($user->refresh()->suspended_at);
    }

    public function test_suspended_user_can_reactivate_their_account(): void
    {
        $user = User::factory()->create(['suspended_at' => now()]);

        $response = $this
            ->actingAs($user)
            ->post('/profile/suspend');

        $response
            ->assertRedirect(route('profile.edit', absolute: false))
            ->assertSessionHas('status', 'account-activated');

        $this->assertNull($user->refresh()->suspended_at);
    }
}
