<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\User;
use Illuminate\Http\Request;

class AppointmentController extends Controller
{
    public function create()
    {
        $teachers = User::where('role', 'teacher')->select('id', 'name')->get();
        $students = User::where('role', 'user')->select('id', 'name')->get();

        return view('admin.appointments.create', compact('teachers', 'students'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'teacher_id'  => 'required|exists:users,id',
            'student_id'  => 'nullable|exists:users,id',
            'school_id'   => 'nullable|exists:schools,id',
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'starts_at'   => 'required|date',
            'ends_at'     => 'required|date|after:starts_at',
            'type'        => 'required|string|in:lesson,consultation,meeting,trial',
            'status'      => 'required|string|in:scheduled,confirmed,cancelled,completed',
            'location'    => 'nullable|string|max:255',
            'meeting_url' => 'nullable|url',
            'notes'       => 'nullable|string',
        ]);

        Appointment::create($validated);

        return redirect()->route('admin.calendar.index')
            ->with('success', 'Appointment created successfully.');
    }

    public function show(Appointment $appointment)
    {
        $appointment->load(['teacher', 'student']);

        return view('admin.appointments.show', compact('appointment'));
    }

    public function edit(Appointment $appointment)
    {
        $teachers = User::where('role', 'teacher')->select('id', 'name')->get();
        $students = User::where('role', 'user')->select('id', 'name')->get();

        return view('admin.appointments.edit', compact('appointment', 'teachers', 'students'));
    }

    public function update(Request $request, Appointment $appointment)
    {
        $validated = $request->validate([
            'teacher_id'  => 'required|exists:users,id',
            'student_id'  => 'nullable|exists:users,id',
            'school_id'   => 'nullable|exists:schools,id',
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'starts_at'   => 'required|date',
            'ends_at'     => 'required|date|after:starts_at',
            'type'        => 'required|string|in:lesson,consultation,meeting,trial',
            'status'      => 'required|string|in:scheduled,confirmed,cancelled,completed',
            'location'    => 'nullable|string|max:255',
            'meeting_url' => 'nullable|url',
            'notes'       => 'nullable|string',
        ]);

        $appointment->update($validated);

        return redirect()->route('admin.calendar.index')
            ->with('success', 'Appointment updated successfully.');
    }

    public function destroy(Appointment $appointment)
    {
        $appointment->delete();

        return redirect()->route('admin.calendar.index')
            ->with('success', 'Appointment deleted successfully.');
    }
}
