<?php

namespace App\Livewire;

use App\Models\User;
use Livewire\Component;

class FollowButton extends Component
{
    public User $user;

    public bool $isFollowing = false;

    public function mount(User $user): void
    {
        $this->user = $user;
        $this->isFollowing = auth()->check() && auth()->user()->isFollowing($user);
    }

    public function toggleFollow(): void
    {
        $me = auth()->user();

        if (! $me || $me->id === $this->user->id) {
            return;
        }

        if ($this->isFollowing) {
            $me->unfollow($this->user);
            $this->isFollowing = false;
        } else {
            $me->follow($this->user);
            $this->isFollowing = true;
        }

        $this->dispatch('follow-changed', userId: $this->user->id, following: $this->isFollowing);
    }

    public function render()
    {
        return view('livewire.follow-button');
    }
}
