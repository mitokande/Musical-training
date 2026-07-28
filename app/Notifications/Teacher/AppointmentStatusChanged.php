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

        $statusKeys = [
            TeacherAppointment::STATUS_CONFIRMED => 'confirmed',
            TeacherAppointment::STATUS_REJECTED => 'rejected',
            TeacherAppointment::STATUS_CANCELLED_BY_TEACHER => 'cancelled_teacher',
            TeacherAppointment::STATUS_CANCELLED_BY_STUDENT => 'cancelled_student',
            TeacherAppointment::STATUS_RESCHEDULE_REQUESTED => 'reschedule',
            TeacherAppointment::STATUS_COMPLETED => 'completed',
            TeacherAppointment::STATUS_NO_SHOW => 'no_show',
        ];
        $key = $statusKeys[$this->appointment->status] ?? 'default';

        $mail = (new MailMessage)
            ->subject(__('notifications.appointment.status_subject', ['when' => $when]))
            ->line(__('notifications.appointment.status.'.$key, ['when' => $when]));

        if ($this->appointment->isConfirmed() && $this->appointment->meeting_url) {
            $mail->line(__('notifications.appointment.lesson_link', ['url' => $this->appointment->meeting_url]));
        }

        return $mail->action(
            __('notifications.appointment.view'),
            $isStudent ? route('my-appointments.index') : route($notifiable->crmRouteName('calendar.index')),
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
