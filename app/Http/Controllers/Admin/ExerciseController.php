<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ExerciseCategory;
use App\Models\LearningPathExercise;
use App\Models\UserLearningPathProgress;
use Illuminate\Support\Facades\DB;

class ExerciseController extends Controller
{
    public function index()
    {
        $totalCategories  = ExerciseCategory::count();
        $totalExercises   = LearningPathExercise::count();
        $activeExercises  = LearningPathExercise::where('is_active', true)->count();
        $inactiveExercises = LearningPathExercise::where('is_active', false)->count();

        // Premium = question_counts.premium > question_counts.free
        $allExercises = LearningPathExercise::all();
        $freeExercises    = $allExercises->filter(fn($e) =>
            ($e->config_json['question_counts']['premium'] ?? 0) <= ($e->config_json['question_counts']['free'] ?? 5)
        )->count();
        $premiumExercises = $totalExercises - $freeExercises;

        $totalAttempts = UserLearningPathProgress::count();
        $avgScore      = round(UserLearningPathProgress::avg('score') ?? 0, 1);

        $completedCount = UserLearningPathProgress::where('completed', true)->count();
        $completionRate = $totalAttempts > 0 ? round($completedCount / $totalAttempts * 100, 1) : 0;

        $mostUsed = LearningPathExercise::withCount('userProgress')
            ->orderByDesc('user_progress_count')
            ->take(5)
            ->get();

        $leastUsed = LearningPathExercise::withCount('userProgress')
            ->orderBy('user_progress_count')
            ->take(5)
            ->get();

        $recentlyEdited = LearningPathExercise::with('category')
            ->latest('updated_at')
            ->take(5)
            ->get();

        return view('admin.exercises.index', compact(
            'totalCategories',
            'totalExercises',
            'activeExercises',
            'inactiveExercises',
            'freeExercises',
            'premiumExercises',
            'totalAttempts',
            'avgScore',
            'completionRate',
            'mostUsed',
            'leastUsed',
            'recentlyEdited'
        ));
    }
}
