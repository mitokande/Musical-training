<?php

namespace App\Http\Controllers;

use App\Models\IntervalComparisonPractice;
use App\Models\IntervalDirectionPractice;
use App\Models\Practice;
use App\Models\SingleNotePractice;
use App\Models\UserPractice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

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
            'interval-comparison-practice' => IntervalComparisonPractice::class
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
            'exercise_types.*' => 'in:single_note,interval_direction,interval_comparison',
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
                'interval_comparison' => IntervalComparisonPractice::inRandomOrder()->limit($questionsPerType)->get(),
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
            } elseif (isset($question['interval_a']) && isset($question['interval_b'])) {
                // Interval Comparison Practice
                $practice = new IntervalComparisonPractice();
                $practice->clef = $question['clef'] ?? 'treble';
                $practice->interval_a = $question['interval_a'];
                $practice->interval_b = $question['interval_b'];
                $practice->target = $question['target'];
                $practice->octave = $question['octave'] ?? '4';
                $practice->id = rand(1, 1000000); // Temporary ID for frontend
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

    /**
     * Display the user's progress page with detailed statistics
     */
    public function progressView() {
        $user = Auth::user();
        $practices = Practice::all();
        
        // Get all user practice sessions
        $userPractices = UserPractice::where('user_id', $user->id)
            ->with('practice')
            ->orderBy('created_at', 'desc')
            ->get();
        
        // Calculate overall statistics
        $totalSessions = $userPractices->count();
        $totalQuestions = $userPractices->sum('total_questions');
        $totalCorrect = $userPractices->sum('correct_answers');
        $totalIncorrect = $userPractices->sum('incorrect_answers');
        $totalSkipped = $userPractices->sum('skipped_answers');
        $totalTime = $userPractices->sum('total_time'); // in seconds
        
        $overallAccuracy = $totalQuestions > 0 
            ? round(($totalCorrect / $totalQuestions) * 100, 1) 
            : 0;
        
        // Calculate streak (consecutive days practiced)
        $streak = $this->calculateStreak($userPractices);
        
        // Calculate practice type breakdown
        $practiceBreakdown = [];
        foreach ($practices as $practice) {
            $typePractices = $userPractices->where('practice_id', $practice->id);
            $typeQuestions = $typePractices->sum('total_questions');
            $typeCorrect = $typePractices->sum('correct_answers');
            $typeTime = $typePractices->sum('total_time');
            
            $practiceBreakdown[] = [
                'id' => $practice->id,
                'name' => $practice->name,
                'slug' => $practice->slug,
                'sessions' => $typePractices->count(),
                'total_questions' => $typeQuestions,
                'correct_answers' => $typeCorrect,
                'accuracy' => $typeQuestions > 0 ? round(($typeCorrect / $typeQuestions) * 100, 1) : 0,
                'total_time' => $typeTime,
                'avg_time' => $typeQuestions > 0 ? round($typeTime / $typeQuestions, 1) : 0,
            ];
        }
        
        // Find best and weakest areas
        $sortedBreakdown = collect($practiceBreakdown)->filter(fn($p) => $p['sessions'] > 0)->sortByDesc('accuracy');
        $bestArea = $sortedBreakdown->first();
        $weakestArea = $sortedBreakdown->last();
        
        // Get recent activity (last 10 sessions)
        $recentActivity = $userPractices->take(10);
        
        // Calculate weekly performance (last 7 days)
        $weeklyPerformance = $this->calculateWeeklyPerformance($userPractices);
        
        // Format total time
        $formattedTime = $this->formatTime($totalTime);
        
        return view('progress', compact(
            'totalSessions',
            'totalQuestions',
            'totalCorrect',
            'totalIncorrect',
            'totalSkipped',
            'overallAccuracy',
            'streak',
            'practiceBreakdown',
            'bestArea',
            'weakestArea',
            'recentActivity',
            'weeklyPerformance',
            'formattedTime'
        ));
    }
    
    /**
     * Calculate the user's practice streak (consecutive days)
     */
    protected function calculateStreak($userPractices): int {
        if ($userPractices->isEmpty()) {
            return 0;
        }
        
        $dates = $userPractices->pluck('created_at')
            ->map(fn($date) => Carbon::parse($date)->format('Y-m-d'))
            ->unique()
            ->sort()
            ->reverse()
            ->values();
        
        if ($dates->isEmpty()) {
            return 0;
        }
        
        $streak = 0;
        $today = Carbon::today()->format('Y-m-d');
        $yesterday = Carbon::yesterday()->format('Y-m-d');
        
        // Check if user practiced today or yesterday to start streak
        if ($dates->first() !== $today && $dates->first() !== $yesterday) {
            return 0;
        }
        
        $currentDate = Carbon::parse($dates->first());
        
        foreach ($dates as $date) {
            $practiceDate = Carbon::parse($date);
            
            if ($currentDate->diffInDays($practiceDate) <= 1) {
                $streak++;
                $currentDate = $practiceDate;
            } else {
                break;
            }
        }
        
        return $streak;
    }
    
    /**
     * Calculate weekly performance data for charts
     */
    protected function calculateWeeklyPerformance($userPractices): array {
        $weeklyData = [];
        
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $dayPractices = $userPractices->filter(function ($practice) use ($date) {
                return Carbon::parse($practice->created_at)->format('Y-m-d') === $date->format('Y-m-d');
            });
            
            $dayQuestions = $dayPractices->sum('total_questions');
            $dayCorrect = $dayPractices->sum('correct_answers');
            
            $weeklyData[] = [
                'date' => $date->format('M d'),
                'day' => $date->format('D'),
                'sessions' => $dayPractices->count(),
                'questions' => $dayQuestions,
                'correct' => $dayCorrect,
                'accuracy' => $dayQuestions > 0 ? round(($dayCorrect / $dayQuestions) * 100, 1) : 0,
            ];
        }
        
        return $weeklyData;
    }
    
    /**
     * Format seconds into human readable time
     */
    protected function formatTime(int $seconds): string {
        if ($seconds < 60) {
            return $seconds . 's';
        }
        
        $minutes = floor($seconds / 60);
        $remainingSeconds = $seconds % 60;
        
        if ($minutes < 60) {
            return $minutes . 'm ' . $remainingSeconds . 's';
        }
        
        $hours = floor($minutes / 60);
        $remainingMinutes = $minutes % 60;
        
        return $hours . 'h ' . $remainingMinutes . 'm';
    }
}
