<?php

namespace App\Livewire;

use App\Models\Message;
use App\Models\TeacherConversation;
use App\Models\User;
use Livewire\Component;

class Messenger extends Component
{
    public ?int $activeUserId = null;

    public string $body = '';

    public function mount(?string $to = null): void
    {
        if ($to) {
            $target = User::where('username', $to)->orWhere('id', $to)->first();
            if ($target && $target->id !== auth()->id()) {
                $this->activeUserId = $target->id;
                $this->markRead($target->id);
            }
        }
    }

    public function selectConversation(int $userId): void
    {
        $this->activeUserId = $userId;
        $this->markRead($userId);
    }

    public function sendMessage(): void
    {
        $this->validate([
            'body' => 'required|string|max:2000',
            'activeUserId' => 'required|integer',
        ]);

        $recipient = User::find($this->activeUserId);
        if (! $recipient || $recipient->id === auth()->id()) {
            return;
        }

        Message::create([
            'sender_id' => auth()->id(),
            'receiver_id' => $recipient->id,
            'body' => $this->body,
            'type' => 'message',
            'status' => 'unread',
        ]);

        $this->body = '';
    }

    protected function markRead(int $otherUserId): void
    {
        Message::where('type', 'message')
            ->where('sender_id', $otherUserId)
            ->where('receiver_id', auth()->id())
            ->where('status', 'unread')
            ->update(['status' => 'read', 'read_at' => now()]);
    }

    public function render()
    {
        $userId = auth()->id();

        $messages = Message::where('type', 'message')
            ->where(function ($q) use ($userId) {
                $q->where('sender_id', $userId)->orWhere('receiver_id', $userId);
            })
            ->orderByDesc('created_at')
            ->get();

        $otherIds = $messages
            ->map(fn ($m) => $m->sender_id === $userId ? $m->receiver_id : $m->sender_id)
            ->unique()
            ->values();

        $users = User::whereIn('id', $otherIds)->get()->keyBy('id');

        // Teacher-student conversations (support file attachments). These are
        // merged into the same inbox so a student never misses a teacher's file
        // just because it lives in a separate thread. Teacher threads open their
        // dedicated view (teacher-messages.show) which renders attachments.
        $teacherConversations = TeacherConversation::with('teacher')
            ->where('student_id', $userId)
            ->orderByDesc('last_message_at')
            ->get()
            ->map(function ($conv) use ($userId) {
                $last = $conv->messages()->latest()->first();

                return [
                    'source' => 'teacher',
                    'url' => route('teacher-messages.show', $conv),
                    'user' => $conv->teacher,
                    'last_body' => $last?->body ?? '',
                    'last_at' => $conv->last_message_at ?? $conv->created_at,
                    'unread' => $conv->messages()
                        ->whereNull('read_at')
                        ->where('sender_id', '!=', $userId)
                        ->count(),
                ];
            })
            ->filter(fn ($c) => $c['user'] !== null);

        $teacherUserIds = $teacherConversations->pluck('user.id')->all();

        $generalConversations = $messages
            ->groupBy(fn ($m) => $m->sender_id === $userId ? $m->receiver_id : $m->sender_id)
            ->map(function ($msgs, $otherId) use ($userId, $users) {
                $last = $msgs->first();

                return [
                    'source' => 'general',
                    'url' => null,
                    'user' => $users->get($otherId),
                    'last_body' => $last->body,
                    'last_at' => $last->created_at,
                    'unread' => $msgs->where('receiver_id', $userId)->where('status', 'unread')->count(),
                ];
            })
            ->filter(fn ($c) => $c['user'] !== null)
            // A teacher thread already covers this pair — avoid a duplicate row.
            ->reject(fn ($c) => in_array($c['user']->id, $teacherUserIds, true))
            ->values();

        $conversations = $teacherConversations
            ->concat($generalConversations)
            ->sortByDesc('last_at')
            ->values();

        $thread = collect();
        $activeUser = null;

        if ($this->activeUserId) {
            $activeUser = $users->get($this->activeUserId) ?? User::find($this->activeUserId);

            $thread = Message::where('type', 'message')
                ->where(function ($q) use ($userId) {
                    // restrict to the conversation pair (me ⇄ active user)
                    $q->where(function ($q2) use ($userId) {
                        $q2->where('sender_id', $userId)->where('receiver_id', $this->activeUserId);
                    })->orWhere(function ($q2) use ($userId) {
                        $q2->where('sender_id', $this->activeUserId)->where('receiver_id', $userId);
                    });
                })
                ->orderBy('created_at')
                ->get();
        }

        return view('livewire.messenger', [
            'conversations' => $conversations,
            'thread' => $thread,
            'activeUser' => $activeUser,
        ]);
    }
}
