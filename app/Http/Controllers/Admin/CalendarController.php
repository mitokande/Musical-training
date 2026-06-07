<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use Illuminate\Http\Request;

class CalendarController extends Controller
{
    public function index()
    {
        return view('admin.calendar.index');
    }

    public function events(Request $request)
    {
        $appointments = Appointment::with(['teacher:id,name', 'student:id,name'])
            ->when($request->start, fn($q, $start) => $q->where('starts_at', '>=', $start))
            ->when($request->end, fn($q, $end) => $q->where('ends_at', '<=', $end))
            ->get();

        $colors = [
            'lesson'       => '#4CAF50',
            'consultation' => '#2196F3',
            'meeting'      => '#FF9800',
            'trial'        => '#9C27B0',
        ];

        $events = $appointments->map(fn($apt) => [
            'id'    => $apt->id,
            'title' => $apt->title,
            'start' => $apt->starts_at->toIso8601String(),
            'end'   => $apt->ends_at->toIso8601String(),
            'color' => $colors[$apt->type] ?? '#607D8B',
            'extendedProps' => [
                'type'    => $apt->type,
                'status'  => $apt->status,
                'teacher' => $apt->teacher?->name,
                'student' => $apt->student?->name,
            ],
        ]);

        return response()->json($events);
    }
}
