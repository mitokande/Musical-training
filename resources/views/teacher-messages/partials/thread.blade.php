{{-- Shared message thread renderer. Expects $messages and $attachmentRoute. --}}
<div class="card p-4 space-y-3 max-h-[55vh] overflow-y-auto" id="messageThread">
    @forelse($messages as $message)
        @php $mine = $message->sender_id === auth()->id(); @endphp
        <div class="flex {{ $mine ? 'justify-end' : 'justify-start' }}">
            <div class="max-w-[80%] rounded-2xl px-4 py-2.5 {{ $mine ? 'bg-primary-600 text-white' : 'bg-gray-100 text-gray-800' }}">
                <p class="text-sm whitespace-pre-line break-words">{{ $message->body }}</p>
                @foreach($message->attachments as $attachment)
                    <a href="{{ route($attachmentRoute, $attachment) }}"
                       class="mt-1.5 flex items-center gap-1.5 text-xs font-semibold underline {{ $mine ? 'text-primary-100' : 'text-primary-700' }}">
                        <i data-lucide="paperclip" class="w-3 h-3"></i> {{ $attachment->original_name }}
                    </a>
                @endforeach
                <p class="text-[10px] mt-1 {{ $mine ? 'text-primary-200' : 'text-gray-400' }}">
                    {{ $message->created_at->format('M j, H:i') }}
                </p>
            </div>
        </div>
    @empty
        <p class="text-sm text-gray-400 text-center py-6">—</p>
    @endforelse
</div>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const thread = document.getElementById('messageThread');
        if (thread) thread.scrollTop = thread.scrollHeight;
    });
</script>
