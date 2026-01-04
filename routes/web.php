<?php

use App\Http\Controllers\AIController;
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


Route::post('/api/practice/check-answer', [PracticeController::class, 'checkAnswer'])->name('api.practice.check-answer');


Route::get('/practice/{slug}', [PageController::class, 'practiceView'])->middleware(['auth', 'verified'])->name('practice');
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::get('/api/ai/generate-interval-direction-practice', [AIController::class, 'generateIntervalDirectionPractice'])->name('api.ai.generate-interval-direction-practice');

require __DIR__.'/auth.php';
