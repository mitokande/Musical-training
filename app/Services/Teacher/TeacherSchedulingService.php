<?php

namespace App\Services\Teacher;

use App\Models\TeacherAppointment;
use App\Models\TeacherAppointmentActivity;
use App\Models\TeacherAvailability;
use App\Models\TeacherAvailabilityException;
use App\Models\TeacherBookingSetting;
use App\Models\User;
use App\Notifications\Teacher\AppointmentRequested;
use App\Notifications\Teacher\AppointmentStatusChanged;
use Carbon\CarbonImmutable;
use InvalidArgumentException;

/**
 * Availability computation and the appointment state machine. Every status
 * transition goes through transition() so the activity log and notifications
 * stay consistent. Meeting links are plain manual URLs in Phase 1; the
 * meeting_provider column and feature flags (google_calendar_sync_enabled,
 * google_meet_enabled — both off) reserve the seam for Phase 2 providers.
 */
class TeacherSchedulingService
{
    /** Allowed status transitions: from => [to, ...] */
    private const TRANSITIONS = [
        TeacherAppointment::STATUS_PENDING => [
            TeacherAppointment::STATUS_CONFIRMED,
            TeacherAppointment::STATUS_REJECTED,
            TeacherAppointment::STATUS_CANCELLED_BY_STUDENT,
            TeacherAppointment::STATUS_CANCELLED_BY_TEACHER,
        ],
        TeacherAppointment::STATUS_CONFIRMED => [
            TeacherAppointment::STATUS_CANCELLED_BY_TEACHER,
            TeacherAppointment::STATUS_CANCELLED_BY_STUDENT,
            TeacherAppointment::STATUS_RESCHEDULE_REQUESTED,
            TeacherAppointment::STATUS_COMPLETED,
            TeacherAppointment::STATUS_NO_SHOW,
        ],
        TeacherAppointment::STATUS_RESCHEDULE_REQUESTED => [
            TeacherAppointment::STATUS_CONFIRMED,
            TeacherAppointment::STATUS_CANCELLED_BY_TEACHER,
            TeacherAppointment::STATUS_CANCELLED_BY_STUDENT,
        ],
    ];

    /**
     * Bookable slots for a teacher on a given date (teacher timezone).
     *
     * @return array<array{starts_at: CarbonImmutable, ends_at: CarbonImmutable}>
     */
    public function slotsFor(User $teacher, CarbonImmutable $date): array
    {
        $settings = TeacherBookingSetting::forTeacher($teacher->id);

        if (! $settings->booking_enabled) {
            return [];
        }

        $tz = $settings->timezone ?: config('app.timezone');
        $date = $date->setTimezone($tz)->startOfDay();

        if ($date->isBefore(CarbonImmutable::now($tz)->startOfDay())
            || $date->isAfter(CarbonImmutable::now($tz)->addDays($settings->advance_booking_days))) {
            return [];
        }

        // Whole-day block wins.
        $exceptions = TeacherAvailabilityException::where('teacher_id', $teacher->id)
            ->whereDate('date', $date->toDateString())
            ->get();

        if ($exceptions->contains(fn ($e) => $e->is_blocked && $e->start_time === null)) {
            return [];
        }

        $windows = TeacherAvailability::where('teacher_id', $teacher->id)
            ->where('weekday', $date->dayOfWeek)
            ->get()
            ->map(fn ($a) => [
                'start' => $date->setTimeFromTimeString($a->start_time),
                'end' => $date->setTimeFromTimeString($a->end_time),
            ]);

        // Extra availability windows for this specific date.
        foreach ($exceptions->where('is_blocked', false) as $extra) {
            if ($extra->start_time && $extra->end_time) {
                $windows->push([
                    'start' => $date->setTimeFromTimeString($extra->start_time),
                    'end' => $date->setTimeFromTimeString($extra->end_time),
                ]);
            }
        }

        $blockedRanges = $exceptions
            ->where('is_blocked', true)
            ->filter(fn ($e) => $e->start_time && $e->end_time)
            ->map(fn ($e) => [
                'start' => $date->setTimeFromTimeString($e->start_time),
                'end' => $date->setTimeFromTimeString($e->end_time),
            ]);

        $busy = TeacherAppointment::blocking()
            ->where('teacher_id', $teacher->id)
            ->whereBetween('starts_at', [$date->startOfDay()->utc(), $date->endOfDay()->utc()])
            ->get();

        $step = $settings->lesson_duration_minutes + $settings->buffer_minutes;
        $minStart = CarbonImmutable::now($tz)->addHours($settings->min_notice_hours);
        $slots = [];

        foreach ($windows as $window) {
            $cursor = $window['start'];
            while ($cursor->addMinutes($settings->lesson_duration_minutes)->lessThanOrEqualTo($window['end'])) {
                $slotStart = $cursor;
                $slotEnd = $cursor->addMinutes($settings->lesson_duration_minutes);

                $conflict = $slotStart->isBefore($minStart)
                    || $blockedRanges->contains(fn ($r) => $slotStart->isBefore($r['end']) && $slotEnd->isAfter($r['start']))
                    || $busy->contains(fn ($a) => $slotStart->utc()->isBefore($a->ends_at) && $slotEnd->utc()->isAfter($a->starts_at));

                if (! $conflict) {
                    $slots[] = ['starts_at' => $slotStart, 'ends_at' => $slotEnd];
                }

                $cursor = $cursor->addMinutes($step);
            }
        }

        usort($slots, fn ($a, $b) => $a['starts_at'] <=> $b['starts_at']);

        return $slots;
    }

    /**
     * A grid of consecutive upcoming days (from today) each with up to $maxSlots
     * bookable slots — empty for days without availability. Powers the compact
     * weekly widget on the public profile, whose day-navigation always works
     * even across unavailable days. slotsFor() is only computed for weekdays that
     * actually have availability windows, so idle days stay cheap.
     *
     * @return array<array{date: string, weekday: string, day: int, month: string, slots: array<array{value: string, label: string}>}>
     */
    public function bookingDaysGrid(User $teacher, int $maxDays = 14, int $maxSlots = 6): array
    {
        $settings = TeacherBookingSetting::forTeacher($teacher->id);
        $tz = $settings->timezone ?: config('app.timezone');
        $enabled = (bool) $settings->booking_enabled;

        $activeWeekdays = $enabled
            ? TeacherAvailability::where('teacher_id', $teacher->id)->pluck('weekday')->unique()
            : collect();
        $hasExtraWindows = $enabled && TeacherAvailabilityException::where('teacher_id', $teacher->id)
            ->where('is_blocked', false)->whereNotNull('start_time')->exists();

        $days = [];
        $cursor = CarbonImmutable::now($tz)->startOfDay();

        for ($i = 0; $i < $maxDays; $i++) {
            $slots = [];

            if ($enabled && ($activeWeekdays->contains($cursor->dayOfWeek) || $hasExtraWindows)) {
                $slots = array_map(fn ($s) => [
                    'value' => $s['starts_at']->toIso8601String(),
                    'label' => $s['starts_at']->format('H:i'),
                ], array_slice($this->slotsFor($teacher, $cursor), 0, $maxSlots));
            }

            $days[] = [
                'date' => $cursor->toDateString(),
                'weekday' => $cursor->translatedFormat('l'),
                'day' => $cursor->day,
                'month' => $cursor->translatedFormat('M'),
                'slots' => $slots,
            ];

            $cursor = $cursor->addDay();
        }

        return $days;
    }

    /** Student requests an appointment at an offered slot. */
    public function request(User $teacher, User $student, CarbonImmutable $startsAt, ?string $topic = null): TeacherAppointment
    {
        $settings = TeacherBookingSetting::forTeacher($teacher->id);
        $tz = $settings->timezone ?: config('app.timezone');
        $startsAt = $startsAt->setTimezone($tz);

        $valid = collect($this->slotsFor($teacher, $startsAt->startOfDay()))
            ->contains(fn ($slot) => $slot['starts_at']->equalTo($startsAt));

        if (! $valid) {
            throw new InvalidArgumentException(__('teacher.appointments.error_slot_unavailable'));
        }

        $appointment = TeacherAppointment::create([
            'teacher_id' => $teacher->id,
            'student_id' => $student->id,
            'starts_at' => $startsAt->utc(),
            'ends_at' => $startsAt->addMinutes($settings->lesson_duration_minutes)->utc(),
            'status' => TeacherAppointment::STATUS_PENDING,
            'topic' => $topic,
            'timezone' => $tz,
        ]);

        $this->log($appointment, $student, 'requested', null, TeacherAppointment::STATUS_PENDING);

        $teacher->notify(new AppointmentRequested($appointment));

        return $appointment;
    }

    public function confirm(TeacherAppointment $appointment, User $actor, ?string $meetingUrl = null): void
    {
        // Approving a reschedule request moves the appointment to the new time.
        if ($appointment->status === TeacherAppointment::STATUS_RESCHEDULE_REQUESTED && $appointment->requested_starts_at) {
            $appointment->starts_at = $appointment->requested_starts_at;
            $appointment->ends_at = $appointment->requested_ends_at;
            $appointment->requested_starts_at = null;
            $appointment->requested_ends_at = null;
        }

        if ($meetingUrl !== null && $meetingUrl !== '') {
            $appointment->meeting_provider = 'manual';
            $appointment->meeting_url = $meetingUrl;
        }

        $this->transition($appointment, $actor, TeacherAppointment::STATUS_CONFIRMED, 'confirmed');
    }

    public function reject(TeacherAppointment $appointment, User $actor, ?string $reason = null): void
    {
        $this->transition($appointment, $actor, TeacherAppointment::STATUS_REJECTED, 'rejected', $reason);
    }

    public function cancel(TeacherAppointment $appointment, User $actor, ?string $reason = null): void
    {
        $status = $actor->id === $appointment->teacher_id
            ? TeacherAppointment::STATUS_CANCELLED_BY_TEACHER
            : TeacherAppointment::STATUS_CANCELLED_BY_STUDENT;

        $this->transition($appointment, $actor, $status, 'cancelled', $reason);
    }

    /** Student asks for a new time; teacher later confirms or the request is cancelled. */
    public function requestReschedule(TeacherAppointment $appointment, User $student, CarbonImmutable $newStartsAt, ?string $note = null): void
    {
        $settings = TeacherBookingSetting::forTeacher($appointment->teacher_id);

        $appointment->requested_starts_at = $newStartsAt->utc();
        $appointment->requested_ends_at = $newStartsAt->addMinutes($settings->lesson_duration_minutes)->utc();

        $this->transition($appointment, $student, TeacherAppointment::STATUS_RESCHEDULE_REQUESTED, 'reschedule_requested', $note);
    }

    /** Teacher moves the appointment directly to a new time. */
    public function reschedule(TeacherAppointment $appointment, User $teacher, CarbonImmutable $newStartsAt): void
    {
        $settings = TeacherBookingSetting::forTeacher($appointment->teacher_id);
        $from = $appointment->status;

        $appointment->starts_at = $newStartsAt->utc();
        $appointment->ends_at = $newStartsAt->addMinutes($settings->lesson_duration_minutes)->utc();
        $appointment->requested_starts_at = null;
        $appointment->requested_ends_at = null;
        $appointment->status = TeacherAppointment::STATUS_CONFIRMED;
        $appointment->save();

        $this->log($appointment, $teacher, 'rescheduled', $from, TeacherAppointment::STATUS_CONFIRMED);
        $this->notifyOther($appointment, $teacher);
    }

    public function complete(TeacherAppointment $appointment, User $actor): void
    {
        $this->transition($appointment, $actor, TeacherAppointment::STATUS_COMPLETED, 'completed');
    }

    public function markNoShow(TeacherAppointment $appointment, User $actor): void
    {
        $this->transition($appointment, $actor, TeacherAppointment::STATUS_NO_SHOW, 'no_show');
    }

    private function transition(TeacherAppointment $appointment, User $actor, string $to, string $action, ?string $notes = null): void
    {
        $from = $appointment->status;

        if (! in_array($to, self::TRANSITIONS[$from] ?? [], true)) {
            throw new InvalidArgumentException(__('teacher.appointments.error_invalid_transition'));
        }

        $appointment->status = $to;
        $appointment->save();

        $this->log($appointment, $actor, $action, $from, $to, $notes);
        $this->notifyOther($appointment, $actor);
    }

    private function log(TeacherAppointment $appointment, ?User $actor, string $action, ?string $from, ?string $to, ?string $notes = null): void
    {
        TeacherAppointmentActivity::create([
            'appointment_id' => $appointment->id,
            'actor_id' => $actor?->id,
            'action' => $action,
            'from_status' => $from,
            'to_status' => $to,
            'notes' => $notes,
        ]);
    }

    private function notifyOther(TeacherAppointment $appointment, User $actor): void
    {
        $other = $actor->id === $appointment->teacher_id ? $appointment->student : $appointment->teacher;
        $other->notify(new AppointmentStatusChanged($appointment));
    }
}
