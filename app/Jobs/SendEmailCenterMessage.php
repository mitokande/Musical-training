<?php

namespace App\Jobs;

use App\Mail\EmailCenterMailable;
use App\Models\EmailEvent;
use App\Models\EmailMessage;
use App\Services\EmailCenter\SuppressionService;
use App\Services\EmailCenter\TemplateRenderer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\RateLimited;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendEmailCenterMessage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public array $backoff = [60, 300];

    public function __construct(public int $emailMessageId) {}

    public function middleware(): array
    {
        return [new RateLimited('email-center-send')];
    }

    public function handle(TemplateRenderer $renderer, SuppressionService $suppressions): void
    {
        $message = EmailMessage::with(['user', 'template', 'campaign', 'automation'])->find($this->emailMessageId);

        if (! $message || $message->status !== 'queued') {
            return;
        }

        // re-check: the address may have bounced between queueing and sending
        if ($message->isMarketing() && $suppressions->isSuppressed($message->recipient_email)) {
            $message->update(['status' => 'suppressed']);

            return;
        }

        $context = $message->metadata['context'] ?? [];
        $rawHtml = $message->template?->html_body ?? ($message->metadata['html'] ?? '');
        $rawSubject = $message->subject;

        $html = $renderer->render($rawHtml, $message, $context);
        $subject = $renderer->renderSubject($rawSubject, $message, $context);

        try {
            $sent = Mail::mailer('ses')
                ->to($message->recipient_email, $message->user?->name)
                ->send(new EmailCenterMailable($message, $subject, $html));

            $sesMessageId = $sent?->getOriginalMessage()->getHeaders()->get('X-SES-Message-ID')?->getBodyAsString();

            $message->update([
                'ses_message_id' => $sesMessageId,
                'status' => 'sent',
                'sent_at' => now(),
            ]);

            $message->campaign?->increment('sent_count');
            $message->automation?->increment('send_count');

            EmailEvent::create([
                'email_message_id' => $message->id,
                'ses_message_id' => $sesMessageId,
                'event_type' => 'sent',
                'recipient_email' => $message->recipient_email,
                'source' => 'internal',
                'occurred_at' => now(),
                'dedup_hash' => hash('sha256', 'internal-sent-'.$message->id),
            ]);
        } catch (\Throwable $e) {
            if ($this->attempts() >= $this->tries) {
                $message->update(['status' => 'failed', 'error' => mb_substr($e->getMessage(), 0, 2000)]);
                $message->campaign?->increment('failed_count');
            }

            throw $e;
        }
    }

    public function failed(\Throwable $e): void
    {
        EmailMessage::where('id', $this->emailMessageId)
            ->where('status', 'queued')
            ->update(['status' => 'failed', 'error' => mb_substr($e->getMessage(), 0, 2000)]);
    }
}
