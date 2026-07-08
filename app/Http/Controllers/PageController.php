<?php

namespace App\Http\Controllers;

use App\Models\ChordPractice;
use App\Models\HarmonicIntervalPractice;
use App\Models\IntervalComparisonPractice;
use App\Models\IntervalConstructionPractice;
use App\Models\IntervalDirectionPractice;
use App\Models\LearningPathExercise;
use App\Models\MelodicDictationPractice;
use App\Models\MelodicIntervalPractice;
use App\Models\Practice;
use App\Models\RhythmPractice;
use App\Models\ScalePractice;
use App\Models\SingleNotePractice;
use App\Models\TeacherProfile;
use App\Models\TeacherReview;
use App\Models\TeacherStudentRelationship;
use App\Models\User;
use App\Models\UserLearningPathProgress;
use App\Models\UserPractice;
use App\Services\LearningPathQuestionGenerator;
use App\Services\MusicTheoryService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class PageController extends Controller
{
    //

    public function feedView()
    {
        return view('feed');
    }

    public function messagesView(Request $request)
    {
        return view('messages', ['to' => $request->query('to')]);
    }

    public function publicProfile(string $username)
    {
        $profileUser = User::with('profile', 'teacherProfile')->where('username', $username)->firstOrFail();

        $userPractices = UserPractice::where('user_id', $profileUser->id)->get();
        $totalSessions = $userPractices->count();
        $totalQuestions = $userPractices->sum('total_questions');
        $totalCorrect = $userPractices->sum('correct_answers');
        $overallAccuracy = $totalQuestions > 0
            ? round(($totalCorrect / $totalQuestions) * 100, 1)
            : 0;

        // Teacher-specific stats: only computed (and shown) for teacher accounts.
        $isTeacher = $profileUser->hasTeacherAccount();
        $studentCount = 0;
        $avgRating = 0;
        $ratingCount = 0;
        $teacherSlug = null;

        if ($isTeacher) {
            $studentCount = TeacherStudentRelationship::active()
                ->where('teacher_id', $profileUser->id)
                ->count();

            if ($tp = $profileUser->teacherProfile) {
                $teacherSlug = $tp->status === TeacherProfile::STATUS_APPROVED ? $tp->slug : null;
                $reviews = TeacherReview::approved()->where('teacher_profile_id', $tp->id);
                $ratingCount = (clone $reviews)->count();
                $avgRating = $ratingCount > 0 ? round((clone $reviews)->avg('rating'), 1) : 0;
            }
        }

        return view('public-profile', [
            'profileUser' => $profileUser,
            'followersCount' => $profileUser->followersCount(),
            'followingCount' => $profileUser->followingCount(),
            'totalSessions' => $totalSessions,
            'overallAccuracy' => $overallAccuracy,
            'isTeacher' => $isTeacher,
            'studentCount' => $studentCount,
            'avgRating' => $avgRating,
            'ratingCount' => $ratingCount,
            'teacherSlug' => $teacherSlug,
        ]);
    }

    public function learnView()
    {
        $lpExercises = LearningPathExercise::with('category')
            ->where('is_active', true)
            ->orderBy('category_id')
            ->orderBy('sort_order')
            ->get();

        $lpProgress = [];
        if (Auth::check()) {
            $userId = Auth::id();
            UserLearningPathProgress::where('user_id', $userId)
                ->get()
                ->each(function ($p) use (&$lpProgress) {
                    $lpProgress[$p->learning_path_exercise_id] = $p;
                });
        }

        return view('learn', compact('lpExercises', 'lpProgress'));
    }

    public function aiExercisesView()
    {
        $practices = Practice::all();

        return view('ai_exercise', compact('practices'));
    }

    public function startAiSession(Request $request)
    {
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

    public function practiceView($slug)
    {
        $backUrl = session()->pull('exercise_back_url', '/learn');

        // Unless a fresh Exercise-Setup launch is incoming (exercise_settings present,
        // consumed by the Livewire component's mount()), any leftover
        // exercise_practice_session is stale. Clear it so it cannot hijack the
        // answer check — checkAnswer() evaluates that session before LP/DB lookup.
        if (! session()->has('exercise_settings')) {
            session()->forget('exercise_practice_session');
        }

        // Teacher assignment session — highest priority. The student plays the
        // assignment's immutable question snapshot; the session is cleared by
        // PracticeController::checkTeacherAssignmentAnswer() after the last
        // question. Navigating to a different practice type abandons the run.
        if ($ta = session('teacher_assignment_session')) {
            if (($ta['practice_type'] ?? null) === $slug) {
                $generator = app(LearningPathQuestionGenerator::class);
                $practices = $generator->reconstructFromSession($ta['questions'], $slug);

                return view('practice', [
                    'practices' => $practices,
                    'slug' => $slug,
                    'backUrl' => $backUrl,
                ]);
            }

            session()->forget('teacher_assignment_session');
        }

        // Teacher assignment PREVIEW session — the teacher solving their own
        // assignment as a student. Same snapshot playback; answers are graded
        // but never recorded (see PracticeController::checkTeacherAssignmentPreview).
        if ($tp = session('teacher_assignment_preview_session')) {
            if (($tp['practice_type'] ?? null) === $slug) {
                $generator = app(LearningPathQuestionGenerator::class);
                $practices = $generator->reconstructFromSession($tp['questions'], $slug);

                return view('practice', [
                    'practices' => $practices,
                    'slug' => $slug,
                    'backUrl' => $backUrl,
                ]);
            }

            session()->forget('teacher_assignment_preview_session');
        }

        // LP session guard: if a learning path session is active for this practice type, use its questions.
        // NOTE: We intentionally do NOT forget the session here — PracticeController::checkLPAnswer()
        // clears it after the final question so answer-checking can reference session data.
        if ($lp = session('learning_path_session')) {
            if (($lp['practice_type'] ?? null) === $slug) {
                $generator = app(LearningPathQuestionGenerator::class);
                $practices = $generator->reconstructFromSession($lp['questions'], $slug);

                return view('practice', [
                    'practices' => $practices,
                    'slug' => $slug,
                    'backUrl' => $backUrl,
                ]);
            }
        }

        $practiceMap = [
            'single-note-practice' => SingleNotePractice::class,
            'interval-direction-practice' => IntervalDirectionPractice::class,
            'interval-comparison-practice' => IntervalComparisonPractice::class,
            'melodic-interval-practice' => MelodicIntervalPractice::class,
            'harmonic-interval-practice' => HarmonicIntervalPractice::class,
            'interval-construction-practice' => IntervalConstructionPractice::class,
            'chord-practice' => ChordPractice::class,
            'scale-practice' => ScalePractice::class,
            'rhythm-practice' => RhythmPractice::class,
            'melodic-dictation' => MelodicDictationPractice::class,
        ];

        $practiceClass = $practiceMap[$slug] ?? null;

        if (! $practiceClass) {
            abort(404, 'Practice not found');
        }

        $practices = $practiceClass::inRandomOrder()->get();

        return view('practice', [
            'practices' => $practices,
            'slug' => $slug,
            'backUrl' => $backUrl,
        ]);
    }

    /**
     * Display the mixed practice setup page or redirect with session data
     */
    public function practiceMixedView()
    {
        // Check if we have session data for mixed practice
        $sessionConfig = session('mixed_practice_config');

        if (! $sessionConfig) {
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
    public function startMixedPractice(Request $request)
    {
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
    protected function getMixedPractices(array $config): Collection
    {
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

    public function aiPracticeView()
    {
        // Get AI-generated questions from session
        $questions = session('ai_practice_questions');
        $title = session('ai_practice_title', 'AI Generated Practice');

        if (! $questions || empty($questions)) {
            // No questions in session, redirect back to AI exercises page
            return redirect()->route('ai.exercises')->with('error', 'No practice questions generated. Please try again.');
        }

        // Clear session after retrieving
        session()->forget(['ai_practice_questions', 'ai_practice_title']);

        // Convert raw question data to practice objects
        $practices = $this->convertAIQuestionsToPractices($questions);

        if ($practices->isEmpty()) {
            return redirect()->route('ai.exercises')->with('error', 'Could not process the generated questions. Please try again.');
        }

        return view('practice-mixed', [
            'practices' => $practices,
            'title' => $title,
        ]);
    }

    /**
     * AI Assisted Exercises — dedicated Melodic Dictation page.
     *
     * The Livewire component (PracticeAiMelodicDictation) consumes the
     * 'ai_dictation_settings' session written by AIController and generates
     * the questions itself, mirroring the Exercise Setup flow. Without that
     * session (refresh / direct visit) the user is sent back to the AI page.
     */
    public function aiDictationPracticeView()
    {
        if (! session()->has('ai_dictation_settings')) {
            return redirect()->route('ai.exercises')->with('error', 'Your AI session has expired. Please start a new session.');
        }

        return view('practice-ai-dictation', ['practices' => []]);
    }

    /**
     * Convert AI-generated question arrays to practice model instances
     */
    public function pianoStudioView()
    {
        return view('piano-studio');
    }

    /**
     * Convert AI-generated question arrays to practice model instances
     */
    protected function convertAIQuestionsToPractices(array $questions): Collection
    {
        $music = app(MusicTheoryService::class);

        return collect($questions)->map(function ($question) use ($music) {
            $type = $question['type'] ?? null;
            $tempId = rand(1, 1000000);

            return match ($type) {

                // ── Interval Direction ───────────────────────────────────────
                // OpenAI provides note1, note2 (bare names), octave, direction.
                // We MUST re-derive note2_octave and direction from MIDI pitches:
                // OpenAI doesn't know about cross-octave boundaries, so e.g.
                // "B→D# ascending" would be stored as D#4 (wrong) without this.
                'interval-direction' => tap(new IntervalDirectionPractice, function ($p) use ($question, $tempId, $music) {
                    $note1 = $music->normalizeNote($question['note1'] ?? 'C');
                    $note2 = $music->normalizeNote($question['note2'] ?? 'E');
                    $octave1 = (int) ($question['octave'] ?? 4);
                    $intent = $question['direction'] ?? 'ascending'; // OpenAI's stated intent

                    // Resolve correct octave for note2 based on the direction intent
                    $octave2 = $music->resolveNote2OctaveFromDirection($note1, $octave1, $note2, $intent);

                    // Re-derive direction from actual MIDI pitches (never trust OpenAI's label)
                    $direction = $music->getDirection($note1, $octave1, $note2, $octave2);

                    $p->id = $tempId;
                    $p->clef = $question['clef'] ?? 'treble';
                    $p->note1 = $note1;
                    $p->note2 = $note2;
                    $p->octave = (string) $octave1;
                    $p->note2_octave = $octave2;
                    $p->direction = $direction;
                }),

                // ── Melodic Interval ─────────────────────────────────────────
                // The correct answer is the interval name. note2 is display-only.
                // We recalculate note2 and note2_octave from the interval so the
                // staff and audio always match the stated interval name exactly.
                // Direction comes from the local generator (Exercise Setup rules:
                // ascending/descending/mixed); questions without one — legacy
                // sessions, OpenAI output — stay ascending as before.
                'melodic-interval' => tap(new MelodicIntervalPractice, function ($p) use ($question, $tempId, $music) {
                    $note1 = $music->normalizeNote($question['note1'] ?? 'C');
                    $octave1 = (int) ($question['octave'] ?? 4);
                    $interval = $music->normalizeIntervalName($question['interval'] ?? 'Major 3rd');
                    $direction = ($question['direction'] ?? 'ascending') === 'descending' ? 'descending' : 'ascending';

                    $result = $direction === 'descending'
                        ? $music->preferredNoteBelowByInterval($note1, $octave1, $interval)
                        : $music->preferredNoteAboveByInterval($note1, $octave1, $interval);
                    $note2 = $result['note'] ?? $music->normalizeNote($question['note2'] ?? 'E');
                    $octave2 = $result['octave'] ?? $octave1;

                    $p->id = $tempId;
                    $p->interval = $interval;
                    $p->note1 = $note1;
                    $p->note2 = $note2;
                    $p->octave = (string) $octave1;
                    $p->note2_octave = $octave2;
                    $p->direction = $direction;
                    $p->options = $question['options'] ?? null;
                }),

                // ── Harmonic Interval ────────────────────────────────────────
                // Identical logic to melodic-interval (only playback differs).
                'harmonic-interval' => tap(new HarmonicIntervalPractice, function ($p) use ($question, $tempId, $music) {
                    $note1 = $music->normalizeNote($question['note1'] ?? 'C');
                    $octave1 = (int) ($question['octave'] ?? 4);
                    $interval = $music->normalizeIntervalName($question['interval'] ?? 'Major 3rd');

                    $result = $music->preferredNoteAboveByInterval($note1, $octave1, $interval);
                    $note2 = $result['note'] ?? $music->normalizeNote($question['note2'] ?? 'E');
                    $octave2 = $result['octave'] ?? $octave1;

                    $p->id = $tempId;
                    $p->interval = $interval;
                    $p->note1 = $note1;
                    $p->note2 = $note2;
                    $p->octave = (string) $octave1;
                    $p->note2_octave = $octave2;
                    $p->options = $question['options'] ?? null;
                }),

                // ── Interval Construction ────────────────────────────────────
                // note2 IS the correct answer; must be exactly right.
                'interval-construction' => tap(new IntervalConstructionPractice, function ($p) use ($question, $tempId, $music) {
                    $note1 = $music->normalizeNote($question['note1'] ?? 'C');
                    $octave1 = (int) ($question['octave'] ?? 4);
                    $interval = $music->normalizeIntervalName($question['interval'] ?? 'Major 3rd');
                    $direction = ($question['direction'] ?? 'ascending') === 'descending' ? 'descending' : 'ascending';

                    // Locally generated questions (Exercise Setup rules) carry a
                    // diatonically spelled note2 + octave that the answer options
                    // were built around — keep them verbatim. Recalculate from the
                    // interval name only when they are missing (legacy sessions,
                    // OpenAI output), as before.
                    if (! empty($question['note2']) && isset($question['note2_octave'])) {
                        $note2 = $question['note2'];
                        $octave2 = (int) $question['note2_octave'];
                    } else {
                        $result = $direction === 'descending'
                            ? $music->preferredNoteBelowByInterval($note1, $octave1, $interval)
                            : $music->preferredNoteAboveByInterval($note1, $octave1, $interval);
                        $note2 = $result['note'] ?? $music->normalizeNote($question['note2'] ?? 'E');
                        $octave2 = $result['octave'] ?? $octave1;
                    }

                    $p->id = $tempId;
                    $p->interval = $interval;
                    $p->note1 = $note1;
                    $p->note2 = $note2;
                    $p->octave = (string) $octave1;
                    $p->note2_octave = $octave2;
                    $p->direction = $direction;
                    $p->options = $question['options'] ?? null;
                }),

                // ── Interval Comparison ──────────────────────────────────────
                // Re-derive target from semitone distances so old session data is
                // also corrected — GPT labels this wrong more often than not.
                'interval-comparison' => tap(new IntervalComparisonPractice, function ($p) use ($question, $tempId, $music) {
                    $intervalA = $question['interval_a'] ?? 'C,E';
                    $intervalB = $question['interval_b'] ?? 'C,G';

                    // Larger pair by same-octave pitch gap (matches staff + audio).
                    // Sanitization already drops true ties, so the fallback is just a guard.
                    $target = $music->largerIntervalPair($intervalA, $intervalB)
                        ?? ($question['target'] ?? 'a');

                    $p->id = $tempId;
                    $p->clef = $question['clef'] ?? 'treble';
                    $p->interval_a = $intervalA;
                    $p->interval_b = $intervalB;
                    $p->target = $target;
                    $p->octave = $question['octave'] ?? '4';
                }),

                // ── Single Note ──────────────────────────────────────────────
                'single-note' => tap(new SingleNotePractice, function ($p) use ($question, $tempId) {
                    $p->id = $tempId;
                    $p->target = $question['target'];
                    $p->target_type = $question['target_type'] ?? 'note';
                    $p->other_options = $question['other_options'];
                    $p->octave = $question['octave'] ?? '4';
                }),

                // ── Chord ─────────────────────────────────────────────────────
                'chord' => tap(new ChordPractice, function ($p) use ($question, $tempId) {
                    $p->id = $tempId;
                    $p->chord_type = $question['chord_type'];
                    $p->root_note = $question['root_note'];
                    $p->voicing = $question['voicing'] ?? 'block';
                    $p->inversion = $question['inversion'] ?? 0;
                    $p->octave = $question['octave'] ?? '4';
                    $p->other_options = $question['other_options'] ?? [];
                }),

                // ── Scale ─────────────────────────────────────────────────────
                'scale' => tap(new ScalePractice, function ($p) use ($question, $tempId) {
                    $p->id = $tempId;
                    $p->scale_type = $question['scale_type'];
                    $p->root_note = $question['root_note'];
                    $p->direction = $question['direction'] ?? 'ascending';
                    $p->octave = $question['octave'] ?? '4';
                    $p->other_options = $question['other_options'] ?? [];
                }),

                // ── Rhythm ────────────────────────────────────────────────────
                'rhythm' => tap(new RhythmPractice, function ($p) use ($question, $tempId) {
                    $p->id = $tempId;
                    $p->time_signature = $question['time_signature'] ?? '4/4';
                    $p->note_values = $question['note_values'] ?? [];
                    $p->other_options = $question['other_options'] ?? [];
                    $p->tempo = $question['tempo'] ?? 80;
                    $p->bars = $question['bars'] ?? 1;
                    $p->allowed_values = $question['allowed_values'] ?? [];
                    // Answer UI variant: 'build' (dictation builder), 'recognition'
                    // (pick the heard pattern) or 'reading' (tap the printed rhythm).
                    $p->rhythm_mode = $question['rhythm_mode'] ?? 'build';
                }),

                // ── Melodic Dictation ─────────────────────────────────────────
                'melodic_dictation' => tap(new MelodicDictationPractice, function ($p) use ($question, $tempId) {
                    $p->id = $tempId;
                    $p->notes = $question['notes'] ?? [];
                    $p->bars = $question['bars'] ?? 1;
                    $p->clef = $question['clef'] ?? 'treble';
                    $p->key_signature = $question['key_signature'] ?? 'C';
                    $p->tempo = $question['tempo'] ?? 60;
                    $p->include_rhythm = $question['include_rhythm'] ?? false;
                }),

                default => null,
            };
        })->filter();
    }

    /**
     * Display the user's progress page with detailed statistics
     */
    public function progressView()
    {
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
        $sortedBreakdown = collect($practiceBreakdown)->filter(fn ($p) => $p['sessions'] > 0)->sortByDesc('accuracy');
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
    protected function calculateStreak($userPractices): int
    {
        if ($userPractices->isEmpty()) {
            return 0;
        }

        $dates = $userPractices->pluck('created_at')
            ->map(fn ($date) => Carbon::parse($date)->format('Y-m-d'))
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
    protected function calculateWeeklyPerformance($userPractices): array
    {
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
    protected function formatTime(int $seconds): string
    {
        if ($seconds < 60) {
            return $seconds.'s';
        }

        $minutes = floor($seconds / 60);
        $remainingSeconds = $seconds % 60;

        if ($minutes < 60) {
            return $minutes.'m '.$remainingSeconds.'s';
        }

        $hours = floor($minutes / 60);
        $remainingMinutes = $minutes % 60;

        return $hours.'h '.$remainingMinutes.'m';
    }
}
