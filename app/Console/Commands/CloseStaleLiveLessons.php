<?php

namespace App\Console\Commands;

use App\Models\LiveLesson;
use App\Services\Derslik\LiveLessonService;
use Illuminate\Console\Command;

/**
 * Closes live lessons nobody ended within services.derslik.lesson_hours and
 * deletes their Derslik rooms, so forgotten lessons do not leave open rooms
 * (and working links) behind. Scheduled hourly in routes/console.php.
 */
class CloseStaleLiveLessons extends Command
{
    protected $signature = 'derslik:close-stale-lessons';

    protected $description = 'Close live lessons past their lesson window and delete their Derslik rooms';

    public function handle(LiveLessonService $lessons): int
    {
        $count = 0;

        LiveLesson::stale()->orderBy('id')->each(function (LiveLesson $lesson) use ($lessons, &$count) {
            $lessons->end($lesson);
            $count++;
        });

        $this->info("Closed {$count} stale live lesson(s).");

        return self::SUCCESS;
    }
}
