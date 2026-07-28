<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\AiCoachAdminController;
use App\Http\Controllers\Admin\AiUsageController;
use App\Http\Controllers\Admin\AppointmentController;
use App\Http\Controllers\Admin\CalendarController;
use App\Http\Controllers\Admin\CommunityController;
use App\Http\Controllers\Admin\ContentCategoryController;
use App\Http\Controllers\Admin\ContentController;
use App\Http\Controllers\Admin\CouponController;
use App\Http\Controllers\Admin\CrmNoteController;
use App\Http\Controllers\Admin\CrmTaskController;
use App\Http\Controllers\Admin\EmailAutomationController;
use App\Http\Controllers\Admin\EmailCampaignController;
use App\Http\Controllers\Admin\EmailCenterController;
use App\Http\Controllers\Admin\EmailTemplateController;
use App\Http\Controllers\Admin\ExerciseCategoryController;
use App\Http\Controllers\Admin\ExerciseController;
use App\Http\Controllers\Admin\ExerciseValidationController;
use App\Http\Controllers\Admin\GameController;
use App\Http\Controllers\Admin\HarmonicIntervalPracticeController;
use App\Http\Controllers\Admin\IntervalComparisonPracticeController;
use App\Http\Controllers\Admin\IntervalConstructionPracticeController;
use App\Http\Controllers\Admin\IntervalDirectionPracticeController;
use App\Http\Controllers\Admin\InvoiceController;
use App\Http\Controllers\Admin\LearningPathExerciseController;
use App\Http\Controllers\Admin\MelodicIntervalPracticeController;
use App\Http\Controllers\Admin\MemberReviewController;
use App\Http\Controllers\Admin\MessageController;
use App\Http\Controllers\Admin\MusicTheoryApiController;
use App\Http\Controllers\Admin\PianoStudioController;
use App\Http\Controllers\Admin\PlanController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\SingleNotePracticeController;
use App\Http\Controllers\Admin\SubscriptionBenefitController;
use App\Http\Controllers\Admin\SubscriptionController;
use App\Http\Controllers\Admin\SupportInboxController;
use App\Http\Controllers\Admin\SystemHealthController;
use App\Http\Controllers\Admin\TeacherProfileModerationController;
use App\Http\Controllers\Admin\TeacherReviewModerationController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\AiChatController;
use App\Http\Controllers\AiCoachController;
use App\Http\Controllers\AIController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\BillingController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Dev\MidiViewerController;
use App\Http\Controllers\EmailPreferenceController;
use App\Http\Controllers\EmailTrackingController;
use App\Http\Controllers\ExerciseSetupController;
use App\Http\Controllers\GameController as MusicGameController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\LearningPathController;
use App\Http\Controllers\MyAppointmentsController;
use App\Http\Controllers\MySchoolsController;
use App\Http\Controllers\MyTeachersController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PracticeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Public\SitemapController;
use App\Http\Controllers\Public\TeacherPublicProfileController;
use App\Http\Controllers\School\SchoolTeacherController;
use App\Http\Controllers\School\SchoolTeacherInvitationController;
use App\Http\Controllers\School\SchoolTeacherRelationshipController;
use App\Http\Controllers\SchoolInvitationAcceptController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\StudentAssignmentController;
use App\Http\Controllers\StudentTeacherMessageController;
use App\Http\Controllers\Teacher\TeacherAccountController;
use App\Http\Controllers\Teacher\TeacherMessageController;
use App\Http\Controllers\TeacherBookingController;
use App\Http\Controllers\TeacherInvitationAcceptController;
use App\Http\Controllers\TeacherReviewController;
use App\Http\Controllers\TrialController;
use App\Http\Controllers\Webhooks\SesWebhookController;
use App\Http\Controllers\Webhooks\StripeWebhookController;
use App\Http\Middleware\ForceLocaleFromUrl;
use Illuminate\Support\Facades\Route;

// `/` is the English / x-default landing page. Non-English visitors (locale
// resolved by SetLocale from user/session/IP) are redirected to their locale
// URL so every landing URL always serves exactly one language — required for
// the canonical + hreflang setup in welcome.blade.php to be truthful.
Route::get('/', function () {
    $locale = app()->getLocale();

    if (in_array($locale, ['de', 'fr', 'es', 'pt', 'tr', 'it'])) {
        return redirect('/'.$locale, 302);
    }

    app()->setLocale('en');

    // The landing nav varies by auth (Login/Register vs Dashboard/Upgrade), so it
    // must never be served from a shared/browser cache to the wrong user. Cache
    // publicly for guests/crawlers only (keyed on the session cookie so a fresh
    // login busts it); authenticated users always get a fresh, uncached page.
    return response()->view('welcome')->withHeaders(auth()->check()
        ? ['Cache-Control' => 'no-store, private']
        : ['Cache-Control' => 'public, max-age=300', 'Vary' => 'Cookie']);
});

// Locale-prefixed landing pages (indexable per-language variants; `en` lives at `/`).
// Each is listed in the sitemap and cross-linked via hreflang tags in welcome.blade.php.
Route::get('/{locale}', function (string $locale) {
    app()->setLocale($locale);
    session(['locale' => $locale]);

    // See the `/` route: cache for guests only so the auth-aware nav stays correct.
    return response()->view('welcome')->withHeaders(auth()->check()
        ? ['Cache-Control' => 'no-store, private']
        : ['Cache-Control' => 'public, max-age=300', 'Vary' => 'Cookie']);
})->whereIn('locale', ['de', 'fr', 'es', 'pt', 'tr', 'it'])
    ->name('welcome.localized');

Route::get('/pricing/teachers-and-schools', function () {
    return view('pricing-teachers');
})->name('pricing.teachers');

// ── Public static pages ────────────────────────────────────────────────────

// Pricing
Route::get('/pricing', fn () => view('pages.pricing'))->name('pricing.index');

// Solutions
Route::get('/students', fn () => view('pages.students'))->name('page.students');
Route::get('/teachers', fn () => view('pages.teachers-solution'))->name('page.teachers-solution');
Route::get('/schools', fn () => view('pages.schools'))->name('page.schools');
Route::get('/piano-learners', fn () => view('pages.piano-learners'))->name('page.piano-learners');
Route::get('/community-feed', fn () => view('pages.community-feed'))->name('page.community-feed');
Route::get('/request-demo', fn () => view('pages.request-demo'))->name('page.request-demo');

// Resources
Route::get('/help', fn () => view('pages.help'))->name('page.help');
// Localized guide: Turkish visitors (locale via SetLocale) get the TR version, everyone else the EN one.
Route::get('/how-it-works', fn () => view(app()->getLocale() === 'tr' ? 'pages.how-it-works-tr' : 'pages.how-it-works'))->name('page.how-it-works');
Route::get('/faq', fn () => view('pages.faq'))->name('page.faq');
Route::get('/blog', fn () => view('pages.articles'))->name('page.articles');
Route::get('/article/{article:slug}', [ArticleController::class, 'show'])->name('articles.show');
Route::get('/ear-training-guide', fn () => view('pages.ear-training-guide'))->name('page.ear-training-guide');
Route::get('/music-theory-basics', fn () => view('pages.music-theory-basics'))->name('page.music-theory-basics');
Route::get('/contact', fn () => view('pages.contact'))->name('page.contact');

// Company
Route::get('/about', fn () => view('pages.about'))->name('page.about');
Route::get('/press', fn () => view('pages.press'))->name('page.press');
Route::get('/partners', fn () => view('pages.partners'))->name('page.partners');

// Legal
Route::get('/privacy-policy', fn () => view('pages.privacy-policy'))->name('page.privacy-policy');
Route::get('/terms-of-service', fn () => view('pages.terms-of-service'))->name('page.terms-of-service');
Route::get('/cookie-policy', fn () => view('pages.cookie-policy'))->name('page.cookie-policy');
Route::get('/subscription-terms', fn () => view('pages.subscription-terms'))->name('page.subscription-terms');
Route::get('/refund-policy', fn () => view('pages.refund-policy'))->name('page.refund-policy');
Route::get('/childrens-privacy', fn () => redirect('/privacy-policy#childrens-privacy'))->name('page.childrens-privacy');

// Locale-prefixed variants of the public template pages (/es/pricing, …).
// Indexable per-language URLs cross-linked via hreflang in layouts/standalone.
// Driven by config('locales.public_pages') so routes, sitemap, and hreflang
// never drift apart. English keeps its own un-prefixed routes above.
Route::prefix('{locale}')
    ->whereIn('locale', config('locales.prefixed'))
    ->middleware(ForceLocaleFromUrl::class)
    ->group(function () {
        // Public pages whose view needs controller-provided data: the localized
        // route must run the same controller action, not just render the view.
        // They still live in config('locales.public_pages') for hreflang/sitemap.
        $controllerPages = [
            '/find-teachers' => [TeacherPublicProfileController::class, 'index'],
            '/learn' => [PageController::class, 'learnView'],
        ];

        foreach ((array) config('locales.public_pages') as $path => $view) {
            if (isset($controllerPages[$path])) {
                Route::get($path, $controllerPages[$path]);

                continue;
            }

            Route::get($path, function () use ($view) {
                // Turkish has a dedicated hand-written how-it-works page.
                if ($view === 'pages.how-it-works' && app()->getLocale() === 'tr') {
                    return view('pages.how-it-works-tr');
                }

                return view($view);
            });
        }
    });

// ── End public static pages ───────────────────────────────────────────────

Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');

// ── Checkout & Billing (Premium purchase) ─────────────────────────────────
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/checkout', [CheckoutController::class, 'show'])->name('checkout.show');
    Route::post('/checkout', [CheckoutController::class, 'store'])->middleware('throttle:10,1')->name('checkout.store');
    Route::get('/checkout/success/{subscription}', [CheckoutController::class, 'success'])->name('checkout.success');
    Route::get('/checkout/pending/{subscription}', [CheckoutController::class, 'pending'])->name('checkout.pending');

    // Free Premium trial — no card, claimable once per account.
    Route::post('/trial/start', [TrialController::class, 'store'])->middleware('throttle:5,1')->name('trial.store');

    Route::get('/account/billing', [BillingController::class, 'index'])->name('billing.index');
    Route::post('/account/billing/{subscription}/cancel', [BillingController::class, 'cancel'])->name('billing.cancel');
});

// Learning Path overview is public (SEO + guest trial with daily session limits).
Route::get('/learn', [PageController::class, 'learnView'])->name('learn');
Route::get('/progress', [PageController::class, 'progressView'])->middleware(['auth', 'verified'])->name('progress');

// AI Exercises are premium-only (plans.*.premium.ai_exercises). Free users may
// VIEW the setup page (so they can see what Premium unlocks); the page gates the
// start action behind an upgrade banner, and the POST below is the hard backend
// gate so it can never be generated without the feature.
Route::get('/ai-exercises', [PageController::class, 'aiExercisesView'])->middleware(['auth', 'verified'])->name('ai.exercises');
Route::post('/ai-exercises/generate', [AIController::class, 'generatePractices'])->middleware(['auth', 'verified', 'plan:ai_exercises', 'throttle:10,1'])->name('ai.generate-practices');

Route::get('/piano-studio', [PageController::class, 'pianoStudioView'])->name('piano.studio');

Route::post('/api/practice/check-answer', [PracticeController::class, 'checkAnswer'])->name('api.practice.check-answer');

// Learning Path Exercise routes — public: guests get a daily session quota
// enforced server-side in the controller (UsageQuotaService).
Route::get('/learn-exercise/{slug}', [LearningPathController::class, 'show'])->name('learning-path.show');
Route::post('/learn-exercise/{slug}/start', [LearningPathController::class, 'start'])->middleware('throttle:30,1')->name('learning-path.start');
Route::post('/api/learning-path/check-answer', [LearningPathController::class, 'checkAnswer'])->middleware('throttle:120,1')->name('api.learning-path.check-answer');

Route::get('/practice/{slug}', [PageController::class, 'practiceView'])->middleware(['track.exercise'])->name('practice');

// Exercise Setup Studio
Route::get('/exercise-setup', [ExerciseSetupController::class, 'index'])->name('exercise-setup.index');
Route::post('/exercise-setup/launch', [ExerciseSetupController::class, 'launch'])->name('exercise-setup.launch');
Route::middleware(['auth', 'verified'])->group(function () {
    Route::post('/exercise-setup/templates', [ExerciseSetupController::class, 'saveTemplate'])->name('exercise-setup.templates.store');
    Route::delete('/exercise-setup/templates/{template}', [ExerciseSetupController::class, 'deleteTemplate'])->name('exercise-setup.templates.destroy');
    Route::patch('/exercise-setup/templates/{template}/favorite', [ExerciseSetupController::class, 'toggleFavorite'])->name('exercise-setup.templates.favorite');
    Route::post('/exercise-setup/ai-recommend', [ExerciseSetupController::class, 'aiRecommend'])->name('exercise-setup.ai-recommend')->middleware(['plan:ai_exercises', 'throttle:5,1']);
});
Route::get('/practice-mixed', [PageController::class, 'practiceMixedView'])->middleware(['auth', 'verified'])->name('practice.mixed');
Route::post('/practice-mixed/start', [PageController::class, 'startMixedPractice'])->middleware(['auth', 'verified'])->name('practice.mixed.start');
Route::get('/practice-ai', [PageController::class, 'aiPracticeView'])->middleware(['auth', 'verified'])->name('practice.ai');
Route::get('/practice-ai/melodic-dictation', [PageController::class, 'aiDictationPracticeView'])->middleware(['auth', 'verified'])->name('practice.ai.dictation');

// Music Games
Route::get('/games', [MusicGameController::class, 'index'])->name('games.index');
// Temporary design test pages — remove after picking a variant
Route::get('/games/a', [MusicGameController::class, 'testA']);
Route::get('/games/b', [MusicGameController::class, 'testB']);
Route::get('/games/c', [MusicGameController::class, 'testC']);
Route::get('/games/{slug}', [MusicGameController::class, 'show'])->name('games.show');
Route::post('/games/{slug}/score', [MusicGameController::class, 'storeScore'])->middleware(['auth', 'verified'])->name('games.score');
Route::post('/games/{slug}/guest-track', [MusicGameController::class, 'trackGuestPlay'])->name('games.guest-track');

// TEMPORARY: preview the current user's interval accuracy multipliers.
// Remove once the adaptive-practice feature consumes these stats directly.
Route::get('/dev/interval-stats', function () {
    return view('dev.interval-stats', [
        'stats' => auth()->user()->intervalAccuracyMultipliers(),
    ]);
})->middleware(['auth', 'verified'])->name('dev.interval-stats');

// Dev: upload + inspect .mid files (admin only)
Route::middleware(['auth', 'verified', 'admin'])->prefix('dev/midi')->name('dev.midi.')->group(function () {
    Route::get('/', [MidiViewerController::class, 'index'])->name('index');
    Route::post('/upload', [MidiViewerController::class, 'upload'])->name('upload');
    Route::get('/file/{filename}', [MidiViewerController::class, 'file'])->name('file');
    Route::delete('/{filename}', [MidiViewerController::class, 'destroy'])->name('destroy');
});
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');

    // Email preferences from the account Settings panel.
    Route::get('/account/email-preferences', [EmailPreferenceController::class, 'edit'])->name('email-preferences.edit');
    Route::put('/account/email-preferences', [EmailPreferenceController::class, 'update'])->name('email-preferences.update');
    Route::post('/profile/avatar', [ProfileController::class, 'updateAvatar'])->name('profile.avatar.update');
    Route::post('/profile/suspend', [ProfileController::class, 'toggleSuspend'])->name('profile.suspend');
    Route::get('/profile/music', [ProfileController::class, 'editExtendedProfile'])->name('profile.extended');
    Route::put('/profile/music', [ProfileController::class, 'updateExtendedProfile'])->name('profile.extended.update');
    Route::get('/profile/questionnaire', [ProfileController::class, 'showQuestionnaire'])->name('profile.questionnaire');
    Route::post('/profile/questionnaire', [ProfileController::class, 'storeQuestionnaire'])->name('profile.questionnaire.store');
});

// Social: Feed, Messaging, Public Profiles
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/feed', [PageController::class, 'feedView'])->name('feed');
    Route::get('/messages', [PageController::class, 'messagesView'])->name('messages');
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/read', [NotificationController::class, 'markAllRead'])->name('notifications.read.all');
    Route::get('/u/{username}', [PageController::class, 'publicProfile'])->name('profile.public');
});

Route::get('/api/ai/generate-interval-direction-practice', [AIController::class, 'generateIntervalDirectionPractice'])->middleware(['auth', 'throttle:10,1'])->name('api.ai.generate-interval-direction-practice');

// Public teacher & school directory + profiles (approved profiles only; owners/admins may preview)
Route::get('/find-teachers', [TeacherPublicProfileController::class, 'index'])->name('teachers.directory');
Route::get('/teachers/{slug}', [TeacherPublicProfileController::class, 'show'])->name('teachers.show')->defaults('entity', 'teacher');
Route::get('/schools/{slug}', [TeacherPublicProfileController::class, 'show'])->name('schools.show')->defaults('entity', 'school');
Route::get('/teachers/{slug}/booking', [TeacherBookingController::class, 'page'])->name('teachers.booking');
Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');

// Any registered user may open a Basic teacher account
Route::post('/teacher/become', [TeacherAccountController::class, 'store'])
    ->middleware(['auth', 'verified'])->name('teacher.become');

// Shared CRM (private) — the teacher CRM and the school panel run the exact same
// controllers/blades (routes/crm.php); only the prefix, route names and skin differ.
// Teacher access: any teacher account (or legacy teacher/admin role). School access:
// role school (SchoolMiddleware also auto-provisions the school profile draft).
Route::middleware(['auth', 'teacher'])->prefix('teacher')->name('teacher.')->group(base_path('routes/crm.php'));
Route::middleware(['auth', 'school'])->prefix('school')->name('school.')->group(base_path('routes/crm.php'));

// Student-facing teacher ecosystem pages
Route::middleware(['auth'])->group(function () {
    Route::get('/my-teachers', [MyTeachersController::class, 'index'])->name('my-teachers.index');
    Route::post('/my-teachers/{relationship}/approve', [MyTeachersController::class, 'approve'])->name('my-teachers.approve');
    Route::post('/my-teachers/{relationship}/decline', [MyTeachersController::class, 'decline'])->name('my-teachers.decline');
    Route::delete('/my-teachers/{relationship}', [MyTeachersController::class, 'destroy'])->name('my-teachers.destroy');

    Route::get('/teacher-invitations/{token}', [TeacherInvitationAcceptController::class, 'show'])->name('teacher-invitations.accept');
    Route::post('/teacher-invitations/{token}', [TeacherInvitationAcceptController::class, 'store'])
        ->middleware('throttle:10,1')->name('teacher-invitations.accept.store');

    // Attachment download shared by both conversation participants.
    Route::get('/teacher-message-attachments/{attachment}', [TeacherMessageController::class, 'downloadAttachment'])->name('teacher-messages.attachment');
    Route::get('/teacher-messages', [StudentTeacherMessageController::class, 'index'])->name('teacher-messages.index');
    Route::post('/teacher-messages/start/{teacher}', [StudentTeacherMessageController::class, 'start'])->name('teacher-messages.start');
    Route::get('/teacher-messages/{conversation}', [StudentTeacherMessageController::class, 'show'])->name('teacher-messages.show');
    Route::post('/teacher-messages/{conversation}', [StudentTeacherMessageController::class, 'store'])
        ->middleware('throttle:30,1')->name('teacher-messages.store');

    Route::get('/my-appointments', [MyAppointmentsController::class, 'index'])->name('my-appointments.index');
    Route::post('/my-appointments/{appointment}/cancel', [MyAppointmentsController::class, 'cancel'])->name('my-appointments.cancel');
    Route::post('/my-appointments/{appointment}/reschedule', [MyAppointmentsController::class, 'requestReschedule'])->name('my-appointments.reschedule');

    Route::get('/teachers/{slug}/slots', [TeacherBookingController::class, 'slots'])->name('teachers.slots');
    Route::post('/teachers/{slug}/book', [TeacherBookingController::class, 'book'])
        ->middleware('throttle:10,1')->name('teachers.book');
    Route::post('/teachers/{slug}/reviews', [TeacherReviewController::class, 'store'])
        ->middleware('throttle:5,1')->name('teachers.reviews.store');
    Route::post('/teacher-reviews/{review}/report', [TeacherReviewController::class, 'report'])->name('teacher-reviews.report');

    Route::get('/assignments', [StudentAssignmentController::class, 'index'])->name('assignments.index');
    Route::post('/assignments/{recipient}/start', [StudentAssignmentController::class, 'start'])->name('assignments.start');
    Route::get('/assignments/media/{media}/download', [StudentAssignmentController::class, 'downloadMedia'])->name('assignments.media.download');
});

// Article Routes (teachers and schools)
Route::middleware(['auth', 'teacher'])->group(function () {
    Route::resource('articles', ArticleController::class)->except(['show']);
});

// School-only module: member teacher management (on top of the shared CRM group)
Route::middleware(['auth', 'school'])->prefix('school')->name('school.')->group(function () {
    Route::get('/teachers', [SchoolTeacherController::class, 'index'])->name('teachers.index');
    Route::get('/teachers/search', [SchoolTeacherRelationshipController::class, 'search'])
        ->middleware('throttle:30,1')->name('teachers.search');
    Route::get('/teachers/{teacher}', [SchoolTeacherController::class, 'show'])->name('teachers.show');

    Route::post('/teacher-relationships', [SchoolTeacherRelationshipController::class, 'store'])
        ->middleware('throttle:20,1')->name('teacher-relationships.store');
    Route::delete('/teacher-relationships/{relationship}', [SchoolTeacherRelationshipController::class, 'destroy'])->name('teacher-relationships.destroy');

    Route::post('/teacher-invitations', [SchoolTeacherInvitationController::class, 'store'])
        ->middleware('throttle:20,1')->name('teacher-invitations.store');
    Route::post('/teacher-invitations/link', [SchoolTeacherInvitationController::class, 'storeLink'])
        ->middleware('throttle:10,1')->name('teacher-invitations.link');
    Route::delete('/teacher-invitations/{invitation}', [SchoolTeacherInvitationController::class, 'destroy'])->name('teacher-invitations.destroy');
});

// Teacher-facing school ecosystem pages
Route::middleware(['auth'])->group(function () {
    Route::get('/my-schools', [MySchoolsController::class, 'index'])->name('my-schools.index');
    Route::post('/my-schools/{relationship}/approve', [MySchoolsController::class, 'approve'])->name('my-schools.approve');
    Route::post('/my-schools/{relationship}/decline', [MySchoolsController::class, 'decline'])->name('my-schools.decline');
    Route::delete('/my-schools/{relationship}', [MySchoolsController::class, 'destroy'])->name('my-schools.destroy');

    Route::get('/school-invitations/{token}', [SchoolInvitationAcceptController::class, 'show'])->name('school-invitations.accept');
    Route::post('/school-invitations/{token}', [SchoolInvitationAcceptController::class, 'store'])
        ->middleware('throttle:10,1')->name('school-invitations.accept.store');
});

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
    Route::get('exercises/validate', [ExerciseValidationController::class, 'index'])->name('exercises.validate');
    Route::post('exercises/repair', [ExerciseValidationController::class, 'repair'])->name('exercises.repair');

    // Enhanced Users (must be before resource route)
    Route::get('users/segments', [UserController::class, 'segments'])->name('users.segments');
    Route::get('users/export', [UserController::class, 'export'])->name('users.export');
    Route::post('users/bulk-action', [UserController::class, 'bulkAction'])->name('users.bulk-action');

    // Member Reviews: new-member control & approvals + review moderation
    Route::get('member-reviews', [MemberReviewController::class, 'index'])->name('member-reviews.index');

    Route::resource('users', UserController::class);
    Route::post('users/{user}/toggle-restriction', [UserController::class, 'toggleRestriction'])->name('users.toggle-restriction');
    Route::post('users/{user}/verify-email', [UserController::class, 'verifyEmail'])->name('users.verify-email');
    Route::post('users/{user}/impersonate', [UserController::class, 'impersonate'])->name('users.impersonate');
    Route::post('users/{user}/send-password-reset', [UserController::class, 'sendPasswordReset'])->name('users.send-password-reset');

    // CRM Notes
    Route::post('users/{user}/notes', [CrmNoteController::class, 'store'])->name('notes.store');
    Route::put('notes/{note}', [CrmNoteController::class, 'update'])->name('notes.update');
    Route::delete('notes/{note}', [CrmNoteController::class, 'destroy'])->name('notes.destroy');
    Route::patch('notes/{note}/pin', [CrmNoteController::class, 'togglePin'])->name('notes.togglePin');

    // Exercises overview + categories
    Route::get('exercises', [ExerciseController::class, 'index'])->name('exercises.index');
    Route::resource('exercise-categories', ExerciseCategoryController::class);

    // Learning Path Exercises
    Route::post('learning-path-exercises/{learningPathExercise}/duplicate', [LearningPathExerciseController::class, 'duplicate'])->name('learning-path-exercises.duplicate');
    Route::patch('learning-path-exercises/{learningPathExercise}/toggle-status', [LearningPathExerciseController::class, 'toggleStatus'])->name('learning-path-exercises.toggle-status');
    Route::resource('learning-path-exercises', LearningPathExerciseController::class)->except(['show']);

    // Content Library
    Route::resource('content', ContentController::class)->except(['show'])->parameters(['content' => 'article']);
    Route::post('content/{article}/approve', [ContentController::class, 'approve'])->name('content.approve');
    Route::post('content/{article}/reject', [ContentController::class, 'reject'])->name('content.reject');
    Route::resource('content-categories', ContentCategoryController::class)->except(['show']);

    // Payments
    Route::resource('plans', PlanController::class);
    Route::resource('subscriptions', SubscriptionController::class)->except(['create', 'store']);
    Route::post('subscriptions/{subscription}/confirm', [SubscriptionController::class, 'confirm'])->name('subscriptions.confirm');
    Route::resource('invoices', InvoiceController::class)->except(['create', 'store']);
    Route::post('invoices/{invoice}/refund', [InvoiceController::class, 'refund'])->name('invoices.refund');
    Route::resource('coupons', CouponController::class);

    // Premium-student incentives (teachers auto-free at 10+, schools approved at 20+)
    Route::get('incentives', [SubscriptionBenefitController::class, 'index'])->name('incentives.index');
    Route::post('incentives/{benefit}/approve', [SubscriptionBenefitController::class, 'approve'])->name('incentives.approve');
    Route::post('incentives/{benefit}/revoke', [SubscriptionBenefitController::class, 'revoke'])->name('incentives.revoke');
    Route::post('incentives/user/{user}/recalculate', [SubscriptionBenefitController::class, 'recalculate'])->name('incentives.recalculate');

    // AI Coach Admin
    Route::get('ai-coach-admin', [AiCoachAdminController::class, 'index'])->name('ai-coach-admin.index');
    Route::get('ai-coach-admin/users/{user}', [AiCoachAdminController::class, 'userProfile'])->name('ai-coach-admin.user');
    Route::get('ai-coach-admin/settings', [AiCoachAdminController::class, 'settings'])->name('ai-coach-admin.settings');
    Route::put('ai-coach-admin/settings', [AiCoachAdminController::class, 'updateSettings'])->name('ai-coach-admin.settings.update');

    // AI Usage Dashboard
    Route::prefix('ai-usage')->name('ai-usage.')->group(function () {
        Route::get('/', [AiUsageController::class, 'index'])->name('index');
        Route::get('export', [AiUsageController::class, 'export'])->name('export');
    });

    // CRM Tasks
    Route::resource('tasks', CrmTaskController::class);

    // Messages
    Route::resource('messages', MessageController::class)->except(['edit']);
    Route::post('messages/{message}/reply', [MessageController::class, 'reply'])->name('messages.reply');
    Route::patch('messages/{message}/read', [MessageController::class, 'markRead'])->name('messages.read');
    Route::patch('messages/{message}/archive', [MessageController::class, 'archive'])->name('messages.archive');

    // Community Feed moderation
    Route::get('community', [CommunityController::class, 'index'])->name('community.index');
    Route::delete('community/{feedItem}', [CommunityController::class, 'destroy'])->name('community.destroy');

    // Teacher Profile moderation
    Route::get('teacher-profiles', [TeacherProfileModerationController::class, 'index'])->name('teacher-profiles.index');
    Route::get('teacher-profiles/{teacherProfile}', [TeacherProfileModerationController::class, 'show'])->name('teacher-profiles.show');
    Route::post('teacher-profiles/{teacherProfile}/approve', [TeacherProfileModerationController::class, 'approve'])->name('teacher-profiles.approve');
    Route::post('teacher-profiles/{teacherProfile}/reject', [TeacherProfileModerationController::class, 'reject'])->name('teacher-profiles.reject');
    Route::post('teacher-profiles/{teacherProfile}/suspend', [TeacherProfileModerationController::class, 'suspend'])->name('teacher-profiles.suspend');
    Route::post('teacher-profiles/{teacherProfile}/reinstate', [TeacherProfileModerationController::class, 'reinstate'])->name('teacher-profiles.reinstate');
    Route::post('teacher-profiles/{teacherProfile}/force-private', [TeacherProfileModerationController::class, 'forcePrivate'])->name('teacher-profiles.force-private');
    Route::post('teacher-profiles/{teacherProfile}/notes', [TeacherProfileModerationController::class, 'addNote'])->name('teacher-profiles.notes');
    Route::patch('teacher-profiles/{teacherProfile}/tier', [TeacherProfileModerationController::class, 'updateTier'])->name('teacher-profiles.tier');
    Route::post('teacher-profiles/{teacherProfile}/recalculate-benefits', [TeacherProfileModerationController::class, 'recalculateBenefits'])->name('teacher-profiles.recalculate-benefits');

    Route::get('teacher-reviews', [TeacherReviewModerationController::class, 'index'])->name('teacher-reviews.index');
    Route::post('teacher-reviews/{review}/hide', [TeacherReviewModerationController::class, 'hide'])->name('teacher-reviews.hide');
    Route::post('teacher-reviews/{review}/approve', [TeacherReviewModerationController::class, 'approve'])->name('teacher-reviews.approve');
    Route::delete('teacher-reviews/{review}', [TeacherReviewModerationController::class, 'destroy'])->name('teacher-reviews.destroy');

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

    // System Health
    Route::get('system-health', [SystemHealthController::class, 'index'])->name('system-health.index');

    // Settings
    Route::get('settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::put('settings', [SettingsController::class, 'update'])->name('settings.update');
    Route::get('settings/activity-log', [SettingsController::class, 'activityLog'])->name('settings.activity-log');

    // Piano Studio & Games
    Route::get('piano-studio/settings', [PianoStudioController::class, 'settings'])->name('piano-studio.settings');
    Route::put('piano-studio/settings', [PianoStudioController::class, 'updateSettings'])->name('piano-studio.settings.update');
    Route::get('games', [GameController::class, 'index'])->name('games.index');
    Route::put('games/settings', [GameController::class, 'updateSettings'])->name('games.settings.update');
    Route::delete('games/scores/{score}', [GameController::class, 'destroyScore'])->name('games.scores.destroy');

    // Legacy /admin/articles module was merged into the Content Library (admin.content.*)
    Route::redirect('articles', '/admin/content');

    // --- Email Center ---
    Route::get('email-center', [EmailCenterController::class, 'dashboard'])->name('email-center.dashboard');
    Route::get('email-center/logs', [EmailCenterController::class, 'logs'])->name('email-center.logs');
    Route::get('email-center/logs/{message}', [EmailCenterController::class, 'logShow'])->name('email-center.logs.show');
    Route::get('email-center/suppressions', [EmailCenterController::class, 'suppressions'])->name('email-center.suppressions');
    Route::post('email-center/suppressions', [EmailCenterController::class, 'suppressionStore'])->name('email-center.suppressions.store');
    Route::delete('email-center/suppressions/{suppression}', [EmailCenterController::class, 'suppressionDestroy'])->name('email-center.suppressions.destroy');
    Route::get('email-center/settings', [EmailCenterController::class, 'settings'])->name('email-center.settings');
    Route::put('email-center/settings', [EmailCenterController::class, 'settingsUpdate'])->name('email-center.settings.update');
    Route::post('email-center/test-send', [EmailCenterController::class, 'testSend'])->name('email-center.test-send');

    Route::get('email-templates/{template}/preview', [EmailTemplateController::class, 'preview'])->name('email-templates.preview');
    Route::post('email-templates/preview', [EmailTemplateController::class, 'preview'])->name('email-templates.preview.live');
    Route::resource('email-templates', EmailTemplateController::class)
        ->except(['show'])->parameters(['email-templates' => 'template']);

    Route::post('email-campaigns/segment-count', [EmailCampaignController::class, 'segmentCount'])->name('email-campaigns.segment-count');
    Route::post('email-campaigns/{campaign}/send', [EmailCampaignController::class, 'send'])->name('email-campaigns.send');
    Route::post('email-campaigns/{campaign}/cancel', [EmailCampaignController::class, 'cancel'])->name('email-campaigns.cancel');
    Route::post('email-campaigns/{campaign}/test-send', [EmailCampaignController::class, 'testSend'])->name('email-campaigns.test-send');
    Route::resource('email-campaigns', EmailCampaignController::class)
        ->parameters(['email-campaigns' => 'campaign']);

    Route::get('email-automations', [EmailAutomationController::class, 'index'])->name('email-automations.index');
    Route::put('email-automations/{automation}', [EmailAutomationController::class, 'update'])->name('email-automations.update');

    // --- Support Inbox ---
    Route::get('support-inbox', [SupportInboxController::class, 'index'])->name('support-inbox.index');
    Route::get('support-inbox/{conversation}', [SupportInboxController::class, 'show'])->name('support-inbox.show');
    Route::post('support-inbox/{conversation}/reply', [SupportInboxController::class, 'reply'])->name('support-inbox.reply');
    Route::put('support-inbox/{conversation}/status', [SupportInboxController::class, 'updateStatus'])->name('support-inbox.status');
    Route::get('support-inbox/attachments/{message}/{index}', [SupportInboxController::class, 'attachment'])->name('support-inbox.attachment');
});

// AI Coach — premium-only (plans.*.premium.ai_coach).
Route::middleware(['auth', 'verified', 'plan:ai_coach'])->group(function () {
    Route::get('/ai-coach', [AiCoachController::class, 'index'])->name('ai-coach.index');
    Route::post('/ai-coach/generate', [AiCoachController::class, 'generate'])->name('ai-coach.generate');
});

// AI Chat
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/ai-chat', [AiChatController::class, 'index'])->name('ai-chat.index');
    Route::post('/ai-chat/send', [AiChatController::class, 'send'])->name('ai-chat.send');
    Route::post('/ai-chat/clear', [AiChatController::class, 'clear'])->name('ai-chat.clear');
});

// Leave impersonation (the impersonated user is not an admin, so this lives outside the admin group)
Route::post('/impersonate/leave', [UserController::class, 'leaveImpersonation'])
    ->middleware('auth')
    ->name('impersonate.leave');

// Search
Route::get('/search', [SearchController::class, 'index'])->name('search');

// Language switch
Route::post('/language/switch', [LanguageController::class, 'switch'])->name('language.switch');

// Stripe Billing webhooks (signature-verified in the controller, CSRF-exempt).
// Point the Stripe Dashboard endpoint here.
Route::post('/webhooks/stripe', [StripeWebhookController::class, 'events'])
    ->name('webhooks.stripe');

// --- Email Center public endpoints ---
// Amazon SNS event notifications (signature-validated in the controller, CSRF-exempt)
Route::post('/webhooks/aws/ses/events', [SesWebhookController::class, 'events'])
    ->name('webhooks.ses.events');

// Internal tracking: open pixel, click attribution redirect, unsubscribe
Route::get('/email/open/{token}', [EmailTrackingController::class, 'open'])->name('email.open');
Route::get('/email/click/{token}', [EmailTrackingController::class, 'click'])
    ->middleware('signed')->name('email.click');
Route::get('/email/unsubscribe/{token}', [EmailTrackingController::class, 'unsubscribe'])
    ->middleware('signed')->name('email.unsubscribe');
Route::post('/email/unsubscribe/{token}', [EmailTrackingController::class, 'unsubscribePost'])
    ->middleware('signed');

// Email preferences centre — signed link from the email footer (works logged out).
Route::get('/email/preferences/{token}', [EmailPreferenceController::class, 'showByToken'])
    ->middleware('signed')->name('email.preferences');
Route::post('/email/preferences/{token}', [EmailPreferenceController::class, 'updateByToken'])
    ->middleware('signed')->name('email.preferences.update');

require __DIR__.'/auth.php';
