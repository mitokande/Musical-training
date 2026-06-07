<?php

namespace App\Livewire;

use App\Livewire\Concerns\HandlesPracticeData;
use App\Models\ChordPractice;
use App\Models\LearningPathExercise;
use App\Models\Practice;
use App\Models\UserPractice;
use App\Services\LearningPathQuestionGenerator;
use Livewire\Component;

class PracticeChord extends Component
{
    use HandlesPracticeData;

    private const ALL_CHORD_TYPES = [
        'Major', 'Minor', 'Diminished', 'Augmented',
        'Dominant 7th', 'Major 7th', 'Minor 7th', 'Half Diminished',
        'Diminished 7th', 'Augmented 7th',
    ];

    public $currentPracticeIndex = 0;
    public $settings = [];
    public $replayLimit = null;
    public $feedbackMode = 'immediate';
    public $timeLimitSeconds = 0;

    public function mount($practices)
    {
        $settings = session('exercise_settings', []);
        session()->forget('exercise_settings');

        if (!empty($settings)) {
            $this->settings = $settings;
            $this->replayLimit = $settings['replay_limit'] ?? null;
            $this->feedbackMode = $settings['feedback_mode'] ?? 'immediate';
            $this->timeLimitSeconds = (int) ($settings['time_limit_seconds'] ?? 0);

            $count = (int) ($settings['question_count'] ?? 10);
            $chordTypes = !empty($settings['chord_types']) ? $settings['chord_types'] : self::ALL_CHORD_TYPES;

            $generator = app(LearningPathQuestionGenerator::class);
            $octaveRange = !empty($settings['octave_range']) ? $settings['octave_range'] : [4];
            sort($octaveRange);
            $midOctave = (string) $octaveRange[(int)(count($octaveRange) / 2)];
            $exercise = new LearningPathExercise(['config_json' => [
                'practice_type'       => 'chord-practice',
                'allowed_chord_types' => $chordTypes,
                'allowed_root_notes'  => ['C', 'D', 'E', 'F', 'G', 'A', 'B'],
                'voicing'             => $settings['voicing'] ?? 'block',
                'include_inversions'  => $settings['include_inversions'] ?? false,
                'distractor_pool'     => self::ALL_CHORD_TYPES,
                'octave'              => $midOctave,
            ]]);
            $generated = $generator->generate($exercise, $count)->values()
                ->map(function ($q, $i) { $q->id = $i + 1; return $q; });

            session(['exercise_practice_session' => [
                'practice_type'  => 'chord-practice',
                'question_count' => $generated->count(),
                'questions'      => $generator->serializeForSession($generated),
            ]]);

            $this->practiceDataArray = $this->serializePractices($generated->all());
        } else {
            $this->practiceDataArray = $this->serializePractices($practices);
        }
    }

    public function render()
    {
        $currentPractice = $this->buildModelFromData(ChordPractice::class, $this->getCurrentPracticeData());
        return view('livewire.practice-chord', [
            'practices'            => $this->practiceDataArray,
            'currentPractice'      => $currentPractice,
            'currentPracticeIndex' => $this->currentPracticeIndex,
        ]);
    }

    public function getNextPractice()
    {
        $this->currentPracticeIndex++;
        $this->dispatch('practice-updated');
    }

    public function answerPractice($answer)
    {
        $practiceId = Practice::where('slug', 'chord-practice')->value('id');
        $userPractice = UserPractice::firstOrCreate(
            ['user_id' => auth()->id(), 'practice_id' => $practiceId],
            ['total_questions' => 0, 'correct_answers' => 0, 'incorrect_answers' => 0, 'score' => 0]
        );

        $data = $this->getCurrentPracticeData();
        $target = $data['chord_type'] ?? '';
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
