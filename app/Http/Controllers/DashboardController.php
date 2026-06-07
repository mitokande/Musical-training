<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        return match ($user->role) {
            'admin' => redirect()->route('admin.dashboard'),
            'teacher' => view('dashboards.teacher', ['user' => $user]),
            'school' => view('dashboards.school', ['user' => $user]),
            default => view('dashboards.user', ['user' => $user]),
        };
    }
}
