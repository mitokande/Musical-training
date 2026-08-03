<?php

namespace App\Services\Meetings;

use App\Models\TeacherAppointment;
use App\Services\Meetings\Contracts\MeetingProvider;

/**
 * The original behaviour: the teacher pastes their own meeting URL when
 * confirming, and Harmoniva just stores it. Nothing to provision, nothing to
 * tear down. Also the fallback whenever Zoom is disabled or the host pool is
 * exhausted, so a lesson can always be confirmed.
 */
class ManualMeetingProvider implements MeetingProvider
{
    public function key(): string
    {
        return 'manual';
    }

    public function create(TeacherAppointment $appointment): void
    {
        $appointment->meeting_provider = $this->key();
    }

    public function update(TeacherAppointment $appointment): void
    {
        // The teacher's own link stays valid across a reschedule.
    }

    public function cancel(TeacherAppointment $appointment): void
    {
        // Nothing is held on our side.
    }
}
