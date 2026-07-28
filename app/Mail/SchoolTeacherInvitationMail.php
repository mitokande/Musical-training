<?php

namespace App\Mail;

use App\Models\SchoolTeacherInvitation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SchoolTeacherInvitationMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public SchoolTeacherInvitation $invitation) {}

    protected function schoolName(): string
    {
        $school = $this->invitation->school;

        return $school->school?->name ?: $school->fullName();
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('notifications.invite.school_subject', ['name' => $this->schoolName()]),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'email.school-teacher-invitation',
            with: [
                'invitation' => $this->invitation,
                'schoolName' => $this->schoolName(),
                'acceptUrl' => $this->invitation->acceptUrl(),
            ],
        );
    }
}
