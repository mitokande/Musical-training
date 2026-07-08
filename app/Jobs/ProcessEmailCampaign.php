<?php

namespace App\Jobs;

use App\Models\EmailCampaign;
use App\Services\EmailCenter\EmailDispatchService;
use App\Services\EmailCenter\SegmentBuilder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Resolves a campaign's segment into recipients and queues one
 * SendEmailCenterMessage per user. Actual SES calls happen in the
 * rate-limited send jobs; the email:process-campaigns scheduler task
 * flips the campaign to "sent" once the queue drains.
 */
class ProcessEmailCampaign implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 1800;

    public int $tries = 1;

    public function __construct(public int $campaignId) {}

    public function handle(SegmentBuilder $segments, EmailDispatchService $dispatcher): void
    {
        $campaign = EmailCampaign::with('template')->find($this->campaignId);

        if (! $campaign || ! in_array($campaign->status, ['scheduled', 'sending'])) {
            return;
        }

        $campaign->update(['status' => 'sending', 'started_at' => $campaign->started_at ?? now()]);

        $queued = 0;

        $segments->query($campaign->segment ?? [])
            // skip users this campaign already queued mail for (safe re-run)
            ->whereNotIn('id', $campaign->messages()->whereNotNull('user_id')->select('user_id'))
            ->orderBy('id')
            ->chunkById(200, function ($users) use ($campaign, $dispatcher, &$queued) {
                foreach ($users as $user) {
                    $message = $dispatcher->dispatch(
                        recipient: $user,
                        emailType: 'campaign',
                        template: $campaign->template,
                        subject: $campaign->subject,
                        html: $campaign->custom_html,
                        campaign: $campaign,
                    );

                    if ($message) {
                        $queued++;
                    }
                }
            });

        $campaign->update(['total_recipients' => $campaign->messages()->count()]);

        Log::info('Campaign queued', ['campaign' => $campaign->id, 'queued' => $queued]);
    }

    public function failed(\Throwable $e): void
    {
        EmailCampaign::where('id', $this->campaignId)->update(['status' => 'failed']);
    }
}
