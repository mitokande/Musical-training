<?php

namespace App\Http\Controllers;

use App\Models\IntervalDirectionPractice;
use App\Models\Practice;
use App\Models\SingleNotePractice;
use Illuminate\Http\Request;

class PageController extends Controller
{
    //

    public function learnView() {
        $practices = Practice::all();
        return view('learn', compact('practices'));
    }

    public function practiceView($slug) {
        $practiceMap = [
            'single-note-practice' => SingleNotePractice::class,
            'interval-direction-practice' => IntervalDirectionPractice::class,
        ];

        $practiceClass = $practiceMap[$slug] ?? null;

        if (!$practiceClass) {
            abort(404, 'Practice not found');
        }

        $practices = $practiceClass::inRandomOrder()->get();
        return view('practice', [
            'practices' => $practices,
            'slug' => $slug,
        ]);
    }
}
