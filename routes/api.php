<?php

use App\Http\Controllers\Api\V1\AiController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CatalogController;
use App\Http\Controllers\Api\V1\PracticeSessionController;
use App\Http\Controllers\Api\V1\ProfileController;
use App\Http\Controllers\Api\V1\StatsController;
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

        Route::prefix('me')->group(function () {
            Route::get('dashboard', [StatsController::class, 'dashboard']);
            Route::get('stats', [StatsController::class, 'stats']);
            Route::get('plan', [StatsController::class, 'plan']);
            Route::put('profile', [ProfileController::class, 'update']);
            // In-app account deletion (Play Store / App Store requirement).
            // Rate-limited like the auth endpoints: it takes a password.
            Route::delete('account', [ProfileController::class, 'destroy'])
                ->middleware('throttle:api-auth');
        });
    });
});
