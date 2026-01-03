<?php

namespace App\Http\Controllers;

use App\Models\IntervalDirectionPractice;
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


    public static function getIntervalDirectionProgress() {
        $user_id = auth()->user()->id;
        $userIntervalPractice = UserPractice::where('user_id','=' ,$user_id)->where('practice_id', '=', '2')->get();
        return $userIntervalPractice;
    }

    public static function getPracticeProgressByUser($slug) {
        if ($slug == "interval-direction-practice") {
            $userP = self::getIntervalDirectionProgress();
            $solved = 0;
            if (count($userP)> 0) {

                $solved = $userP[0]->total_questions;
            }
            $all = IntervalDirectionPractice::all();
            $progress = $solved / count($all);
            return $progress == 1 ? 100 : $progress;
        }
    }
}
