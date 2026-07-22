<?php

namespace App\Notifications\Teacher;

use App\Models\TeacherAssignment;
use App\Models\TeacherAssignmentAttempt;
use App\Models\TeacherAssignmentRecipient;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/** Sent to the teacher when a student completes an assignment attempt. */
class StudentAssignmentCompleted extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public TeacherAssignment $assignment,
        public TeacherAssignmentRecipient $recipient,
        public TeacherAssignmentAttempt $attempt,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $student = $this->recipient->student;
        $studentName = trim($student->name.' '.$student->surname);

        return (new MailMessage)
            ->subject($studentName.' completed "'.$this->assignment->title.'"')
            ->line($studentName.' completed the assignment **'.$this->assignment->title.'** with a score of '.number_format((float) $this->attempt->score, 1).'%.')
            ->action('View the results', route($notifiable->crmRouteName('assignments.show'), $this->assignment));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'student_assignment_completed',
            'assignment_id' => $this->assignment->id,
            'title' => $this->assignment->title,
            'student_id' => $this->recipient->student_id,
            'score' => (float) $this->attempt->score,
            'url' => route($notifiable->crmRouteName('assignments.show'), $this->assignment),
        ];
    }
}
