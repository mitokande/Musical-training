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

    public function aiPracticeView() {
        // Get AI-generated questions from session
        $questions = session('ai_practice_questions');
        $title = session('ai_practice_title', 'AI Generated Practice');
        
        if (!$questions || empty($questions)) {
            // No questions in session, redirect back to AI exercises page
            return redirect()->route('ai.exercises')->with('error', 'No practice questions generated. Please try again.');
        }
        
        // Clear session after retrieving
        session()->forget(['ai_practice_questions', 'ai_practice_title']);
        
        // Convert raw question data to practice objects
        $practices = $this->convertAIQuestionsToPractices($questions);
        \Log::info(json_encode($practices));
        return view('practice-mixed', [
            'practices' => $practices,
            'title' => $title,
        ]);
    }
    
    /**
     * Convert AI-generated question arrays to practice model instances
     */
    protected function convertAIQuestionsToPractices(array $questions): \Illuminate\Support\Collection {
        return collect($questions)->map(function ($question) {
            // Determine the type based on the question structure
            if (isset($question['direction']) && isset($question['note1']) && isset($question['note2'])) {
                // Interval Direction Practice
                $practice = new IntervalDirectionPractice();
                $practice->clef = $question['clef'] ?? 'treble';
                $practice->note1 = $question['note1'];
                $practice->note2 = $question['note2'];
                $practice->direction = $question['direction'];
                $practice->octave = $question['octave'] ?? '4';
                $practice->id =  rand(1, 1000000); // Temporary ID for frontend
                return $practice;
            } elseif (isset($question['target']) && isset($question['other_options'])) {
                // Single Note Practice
                $practice = new SingleNotePractice();
                $practice->target = $question['target'];
                $practice->target_type = $question['target_type'] ?? 'note';
                $practice->other_options = $question['other_options'];
                $practice->octave = $question['octave'] ?? '4';
                $practice->id = rand(1, 1000000); // Temporary ID for frontend
                return $practice;
            }
            
            return null;
        })->filter(); // Remove any null values
    }
}
