<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Symfony\Component\Mime\Email;

/**
 * Admin reply in a support conversation. Sent through SES from the
 * support address with proper threading headers so it lands in the same
 * conversation in the customer's mail client. Transactional — ignores
 * marketing unsubscribes and carries no tracking pixel.
 */
class SupportReplyMail extends Mailable
{
    public function __construct(
        public string $replySubject,
        public string $bodyHtml,
        public ?string $inReplyToMessageId = null,
        public ?string $referencesHeader = null,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address(config('email-center.support_address'), config('app.name').' Support'),
            replyTo: [config('email-center.support_address')],
            subject: $this->replySubject,
        );
    }

    public function content(): Content
    {
        return new Content(htmlString: $this->bodyHtml);
    }

    public function build()
    {
        $this->withSymfonyMessage(function (Email $email) {
            $headers = $email->getHeaders();

            if ($configSet = config('email-center.configuration_set')) {
                $headers->addTextHeader('X-SES-CONFIGURATION-SET', $configSet);
            }

            if ($this->inReplyToMessageId) {
                $headers->addIdHeader('In-Reply-To', $this->inReplyToMessageId);
                $headers->addTextHeader('References', trim(($this->referencesHeader ?? '').' <'.$this->inReplyToMessageId.'>'));
            }
        });

        return $this;
    }
}
