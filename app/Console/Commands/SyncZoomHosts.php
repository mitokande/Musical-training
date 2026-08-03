<?php

namespace App\Console\Commands;

use App\Models\ZoomHost;
use App\Services\Zoom\ZoomClient;
use Illuminate\Console\Command;

/**
 * Pulls the licensed Zoom users on the corporate account into the host pool.
 *
 * Run by hand after buying or removing a licence — deliberately not scheduled,
 * and it never buys anything at Zoom. An admin adds the seat in the Zoom portal,
 * then syncs here.
 */
class SyncZoomHosts extends Command
{
    protected $signature = 'zoom:sync-hosts';

    protected $description = 'Sync licensed Zoom users into the live-lesson host pool';

    public function handle(ZoomClient $zoom): int
    {
        if (! $zoom->configured()) {
            $this->error('Zoom Server-to-Server OAuth credentials are not configured.');

            return self::FAILURE;
        }

        try {
            $users = $zoom->listLicensedUsers();
        } catch (\Throwable $e) {
            $this->error('Could not reach Zoom: '.$e->getMessage());

            return self::FAILURE;
        }

        foreach ($users as $user) {
            ZoomHost::updateOrCreate(
                ['zoom_user_id' => $user['id']],
                [
                    'email' => $user['email'],
                    'display_name' => $user['name'] ?: $user['email'],
                    'is_active' => true,
                    'synced_at' => now(),
                ],
            );
        }

        // A licence that disappeared from Zoom must stop being handed out, but
        // its rows stay so past lessons keep their history.
        $removed = ZoomHost::active()
            ->whereNotIn('zoom_user_id', array_column($users, 'id'))
            ->update(['is_active' => false]);

        $this->info(sprintf('Synced %d licensed host(s); deactivated %d.', count($users), $removed));

        return self::SUCCESS;
    }
}
