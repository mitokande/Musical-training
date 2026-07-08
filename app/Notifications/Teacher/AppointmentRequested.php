<?php

namespace App\Notifications\Teacher;

use App\Models\TeacherAppointment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/** Sent to the teacher when a student requests an appointment. */
class AppointmentRequested extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public TeacherAppointment $appointment) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $student = $this->appointment->student;
        $studentName = trim($student->name.' '.$student->surname);

        return (new MailMessage)
            ->subject('New appointment request from '.$studentName)
            ->line($studentName.' requested a lesson on '.$this->appointment->starts_at
                ->timezone($this->appointment->timezone ?? config('app.timezone'))
                ->format('F j, Y H:i').'.')
            ->line($this->appointment->topic ? 'Topic: '.$this->appointment->topic : '')
            ->action('Review the request', route('teacher.calendar.index'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'appointment_requested',
            'appointment_id' => $this->appointment->id,
            'student_id' => $this->appointment->student_id,
            'starts_at' => $this->appointment->starts_at->toIso8601String(),
            'url' => route('teacher.calendar.index'),
        ];
    }
}
