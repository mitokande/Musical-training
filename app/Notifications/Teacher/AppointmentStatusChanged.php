<?php

namespace App\Notifications\Teacher;

use App\Models\TeacherAppointment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/** Sent to the other party after any meaningful appointment status change. */
class AppointmentStatusChanged extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public TeacherAppointment $appointment) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $isStudent = $notifiable->id === $this->appointment->student_id;
        $when = $this->appointment->starts_at
            ->timezone($this->appointment->timezone ?? config('app.timezone'))
            ->format('F j, Y H:i');

        $statusLines = [
            TeacherAppointment::STATUS_CONFIRMED => 'Your lesson on '.$when.' is confirmed.',
            TeacherAppointment::STATUS_REJECTED => 'The appointment request for '.$when.' was declined.',
            TeacherAppointment::STATUS_CANCELLED_BY_TEACHER => 'The lesson on '.$when.' was cancelled by the teacher.',
            TeacherAppointment::STATUS_CANCELLED_BY_STUDENT => 'The lesson on '.$when.' was cancelled by the student.',
            TeacherAppointment::STATUS_RESCHEDULE_REQUESTED => 'A new time was requested for the lesson on '.$when.'.',
            TeacherAppointment::STATUS_COMPLETED => 'The lesson on '.$when.' was marked as completed.',
            TeacherAppointment::STATUS_NO_SHOW => 'The lesson on '.$when.' was marked as a no-show.',
        ];

        $mail = (new MailMessage)
            ->subject('Appointment update — '.$when)
            ->line($statusLines[$this->appointment->status] ?? 'Your appointment status changed.');

        if ($this->appointment->isConfirmed() && $this->appointment->meeting_url) {
            $mail->line('Lesson link: '.$this->appointment->meeting_url);
        }

        return $mail->action(
            'View appointment',
            $isStudent ? route('my-appointments.index') : route('teacher.calendar.index'),
        );
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'appointment_status_changed',
            'appointment_id' => $this->appointment->id,
            'status' => $this->appointment->status,
            'starts_at' => $this->appointment->starts_at->toIso8601String(),
        ];
    }
}
