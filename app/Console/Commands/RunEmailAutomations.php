<?php

namespace App\Console\Commands;

use App\Services\EmailCenter\AutomationEngine;
use Illuminate\Console\Command;

class RunEmailAutomations extends Command
{
    protected $signature = 'email:run-automations';

    protected $description = 'Queue due Email Center automation emails (welcome, reminders, digests)';

    public function handle(AutomationEngine $engine): int
    {
        $results = $engine->run();

        foreach ($results as $key => $count) {
            $this->line("{$key}: {$count} queued");
        }

        if ($results === []) {
            $this->line('No automations enabled.');
        }

        return self::SUCCESS;
    }
}
