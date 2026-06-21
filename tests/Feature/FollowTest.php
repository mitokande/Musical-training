<?php

namespace Tests\Feature;

use App\Livewire\FollowButton;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class FollowTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_follow_and_unfollow(): void
    {
        $a = User::factory()->create();
        $b = User::factory()->create();

        $a->follow($b);

        $this->assertTrue($a->fresh()->isFollowing($b));
        $this->assertDatabaseHas('follows', ['follower_id' => $a->id, 'followed_id' => $b->id]);
        $this->assertSame(1, $b->followersCount());

        $a->unfollow($b);

        $this->assertFalse($a->fresh()->isFollowing($b));
        $this->assertDatabaseMissing('follows', ['follower_id' => $a->id, 'followed_id' => $b->id]);
    }

    public function test_following_records_a_feed_item(): void
    {
        $a = User::factory()->create();
        $b = User::factory()->create();

        $a->follow($b);

        $this->assertDatabaseHas('feed_items', [
            'user_id' => $a->id,
            'type' => 'follow',
            'subject_id' => $b->id,
        ]);
    }

    public function test_you_cannot_follow_yourself(): void
    {
        $a = User::factory()->create();
        $a->follow($a);

        $this->assertDatabaseMissing('follows', ['follower_id' => $a->id, 'followed_id' => $a->id]);
    }

    public function test_follow_button_toggles(): void
    {
        $a = User::factory()->create();
        $b = User::factory()->create();

        $this->actingAs($a);

        Livewire::test(FollowButton::class, ['user' => $b])
            ->assertSet('isFollowing', false)
            ->call('toggleFollow')
            ->assertSet('isFollowing', true)
            ->call('toggleFollow')
            ->assertSet('isFollowing', false);
    }
}
