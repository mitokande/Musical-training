<?php

namespace App\Services\Meetings\Contracts;

use App\Models\TeacherAppointment;

/**
 * A meeting provider owns the video call behind an appointment. The scheduling
 * service is provider-agnostic and talks only to this contract, so the manual
 * paste-a-link flow, the pooled Zoom flow, and a future per-teacher BYO-Zoom
 * flow are interchangeable.
 *
 * Implementations must be idempotent and forgiving: a lesson may be cancelled
 * twice, or confirmed when the provider is unreachable. Never let a provider
 * failure block an appointment state change.
 */
interface MeetingProvider
{
    /** Stable identifier stored on teacher_appointments.meeting_provider. */
    public function key(): string;

    /** Provision the meeting for a newly confirmed appointment. */
    public function create(TeacherAppointment $appointment): void;

    /** Move an existing meeting to the appointment's current start/end time. */
    public function update(TeacherAppointment $appointment): void;

    /** Tear the meeting down and release any resource it holds. */
    public function cancel(TeacherAppointment $appointment): void;
}
