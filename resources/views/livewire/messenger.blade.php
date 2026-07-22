<div class="max-w-5xl mx-auto">
    <div class="card overflow-hidden" style="height:70vh;">
        <div class="flex h-full">

            {{-- Conversation list --}}
            <div class="w-full sm:w-72 border-r border-gray-100 flex flex-col {{ $activeUser ? 'hidden sm:flex' : '' }}">
                <div class="px-4 py-3 border-b border-gray-100">
                    <h2 class="font-bold text-gray-900">{{ __('app.messenger.title') }}</h2>
                </div>
                <div class="flex-1 overflow-y-auto">
                    @forelse($conversations as $conv)
                        @php $isTeacher = ($conv['source'] ?? 'general') === 'teacher'; @endphp
                        @if($isTeacher)
                            <a href="{{ $conv['url'] }}"
                               wire:key="conv-teacher-{{ $conv['user']->id }}"
                               class="w-full flex items-center gap-3 px-4 py-3 text-left hover:bg-gray-50 transition-colors border-b border-gray-50">
                        @else
                            <button wire:click="selectConversation({{ $conv['user']->id }})"
                                    wire:key="conv-general-{{ $conv['user']->id }}"
                                    class="w-full flex items-center gap-3 px-4 py-3 text-left hover:bg-gray-50 transition-colors border-b border-gray-50
                                           {{ $activeUser && $activeUser->id === $conv['user']->id ? 'bg-purple-50' : '' }}">
                        @endif
                            @if($conv['user']->hasAvatar())
                                <img src="{{ $conv['user']->avatar }}" alt="" class="w-10 h-10 rounded-full object-cover shrink-0">
                            @else
                                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-purple-500 to-pink-500 flex items-center justify-center text-white font-semibold shrink-0">
                                    {{ substr($conv['user']->name ?? 'U', 0, 1) }}
                                </div>
                            @endif
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between gap-2">
                                    <span class="text-sm font-semibold text-gray-900 truncate">{{ $conv['user']->fullName() }}</span>
                                    @if($conv['unread'] > 0)
                                        <span class="bg-purple-600 text-white text-xs font-bold rounded-full px-1.5 min-w-[18px] text-center">{{ $conv['unread'] }}</span>
                                    @endif
                                </div>
                                <div class="flex items-center gap-1.5">
                                    @if($isTeacher)
                                        <span class="inline-flex items-center gap-0.5 text-[10px] font-semibold text-purple-600 bg-purple-50 rounded px-1 py-0.5 shrink-0">
                                            <i data-lucide="graduation-cap" class="w-3 h-3"></i>{{ __('app.messenger.teacher_badge') }}
                                        </span>
                                    @endif
                                    <p class="text-xs text-gray-400 truncate">{{ $conv['last_body'] }}</p>
                                </div>
                            </div>
                        @if($isTeacher)</a>@else</button>@endif
                    @empty
                        <p class="p-4 text-sm text-gray-400">{{ __('app.messenger.no_conversations') }}</p>
                    @endforelse
                </div>
            </div>

            {{-- Thread --}}
            <div class="flex-1 flex flex-col {{ $activeUser ? '' : 'hidden sm:flex' }}">
                @if($activeUser)
                    <div class="px-4 py-3 border-b border-gray-100 flex items-center gap-3">
                        <button wire:click="$set('activeUserId', null)" class="sm:hidden text-gray-500">
                            <i data-lucide="arrow-left" class="w-5 h-5"></i>
                        </button>
                        <a href="{{ url('/u/'.$activeUser->username) }}" class="flex items-center gap-3">
                            @if($activeUser->hasAvatar())
                                <img src="{{ $activeUser->avatar }}" alt="" class="w-9 h-9 rounded-full object-cover">
                            @else
                                <div class="w-9 h-9 rounded-full bg-gradient-to-br from-purple-500 to-pink-500 flex items-center justify-center text-white font-semibold">
                                    {{ substr($activeUser->name ?? 'U', 0, 1) }}
                                </div>
                            @endif
                            <span class="font-semibold text-gray-900">{{ $activeUser->fullName() }}</span>
                        </a>
                    </div>

                    <div class="flex-1 overflow-y-auto p-4 space-y-2 bg-gray-50">
                        @foreach($thread as $msg)
                            @php $mine = $msg->sender_id === auth()->id(); @endphp
                            <div class="flex {{ $mine ? 'justify-end' : 'justify-start' }}" wire:key="msg-{{ $msg->id }}">
                                <div class="max-w-[75%] rounded-2xl px-4 py-2 text-sm
                                            {{ $mine ? 'bg-purple-600 text-white rounded-br-sm' : 'bg-white border border-gray-100 text-gray-800 rounded-bl-sm' }}">
                                    <p class="whitespace-pre-line break-words">{{ $msg->body }}</p>
                                    <p class="text-[10px] mt-1 {{ $mine ? 'text-purple-200' : 'text-gray-400' }}">{{ $msg->created_at->format('H:i') }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <form wire:submit="sendMessage" class="p-3 border-t border-gray-100 flex items-center gap-2">
                        <input type="text" wire:model="body" placeholder="{{ __('app.messenger.type_message') }}"
                               class="flex-1 rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500">
                        <button type="submit" wire:loading.attr="disabled"
                                class="btn-primary text-white p-2.5 rounded-xl shrink-0 disabled:opacity-50">
                            <i data-lucide="send" class="w-5 h-5"></i>
                        </button>
                    </form>
                @else
                    <div class="flex-1 flex flex-col items-center justify-center text-gray-300">
                        <i data-lucide="message-circle" class="w-12 h-12 mb-3"></i>
                        <p class="text-sm">{{ __('app.messenger.select_conversation') }}</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
