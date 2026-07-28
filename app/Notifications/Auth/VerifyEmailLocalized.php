<?php

namespace App\Notifications\Auth;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;

/**
 * Email-verification notification rendered in the recipient's language.
 * Laravel applies User::preferredLocale() automatically, so the activation
 * mail arrives in the user's own locale.
 */
class VerifyEmailLocalized extends VerifyEmail
{
    public function toMail($notifiable): MailMessage
    {
        $url = $this->verificationUrl($notifiable);

        return (new MailMessage)
            ->subject(__('notifications.verify.subject'))
            ->line(__('notifications.verify.line1', ['app' => config('app.name')]))
            ->action(__('notifications.verify.action'), $url)
            ->line(__('notifications.verify.line2'));
    }
}
