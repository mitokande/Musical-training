<?php

namespace App\Notifications\Teacher;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/** Sent to the teacher when a student accepts the relationship. */
class TeacherRelationshipAccepted extends Notification implements ShouldQueue
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
            ->subject($studentName.' is now your student 🎉')
            ->line($studentName.' accepted your request and is now connected to you on Harmoniva.')
            ->action('Open your student list', route('teacher.students.index'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'teacher_relationship_accepted',
            'student_id' => $this->student->id,
            'student_name' => trim($this->student->name.' '.$this->student->surname),
            'url' => route('teacher.students.index'),
        ];
    }
}
