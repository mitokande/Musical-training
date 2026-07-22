<?php

namespace App\Notifications\Teacher;

use App\Models\TeacherProfile;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TeacherProfileRejected extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public TeacherProfile $profile, public ?string $reason = null) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject('Your teacher profile needs changes')
            ->line('Your Harmoniva teacher profile was reviewed and requires changes before it can be published.');

        if ($this->reason) {
            $mail->line('Reviewer notes: '.$this->reason);
        }

        return $mail->action('Edit your profile', route($notifiable->crmRouteName('profile.edit')));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'teacher_profile_rejected',
            'teacher_profile_id' => $this->profile->id,
            'reason' => $this->reason,
            'url' => route($notifiable->crmRouteName('profile.edit')),
        ];
    }
}
