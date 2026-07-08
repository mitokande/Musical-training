<?php

namespace App\Notifications\Teacher;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/** Sent to the student when a teacher requests a relationship. */
class TeacherRelationshipRequested extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public User $teacher) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $teacherName = trim($this->teacher->name.' '.$this->teacher->surname);

        return (new MailMessage)
            ->subject($teacherName.' wants to add you as a student on Harmoniva')
            ->line($teacherName.' would like to connect with you as their student on Harmoniva.')
            ->line('Once you approve, your teacher can follow your practice progress and assign you homework.')
            ->action('Review the request', route('my-teachers.index'))
            ->line('You can decline the request if you do not know this teacher.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'teacher_relationship_requested',
            'teacher_id' => $this->teacher->id,
            'teacher_name' => trim($this->teacher->name.' '.$this->teacher->surname),
            'url' => route('my-teachers.index'),
        ];
    }
}
