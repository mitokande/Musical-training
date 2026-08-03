<?php

namespace App\Livewire;

use App\Models\User;
use App\Notifications\UserFollowed;
use Livewire\Component;

class FollowButton extends Component
{
    public User $user;

    public bool $isFollowing = false;

    /**
     * Tailwind fill+hover classes for the "Follow" state, matching the avatar
     * colour code: Teacher → purple, Student → pink; Premium → base tone,
     * Free → 1-2 tones lighter.
     */
    public string $followColor = 'bg-pink-300 hover:bg-pink-400';

    public function mount(User $user): void
    {
        $this->user = $user;
        $this->isFollowing = auth()->check() && auth()->user()->isFollowing($user);

        $tp = $user->teacherProfile; // lazy-loads once, then cached on the model
        $isTeacher = $user->isTeacher() || $tp !== null;
        $isPremium = $isTeacher ? ($tp?->tier === 'premium') : $user->isPremium();

        $this->followColor = match (true) {
            $isTeacher && $isPremium => 'bg-purple-600 hover:bg-purple-700',
            $isTeacher => 'bg-purple-400 hover:bg-purple-500',
            $isPremium => 'bg-pink-500 hover:bg-pink-600',
            default => 'bg-pink-300 hover:bg-pink-400',
        };
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
            $this->user->notify(new UserFollowed($me));
        }

        $this->dispatch('follow-changed', userId: $this->user->id, following: $this->isFollowing);
    }

    public function render()
    {
        return view('livewire.follow-button');
    }
}
