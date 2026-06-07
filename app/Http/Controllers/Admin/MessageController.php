<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Message;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    public function index(Request $request)
    {
        $messages = Message::with(['sender', 'receiver'])
            ->whereNull('parent_id')
            ->when($request->type, fn($q, $type) => $q->where('type', $type))
            ->when($request->status, fn($q, $status) => $q->where('status', $status))
            ->when($request->priority, fn($q, $priority) => $q->where('priority', $priority))
            ->when($request->search, function ($q, $search) {
                $q->where(function ($q) use ($search) {
                    $q->where('subject', 'like', "%{$search}%")
                      ->orWhere('body', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(20);

        return view('admin.messages.index', compact('messages'));
    }

    public function show(Message $message)
    {
        $message->load(['sender', 'receiver']);

        $replies = Message::with(['sender', 'receiver'])
            ->where('parent_id', $message->id)
            ->oldest()
            ->get();

        return view('admin.messages.show', compact('message', 'replies'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'receiver_id' => 'required|exists:users,id',
            'subject'     => 'required|string|max:255',
            'body'        => 'required|string',
            'type'        => 'required|string|in:notification,support,announcement,system',
            'priority'    => 'required|string|in:low,normal,high,urgent',
        ]);

        $validated['sender_id'] = auth()->id();
        $validated['status'] = 'sent';

        Message::create($validated);

        return redirect()->route('admin.messages.index')
            ->with('success', 'Message sent successfully.');
    }

    public function reply(Request $request, Message $message)
    {
        $validated = $request->validate([
            'body' => 'required|string',
        ]);

        Message::create([
            'sender_id'   => auth()->id(),
            'receiver_id' => $message->sender_id,
            'subject'     => 'Re: ' . $message->subject,
            'body'        => $validated['body'],
            'type'        => $message->type,
            'priority'    => $message->priority,
            'status'      => 'sent',
            'parent_id'   => $message->id,
        ]);

        return back()->with('success', 'Reply sent successfully.');
    }

    public function markRead(Message $message)
    {
        $message->update(['read_at' => now()]);

        return back()->with('success', 'Message marked as read.');
    }

    public function archive(Message $message)
    {
        $message->update(['status' => 'archived']);

        return back()->with('success', 'Message archived.');
    }
}
