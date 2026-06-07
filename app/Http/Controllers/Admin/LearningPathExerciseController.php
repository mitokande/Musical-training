<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ExerciseCategory;
use App\Models\LearningPathExercise;
use App\Models\UserLearningPathProgress;
use Illuminate\Http\Request;

class LearningPathExerciseController extends Controller
{
    public function index()
    {
        $query = LearningPathExercise::with('category')
            ->withCount('userProgress')
            ->orderBy('category_id')
            ->orderBy('sort_order');

        if (request('category')) {
            $query->where('category_id', request('category'));
        }

        $exercises = $query->paginate(30)->withQueryString();

        $categories = ExerciseCategory::orderBy('sort_order')->get();

        return view('admin.learning-path-exercises.index', compact('exercises', 'categories'));
    }

    public function create()
    {
        $categories = ExerciseCategory::orderBy('sort_order')->get();
        $preselectedCategoryId = request('category_id');
        return view('admin.learning-path-exercises.create', compact('categories', 'preselectedCategoryId'));
    }

    public function store(Request $request)
    {
        $validated = $this->validateExercise($request);

        $exercise = LearningPathExercise::create($validated);

        $redirectRoute = $exercise->category_id
            ? route('admin.exercise-categories.show', $exercise->category_id)
            : route('admin.learning-path-exercises.index');

        return redirect($redirectRoute)->with('success', 'Egzersiz başarıyla oluşturuldu.');
    }

    public function edit(LearningPathExercise $learningPathExercise)
    {
        $categories = ExerciseCategory::orderBy('sort_order')->get();
        return view('admin.learning-path-exercises.edit', [
            'exercise'   => $learningPathExercise,
            'categories' => $categories,
        ]);
    }

    public function update(Request $request, LearningPathExercise $learningPathExercise)
    {
        $validated = $this->validateExercise($request);
        $learningPathExercise->update($validated);

        $redirectBack = url()->previous();
        $fallback = route('admin.exercise-categories.show', $learningPathExercise->category_id)
            ?: route('admin.learning-path-exercises.index');

        return redirect($redirectBack ?: $fallback)->with('success', 'Egzersiz güncellendi.');
    }

    public function destroy(LearningPathExercise $learningPathExercise)
    {
        $hasProgress = UserLearningPathProgress::where('learning_path_exercise_id', $learningPathExercise->id)->exists();

        // Soft delete if user progress exists, otherwise hard delete
        if ($hasProgress) {
            $learningPathExercise->delete(); // SoftDeletes — sets deleted_at
            $msg = 'Egzersiz arşivlendi (kullanıcı ilerlemesi korundu).';
        } else {
            $learningPathExercise->forceDelete();
            $msg = 'Egzersiz kalıcı olarak silindi.';
        }

        $categoryId = $learningPathExercise->category_id;
        return redirect(
            $categoryId
                ? route('admin.exercise-categories.show', $categoryId)
                : route('admin.learning-path-exercises.index')
        )->with('success', $msg);
    }

    public function duplicate(LearningPathExercise $learningPathExercise)
    {
        $newExercise = $learningPathExercise->replicate();
        $newExercise->title      = 'Copy of ' . $learningPathExercise->title;
        $newExercise->slug       = $learningPathExercise->slug . '-copy-' . time();
        $newExercise->sort_order = $learningPathExercise->sort_order + 1;
        $newExercise->is_active  = false;
        $newExercise->save();

        return redirect()->back()->with('success', 'Egzersiz kopyalandı. Başlığı ve slug\'ı güncellemeyi unutmayın.');
    }

    public function toggleStatus(LearningPathExercise $learningPathExercise)
    {
        $learningPathExercise->update(['is_active' => !$learningPathExercise->is_active]);

        $status = $learningPathExercise->is_active ? 'aktif' : 'pasif';
        return redirect()->back()->with('success', "Egzersiz $status yapıldı.");
    }

    private function validateExercise(Request $request): array
    {
        $data = $request->validate([
            'category_id'                => 'required|exists:exercise_categories,id',
            'title'                      => 'required|string|max:255',
            'description'                => 'required|string',
            'title_tr'                   => 'nullable|string|max:255',
            'description_tr'             => 'nullable|string',
            'level'                      => 'required|in:beginner,intermediate,advanced',
            'sort_order'                 => 'required|integer|min:1|max:999',
            'slug'                       => 'required|string|max:255',
            'config_json'                => 'required|string',
            'tags'                       => 'nullable|string',
            'skills_trained'             => 'nullable|string',
            'estimated_duration_minutes' => 'required|integer|min:1|max:120',
            'is_active'                  => 'sometimes|boolean',
        ]);

        $config = json_decode($data['config_json'], true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'config_json' => 'Config JSON geçerli bir JSON değil.',
            ]);
        }

        return [
            'category_id'                => $data['category_id'],
            'title'                      => $data['title'],
            'description'                => $data['description'],
            'translations'               => [
                'tr' => [
                    'title'       => $data['title_tr'] ?? $data['title'],
                    'description' => $data['description_tr'] ?? $data['description'],
                ],
            ],
            'level'                      => $data['level'],
            'sort_order'                 => $data['sort_order'],
            'slug'                       => $data['slug'],
            'config_json'                => $config,
            'tags'                       => $data['tags'] ? array_map('trim', explode(',', $data['tags'])) : [],
            'skills_trained'             => $data['skills_trained'] ? array_map('trim', explode(',', $data['skills_trained'])) : [],
            'estimated_duration_minutes' => $data['estimated_duration_minutes'],
            'is_active'                  => $request->boolean('is_active', true),
        ];
    }
}
