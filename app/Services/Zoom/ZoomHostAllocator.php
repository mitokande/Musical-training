<?php

namespace App\Services\Zoom;

use App\Models\ZoomHost;
use App\Models\ZoomMeeting;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;

/**
 * Picks a free licensed Zoom host for a lesson window.
 *
 * A licence can only run one meeting at a time, so the pool size is the hard
 * ceiling on concurrent lessons. Allocation runs inside a locked transaction so
 * two teachers confirming overlapping lessons at the same instant cannot be
 * handed the same host.
 *
 * Returning null means the pool is exhausted — the caller must degrade to the
 * manual provider rather than failing the confirm.
 */
class ZoomHostAllocator
{
    /**
     * @param  int|null  $ignoreAppointmentId  Appointment whose own meeting should
     *                                         not count as a conflict (rescheduling).
     */
    public function allocate(CarbonInterface $startsAt, CarbonInterface $endsAt, ?int $ignoreAppointmentId = null): ?ZoomHost
    {
        $buffer = (int) config('zoom.host_buffer_minutes');
        $windowStart = $startsAt->copy()->subMinutes($buffer);
        $windowEnd = $endsAt->copy()->addMinutes($buffer);

        return DB::transaction(function () use ($windowStart, $windowEnd, $ignoreAppointmentId) {
            $hosts = ZoomHost::active()->orderBy('id')->lockForUpdate()->get();

            foreach ($hosts as $host) {
                $busy = ZoomMeeting::active()
                    ->where('zoom_host_id', $host->id)
                    ->when($ignoreAppointmentId, fn ($q) => $q->where('appointment_id', '!=', $ignoreAppointmentId))
                    ->where('starts_at', '<', $windowEnd)
                    ->where('ends_at', '>', $windowStart)
                    ->exists();

                if (! $busy) {
                    return $host;
                }
            }

            return null;
        });
    }
}
