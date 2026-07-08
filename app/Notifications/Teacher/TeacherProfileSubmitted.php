<?php

namespace App\Notifications\Teacher;

use App\Models\TeacherProfile;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/** Sent to admins when a teacher submits their profile for review. */
class TeacherProfileSubmitted extends Notification implements ShouldQueue
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
            ->subject('Teacher profile submitted for review — '.$this->profile->displayName())
            ->line($this->profile->displayName().' submitted their teacher profile for review.')
            ->action('Review profile', route('admin.teacher-profiles.show', $this->profile));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'teacher_profile_submitted',
            'teacher_profile_id' => $this->profile->id,
            'teacher_name' => $this->profile->displayName(),
            'url' => route('admin.teacher-profiles.show', $this->profile),
        ];
    }
}
