<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SingleNotePractice;
use App\Models\IntervalDirectionPractice;
use App\Models\IntervalComparisonPractice;

class AdminController extends Controller
{
    /**
     * Display the admin dashboard.
     */
    public function dashboard()
    {
        $stats = [
            'single_note_count' => SingleNotePractice::count(),
            'interval_direction_count' => IntervalDirectionPractice::count(),
            'interval_comparison_count' => IntervalComparisonPractice::count(),
        ];

        return view('admin.dashboard', compact('stats'));
    }
}

