<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Teacher\TeacherAssignmentController;
use App\Http\Controllers\Teacher\TeacherCalendarController;
use App\Http\Controllers\Teacher\TeacherClassController;
use App\Http\Controllers\Teacher\TeacherContentController;
use App\Http\Controllers\Teacher\TeacherDashboardController;
use App\Http\Controllers\Teacher\TeacherInvitationController;
use App\Http\Controllers\Teacher\TeacherMediaController;
use App\Http\Controllers\Teacher\TeacherMessageController;
use App\Http\Controllers\Teacher\TeacherPaymentLinkController;
use App\Http\Controllers\Teacher\TeacherProfileController;
use App\Http\Controllers\Teacher\TeacherServiceController;
use App\Http\Controllers\Teacher\TeacherStudentController;
use App\Http\Controllers\Teacher\TeacherStudentRelationshipController;
use App\Http\Controllers\Teacher\TeacherVideoController;
use Illuminate\Support\Facades\Route;

/*
 * Shared CRM route definitions. Registered TWICE from routes/web.php —
 * once under prefix teacher/name teacher.* and once under prefix
 * school/name school.* — so the teacher CRM and the school panel run the
 * same controllers. This file is evaluated once per registration: keep it
 * to plain Route definitions only (no stateful code).
 */

Route::get('/dashboard', [TeacherDashboardController::class, 'index'])->name('dashboard');
// Teachers join the shared social dashboard/feed via a teacher-namespaced URL that
// renders the exact same page students see on their dashboard (dashboards.user:
// hero + social feed + sidebar); only the hero "Welcome back" box is teacher-specific.
Route::get('/feed', [DashboardController::class, 'feed'])->name('feed');
Route::get('/settings', [TeacherDashboardController::class, 'settings'])->name('settings');
Route::put('/settings/account', [TeacherDashboardController::class, 'updateAccount'])->name('settings.account');
Route::post('/notifications/read', [TeacherDashboardController::class, 'markNotificationsRead'])->name('notifications.read');

Route::get('/profile', [TeacherProfileController::class, 'edit'])->name('profile.edit');
Route::put('/profile', [TeacherProfileController::class, 'update'])->name('profile.update');
Route::post('/profile/cover', [TeacherProfileController::class, 'updateCover'])->name('profile.cover');
Route::post('/profile/submit', [TeacherProfileController::class, 'submit'])->name('profile.submit');
Route::get('/profile/preview', [TeacherProfileController::class, 'preview'])->name('profile.preview');

Route::post('/services', [TeacherServiceController::class, 'store'])->name('services.store');
Route::put('/services/{service}', [TeacherServiceController::class, 'update'])->name('services.update');
Route::delete('/services/{service}', [TeacherServiceController::class, 'destroy'])->name('services.destroy');

Route::post('/videos', [TeacherVideoController::class, 'store'])->name('videos.store');
Route::put('/videos/{video}', [TeacherVideoController::class, 'update'])->name('videos.update');
Route::delete('/videos/{video}', [TeacherVideoController::class, 'destroy'])->name('videos.destroy');

Route::post('/media', [TeacherMediaController::class, 'store'])->name('media.store');
Route::put('/media/{media}', [TeacherMediaController::class, 'update'])->name('media.update');
Route::get('/media/{media}/download', [TeacherMediaController::class, 'download'])->name('media.download');
Route::post('/media/{media}/share', [TeacherMediaController::class, 'share'])->name('media.share');
Route::delete('/media/{media}', [TeacherMediaController::class, 'destroy'])->name('media.destroy');

// Package 2: Students, invitations, classes, assignments
Route::get('/students', [TeacherStudentController::class, 'index'])->name('students.index');
Route::get('/students/search', [TeacherStudentRelationshipController::class, 'search'])
    ->middleware('throttle:30,1')->name('students.search');
Route::get('/students/{student}', [TeacherStudentController::class, 'show'])->name('students.show');
Route::post('/students/{student}/notes', [TeacherStudentController::class, 'storeNote'])->name('students.notes.store');
Route::delete('/student-notes/{note}', [TeacherStudentController::class, 'destroyNote'])->name('students.notes.destroy');
Route::post('/student-tags', [TeacherStudentController::class, 'storeTag'])->name('students.tags.store');
Route::put('/students/{student}/tags', [TeacherStudentController::class, 'syncTags'])->name('students.tags.sync');
Route::post('/students/{student}/rewards', [TeacherStudentController::class, 'storeReward'])->name('students.rewards.store');

Route::post('/relationships', [TeacherStudentRelationshipController::class, 'store'])
    ->middleware('throttle:20,1')->name('relationships.store');
Route::delete('/relationships/{relationship}', [TeacherStudentRelationshipController::class, 'destroy'])->name('relationships.destroy');

Route::post('/invitations', [TeacherInvitationController::class, 'store'])
    ->middleware('throttle:20,1')->name('invitations.store');
Route::post('/invitations/link', [TeacherInvitationController::class, 'storeLink'])
    ->middleware('throttle:10,1')->name('invitations.link');
Route::delete('/invitations/{invitation}', [TeacherInvitationController::class, 'destroy'])->name('invitations.destroy');

Route::get('/classes', [TeacherClassController::class, 'index'])->name('classes.index');
Route::post('/classes', [TeacherClassController::class, 'store'])->name('classes.store');
Route::get('/classes/{class}', [TeacherClassController::class, 'show'])->name('classes.show');
Route::put('/classes/{class}', [TeacherClassController::class, 'update'])->name('classes.update');
Route::post('/classes/{class}/students', [TeacherClassController::class, 'addStudent'])->name('classes.students.add');
Route::delete('/classes/{class}/students/{student}', [TeacherClassController::class, 'removeStudent'])->name('classes.students.remove');
Route::post('/classes/{class}/archive', [TeacherClassController::class, 'archive'])->name('classes.archive');

Route::get('/assignments', [TeacherAssignmentController::class, 'index'])->name('assignments.index');
Route::get('/assignments/create', [TeacherAssignmentController::class, 'create'])->name('assignments.create');
Route::post('/assignments/ai-suggest', [TeacherAssignmentController::class, 'aiSuggest'])
    ->middleware('throttle:15,1')->name('assignments.ai-suggest');
Route::post('/assignments', [TeacherAssignmentController::class, 'store'])->name('assignments.store');
Route::get('/assignments/{assignment}', [TeacherAssignmentController::class, 'show'])->name('assignments.show');
Route::put('/assignments/{assignment}', [TeacherAssignmentController::class, 'update'])->name('assignments.update');
Route::post('/assignments/{assignment}/regenerate', [TeacherAssignmentController::class, 'regenerateQuestions'])->name('assignments.regenerate');
Route::post('/assignments/{assignment}/questions/{question}/regenerate', [TeacherAssignmentController::class, 'regenerateQuestion'])->name('assignments.questions.regenerate');
Route::put('/assignments/{assignment}/questions/{question}', [TeacherAssignmentController::class, 'updateQuestion'])->name('assignments.questions.update');
Route::delete('/assignments/{assignment}/questions/{question}', [TeacherAssignmentController::class, 'destroyQuestion'])->name('assignments.questions.destroy');
Route::get('/assignments/{assignment}/preview', [TeacherAssignmentController::class, 'preview'])->name('assignments.preview');
Route::post('/assignments/{assignment}/send', [TeacherAssignmentController::class, 'send'])->name('assignments.send');
Route::post('/assignments/{assignment}/duplicate', [TeacherAssignmentController::class, 'duplicate'])->name('assignments.duplicate');
Route::post('/assignments/{assignment}/archive', [TeacherAssignmentController::class, 'archive'])->name('assignments.archive');
Route::delete('/assignments/{assignment}', [TeacherAssignmentController::class, 'destroy'])->name('assignments.destroy');

// Package 3: Messaging + calendar
Route::get('/content', [TeacherContentController::class, 'index'])->name('content.index');
Route::post('/content/articles/{article}/share', [TeacherContentController::class, 'shareArticle'])->name('content.articles.share');

Route::get('/messages', [TeacherMessageController::class, 'index'])->name('messages.index');
Route::post('/messages/start/{student}', [TeacherMessageController::class, 'start'])->name('messages.start');
Route::get('/messages/{conversation}', [TeacherMessageController::class, 'show'])->name('messages.show');
Route::post('/messages/{conversation}', [TeacherMessageController::class, 'store'])
    ->middleware('throttle:30,1')->name('messages.store');

Route::get('/calendar', [TeacherCalendarController::class, 'index'])->name('calendar.index');
Route::put('/calendar/settings', [TeacherCalendarController::class, 'updateSettings'])->name('calendar.settings');
Route::post('/calendar/availability', [TeacherCalendarController::class, 'storeAvailability'])->name('calendar.availability.store');
Route::delete('/calendar/availability/{availability}', [TeacherCalendarController::class, 'destroyAvailability'])->name('calendar.availability.destroy');
Route::post('/calendar/exceptions', [TeacherCalendarController::class, 'storeException'])->name('calendar.exceptions.store');
Route::delete('/calendar/exceptions/{exception}', [TeacherCalendarController::class, 'destroyException'])->name('calendar.exceptions.destroy');
Route::post('/appointments/{appointment}/confirm', [TeacherCalendarController::class, 'confirm'])->name('appointments.confirm');
Route::post('/appointments/{appointment}/reject', [TeacherCalendarController::class, 'reject'])->name('appointments.reject');
Route::post('/appointments/{appointment}/cancel', [TeacherCalendarController::class, 'cancel'])->name('appointments.cancel');
Route::post('/appointments/{appointment}/reschedule', [TeacherCalendarController::class, 'reschedule'])->name('appointments.reschedule');
Route::post('/appointments/{appointment}/complete', [TeacherCalendarController::class, 'complete'])->name('appointments.complete');
Route::post('/appointments/{appointment}/no-show', [TeacherCalendarController::class, 'noShow'])->name('appointments.no-show');
Route::put('/appointments/{appointment}/note', [TeacherCalendarController::class, 'updateNote'])->name('appointments.note');

Route::post('/payment-links', [TeacherPaymentLinkController::class, 'store'])->name('payment-links.store');
Route::put('/payment-links/{paymentLink}', [TeacherPaymentLinkController::class, 'update'])->name('payment-links.update');
Route::delete('/payment-links/{paymentLink}', [TeacherPaymentLinkController::class, 'destroy'])->name('payment-links.destroy');
