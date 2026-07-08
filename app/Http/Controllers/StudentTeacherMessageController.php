<?php

namespace App\Http\Controllers;

use App\Models\TeacherConversation;
use App\Models\TeacherStudentRelationship;
use App\Models\User;
use App\Services\Teacher\TeacherMessagingService;
use Illuminate\Http\Request;
use InvalidArgumentException;

/** Student-side view of teacher-student conversations. */
class StudentTeacherMessageController extends Controller
{
    public function __construct(private TeacherMessagingService $messaging) {}

    public function index(Request $request)
    {
        $conversations = TeacherConversation::with('teacher')
            ->where('student_id', $request->user()->id)
            ->orderByDesc('last_message_at')
            ->get();

        $activeTeachers = TeacherStudentRelationship::with('teacher')
            ->active()
            ->where('student_id', $request->user()->id)
            ->get()
            ->pluck('teacher')
            ->reject(fn ($t) => $conversations->pluck('teacher_id')->contains($t->id));

        return view('teacher-messages.index', [
            'conversations' => $conversations,
            'newRecipients' => $activeTeachers,
        ]);
    }

    /** Student opens (or creates) a conversation with an active teacher. */
    public function start(Request $request, User $teacher)
    {
        try {
            $conversation = $this->messaging->conversationBetween($teacher, $request->user());
        } catch (InvalidArgumentException $e) {
            return redirect()->route('teacher-messages.index')->withErrors(['conversation' => $e->getMessage()]);
        }

        return redirect()->route('teacher-messages.show', $conversation);
    }

    public function show(Request $request, TeacherConversation $conversation)
    {
        abort_unless($conversation->student_id === $request->user()->id, 403);

        $this->messaging->markRead($conversation, $request->user());

        return view('teacher-messages.show', [
            'conversation' => $conversation->load('teacher'),
            'messages' => $conversation->messages()->with(['sender', 'attachments'])->orderBy('created_at')->get(),
        ]);
    }

    public function store(Request $request, TeacherConversation $conversation)
    {
        abort_unless($conversation->student_id === $request->user()->id, 403);

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

        return redirect()->route('teacher-messages.show', $conversation);
    }
}
