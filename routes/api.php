<?php

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

        Route::prefix('me')->group(function () {
            Route::get('dashboard', [StatsController::class, 'dashboard']);
            Route::get('stats', [StatsController::class, 'stats']);
            Route::get('plan', [StatsController::class, 'plan']);
            Route::put('profile', [ProfileController::class, 'update']);
        });
    });
});
