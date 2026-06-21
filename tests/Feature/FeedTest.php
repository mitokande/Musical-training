<?php

namespace Tests\Feature;

use App\Livewire\SocialFeed;
use App\Models\FeedItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class FeedTest extends TestCase
{
    use RefreshDatabase;

    public function test_feed_page_loads(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/feed')->assertOk();
    }

    public function test_user_can_create_a_post(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Livewire::test(SocialFeed::class)
            ->set('postBody', 'Hello community')
            ->call('post')
            ->assertSet('postBody', '');

        $this->assertDatabaseHas('feed_items', [
            'user_id' => $user->id,
            'type' => 'post',
            'body' => 'Hello community',
        ]);
    }

    public function test_following_scope_shows_followed_users_posts_but_not_strangers(): void
    {
        $me = User::factory()->create();
        $friend = User::factory()->create();
        $stranger = User::factory()->create();

        $me->follow($friend);

        FeedItem::recordPost($friend, 'Friend post here');
        FeedItem::recordPost($stranger, 'Stranger post here');

        $this->actingAs($me);

        Livewire::test(SocialFeed::class)
            ->call('setScope', 'following')
            ->assertSee('Friend post here')
            ->assertDontSee('Stranger post here');
    }

    public function test_default_feed_scope_shows_all_events_globally(): void
    {
        $me = User::factory()->create();
        $stranger = User::factory()->create();

        FeedItem::recordPost($stranger, 'Stranger post here');

        $this->actingAs($me);

        // Default scope is the global "feed" — strangers' posts are visible.
        Livewire::test(SocialFeed::class)
            ->assertSet('scope', 'feed')
            ->assertSee('Stranger post here');
    }

    public function test_new_member_items_are_global(): void
    {
        $me = User::factory()->create();
        $newcomer = User::factory()->create(['name' => 'Brand Newperson']);

        $this->actingAs($me);

        // new_member feed item is auto-created on registration and visible to everyone
        Livewire::test(SocialFeed::class)
            ->assertSee('Brand Newperson');
    }

    public function test_like_toggle(): void
    {
        $me = User::factory()->create();
        $item = FeedItem::recordPost($me, 'Likeable');

        $this->actingAs($me);

        Livewire::test(SocialFeed::class)
            ->call('toggleLike', $item->id);
        $this->assertDatabaseHas('feed_likes', ['feed_item_id' => $item->id, 'user_id' => $me->id]);

        Livewire::test(SocialFeed::class)
            ->call('toggleLike', $item->id);
        $this->assertDatabaseMissing('feed_likes', ['feed_item_id' => $item->id, 'user_id' => $me->id]);
    }

    public function test_owner_can_delete_post(): void
    {
        $me = User::factory()->create();
        $item = FeedItem::recordPost($me, 'Delete me');

        $this->actingAs($me);

        Livewire::test(SocialFeed::class)->call('deletePost', $item->id);

        $this->assertDatabaseMissing('feed_items', ['id' => $item->id]);
    }

    public function test_new_member_feed_item_created_on_registration(): void
    {
        $user = User::factory()->create();

        $this->assertDatabaseHas('feed_items', [
            'user_id' => $user->id,
            'type' => 'new_member',
        ]);
    }
}
