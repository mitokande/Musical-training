<?php

namespace App\Console\Commands;

use App\Models\AdCreative;
use App\Services\AdStudio\AdCreativeService;
use Illuminate\Console\Command;

/**
 * Drains the Ad Studio work queue: narration + project builds, then renders.
 *
 * This deliberately does NOT go through the application queue. This server has
 * no daemon worker — `routes/console.php` drains the database queue with
 * `queue:work --stop-when-empty --max-time=55` once a minute, and that worker
 * applies Laravel's default 60-second per-job timeout. A 1080x1920 30s render
 * takes about three minutes here, so as a queued job it would be killed
 * mid-frame every time, and while it ran it would hold the `withoutOverlapping`
 * lock that transactional email needs.
 *
 * A dedicated schedule entry has neither problem: no per-job timeout, and its
 * own lock. A long render delays nothing except the next render.
 */
class ProcessAdCreatives extends Command
{
    protected $signature = 'ads:process-queue
                            {--limit=1 : How many pending creatives to process in this run}';

    protected $description = 'Build and render pending Ad Studio creatives (a render takes minutes)';

    public function handle(AdCreativeService $service): int
    {
        if (! config('ad_studio.enabled')) {
            $this->comment('Ad Studio is disabled; nothing to do.');

            return self::SUCCESS;
        }

        $limit = max(1, (int) $this->option('limit'));
        $processed = 0;

        for ($i = 0; $i < $limit; $i++) {
            $creative = $service->processNext();

            if (! $creative) {
                break;
            }

            $processed++;
            $this->report($creative);
        }

        if ($processed === 0) {
            $this->line('Nothing pending.');
        }

        return self::SUCCESS;
    }

    private function report(AdCreative $creative): void
    {
        match ($creative->status) {
            AdCreative::STATUS_RENDERED => $this->info(sprintf(
                'Rendered [%s] — %.1f MB, %ss.',
                $creative->name,
                ($creative->render_bytes ?? 0) / 1048576,
                $creative->duration_seconds,
            )),
            AdCreative::STATUS_BUILT => $this->info(sprintf(
                'Built [%s] — %ss cut from %d takes.',
                $creative->name,
                $creative->duration_seconds,
                count($creative->vo_manifest ?? []),
            )),
            AdCreative::STATUS_QUEUED => $this->info(sprintf('Built [%s]; queued for render.', $creative->name)),
            AdCreative::STATUS_FAILED => $this->error(sprintf('Failed [%s]: %s', $creative->name, $creative->error)),
            default => $this->line(sprintf('[%s] is now %s.', $creative->name, $creative->status)),
        };
    }
}
