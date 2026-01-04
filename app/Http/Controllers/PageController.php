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
}
