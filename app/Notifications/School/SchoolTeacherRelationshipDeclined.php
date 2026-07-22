<?php

namespace App\Notifications\School;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/** Sent to the school when a teacher declines the membership request. */
class SchoolTeacherRelationshipDeclined extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public User $teacher) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject($this->teacher->fullName().' declined your school membership request')
            ->line($this->teacher->fullName().' declined your request to join your school on Harmoniva.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'school_teacher_relationship_declined',
            'teacher_id' => $this->teacher->id,
            'teacher_name' => $this->teacher->fullName(),
        ];
    }
}
