<?php

namespace Tests\Feature;

use App\Livewire\FollowButton;
use App\Models\User;
use App\Notifications\UserFollowed;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class NotificationCenterTest extends TestCase
{
    use RefreshDatabase;

    public function test_notifications_page_loads_for_authenticated_user(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/notifications')
            ->assertOk()
            ->assertSee(__('app.notifications.title'));
    }

    public function test_following_a_user_creates_a_database_notification(): void
    {
        $follower = User::factory()->create(['name' => 'Ada']);
        $target = User::factory()->create();

        $this->actingAs($follower);

        Livewire::test(FollowButton::class, ['user' => $target])
            ->call('toggleFollow');

        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $target->id,
            'type' => UserFollowed::class,
        ]);
        $this->assertSame(1, $target->fresh()->unreadNotifications()->count());
    }

    public function test_visiting_the_centre_marks_notifications_read(): void
    {
        $follower = User::factory()->create();
        $target = User::factory()->create();

        $target->notify(new UserFollowed($follower));
        $this->assertSame(1, $target->unreadNotifications()->count());

        $this->actingAs($target)->get('/notifications')->assertOk();

        $this->assertSame(0, $target->fresh()->unreadNotifications()->count());
    }
}
