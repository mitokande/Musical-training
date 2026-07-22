<?php

namespace App\Providers;

use App\Models\SystemSetting;
use App\Models\User;
use App\Services\Teacher\TeacherCapabilityService;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Stripe\StripeClient;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Single shared Stripe API client, built lazily from config so it is only
        // constructed when the Stripe gateway is actually used. Tests swap this
        // binding for a fake to drive the integration without network calls.
        $this->app->singleton(StripeClient::class, function () {
            return new StripeClient([
                'api_key' => (string) config('services.stripe.secret'),
                'stripe_version' => config('services.stripe.api_version'),
            ]);
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Throttle Email Center sends below the SES account MaxSendRate.
        RateLimiter::for('email-center-send', function () {
            $perSecond = (int) rescue(
                fn () => SystemSetting::get('email_send_rate', config('email-center.send_rate_per_second')),
                config('email-center.send_rate_per_second'),
                false
            );

            return Limit::perSecond(max(1, $perSecond));
        });

        // Teacher CRM: a teacher may see a student's private performance data
        // only with an active mutually-approved relationship AND the premium
        // capability. Admins may access for support/moderation purposes.
        Gate::define('view-student-data', function (User $teacher, User $student) {
            if ($teacher->isAdmin()) {
                return true;
            }

            return app(TeacherCapabilityService::class)->canViewStudentAnalytics($teacher)
                && $teacher->hasActiveStudent($student->id);
        });
    }
}
