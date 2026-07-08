<?php

namespace App\Mail;

use App\Models\EmailMessage;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Support\Facades\URL;
use Symfony\Component\Mailer\Header\MetadataHeader;
use Symfony\Component\Mime\Email;

/**
 * Generic mailable for every Email Center send. Attaches the SES
 * configuration set + message tags so SNS event notifications can be
 * matched back to the email_messages row.
 */
class EmailCenterMailable extends Mailable
{
    public function __construct(
        public EmailMessage $emailMessage,
        public string $renderedSubject,
        public string $renderedHtml,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            replyTo: [config('email-center.reply_to')],
            subject: $this->renderedSubject,
        );
    }

    public function content(): Content
    {
        return new Content(
            htmlString: $this->renderedHtml,
        );
    }

    public function build()
    {
        $message = $this->emailMessage;

        $this->withSymfonyMessage(function (Email $email) use ($message) {
            $headers = $email->getHeaders();

            // SES honors this header inside SendRawEmail payloads
            if ($configSet = config('email-center.configuration_set')) {
                $headers->addTextHeader('X-SES-CONFIGURATION-SET', $configSet);
            }

            // MetadataHeader → SesTransport converts these to SES message
            // tags, which come back inside event notifications (mail.tags)
            $tags = array_filter([
                'email_type' => $message->email_type,
                'tracking_token' => $message->tracking_token,
                'user_id' => $message->user_id,
                'campaign_id' => $message->campaign_id,
                'automation_id' => $message->automation_id,
                'template_id' => $message->template_id,
            ], fn ($v) => $v !== null && $v !== '');

            foreach ($tags as $key => $value) {
                // SES tag values allow only alphanumerics, dash, underscore
                $headers->add(new MetadataHeader($key, preg_replace('/[^a-zA-Z0-9_-]/', '-', (string) $value)));
            }

            // RFC 8058 one-click unsubscribe for marketing mail
            if ($message->isMarketing()) {
                $unsubUrl = URL::signedRoute('email.unsubscribe', ['token' => $message->tracking_token]);
                $headers->addTextHeader('List-Unsubscribe', '<'.$unsubUrl.'>');
                $headers->addTextHeader('List-Unsubscribe-Post', 'List-Unsubscribe=One-Click');
            }
        });

        return $this;
    }
}
