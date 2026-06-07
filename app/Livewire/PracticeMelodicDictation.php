<?php

namespace App\Livewire;

use App\Livewire\Concerns\HandlesPracticeData;
use App\Models\LearningPathExercise;
use App\Models\MelodicDictationPractice;
use App\Models\Practice;
use App\Models\UserPractice;
use App\Services\LearningPathQuestionGenerator;
use Livewire\Component;

class PracticeMelodicDictation extends Component
{
    use HandlesPracticeData;

    public $currentPracticeIndex = 0;
    public $settings = [];
    public $replayLimit = null;
    public $feedbackMode = 'immediate';
    public $timeLimitSeconds = 0;
    public $dictationListenCount = 'unlimited';
    public $dictationAnswerMode = 'keyboard';

    public function mount($practices)
    {
        $settings = session('exercise_settings', []);
        session()->forget('exercise_settings');

        if (!empty($settings)) {
            $this->settings = $settings;
            $this->replayLimit = $settings['replay_limit'] ?? null;
            $this->feedbackMode = $settings['feedback_mode'] ?? 'immediate';
            $this->timeLimitSeconds = (int) ($settings['time_limit_seconds'] ?? 0);
            $this->dictationListenCount = $settings['dictation_listen_count'] ?? 'unlimited';
            $this->dictationAnswerMode = $settings['dictation_answer_mode'] ?? 'keyboard';

            $count = (int) ($settings['question_count'] ?? 10);
            $bars = (int) ($settings['dictation_bars'] ?? 2);
            $clef = $settings['clef'] ?? 'treble';

            $generator = app(LearningPathQuestionGenerator::class);
            $exercise = new LearningPathExercise(['config_json' => [
                'practice_type'  => 'melodic-dictation',
                'note_pool'      => ['C4', 'D4', 'E4', 'F4', 'G4', 'A4', 'B4'],
                'melody_length'  => $bars * 4,
                'clef'           => $clef,
                'key_signatures' => ['C'],
                'tempo_range'    => [52, 60],
                'include_rhythm' => $settings['dictation_include_rhythm'] ?? false,
                'bars'           => $bars,
            ]]);
            $generated = $generator->generate($exercise, $count)->values()
                ->map(function ($q, $i) { $q->id = $i + 1; return $q; });

            session(['exercise_practice_session' => [
                'practice_type'  => 'melodic-dictation',
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
        $currentPractice = $this->buildModelFromData(MelodicDictationPractice::class, $this->getCurrentPracticeData());
        return view('livewire.practice-melodic-dictation', [
            'practices'            => $this->practiceDataArray,
            'currentPractice'      => $currentPractice,
            'currentPracticeIndex' => $this->currentPracticeIndex,
            'dictationListenCount' => $this->dictationListenCount,
            'dictationAnswerMode'  => $this->dictationAnswerMode,
        ]);
    }

    public function getNextPractice()
    {
        $this->currentPracticeIndex++;
        $this->dispatch('practice-updated');
    }

    public function answerPractice($answer)
    {
        $practiceId = Practice::where('slug', 'melodic-dictation')->value('id');
        $userPractice = UserPractice::firstOrCreate(
            ['user_id' => auth()->id(), 'practice_id' => $practiceId],
            ['total_questions' => 0, 'correct_answers' => 0, 'incorrect_answers' => 0, 'score' => 0]
        );

        $data = $this->getCurrentPracticeData();
        $notes = $data['notes'] ?? [];
        if (is_string($notes)) {
            $notes = json_decode($notes, true) ?? [];
        }
        $correctNotes = implode(',', $notes);
        $userAnswer = strtoupper(preg_replace('/\s+/', '', trim($answer)));
        $correctAnswer = strtoupper(preg_replace('/\s+/', '', $correctNotes));
        $isCorrect = $userAnswer === $correctAnswer;

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
