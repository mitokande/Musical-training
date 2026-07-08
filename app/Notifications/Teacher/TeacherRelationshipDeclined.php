<?php

namespace App\Notifications\Teacher;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/** Sent to the teacher when a student declines the relationship request. */
class TeacherRelationshipDeclined extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public User $student) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $studentName = trim($this->student->name.' '.$this->student->surname);

        return (new MailMessage)
            ->subject('Your student request was declined')
            ->line($studentName.' declined your relationship request on Harmoniva.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'teacher_relationship_declined',
            'student_id' => $this->student->id,
            'student_name' => trim($this->student->name.' '.$this->student->surname),
        ];
    }
}
