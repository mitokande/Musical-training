<?php

namespace App\Livewire;

use App\Models\MelodicIntervalPractice;
use App\Models\UserPractice;
use Livewire\Component;

class PracticeMelodicInterval extends Component
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
        \Log::info('render melodic interval', ['currentPracticeIndex' => $this->currentPracticeIndex, 'practices' => $this->practices]);

        return view('livewire.practice-melodic-interval', [
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
            'practice_id' => 4, // Melodic Interval Practice ID
        ]);

        $userPractice->total_questions++;
        $userPractice->save();
        $target = $this->currentPractice->interval;

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
}
