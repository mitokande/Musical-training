<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmailCampaign;
use App\Models\EmailEvent;
use App\Models\EmailMessage;
use App\Models\EmailSuppression;
use App\Models\EmailTemplate;
use App\Models\SystemSetting;
use App\Services\EmailCenter\EmailDispatchService;
use App\Services\EmailCenter\SesStatusService;
use App\Services\EmailCenter\SuppressionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EmailCenterController extends Controller
{
    public function dashboard()
    {
        $since = now()->subDays(30);

        $totals = [
            'sent' => EmailMessage::where('created_at', '>=', $since)->whereNotIn('status', ['queued', 'suppressed', 'failed'])->count(),
            'delivered' => EmailMessage::where('created_at', '>=', $since)->whereNotNull('delivered_at')->count(),
            'opened' => EmailMessage::where('created_at', '>=', $since)->whereNotNull('opened_at')->count(),
            'clicked' => EmailMessage::where('created_at', '>=', $since)->whereNotNull('clicked_at')->count(),
            'bounced' => EmailMessage::where('created_at', '>=', $since)->where('status', 'bounced')->count(),
            'complained' => EmailMessage::where('created_at', '>=', $since)->where('status', 'complained')->count(),
            'queued' => EmailMessage::where('status', 'queued')->count(),
        ];

        $totals['delivery_rate'] = $totals['sent'] > 0 ? round($totals['delivered'] / $totals['sent'] * 100, 1) : null;
        $totals['open_rate'] = $totals['delivered'] > 0 ? round($totals['opened'] / $totals['delivered'] * 100, 1) : null;
        $totals['click_rate'] = $totals['delivered'] > 0 ? round($totals['clicked'] / $totals['delivered'] * 100, 1) : null;

        // daily send/open/click series for the chart
        $daily = EmailEvent::where('occurred_at', '>=', now()->subDays(14))
            ->whereIn('event_type', ['sent', 'delivered', 'opened', 'clicked', 'bounced'])
            ->groupBy('day', 'event_type')
            ->orderBy('day')
            ->get([
                DB::raw('DATE(occurred_at) as day'),
                'event_type',
                DB::raw('COUNT(*) as total'),
            ])
            ->groupBy('day');

        $recentCampaigns = EmailCampaign::latest()->limit(5)->get();
        $recentEvents = EmailEvent::with('message')->latest('occurred_at')->limit(10)->get();
        $suppressionCount = EmailSuppression::count();

        return view('admin.email-center.dashboard', compact('totals', 'daily', 'recentCampaigns', 'recentEvents', 'suppressionCount'));
    }

    public function logs(Request $request)
    {
        $messages = EmailMessage::with(['user', 'campaign', 'automation', 'template'])
            ->when($request->status, fn ($q, $s) => $q->where('status', $s))
            ->when($request->type, fn ($q, $t) => $q->where('email_type', $t))
            ->when($request->search, fn ($q, $s) => $q->where('recipient_email', 'like', "%{$s}%"))
            ->latest()
            ->paginate(30)
            ->withQueryString();

        return view('admin.email-center.logs.index', compact('messages'));
    }

    public function logShow(EmailMessage $message)
    {
        $message->load(['user', 'campaign', 'automation', 'template', 'events' => fn ($q) => $q->orderBy('occurred_at')]);

        return view('admin.email-center.logs.show', compact('message'));
    }

    public function suppressions(Request $request)
    {
        $suppressions = EmailSuppression::query()
            ->when($request->reason, fn ($q, $r) => $q->where('reason', $r))
            ->when($request->search, fn ($q, $s) => $q->where('email', 'like', "%{$s}%"))
            ->latest('suppressed_at')
            ->paginate(30)
            ->withQueryString();

        // repeat soft bouncers (last 30 days) surfaced for manual suppression
        $softBouncers = EmailEvent::where('event_type', 'bounced')
            ->where('occurred_at', '>=', now()->subDays(30))
            ->where('metadata->bounceType', 'Transient')
            ->groupBy('recipient_email')
            ->havingRaw('COUNT(*) >= ?', [config('email-center.soft_bounce_threshold')])
            ->whereNotIn('recipient_email', EmailSuppression::select('email'))
            ->get(['recipient_email', DB::raw('COUNT(*) as bounce_count')]);

        return view('admin.email-center.suppressions.index', compact('suppressions', 'softBouncers'));
    }

    public function suppressionStore(Request $request, SuppressionService $service)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'reason' => 'required|in:'.implode(',', EmailSuppression::REASONS),
            'notes' => 'nullable|string|max:500',
        ]);

        $service->suppress($validated['email'], $validated['reason'], 'admin', $validated['notes'] ?? null);

        return back()->with('success', 'Address suppressed.');
    }

    public function suppressionDestroy(EmailSuppression $suppression, SuppressionService $service)
    {
        $service->unsuppress($suppression->email);

        return back()->with('success', 'Address removed from suppression list.');
    }

    public function settings(SesStatusService $sesStatus)
    {
        return view('admin.email-center.settings', [
            'ses' => $sesStatus->status(),
            'settings' => [
                'email_frequency_cap' => SystemSetting::get('email_frequency_cap', config('email-center.frequency_cap')),
                'email_send_rate' => SystemSetting::get('email_send_rate', config('email-center.send_rate_per_second')),
                'email_test_recipient' => SystemSetting::get('email_test_recipient', config('email-center.test_recipient')),
            ],
            'supportMode' => config('email-center.support_inbound_mode'),
            'webmailUrl' => config('email-center.webmail_url'),
            'fromAddress' => config('email-center.from.address'),
            'replyTo' => config('email-center.reply_to'),
            'supportAddress' => config('email-center.support_address'),
        ]);
    }

    public function settingsUpdate(Request $request)
    {
        $validated = $request->validate([
            'email_frequency_cap' => 'required|integer|min:0|max:50',
            'email_send_rate' => 'required|integer|min:1|max:14',
            'email_test_recipient' => 'nullable|email',
        ]);

        SystemSetting::set('email_frequency_cap', (string) $validated['email_frequency_cap'], 'integer', 'email');
        SystemSetting::set('email_send_rate', (string) $validated['email_send_rate'], 'integer', 'email');
        SystemSetting::set('email_test_recipient', (string) ($validated['email_test_recipient'] ?? ''), 'string', 'email');

        return back()->with('success', 'Email settings updated.');
    }

    public function testSend(Request $request, EmailDispatchService $dispatcher)
    {
        $validated = $request->validate([
            'template_id' => 'required|exists:email_templates,id',
            'recipient' => 'required|email',
        ]);

        $template = EmailTemplate::findOrFail($validated['template_id']);

        $message = $dispatcher->dispatch(
            recipient: $request->user()->email === $validated['recipient'] ? $request->user() : $validated['recipient'],
            emailType: 'test',
            template: $template,
        );

        return back()->with(
            $message ? 'success' : 'error',
            $message ? "Test email queued to {$validated['recipient']}." : 'Test email could not be queued (address suppressed?).'
        );
    }
}
