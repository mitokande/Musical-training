<?php

namespace App\Http\Controllers;

use App\Models\TeacherAssignmentRecipient;
use App\Models\TeacherMedia;
use App\Services\Teacher\TeacherAssignmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\StreamedResponse;

/** Student-facing homework list and attempt start. */
class StudentAssignmentController extends Controller
{
    public function __construct(private TeacherAssignmentService $assignments) {}

    public function index(Request $request)
    {
        $recipients = TeacherAssignmentRecipient::with(['assignment.teacher', 'assignment.media', 'attempts'])
            ->forStudent($request->user()->id)
            ->whereHas('assignment', fn ($q) => $q->whereIn('status', ['sent', 'completed']))
            ->orderByRaw("status != 'completed' desc")
            ->orderByDesc('created_at')
            ->get();

        return view('assignments', [
            'recipients' => $recipients,
            'rewards' => $request->user()->studentRewards()->with('teacher')->whereHas('teacher')->orderByDesc('created_at')->limit(12)->get(),
        ]);
    }

    public function start(Request $request, TeacherAssignmentRecipient $recipient)
    {
        abort_unless($recipient->student_id === $request->user()->id, 403);

        try {
            $payload = $this->assignments->startAttempt($recipient);
        } catch (InvalidArgumentException $e) {
            return back()->withErrors(['assignment' => $e->getMessage()]);
        }

        // A fresh assignment run must not be hijacked by any stale practice session.
        session()->forget(['learning_path_session', 'exercise_practice_session', 'exercise_settings']);

        session([
            'teacher_assignment_session' => $payload,
            'exercise_back_url' => route('assignments.index'),
        ]);

        return redirect('/practice/'.$payload['practice_type']);
    }

    /** Download a file attached to a homework the student received. */
    public function downloadMedia(Request $request, TeacherMedia $media): StreamedResponse
    {
        abort_unless($media->canBeDownloadedBy($request->user()), 403);
        abort_unless($media->disk === 'local' && Storage::disk('local')->exists($media->path), 404);

        return Storage::disk('local')->download($media->path, $media->original_name);
    }
}
