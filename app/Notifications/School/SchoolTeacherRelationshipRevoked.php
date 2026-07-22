<?php

namespace App\Notifications\School;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/** Sent to the other party when a school-teacher membership is ended. */
class SchoolTeacherRelationshipRevoked extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public User $otherParty, public bool $bySchool) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $name = $this->otherParty->isSchool()
            ? ($this->otherParty->school?->name ?: $this->otherParty->fullName())
            : $this->otherParty->fullName();

        $line = $this->bySchool
            ? $name.' has removed you from their teaching staff on Harmoniva.'
            : $name.' has left your school on Harmoniva.';

        return (new MailMessage)
            ->subject('A school membership was ended')
            ->line($line);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'school_teacher_relationship_revoked',
            'by_school' => $this->bySchool,
            'other_party_id' => $this->otherParty->id,
            'other_party_name' => $this->otherParty->fullName(),
        ];
    }
}
