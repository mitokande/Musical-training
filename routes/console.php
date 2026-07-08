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
