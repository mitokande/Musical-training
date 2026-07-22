<?php

namespace App\Notifications\School;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/** Sent to the teacher when a school requests a membership. */
class SchoolTeacherRelationshipRequested extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public User $school) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $schoolName = $this->school->school?->name ?: $this->school->fullName();

        return (new MailMessage)
            ->subject($schoolName.' wants to add you to their teaching staff on Harmoniva')
            ->line($schoolName.' would like to add you as a teacher of their music school on Harmoniva.')
            ->line('As a member teacher you get the full teacher toolset (students, classes, assignments, calendar) and the school can support you in managing your students.')
            ->action('Review the request', route('my-schools.index'))
            ->line('You can decline the request if you do not know this school.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'school_teacher_relationship_requested',
            'school_id' => $this->school->id,
            'school_name' => $this->school->school?->name ?: $this->school->fullName(),
            'url' => route('my-schools.index'),
        ];
    }
}
