<?php

namespace App\Livewire;

use App\Models\FeedItem;
use App\Models\FeedLike;
use Livewire\Component;

class SocialFeed extends Component
{
    public string $postBody = '';

    /** 'feed' = all events globally (default), 'following' = only users I follow. */
    public string $scope = 'feed';

    /** How many items are currently shown; grows by loadMore() (infinite scroll / "Show more"). */
    public int $perPage = 15;

    public function setScope(string $scope): void
    {
        $this->scope = $scope === 'following' ? 'following' : 'feed';
        $this->perPage = 15;
    }

    public function loadMore(): void
    {
        $this->perPage += 15;
    }

    public function post(): void
    {
        $this->validate([
            'postBody' => 'required|string|max:1000',
        ]);

        FeedItem::recordPost(auth()->user(), $this->postBody);

        $this->postBody = '';
        $this->perPage = 15;
        $this->dispatch('post-shared');
    }

    public function toggleLike(int $feedItemId): void
    {
        $like = FeedLike::where('feed_item_id', $feedItemId)
            ->where('user_id', auth()->id())
            ->first();

        if ($like) {
            $like->delete();
        } else {
            FeedLike::create([
                'feed_item_id' => $feedItemId,
                'user_id' => auth()->id(),
            ]);
        }
    }

    public function deletePost(int $feedItemId): void
    {
        FeedItem::where('id', $feedItemId)
            ->where('user_id', auth()->id())
            ->where('type', 'post')
            ->delete();
    }

    public function render()
    {
        // whereHas('actor') leans on the users soft-delete scope: a deleted
        // account's posts and activity drop out of the feed for everyone.
        $query = FeedItem::with(['actor', 'subject'])
            ->whereHas('actor')
            ->withCount('likes');

        if ($this->scope === 'following') {
            // Only events from users I follow (plus my own activity).
            $followingIds = auth()->user()->following()->pluck('users.id')->push(auth()->id());
            $query->whereIn('user_id', $followingIds);
        }
        // 'feed' (default) shows every event globally — no actor filter.

        // Fetch one extra row to know whether more pages exist, then trim to $perPage.
        $items = $query->latest()->take($this->perPage + 1)->get();
        $hasMore = $items->count() > $this->perPage;
        $items = $items->take($this->perPage);

        $likedIds = FeedLike::where('user_id', auth()->id())
            ->whereIn('feed_item_id', $items->pluck('id'))
            ->pluck('feed_item_id')
            ->all();

        return view('livewire.social-feed', [
            'items' => $items,
            'likedIds' => $likedIds,
            'hasMore' => $hasMore,
        ]);
    }
}
