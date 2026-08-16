<?php

namespace App\Http\Controllers;

use App\Models\SupportConversation;
use App\Models\SupportMessage;
use App\Models\User;
use App\Services\EmailCenter\EmailDispatchService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ContactController extends Controller
{
    /**
     * Public contact form. Lands the message in the admin Support Inbox as an
     * inbound message (same tables the IMAP fetcher writes to) and notifies the
     * support address so replies can go out through the normal inbox flow.
     */
    public function store(Request $request, EmailDispatchService $dispatcher): RedirectResponse
    {
        // Honeypot: bots fill every field, humans never see this one.
        if (filled($request->input('website'))) {
            return back()->with('contact_status', 'sent');
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email:rfc', 'max:190'],
            'subject' => ['required', 'string', 'in:general,billing,technical,schools,other'],
            'message' => ['required', 'string', 'min:10', 'max:5000'],
        ]);

        $email = mb_strtolower($data['email']);
        $subjectLabel = __('pages.contact.subject_'.$data['subject'], [], 'en');
        $subject = '[Contact] '.$subjectLabel.' — '.$data['name'];

        $conversation = SupportConversation::create([
            'subject' => $subject,
            'subject_key' => SupportConversation::normalizeSubject($subject),
            'contact_email' => $email,
            'contact_name' => $data['name'],
            'user_id' => $request->user()?->id ?? User::whereRaw('LOWER(email) = ?', [$email])->value('id'),
            'status' => 'open',
            'last_message_at' => now(),
        ]);

        SupportMessage::create([
            'conversation_id' => $conversation->id,
            'direction' => 'inbound',
            'message_id' => 'contact-form-'.Str::uuid()->toString().'@'.parse_url(config('app.url'), PHP_URL_HOST),
            'from_name' => $data['name'],
            'from_email' => $email,
            'to_email' => config('email-center.support_address'),
            'subject' => $subject,
            'plain_text_body' => $data['message'],
            'received_at' => now(),
        ]);

        $conversation->update(['message_count' => 1]);

        try {
            $dispatcher->dispatch(
                recipient: config('email-center.support_address'),
                emailType: 'support',
                subject: $subject,
                html: view('emails.contact-form', [
                    'name' => $data['name'],
                    'email' => $email,
                    'topic' => $subjectLabel,
                    'body' => $data['message'],
                    'conversation' => $conversation,
                ])->render(),
            );
        } catch (\Throwable $e) {
            // The message is already stored in the Support Inbox; the
            // notification is a convenience, never a reason to fail the form.
            Log::warning('Contact form notification failed: '.$e->getMessage());
        }

        return back()->with('contact_status', 'sent');
    }
}
