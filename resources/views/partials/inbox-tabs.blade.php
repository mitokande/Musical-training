{{-- Shared Notifications / Messages tab band --}}
{{-- Usage: @include('partials.inbox-tabs', ['active' => 'notifications'|'messages']) --}}
@php
    $inboxActive = $active ?? 'notifications';
    $unreadMessages = 0;
    $unreadNotifications = 0;
    if (Auth::check()) {
        $unreadMessages = \App\Models\Message::where('receiver_id', Auth::id())->where('type', 'message')->unread()->count()
            + app(\App\Services\Teacher\TeacherMessagingService::class)->unreadTotalFor(Auth::user());
        $unreadNotifications = Auth::user()->unreadNotifications()->count();
    }
    $inboxTabs = [
        ['key' => 'notifications', 'label' => __('app.nav.notifications'), 'icon' => 'bell', 'href' => route('notifications.index'), 'badge' => $unreadNotifications],
        ['key' => 'messages', 'label' => __('app.nav.messages'), 'icon' => 'message-circle', 'href' => url('/messages'), 'badge' => $unreadMessages],
    ];
@endphp
<div class="mb-6">
    <div class="inline-flex items-center gap-1 bg-white border border-gray-200 rounded-xl p-1 shadow-sm">
        @foreach($inboxTabs as $tab)
            <a href="{{ $tab['href'] }}"
               class="relative flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-semibold transition-all
                      {{ $inboxActive === $tab['key']
                         ? 'bg-primary-600 text-white shadow-sm'
                         : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50' }}">
                <i data-lucide="{{ $tab['icon'] }}" class="w-4 h-4"></i>
                {{ $tab['label'] }}
                @if($tab['badge'] > 0)
                    <span class="ml-0.5 text-[10px] font-bold rounded-full px-1.5 min-w-[18px] text-center leading-4
                                 {{ $inboxActive === $tab['key'] ? 'bg-white/25 text-white' : 'bg-primary-600 text-white' }}">
                        {{ $tab['badge'] > 9 ? '9+' : $tab['badge'] }}
                    </span>
                @endif
            </a>
        @endforeach
    </div>
</div>
