<?php

namespace App\Http\Controllers;

use App\Models\SingleNotePractice;
use App\Models\UserPractice;
use Illuminate\Http\Request;

class PracticeController extends Controller
{
    //


    public function getSingleNotePractices() {
        $singleNotePractices = SingleNotePractice::all();
        return response()->json($singleNotePractices);
    }

    public function checkAnswer(Request $request) {
        $practiceId = $request->input('practice_id');

        $userPractice = UserPractice::firstOrCreate([
            'user_id' => auth()->user()->id,
            'practice_id' => $practiceId,
        ]);

        $userPractice->total_questions++;
        $userPractice->save();
        $answer = $request->input('answer');
        $target = $request->input('target');

        if ($answer == $target) {
            $userPractice->correct_answers++;
        } else {
            $userPractice->incorrect_answers++;
        }
        $userPractice->score = $userPractice->correct_answers / $userPractice->total_questions * 100;
        $userPractice->save();


        return response()->json([
            'answer' => $answer,
            'target' => $target,
            'is_correct' => $answer == $target,
        ]);
    }
}
