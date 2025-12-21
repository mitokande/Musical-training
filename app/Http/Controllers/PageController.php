<?php

namespace App\Http\Controllers;

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
        ];

        $practiceClass = $practiceMap[$slug];

        if (!$practiceClass) {
            return redirect()->back()->with('error', 'Practice not found');
        }
        $practices = $practiceClass::all();
        return view('practice', compact('practices'));
    }
}
