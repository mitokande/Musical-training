<?php

use App\Http\Controllers\AIController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\SingleNotePracticeController;
use App\Http\Controllers\Admin\IntervalDirectionPracticeController;
use App\Http\Controllers\Admin\IntervalComparisonPracticeController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PracticeController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/learn', [PageController::class, 'learnView'])->middleware(['auth', 'verified'])->name('learn');
Route::get('/progress', [PageController::class, 'progressView'])->middleware(['auth', 'verified'])->name('progress');

Route::get('/ai-exercises', [PageController::class, 'aiExercisesView'])->middleware(['auth', 'verified'])->name('ai.exercises');
Route::post('/ai-exercises/generate', [AIController::class, 'generatePractices'])->middleware(['auth', 'verified'])->name('ai.generate-practices');


Route::post('/api/practice/check-answer', [PracticeController::class, 'checkAnswer'])->name('api.practice.check-answer');


Route::get('/practice/{slug}', [PageController::class, 'practiceView'])->middleware(['auth', 'verified'])->name('practice');
Route::get('/practice-mixed', [PageController::class, 'practiceMixedView'])->middleware(['auth', 'verified'])->name('practice.mixed');
Route::post('/practice-mixed/start', [PageController::class, 'startMixedPractice'])->middleware(['auth', 'verified'])->name('practice.mixed.start');
Route::get('/practice-ai', [PageController::class, 'aiPracticeView'])->middleware(['auth', 'verified'])->name('practice.ai');
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::get('/api/ai/generate-interval-direction-practice', [AIController::class, 'generateIntervalDirectionPractice'])->name('api.ai.generate-interval-direction-practice');

// Admin Routes
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::resource('single-note', SingleNotePracticeController::class)->except(['show']);
    Route::resource('interval-direction', IntervalDirectionPracticeController::class)->except(['show']);
    Route::resource('interval-comparison', IntervalComparisonPracticeController::class)->except(['show']);
});

require __DIR__.'/auth.php';
