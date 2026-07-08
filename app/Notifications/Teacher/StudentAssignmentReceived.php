<?php

namespace App\Notifications\Teacher;

use App\Models\TeacherAssignment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/** Sent to the student when a teacher assigns them homework. */
class StudentAssignmentReceived extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public TeacherAssignment $assignment) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $teacher = $this->assignment->teacher;
        $teacherName = trim($teacher->name.' '.$teacher->surname);

        $mail = (new MailMessage)
            ->subject('New homework from '.$teacherName)
            ->line($teacherName.' assigned you new homework: **'.$this->assignment->title.'**');

        if ($this->assignment->due_at) {
            $mail->line('Due date: '.$this->assignment->due_at->format('F j, Y H:i'));
        }

        return $mail->action('Open your assignments', route('assignments.index'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'student_assignment_received',
            'assignment_id' => $this->assignment->id,
            'title' => $this->assignment->title,
            'due_at' => $this->assignment->due_at?->toIso8601String(),
            'url' => route('assignments.index'),
        ];
    }
}
