<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\LearningPathExercise;
use App\Models\TeacherAssignment;
use App\Models\TeacherAssignmentQuestion;
use App\Models\TeacherAssignmentRecipient;
use App\Models\TeacherClass;
use App\Models\TeacherStudentRelationship;
use App\Services\LearningPathQuestionGenerator;
use App\Services\MusicTheoryService;
use App\Services\Teacher\TeacherAiHomeworkService;
use App\Services\Teacher\TeacherAssignmentConfigFactory;
use App\Services\Teacher\TeacherAssignmentService;
use App\Services\Teacher\TeacherCapabilityService;
use Illuminate\Http\Request;
use InvalidArgumentException;

class TeacherAssignmentController extends Controller
{
    /**
     * Content fields a teacher may edit per practice type. These are the raw
     * keys inside the question_data snapshot that define the question (notes,
     * intervals, meta) — the same set surfaced read-only in previewMeta().
     */
    private const EDITABLE_FIELDS = [
        'single-note-practice' => ['target', 'octave'],
        'melodic-interval-practice' => ['interval', 'note1', 'note2', 'octave', 'note2_octave'],
        'harmonic-interval-practice' => ['interval', 'note1', 'note2', 'octave', 'note2_octave'],
        'interval-direction-practice' => ['note1', 'note2', 'octave', 'note2_octave'],
        'interval-construction-practice' => ['interval', 'note1', 'note2', 'octave'],
        'interval-comparison-practice' => ['interval_a', 'interval_b', 'octave', 'clef'],
        'chord-practice' => ['chord_type', 'root_note', 'voicing', 'inversion', 'octave'],
        'scale-practice' => ['scale_type', 'root_note', 'direction', 'octave'],
        'rhythm-practice' => ['time_signature', 'note_values', 'tempo', 'bars'],
        'melodic-dictation' => ['key_signature', 'time_signature', 'tempo', 'clef', 'notes'],
    ];

    public function __construct(
        private TeacherCapabilityService $capabilities,
        private TeacherAssignmentService $assignments,
        private TeacherAssignmentConfigFactory $configFactory,
    ) {}

    public function index(Request $request)
    {
        abort_unless($this->capabilities->canCreateAssignments($request->user()), 403);

        $assignments = TeacherAssignment::withCount([
            'recipients',
            'recipients as completed_count' => fn ($q) => $q->where('status', TeacherAssignmentRecipient::STATUS_COMPLETED),
        ])
            ->forTeacher($request->user()->id)
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('teacher.assignments.index', ['assignments' => $assignments]);
    }

    public function create(Request $request)
    {
        abort_unless($this->capabilities->canCreateAssignments($request->user()), 403);

        $profile = $request->user()->teacherProfile;

        return view('teacher.assignments.create', [
            'practiceTypes' => $this->configFactory->supportedTypes(),
            'lpExercises' => LearningPathExercise::where('is_active', true)->orderBy('sort_order')->get(),
            'canUseAi' => $this->capabilities->canUseAIHomeworkBuilder($request->user()),
            'mediaLibrary' => $profile ? $profile->media()->orderByDesc('created_at')->get() : collect(),
        ]);
    }

    /** AI prompt → suggested structured settings (JSON, premium only). */
    public function aiSuggest(Request $request, TeacherAiHomeworkService $ai)
    {
        abort_unless($this->capabilities->canUseAIHomeworkBuilder($request->user()), 403);

        $request->validate(['prompt' => 'required|string|min:10|max:2000']);

        try {
            $suggestion = $ai->interpretPrompt($request->prompt);
        } catch (InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        } catch (\Throwable $e) {
            // API/auth/rate-limit failures are not the teacher's fault — do not
            // tell them to "edit the prompt"; surface an honest service error.
            \Log::error('AI homework suggestion failed: '.$e->getMessage());

            return response()->json(['error' => __('teacher.assignments.ai_error')], 503);
        }

        return response()->json($suggestion);
    }

    public function store(Request $request)
    {
        abort_unless($this->capabilities->canCreateAssignments($request->user()), 403);

        $validated = $request->validate([
            'title' => 'required|string|max:150',
            'description' => 'nullable|string|max:2000',
            'instructions' => 'nullable|string|max:5000',
            'type' => 'required|in:exercise,learning_path,ai_generated,practice_goal',
            'practice_type' => 'nullable|string',
            'learning_path_exercise_id' => 'nullable|integer|exists:learning_path_exercises,id',
            'difficulty' => 'nullable|in:beginner,intermediate,advanced',
            'question_count' => 'nullable|integer|min:3|max:30',
            'starts_at' => 'nullable|date',
            'due_at' => 'nullable|date|after:starts_at',
            'max_attempts' => 'nullable|integer|min:1|max:20',
            'daily_practice_minutes' => 'nullable|integer|min:5|max:240',
            'weekly_practice_minutes' => 'nullable|integer|min:10|max:1000',
            'reward_label' => 'nullable|string|max:100',
            'ai_prompt' => 'nullable|string|max:2000',
            'overrides' => 'nullable|string', // JSON from the AI suggestion flow
            'media_ids' => 'nullable|array',
            'media_ids.*' => 'integer',
        ]);

        $type = $validated['type'];
        $config = null;
        $practiceType = $validated['practice_type'] ?? null;

        try {
            if ($type === TeacherAssignment::TYPE_LEARNING_PATH) {
                $exercise = LearningPathExercise::findOrFail($validated['learning_path_exercise_id'] ?? 0);
                $practiceType = $exercise->config_json['practice_type'];
            } elseif ($type !== TeacherAssignment::TYPE_PRACTICE_GOAL) {
                $overrides = json_decode($validated['overrides'] ?? '[]', true) ?: [];
                $config = $this->configFactory->build(
                    (string) $practiceType,
                    $validated['difficulty'] ?? 'beginner',
                    $overrides,
                );
            }
        } catch (InvalidArgumentException $e) {
            return back()->withErrors(['practice_type' => $e->getMessage()])->withInput();
        }

        $assignment = TeacherAssignment::create([
            'teacher_id' => $request->user()->id,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'instructions' => $validated['instructions'] ?? null,
            'type' => $type,
            'practice_type' => $practiceType,
            'learning_path_exercise_id' => $validated['learning_path_exercise_id'] ?? null,
            'config_json' => $config,
            'ai_prompt' => $validated['ai_prompt'] ?? null,
            'difficulty' => $validated['difficulty'] ?? null,
            'question_count' => (int) ($validated['question_count'] ?? 10),
            'starts_at' => $validated['starts_at'] ?? null,
            'due_at' => $validated['due_at'] ?? null,
            'max_attempts' => $validated['max_attempts'] ?? null,
            'daily_practice_minutes' => $validated['daily_practice_minutes'] ?? null,
            'weekly_practice_minutes' => $validated['weekly_practice_minutes'] ?? null,
            'reward_label' => $validated['reward_label'] ?? null,
            'status' => TeacherAssignment::STATUS_DRAFT,
        ]);

        // Attach files chosen from the teacher's own media library (practice
        // goals mainly). Only the teacher's own media may be attached.
        if (! empty($validated['media_ids']) && ($profile = $request->user()->teacherProfile)) {
            $ownIds = $profile->media()->whereIn('id', $validated['media_ids'])->pluck('id')->all();
            $assignment->media()->sync($ownIds);
        }

        if ($type !== TeacherAssignment::TYPE_PRACTICE_GOAL) {
            try {
                $this->assignments->generateQuestions($assignment);
            } catch (InvalidArgumentException $e) {
                $assignment->delete();

                return back()->withErrors(['practice_type' => $e->getMessage()])->withInput();
            }
        }

        return redirect()->route('teacher.assignments.show', $assignment)
            ->with('status', 'assignment-created');
    }

    public function show(Request $request, TeacherAssignment $assignment)
    {
        $this->authorizeAssignment($request, $assignment);

        $assignment->load(['questions', 'recipients.student', 'recipients.attempts', 'media']);

        $activeStudents = TeacherStudentRelationship::with('student')
            ->active()
            ->where('teacher_id', $request->user()->id)
            ->get()
            ->pluck('student');

        return view('teacher.assignments.show', [
            'assignment' => $assignment,
            'previews' => $this->questionPreviews($assignment),
            'students' => $activeStudents,
            'classes' => TeacherClass::active()->where('teacher_id', $request->user()->id)->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, TeacherAssignment $assignment)
    {
        $this->authorizeAssignment($request, $assignment);

        $validated = $request->validate([
            'title' => 'required|string|max:150',
            'description' => 'nullable|string|max:2000',
            'instructions' => 'nullable|string|max:5000',
            'starts_at' => 'nullable|date',
            'due_at' => 'nullable|date',
            'max_attempts' => 'nullable|integer|min:1|max:20',
            'reward_label' => 'nullable|string|max:100',
        ]);

        $assignment->update($validated);

        return back()->with('status', 'assignment-updated');
    }

    public function regenerateQuestions(Request $request, TeacherAssignment $assignment)
    {
        $this->authorizeAssignment($request, $assignment);

        try {
            $this->assignments->generateQuestions($assignment);
        } catch (InvalidArgumentException $e) {
            return back()->withErrors(['questions' => $e->getMessage()]);
        }

        return back()->with('status', 'questions-regenerated');
    }

    public function regenerateQuestion(Request $request, TeacherAssignment $assignment, TeacherAssignmentQuestion $question)
    {
        $this->authorizeAssignment($request, $assignment);
        abort_unless($question->teacher_assignment_id === $assignment->id, 404);

        try {
            $this->assignments->regenerateQuestion($assignment, $question);
        } catch (InvalidArgumentException $e) {
            return back()->withErrors(['questions' => $e->getMessage()]);
        }

        return back()->with('status', 'question-regenerated');
    }

    /**
     * Edit a single question's content in a draft. The teacher may change any
     * content field (notes, interval, meta) and the answer options; the merged
     * values are written straight back into the immutable-until-sent snapshot,
     * then previews (audio, notation, correct answer) recompute from them.
     */
    public function updateQuestion(Request $request, TeacherAssignment $assignment, TeacherAssignmentQuestion $question)
    {
        $this->authorizeAssignment($request, $assignment);
        abort_unless($question->teacher_assignment_id === $assignment->id, 404);

        if ($assignment->questionsLocked()) {
            return back()->withErrors(['questions' => __('teacher.assignments.error_locked')]);
        }

        $validated = $request->validate([
            'fields' => 'nullable|array',
            'fields.*' => 'nullable|string|max:500',
            'options' => 'nullable|string|max:4000',
        ]);

        $slug = (string) $assignment->practice_type;
        $allowed = self::EDITABLE_FIELDS[$slug] ?? [];
        $data = $question->question_data;

        foreach ($validated['fields'] ?? [] as $key => $value) {
            if (! in_array($key, $allowed, true)) {
                continue;
            }
            $original = $data[$key] ?? null;
            $value = (string) ($value ?? '');

            // Array-typed fields (notes, note_values) round-trip as comma lists.
            $data[$key] = is_array($original)
                ? array_values(array_filter(array_map('trim', explode(',', $value)), fn ($v) => $v !== ''))
                : trim($value);
        }

        if (array_key_exists('options', $validated) && $validated['options'] !== null) {
            $data['other_options'] = $this->parseOptionsInput($validated['options'], $data['other_options'] ?? null);
        }

        $question->update(['question_data' => $data]);

        return back()->with('status', 'question-updated');
    }

    /**
     * Parse the answer-options textarea back into the shape the snapshot uses:
     * token arrays (rhythm) stay one-option-per-line/space-separated, plain
     * arrays (chord/scale/interval distractors) are comma lists, and the
     * single-note string list stays a comma-separated string.
     */
    private function parseOptionsInput(string $input, mixed $existing): array|string
    {
        $isNested = is_array($existing) && isset($existing[0]) && is_array($existing[0]);

        if ($isNested) {
            $lines = preg_split('/\r\n|\r|\n/', $input) ?: [];

            return array_values(array_filter(array_map(
                fn ($line) => array_values(array_filter(preg_split('/\s+/', trim($line)) ?: [], fn ($t) => $t !== '')),
                $lines,
            ), fn ($opt) => ! empty($opt)));
        }

        $parts = array_values(array_filter(array_map('trim', explode(',', $input)), fn ($v) => $v !== ''));

        // single-note stores other_options as a comma-separated string.
        return is_array($existing) ? $parts : implode(',', $parts);
    }

    /**
     * Teacher-only "solve as a student" preview. Loads the assignment's real
     * question snapshots into a preview session that the practice engine plays
     * exactly as a student sees it, but answers are graded without recording
     * any attempt, student stat, or teacher practice stat.
     */
    public function preview(Request $request, TeacherAssignment $assignment)
    {
        $this->authorizeAssignment($request, $assignment);

        if (! $assignment->practice_type) {
            return back()->withErrors(['questions' => __('teacher.assignments.error_no_questions')]);
        }

        $questions = $assignment->questions()->orderBy('position')->pluck('question_data')->values()->all();

        if ($questions === []) {
            return back()->withErrors(['questions' => __('teacher.assignments.error_no_questions')]);
        }

        // A fresh preview must not be hijacked by any stale practice session.
        session()->forget([
            'learning_path_session',
            'exercise_practice_session',
            'exercise_settings',
            'teacher_assignment_session',
        ]);

        session([
            'teacher_assignment_preview_session' => [
                'assignment_id' => $assignment->id,
                'practice_type' => $assignment->practice_type,
                'question_count' => count($questions),
                'questions' => $questions,
            ],
            'exercise_back_url' => route('teacher.assignments.show', $assignment),
        ]);

        return redirect('/practice/'.$assignment->practice_type);
    }

    public function destroyQuestion(Request $request, TeacherAssignment $assignment, TeacherAssignmentQuestion $question)
    {
        $this->authorizeAssignment($request, $assignment);
        abort_unless($question->teacher_assignment_id === $assignment->id, 404);

        try {
            $this->assignments->removeQuestion($assignment, $question);
        } catch (InvalidArgumentException $e) {
            return back()->withErrors(['questions' => $e->getMessage()]);
        }

        return back()->with('status', 'question-removed');
    }

    public function send(Request $request, TeacherAssignment $assignment)
    {
        $this->authorizeAssignment($request, $assignment);

        $validated = $request->validate([
            'student_ids' => 'nullable|array',
            'student_ids.*' => 'integer',
            'class_ids' => 'nullable|array',
            'class_ids.*' => 'integer',
        ]);

        try {
            $count = $this->assignments->send(
                $assignment,
                $request->user(),
                $validated['student_ids'] ?? [],
                $validated['class_ids'] ?? [],
            );
        } catch (InvalidArgumentException $e) {
            return back()->withErrors(['recipients' => $e->getMessage()]);
        }

        return back()->with('status', 'assignment-sent')->with('sent-count', $count);
    }

    public function duplicate(Request $request, TeacherAssignment $assignment)
    {
        $this->authorizeAssignment($request, $assignment);

        $copy = $assignment->replicate(['status', 'sent_at']);
        $copy->title = $assignment->title.' (copy)';
        $copy->status = TeacherAssignment::STATUS_DRAFT;
        $copy->sent_at = null;
        $copy->save();

        foreach ($assignment->questions as $question) {
            TeacherAssignmentQuestion::create([
                'teacher_assignment_id' => $copy->id,
                'position' => $question->position,
                'question_data' => $question->question_data,
            ]);
        }

        return redirect()->route('teacher.assignments.show', $copy)->with('status', 'assignment-duplicated');
    }

    public function archive(Request $request, TeacherAssignment $assignment)
    {
        $this->authorizeAssignment($request, $assignment);

        $assignment->update(['status' => TeacherAssignment::STATUS_ARCHIVED]);

        return redirect()->route('teacher.assignments.index')->with('status', 'assignment-archived');
    }

    public function destroy(Request $request, TeacherAssignment $assignment)
    {
        $this->authorizeAssignment($request, $assignment);
        abort_unless($assignment->isDraft(), 403);

        $assignment->delete();

        return redirect()->route('teacher.assignments.index')->with('status', 'assignment-deleted');
    }

    private function authorizeAssignment(Request $request, TeacherAssignment $assignment): void
    {
        abort_unless($this->capabilities->canCreateAssignments($request->user()), 403);
        abort_unless($assignment->teacher_id === $request->user()->id, 403);
    }

    /**
     * Build review previews from the immutable snapshots: playable notes,
     * play mode, correct answer, and answer options per question.
     */
    private function questionPreviews(TeacherAssignment $assignment): array
    {
        if (! $assignment->practice_type || $assignment->questions->isEmpty()) {
            return [];
        }

        $generator = app(LearningPathQuestionGenerator::class);
        $music = app(MusicTheoryService::class);
        $slug = $assignment->practice_type;

        return $assignment->questions->map(function (TeacherAssignmentQuestion $question) use ($generator, $music, $slug) {
            $data = $question->question_data;
            $models = $generator->reconstructFromSession([$data], $slug);
            $model = $models->first();

            $correct = rescue(fn () => $generator->getAnswerFromSessionQuestion($data, $slug), '', false);

            [$notes, $mode] = $this->previewNotes($model, $data, $slug, $music);

            $options = $data['other_options'] ?? null;
            if (is_string($options)) {
                $options = array_filter(array_map('trim', explode(',', $options)));
            }
            // Rhythm options are token arrays — flatten each to a readable string.
            if (is_array($options)) {
                $options = array_map(
                    fn ($opt) => is_array($opt) ? implode(' ', $opt) : $opt,
                    $options,
                );
            }

            return [
                'id' => $question->id,
                'position' => $question->position,
                'correct' => is_array($correct) ? implode(' ', $correct) : $correct,
                'options' => $options,
                'notes' => $notes,
                'mode' => $mode,
                'meta' => $this->previewMeta($data, $slug),
                // Raw values for the inline edit form (arrays as comma lists).
                'edit_fields' => $this->editFieldValues($data, $slug),
                'options_input' => $this->optionsInputValue($data['other_options'] ?? null),
            ];
        })->values()->all();
    }

    /** Raw per-field strings for the edit form (array fields → comma lists). */
    private function editFieldValues(array $data, string $slug): array
    {
        $values = [];
        foreach (self::EDITABLE_FIELDS[$slug] ?? [] as $key) {
            $v = $data[$key] ?? '';
            $values[$key] = is_array($v)
                ? implode(', ', array_map(fn ($x) => is_array($x) ? implode(' ', $x) : $x, $v))
                : (string) $v;
        }

        return $values;
    }

    /** other_options → editable textarea string (round-trips parseOptionsInput). */
    private function optionsInputValue(mixed $options): string
    {
        if ($options === null) {
            return '';
        }
        if (is_array($options) && isset($options[0]) && is_array($options[0])) {
            // Rhythm token arrays: one option per line, tokens space-separated.
            return implode("\n", array_map(fn ($opt) => implode(' ', $opt), $options));
        }
        if (is_array($options)) {
            return implode(', ', $options);
        }

        return (string) $options;
    }

    private function previewNotes(?object $model, array $data, string $slug, MusicTheoryService $music): array
    {
        $note = fn ($n, $o) => $n !== null ? $n.$o : null;

        return match ($slug) {
            'single-note-practice' => [[$note($data['target'] ?? null, $data['octave'] ?? '4')], 'single'],
            'melodic-interval-practice', 'interval-direction-practice' => [
                array_filter([
                    $note($data['note1'] ?? null, $data['octave'] ?? '4'),
                    $note($data['note2'] ?? null, $data['note2_octave'] ?? ($data['octave'] ?? '4')),
                ]),
                'sequential',
            ],
            'harmonic-interval-practice' => [
                array_filter([
                    $note($data['note1'] ?? null, $data['octave'] ?? '4'),
                    $note($data['note2'] ?? null, $data['note2_octave'] ?? ($data['octave'] ?? '4')),
                ]),
                'simultaneous',
            ],
            'interval-construction-practice' => [
                array_filter([
                    $note($data['note1'] ?? null, $data['octave'] ?? '4'),
                    $note($data['note2'] ?? null, $data['note2_octave'] ?? ($data['octave'] ?? '4')),
                ]),
                'sequential',
            ],
            'interval-comparison-practice' => [
                $this->comparisonNotes($data, $music),
                'pairs',
            ],
            'chord-practice' => [
                $model?->note_array ?? [],
                ($data['voicing'] ?? 'block') === 'arpeggiated' ? 'arpeggio' : 'simultaneous',
            ],
            'scale-practice' => [$model?->note_array ?? [], 'sequential'],
            'melodic-dictation' => [is_array($data['notes'] ?? null) ? $data['notes'] : [], 'sequential'],
            default => [[], 'none'],
        };
    }

    /** Both comparison pairs as playable notes: A1,A2 then B1,B2. */
    private function comparisonNotes(array $data, MusicTheoryService $music): array
    {
        $octave = (string) ($data['octave'] ?? '4');
        $notes = [];

        foreach (['interval_a', 'interval_b'] as $key) {
            $pair = $data[$key] ?? null;
            if (! is_string($pair)) {
                continue;
            }
            foreach (explode(',', $pair) as $n) {
                $notes[] = trim($n).$octave;
            }
        }

        return $notes;
    }

    private function previewMeta(array $data, string $slug): array
    {
        $keep = self::EDITABLE_FIELDS[$slug] ?? [];

        return collect($data)->only($keep)->map(function ($v) {
            return is_array($v) ? implode(' ', array_map(fn ($x) => is_array($x) ? json_encode($x) : $x, $v)) : $v;
        })->all();
    }
}
