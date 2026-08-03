<?php

namespace App\Services\Meetings;

use App\Models\TeacherAppointment;
use App\Models\ZoomMeeting;
use App\Services\Meetings\Contracts\MeetingProvider;
use App\Services\Zoom\ZoomClient;
use App\Services\Zoom\ZoomHostAllocator;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * Hosts the lesson on a pooled Harmoniva-owned Zoom licence.
 *
 * The join URL is mirrored onto teacher_appointments.meeting_url so every
 * existing "join" link in the CRM, the student dashboard and the appointment
 * notifications keeps working untouched; the embedded Lesson Room is an
 * additional entry point, not a replacement.
 *
 * The host `start_url` Zoom returns is deliberately discarded — it is a
 * long-lived host credential. Teachers start the meeting with a fresh ZAK
 * fetched at join time instead.
 */
class ZoomMeetingProvider implements MeetingProvider
{
    public function __construct(
        private ZoomClient $zoom,
        private ZoomHostAllocator $allocator,
    ) {}

    public function key(): string
    {
        return 'zoom';
    }

    /** Whether a lesson can actually be hosted on Zoom right now. */
    public function available(): bool
    {
        return (bool) config('zoom.enabled') && $this->zoom->configured();
    }

    public function create(TeacherAppointment $appointment): void
    {
        $host = $this->allocator->allocate($appointment->starts_at, $appointment->ends_at, $appointment->id);

        if (! $host) {
            throw new HostPoolExhausted;
        }

        $created = $this->zoom->createMeeting($host->zoom_user_id, [
            'topic' => $this->topicFor($appointment),
            'start_time' => $appointment->starts_at->utc()->format('Y-m-d\TH:i:s\Z'),
            'duration' => max(1, $appointment->starts_at->diffInMinutes($appointment->ends_at)),
            'timezone' => 'UTC',
            'agenda' => $appointment->topic,
        ]);

        if ($created['id'] === '' || $created['join_url'] === '') {
            throw new RuntimeException('Zoom returned an incomplete meeting.');
        }

        $meeting = ZoomMeeting::updateOrCreate(
            ['appointment_id' => $appointment->id],
            [
                'zoom_host_id' => $host->id,
                'zoom_meeting_id' => $created['id'],
                'zoom_meeting_uuid' => $created['uuid'],
                'join_url' => $created['join_url'],
                'passcode' => $created['password'],
                'starts_at' => $appointment->starts_at,
                'ends_at' => $appointment->ends_at,
                'status' => ZoomMeeting::STATUS_ACTIVE,
            ],
        );

        // Refresh the cached relation: callers routinely read zoomMeeting on
        // this same instance before creating (to decide create-vs-move) and
        // again afterwards to cancel it.
        $appointment->setRelation('zoomMeeting', $meeting);

        $appointment->meeting_provider = $this->key();
        $appointment->meeting_url = $created['join_url'];
    }

    /**
     * Move the meeting to the appointment's new time. The original host may now
     * be busy at that time, so re-run allocation: if a different licence is
     * free we rebuild the meeting there, and if none is we tear the Zoom
     * meeting down and let the caller fall back to a manual link.
     */
    public function update(TeacherAppointment $appointment): void
    {
        $meeting = $appointment->zoomMeeting;

        if (! $meeting || ! $meeting->isActive()) {
            $this->create($appointment);

            return;
        }

        $host = $this->allocator->allocate($appointment->starts_at, $appointment->ends_at, $appointment->id);

        if (! $host) {
            $this->cancel($appointment);

            throw new HostPoolExhausted;
        }

        if ($host->id !== $meeting->zoom_host_id) {
            $this->cancel($appointment);
            $this->create($appointment);

            return;
        }

        $this->zoom->updateMeeting($meeting->zoom_meeting_id, [
            'topic' => $this->topicFor($appointment),
            'start_time' => $appointment->starts_at->utc()->format('Y-m-d\TH:i:s\Z'),
            'duration' => max(1, $appointment->starts_at->diffInMinutes($appointment->ends_at)),
            'timezone' => 'UTC',
        ]);

        $meeting->update([
            'starts_at' => $appointment->starts_at,
            'ends_at' => $appointment->ends_at,
        ]);

        $appointment->meeting_url = $meeting->join_url;
    }

    public function cancel(TeacherAppointment $appointment): void
    {
        $meeting = $appointment->zoomMeeting;

        if (! $meeting || ! $meeting->isActive()) {
            return;
        }

        // Free the licence even if Zoom is unreachable — a stale meeting at
        // Zoom's end is far less harmful than a host slot we can never reuse.
        try {
            $this->zoom->deleteMeeting($meeting->zoom_meeting_id);
        } catch (\Throwable $e) {
            Log::warning('Zoom meeting delete failed; releasing the host anyway.', [
                'appointment_id' => $appointment->id,
                'exception' => $e->getMessage(),
            ]);
        }

        $meeting->update(['status' => ZoomMeeting::STATUS_CANCELLED]);

        $appointment->meeting_url = null;
        $appointment->setRelation('zoomMeeting', null);
    }

    private function topicFor(TeacherAppointment $appointment): string
    {
        $student = $appointment->student?->name ?: __('teacher.lesson_room.student');

        return mb_substr(__('teacher.lesson_room.meeting_topic', ['student' => $student]), 0, 200);
    }
}
