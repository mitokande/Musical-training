<?php

use App\Services\Teacher\TeacherSubscriptionBenefitService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// --- Email Center ---

// Drain the database queue without a dedicated worker process.
Schedule::command('queue:work --stop-when-empty --max-time=55 --tries=3')
    ->everyMinute()
    ->withoutOverlapping();

// Start due scheduled campaigns / finalize drained ones.
Schedule::command('email:process-campaigns')->everyMinute()->withoutOverlapping();

// Queue due automation emails (welcome, reminders, weekly digest...).
Schedule::command('email:run-automations')->everyFifteenMinutes()->withoutOverlapping();

// Pull new support@harmoniva.app mail from the local Dovecot mailbox.
Schedule::command('support:fetch-mail')->everyFiveMinutes()->withoutOverlapping();

// --- Teacher CRM ---

// Expire teacher premium incentive benefits whose end date has passed.
Schedule::call(function () {
    app(TeacherSubscriptionBenefitService::class)->expireLapsedBenefits();
})->daily()->name('teacher:expire-benefits')->withoutOverlapping();

// --- Paid subscriptions ---

// Expire paid subscriptions whose period has ended (downgrades lapsed users).
Schedule::command('subscriptions:expire')->hourly()->withoutOverlapping();

// --- Ad Studio ---

// Build and render pending ad creatives. NOT on the application queue: the
// worker above runs with Laravel's default 60s per-job timeout, and a 1080x1920
// 30s render takes ~3 minutes on this box — it would be killed mid-frame, and
// while it ran it would hold the queue lock that transactional email needs. Its
// own schedule entry has no per-job timeout and its own overlap lock, so a long
// render delays nothing but the next render.
Schedule::command('ads:process-queue')
    ->everyMinute()
    ->withoutOverlapping(30)
    ->runInBackground();
