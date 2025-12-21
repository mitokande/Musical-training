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

        $practice = $practiceMap[$slug];

        if (!$practice) {
            return redirect()->back()->with('error', 'Practice not found');
        }
        $practices = $practice->all();
        return view('practice', compact('practices'));
    }
}
