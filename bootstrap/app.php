<?php

use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\CheckPlanFeature;
use App\Http\Middleware\CheckUserRestriction;
use App\Http\Middleware\SchoolMiddleware;
use App\Http\Middleware\SetLocale;
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
        $middleware->web(append: [
            SetLocale::class,
            CheckUserRestriction::class,
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
        $exceptions->render(function (PostTooLargeException $e, Request $request) {
            return back()
                ->withErrors(['upload' => __('The uploaded file is too large. Please choose a smaller file.')])
                ->withInput($request->except(['cover', 'avatar', 'file']));
        });
    })->create();
