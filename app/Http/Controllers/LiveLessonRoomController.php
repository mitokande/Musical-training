<?php

namespace App\Http\Controllers;

use App\Models\LiveLesson;
use App\Services\Derslik\DerslikClient;
use App\Services\Teacher\TeacherCapabilityService;
use Illuminate\Http\Request;

/**
 * The Harmoniva pages a Derslik lesson is embedded in.
 *
 * room() — the teacher's side, signed in and owner only; embeds Derslik's
 * teacher URL (which carries the teacher key, so it never leaves this page).
 *
 * join() — the link teachers share (/live/{token}). Public on purpose: the
 * student may not have a Harmoniva account, and Derslik's waiting room means
 * nobody gets into the lesson until the teacher admits them. Signed-in
 * visitors get their name filled in.
 */
class LiveLessonRoomController extends Controller
{
    public function __construct(
        private DerslikClient $derslik,
        private TeacherCapabilityService $capabilities,
    ) {}

    public function room(Request $request, LiveLesson $liveLesson)
    {
        $user = $request->user();
        abort_unless($liveLesson->teacher_id === $user->id, 404);
        abort_unless($this->capabilities->canHostLiveLessons($user), 403);

        if (! $liveLesson->isOpen()) {
            return redirect()->route($user->crmRouteName('live-lessons.index'))
                ->withErrors(['live_lesson' => __('teacher.live_lessons.ended_notice')]);
        }

        return $this->render($liveLesson, true, $liveLesson->teacher_url, $user->fullName(), [
            'back' => route($user->crmRouteName('dashboard')),
            'end' => route($user->crmRouteName('live-lessons.end'), $liveLesson),
        ]);
    }

    public function join(Request $request, string $token)
    {
        $lesson = LiveLesson::with('teacher')->where('share_token', $token)->firstOrFail();
        $user = $request->user();

        // The teacher opening their own shared link belongs on the teacher side.
        if ($user && $user->id === $lesson->teacher_id && $lesson->isOpen()) {
            return redirect()->to($lesson->roomUrl());
        }

        if (! $lesson->isOpen()) {
            return response()->view('lessons.live-room-ended', ['lesson' => $lesson], 410);
        }

        return $this->render($lesson, false, $lesson->student_url, $user?->fullName(), [
            'back' => $user ? route('dashboard') : url('/'),
        ]);
    }

    private function render(LiveLesson $lesson, bool $isTeacher, string $derslikUrl, ?string $name, array $links)
    {
        $query = ['embed' => '1'];
        if (filled($name)) {
            $query['name'] = $name;
        }
        $src = $derslikUrl.(str_contains($derslikUrl, '?') ? '&' : '?').http_build_query($query, '', '&', PHP_QUERY_RFC3986);

        return response()
            ->view('lessons.live-room', [
                'lesson' => $lesson,
                'isTeacher' => $isTeacher,
                'iframeSrc' => $src,
                'derslikOrigin' => $this->derslik->origin(),
                'shareUrl' => $lesson->joinUrl(),
                'links' => $links,
            ])
            // The teacher page holds a credential-bearing URL; keep it out of caches and referrers.
            ->header('Cache-Control', 'no-store, private')
            ->header('Referrer-Policy', 'same-origin');
    }
}
