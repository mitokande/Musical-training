<?php

namespace App\Notifications\Teacher;

use App\Models\TeacherProfile;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TeacherProfileSuspended extends Notification implements ShouldQueue
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
            ->subject('Your teacher profile has been suspended')
            ->line('Your Harmoniva teacher profile has been suspended and is no longer publicly visible.');

        if ($this->reason) {
            $mail->line('Reason: '.$this->reason);
        }

        return $mail->line('Please contact support if you believe this is a mistake.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'teacher_profile_suspended',
            'teacher_profile_id' => $this->profile->id,
            'reason' => $this->reason,
        ];
    }
}
