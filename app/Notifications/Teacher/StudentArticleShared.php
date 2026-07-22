<?php

namespace App\Notifications\Teacher;

use App\Models\Article;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/** Sent to a student when a teacher shares one of their articles. */
class StudentArticleShared extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Article $article, public User $teacher) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $teacherName = trim($this->teacher->name.' '.$this->teacher->surname);

        return (new MailMessage)
            ->subject($teacherName.' shared an article with you')
            ->line($teacherName.' shared an article with you: **'.$this->article->title.'**')
            ->action('Read the article', route('articles.show', $this->article->slug));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'student_article_shared',
            'article_id' => $this->article->id,
            'title' => $this->article->title,
            'teacher' => trim($this->teacher->name.' '.$this->teacher->surname),
            'url' => route('articles.show', $this->article->slug),
        ];
    }
}
