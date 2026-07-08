<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\TeacherAppointment;
use App\Models\TeacherAvailability;
use App\Models\TeacherAvailabilityException;
use App\Models\TeacherBookingSetting;
use App\Services\Teacher\TeacherCapabilityService;
use App\Services\Teacher\TeacherSchedulingService;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use InvalidArgumentException;

class TeacherCalendarController extends Controller
{
    public function __construct(
        private TeacherCapabilityService $capabilities,
        private TeacherSchedulingService $scheduling,
    ) {}

    public function index(Request $request)
    {
        abort_unless($this->capabilities->canManageAvailability($request->user()), 403);

        $teacher = $request->user();

        return view('teacher.calendar.index', [
            'settings' => TeacherBookingSetting::forTeacher($teacher->id),
            'availabilities' => TeacherAvailability::where('teacher_id', $teacher->id)
                ->orderBy('weekday')->orderBy('start_time')->get(),
            'exceptions' => TeacherAvailabilityException::where('teacher_id', $teacher->id)
                ->where('date', '>=', now()->toDateString())->orderBy('date')->get(),
            'pending' => TeacherAppointment::with('student')
                ->where('teacher_id', $teacher->id)
                ->whereIn('status', [TeacherAppointment::STATUS_PENDING, TeacherAppointment::STATUS_RESCHEDULE_REQUESTED])
                ->orderBy('starts_at')->get(),
            'upcoming' => TeacherAppointment::with('student')
                ->where('teacher_id', $teacher->id)
                ->where('status', TeacherAppointment::STATUS_CONFIRMED)
                ->upcoming()->orderBy('starts_at')->limit(20)->get(),
            'past' => TeacherAppointment::with('student')
                ->where('teacher_id', $teacher->id)
                ->whereIn('status', [
                    TeacherAppointment::STATUS_CONFIRMED,
                    TeacherAppointment::STATUS_COMPLETED,
                    TeacherAppointment::STATUS_NO_SHOW,
                ])
                ->where('starts_at', '<', now())
                ->orderByDesc('starts_at')->limit(10)->get(),
        ]);
    }

    public function updateSettings(Request $request)
    {
        abort_unless($this->capabilities->canManageAvailability($request->user()), 403);

        $validated = $request->validate([
            'booking_enabled' => 'nullable|boolean',
            'lesson_duration_minutes' => 'required|integer|min:15|max:180',
            'buffer_minutes' => 'required|integer|min:0|max:60',
            'advance_booking_days' => 'required|integer|min:1|max:120',
            'min_notice_hours' => 'required|integer|min:0|max:168',
            'timezone' => 'required|timezone',
        ]);

        $validated['booking_enabled'] = $request->boolean('booking_enabled');

        TeacherBookingSetting::forTeacher($request->user()->id)->update($validated);

        return back()->with('status', 'booking-settings-saved');
    }

    public function storeAvailability(Request $request)
    {
        abort_unless($this->capabilities->canManageAvailability($request->user()), 403);

        $validated = $request->validate([
            'weekday' => 'required|integer|min:0|max:6',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
        ]);

        TeacherAvailability::create($validated + ['teacher_id' => $request->user()->id]);

        return back()->with('status', 'availability-saved');
    }

    public function destroyAvailability(Request $request, TeacherAvailability $availability)
    {
        abort_unless($this->capabilities->canManageAvailability($request->user()), 403);
        abort_unless($availability->teacher_id === $request->user()->id, 403);

        $availability->delete();

        return back()->with('status', 'availability-removed');
    }

    public function storeException(Request $request)
    {
        abort_unless($this->capabilities->canManageAvailability($request->user()), 403);

        $validated = $request->validate([
            'date' => 'required|date|after_or_equal:today',
            'start_time' => 'nullable|date_format:H:i|required_with:end_time',
            'end_time' => 'nullable|date_format:H:i|after:start_time',
            'is_blocked' => 'nullable|boolean',
            'note' => 'nullable|string|max:255',
        ]);

        $validated['is_blocked'] = $request->boolean('is_blocked', true);

        TeacherAvailabilityException::create($validated + ['teacher_id' => $request->user()->id]);

        return back()->with('status', 'availability-saved');
    }

    public function destroyException(Request $request, TeacherAvailabilityException $exception)
    {
        abort_unless($this->capabilities->canManageAvailability($request->user()), 403);
        abort_unless($exception->teacher_id === $request->user()->id, 403);

        $exception->delete();

        return back()->with('status', 'availability-removed');
    }

    // ── Appointment actions ──────────────────────────────────────────────────

    public function confirm(Request $request, TeacherAppointment $appointment)
    {
        $this->authorizeAppointment($request, $appointment);

        $request->validate(['meeting_url' => 'nullable|url|max:500']);

        try {
            $this->scheduling->confirm($appointment, $request->user(), $request->input('meeting_url'));
        } catch (InvalidArgumentException $e) {
            return back()->withErrors(['appointment' => $e->getMessage()]);
        }

        return back()->with('status', 'appointment-confirmed');
    }

    public function reject(Request $request, TeacherAppointment $appointment)
    {
        $this->authorizeAppointment($request, $appointment);

        try {
            $this->scheduling->reject($appointment, $request->user(), $request->input('reason'));
        } catch (InvalidArgumentException $e) {
            return back()->withErrors(['appointment' => $e->getMessage()]);
        }

        return back()->with('status', 'appointment-rejected');
    }

    public function cancel(Request $request, TeacherAppointment $appointment)
    {
        $this->authorizeAppointment($request, $appointment);

        try {
            $this->scheduling->cancel($appointment, $request->user(), $request->input('reason'));
        } catch (InvalidArgumentException $e) {
            return back()->withErrors(['appointment' => $e->getMessage()]);
        }

        return back()->with('status', 'appointment-cancelled');
    }

    public function reschedule(Request $request, TeacherAppointment $appointment)
    {
        $this->authorizeAppointment($request, $appointment);

        $request->validate(['starts_at' => 'required|date|after:now']);

        $settings = TeacherBookingSetting::forTeacher($request->user()->id);
        $startsAt = CarbonImmutable::parse($request->starts_at, $settings->timezone);

        $this->scheduling->reschedule($appointment, $request->user(), $startsAt);

        return back()->with('status', 'appointment-rescheduled');
    }

    public function complete(Request $request, TeacherAppointment $appointment)
    {
        $this->authorizeAppointment($request, $appointment);

        try {
            $this->scheduling->complete($appointment, $request->user());
        } catch (InvalidArgumentException $e) {
            return back()->withErrors(['appointment' => $e->getMessage()]);
        }

        return back()->with('status', 'appointment-completed');
    }

    public function noShow(Request $request, TeacherAppointment $appointment)
    {
        $this->authorizeAppointment($request, $appointment);

        try {
            $this->scheduling->markNoShow($appointment, $request->user());
        } catch (InvalidArgumentException $e) {
            return back()->withErrors(['appointment' => $e->getMessage()]);
        }

        return back()->with('status', 'appointment-updated');
    }

    public function updateNote(Request $request, TeacherAppointment $appointment)
    {
        $this->authorizeAppointment($request, $appointment);

        $request->validate(['teacher_note' => 'nullable|string|max:2000']);

        $appointment->update(['teacher_note' => $request->teacher_note]);

        return back()->with('status', 'appointment-updated');
    }

    private function authorizeAppointment(Request $request, TeacherAppointment $appointment): void
    {
        abort_unless($this->capabilities->canAcceptAppointments($request->user()), 403);
        abort_unless($appointment->teacher_id === $request->user()->id, 403);
    }
}
