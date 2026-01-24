<?php

namespace App\Http\Controllers;

use App\Models\IntervalComparisonPractice;
use App\Models\IntervalDirectionPractice;
use App\Models\MelodicIntervalPractice;
use App\Models\HarmonicIntervalPractice;
use App\Models\IntervalConstructionPractice;
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

    public static function getSingleNoteProgress() {
        $user_id = auth()->user()->id;
        $userSingleNotePractice = UserPractice::where('user_id','=' ,$user_id)->where('practice_id', '=', '1')->get();
        return $userSingleNotePractice;
    }

    public static function getIntervalComparisonProgress() {
        $user_id = auth()->user()->id;
        $userIntervalComparisonPractice = UserPractice::where('user_id','=' ,$user_id)->where('practice_id', '=', '3')->get();
        return $userIntervalComparisonPractice;
    }

    public static function getMelodicIntervalProgress() {
        $user_id = auth()->user()->id;
        $userMelodicIntervalPractice = UserPractice::where('user_id','=' ,$user_id)->where('practice_id', '=', '4')->get();
        return $userMelodicIntervalPractice;
    }

    public static function getHarmonicIntervalProgress() {
        $user_id = auth()->user()->id;
        $userHarmonicIntervalPractice = UserPractice::where('user_id','=' ,$user_id)->where('practice_id', '=', '5')->get();
        return $userHarmonicIntervalPractice;
    }

    public static function getIntervalConstructionProgress() {
        $user_id = auth()->user()->id;
        $userIntervalConstructionPractice = UserPractice::where('user_id','=' ,$user_id)->where('practice_id', '=', '6')->get();
        return $userIntervalConstructionPractice;
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
        if ($slug == "single-note-practice") {
            $userP = self::getSingleNoteProgress();
            $solved = 0;
            if (count($userP)> 0) {
                $solved = $userP[0]->total_questions;
            }
            $all = SingleNotePractice::all();
            $progress = $solved / count($all);
            return $progress == 1 ? 100 : $progress;
        }
        if ($slug == "interval-comparison-practice") {
            $userP = self::getIntervalComparisonProgress();
            $solved = 0;
            if (count($userP)> 0) {
                $solved = $userP[0]->total_questions;
            }
            $all = IntervalComparisonPractice::all();
            $progress = count($all) > 0 ? $solved / count($all) : 0;
            return $progress == 1 ? 100 : $progress;
        }
        if ($slug == "melodic-interval-practice") {
            $userP = self::getMelodicIntervalProgress();
            $solved = 0;
            if (count($userP)> 0) {
                $solved = $userP[0]->total_questions;
            }
            $all = MelodicIntervalPractice::all();
            $progress = count($all) > 0 ? $solved / count($all) : 0;
            return $progress == 1 ? 100 : $progress;
        }
        if ($slug == "harmonic-interval-practice") {
            $userP = self::getHarmonicIntervalProgress();
            $solved = 0;
            if (count($userP)> 0) {
                $solved = $userP[0]->total_questions;
            }
            $all = HarmonicIntervalPractice::all();
            $progress = count($all) > 0 ? $solved / count($all) : 0;
            return $progress == 1 ? 100 : $progress;
        }
        if ($slug == "interval-construction-practice") {
            $userP = self::getIntervalConstructionProgress();
            $solved = 0;
            if (count($userP)> 0) {
                $solved = $userP[0]->total_questions;
            }
            $all = IntervalConstructionPractice::all();
            $progress = count($all) > 0 ? $solved / count($all) : 0;
            return $progress == 1 ? 100 : $progress;
        }
    }
}
