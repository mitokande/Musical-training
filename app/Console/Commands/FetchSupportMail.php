<?php

namespace App\Console\Commands;

use App\Services\EmailCenter\SupportMailFetcher;
use Illuminate\Console\Command;

class FetchSupportMail extends Command
{
    protected $signature = 'support:fetch-mail';

    protected $description = 'Import new support@harmoniva.app mail from the local IMAP mailbox into the admin Support Inbox';

    public function handle(SupportMailFetcher $fetcher): int
    {
        if (config('email-center.support_inbound_mode') !== 'imap') {
            $this->line('Support inbound mode is not "imap" — skipping.');

            return self::SUCCESS;
        }

        $count = $fetcher->fetch();
        $this->line("Imported {$count} new support message(s).");

        return self::SUCCESS;
    }
}
