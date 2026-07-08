<?php

namespace App\Mail;

use App\Models\TeacherStudentInvitation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TeacherStudentInvitationMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public TeacherStudentInvitation $invitation) {}

    public function envelope(): Envelope
    {
        $teacher = $this->invitation->teacher;

        return new Envelope(
            subject: trim($teacher->name.' '.$teacher->surname).' invited you to Harmoniva',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'email.teacher-student-invitation',
            with: [
                'invitation' => $this->invitation,
                'teacherName' => trim($this->invitation->teacher->name.' '.$this->invitation->teacher->surname),
                'acceptUrl' => $this->invitation->acceptUrl(),
            ],
        );
    }
}
