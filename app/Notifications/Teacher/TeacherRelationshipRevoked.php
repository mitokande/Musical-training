<?php

namespace App\Notifications\Teacher;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/** Sent to the other party when a teacher-student relationship is revoked. */
class TeacherRelationshipRevoked extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public User $otherParty, public bool $byTeacher) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $name = trim($this->otherParty->name.' '.$this->otherParty->surname);

        $line = $this->byTeacher
            ? $name.' has ended your teacher-student connection on Harmoniva.'
            : $name.' has ended their student connection with you on Harmoniva.';

        return (new MailMessage)
            ->subject('A teacher-student connection was ended')
            ->line($line);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'teacher_relationship_revoked',
            'by_teacher' => $this->byTeacher,
            'other_party_id' => $this->otherParty->id,
            'other_party_name' => trim($this->otherParty->name.' '.$this->otherParty->surname),
        ];
    }
}
