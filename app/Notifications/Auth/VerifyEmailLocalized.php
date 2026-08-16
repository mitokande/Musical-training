<?php

namespace App\Notifications\Auth;

use App\Services\EmailCenter\EmailTemplateLibrary;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;

/**
 * Email-verification notification rendered in the recipient's language and in
 * the Harmoniva brand shell. Laravel applies User::preferredLocale()
 * automatically, so the activation mail arrives in the user's own locale.
 *
 * The body is built from EmailTemplateLibrary — the same layout, button and
 * palette the Email Center lifecycle mail uses — rather than Laravel's stock
 * markdown theme, so the first email a new account ever receives already looks
 * like the rest of the mailing. It is service mail, so the layout is rendered
 * without the unsubscribe/preferences footer.
 */
class VerifyEmailLocalized extends VerifyEmail
{
    public function toMail($notifiable): MailMessage
    {
        $library = app(EmailTemplateLibrary::class);
        $locale = app()->getLocale();
        $url = $this->verificationUrl($notifiable);

        return (new MailMessage)
            ->subject(__('notifications.verify.subject'))
            ->view('email.branded', [
                'html' => $library->standaloneHtml(
                    $this->body($library, $url),
                    __('notifications.verify.preheader'),
                    $locale
                ),
            ]);
    }

    protected function body(EmailTemplateLibrary $library, string $url): string
    {
        $title = e(__('notifications.verify.title'));
        $line1 = e(__('notifications.verify.line1', ['app' => config('app.name')]));
        $line2 = e(__('notifications.verify.line2'));
        $fallback = e(__('notifications.verify.fallback'));
        $button = $library->button($url, __('notifications.verify.action'), __('notifications.verify.btn_sub'));
        $safeUrl = e($url);

        return <<<HTML
<div style="text-align:center;margin-bottom:8px;">
    <div style="display:inline-block;width:64px;height:64px;border-radius:18px;background:linear-gradient(135deg,#9333ea,#7c3aed);line-height:64px;font-size:32px;">✉️</div>
</div>
<h1 style="margin:6px 0 6px;color:#111827;font-size:24px;text-align:center;">{$title}</h1>
<p style="text-align:center;color:#6b7280;margin:0 0 22px;">{$line1}</p>
{$button}
<div style="margin:26px 0 0;padding:16px 18px;background:#faf5ff;border:1px solid #ecdcff;border-radius:12px;">
    <div style="font-size:12.5px;color:#6b21a8;font-weight:600;margin-bottom:6px;">{$fallback}</div>
    <div style="font-size:12px;color:#7c3aed;word-break:break-all;line-height:1.6;">{$safeUrl}</div>
</div>
<p style="text-align:center;color:#9ca3af;font-size:13px;margin-top:24px;">{$line2}</p>
HTML;
    }
}
