<?php

namespace App\Livewire;

use App\Livewire\Concerns\HandlesPracticeData;
use App\Models\LearningPathExercise;
use App\Models\Practice;
use App\Models\ScalePractice;
use App\Models\UserPractice;
use App\Services\LearningPathQuestionGenerator;
use Livewire\Component;

class PracticeScale extends Component
{
    use HandlesPracticeData;

    private const ALL_SCALE_TYPES = [
        'Major', 'Natural Minor', 'Harmonic Minor', 'Melodic Minor',
        'Ionian', 'Dorian', 'Phrygian', 'Lydian', 'Mixolydian', 'Aeolian', 'Locrian',
        'Major Pentatonic', 'Minor Pentatonic', 'Blues Scale', 'Chromatic Scale', 'Whole Tone Scale',
    ];

    public $currentPracticeIndex = 0;

    public $settings = [];

    public $scaleTempo = 'normal';

    public $replayLimit = null;

    public $feedbackMode = 'immediate';

    public $timeLimitSeconds = 0;

    public function mount($practices)
    {
        $settings = session('exercise_settings', []);
        session()->forget('exercise_settings');

        if (! empty($settings)) {
            $this->settings = $settings;
            $this->scaleTempo = $settings['scale_tempo'] ?? 'normal';
            $this->replayLimit = $settings['replay_limit'] ?? null;
            $this->feedbackMode = $settings['feedback_mode'] ?? 'immediate';
            $this->timeLimitSeconds = (int) ($settings['time_limit_seconds'] ?? 0);

            $count = (int) ($settings['question_count'] ?? 10);
            $scaleTypes = ! empty($settings['scale_types']) ? $settings['scale_types'] : self::ALL_SCALE_TYPES;
            $scaleDir = $settings['scale_direction'] ?? 'ascending';

            $generator = app(LearningPathQuestionGenerator::class);
            $directions = $scaleDir === 'both' ? ['ascending', 'descending'] : [$scaleDir];
            $perDir = (int) ceil($count / count($directions));

            $allGenerated = collect();
            foreach ($directions as $dir) {
                // No explicit octave: the generator keeps the whole scale inside
                // the selected clef's playable range (CLEF_RANGES).
                $exercise = new LearningPathExercise(['config_json' => [
                    'practice_type' => 'scale-practice',
                    'allowed_scale_types' => $scaleTypes,
                    'allowed_root_notes' => ['C', 'D', 'E', 'F', 'G', 'A', 'B'],
                    'direction' => $dir,
                    'distractor_pool' => $scaleTypes,
                    'clef' => $settings['clef'] ?? 'treble',
                ]]);
                $allGenerated = $allGenerated->merge($generator->generate($exercise, $perDir));
            }

            $generated = $allGenerated->shuffle()->values()->take($count)
                ->map(function ($q, $i) {
                    $q->id = $i + 1;

                    return $q;
                });

            session(['exercise_practice_session' => [
                'practice_type' => 'scale-practice',
                'question_count' => $generated->count(),
                'questions' => $generator->serializeForSession($generated),
            ]]);

            $this->practiceDataArray = $this->serializePractices($generated->all());
        } else {
            $this->practiceDataArray = $this->serializePractices($practices);
        }
    }

    public function render()
    {
        $data = $this->getCurrentPracticeData();
        $currentPractice = $this->buildModelFromData(ScalePractice::class, $data);
        $scaleTypes = $this->settings['scale_types'] ?? [];

        // Teacher-assignment snapshots may carry a per-question playback
        // tempo (slow|normal|fast); the component-level tempo only covers
        // the Exercise Setup flow.
        $questionTempo = $data['tempo'] ?? null;
        $tempo = in_array($questionTempo, ['slow', 'normal', 'fast'], true)
            ? $questionTempo
            : $this->scaleTempo;

        return view('livewire.practice-scale', [
            'practices' => $this->practiceDataArray,
            'currentPractice' => $currentPractice,
            'currentPracticeIndex' => $this->currentPracticeIndex,
            'scaleTypes' => $scaleTypes,
            'scaleTempo' => $tempo,
        ]);
    }

    public function getNextPractice()
    {
        $this->currentPracticeIndex++;
        $this->dispatch('practice-updated');
    }

    public function answerPractice($answer)
    {
        $practiceId = Practice::where('slug', 'scale-practice')->value('id');
        $userPractice = UserPractice::firstOrCreate(
            ['user_id' => auth()->id(), 'practice_id' => $practiceId],
            ['total_questions' => 0, 'correct_answers' => 0, 'incorrect_answers' => 0, 'score' => 0]
        );

        $data = $this->getCurrentPracticeData();
        $target = $data['scale_type'] ?? '';
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
