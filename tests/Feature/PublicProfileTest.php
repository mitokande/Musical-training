<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_profile_loads_with_follow_and_message_buttons(): void
    {
        $viewer = User::factory()->create();
        $target = User::factory()->create(['name' => 'Profile Owner']);

        $response = $this->actingAs($viewer)->get('/u/'.$target->username);

        $response->assertOk()
            ->assertSee('Profile Owner')
            ->assertSee(__('app.social.follow'))
            ->assertSee(__('app.social.message'));
    }

    public function test_unknown_username_returns_404(): void
    {
        $viewer = User::factory()->create();

        $this->actingAs($viewer)->get('/u/nobody-here')->assertNotFound();
    }
}
