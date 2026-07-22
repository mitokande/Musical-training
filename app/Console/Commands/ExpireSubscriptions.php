<?php

namespace App\Console\Commands;

use App\Models\Subscription;
use App\Services\Payments\SubscriptionService;
use Illuminate\Console\Command;

class ExpireSubscriptions extends Command
{
    protected $signature = 'subscriptions:expire';

    protected $description = 'Expire active subscriptions whose period has ended and drop lapsed users back to free.';

    public function handle(SubscriptionService $subscriptions): int
    {
        // Real auto-renewal happens at the provider (webhook re-activates). With
        // the manual gateway there is no charge to attempt, so a lapsed period
        // simply expires. A real gateway can later branch on auto_renew here.
        $expired = 0;

        Subscription::where('status', 'active')
            ->whereNotNull('ends_at')
            ->where('ends_at', '<', now())
            ->with('user')
            ->chunkById(100, function ($chunk) use ($subscriptions, &$expired) {
                foreach ($chunk as $subscription) {
                    $subscriptions->expire($subscription);
                    $expired++;
                }
            });

        $this->info("Expired {$expired} subscription(s).");

        return self::SUCCESS;
    }
}
