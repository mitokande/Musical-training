<?php

namespace App\Notifications\School;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/** Sent to the school when a teacher joins (accepts request or invitation). */
class SchoolTeacherRelationshipAccepted extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public User $teacher) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $teacherName = $this->teacher->fullName();

        return (new MailMessage)
            ->subject($teacherName.' joined your school on Harmoniva')
            ->line($teacherName.' accepted your invitation and is now a member teacher of your school.')
            ->action('Open your teacher list', route('school.teachers.index'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'school_teacher_relationship_accepted',
            'teacher_id' => $this->teacher->id,
            'teacher_name' => $this->teacher->fullName(),
            'url' => route('school.teachers.index'),
        ];
    }
}
