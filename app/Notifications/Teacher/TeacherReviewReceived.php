<?php

namespace App\Notifications\Teacher;

use App\Models\TeacherReview;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/** Sent to the teacher when a student leaves a public review. */
class TeacherReviewReceived extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public TeacherReview $review) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $student = $this->review->student;
        $studentName = trim($student->name.' '.$student->surname);

        return (new MailMessage)
            ->subject('New review on your teacher profile ⭐')
            ->line($studentName.' left a '.$this->review->rating.'-star review on your Harmoniva profile.')
            ->action('View your public profile', $this->review->teacherProfile->publicUrl());
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'teacher_review_received',
            'review_id' => $this->review->id,
            'rating' => $this->review->rating,
            'student_id' => $this->review->student_id,
        ];
    }
}
