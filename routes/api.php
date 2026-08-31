<?php

use App\Http\Controllers\Api\V1\AiController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\BillingController;
use App\Http\Controllers\Api\V1\CatalogController;
use App\Http\Controllers\Api\V1\ExercisePlanController;
use App\Http\Controllers\Api\V1\PracticeSessionController;
use App\Http\Controllers\Api\V1\ProfileController;
use App\Http\Controllers\Api\V1\StatsController;
use App\Http\Controllers\Webhooks\AdaptyWebhookController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Mobile API (v1)
|--------------------------------------------------------------------------
|
| Consumed by the Expo app in /home/harmoniva/mobileapp. Token-authenticated
| with Sanctum — no session, no CSRF.
|
| Everything is namespaced under /api/v1 on purpose: api routes register
| BEFORE web routes, so any URI overlapping the legacy /api/practice/... and
| /api/learning-path/... routes in web.php would silently shadow them and
| break the Livewire practice pages. RouteCollisionTest guards this.
|
*/

Route::prefix('v1')->group(function () {
    // Adapty's server-side events for the mobile app's store subscriptions.
    // Unauthenticated in the Sanctum sense — Adapty is a server, not a signed-in
    // person, so it presents the shared secret configured in the dashboard and
    // AdaptyWebhookController checks it. It lives on the api surface because
    // that is the URL registered in the Adapty workspace; the equivalent
    // /webhooks/adapty (registered in web.php next to Stripe and SES) points at
    // the same controller and the same idempotency ledger, so whichever one a
    // delivery arrives on, a duplicate is still collapsed.
    //
    // Deliberately un-throttled: the store can fan out a burst of renewals at a
    // billing boundary, and a rejected delivery is a customer whose Premium
    // arrives late.
    Route::post('adapty/events', [AdaptyWebhookController::class, 'events'])
        ->name('webhooks.adapty.api');

    Route::prefix('auth')->middleware('throttle:api-auth')->group(function () {
        Route::post('register', [AuthController::class, 'register']);
        Route::post('login', [AuthController::class, 'login']);
        Route::post('forgot-password', [AuthController::class, 'forgotPassword']);
    });

    Route::middleware(['auth:sanctum', 'throttle:api'])->group(function () {
        Route::prefix('auth')->group(function () {
            Route::get('me', [AuthController::class, 'me']);
            Route::post('logout', [AuthController::class, 'logout']);
            Route::post('logout-all', [AuthController::class, 'logoutAll']);
            Route::post('email/resend', [AuthController::class, 'resendVerification']);
            Route::get('email/status', [AuthController::class, 'verificationStatus']);
            Route::put('password', [AuthController::class, 'changePassword']);
        });

        Route::prefix('catalog')->group(function () {
            Route::get('practice-types', [CatalogController::class, 'practiceTypes']);
            Route::get('learning-path', [CatalogController::class, 'learningPath']);
            Route::get('learning-path/{slug}', [CatalogController::class, 'learningPathExercise']);
        });

        Route::prefix('sessions')->group(function () {
            Route::post('/', [PracticeSessionController::class, 'store']);
            Route::get('/', [PracticeSessionController::class, 'index']);
            Route::get('{uuid}', [PracticeSessionController::class, 'show']);
            Route::post('{uuid}/answers', [PracticeSessionController::class, 'answer'])
                ->middleware('throttle:api-answer');
            Route::post('{uuid}/complete', [PracticeSessionController::class, 'complete']);
            Route::delete('{uuid}', [PracticeSessionController::class, 'abandon']);
        });

        // AI Coach and the music assistant. Entitlement is enforced in the
        // controller from the plan matrix — `ai_coach` (premium-only, same as
        // the web route gate) and the `ask_ai_daily` allowance. The throttles
        // here are the separate, infrastructural guard against a client in a
        // loop — a plan costs far more than an ordinary request, and neither is
        // something a person can legitimately do at `throttle:api` speed.
        Route::prefix('ai')->group(function () {
            Route::get('coach/plan', [AiController::class, 'coachPlan']);
            Route::post('coach/plan', [AiController::class, 'generateCoachPlan'])
                ->middleware('throttle:ai-generate');
            Route::get('chat/quota', [AiController::class, 'chatQuota']);
            Route::post('chat', [AiController::class, 'chat'])
                ->middleware('throttle:ai-generate');
        });

        // Saved practice plans, the same rows the website's Exercise Setup
        // Studio writes. The allowance is a plan feature, enforced in the
        // controller like the AI gates rather than by middleware, so the app
        // gets a 403 it can explain instead of a bare rejection.
        Route::prefix('plans')->group(function () {
            Route::get('/', [ExercisePlanController::class, 'index']);
            Route::post('/', [ExercisePlanController::class, 'store']);
            Route::put('{id}', [ExercisePlanController::class, 'update']);
            Route::delete('{id}', [ExercisePlanController::class, 'destroy']);
        });

        Route::prefix('me')->group(function () {
            Route::get('dashboard', [StatsController::class, 'dashboard']);
            Route::get('stats', [StatsController::class, 'stats']);
            Route::get('plan', [StatsController::class, 'plan']);
            Route::put('profile', [ProfileController::class, 'update']);
            // Claims an Adapty profile for this account. Only needed for a
            // purchase made during onboarding, before the account existed —
            // every later event identifies its own user. Grants nothing on its
            // own: it replays what the webhook already recorded.
            Route::post('billing/adapty', [BillingController::class, 'linkAdapty']);
            // In-app account deletion (Play Store / App Store requirement).
            // Rate-limited like the auth endpoints: it takes a password.
            Route::delete('account', [ProfileController::class, 'destroy'])
                ->middleware('throttle:api-auth');
        });
    });
});
