<?php

namespace App\Console\Commands;

use App\Jobs\ProcessEmailCampaign;
use App\Models\EmailCampaign;
use Illuminate\Console\Command;

class ProcessEmailCampaigns extends Command
{
    protected $signature = 'email:process-campaigns';

    protected $description = 'Start due scheduled campaigns and finalize campaigns whose queue has drained';

    public function handle(): int
    {
        EmailCampaign::where('status', 'scheduled')
            ->where('scheduled_at', '<=', now())
            ->each(function (EmailCampaign $campaign) {
                ProcessEmailCampaign::dispatch($campaign->id);
                $this->line("Dispatched campaign #{$campaign->id} ({$campaign->name})");
            });

        // a sending campaign with no queued messages left is finished
        EmailCampaign::where('status', 'sending')
            ->where('started_at', '<=', now()->subMinutes(2))
            ->each(function (EmailCampaign $campaign) {
                if (! $campaign->messages()->where('status', 'queued')->exists()) {
                    $campaign->update(['status' => 'sent', 'completed_at' => now()]);
                    $this->line("Campaign #{$campaign->id} completed");
                }
            });

        return self::SUCCESS;
    }
}
