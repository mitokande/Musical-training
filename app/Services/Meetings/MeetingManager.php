<?php

namespace App\Services\Meetings;

use App\Models\TeacherAppointment;
use App\Services\Meetings\Contracts\MeetingProvider;

/**
 * Resolves the meeting provider for an appointment.
 *
 * This is the single place that decides where a lesson is hosted. Per-teacher
 * BYO-Zoom becomes a third branch here (checking the teacher's own connected
 * credentials before falling through to the shared pool) without any change to
 * the schema, the scheduling service, or the Lesson Room.
 */
class MeetingManager
{
    public function __construct(
        private ZoomMeetingProvider $zoom,
        private ManualMeetingProvider $manual,
    ) {}

    /**
     * @param  bool  $preferManual  The teacher supplied their own link, which
     *                              always wins over an auto-provisioned meeting.
     */
    public function for(TeacherAppointment $appointment, bool $preferManual = false): MeetingProvider
    {
        if ($preferManual || ! $this->zoom->available()) {
            return $this->manual;
        }

        return $this->zoom;
    }

    /**
     * The provider that currently owns this appointment's meeting, for updates
     * and teardown — derived from what was stored, not from current config, so
     * turning Zoom off still lets existing Zoom meetings be cancelled cleanly.
     */
    public function existing(TeacherAppointment $appointment): MeetingProvider
    {
        return $appointment->meeting_provider === $this->zoom->key() ? $this->zoom : $this->manual;
    }
}
