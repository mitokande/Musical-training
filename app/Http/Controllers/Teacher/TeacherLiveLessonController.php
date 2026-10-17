<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\LiveLesson;
use App\Services\Derslik\DerslikClient;
use App\Services\Derslik\LiveLessonService;
use App\Services\Teacher\TeacherCapabilityService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * Ad-hoc live lessons on Derslik, started from the CRM. Registered in
 * routes/crm.php, so it runs under both /teacher and /school. A lesson always
 * belongs to the account that started it.
 */
class TeacherLiveLessonController extends Controller
{
    public function __construct(
        private TeacherCapabilityService $capabilities,
        private DerslikClient $derslik,
        private LiveLessonService $lessons,
    ) {}

    public function index(Request $request): View
    {
        $this->authorizeHosting($request);
        $user = $request->user();

        $open = LiveLesson::open()->where('teacher_id', $user->id)->latest()->get();

        return view('teacher.live-lessons.index', [
            'openLessons' => $open,
            'liveStatus' => $open->mapWithKeys(fn (LiveLesson $l) => [$l->id => $this->lessons->liveStatus($l)]),
            'pastLessons' => LiveLesson::where('teacher_id', $user->id)
                ->where(fn ($q) => $q->whereNotNull('ended_at')->orWhere('created_at', '<', now()->subHours(LiveLesson::lessonHours())))
                ->latest()->limit(10)->get(),
            'maxOpen' => LiveLessonService::MAX_OPEN_PER_TEACHER,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeHosting($request);

        $data = $request->validate(['title' => ['nullable', 'string', 'max:80']]);

        try {
            $lesson = $this->lessons->start($request->user(), $data['title'] ?? null);
        } catch (ValidationException $e) {
            throw $e; // too many open lessons: shown under the title field
        } catch (\Throwable $e) {
            Log::error('Could not create a Derslik room for a live lesson.', [
                'teacher_id' => $request->user()->id,
                'exception' => $e->getMessage(),
            ]);

            return back()->withInput()->withErrors(['title' => __('teacher.live_lessons.unavailable')]);
        }

        // Straight into the room: the share panel there carries the student link.
        return redirect()->to($lesson->roomUrl());
    }

    public function end(Request $request, LiveLesson $liveLesson): RedirectResponse
    {
        abort_unless($liveLesson->teacher_id === $request->user()->id, 404);

        $this->lessons->end($liveLesson);

        return redirect()->route($request->user()->crmRouteName('dashboard'))->with('status', 'live-lesson-ended');
    }

    private function authorizeHosting(Request $request): void
    {
        abort_unless($this->derslik->configured(), 404);
        abort_unless($this->capabilities->canHostLiveLessons($request->user()), 403);
    }
}
