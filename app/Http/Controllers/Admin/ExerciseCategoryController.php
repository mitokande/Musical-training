<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ExerciseCategory;
use App\Models\LearningPathExercise;
use Illuminate\Http\Request;

class ExerciseCategoryController extends Controller
{
    public function index()
    {
        $categories = ExerciseCategory::with('children')
            ->whereNull('parent_id')
            ->orderBy('sort_order')
            ->get();

        return view('admin.exercise-categories.index', compact('categories'));
    }

    public function show(ExerciseCategory $exerciseCategory)
    {
        $exerciseCategory->load(['children', 'parent']);

        $exercises = LearningPathExercise::where('category_id', $exerciseCategory->id)
            ->withCount('userProgress')
            ->withAvg('userProgress', 'score')
            ->orderBy('sort_order')
            ->get();

        $subcategories = $exerciseCategory->children()
            ->orderBy('sort_order')
            ->get()
            ->map(function ($sub) {
                $sub->setRelation('exercises', LearningPathExercise::where('category_id', $sub->id)
                    ->withCount('userProgress')
                    ->withAvg('userProgress', 'score')
                    ->orderBy('sort_order')
                    ->get());
                return $sub;
            });

        return view('admin.exercise-categories.show', compact('exerciseCategory', 'exercises', 'subcategories'));
    }

    public function create()
    {
        $parents = ExerciseCategory::whereNull('parent_id')->get();

        return view('admin.exercise-categories.create', compact('parents'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'slug'        => 'required|string|max:255|unique:exercise_categories',
            'parent_id'   => 'nullable|exists:exercise_categories,id',
            'description' => 'nullable|string',
            'icon'        => 'nullable|string|max:100',
            'sort_order'  => 'integer|min:0',
            'is_active'   => 'boolean',
            'is_premium'  => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active');
        $validated['is_premium'] = $request->boolean('is_premium');

        ExerciseCategory::create($validated);

        return redirect()->route('admin.exercise-categories.index')
            ->with('success', 'Exercise category created successfully.');
    }

    public function edit(ExerciseCategory $exerciseCategory)
    {
        $parents = ExerciseCategory::whereNull('parent_id')
            ->where('id', '!=', $exerciseCategory->id)
            ->get();

        return view('admin.exercise-categories.edit', compact('exerciseCategory', 'parents'));
    }

    public function update(Request $request, ExerciseCategory $exerciseCategory)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'slug'        => 'required|string|max:255|unique:exercise_categories,slug,' . $exerciseCategory->id,
            'parent_id'   => 'nullable|exists:exercise_categories,id',
            'description' => 'nullable|string',
            'icon'        => 'nullable|string|max:100',
            'sort_order'  => 'integer|min:0',
            'is_active'   => 'boolean',
            'is_premium'  => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active');
        $validated['is_premium'] = $request->boolean('is_premium');

        $exerciseCategory->update($validated);

        return redirect()->route('admin.exercise-categories.index')
            ->with('success', 'Exercise category updated successfully.');
    }

    public function destroy(ExerciseCategory $exerciseCategory)
    {
        $exerciseCategory->delete();

        return redirect()->route('admin.exercise-categories.index')
            ->with('success', 'Exercise category deleted successfully.');
    }
}
