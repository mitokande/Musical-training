<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\SupportReplyMail;
use App\Models\SupportConversation;
use App\Models\SupportMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class SupportInboxController extends Controller
{
    public function index(Request $request)
    {
        $conversations = SupportConversation::with('user', 'assignedAdmin')
            ->when($request->status, fn ($q, $s) => $q->where('status', $s))
            ->when(
                $request->search,
                fn ($q, $s) => $q->where(fn ($w) => $w->where('subject', 'like', "%{$s}%")->orWhere('contact_email', 'like', "%{$s}%"))
            )
            ->orderByDesc('last_message_at')
            ->paginate(25)
            ->withQueryString();

        $counts = SupportConversation::selectRaw('status, COUNT(*) as total')->groupBy('status')->pluck('total', 'status');

        return view('admin.email-center.support.index', [
            'conversations' => $conversations,
            'counts' => $counts,
            'mode' => config('email-center.support_inbound_mode'),
            'webmailUrl' => config('email-center.webmail_url'),
        ]);
    }

    public function show(SupportConversation $conversation)
    {
        $conversation->load(['messages' => fn ($q) => $q->orderByDesc('created_at'), 'user', 'assignedAdmin']);

        return view('admin.email-center.support.show', compact('conversation'));
    }

    public function reply(Request $request, SupportConversation $conversation)
    {
        $validated = $request->validate(['body' => 'required|string|max:20000']);

        $lastInbound = $conversation->messages()->where('direction', 'inbound')->latest('received_at')->first();

        $subject = preg_match('/^re:/i', $conversation->subject) ? $conversation->subject : 'Re: '.$conversation->subject;

        $bodyHtml = nl2br(e($validated['body']));
        $bodyHtml = '<div style="font-family:-apple-system,\'Segoe UI\',sans-serif;font-size:14px;color:#1f2937;line-height:1.7;">'
            .$bodyHtml
            .'<div style="border-top:1px solid #e5e7eb;margin-top:24px;padding-top:12px;font-size:13px;color:#6b7280;">'
            .'<strong style="color:#4f46e5;">'.e(config('app.name')).'</strong> / Support Team<br>'
            .'<a href="'.e(config('app.url')).'" style="color:#4f46e5;text-decoration:none;">harmoniva.app</a>'
            .'</div></div>';

        $sent = Mail::mailer('ses')
            ->to($conversation->contact_email, $conversation->contact_name)
            ->send(new SupportReplyMail(
                replySubject: $subject,
                bodyHtml: $bodyHtml,
                inReplyToMessageId: $lastInbound?->message_id,
                referencesHeader: $lastInbound?->references,
            ));

        $conversation->messages()->create([
            'direction' => 'outbound',
            'message_id' => $sent?->getOriginalMessage()->getHeaders()->get('Message-ID')?->getBodyAsString(),
            'in_reply_to' => $lastInbound?->message_id,
            'references' => trim(($lastInbound?->references ?? '').' <'.($lastInbound?->message_id ?? '').'>'),
            'from_name' => config('app.name').' Support',
            'from_email' => config('email-center.support_address'),
            'to_email' => $conversation->contact_email,
            'subject' => $subject,
            'plain_text_body' => $validated['body'],
            'html_body_sanitized' => $bodyHtml,
            'sent_by_admin_id' => $request->user()->id,
            'ses_message_id' => $sent?->getOriginalMessage()->getHeaders()->get('X-SES-Message-ID')?->getBodyAsString(),
            'received_at' => now(),
        ]);

        $conversation->update([
            'status' => 'pending',
            'last_message_at' => now(),
            'message_count' => $conversation->messages()->count(),
        ]);

        return back()->with('success', 'Reply sent via Amazon SES.');
    }

    public function updateStatus(Request $request, SupportConversation $conversation)
    {
        $validated = $request->validate([
            'status' => 'required|in:'.implode(',', SupportConversation::STATUSES),
            'assigned_admin_id' => 'nullable|exists:users,id',
        ]);

        $conversation->update($validated);

        return back()->with('success', 'Conversation updated.');
    }

    public function attachment(SupportMessage $message, int $index)
    {
        $meta = $message->attachment_metadata[$index] ?? null;

        abort_unless($meta && ($meta['stored'] ?? false), 404);

        return Storage::disk('local')->download($meta['path'], $meta['name']);
    }
}
