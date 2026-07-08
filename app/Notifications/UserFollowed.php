<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

/** Sent to a user when another user starts following them (social graph). */
class UserFollowed extends Notification
{
    use Queueable;

    public function __construct(public User $follower) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'user_followed',
            'follower_id' => $this->follower->id,
            'follower_name' => trim($this->follower->name.' '.$this->follower->surname),
            'url' => $this->follower->username ? url('/u/'.$this->follower->username) : url('/feed'),
        ];
    }
}
