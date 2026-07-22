<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\LearningPathExercise;
use App\Models\TeacherAssignment;
use App\Models\TeacherAssignmentQuestion;
use App\Models\TeacherAssignmentRecipient;
use App\Models\TeacherClass;
use App\Models\TeacherStudentRelationship;
use App\Services\DictationRhythmService;
use App\Services\LearningPathQuestionGenerator;
use App\Services\MusicTheoryService;
use App\Services\RhythmDistractorService;
use App\Services\RhythmGroupingService;
use App\Services\Teacher\CrmQuotaService;
use App\Services\Teacher\TeacherAiHomeworkService;
use App\Services\Teacher\TeacherAssignmentConfigFactory;
use App\Services\Teacher\TeacherAssignmentService;
use App\Services\Teacher\TeacherCapabilityService;
use App\Services\TonalMelodyGenerator;
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
        'single-note-practice' => ['target', 'octave', 'clef', 'reference_note'],
        'melodic-interval-practice' => ['interval', 'note1', 'octave', 'direction', 'clef'],
        'harmonic-interval-practice' => ['interval', 'note1', 'octave', 'clef'],
        'interval-direction-practice' => ['note1', 'note2', 'octave', 'note2_octave', 'clef'],
        'interval-construction-practice' => ['interval', 'note1', 'octave', 'direction'],
        'interval-comparison-practice' => ['interval_a', 'interval_b', 'octave', 'clef'],
        'chord-practice' => ['chord_type', 'root_note', 'voicing', 'inversion', 'octave', 'clef'],
        'scale-practice' => ['scale_type', 'root_note', 'direction', 'octave', 'clef', 'tempo'],
        'rhythm-practice' => ['time_signature', 'note_values', 'tempo', 'bars'],
        'melodic-dictation' => ['key_signature', 'time_signature', 'tempo', 'clef', 'notes'],
    ];

    /**
     * Types whose A/B/C/D distractor list may be edited by hand in the modal.
     * single-note answers on the piano keyboard (no choices), comparison's
     * choices ARE the two intervals, and rhythm/dictation choices are always
     * recomputed server-side from the (possibly edited) question content.
     */
    private const CHOICES_EDITABLE = [
        'melodic-interval-practice',
        'harmonic-interval-practice',
        'chord-practice',
        'scale-practice',
    ];

    /** Note-name vocabulary offered in the edit modal (canonical spellings). */
    private const EDIT_NOTE_POOL = [
        'C', 'C#', 'Db', 'D', 'D#', 'Eb', 'E', 'F', 'F#', 'Gb', 'G', 'G#', 'Ab', 'A', 'A#', 'Bb', 'B',
    ];

    /** Every rhythm token the pattern builder may emit. */
    private const RHYTHM_EDIT_TOKENS = [
        'whole', 'dotted-half', 'half', 'dotted-quarter', 'quarter',
        'dotted-eighth', 'eighth', 'sixteenth', 'triplet-eighth',
        'whole_rest', 'half_rest', 'quarter_rest', 'eighth_rest',
    ];

    /** Note values selectable as dictation rhythm building blocks. */
    private const DICTATION_RHYTHM_VALUES = [
        'whole', 'half', 'dotted-half', 'quarter', 'dotted-quarter', 'eighth', 'dotted-eighth', 'sixteenth',
    ];

    private const DICTATION_KEYS = ['C', 'G', 'D', 'A', 'E', 'F', 'Bb', 'Eb', 'Ab'];

    /**
     * Which snapshot field holds each type's multiple-choice list, and whether
     * that stored list already contains the correct answer. Types absent here
     * (construction, direction, comparison, dictation) have no editable choices.
     */
    private const CHOICES_FIELD = [
        'single-note-practice' => 'other_options',
        'melodic-interval-practice' => 'options',
        'harmonic-interval-practice' => 'options',
        'chord-practice' => 'other_options',
        'scale-practice' => 'other_options',
        'rhythm-practice' => 'other_options',
    ];

    /** Types whose choices field stores the correct answer inline (not just distractors). */
    private const CHOICES_INCLUDE_CORRECT = [
        'single-note-practice',
        'melodic-interval-practice',
        'harmonic-interval-practice',
    ];

    public function __construct(
        private TeacherCapabilityService $capabilities,
        private TeacherAssignmentService $assignments,
        private TeacherAssignmentConfigFactory $configFactory,
        private CrmQuotaService $quota,
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

        $music = app(MusicTheoryService::class);
        $intervals = [];
        for ($s = 1; $s <= 12; $s++) {
            $name = $music->intervalNameFromSemitones($s);
            if ($name !== null) {
                $intervals[] = $name;
            }
        }

        return view('teacher.assignments.create', [
            'practiceTypes' => $this->configFactory->supportedTypes(),
            'lpExercises' => LearningPathExercise::where('is_active', true)->orderBy('sort_order')->get(),
            'canUseAi' => $this->capabilities->canUseAIHomeworkBuilder($request->user()),
            'mediaLibrary' => $profile ? $profile->media()->where('kind', 'document')->orderByDesc('created_at')->get() : collect(),
            // Canonical vocabularies for the per-type "exercise content" panel.
            'configOptions' => [
                'intervals' => $intervals,
                'notes' => self::EDIT_NOTE_POOL,
                'chord_types' => TeacherAssignmentConfigFactory::CHORD_TYPES,
                'scale_types' => TeacherAssignmentConfigFactory::SCALE_TYPES,
                'note_values' => TeacherAssignmentConfigFactory::NOTE_VALUES,
                'time_signatures' => TeacherAssignmentConfigFactory::TIME_SIGNATURES,
                'clefs' => TeacherAssignmentConfigFactory::CLEFS,
                'scale_tempos' => TeacherAssignmentConfigFactory::SCALE_TEMPOS,
                'dictation_keys' => self::DICTATION_KEYS,
                'dictation_values' => self::DICTATION_RHYTHM_VALUES,
            ],
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

        // Free-tier cap: max_active_assignments non-archived assignments.
        // Existing data is never deleted — only new creation is blocked.
        if (! $this->quota->canCreateAssignment($request->user())) {
            return back()->withInput()->withErrors([
                'limit' => __('teacher.limits.assignments_reached', [
                    'limit' => $this->quota->limit($request->user(), 'max_active_assignments'),
                ]),
            ]);
        }

        // Free tier may not use the AI homework builder output either.
        if (! $this->capabilities->canUseAIHomeworkBuilder($request->user())
            && ($request->input('type') === 'ai_generated' || filled($request->input('ai_prompt')))) {
            return back()->withInput()->withErrors([
                'limit' => __('teacher.limits.assignment_ai_premium'),
            ]);
        }

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

        return redirect()->route(crm_prefix().'.assignments.show', $assignment)
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
            'editSchema' => $this->editSchema($assignment->practice_type),
            'choicesEditable' => in_array($assignment->practice_type, self::CHOICES_EDITABLE, true),
            'dictationRhythmValues' => self::DICTATION_RHYTHM_VALUES,
        ]);
    }

    /**
     * Field descriptors for the type-aware edit modal: which inputs to show
     * per practice type and the canonical vocabulary each select offers, so
     * teachers can never type a value the engine does not understand.
     */
    private function editSchema(?string $slug): array
    {
        if (! $slug) {
            return [];
        }

        $music = app(MusicTheoryService::class);
        $sel = fn (array $vals) => array_combine($vals, $vals);

        $intervals = [];
        for ($s = 1; $s <= 12; $s++) {
            $name = $music->intervalNameFromSemitones($s);
            if ($name !== null) {
                $intervals[] = $name;
            }
        }

        $notes = $sel(self::EDIT_NOTE_POOL);
        $octaves = $sel(['2', '3', '4', '5', '6']);
        $clefs = [
            'treble' => __('teacher.assignments.clef_treble'),
            'bass' => __('teacher.assignments.clef_bass'),
            'alto' => __('teacher.assignments.clef_alto'),
        ];
        $directions = [
            'ascending' => __('teacher.assignments.direction_ascending'),
            'descending' => __('teacher.assignments.direction_descending'),
        ];

        return match ($slug) {
            'single-note-practice' => [
                ['key' => 'target', 'input' => 'select', 'options' => $notes],
                ['key' => 'octave', 'input' => 'select', 'options' => $octaves],
                ['key' => 'clef', 'input' => 'select', 'options' => $clefs],
                ['key' => 'reference_note', 'input' => 'text', 'placeholder' => 'A4'],
            ],
            'melodic-interval-practice' => [
                ['key' => 'note1', 'input' => 'select', 'options' => $notes],
                ['key' => 'octave', 'input' => 'select', 'options' => $octaves],
                ['key' => 'interval', 'input' => 'select', 'options' => $sel($intervals)],
                ['key' => 'direction', 'input' => 'select', 'options' => $directions],
                ['key' => 'clef', 'input' => 'select', 'options' => $clefs],
            ],
            'harmonic-interval-practice' => [
                ['key' => 'note1', 'input' => 'select', 'options' => $notes],
                ['key' => 'octave', 'input' => 'select', 'options' => $octaves],
                ['key' => 'interval', 'input' => 'select', 'options' => $sel($intervals)],
                ['key' => 'clef', 'input' => 'select', 'options' => $clefs],
            ],
            'interval-direction-practice' => [
                ['key' => 'note1', 'input' => 'select', 'options' => $notes],
                ['key' => 'octave', 'input' => 'select', 'options' => $octaves],
                ['key' => 'note2', 'input' => 'select', 'options' => $notes],
                ['key' => 'note2_octave', 'input' => 'select', 'options' => $octaves],
                ['key' => 'clef', 'input' => 'select', 'options' => $clefs],
            ],
            'interval-construction-practice' => [
                ['key' => 'note1', 'input' => 'select', 'options' => $notes],
                ['key' => 'octave', 'input' => 'select', 'options' => $octaves],
                ['key' => 'interval', 'input' => 'select', 'options' => $sel($intervals)],
                ['key' => 'direction', 'input' => 'select', 'options' => $directions],
            ],
            'interval-comparison-practice' => [
                ['key' => 'interval_a', 'input' => 'text', 'placeholder' => 'C,E'],
                ['key' => 'interval_b', 'input' => 'text', 'placeholder' => 'C,G'],
                ['key' => 'octave', 'input' => 'select', 'options' => $octaves],
                ['key' => 'clef', 'input' => 'select', 'options' => $clefs],
            ],
            'chord-practice' => [
                ['key' => 'chord_type', 'input' => 'select', 'options' => $sel(TeacherAssignmentConfigFactory::CHORD_TYPES)],
                ['key' => 'root_note', 'input' => 'select', 'options' => $notes],
                ['key' => 'inversion', 'input' => 'select', 'options' => ['0' => '0', '1' => '1', '2' => '2']],
                ['key' => 'voicing', 'input' => 'select', 'options' => $sel(['block', 'arpeggiated'])],
                ['key' => 'octave', 'input' => 'select', 'options' => $octaves],
                ['key' => 'clef', 'input' => 'select', 'options' => $clefs],
            ],
            'scale-practice' => [
                ['key' => 'scale_type', 'input' => 'select', 'options' => $sel(TeacherAssignmentConfigFactory::SCALE_TYPES)],
                ['key' => 'root_note', 'input' => 'select', 'options' => $notes],
                ['key' => 'direction', 'input' => 'select', 'options' => $directions],
                ['key' => 'octave', 'input' => 'select', 'options' => $octaves],
                ['key' => 'clef', 'input' => 'select', 'options' => $clefs],
                ['key' => 'tempo', 'input' => 'select', 'options' => $sel(TeacherAssignmentConfigFactory::SCALE_TEMPOS)],
            ],
            'rhythm-practice' => [
                ['key' => 'time_signature', 'input' => 'select', 'options' => $sel(TeacherAssignmentConfigFactory::TIME_SIGNATURES)],
                ['key' => 'bars', 'input' => 'select', 'options' => $sel(['1', '2', '3', '4'])],
                ['key' => 'tempo', 'input' => 'number', 'min' => 40, 'max' => 208],
            ],
            'melodic-dictation' => [
                ['key' => 'key_signature', 'input' => 'select', 'options' => $sel(self::DICTATION_KEYS)],
                ['key' => 'time_signature', 'input' => 'select', 'options' => $sel(TeacherAssignmentConfigFactory::TIME_SIGNATURES)],
                ['key' => 'tempo', 'input' => 'number', 'min' => 40, 'max' => 208],
                ['key' => 'clef', 'input' => 'select', 'options' => $clefs],
                ['key' => 'notes', 'input' => 'text', 'placeholder' => 'C4, D4, E4'],
            ],
            default => [],
        };
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
            'rhythm_values' => 'nullable|array',
            'rhythm_values.*' => 'string|max:30',
            'regenerate_melody' => 'nullable|boolean',
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

        // Type-aware validation + recomputation of derived fields (note2 from
        // interval, comparison target, direction, rhythm option regeneration…)
        // so an edit can never leave the audio, the correct answer and the
        // choices contradicting each other.
        try {
            $data = $this->normalizeEditedQuestion($assignment, $slug, $data, $validated);
        } catch (InvalidArgumentException $e) {
            return back()->withErrors(['questions' => $e->getMessage()]);
        }

        $choicesField = self::CHOICES_FIELD[$slug] ?? null;
        if ($choicesField !== null && array_key_exists('options', $validated) && $validated['options'] !== null) {
            $existing = $data[$choicesField] ?? null;
            $parsed = $this->parseOptionsInput($validated['options'], $existing);

            // The A/B/C/D editor submits distractors only. Types whose stored list
            // must also contain the correct answer get it re-merged (derived from
            // the possibly-edited fields above). Array-shaped lists render in stored
            // order, so reshuffle to keep the correct answer from landing first.
            if (in_array($slug, self::CHOICES_INCLUDE_CORRECT, true)) {
                $correct = trim((string) rescue(
                    fn () => app(LearningPathQuestionGenerator::class)->getAnswerFromSessionQuestion($data, $slug),
                    '',
                    false,
                ));
                $parsed = $this->mergeCorrectIntoChoices($parsed, $correct);
            }

            $data[$choicesField] = $parsed;
        }

        // Final integrity pass: the correct answer is present exactly once
        // where it must be, never among distractor-only lists, and the choice
        // count is topped back up to a full set when an edit collapsed it.
        $data = $this->ensureChoiceIntegrity($slug, $data);

        $question->update(['question_data' => $data]);

        return back()->with('status', 'question-updated');
    }

    /**
     * Validate edited content against the canonical vocabularies and
     * recompute every derived field for the practice type. Throws
     * InvalidArgumentException with a user-readable message when the edit
     * would produce an unplayable or unanswerable question.
     */
    private function normalizeEditedQuestion(TeacherAssignment $assignment, string $slug, array $data, array $validated): array
    {
        $music = app(MusicTheoryService::class);

        $noteNames = array_keys(MusicTheoryService::NOTE_SEMITONES);
        $assertNote = function (string $note) use ($noteNames) {
            if (! in_array($note, $noteNames, true)) {
                throw new InvalidArgumentException(__('teacher.assignments.edit_error_invalid_note', ['note' => $note]));
            }
        };
        $assertClef = function (array $data) {
            if (($data['clef'] ?? '') !== '' && ! in_array($data['clef'], TeacherAssignmentConfigFactory::CLEFS, true)) {
                throw new InvalidArgumentException(__('teacher.assignments.edit_error_invalid_value', ['field' => 'clef']));
            }
        };
        $assertInRange = function (string $note, int $octave, ?string $clef) use ($music) {
            if ($clef === null || $clef === '') {
                return;
            }
            [$min, $max] = $music->clefRangeMidi($clef);
            $midi = $music->midiNumber($note, $octave);
            if ($midi === null || $midi < $min || $midi > $max) {
                throw new InvalidArgumentException(__('teacher.assignments.edit_error_out_of_range', [
                    'note' => $note.$octave, 'clef' => $clef,
                ]));
            }
        };
        $intOr = fn ($v, int $default) => is_numeric($v) ? (int) $v : $default;

        switch ($slug) {
            case 'single-note-practice':
                $target = (string) ($data['target'] ?? '');
                $assertNote($target);
                $assertClef($data);
                $octave = $intOr($data['octave'] ?? null, 4);
                $data['octave'] = (string) $octave;
                $assertInRange($target, $octave, $data['clef'] ?? null);

                $ref = (string) ($data['reference_note'] ?? '');
                if ($ref === '') {
                    $naturals = array_values(array_filter(['C', 'D', 'E', 'F', 'G', 'A', 'B'], fn ($n) => $n !== strtoupper(substr($target, 0, 1))));
                    $ref = $naturals[array_rand($naturals)].$octave;
                } elseif (! preg_match('/^([A-G][#b]?)(\d)$/', $ref, $m) || ! in_array($m[1], $noteNames, true)) {
                    throw new InvalidArgumentException(__('teacher.assignments.edit_error_invalid_note', ['note' => $ref]));
                }
                $data['reference_note'] = $ref;
                break;

            case 'melodic-interval-practice':
            case 'harmonic-interval-practice':
            case 'interval-construction-practice':
                $interval = $music->normalizeIntervalName((string) ($data['interval'] ?? ''));
                if (! array_key_exists($interval, MusicTheoryService::INTERVAL_SEMITONES)) {
                    throw new InvalidArgumentException(__('teacher.assignments.edit_error_invalid_interval', ['interval' => (string) ($data['interval'] ?? '')]));
                }
                $data['interval'] = $interval;

                $note1 = (string) ($data['note1'] ?? '');
                $assertNote($note1);
                $assertClef($data);
                $octave = $intOr($data['octave'] ?? null, 4);
                $data['octave'] = (string) $octave;

                $direction = $slug === 'harmonic-interval-practice'
                    ? 'ascending'
                    : (($data['direction'] ?? 'ascending') ?: 'ascending');
                if (! in_array($direction, ['ascending', 'descending'], true)) {
                    throw new InvalidArgumentException(__('teacher.assignments.edit_error_invalid_value', ['field' => 'direction']));
                }
                if ($slug !== 'harmonic-interval-practice') {
                    $data['direction'] = $direction;
                }

                // The sounding second note is always derived — never stored
                // stale — so audio, notation and answer agree after any edit.
                $result = $direction === 'descending'
                    ? $music->preferredNoteBelowByInterval($note1, $octave, $interval)
                    : $music->preferredNoteAboveByInterval($note1, $octave, $interval);
                // B#/Cb are excluded from NOTE_SEMITONES (written octave differs
                // from the sounding one, breaking playback) — respell chromatically.
                if ($result !== null && ! isset(MusicTheoryService::NOTE_SEMITONES[$result['note']])) {
                    $result = $direction === 'descending'
                        ? $music->noteBelowByInterval($note1, $octave, $interval)
                        : $music->noteAboveByInterval($note1, $octave, $interval);
                }
                if ($result === null) {
                    throw new InvalidArgumentException(__('teacher.assignments.edit_error_unbuildable'));
                }
                $data['note2'] = $result['note'];
                $data['note2_octave'] = $result['octave'];

                // Construction answers ARE note2: any stored answer options were
                // built around the previous note2 and must be rebuilt with it
                // (single-accidental palette, one option per pitch class).
                if ($slug === 'interval-construction-practice' && array_key_exists('options', $data)) {
                    $optionPool = array_values(array_diff(
                        array_keys(MusicTheoryService::NOTE_SEMITONES),
                        ['E#', 'Fb']
                    ));
                    shuffle($optionPool);
                    $options = [$result['note']];
                    $usedPitchClasses = [$music->parseNoteChromatic($result['note'])];
                    foreach ($optionPool as $candidate) {
                        if (count($options) >= 4) {
                            break;
                        }
                        $pc = $music->parseNoteChromatic($candidate);
                        if ($pc === null || in_array($pc, $usedPitchClasses, true)) {
                            continue;
                        }
                        $usedPitchClasses[] = $pc;
                        $options[] = $candidate;
                    }
                    shuffle($options);
                    $data['options'] = $options;
                }

                $clef = ($data['clef'] ?? '') ?: null;
                $assertInRange($note1, $octave, $clef);
                $assertInRange($result['note'], (int) $result['octave'], $clef);
                break;

            case 'interval-direction-practice':
                $assertClef($data);
                foreach (['note1', 'note2'] as $k) {
                    $assertNote((string) ($data[$k] ?? ''));
                }
                $octave1 = $intOr($data['octave'] ?? null, 4);
                $octave2 = $intOr($data['note2_octave'] ?? null, $octave1);
                $data['octave'] = (string) $octave1;
                $data['note2_octave'] = (string) $octave2;

                $direction = $music->getDirection((string) $data['note1'], $octave1, (string) $data['note2'], $octave2);
                if (! in_array($direction, ['ascending', 'descending'], true)) {
                    throw new InvalidArgumentException(__('teacher.assignments.edit_error_unison'));
                }
                $data['direction'] = $direction;

                $clef = ($data['clef'] ?? '') ?: null;
                $assertInRange((string) $data['note1'], $octave1, $clef);
                $assertInRange((string) $data['note2'], $octave2, $clef);
                break;

            case 'interval-comparison-practice':
                $assertClef($data);
                foreach (['interval_a', 'interval_b'] as $k) {
                    $pair = (string) ($data[$k] ?? '');
                    $parts = array_map('trim', explode(',', $pair));
                    if (count($parts) !== 2) {
                        throw new InvalidArgumentException(__('teacher.assignments.edit_error_invalid_pair', ['pair' => $pair]));
                    }
                    foreach ($parts as $n) {
                        $assertNote($n);
                    }
                    $data[$k] = $parts[0].','.$parts[1];
                }

                // The answer is derived from the actual pairs. Equal-size pairs
                // are unanswerable and rejected outright.
                $target = $music->largerIntervalPair((string) $data['interval_a'], (string) $data['interval_b']);
                if ($target === null) {
                    throw new InvalidArgumentException(__('teacher.assignments.edit_error_equal_pairs'));
                }
                $data['target'] = $target;
                $data['octave'] = (string) $intOr($data['octave'] ?? null, 4);
                break;

            case 'chord-practice':
                if (! in_array($data['chord_type'] ?? '', TeacherAssignmentConfigFactory::CHORD_TYPES, true)) {
                    throw new InvalidArgumentException(__('teacher.assignments.edit_error_invalid_value', ['field' => 'chord type']));
                }
                $assertNote((string) ($data['root_note'] ?? ''));
                $assertClef($data);
                if (! in_array($data['voicing'] ?? 'block', ['block', 'arpeggiated'], true)) {
                    throw new InvalidArgumentException(__('teacher.assignments.edit_error_invalid_value', ['field' => 'voicing']));
                }
                $inversion = $intOr($data['inversion'] ?? null, 0);
                if ($inversion < 0 || $inversion > 2) {
                    throw new InvalidArgumentException(__('teacher.assignments.edit_error_invalid_value', ['field' => 'inversion']));
                }
                $data['inversion'] = $inversion;
                $data['octave'] = (string) $intOr($data['octave'] ?? null, 4);
                break;

            case 'scale-practice':
                if (! in_array($data['scale_type'] ?? '', TeacherAssignmentConfigFactory::SCALE_TYPES, true)) {
                    throw new InvalidArgumentException(__('teacher.assignments.edit_error_invalid_value', ['field' => 'scale type']));
                }
                $assertNote((string) ($data['root_note'] ?? ''));
                $assertClef($data);
                if (! in_array($data['direction'] ?? 'ascending', ['ascending', 'descending'], true)) {
                    throw new InvalidArgumentException(__('teacher.assignments.edit_error_invalid_value', ['field' => 'direction']));
                }
                if (($data['tempo'] ?? '') !== '' && ! in_array($data['tempo'], TeacherAssignmentConfigFactory::SCALE_TEMPOS, true)) {
                    throw new InvalidArgumentException(__('teacher.assignments.edit_error_invalid_value', ['field' => 'tempo']));
                }
                $data['octave'] = (string) $intOr($data['octave'] ?? null, 4);
                break;

            case 'rhythm-practice':
                $timeSig = (string) ($data['time_signature'] ?? '4/4');
                if (! in_array($timeSig, TeacherAssignmentConfigFactory::TIME_SIGNATURES, true)) {
                    throw new InvalidArgumentException(__('teacher.assignments.edit_error_invalid_value', ['field' => 'time signature']));
                }
                $bars = $intOr($data['bars'] ?? null, 1);
                if ($bars < 1 || $bars > 4) {
                    throw new InvalidArgumentException(__('teacher.assignments.edit_error_invalid_value', ['field' => 'bars']));
                }
                $data['bars'] = $bars;
                $tempo = $intOr($data['tempo'] ?? null, 80);
                if ($tempo < 40 || $tempo > 208) {
                    throw new InvalidArgumentException(__('teacher.assignments.edit_error_invalid_value', ['field' => 'tempo']));
                }
                $data['tempo'] = $tempo;

                $pattern = is_array($data['note_values'] ?? null) ? $data['note_values'] : [];
                if ($pattern === []) {
                    throw new InvalidArgumentException(__('teacher.assignments.edit_error_empty_pattern'));
                }
                $grouping = app(RhythmGroupingService::class);
                $total = 0;
                foreach ($pattern as $token) {
                    if (! in_array($token, self::RHYTHM_EDIT_TOKENS, true)) {
                        throw new InvalidArgumentException(__('teacher.assignments.edit_error_invalid_value', ['field' => $token]));
                    }
                    $total += $grouping->noteTwelfths($token);
                }
                if (str_ends_with((string) $pattern[0], '_rest')) {
                    throw new InvalidArgumentException(__('teacher.assignments.edit_error_rest_start'));
                }
                [$num, $den] = array_map('intval', explode('/', $timeSig));
                $required = $bars * $num * intdiv(48, $den);
                if ($total !== $required) {
                    throw new InvalidArgumentException(__('teacher.assignments.edit_error_pattern_length', [
                        'expected' => $bars.' × '.$timeSig,
                    ]));
                }

                // Distractors are always rebuilt for the edited pattern and
                // meter — a 3/4 question can never keep 4/4 answer options.
                $difficulty = match ($assignment->difficulty) {
                    'beginner' => 'easy',
                    'advanced' => 'hard',
                    default => 'medium',
                };
                $data['other_options'] = app(RhythmDistractorService::class)
                    ->generate($pattern, $timeSig, $difficulty);
                break;

            case 'melodic-dictation':
                if (! in_array($data['key_signature'] ?? 'C', self::DICTATION_KEYS, true)) {
                    throw new InvalidArgumentException(__('teacher.assignments.edit_error_invalid_value', ['field' => 'key']));
                }
                $assertClef($data);
                $tempo = $intOr($data['tempo'] ?? null, 60);
                if ($tempo < 40 || $tempo > 208) {
                    throw new InvalidArgumentException(__('teacher.assignments.edit_error_invalid_value', ['field' => 'tempo']));
                }
                $data['tempo'] = $tempo;
                if (($data['time_signature'] ?? '') !== '' && ! in_array($data['time_signature'], TeacherAssignmentConfigFactory::TIME_SIGNATURES, true)) {
                    throw new InvalidArgumentException(__('teacher.assignments.edit_error_invalid_value', ['field' => 'time signature']));
                }

                $regenerate = ! empty($validated['regenerate_melody']) && ! empty($validated['rhythm_values']);
                if ($regenerate) {
                    $values = array_values(array_intersect((array) $validated['rhythm_values'], self::DICTATION_RHYTHM_VALUES));
                    if ($values === []) {
                        throw new InvalidArgumentException(__('teacher.assignments.edit_error_no_rhythm_values'));
                    }
                    $data = $this->regenerateDictationContent($assignment, $data, $values);
                    break;
                }

                $notes = is_array($data['notes'] ?? null) ? $data['notes'] : [];
                if ($notes === []) {
                    throw new InvalidArgumentException(__('teacher.assignments.edit_error_empty_melody'));
                }
                foreach ($notes as $n) {
                    if (! preg_match('/^([A-G][#b]?)(\d)$/', (string) $n, $m) || ! in_array($m[1], $noteNames, true)) {
                        throw new InvalidArgumentException(__('teacher.assignments.edit_error_invalid_note', ['note' => (string) $n]));
                    }
                }

                $noteValues = is_array($data['note_values'] ?? null) ? $data['note_values'] : [];
                if ($noteValues !== []) {
                    // Rhythmic dictation: when the meter no longer fits the
                    // stored pattern (the teacher changed the time signature),
                    // rebuild rhythm + melody from the pattern's own vocabulary
                    // so the edit actually shows up in the question.
                    $grouping = app(RhythmGroupingService::class);
                    $total = 0;
                    foreach ($noteValues as $token) {
                        $total += $grouping->noteTwelfths((string) $token);
                    }
                    $timeSig = ($data['time_signature'] ?? '') ?: '4/4';
                    $bars = max(1, min(4, $intOr($data['bars'] ?? null, 1)));
                    [$num, $den] = array_map('intval', explode('/', $timeSig));
                    $required = $bars * $num * intdiv(48, $den);

                    if ($total !== $required) {
                        $values = array_values(array_intersect(
                            array_unique(array_map('strval', $noteValues)),
                            self::DICTATION_RHYTHM_VALUES,
                        )) ?: ['quarter', 'eighth'];
                        $data = $this->regenerateDictationContent($assignment, $data, $values);
                        break;
                    }

                    // A hand-edited melody must stay in sync with the rhythm —
                    // one pitch per rhythm value.
                    if (count($noteValues) !== count($notes)) {
                        throw new InvalidArgumentException(__('teacher.assignments.edit_error_melody_rhythm_mismatch', [
                            'notes' => count($notes), 'values' => count($noteValues),
                        ]));
                    }
                }
                break;
        }

        return $data;
    }

    /**
     * Rebuild a dictation question's rhythm from the given note values and
     * regenerate a tonal melody to match it, honouring the question's key,
     * clef, meter and the assignment's difficulty. The canonical pipeline:
     * DictationRhythmService for the beat patterns, TonalMelodyGenerator for
     * the pitches — the same engines every other dictation flow uses.
     */
    private function regenerateDictationContent(TeacherAssignment $assignment, array $data, array $values): array
    {
        $timeSig = ($data['time_signature'] ?? '') ?: '4/4';
        $data['time_signature'] = $timeSig;
        $bars = max(1, min(4, is_numeric($data['bars'] ?? null) ? (int) $data['bars'] : 1));

        $noteValues = app(DictationRhythmService::class)
            ->generateBeatPatternRhythm($bars, $timeSig, $values);

        $melodyGenerator = app(TonalMelodyGenerator::class);
        $keySig = (string) ($data['key_signature'] ?? 'C');
        $clef = ($data['clef'] ?? '') ?: 'treble';
        $level = in_array($assignment->difficulty, ['beginner', 'intermediate', 'advanced'], true)
            ? $assignment->difficulty
            : 'intermediate';
        $context = $melodyGenerator->contextForKey($keySig, 'major', $clef);
        $melody = $melodyGenerator->generateMelody(count($noteValues), $context, $level);
        $melody = $melodyGenerator->applyAccidentals($melody, $keySig, 'major', $level);

        $data['notes'] = $melody;
        $data['note_values'] = $noteValues;
        $data['include_rhythm'] = true;

        return $data;
    }

    /**
     * Keep every choice list coherent after an edit: the correct answer
     * appears exactly once where the list contains it, never appears among
     * distractor-only lists, duplicates collapse, and the list is topped
     * back up from the canonical vocabulary when it fell short — this is
     * what used to shrink interval questions to 3 options.
     */
    private function ensureChoiceIntegrity(string $slug, array $data): array
    {
        $music = app(MusicTheoryService::class);
        $correct = trim((string) rescue(
            fn () => app(LearningPathQuestionGenerator::class)->getAnswerFromSessionQuestion($data, $slug),
            '',
            false,
        ));

        if ($correct === '') {
            return $data;
        }

        $sameAnswer = fn ($a, $b) => mb_strtolower(trim((string) $a)) === mb_strtolower(trim((string) $b))
            || ($music->normalizeIntervalName((string) $a) !== '' && $music->normalizeIntervalName((string) $a) === $music->normalizeIntervalName((string) $b));

        $canonicalIntervals = [];
        for ($s = 1; $s <= 12; $s++) {
            $name = $music->intervalNameFromSemitones($s);
            if ($name !== null) {
                $canonicalIntervals[] = $name;
            }
        }

        $topUp = function (array $list, array $pool, int $target) use ($correct, $sameAnswer) {
            $shuffled = $pool;
            shuffle($shuffled);
            foreach ($shuffled as $candidate) {
                if (count($list) >= $target) {
                    break;
                }
                if ($sameAnswer($candidate, $correct)) {
                    continue;
                }
                if (collect($list)->contains(fn ($v) => $sameAnswer($v, $candidate))) {
                    continue;
                }
                $list[] = $candidate;
            }

            return $list;
        };

        switch ($slug) {
            case 'melodic-interval-practice':
            case 'harmonic-interval-practice':
                $options = is_array($data['options'] ?? null) ? $data['options'] : [];
                $distractors = [];
                foreach ($options as $opt) {
                    if ($sameAnswer($opt, $correct)) {
                        continue;
                    }
                    if (collect($distractors)->contains(fn ($v) => $sameAnswer($v, $opt))) {
                        continue;
                    }
                    $distractors[] = $opt;
                }
                $distractors = $topUp($distractors, $canonicalIntervals, 3);
                $merged = array_merge([$correct], array_slice($distractors, 0, 3));
                shuffle($merged);
                $data['options'] = $merged;
                break;

            case 'single-note-practice':
                $options = array_values(array_filter(array_map('trim', explode(',', (string) ($data['other_options'] ?? ''))), fn ($v) => $v !== ''));
                $distractors = array_values(array_filter($options, fn ($v) => ! $sameAnswer($v, $correct)));
                $distractors = $topUp($distractors, self::EDIT_NOTE_POOL, 3);
                $data['other_options'] = implode(',', array_merge([$correct], $distractors));
                break;

            case 'chord-practice':
            case 'scale-practice':
                $pool = $slug === 'chord-practice'
                    ? TeacherAssignmentConfigFactory::CHORD_TYPES
                    : TeacherAssignmentConfigFactory::SCALE_TYPES;
                $options = is_array($data['other_options'] ?? null) ? $data['other_options'] : [];
                $distractors = [];
                foreach ($options as $opt) {
                    if ($sameAnswer($opt, $correct)) {
                        continue;
                    }
                    if (collect($distractors)->contains(fn ($v) => $sameAnswer($v, $opt))) {
                        continue;
                    }
                    $distractors[] = $opt;
                }
                $distractors = $topUp($distractors, $pool, 3);
                $data['other_options'] = array_slice($distractors, 0, 3);
                break;
        }

        return $data;
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

        // Choices arrive one-per-line from the A/B/C/D editor (legacy callers may
        // still send a comma list), so split on either.
        $parts = array_values(array_filter(
            array_map('trim', preg_split('/\r\n|\r|\n|,/', $input) ?: []),
            fn ($v) => $v !== '',
        ));

        // single-note stores other_options as a comma-separated string.
        return is_array($existing) ? $parts : implode(',', $parts);
    }

    /**
     * Re-insert the correct answer into a distractor-only choice list for the
     * types that store it inline. String lists (single-note piano) keep their
     * order — the correct answer is prepended; array lists render in stored order
     * so they are reshuffled to avoid the correct answer always landing first.
     */
    private function mergeCorrectIntoChoices(array|string $distractors, string $correct): array|string
    {
        if ($correct === '') {
            return $distractors;
        }

        $matchesCorrect = fn ($v) => is_string($v) && mb_strtolower(trim($v)) === mb_strtolower($correct);

        if (is_string($distractors)) {
            $parts = array_values(array_filter(
                array_map('trim', explode(',', $distractors)),
                fn ($v) => $v !== '' && ! $matchesCorrect($v),
            ));

            return implode(',', array_merge([$correct], $parts));
        }

        $parts = array_values(array_filter($distractors, fn ($v) => ! $matchesCorrect($v)));
        $merged = array_merge([$correct], $parts);
        shuffle($merged);

        return $merged;
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
            'exercise_back_url' => route(crm_prefix().'.assignments.show', $assignment),
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

        // Duplicating also creates a new active assignment — same cap as store().
        if (! $this->quota->canCreateAssignment($request->user())) {
            return back()->withErrors([
                'limit' => __('teacher.limits.assignments_reached', [
                    'limit' => $this->quota->limit($request->user(), 'max_active_assignments'),
                ]),
            ]);
        }

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

        return redirect()->route(crm_prefix().'.assignments.show', $copy)->with('status', 'assignment-duplicated');
    }

    public function archive(Request $request, TeacherAssignment $assignment)
    {
        $this->authorizeAssignment($request, $assignment);

        $assignment->update(['status' => TeacherAssignment::STATUS_ARCHIVED]);

        return redirect()->route(crm_prefix().'.assignments.index')->with('status', 'assignment-archived');
    }

    public function destroy(Request $request, TeacherAssignment $assignment)
    {
        $this->authorizeAssignment($request, $assignment);
        abort_unless($assignment->isDraft(), 403);

        $assignment->delete();

        return redirect()->route(crm_prefix().'.assignments.index')->with('status', 'assignment-deleted');
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

            $choicesField = self::CHOICES_FIELD[$slug] ?? null;
            $options = $choicesField ? ($data[$choicesField] ?? null) : null;
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
                // Dictation modal: pre-tick the rhythm values the question uses.
                'rhythm_values' => $slug === 'melodic-dictation'
                    ? (array_values(array_intersect(
                        array_unique(array_map('strval', (array) ($data['note_values'] ?? []))),
                        self::DICTATION_RHYTHM_VALUES,
                    )) ?: ['quarter', 'eighth'])
                    : [],
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

            // Sensible defaults for snapshots created before these fields
            // became editable, so the selects open on the effective value.
            if ($values[$key] === '') {
                $values[$key] = match ($key) {
                    'clef' => 'treble',
                    'direction' => 'ascending',
                    'tempo' => $slug === 'scale-practice' ? 'normal' : '',
                    default => '',
                };
            }
        }

        return $values;
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
