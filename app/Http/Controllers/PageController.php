<?php

namespace App\Http\Controllers;

use App\Models\IntervalDirectionPractice;
use App\Models\Practice;
use App\Models\SingleNotePractice;
use Illuminate\Http\Request;

class PageController extends Controller
{
    //

    public function learnView() {
        $practices = Practice::all();
        return view('learn', compact('practices'));
    }

    public function aiExercisesView() {
        $practices = Practice::all();
        return view('ai_exercise', compact('practices'));
    }

    public function startAiSession(Request $request) {
        // Store session configuration
        $sessionConfig = [
            'exercise_types' => $request->input('exercise_types', []),
            'num_questions' => $request->input('num_questions', 10),
            'student_level' => $request->input('student_level', 'intermediate'),
            'difficulty' => $request->input('difficulty', 'adaptive'),
            'coach_notes' => $request->input('coach_notes', ''),
        ];
        
        // Store in session for now - can be expanded to use database
        session(['ai_session_config' => $sessionConfig]);
        
        // Redirect to the practice session (placeholder for now)
        return redirect()->route('ai.exercises')->with('success', 'AI Practice session configuration saved!');
    }

    public function practiceView($slug) {
        $practiceMap = [
            'single-note-practice' => SingleNotePractice::class,
            'interval-direction-practice' => IntervalDirectionPractice::class,
        ];

        $practiceClass = $practiceMap[$slug] ?? null;

        if (!$practiceClass) {
            abort(404, 'Practice not found');
        }

        $practices = $practiceClass::inRandomOrder()->get();
        return view('practice', [
            'practices' => $practices,
            'slug' => $slug,
        ]);
    }

    /**
     * Display the mixed practice setup page or redirect with session data
     */
    public function practiceMixedView() {
        // Check if we have session data for mixed practice
        $sessionConfig = session('mixed_practice_config');
        
        if (!$sessionConfig) {
            // Show setup page if no config exists
            return view('practice-mixed-setup');
        }

        // Get practices based on config
        $practices = $this->getMixedPractices($sessionConfig);
        
        // Clear session after using it
        session()->forget('mixed_practice_config');

        return view('practice-mixed', [
            'practices' => $practices,
            'title' => $sessionConfig['title'] ?? 'Mixed Practice',
        ]);
    }

    /**
     * Start a mixed practice session with specified configuration
     */
    public function startMixedPractice(\Illuminate\Http\Request $request) {
        $validated = $request->validate([
            'exercise_types' => 'required|array|min:1',
            'exercise_types.*' => 'in:single_note,interval_direction',
            'num_questions' => 'required|integer|min:1|max:50',
            'title' => 'nullable|string|max:100',
        ]);

        $config = [
            'exercise_types' => $validated['exercise_types'],
            'num_questions' => $validated['num_questions'],
            'title' => $validated['title'] ?? 'Mixed Practice',
        ];

        // Store config in session and redirect
        session(['mixed_practice_config' => $config]);

        return redirect()->route('practice.mixed');
    }

    /**
     * Get mixed practices based on configuration
     */
    protected function getMixedPractices(array $config): \Illuminate\Support\Collection {
        $types = $config['exercise_types'];
        $numQuestions = $config['num_questions'];
        $questionsPerType = (int) ceil($numQuestions / count($types));

        $practices = collect();

        foreach ($types as $type) {
            $typeQuestions = match ($type) {
                'single_note' => SingleNotePractice::inRandomOrder()->limit($questionsPerType)->get(),
                'interval_direction' => IntervalDirectionPractice::inRandomOrder()->limit($questionsPerType)->get(),
                default => collect(),
            };
            $practices = $practices->merge($typeQuestions);
        }

        // Shuffle and limit to exact number requested
        return $practices->shuffle()->take($numQuestions);
    }
}
