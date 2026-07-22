<?php

namespace App\Notifications\Teacher;

use App\Models\TeacherMedia;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/** Sent to a student when a teacher shares a document from their archive. */
class StudentDocumentShared extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public TeacherMedia $media, public User $teacher) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $teacherName = trim($this->teacher->name.' '.$this->teacher->surname);
        $title = $this->media->title ?: $this->media->original_name;

        return (new MailMessage)
            ->subject($teacherName.' shared a document with you')
            ->line($teacherName.' shared a document with you: **'.$title.'**')
            ->action('Download the file', route('assignments.media.download', $this->media));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'student_document_shared',
            'media_id' => $this->media->id,
            'title' => $this->media->title ?: $this->media->original_name,
            'teacher' => trim($this->teacher->name.' '.$this->teacher->surname),
            'url' => route('assignments.media.download', $this->media),
        ];
    }
}
