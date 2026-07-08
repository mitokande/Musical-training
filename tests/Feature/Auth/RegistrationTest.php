<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_new_users_can_register(): void
    {
        // Registration requires role + surname, does not auto-login, and sends
        // the new user to the email-verification notice first
        // (RegisteredUserController::store).
        $response = $this->post('/register', [
            'role' => 'user',
            'name' => 'Test',
            'surname' => 'User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('verification.notice', absolute: false));
        $this->assertGuest();

        $user = User::where('email', 'test@example.com')->first();
        $this->assertNotNull($user);
        $this->assertSame('user', $user->role);
        $this->assertSame('free', $user->plan);
        $this->assertNotEmpty($user->username);
    }
}
