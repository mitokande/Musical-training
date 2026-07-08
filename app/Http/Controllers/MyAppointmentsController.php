<?php

namespace App\Http\Controllers;

use App\Models\TeacherAppointment;
use App\Services\Teacher\TeacherSchedulingService;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use InvalidArgumentException;

/** Student-facing appointment list and actions. */
class MyAppointmentsController extends Controller
{
    public function __construct(private TeacherSchedulingService $scheduling) {}

    public function index(Request $request)
    {
        $appointments = TeacherAppointment::with('teacher.teacherProfile')
            ->where('student_id', $request->user()->id)
            ->orderByDesc('starts_at')
            ->get();

        return view('my-appointments', [
            'upcoming' => $appointments->filter(fn ($a) => $a->starts_at->isFuture()
                && in_array($a->status, TeacherAppointment::BLOCKING_STATUSES, true)),
            'history' => $appointments->reject(fn ($a) => $a->starts_at->isFuture()
                && in_array($a->status, TeacherAppointment::BLOCKING_STATUSES, true)),
        ]);
    }

    public function cancel(Request $request, TeacherAppointment $appointment)
    {
        abort_unless($appointment->student_id === $request->user()->id, 403);

        try {
            $this->scheduling->cancel($appointment, $request->user(), $request->input('reason'));
        } catch (InvalidArgumentException $e) {
            return back()->withErrors(['appointment' => $e->getMessage()]);
        }

        return back()->with('status', 'appointment-cancelled');
    }

    public function requestReschedule(Request $request, TeacherAppointment $appointment)
    {
        abort_unless($appointment->student_id === $request->user()->id, 403);

        $request->validate([
            'starts_at' => 'required|date|after:now',
            'note' => 'nullable|string|max:500',
        ]);

        try {
            $this->scheduling->requestReschedule(
                $appointment,
                $request->user(),
                CarbonImmutable::parse($request->starts_at, $appointment->timezone ?? config('app.timezone')),
                $request->input('note'),
            );
        } catch (InvalidArgumentException $e) {
            return back()->withErrors(['appointment' => $e->getMessage()]);
        }

        return back()->with('status', 'reschedule-requested');
    }
}
