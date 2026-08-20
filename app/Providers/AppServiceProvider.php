<?php

namespace App\Providers;

use App\Listeners\RecordAuthAnalytics;
use App\Models\SystemSetting;
use App\Models\User;
use App\Services\Ai\ChatCompletionClient;
use App\Services\Ai\OpenAiChatCompletionClient;
use App\Services\Analytics\PostHogService;
use App\Services\Teacher\TeacherCapabilityService;
use App\Services\Zoom\ZoomClient;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Events\Verified;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
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

        // The one seam between the AI features and OpenAI. Bound rather than
        // constructed inline (as the web AI controllers do) so the mobile AI
        // endpoints can be tested without an API key or a network call.
        $this->app->bind(ChatCompletionClient::class, OpenAiChatCompletionClient::class);

        // Shared PostHog client. Held as a singleton so the underlying SDK is
        // initialised at most once per process and the batched event queue is
        // flushed a single time when the request ends.
        $this->app->singleton(PostHogService::class);

        // Single shared Zoom REST client, so the Server-to-Server OAuth token
        // is fetched at most once per process. Tests swap this binding for a
        // fake to drive live lessons without network calls.
        $this->app->singleton(ZoomClient::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Funnel analytics: signup, login and verification go to PostHog from
        // the server so ad-blockers cannot silently drop the top of the funnel.
        Event::listen(Registered::class, [RecordAuthAnalytics::class, 'registered']);
        Event::listen(Login::class, [RecordAuthAnalytics::class, 'login']);
        Event::listen(Verified::class, [RecordAuthAnalytics::class, 'verified']);

        // Mobile API limiters. The default `api` group ships with no limiter at
        // all, and answer submission needs a far higher ceiling than login.
        RateLimiter::for('api', fn (Request $request) => Limit::perMinute(120)
            ->by($request->user()?->id ?: $request->ip()));
        RateLimiter::for('api-auth', fn (Request $request) => Limit::perMinute(20)->by($request->ip()));
        RateLimiter::for('api-answer', fn (Request $request) => Limit::perMinute(240)
            ->by($request->user()?->id ?: $request->ip()));
        // Model calls are billed per request, so they get their own ceiling well
        // under `api`. This is the runaway-client guard, not the product limit —
        // that is `ask_ai_daily` in the plan matrix, enforced in AiController.
        RateLimiter::for('ai-generate', fn (Request $request) => Limit::perMinute(6)
            ->by($request->user()?->id ?: $request->ip()));

        // Throttle Email Center sends below the SES account MaxSendRate.
        RateLimiter::for('email-center-send', function () {
            $perSecond = (int) rescue(
                fn () => SystemSetting::get('email_send_rate', config('email-center.send_rate_per_second')),
                config('email-center.send_rate_per_second'),
                false
            );

            return Limit::perSecond(max(1, $perSecond));
        });

        // Zoom rate-limits per endpoint; keep our own calls well under it.
        RateLimiter::for('zoom-api', fn () => Limit::perSecond(max(1, (int) config('zoom.api_rate_per_second'))));

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
