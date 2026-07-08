<?php

namespace App\Notifications\Teacher;

use App\Models\TeacherStudentReward;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/** Sent to the student when their teacher gives them a reward or sticker. */
class StudentRewardReceived extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public User $teacher, public TeacherStudentReward $reward) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $teacherName = trim($this->teacher->name.' '.$this->teacher->surname);

        $mail = (new MailMessage)
            ->subject('You earned a reward on Harmoniva! 🌟')
            ->line($teacherName.' gave you a reward: **'.$this->reward->label.'**');

        if ($this->reward->note) {
            $mail->line('"'.$this->reward->note.'"');
        }

        return $mail->line('Keep up the great work!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'student_reward_received',
            'teacher_id' => $this->teacher->id,
            'teacher_name' => trim($this->teacher->name.' '.$this->teacher->surname),
            'reward_id' => $this->reward->id,
            'label' => $this->reward->label,
            'reward_type' => $this->reward->type,
        ];
    }
}
