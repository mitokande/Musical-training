<?php

namespace App\Livewire;

use App\Models\UserPractice;
use Livewire\Component;

class PracticeMixed extends Component
{
    public $practices = [];
    public $currentPractice;
    public $currentPracticeIndex = 0;
    public $totalQuestions;
    public $correctCount = 0;
    public $incorrectCount = 0;
    public $xpEarned = 0;
    public $sessionTitle = 'Mixed Practice';

    public function mount($practices, $title = 'Mixed Practice')
    {
        // Convert practices to plain arrays (not model instances) to survive Livewire hydration
        $this->practices = collect($practices)->map(function ($practice) {
            $type = $this->getPracticeType($practice);
            
            // Convert model to array to prevent Livewire hydration issues
            $data = $practice->toArray();
            
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
            default => 'unknown',
        };
    }

    /**
     * Get practice ID based on type (for UserPractice tracking)
     */
    protected function getPracticeIdByType(string $type): int
    {
        return match ($type) {
            'single_note' => 1,
            'interval_direction' => 2,
            default => 0,
        };
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

    public function answerPractice($answer, $target)
    {
        $practiceType = $this->currentPractice['type'] ?? 'unknown';
        $practiceId = $this->getPracticeIdByType($practiceType);

        // Track user progress
        $userPractice = UserPractice::firstOrCreate([
            'user_id' => auth()->user()->id,
            'practice_id' => $practiceId,
        ]);

        $userPractice->total_questions++;

        $isCorrect = strtolower($answer) === strtolower($target);

        if ($isCorrect) {
            $userPractice->correct_answers++;
            $this->correctCount++;
            $this->xpEarned += 10; // Award XP for correct answers
        } else {
            $userPractice->incorrect_answers++;
            $this->incorrectCount++;
        }

        $userPractice->score = ($userPractice->correct_answers / $userPractice->total_questions) * 100;
        $userPractice->save();

        return [
            'is_correct' => $isCorrect,
            'correctCount' => $this->correctCount,
            'totalCount' => $this->currentPracticeIndex + 1,
            'xp' => $this->xpEarned,
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
}

