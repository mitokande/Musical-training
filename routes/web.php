<?php

use App\Http\Controllers\AIController;
use App\Http\Controllers\AiChatController;
use App\Http\Controllers\AiCoachController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\SingleNotePracticeController;
use App\Http\Controllers\Admin\IntervalDirectionPracticeController;
use App\Http\Controllers\Admin\IntervalComparisonPracticeController;
use App\Http\Controllers\Admin\MelodicIntervalPracticeController;
use App\Http\Controllers\Admin\HarmonicIntervalPracticeController;
use App\Http\Controllers\Admin\IntervalConstructionPracticeController;
use App\Http\Controllers\Admin\ArticleApprovalController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\CrmNoteController;
use App\Http\Controllers\Admin\MessageController;
use App\Http\Controllers\Admin\PlanController;
use App\Http\Controllers\Admin\SubscriptionController;
use App\Http\Controllers\Admin\InvoiceController;
use App\Http\Controllers\Admin\CouponController;
use App\Http\Controllers\Admin\ContentController;
use App\Http\Controllers\Admin\ContentCategoryController;
use App\Http\Controllers\Admin\ExerciseController;
use App\Http\Controllers\Admin\ExerciseCategoryController;
use App\Http\Controllers\Admin\AiCoachAdminController;
use App\Http\Controllers\Admin\CrmTaskController;
use App\Http\Controllers\Admin\AppointmentController;
use App\Http\Controllers\Admin\CalendarController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\MusicTheoryApiController;
use App\Http\Controllers\Admin\PianoStudioController;
use App\Http\Controllers\Admin\GameController;
use App\Http\Controllers\GameController as MusicGameController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExerciseSetupController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\LearningPathController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PracticeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SchoolProfileController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\TeacherProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/pricing/teachers-and-schools', function () {
    return view('pricing-teachers');
})->name('pricing.teachers');

// ── Public static pages ────────────────────────────────────────────────────

// Pricing
Route::get('/pricing', fn () => view('pages.pricing'))->name('pricing.index');

// Solutions
Route::get('/students',       fn () => view('pages.students'))->name('page.students');
Route::get('/teachers',       fn () => view('pages.teachers-solution'))->name('page.teachers-solution');
Route::get('/schools',        fn () => view('pages.schools'))->name('page.schools');
Route::get('/piano-learners', fn () => view('pages.piano-learners'))->name('page.piano-learners');
Route::get('/request-demo',   fn () => view('pages.request-demo'))->name('page.request-demo');

// Resources
Route::get('/help',               fn () => view('pages.help'))->name('page.help');
Route::get('/faq',                fn () => view('pages.faq'))->name('page.faq');
Route::get('/blog',               fn () => view('pages.articles'))->name('page.articles');
Route::get('/ear-training-guide', fn () => view('pages.ear-training-guide'))->name('page.ear-training-guide');
Route::get('/music-theory-basics',fn () => view('pages.music-theory-basics'))->name('page.music-theory-basics');
Route::get('/contact',            fn () => view('pages.contact'))->name('page.contact');

// Company
Route::get('/about',    fn () => view('pages.about'))->name('page.about');
Route::get('/press',    fn () => view('pages.press'))->name('page.press');
Route::get('/partners', fn () => view('pages.partners'))->name('page.partners');

// Legal
Route::get('/privacy-policy',    fn () => view('pages.privacy-policy'))->name('page.privacy-policy');
Route::get('/terms-of-service',  fn () => view('pages.terms-of-service'))->name('page.terms-of-service');
Route::get('/cookie-policy',     fn () => view('pages.cookie-policy'))->name('page.cookie-policy');
Route::get('/subscription-terms',fn () => view('pages.subscription-terms'))->name('page.subscription-terms');
Route::get('/refund-policy',     fn () => view('pages.refund-policy'))->name('page.refund-policy');
Route::get('/childrens-privacy', fn () => redirect('/privacy-policy#childrens-privacy'))->name('page.childrens-privacy');

// ── End public static pages ───────────────────────────────────────────────

Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/learn', [PageController::class, 'learnView'])->middleware(['auth', 'verified'])->name('learn');
Route::get('/progress', [PageController::class, 'progressView'])->middleware(['auth', 'verified'])->name('progress');

Route::get('/ai-exercises', [PageController::class, 'aiExercisesView'])->middleware(['auth', 'verified'])->name('ai.exercises');
Route::post('/ai-exercises/generate', [AIController::class, 'generatePractices'])->middleware(['auth', 'verified', 'throttle:10,1'])->name('ai.generate-practices');

Route::get('/piano-studio', [PageController::class, 'pianoStudioView'])->name('piano.studio');


Route::post('/api/practice/check-answer', [PracticeController::class, 'checkAnswer'])->name('api.practice.check-answer');

// Learning Path Exercise routes
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/learn-exercise/{slug}',        [LearningPathController::class, 'show'])->name('learning-path.show');
    Route::post('/learn-exercise/{slug}/start', [LearningPathController::class, 'start'])->name('learning-path.start');
});
Route::post('/api/learning-path/check-answer', [LearningPathController::class, 'checkAnswer'])->middleware(['auth'])->name('api.learning-path.check-answer');


Route::get('/practice/{slug}', [PageController::class, 'practiceView'])->middleware(['track.exercise'])->name('practice');

// Exercise Setup Studio
Route::get('/exercise-setup', [ExerciseSetupController::class, 'index'])->name('exercise-setup.index');
Route::post('/exercise-setup/launch', [ExerciseSetupController::class, 'launch'])->name('exercise-setup.launch');
Route::middleware(['auth', 'verified'])->group(function () {
    Route::post('/exercise-setup/templates', [ExerciseSetupController::class, 'saveTemplate'])->name('exercise-setup.templates.store');
    Route::delete('/exercise-setup/templates/{template}', [ExerciseSetupController::class, 'deleteTemplate'])->name('exercise-setup.templates.destroy');
    Route::patch('/exercise-setup/templates/{template}/favorite', [ExerciseSetupController::class, 'toggleFavorite'])->name('exercise-setup.templates.favorite');
    Route::post('/exercise-setup/ai-recommend', [ExerciseSetupController::class, 'aiRecommend'])->name('exercise-setup.ai-recommend')->middleware('throttle:5,1');
});
Route::get('/practice-mixed', [PageController::class, 'practiceMixedView'])->middleware(['auth', 'verified'])->name('practice.mixed');
Route::post('/practice-mixed/start', [PageController::class, 'startMixedPractice'])->middleware(['auth', 'verified'])->name('practice.mixed.start');
Route::get('/practice-ai', [PageController::class, 'aiPracticeView'])->middleware(['auth', 'verified'])->name('practice.ai');

// Music Games
Route::get('/games', [MusicGameController::class, 'index'])->name('games.index');
Route::get('/games/{slug}', [MusicGameController::class, 'show'])->name('games.show');
Route::post('/games/{slug}/score', [MusicGameController::class, 'storeScore'])->middleware(['auth', 'verified'])->name('games.score');

// TEMPORARY: preview the current user's interval accuracy multipliers.
// Remove once the adaptive-practice feature consumes these stats directly.
Route::get('/dev/interval-stats', function () {
    return view('dev.interval-stats', [
        'stats' => auth()->user()->intervalAccuracyMultipliers(),
    ]);
})->middleware(['auth', 'verified'])->name('dev.interval-stats');
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/avatar', [ProfileController::class, 'updateAvatar'])->name('profile.avatar.update');
    Route::post('/profile/suspend', [ProfileController::class, 'toggleSuspend'])->name('profile.suspend');
    Route::get('/profile/music', [ProfileController::class, 'editExtendedProfile'])->name('profile.extended');
    Route::put('/profile/music', [ProfileController::class, 'updateExtendedProfile'])->name('profile.extended.update');
    Route::get('/profile/questionnaire', [ProfileController::class, 'showQuestionnaire'])->name('profile.questionnaire');
    Route::post('/profile/questionnaire', [ProfileController::class, 'storeQuestionnaire'])->name('profile.questionnaire.store');
});

Route::get('/api/ai/generate-interval-direction-practice', [AIController::class, 'generateIntervalDirectionPractice'])->middleware(['auth', 'throttle:10,1'])->name('api.ai.generate-interval-direction-practice');

// Teacher Routes
Route::middleware(['auth', 'teacher'])->prefix('teacher')->name('teacher.')->group(function () {
    Route::get('/profile', [TeacherProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [TeacherProfileController::class, 'update'])->name('profile.update');
});

// School Routes
Route::middleware(['auth', 'school'])->prefix('school')->name('school.')->group(function () {
    Route::get('/profile', [SchoolProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [SchoolProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/logo', [SchoolProfileController::class, 'updateLogo'])->name('logo.update');
});

// Article Routes (teachers and schools)
Route::middleware(['auth', 'teacher'])->group(function () {
    Route::resource('articles', ArticleController::class)->except(['show']);
});

// Teacher/School Dashboard Aliases
Route::get('/teacher/dashboard', fn() => redirect()->route('dashboard'))->middleware(['auth', 'teacher'])->name('teacher.dashboard');
Route::get('/school/dashboard', fn() => redirect()->route('dashboard'))->middleware(['auth', 'school'])->name('school.dashboard');

// Admin Routes
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');
    
    Route::resource('single-note', SingleNotePracticeController::class)->except(['show']);
    Route::put('single-note-settings', [SingleNotePracticeController::class, 'updateSettings'])->name('single-note.settings');
    
    Route::resource('interval-direction', IntervalDirectionPracticeController::class)->except(['show']);
    Route::put('interval-direction-settings', [IntervalDirectionPracticeController::class, 'updateSettings'])->name('interval-direction.settings');
    
    Route::resource('interval-comparison', IntervalComparisonPracticeController::class)->except(['show']);
    Route::put('interval-comparison-settings', [IntervalComparisonPracticeController::class, 'updateSettings'])->name('interval-comparison.settings');
    
    Route::resource('melodic-interval', MelodicIntervalPracticeController::class)->except(['show']);
    Route::put('melodic-interval-settings', [MelodicIntervalPracticeController::class, 'updateSettings'])->name('melodic-interval.settings');
    
    Route::resource('harmonic-interval', HarmonicIntervalPracticeController::class)->except(['show']);
    Route::put('harmonic-interval-settings', [HarmonicIntervalPracticeController::class, 'updateSettings'])->name('harmonic-interval.settings');
    
    Route::resource('interval-construction', IntervalConstructionPracticeController::class)->except(['show']);
    Route::put('interval-construction-settings', [IntervalConstructionPracticeController::class, 'updateSettings'])->name('interval-construction.settings');

    // Music Theory API — auto-recalculate and validate endpoints for admin edit forms
    Route::get('api/recalculate-interval', [MusicTheoryApiController::class, 'recalculate'])->name('api.recalculate-interval');
    Route::post('api/validate-question', [MusicTheoryApiController::class, 'validateQuestion'])->name('api.validate-question');
    Route::get('api/validate-all', [MusicTheoryApiController::class, 'validateAll'])->name('api.validate-all');

    // Bulk validate/repair pages
    Route::get('exercises/validate', [\App\Http\Controllers\Admin\ExerciseValidationController::class, 'index'])->name('exercises.validate');
    Route::post('exercises/repair', [\App\Http\Controllers\Admin\ExerciseValidationController::class, 'repair'])->name('exercises.repair');
    
    // Enhanced Users (must be before resource route)
    Route::get('users/segments', [UserController::class, 'segments'])->name('users.segments');
    Route::get('users/export', [UserController::class, 'export'])->name('users.export');

    Route::resource('users', UserController::class);

    // CRM Notes
    Route::post('users/{user}/notes', [CrmNoteController::class, 'store'])->name('notes.store');
    Route::put('notes/{note}', [CrmNoteController::class, 'update'])->name('notes.update');
    Route::delete('notes/{note}', [CrmNoteController::class, 'destroy'])->name('notes.destroy');
    Route::patch('notes/{note}/pin', [CrmNoteController::class, 'togglePin'])->name('notes.togglePin');

    // Exercises overview + categories
    Route::get('exercises', [ExerciseController::class, 'index'])->name('exercises.index');
    Route::resource('exercise-categories', ExerciseCategoryController::class);

    // Learning Path Exercises
    Route::post('learning-path-exercises/{learningPathExercise}/duplicate', [\App\Http\Controllers\Admin\LearningPathExerciseController::class, 'duplicate'])->name('learning-path-exercises.duplicate');
    Route::patch('learning-path-exercises/{learningPathExercise}/toggle-status', [\App\Http\Controllers\Admin\LearningPathExerciseController::class, 'toggleStatus'])->name('learning-path-exercises.toggle-status');
    Route::resource('learning-path-exercises', \App\Http\Controllers\Admin\LearningPathExerciseController::class)->except(['show']);

    // Content Library
    Route::resource('content', ContentController::class);
    Route::post('content/{article}/approve', [ContentController::class, 'approve'])->name('content.approve');
    Route::post('content/{article}/reject', [ContentController::class, 'reject'])->name('content.reject');
    Route::resource('content-categories', ContentCategoryController::class)->except(['show']);

    // Payments
    Route::resource('plans', PlanController::class);
    Route::resource('subscriptions', SubscriptionController::class)->except(['create', 'store']);
    Route::resource('invoices', InvoiceController::class)->except(['create', 'store']);
    Route::resource('coupons', CouponController::class);

    // AI Coach Admin
    Route::get('ai-coach-admin', [AiCoachAdminController::class, 'index'])->name('ai-coach-admin.index');
    Route::get('ai-coach-admin/users/{user}', [AiCoachAdminController::class, 'userProfile'])->name('ai-coach-admin.user');
    Route::get('ai-coach-admin/settings', [AiCoachAdminController::class, 'settings'])->name('ai-coach-admin.settings');
    Route::put('ai-coach-admin/settings', [AiCoachAdminController::class, 'updateSettings'])->name('ai-coach-admin.settings.update');

    // CRM Tasks
    Route::resource('tasks', CrmTaskController::class);

    // Messages
    Route::resource('messages', MessageController::class)->except(['edit']);
    Route::post('messages/{message}/reply', [MessageController::class, 'reply'])->name('messages.reply');
    Route::patch('messages/{message}/read', [MessageController::class, 'markRead'])->name('messages.read');
    Route::patch('messages/{message}/archive', [MessageController::class, 'archive'])->name('messages.archive');

    // Calendar
    Route::get('calendar', [CalendarController::class, 'index'])->name('calendar.index');
    Route::get('calendar/events', [CalendarController::class, 'events'])->name('calendar.events');
    Route::resource('appointments', AppointmentController::class)->except(['index']);

    // Reports
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('members', [ReportController::class, 'members'])->name('members');
        Route::get('revenue', [ReportController::class, 'revenue'])->name('revenue');
        Route::get('subscriptions', [ReportController::class, 'subscriptions'])->name('subscriptions');
        Route::get('exercises', [ReportController::class, 'exercises'])->name('exercises');
        Route::get('ai-coach', [ReportController::class, 'aiCoach'])->name('ai-coach');
        Route::get('content', [ReportController::class, 'content'])->name('content');
        Route::get('export/{type}', [ReportController::class, 'export'])->name('export');
    });

    // Settings
    Route::get('settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::put('settings', [SettingsController::class, 'update'])->name('settings.update');
    Route::get('settings/activity-log', [SettingsController::class, 'activityLog'])->name('settings.activity-log');

    // Piano Studio & Games
    Route::get('piano-studio/settings', [PianoStudioController::class, 'settings'])->name('piano-studio.settings');
    Route::put('piano-studio/settings', [PianoStudioController::class, 'updateSettings'])->name('piano-studio.settings.update');
    Route::get('games', [GameController::class, 'index'])->name('games.index');
    Route::put('games/settings', [GameController::class, 'updateSettings'])->name('games.settings.update');

    // Article Approval
    Route::get('articles', [ArticleApprovalController::class, 'index'])->name('articles.index');
    Route::get('articles/{article}', [ArticleApprovalController::class, 'show'])->name('articles.show');
    Route::post('articles/{article}/approve', [ArticleApprovalController::class, 'approve'])->name('articles.approve');
    Route::post('articles/{article}/reject', [ArticleApprovalController::class, 'reject'])->name('articles.reject');
});

// AI Coach
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/ai-coach', [AiCoachController::class, 'index'])->name('ai-coach.index');
    Route::post('/ai-coach/generate', [AiCoachController::class, 'generate'])->name('ai-coach.generate');
});

// AI Chat
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/ai-chat', [AiChatController::class, 'index'])->name('ai-chat.index');
    Route::post('/ai-chat/send', [AiChatController::class, 'send'])->name('ai-chat.send');
    Route::post('/ai-chat/clear', [AiChatController::class, 'clear'])->name('ai-chat.clear');
});

// Search
Route::get('/search', [SearchController::class, 'index'])->name('search');

// Language switch
Route::post('/language/switch', [LanguageController::class, 'switch'])->name('language.switch');

require __DIR__.'/auth.php';
