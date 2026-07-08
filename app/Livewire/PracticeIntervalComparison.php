<?php

namespace App\Livewire;

use App\Http\Controllers\AIController;
use App\Livewire\Concerns\HandlesPracticeData;
use App\Models\IntervalComparisonPractice;
use App\Models\LearningPathExercise;
use App\Models\Practice;
use App\Models\UserPractice;
use App\Services\LearningPathQuestionGenerator;
use Livewire\Component;

class PracticeIntervalComparison extends Component
{
    use HandlesPracticeData;

    private const DEFAULT_PAIRS = [
        ['C,D', 'C,E'],  ['C,D', 'C,F'],  ['C,E', 'C,G'],  ['C,D', 'C,G'],
        ['C,F', 'C,G'],  ['C,E', 'C,A'],  ['C,G', 'C,A'],  ['C,D', 'C,A'],
        ['C,E', 'C,B'],  ['C,F', 'C,B'],  ['C,G', 'C,B'],  ['C,A', 'C,B'],
        ['C,D', 'C,B'],  ['C,E', 'C,F'],  ['C,F', 'C,A'],  ['C,G', 'C,B'],
    ];

    // Maps interval abbreviation → canonical same-octave C-root note pair,
    // using correct diatonic spelling (m2 above C is Db, not C#; m3 is Eb…)
    // Public: AIController mirrors these Exercise Setup comparison rules when
    // building the AI Assisted Exercises difficulty presets.
    public const POOL_TO_PAIR = [
        'm2' => 'C,Db',
        'M2' => 'C,D',
        'm3' => 'C,Eb',
        'M3' => 'C,E',
        'P4' => 'C,F',
        'TT' => 'C,F#',
        'P5' => 'C,G',
        'm6' => 'C,Ab',
        'M6' => 'C,A',
        'm7' => 'C,Bb',
        'M7' => 'C,B',
        // '8ve' omitted — same-octave pair gives 0 semitones (C,C), unusable for comparison
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

        if (! empty($settings)) {
            $this->settings = $settings;
            $this->replayLimit = $settings['replay_limit'] ?? null;
            $this->feedbackMode = $settings['feedback_mode'] ?? 'immediate';
            $this->timeLimitSeconds = (int) ($settings['time_limit_seconds'] ?? 0);

            $count = (int) ($settings['question_count'] ?? 10);

            $intervalPairs = self::DEFAULT_PAIRS;
            if (! empty($settings['interval_pool'])) {
                $builtPairs = self::buildPairsFromPool($settings['interval_pool']);
                if (! empty($builtPairs)) {
                    $intervalPairs = $builtPairs;
                }
            }

            $generator = app(LearningPathQuestionGenerator::class);
            // No explicit octave: the generator places the pair inside the
            // selected clef's playable range (CLEF_RANGES).
            $exercise = new LearningPathExercise(['config_json' => [
                'practice_type' => 'interval-comparison-practice',
                'allowed_interval_pairs' => $intervalPairs,
                'clef' => $settings['clef'] ?? 'treble',
            ]]);
            $generated = $generator->generate($exercise, $count)->values()
                ->map(function ($q, $i) {
                    $q->id = $i + 1;

                    return $q;
                });

            session(['exercise_practice_session' => [
                'practice_type' => 'interval-comparison-practice',
                'question_count' => $generated->count(),
                'questions' => $generator->serializeForSession($generated),
            ]]);

            $this->practiceDataArray = $this->serializePractices($generated->all());
        } else {
            $this->practiceDataArray = $this->serializePractices($practices);
        }
    }

    /**
     * Build every pairwise comparison combination from a pool of interval
     * abbreviations (m2…M7). This is the canonical Exercise Setup rule for
     * turning the user's interval pool into comparison questions; the AI
     * Assisted Exercises flow reuses it so both flows share one logic.
     * Returns [] when fewer than two known abbreviations are given.
     */
    public static function buildPairsFromPool(array $pool): array
    {
        $selectedPairs = array_values(array_filter(
            array_map(fn ($a) => self::POOL_TO_PAIR[$a] ?? null, $pool)
        ));
        if (count($selectedPairs) < 2) {
            return [];
        }

        $builtPairs = [];
        for ($i = 0; $i < count($selectedPairs); $i++) {
            for ($j = $i + 1; $j < count($selectedPairs); $j++) {
                $builtPairs[] = [$selectedPairs[$i], $selectedPairs[$j]];
            }
        }

        return $builtPairs;
    }

    public function render()
    {
        $currentPractice = $this->buildModelFromData(IntervalComparisonPractice::class, $this->getCurrentPracticeData());

        return view('livewire.practice-interval-comparison', [
            'practices' => $this->practiceDataArray,
            'currentPractice' => $currentPractice,
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
        $practiceId = Practice::where('slug', 'interval-comparison-practice')->value('id');
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

    public function generateIntervalComparisonPractice()
    {
        try {
            $aiController = new AIController;
            $practice = $aiController->generateIntervalDirectionPractice();

            if (isset($practice['error'])) {
                return;
            }

            $intervalComparisonPractice = new IntervalComparisonPractice;
            $intervalComparisonPractice->clef = $practice['clef'] ?? 'treble';
            $intervalComparisonPractice->interval_a = $practice['interval_a'] ?? '';
            $intervalComparisonPractice->interval_b = $practice['interval_b'] ?? '';
            $intervalComparisonPractice->target = $practice['target'] ?? '';
            $intervalComparisonPractice->octave = $practice['octave'] ?? '4';
            $intervalComparisonPractice->save();
            $this->practiceDataArray = $this->serializePractices([$intervalComparisonPractice]);
            $this->currentPracticeIndex = 0;
            $this->dispatch('practice-updated');
        } catch (\Exception $e) {
            \Log::error('Failed to generate interval comparison practice: '.$e->getMessage());
        }
    }
}
