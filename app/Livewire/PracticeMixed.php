<?php

namespace App\Livewire;

use App\Http\Controllers\AIController;
use App\Http\Controllers\PageController;
use App\Models\UserIntervalStat;
use App\Models\UserPractice;
use App\Services\MusicTheoryService;
use Livewire\Component;

class PracticeMixed extends Component
{
    public $practices = [];
    public $answers = [];

    public $currentPractice;
    public $currentPracticeIndex = 0;
    public $totalQuestions;
    public $correctCount = 0;
    public $incorrectCount = 0;
    public $xpEarned = 0;
    public $sessionTitle = 'Mixed Practice';
    
    public $showResults = false;
    public $coachNotes = null;
    public $isGeneratingNotes = false;

    public function mount($practices, $title = 'Mixed Practice')
    {
        // Convert practices to plain arrays (not model instances) to survive Livewire hydration
        $this->practices = collect($practices)->map(function ($practice) {
            $type = $this->getPracticeType($practice);

            // Convert model to array to prevent Livewire hydration issues
            $data = $practice->toArray();

            // Include computed note_array for chord/scale (not in $appends, so toArray() skips it)
            if (in_array($type, ['chord', 'scale']) && method_exists($practice, 'getNoteArrayAttribute')) {
                $data['note_array'] = $practice->note_array;
            }

            return [
                'data' => $data,
                'type' => $type,
            ];
        })->toArray();
        
        $this->totalQuestions = count($this->practices);
        $this->sessionTitle = $title;

        if ($this->totalQuestions > 0) {
            $this->currentPractice = $this->practices[$this->currentPracticeIndex];
        }
    }

    /**
     * Determine the practice type from the model
     */
    protected function getPracticeType($practice): string
    {
        $class = get_class($practice);

        return match ($class) {
            'App\Models\SingleNotePractice' => 'single_note',
            'App\Models\IntervalDirectionPractice' => 'interval_direction',
            'App\Models\IntervalComparisonPractice' => 'interval_comparison',
            'App\Models\MelodicIntervalPractice' => 'melodic_interval',
            'App\Models\HarmonicIntervalPractice' => 'harmonic_interval',
            'App\Models\IntervalConstructionPractice' => 'interval_construction',
            'App\Models\ChordPractice' => 'chord',
            'App\Models\ScalePractice' => 'scale',
            'App\Models\RhythmPractice' => 'rhythm',
            'App\Models\MelodicDictationPractice' => 'melodic_dictation',
            default => 'unknown',
        };
    }

    /**
     * Get practice ID based on type (for UserPractice tracking)
     */
    protected function getPracticeIdByType(string $type): int
    {
        $slug = $this->slugForType($type);
        if (!$slug) {
            return 0;
        }

        return \App\Models\Practice::where('slug', $slug)->value('id') ?? 0;
    }

    /**
     * Map the internal underscore practice type to its canonical slug.
     */
    protected function slugForType(string $type): ?string
    {
        return [
            'single_note'          => 'single-note-practice',
            'interval_direction'   => 'interval-direction-practice',
            'interval_comparison'  => 'interval-comparison-practice',
            'melodic_interval'     => 'melodic-interval-practice',
            'harmonic_interval'    => 'harmonic-interval-practice',
            'interval_construction'=> 'interval-construction-practice',
            'chord'                => 'chord-practice',
            'scale'                => 'scale-practice',
            'rhythm'               => 'rhythm-practice',
            'melodic_dictation'    => 'melodic-dictation',
        ][$type] ?? null;
    }

    public function render()
    {
        return view('livewire.practice-mixed', [
            'practices' => $this->practices,
            'currentPractice' => $this->currentPractice,
            'currentPracticeIndex' => $this->currentPracticeIndex,
            'totalQuestions' => $this->totalQuestions,
            'correctCount' => $this->correctCount,
            'incorrectCount' => $this->incorrectCount,
            'xpEarned' => $this->xpEarned,
            'sessionTitle' => $this->sessionTitle,
            'answers' => $this->answers,
        ]);
    }

    public function getNextPractice()
    {
        $this->currentPracticeIndex++;
        
        if (isset($this->practices[$this->currentPracticeIndex])) {
            $this->currentPractice = $this->practices[$this->currentPracticeIndex];
        }

        $this->dispatch('practice-updated');
    }

    public function answerPractice($practiceType, $answer, $target)
    {
        $this->skipRender();

        // Save answer for coach notes
        $this->answers[] = [
            'id' => $this->practices[$this->currentPracticeIndex]['data']['id'] ?? 0,
            'answer' => $answer,
            'target' => $target,
        ];

        $userPractice = UserPractice::firstOrCreate(
            [
                'user_id' => auth()->user()->id,
                'practice_id' => $this->getPracticeIdByType($practiceType),
            ],
            ['total_questions' => 0, 'correct_answers' => 0, 'incorrect_answers' => 0, 'score' => 0]
        );

        $userPractice->total_questions++;

        $isCorrect = strtolower(trim($answer)) === strtolower(trim($target));

        if ($isCorrect) {
            $userPractice->correct_answers++;
            $this->correctCount++;
            $this->xpEarned += 10;
        } else {
            $userPractice->incorrect_answers++;
            $this->incorrectCount++;
        }
        $userPractice->score = $userPractice->total_questions > 0
            ? ($userPractice->correct_answers / $userPractice->total_questions) * 100
            : 0;
        $userPractice->save();

        $slug = $this->slugForType($practiceType);
        if ($slug) {
            $data     = $this->practices[$this->currentPracticeIndex]['data'] ?? [];
            $interval = app(MusicTheoryService::class)->intervalForStats($data, $slug);
            if ($interval !== null) {
                UserIntervalStat::record(auth()->id(), $this->getPracticeIdByType($practiceType), $interval, $isCorrect);
            }
        }

        return [
            'answer' => $answer,
            'target' => $target,
            'is_correct' => $isCorrect,
        ];
    }

    /**
     * Get the current practice data
     */
    public function getCurrentPracticeData()
    {
        return $this->currentPractice['data'] ?? null;
    }

    /**
     * Get the current practice type
     */
    public function getCurrentPracticeType(): string
    {
        return $this->currentPractice['type'] ?? 'unknown';
    }

    /**
     * Check if there are more practices
     */
    public function hasMorePractices(): bool
    {
        return $this->currentPracticeIndex < ($this->totalQuestions - 1);
    }

    /**
     * Get progress percentage
     */
    public function getProgressPercentage(): float
    {
        if ($this->totalQuestions === 0) {
            return 0;
        }

        return (($this->currentPracticeIndex + 1) / $this->totalQuestions) * 100;
    }

    public function generateCoachNotes() {
        $this->isGeneratingNotes = true;
        
        $aiController = new AIController();
        $questions = $this->practices;
        $answers = $this->answers;
        $coachNotes = $aiController->generateCoachNotes(['questions' => $questions, 'answers' => $answers]);
        
        $this->coachNotes = $coachNotes;
        $this->isGeneratingNotes = false;
        $this->showResults = true;
    }

    public function saveAnswerPractice($answer, $target) {
        $this->skipRender();
        $this->answers[] = [
            'id' => $this->practices[$this->currentPracticeIndex]['data']['id'],
            'answer' => $answer,
            'target' => $target,
        ];
    }
}

