<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SingleNotePractice;
use App\Models\IntervalDirectionPractice;
use App\Models\IntervalComparisonPractice;
use App\Models\User;

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
            'user_count' => User::count(),
            'student_count' => User::where('role', 'student')->count(),
            'admin_count' => User::where('role', 'admin')->count(),
        ];

        return view('admin.dashboard', compact('stats'));
    }
}

