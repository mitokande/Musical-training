<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\TeacherConversation;
use App\Models\TeacherConversationAttachment;
use App\Models\TeacherStudentRelationship;
use App\Models\User;
use App\Services\Teacher\TeacherCapabilityService;
use App\Services\Teacher\TeacherMessagingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;

/** Teacher-side messaging screens inside the CRM. */
class TeacherMessageController extends Controller
{
    public function __construct(
        private TeacherCapabilityService $capabilities,
        private TeacherMessagingService $messaging,
    ) {}

    public function index(Request $request)
    {
        $teacher = $request->user();

        $conversations = TeacherConversation::with(['student'])
            ->where('teacher_id', $teacher->id)
            ->orderByDesc('last_message_at')
            ->get();

        $activeStudents = TeacherStudentRelationship::with('student')
            ->active()
            ->where('teacher_id', $teacher->id)
            ->get()
            ->pluck('student')
            ->reject(fn ($s) => $conversations->pluck('student_id')->contains($s->id));

        return view('teacher.messages.index', [
            'conversations' => $conversations,
            'newRecipients' => $this->capabilities->canReplyToMessages($teacher) ? $activeStudents : collect(),
            'canReply' => $this->capabilities->canReplyToMessages($teacher),
        ]);
    }

    /** Premium teacher opens (or creates) a conversation with a student. */
    public function start(Request $request, User $student)
    {
        abort_unless($this->capabilities->canReplyToMessages($request->user()), 403);

        try {
            $conversation = $this->messaging->conversationBetween($request->user(), $student);
        } catch (InvalidArgumentException $e) {
            return redirect()->route(crm_prefix().'.messages.index')->withErrors(['conversation' => $e->getMessage()]);
        }

        return redirect()->route(crm_prefix().'.messages.show', $conversation);
    }

    public function show(Request $request, TeacherConversation $conversation)
    {
        abort_unless($conversation->teacher_id === $request->user()->id, 403);

        $this->messaging->markRead($conversation, $request->user());

        return view('teacher.messages.show', [
            'conversation' => $conversation->load('student'),
            'messages' => $conversation->messages()->with(['sender', 'attachments'])->orderBy('created_at')->get(),
            'canReply' => $this->capabilities->canReplyToMessages($request->user()),
        ]);
    }

    public function store(Request $request, TeacherConversation $conversation)
    {
        abort_unless($conversation->teacher_id === $request->user()->id, 403);

        $request->validate([
            'body' => 'required|string|max:5000',
            'attachments' => 'nullable|array|max:3',
            'attachments.*' => 'file|max:10240',
        ]);

        try {
            $this->messaging->send(
                $conversation,
                $request->user(),
                $request->body,
                $request->file('attachments', []),
            );
        } catch (InvalidArgumentException $e) {
            return back()->withErrors(['body' => $e->getMessage()])->withInput();
        }

        return redirect()->route(crm_prefix().'.messages.show', $conversation);
    }

    /** Authorized attachment download for either participant. */
    public function downloadAttachment(Request $request, TeacherConversationAttachment $attachment)
    {
        $conversation = $attachment->message->conversation;

        abort_unless($conversation->involves($request->user()) || $request->user()->isAdmin(), 403);
        abort_unless(Storage::disk($attachment->disk)->exists($attachment->path), 404);

        return Storage::disk($attachment->disk)->download($attachment->path, $attachment->original_name);
    }
}
