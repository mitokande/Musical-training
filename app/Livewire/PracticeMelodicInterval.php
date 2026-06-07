<?php

namespace App\Livewire;

use App\Livewire\Concerns\HandlesPracticeData;
use App\Models\LearningPathExercise;
use App\Models\MelodicIntervalPractice;
use App\Models\UserPractice;
use App\Services\LearningPathQuestionGenerator;
use App\Services\MusicTheoryService;
use Livewire\Component;

class PracticeMelodicInterval extends Component
{
    use HandlesPracticeData;

    private const INTERVAL_POOL_MAP = [
        'm2'  => 'Minor 2nd',
        'M2'  => 'Major 2nd',
        'm3'  => 'Minor 3rd',
        'M3'  => 'Major 3rd',
        'P4'  => 'Perfect 4th',
        'P5'  => 'Perfect 5th',
        'm6'  => 'Minor 6th',
        'M6'  => 'Major 6th',
        'm7'  => 'Minor 7th',
        'M7'  => 'Major 7th',
        '8ve' => 'Perfect Octave',
    ];

    public $currentPracticeIndex = 0;
    public $settings = [];
    public $clef = 'treble';
    public $replayLimit = null;
    public $feedbackMode = 'immediate';
    public $timeLimitSeconds = 0;

    public function mount($practices)
    {
        $settings = session('exercise_settings', []);
        session()->forget('exercise_settings');

        $resolvedPool = [];

        if (!empty($settings)) {
            $this->settings = $settings;
            $this->clef = $settings['clef'] ?? 'treble';
            $this->replayLimit = $settings['replay_limit'] ?? null;
            $this->feedbackMode = $settings['feedback_mode'] ?? 'immediate';
            $this->timeLimitSeconds = (int) ($settings['time_limit_seconds'] ?? 0);

            if (!empty($settings['interval_pool'])) {
                $resolvedPool = array_values(array_filter(
                    array_map(fn($a) => self::INTERVAL_POOL_MAP[$a] ?? null, $settings['interval_pool'])
                ));
            }
        }

        $count = !empty($settings) ? (int) ($settings['question_count'] ?? 10) : 10;

        if (!empty($resolvedPool)) {
            // Generate questions programmatically — the DB table is sparsely populated
            // and cannot be relied on to cover custom interval pools.
            $generator = app(LearningPathQuestionGenerator::class);
            $octaveRange = !empty($settings['octave_range'])
                ? array_map('strval', $settings['octave_range'])
                : ['3', '4', '5'];
            $exercise = new LearningPathExercise(['config_json' => [
                'practice_type'     => 'melodic-interval-practice',
                'allowed_intervals' => $resolvedPool,
                'allowed_notes'     => ['C', 'D', 'E', 'F', 'G', 'A', 'B'],
                'octave_range'      => $octaveRange,
            ]]);
            $generated = $generator->generate($exercise, $count)->values()
                ->map(function ($q, $i) { $q->id = $i + 1; return $q; });

            // Store in session so PracticeController::checkAnswer can verify answers
            session(['exercise_practice_session' => [
                'practice_type'  => 'melodic-interval-practice',
                'question_count' => $generated->count(),
                'questions'      => $generator->serializeForSession($generated),
            ]]);

            $music = app(MusicTheoryService::class);
            $distractorCount = count($resolvedPool) <= 2 ? 1 : 3;
            $allIntervals    = array_values(self::INTERVAL_POOL_MAP);
            $distractorPool  = $distractorCount > 1 && count($resolvedPool) < 4
                ? $allIntervals
                : $resolvedPool;
            $this->practiceDataArray = $generated->map(function ($q) use ($resolvedPool, $music, $distractorCount, $distractorPool) {
                $data    = $this->serializeOnePractice($q);
                $correct = $data['interval'];
                $distractors = $music->buildOptions($correct, $distractorPool, $distractorCount);
                $options = array_merge([$correct], $distractors);
                shuffle($options);
                $data['options'] = $options;
                return $data;
            })->values()->toArray();
        } else {
            if (!empty($settings)) {
                if (!empty($practices) && count($practices) < $count) {
                    $base = collect($practices);
                    $repeated = collect();
                    while ($repeated->count() < $count) {
                        $repeated = $repeated->merge($base->shuffle());
                    }
                    $practices = $repeated->take($count)->values()->all();
                } else {
                    $practices = collect($practices)->take($count)->shuffle()->values()->all();
                }
            }
            $this->practiceDataArray = $this->serializePractices($practices);
        }
    }

    public function render()
    {
        $data = $this->getCurrentPracticeData();
        $currentPractice = $this->buildModelFromData(MelodicIntervalPractice::class, $data);
        return view('livewire.practice-melodic-interval', [
            'practices'            => $this->practiceDataArray,
            'currentPractice'      => $currentPractice,
            'currentPracticeIndex' => $this->currentPracticeIndex,
            'intervalOptions'      => $data['options'] ?? null,
            'clef'                 => $this->clef,
        ]);
    }

    public function getNextPractice()
    {
        $this->currentPracticeIndex++;
        $this->dispatch('practice-updated');
    }

    public function answerPractice($answer)
    {
        $practiceId = \App\Models\Practice::where('slug', 'melodic-interval-practice')->value('id');
        $userPractice = UserPractice::firstOrCreate(
            ['user_id' => auth()->user()->id, 'practice_id' => $practiceId],
            ['total_questions' => 0, 'correct_answers' => 0, 'incorrect_answers' => 0, 'score' => 0]
        );

        $data   = $this->getCurrentPracticeData();
        $target = app(MusicTheoryService::class)->getAnswerFromQuestion($data, 'melodic-interval-practice');
        $isCorrect = strtolower(preg_replace('/\s+/', '', $answer)) === strtolower(preg_replace('/\s+/', '', $target));

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
