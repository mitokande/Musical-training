<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessEmailCampaign;
use App\Models\EmailCampaign;
use App\Models\EmailTemplate;
use App\Services\EmailCenter\EmailDispatchService;
use App\Services\EmailCenter\SegmentBuilder;
use Illuminate\Http\Request;

class EmailCampaignController extends Controller
{
    public function index()
    {
        $campaigns = EmailCampaign::with('template')->latest()->paginate(20);

        return view('admin.email-center.campaigns.index', compact('campaigns'));
    }

    public function create()
    {
        return view('admin.email-center.campaigns.form', [
            'campaign' => new EmailCampaign,
            'templates' => EmailTemplate::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validated($request);
        $validated['created_by'] = $request->user()->id;

        $campaign = EmailCampaign::create($validated);

        return redirect()->route('admin.email-campaigns.show', $campaign)
            ->with('success', $campaign->status === 'scheduled' ? 'Campaign scheduled.' : 'Campaign saved as draft.');
    }

    public function show(EmailCampaign $campaign, SegmentBuilder $segments)
    {
        $campaign->load('template', 'creator');

        return view('admin.email-center.campaigns.show', [
            'campaign' => $campaign,
            'estimatedRecipients' => $campaign->isEditable() ? $segments->count($campaign->segment ?? []) : null,
        ]);
    }

    public function edit(EmailCampaign $campaign)
    {
        abort_unless($campaign->isEditable(), 403, 'Only draft or scheduled campaigns can be edited.');

        return view('admin.email-center.campaigns.form', [
            'campaign' => $campaign,
            'templates' => EmailTemplate::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, EmailCampaign $campaign)
    {
        abort_unless($campaign->isEditable(), 403);

        $campaign->update($this->validated($request));

        return redirect()->route('admin.email-campaigns.show', $campaign)->with('success', 'Campaign updated.');
    }

    public function send(EmailCampaign $campaign)
    {
        abort_unless($campaign->isEditable(), 403);

        $campaign->update(['status' => 'scheduled', 'scheduled_at' => now()]);
        ProcessEmailCampaign::dispatch($campaign->id);

        return back()->with('success', 'Campaign queued for sending.');
    }

    public function cancel(EmailCampaign $campaign)
    {
        abort_unless(in_array($campaign->status, ['scheduled', 'sending']), 403);

        // queued-but-unsent messages are skipped by the send job status check
        $campaign->messages()->where('status', 'queued')->update(['status' => 'suppressed']);
        $campaign->update(['status' => 'cancelled']);

        return back()->with('success', 'Campaign cancelled.');
    }

    public function destroy(EmailCampaign $campaign)
    {
        abort_unless(in_array($campaign->status, ['draft', 'cancelled', 'sent', 'failed']), 403);

        $campaign->delete();

        return redirect()->route('admin.email-campaigns.index')->with('success', 'Campaign deleted.');
    }

    public function testSend(Request $request, EmailCampaign $campaign, EmailDispatchService $dispatcher)
    {
        $validated = $request->validate(['recipient' => 'required|email']);

        $message = $dispatcher->dispatch(
            recipient: $validated['recipient'],
            emailType: 'test',
            template: $campaign->template,
            subject: '[TEST] '.$campaign->subject,
            html: $campaign->custom_html,
        );

        return back()->with(
            $message ? 'success' : 'error',
            $message ? "Test email queued to {$validated['recipient']}." : 'Test email could not be queued.'
        );
    }

    /**
     * Live recipient estimate for the segment builder UI.
     */
    public function segmentCount(Request $request, SegmentBuilder $segments)
    {
        return response()->json(['count' => $segments->count($this->segmentFromRequest($request))]);
    }

    protected function validated(Request $request): array
    {
        $data = $request->validate([
            'name' => 'required|string|max:150',
            'subject' => 'required|string|max:255',
            'preheader' => 'nullable|string|max:255',
            'template_id' => 'nullable|exists:email_templates,id',
            'custom_html' => 'nullable|string|required_without:template_id',
            'schedule_mode' => 'required|in:draft,now,later',
            'scheduled_at' => 'nullable|date|required_if:schedule_mode,later|after:now',
        ]);

        return [
            'name' => $data['name'],
            'subject' => $data['subject'],
            'preheader' => $data['preheader'] ?? null,
            'template_id' => $data['template_id'] ?? null,
            'custom_html' => $data['custom_html'] ?? null,
            'segment' => $this->segmentFromRequest($request),
            'status' => $data['schedule_mode'] === 'draft' ? 'draft' : 'scheduled',
            'scheduled_at' => match ($data['schedule_mode']) {
                'now' => now(),
                'later' => $data['scheduled_at'],
                default => null,
            },
        ];
    }

    protected function segmentFromRequest(Request $request): array
    {
        return array_filter([
            'plans' => array_filter((array) $request->input('segment.plans', [])),
            'roles' => array_filter((array) $request->input('segment.roles', [])),
            'locales' => array_filter((array) $request->input('segment.locales', [])),
            'activity' => $request->input('segment.activity', 'any'),
            'registered_within_days' => $request->input('segment.registered_within_days'),
            'registered_before_days' => $request->input('segment.registered_before_days'),
            'has_learning_path' => $request->boolean('segment.has_learning_path') ?: null,
        ]);
    }
}
