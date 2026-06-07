<?php

namespace App\Livewire;

use App\Http\Controllers\AIController;
use App\Livewire\Concerns\HandlesPracticeData;
use App\Models\IntervalDirectionPractice;
use App\Models\LearningPathExercise;
use App\Models\UserPractice;
use App\Services\LearningPathQuestionGenerator;
use App\Services\MusicTheoryService;
use Livewire\Component;

class PracticeIntervalDirection extends Component
{
    use HandlesPracticeData;

    private const POOL_TO_SEMITONES = [
        'm2'  => 1,  'M2'  => 2,  'm3'  => 3,  'M3'  => 4,
        'P4'  => 5,  'TT'  => 6,  'P5'  => 7,  'm6'  => 8,
        'M6'  => 9,  'm7'  => 10, 'M7'  => 11, '8ve' => 12,
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
            $direction = $settings['direction'] ?? 'mixed';

            $semitones = !empty($settings['interval_pool'])
                ? array_values(array_filter(array_map(fn($a) => self::POOL_TO_SEMITONES[$a] ?? null, $settings['interval_pool'])))
                : range(1, 12);
            if (empty($semitones)) {
                $semitones = range(1, 12);
            }

            $generator = app(LearningPathQuestionGenerator::class);
            $octaveRange = !empty($settings['octave_range']) ? $settings['octave_range'] : [4];
            sort($octaveRange);
            $midOctave = (string) $octaveRange[(int)(count($octaveRange) / 2)];
            $exercise = new LearningPathExercise(['config_json' => [
                'practice_type'               => 'interval-direction-practice',
                'allowed_intervals_semitones' => $semitones,
                'allowed_notes'               => ['C', 'D', 'E', 'F', 'G', 'A', 'B'],
                'octave'                      => $midOctave,
                'clef'                        => $settings['clef'] ?? 'treble',
            ]]);

            $genCount = ($direction !== 'mixed') ? $count * 3 : $count;
            $raw = $generator->generate($exercise, $genCount)->values();

            if ($direction !== 'mixed') {
                $filtered = $raw->filter(fn($q) => $q->direction === $direction)->values();
                $pool = $filtered->count() > 0 ? $filtered : $raw;
                while ($pool->count() < $count) {
                    $pool = $pool->merge($pool->shuffle()->values());
                }
                $raw = $pool->take($count)->values();
            }

            $generated = $raw->map(function ($q, $i) { $q->id = $i + 1; return $q; });

            session(['exercise_practice_session' => [
                'practice_type'  => 'interval-direction-practice',
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
        $currentPractice = $this->buildModelFromData(IntervalDirectionPractice::class, $this->getCurrentPracticeData());
        return view('livewire.practice-interval-direction', [
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

    public function answerPractice($answer) {
        $practiceId = \App\Models\Practice::where('slug', 'interval-direction-practice')->value('id');
        $userPractice = UserPractice::firstOrCreate(
            ['user_id' => auth()->user()->id, 'practice_id' => $practiceId],
            ['total_questions' => 0, 'correct_answers' => 0, 'incorrect_answers' => 0, 'score' => 0]
        );

        $data = $this->getCurrentPracticeData();
        // Re-derive direction from actual note pitches — never trust stored label alone
        $music  = app(MusicTheoryService::class);
        $octave1 = (int) ($data['octave'] ?? 4);
        $octave2 = (int) ($data['note2_octave'] ?? $octave1);
        $target  = $music->getDirection($data['note1'] ?? '', $octave1, $data['note2'] ?? '', $octave2);
        $isCorrect = strtolower(trim($answer)) === $target;

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

    public function generateIntervalDirectionPractice() {
        try {
            $aiController = new AIController();
            $practice = $aiController->generateIntervalDirectionPractice();

            if (isset($practice['error'])) {
                return;
            }

            $intervalDirectionPractice = new IntervalDirectionPractice();
            $intervalDirectionPractice->clef = $practice['clef'];
            $intervalDirectionPractice->note1 = $practice['note1'];
            $intervalDirectionPractice->note2 = $practice['note2'];
            $intervalDirectionPractice->direction = $practice['direction'];
            $intervalDirectionPractice->octave = $practice['octave'];
            $intervalDirectionPractice->save();
            $this->practiceDataArray = $this->serializePractices([$intervalDirectionPractice]);
            $this->currentPracticeIndex = 0;
            $this->dispatch('practice-updated');
        } catch (\Exception $e) {
            \Log::error('Failed to generate interval direction practice: ' . $e->getMessage());
        }
    }
}
