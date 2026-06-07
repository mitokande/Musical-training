<?php

namespace App\Livewire;

use App\Livewire\Concerns\HandlesPracticeData;
use App\Models\IntervalConstructionPractice;
use App\Models\LearningPathExercise;
use App\Models\UserPractice;
use App\Services\LearningPathQuestionGenerator;
use App\Services\MusicTheoryService;
use Livewire\Component;

class PracticeIntervalConstruction extends Component
{
    use HandlesPracticeData;

    private const ALL_INTERVALS = [
        'Minor 2nd', 'Major 2nd', 'Minor 3rd', 'Major 3rd', 'Perfect 4th',
        'Tritone', 'Perfect 5th', 'Minor 6th', 'Major 6th',
        'Minor 7th', 'Major 7th', 'Perfect Octave',
    ];

    private const INTERVAL_POOL_MAP = [
        'm2' => 'Minor 2nd',   'M2' => 'Major 2nd',
        'm3' => 'Minor 3rd',   'M3' => 'Major 3rd',
        'P4' => 'Perfect 4th', 'TT' => 'Tritone',
        'P5' => 'Perfect 5th', 'm6' => 'Minor 6th',
        'M6' => 'Major 6th',   'm7' => 'Minor 7th',
        'M7' => 'Major 7th',   '8ve' => 'Perfect Octave',
    ];

    private const DIATONIC_NOTE_POOL = [
        'C', 'C#', 'Db', 'D', 'D#', 'Eb', 'E', 'E#', 'Fb',
        'F', 'F#', 'Gb', 'G', 'G#', 'Ab', 'A', 'A#', 'Bb',
        'B', 'B#', 'Cb', 'C##', 'D##', 'F##', 'G##', 'A##',
        'Dbb', 'Ebb', 'Gbb', 'Abb', 'Bbb',
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

            $allowedIntervals = self::ALL_INTERVALS;
            if (!empty($settings['interval_pool'])) {
                $mapped = array_values(array_filter(
                    array_map(fn($a) => self::INTERVAL_POOL_MAP[$a] ?? null, $settings['interval_pool'])
                ));
                if (!empty($mapped)) {
                    $allowedIntervals = $mapped;
                }
            }

            $generator = app(LearningPathQuestionGenerator::class);
            $octaveRange = !empty($settings['octave_range']) ? $settings['octave_range'] : [4];
            sort($octaveRange);
            $midOctave = (string) $octaveRange[(int)(count($octaveRange) / 2)];
            $exercise = new LearningPathExercise(['config_json' => [
                'practice_type'      => 'interval-construction-practice',
                'allowed_intervals'  => $allowedIntervals,
                'allowed_root_notes' => ['C', 'D', 'E', 'F', 'G', 'A', 'B'],
                'octave'             => $midOctave,
            ]]);
            $generated = $generator->generate($exercise, $count)->values()
                ->map(function ($q, $i) { $q->id = $i + 1; return $q; });

            session(['exercise_practice_session' => [
                'practice_type'  => 'interval-construction-practice',
                'question_count' => $generated->count(),
                'questions'      => $generator->serializeForSession($generated),
            ]]);

            $music = app(MusicTheoryService::class);
            $this->practiceDataArray = $generated->map(function ($q) use ($music) {
                $data    = $this->serializeOnePractice($q);
                $correct = $data['note2'];

                // Pick distractors from diatonic pool, excluding enharmonic equivalents
                $pool = self::DIATONIC_NOTE_POOL;
                shuffle($pool);
                $distractors = [];
                foreach ($pool as $candidate) {
                    if (count($distractors) >= 3) break;
                    if ($music->notesAreEnharmonic($candidate, $correct)) continue;
                    $distractors[] = $candidate;
                }

                $options = array_merge([$correct], $distractors);
                shuffle($options);
                $data['options'] = $options;
                return $data;
            })->values()->toArray();
        } else {
            $this->practiceDataArray = $this->serializePractices($practices);
        }
    }

    public function render()
    {
        $data = $this->getCurrentPracticeData();
        $currentPractice = $this->buildModelFromData(IntervalConstructionPractice::class, $data);
        return view('livewire.practice-interval-construction', [
            'practices'            => $this->practiceDataArray,
            'currentPractice'      => $currentPractice,
            'currentPracticeIndex' => $this->currentPracticeIndex,
            'noteOptions'          => $data['options'] ?? null,
            'settings'             => $this->settings,
        ]);
    }

    public function getNextPractice()
    {
        $this->currentPracticeIndex++;
        $this->dispatch('practice-updated');
    }

    public function answerPractice($answer) {
        $practiceId = \App\Models\Practice::where('slug', 'interval-construction-practice')->value('id');
        $userPractice = UserPractice::firstOrCreate(
            ['user_id' => auth()->user()->id, 'practice_id' => $practiceId],
            ['total_questions' => 0, 'correct_answers' => 0, 'incorrect_answers' => 0, 'score' => 0]
        );

        $data   = $this->getCurrentPracticeData();
        $target = app(MusicTheoryService::class)->getAnswerFromQuestion($data, 'interval-construction-practice');
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
