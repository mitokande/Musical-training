<?php

namespace App\Services\EmailCenter;

use App\Models\SupportConversation;
use App\Models\SupportMessage;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Webklex\PHPIMAP\ClientManager;

/**
 * Pulls new mail from the local support@harmoniva.app Dovecot mailbox over
 * IMAP and files it into support conversations. The MX record keeps
 * pointing at this server — SES is only used for outbound replies.
 */
class SupportMailFetcher
{
    protected const MAX_ATTACHMENT_BYTES = 10 * 1024 * 1024;

    public function fetch(): int
    {
        $config = config('email-center.support_imap');

        if (empty($config['password'])) {
            Log::warning('Support IMAP fetch skipped: SUPPORT_IMAP_PASSWORD not set');

            return 0;
        }

        $client = (new ClientManager)->make([
            'host' => $config['host'],
            'port' => $config['port'],
            'encryption' => $config['encryption'],
            'validate_cert' => $config['validate_cert'],
            'username' => $config['username'],
            'password' => $config['password'],
            'protocol' => 'imap',
        ]);

        $client->connect();

        $folder = $client->getFolder($config['folder'] ?? 'INBOX');
        $messages = $folder->messages()->unseen()->limit(50)->get();

        $imported = 0;

        foreach ($messages as $imapMessage) {
            try {
                if ($this->import($imapMessage)) {
                    $imported++;
                }
                $imapMessage->setFlag('Seen');
            } catch (\Throwable $e) {
                Log::error('Support mail import failed', [
                    'error' => $e->getMessage(),
                    'uid' => $imapMessage->getUid(),
                ]);
                // leave unseen so the next run retries it
            }
        }

        $client->disconnect();

        return $imported;
    }

    protected function import($imapMessage): bool
    {
        $messageId = trim((string) $imapMessage->getMessageId(), '<> ');
        $fromAddress = $imapMessage->getFrom()[0] ?? null;
        $fromEmail = mb_strtolower($fromAddress->mail ?? '');
        $fromName = $this->decodeMimeHeader($fromAddress->personal ?? null);
        $subject = $this->decodeMimeHeader((string) ($imapMessage->getSubject() ?? '')) ?: '(no subject)';

        if ($fromEmail === '') {
            return false;
        }

        // ignore our own outbound mail and obvious loops
        if ($fromEmail === mb_strtolower(config('email-center.support_address'))) {
            return false;
        }

        // idempotency on the RFC 5322 Message-ID
        if ($messageId !== '' && SupportMessage::where('message_id', $messageId)->exists()) {
            return false;
        }

        $inReplyTo = trim((string) $imapMessage->getInReplyTo(), '<> ');
        $references = trim((string) $imapMessage->getReferences());

        $conversation = $this->resolveConversation($subject, $fromEmail, $fromName, $inReplyTo, $references);

        $receivedAt = $imapMessage->getDate()?->toDate() ?? now();

        $message = $conversation->messages()->create([
            'direction' => 'inbound',
            'message_id' => $messageId ?: null,
            'in_reply_to' => $inReplyTo ?: null,
            'references' => $references ?: null,
            'from_name' => $fromName,
            'from_email' => $fromEmail,
            'to_email' => config('email-center.support_address'),
            'subject' => $subject,
            'plain_text_body' => $imapMessage->getTextBody() ?: null,
            'html_body_sanitized' => $this->sanitizeHtml((string) $imapMessage->getHTMLBody()),
            'received_at' => $receivedAt,
        ]);

        $message->update(['attachment_metadata' => $this->storeAttachments($imapMessage, $message->id)]);

        $conversation->update([
            'status' => $conversation->status === 'closed' ? 'open' : $conversation->status,
            'last_message_at' => $receivedAt,
            'message_count' => $conversation->messages()->count(),
        ]);

        return true;
    }

    protected function resolveConversation(string $subject, string $fromEmail, ?string $fromName, string $inReplyTo, string $references): SupportConversation
    {
        // thread by In-Reply-To / References against known Message-IDs
        $referenceIds = array_filter(array_map(
            fn ($ref) => trim($ref, '<> '),
            array_merge([$inReplyTo], preg_split('/\s+/', $references) ?: [])
        ));

        if ($referenceIds !== []) {
            $existing = SupportMessage::whereIn('message_id', $referenceIds)->first();
            if ($existing) {
                return $existing->conversation;
            }
        }

        // fallback: same sender + normalized subject within 30 days
        $subjectKey = SupportConversation::normalizeSubject($subject);

        $recent = SupportConversation::where('contact_email', $fromEmail)
            ->where('subject_key', $subjectKey)
            ->where('last_message_at', '>=', now()->subDays(30))
            ->first();

        if ($recent) {
            return $recent;
        }

        return SupportConversation::create([
            'subject' => $subject,
            'subject_key' => $subjectKey,
            'contact_email' => $fromEmail,
            'contact_name' => $fromName,
            'user_id' => User::whereRaw('LOWER(email) = ?', [$fromEmail])->value('id'),
            'status' => 'open',
            'last_message_at' => now(),
        ]);
    }

    protected function storeAttachments($imapMessage, int $messageId): ?array
    {
        $metadata = [];

        foreach ($imapMessage->getAttachments() as $attachment) {
            $size = (int) $attachment->getSize();
            $name = Str::limit(preg_replace('/[^\w.\-]+/u', '_', (string) $attachment->getName()), 120, '');

            if ($size > self::MAX_ATTACHMENT_BYTES || $name === '') {
                $metadata[] = ['name' => $name ?: 'unnamed', 'size' => $size, 'stored' => false, 'reason' => 'too large or unnamed'];

                continue;
            }

            $path = "support-attachments/{$messageId}/".$name;
            Storage::disk('local')->put($path, $attachment->getContent());

            $metadata[] = [
                'name' => $name,
                'size' => $size,
                'mime' => $attachment->getMimeType(),
                'path' => $path,
                'stored' => true,
            ];
        }

        return $metadata ?: null;
    }

    /**
     * Decodes RFC 2047 encoded-words (=?utf-8?Q?...?=) that the IMAP
     * library occasionally passes through undecoded.
     */
    protected function decodeMimeHeader(?string $value): ?string
    {
        if ($value === null || $value === '') {
            return $value;
        }

        if (str_contains($value, '=?')) {
            $decoded = iconv_mime_decode($value, ICONV_MIME_DECODE_CONTINUE_ON_ERROR, 'UTF-8');

            return $decoded !== false ? $decoded : $value;
        }

        return $value;
    }

    /**
     * Conservative HTML sanitizer for display inside the admin panel
     * (which additionally renders it inside a sandboxed iframe).
     */
    protected function sanitizeHtml(string $html): ?string
    {
        if (trim($html) === '') {
            return null;
        }

        $html = preg_replace('#<(script|style|iframe|object|embed|form|link|meta|base)\b[^>]*>.*?</\1>#is', '', $html);
        $html = preg_replace('#<(script|style|iframe|object|embed|form|link|meta|base)\b[^>]*/?>#i', '', $html);
        $html = preg_replace('/\son\w+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', $html);
        $html = preg_replace('/(href|src)\s*=\s*(["\']?)\s*javascript:[^"\'>\s]*\2/i', '$1=$2#$2', $html);

        return $html;
    }
}
