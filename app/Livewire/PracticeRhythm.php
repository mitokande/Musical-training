<?php

namespace App\Livewire;

use App\Livewire\Concerns\HandlesPracticeData;
use App\Models\LearningPathExercise;
use App\Models\Practice;
use App\Models\RhythmPractice;
use App\Models\UserPractice;
use App\Services\LearningPathQuestionGenerator;
use Livewire\Component;

class PracticeRhythm extends Component
{
    use HandlesPracticeData;

    public $currentPracticeIndex = 0;
    public $settings = [];
    public $replayLimit = null;
    public $feedbackMode = 'immediate';
    public $timeLimitSeconds = 0;
    public $metronome = true;
    public $rhythmMode = 'dictation';

    public function mount($practices)
    {
        $settings = session('exercise_settings', []);
        session()->forget('exercise_settings');

        if (!empty($settings)) {
            $this->settings = $settings;
            $this->replayLimit = $settings['replay_limit'] ?? null;
            $this->feedbackMode = $settings['feedback_mode'] ?? 'immediate';
            $this->timeLimitSeconds = (int) ($settings['time_limit_seconds'] ?? 0);
            $this->metronome = isset($settings['metronome']) ? (bool) $settings['metronome'] : true;
            $this->rhythmMode = $settings['rhythm_mode'] ?? 'dictation';

            $count = (int) ($settings['question_count'] ?? 10);
            $timeSig = $settings['time_signature'] ?? '4/4';
            $noteVals = !empty($settings['note_values']) ? $settings['note_values'] : ['quarter', 'half'];
            $tempo = (int) ($settings['tempo'] ?? 80);

            $generator = app(LearningPathQuestionGenerator::class);
            $exercise = new LearningPathExercise(['config_json' => [
                'practice_type'       => 'rhythm-practice',
                'time_signatures'     => [$timeSig],
                'allowed_note_values' => $noteVals,
                'tempo_range'         => [max(40, $tempo - 4), min(160, $tempo + 4)],
                'bars'                => 1,
            ]]);
            $generated = $generator->generate($exercise, $count)->values()
                ->map(function ($q, $i) { $q->id = $i + 1; return $q; });

            session(['exercise_practice_session' => [
                'practice_type'  => 'rhythm-practice',
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
        $currentPractice = $this->buildModelFromData(RhythmPractice::class, $this->getCurrentPracticeData());
        return view('livewire.practice-rhythm', [
            'practices'            => $this->practiceDataArray,
            'currentPractice'      => $currentPractice,
            'currentPracticeIndex' => $this->currentPracticeIndex,
            'metronome'            => $this->metronome,
            'rhythmMode'           => $this->rhythmMode,
        ]);
    }

    public function getNextPractice()
    {
        $this->currentPracticeIndex++;
        $this->dispatch('practice-updated');
    }

    public function answerPractice($answer)
    {
        $practiceId = Practice::where('slug', 'rhythm-practice')->value('id');
        $userPractice = UserPractice::firstOrCreate(
            ['user_id' => auth()->id(), 'practice_id' => $practiceId],
            ['total_questions' => 0, 'correct_answers' => 0, 'incorrect_answers' => 0, 'score' => 0]
        );

        $data = $this->getCurrentPracticeData();
        $noteValues = $data['note_values'] ?? [];
        if (is_string($noteValues)) {
            $noteValues = json_decode($noteValues, true) ?? [];
        }
        $correctSequence = implode(',', $noteValues);
        $isCorrect = trim($answer) === trim($correctSequence);

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
