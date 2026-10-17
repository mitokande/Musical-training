<?php

namespace App\Services\Derslik;

use App\Models\LiveLesson;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

/**
 * Starting and closing ad-hoc Derslik lessons. The one place that talks to
 * DerslikClient on behalf of a LiveLesson — controllers and the hourly cleanup
 * both come through here.
 */
class LiveLessonService
{
    /** Open lessons one teacher may hold at once — each is a live Derslik room. */
    public const MAX_OPEN_PER_TEACHER = 5;

    public function __construct(private DerslikClient $derslik) {}

    /**
     * Create the Derslik room and the Harmoniva record pointing at it.
     *
     * @throws ValidationException when the teacher already holds too many open lessons
     * @throws \Throwable when Derslik is unreachable or refuses the request
     */
    public function start(User $teacher, ?string $title = null): LiveLesson
    {
        if (LiveLesson::open()->where('teacher_id', $teacher->id)->count() >= self::MAX_OPEN_PER_TEACHER) {
            throw ValidationException::withMessages([
                'title' => __('teacher.live_lessons.too_many', ['count' => self::MAX_OPEN_PER_TEACHER]),
            ]);
        }

        $title = trim((string) $title) ?: __('teacher.live_lessons.default_title', ['name' => $teacher->fullName()]);
        $title = mb_substr($title, 0, 80);

        $room = $this->derslik->createRoom($title, [
            'source' => 'harmoniva',
            'harmonivaTeacherId' => (string) $teacher->id,
        ]);

        return LiveLesson::create([
            'teacher_id' => $teacher->id,
            'title' => $title,
            'derslik_room_id' => $room['id'],
            'teacher_url' => $room['teacherUrl'],
            'student_url' => $room['studentUrl'],
            'share_token' => LiveLesson::newShareToken(),
        ]);
    }

    /**
     * Close the lesson: everyone is disconnected and the Derslik room deleted.
     * The Harmoniva links close even if Derslik cannot be reached — the
     * student only ever had the harmoniva.app link, which now shows "ended".
     */
    public function end(LiveLesson $lesson): void
    {
        if ($lesson->ended_at === null) {
            $lesson->forceFill(['ended_at' => now()])->save();
        }

        try {
            $this->derslik->endRoom($lesson->derslik_room_id);
            $this->derslik->deleteRoom($lesson->derslik_room_id);
        } catch (\Throwable $e) {
            Log::warning('Could not delete a Derslik room for an ended live lesson.', [
                'live_lesson_id' => $lesson->id,
                'exception' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Live status for the CRM list: whether a lesson is running and who is in
     * it. Best-effort — an unreachable Derslik yields null, never an error page.
     *
     * @return array{active: bool, participants: int, teacherPresent: bool}|null
     */
    public function liveStatus(LiveLesson $lesson): ?array
    {
        try {
            $live = $this->derslik->room($lesson->derslik_room_id)['live'] ?? null;
        } catch (\Throwable) {
            return null;
        }

        return is_array($live) ? [
            'active' => (bool) ($live['active'] ?? false),
            'participants' => (int) ($live['participants'] ?? 0),
            'teacherPresent' => (bool) ($live['teacherPresent'] ?? false),
        ] : null;
    }
}
