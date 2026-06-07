<?php

namespace App\Livewire;

use App\Livewire\Concerns\HandlesPracticeData;
use App\Models\LearningPathExercise;
use App\Models\SingleNotePractice;
use App\Models\UserPractice;
use App\Services\LearningPathQuestionGenerator;
use Livewire\Component;

class PracticeSingleNote extends Component
{
    use HandlesPracticeData;

    private const ALL_NOTES = ['C', 'D', 'E', 'F', 'G', 'A', 'B'];

    public $currentPracticeIndex = 0;
    public $settings = [];
    public $replayLimit = null;
    public $feedbackMode = 'immediate';
    public $timeLimitSeconds = 0;
    public $groupSize = 1;
    public $answerMode = 'keyboard';
    public $allowedNotes = ['C', 'D', 'E', 'F', 'G', 'A', 'B'];
    public $clef = 'treble';

    public function mount($practices)
    {
        $settings = session('exercise_settings', []);
        session()->forget('exercise_settings');

        if (!empty($settings)) {
            $this->settings = $settings;
            $this->replayLimit = $settings['replay_limit'] ?? null;
            $this->feedbackMode = $settings['feedback_mode'] ?? 'immediate';
            $this->timeLimitSeconds = (int) ($settings['time_limit_seconds'] ?? 0);
            $this->groupSize = max(1, (int) ($settings['single_note_group_size'] ?? 1));
            $this->answerMode = $settings['single_note_answer_mode'] ?? 'keyboard';
            $this->clef = $settings['clef'] ?? 'treble';

            // Allowed notes: naturals + sharps selected in setup
            $allAllowed = array_merge(
                self::ALL_NOTES,
                ['C#', 'D#', 'F#', 'G#', 'A#']
            );
            $this->allowedNotes = !empty($settings['single_note_allowed_notes'])
                ? array_values(array_filter($settings['single_note_allowed_notes'], fn($n) => in_array($n, $allAllowed)))
                : self::ALL_NOTES;
            if (empty($this->allowedNotes)) {
                $this->allowedNotes = self::ALL_NOTES;
            }

            $questionCount = (int) ($settings['question_count'] ?? 10);
            $totalNotes = $questionCount * $this->groupSize;

            $generator = app(LearningPathQuestionGenerator::class);
            // Octave ranges per user reference:
            //   Sol  (Treble): G3–G5  → octaves 4, 5   (C4–B5 covers the treble staff)
            //   Fa   (Bass)  : C2–C4  → octaves 2, 3   (C2–B3 = full bass range)
            //   Do   (Alto)  : C3–C5  → octaves 3, 4   (C3–B4 = full alto range)
            // NOTE: StaveNote MUST receive clef:'xx' to be positioned correctly —
            //       stave.addClef() only draws the visual symbol, not note Y positions.
            $octaveRange = match($this->clef) {
                'bass'  => ['2', '3'],
                'alto'  => ['3', '4'],
                default => ['4', '5'],   // treble
            };
            $exercise = new LearningPathExercise(['config_json' => [
                'practice_type'    => 'single-note-practice',
                'allowed_notes'    => $this->allowedNotes,
                'octave_range'     => $octaveRange,
                'distractor_count' => 3,
            ]]);

            $generated = $generator->generate($exercise, $totalNotes)->values()
                ->map(function ($q, $i) {
                    $q->id = $i + 1;
                    // other_options contains all allowed notes (for keyboard answer)
                    $q->other_options = implode(',', $this->allowedNotes);
                    return $q;
                });

            session(['exercise_practice_session' => [
                'practice_type'  => 'single-note-practice',
                'question_count' => $generated->count(),
                'group_size'     => $this->groupSize,
                'answer_mode'    => $this->answerMode,
                'questions'      => $generator->serializeForSession($generated),
            ]]);

            $this->practiceDataArray = $this->serializePractices($generated->all());
        } else {
            $this->practiceDataArray = $this->serializePractices($practices);
        }
    }

    public function render()
    {
        // Build current group
        $currentGroupNotes = [];
        for ($i = 0; $i < $this->groupSize; $i++) {
            $idx = $this->currentPracticeIndex + $i;
            if (isset($this->practiceDataArray[$idx])) {
                $currentGroupNotes[] = $this->practiceDataArray[$idx];
            }
        }

        $totalQuestions = $this->groupSize > 0
            ? (int) floor(count($this->practiceDataArray) / $this->groupSize)
            : count($this->practiceDataArray);

        $currentQuestionNumber = $this->groupSize > 0
            ? (int) floor($this->currentPracticeIndex / $this->groupSize) + 1
            : $this->currentPracticeIndex + 1;

        $isLastQuestion = ($this->currentPracticeIndex + $this->groupSize) >= count($this->practiceDataArray);

        return view('livewire.practice-single-note', [
            'practices'             => $this->practiceDataArray,
            'currentGroupNotes'     => $currentGroupNotes,
            'groupSize'             => $this->groupSize,
            'answerMode'            => $this->answerMode,
            'allowedNotes'          => $this->allowedNotes,
            'clef'                  => $this->clef,
            'currentPracticeIndex'  => $this->currentPracticeIndex,
            'totalQuestions'        => $totalQuestions,
            'currentQuestionNumber' => $currentQuestionNumber,
            'isLastQuestion'        => $isLastQuestion,
        ]);
    }

    public function getNextPractice()
    {
        $this->currentPracticeIndex += $this->groupSize;
        $this->dispatch('practice-updated');
    }

    public function answerPractice($answer)
    {
        $practiceId = \App\Models\Practice::where('slug', 'single-note-practice')->value('id');
        $userPractice = UserPractice::firstOrCreate(
            ['user_id' => auth()->user()->id, 'practice_id' => $practiceId],
            ['total_questions' => 0, 'correct_answers' => 0, 'incorrect_answers' => 0, 'score' => 0]
        );

        $data = $this->getCurrentPracticeData();
        $target = $data['target'] ?? '';
        $isCorrect = strtolower(trim($answer)) === strtolower(trim($target));

        $userPractice->total_questions++;
        if ($isCorrect) {
            $userPractice->correct_answers++;
        } else {
            $userPractice->incorrect_answers++;
        }
        $userPractice->score = $userPractice->total_questions > 0
            ? ($userPractice->correct_answers / $userPractice->total_questions) * 100
            : 0;
        $userPractice->save();

        return $isCorrect;
    }
}
