<?php

namespace App\Livewire;

use App\Http\Controllers\AIController;
use App\Models\IntervalComparisonPractice;
use App\Models\UserPractice;
use Livewire\Component;

class PracticeIntervalComparison extends Component
{
    public $practices;
    public $currentPractice;
    public $currentPracticeIndex = 0;

    public function mount($practices)
    {
        $this->practices = $practices;
        $this->currentPractice = $this->practices[$this->currentPracticeIndex];
    }

    public function render()
    {
        \Log::info('render', ['currentPracticeIndex' => $this->currentPracticeIndex, 'practices' => $this->practices]);

        return view('livewire.practice-interval-comparison', [
            'practices' => $this->practices,
            'currentPractice' => $this->currentPractice,
            'currentPracticeIndex' => $this->currentPracticeIndex,
        ]);
    }

    public function getNextPractice()
    {
        $this->currentPracticeIndex++;
        \Log::info('getNextPractice', ['currentPracticeIndex' => $this->currentPracticeIndex]);
        if (isset($this->practices[$this->currentPracticeIndex])) {
            $this->currentPractice = $this->practices[$this->currentPracticeIndex];
        }
        $this->dispatch('practice-updated');
    }

    public function answerPractice($answer) {
        $userPractice = UserPractice::firstOrCreate([
            'user_id' => auth()->user()->id,
            'practice_id' => 3, // Interval Comparison Practice ID
        ]);

        $userPractice->total_questions++;
        $userPractice->save();
        $target = $this->currentPractice->target;

        if (strtolower($answer) == strtolower($target)) {
            $userPractice->correct_answers++;
        } else {
            $userPractice->incorrect_answers++;
        }
        $userPractice->score = $userPractice->correct_answers / $userPractice->total_questions * 100;
        $userPractice->save();

        if (strtolower($answer) == strtolower($target)) {
            return true;
        } else {
            return false;
        }
    }

    public function generateIntervalComparisonPractice() {
        $aiController = new AIController();
        $practice = $aiController->generateIntervalComparisonPractice();
        \Log::info($practice);
        $intervalComparisonPractice = new IntervalComparisonPractice();
        $intervalComparisonPractice->clef = $practice['clef'];
        $intervalComparisonPractice->interval_a = $practice['interval_a'];
        $intervalComparisonPractice->interval_b = $practice['interval_b'];
        $intervalComparisonPractice->target = $practice['target'];
        $intervalComparisonPractice->octave = $practice['octave'];
        $intervalComparisonPractice->save();
        $this->practices = [$intervalComparisonPractice];
        $this->currentPractice = $intervalComparisonPractice;
        $this->currentPracticeIndex = count($this->practices) - 1;
        $this->dispatch('practice-updated');
    }
}

