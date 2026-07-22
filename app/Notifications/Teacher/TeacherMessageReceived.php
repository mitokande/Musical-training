<?php

namespace App\Notifications\Teacher;

use App\Models\TeacherConversation;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/** Sent to the other party when a teacher-student message arrives. */
class TeacherMessageReceived extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public TeacherConversation $conversation, public User $sender) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $senderName = trim($this->sender->name.' '.$this->sender->surname);
        $isStudentRecipient = $notifiable->id === $this->conversation->student_id;

        $url = $isStudentRecipient
            ? route('teacher-messages.show', $this->conversation)
            : route($notifiable->crmRouteName('messages.show'), $this->conversation);

        return (new MailMessage)
            ->subject('New message from '.$senderName)
            ->line($senderName.' sent you a message on Harmoniva.')
            ->action('Read the message', $url);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'teacher_message_received',
            'conversation_id' => $this->conversation->id,
            'sender_id' => $this->sender->id,
            'sender_name' => trim($this->sender->name.' '.$this->sender->surname),
        ];
    }
}
