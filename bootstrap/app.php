<?php

use App\Exceptions\Api\ApiException;
use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\CheckPlanFeature;
use App\Http\Middleware\CheckUserRestriction;
use App\Http\Middleware\EnsureApiUserActive;
use App\Http\Middleware\ForceDefaultLocaleForPublicPages;
use App\Http\Middleware\SchoolMiddleware;
use App\Http\Middleware\SetApiLocale;
use App\Http\Middleware\SetLocale;
use App\Http\Middleware\ShareSeoContext;
use App\Http\Middleware\TeacherMiddleware;
use App\Http\Middleware\TrackExerciseUsage;
use App\Services\Analytics\PostHogService;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Exceptions\PostTooLargeException;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        // Mobile app surface. Everything lives under /api/v1/* so it cannot
        // shadow the three legacy /api/* routes declared in web.php — api
        // routes are registered *before* web routes, so an overlapping URI
        // would silently steal them (and break the Livewire practice pages,
        // which need the session those web routes read).
        api: __DIR__.'/../routes/api.php',
        apiPrefix: 'api',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin' => AdminMiddleware::class,
            'teacher' => TeacherMiddleware::class,
            'school' => SchoolMiddleware::class,
            'plan' => CheckPlanFeature::class,
            'track.exercise' => TrackExerciseUsage::class,
            'check.restriction' => CheckUserRestriction::class,
        ]);
        // A language code is not a secret, and this cookie rides along on every
        // request to the domain — assets included. Encrypted it would be ~300
        // bytes of Laravel payload; in the clear it is ~20. SetLocale validates
        // the value against its supported-locale list on every read, so a
        // tampered cookie can only ever select a language the site already ships.
        $middleware->encryptCookies(except: [
            SetLocale::LOCALE_COOKIE,
        ]);
        $middleware->web(append: [
            SetLocale::class,
            // Runs after SetLocale to pin the un-prefixed form of a localized
            // public page (e.g. /contact vs /de/contact) to the English
            // x-default, so crawlers never see it self-canonicalise to /de.
            ForceDefaultLocaleForPublicPages::class,
            CheckUserRestriction::class,
            // Canonical/hreflang/<html lang> for the URL being served, shared
            // with every view. Last, so it sees the locale the two middlewares
            // above settled on.
            ShareSeoContext::class,
        ]);
        // The api group has no session, so SetLocale/CheckUserRestriction
        // (which redirect) cannot be reused — these are their stateless twins.
        $middleware->api(append: [
            SetApiLocale::class,
            EnsureApiUserActive::class,
        ]);
        // AWS SNS posts signed JSON (validated in SesWebhookController);
        // Stripe posts signature-verified events (StripeWebhookController);
        // unsubscribe POST is the RFC 8058 one-click coming from mail clients.
        $middleware->validateCsrfTokens(except: [
            'webhooks/aws/ses/*',
            'webhooks/stripe',
            'email/unsubscribe/*',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Ship exceptions to PostHog error tracking. Laravel only runs reportable
        // callbacks for exceptions that pass shouldReport(), so 404s, validation
        // failures and auth redirects are filtered out before they get here.
        // Returning nothing lets the exception continue on to the normal log stack.
        $exceptions->report(function (Throwable $e): void {
            app(PostHogService::class)->captureException($e);
        });

        // Oversized uploads (beyond post_max_size / client_max_body_size)
        // otherwise surface as a bare 413 page; send the user back to the
        // form with a readable validation-style error instead.
        // The mobile API always speaks JSON, never HTML error pages.
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson()
        );

        // Uniform error envelope for the /api/v1 surface.
        $exceptions->render(function (ApiException $e) {
            return $e->toResponse();
        });

        $exceptions->render(function (PostTooLargeException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'error' => [
                        'code' => 'payload_too_large',
                        'message' => __('The uploaded file is too large. Please choose a smaller file.'),
                    ],
                ], 413);
            }

            return back()
                ->withErrors(['upload' => __('The uploaded file is too large. Please choose a smaller file.')])
                ->withInput($request->except(['cover', 'avatar', 'file']));
        });
    })->create();
