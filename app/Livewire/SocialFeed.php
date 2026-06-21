<?php

namespace App\Livewire;

use App\Models\FeedItem;
use App\Models\FeedLike;
use Livewire\Component;
use Livewire\WithPagination;

class SocialFeed extends Component
{
    use WithPagination;

    public string $postBody = '';

    /** 'feed' = all events globally (default), 'following' = only users I follow. */
    public string $scope = 'feed';

    public function setScope(string $scope): void
    {
        $this->scope = $scope === 'following' ? 'following' : 'feed';
        $this->resetPage();
    }

    public function post(): void
    {
        $this->validate([
            'postBody' => 'required|string|max:1000',
        ]);

        FeedItem::recordPost(auth()->user(), $this->postBody);

        $this->postBody = '';
        $this->resetPage();
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
        $query = FeedItem::with(['actor', 'subject'])->withCount('likes');

        if ($this->scope === 'following') {
            // Only events from users I follow (plus my own activity).
            $followingIds = auth()->user()->following()->pluck('users.id')->push(auth()->id());
            $query->whereIn('user_id', $followingIds);
        }
        // 'feed' (default) shows every event globally — no actor filter.

        $items = $query->latest()->paginate(15);

        $likedIds = FeedLike::where('user_id', auth()->id())
            ->whereIn('feed_item_id', $items->pluck('id'))
            ->pluck('feed_item_id')
            ->all();

        return view('livewire.social-feed', [
            'items' => $items,
            'likedIds' => $likedIds,
        ]);
    }
}
