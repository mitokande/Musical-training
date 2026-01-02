<?php

namespace App\Livewire;

use App\Models\UserPractice;
use Livewire\Component;

class PracticeIntervalDirection extends Component
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
        return view('livewire.practice-interval-direction', [
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
    }

    public function answerPractice($answer) {
        $userPractice = UserPractice::firstOrCreate([
            'user_id' => auth()->user()->id,
            'practice_id' => 2,
        ]);

        $userPractice->total_questions++;
        $userPractice->save();
        $target = $this->currentPractice->direction;

        if ($answer == $target) {
            $userPractice->correct_answers++;
        } else {
            $userPractice->incorrect_answers++;
        }
        $userPractice->score = $userPractice->correct_answers / $userPractice->total_questions * 100;
        $userPractice->save();

        if ($answer == $target) {
            return true;
        } else {
            return false;
        }
    }
}
