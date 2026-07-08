<?php

namespace App\Notifications\Teacher;

use App\Models\TeacherProfile;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TeacherProfileApproved extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public TeacherProfile $profile) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your teacher profile is approved 🎉')
            ->line('Great news — your Harmoniva teacher profile has been approved and is now publicly visible.')
            ->action('View your public profile', $this->profile->publicUrl());
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'teacher_profile_approved',
            'teacher_profile_id' => $this->profile->id,
            'url' => $this->profile->publicUrl(),
        ];
    }
}
