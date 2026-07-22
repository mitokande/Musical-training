<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('partials.google-analytics')
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('app.notifications.title') }} - {{ config('app.name', 'Harmoniva') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@0.460.0"></script>
    <script defer src="https://unpkg.com/alpinejs@3.14.8/dist/cdn.min.js"></script>
    <script>
        tailwind.config = {
            theme: { extend: {
                fontFamily: { sans: ['Plus Jakarta Sans', 'system-ui', 'sans-serif'] },
                colors: {
                    primary: { 50:'#faf5ff',100:'#f3e8ff',200:'#e9d5ff',300:'#d8b4fe',400:'#c084fc',500:'#a855f7',600:'#9333ea',700:'#7c3aed',800:'#6b21a8',900:'#581c87' },
                }
            } }
        }
    </script>
    <style>
        .card { background:white; border-radius:16px; border:1px solid #ede9fe; box-shadow:0 2px 8px 0 rgb(109 40 217/0.06),0 1px 2px -1px rgb(0 0 0/0.06); }
    </style>
</head>
<body class="font-sans bg-gray-50 min-h-screen">

    @include('partials.navbar', ['active' => 'notifications'])

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        @include('partials.inbox-tabs', ['active' => 'notifications'])

        <div class="max-w-5xl mx-auto">
        <div class="flex items-center justify-between mb-6">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-primary-50 flex items-center justify-center">
                    <i data-lucide="bell" class="w-5 h-5 text-primary-600"></i>
                </div>
                <h1 class="text-2xl font-bold text-gray-900">{{ __('app.notifications.title') }}</h1>
            </div>
        </div>

        <div class="card overflow-hidden" style="height:70vh;">
        <div class="h-full overflow-y-auto divide-y divide-gray-50">
            @forelse($notifications as $notification)
                @php
                    $data = $notification->data;
                    $type = $data['type'] ?? '';
                    $isFresh = $freshIds->contains($notification->id);

                    $url = match ($type) {
                        'teacher_message_received' => isset($data['conversation_id']) ? route('teacher-messages.show', $data['conversation_id']) : url('/messages'),
                        'student_assignment_received' => $data['url'] ?? (\Illuminate\Support\Facades\Route::has('assignments.index') ? route('assignments.index') : url('/')),
                        'teacher_relationship_requested', 'teacher_relationship_accepted', 'teacher_relationship_declined', 'teacher_relationship_revoked', 'student_reward_received' => $data['url'] ?? (\Illuminate\Support\Facades\Route::has('my-teachers.index') ? route('my-teachers.index') : url('/')),
                        default => $data['url'] ?? null,
                    };

                    $icon = match ($type) {
                        'teacher_message_received' => 'message-circle',
                        'student_assignment_received' => 'clipboard-list',
                        'student_reward_received' => 'gift',
                        'student_document_shared' => 'file-text',
                        'student_article_shared' => 'newspaper',
                        'user_followed' => 'user-plus',
                        'teacher_relationship_requested', 'teacher_relationship_accepted' => 'user-check',
                        'teacher_relationship_declined', 'teacher_relationship_revoked' => 'user-x',
                        default => 'bell',
                    };
                @endphp

                <a href="{{ $url ?? '#' }}"
                   class="flex items-start gap-3 p-4 transition-colors
                          {{ $isFresh ? 'bg-primary-50 hover:bg-primary-100' : 'hover:bg-gray-50 opacity-70' }}">
                    <div class="w-9 h-9 rounded-full flex items-center justify-center shrink-0 mt-0.5
                                {{ $isFresh ? 'bg-primary-600 text-white' : 'bg-gray-100 text-gray-400' }}">
                        <i data-lucide="{{ $icon }}" class="w-4 h-4"></i>
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm {{ $isFresh ? 'font-semibold text-gray-900' : 'text-gray-600' }}">
                            @switch($type)
                                @case('teacher_message_received')
                                    {{ ($data['sender_name'] ?? '') }} — {{ __('app.notifications.msg_message') }}
                                    @break
                                @case('student_assignment_received')
                                    {{ __('app.notifications.msg_assignment') }}: {{ $data['title'] ?? '' }}
                                    @break
                                @case('student_reward_received')
                                    {{ ($data['teacher_name'] ?? '') }} — {{ __('app.notifications.msg_reward') }}{{ !empty($data['label']) ? ' · '.$data['label'] : '' }}
                                    @break
                                @case('student_document_shared')
                                    {{ ($data['teacher'] ?? '') }} — {{ __('app.notifications.msg_document_shared') }}: {{ $data['title'] ?? '' }}
                                    @break
                                @case('student_article_shared')
                                    {{ ($data['teacher'] ?? '') }} — {{ __('app.notifications.msg_article_shared') }}: {{ $data['title'] ?? '' }}
                                    @break
                                @case('user_followed')
                                    {{ ($data['follower_name'] ?? '') }} — {{ __('app.notifications.msg_followed') }}
                                    @break
                                @case('teacher_relationship_requested')
                                    {{ ($data['teacher_name'] ?? '') }} — {{ __('app.notifications.msg_relationship_requested') }}
                                    @break
                                @case('teacher_relationship_accepted')
                                    {{ ($data['teacher_name'] ?? '') }} — {{ __('app.notifications.msg_relationship_accepted') }}
                                    @break
                                @case('teacher_relationship_declined')
                                    {{ ($data['teacher_name'] ?? '') }} — {{ __('app.notifications.msg_relationship_declined') }}
                                    @break
                                @case('teacher_relationship_revoked')
                                    {{ ($data['teacher_name'] ?? '') }} — {{ __('app.notifications.msg_relationship_revoked') }}
                                    @break
                                @default
                                    {{ ucfirst(str_replace('_', ' ', $type)) }}
                            @endswitch
                        </p>
                        <p class="text-xs text-gray-400 mt-0.5">{{ $notification->created_at->diffForHumans() }}</p>
                    </div>
                    @if($isFresh)
                        <span class="shrink-0 mt-1.5 w-2 h-2 rounded-full bg-primary-600"></span>
                    @endif
                </a>
            @empty
                <div class="p-12 text-center">
                    <div class="w-14 h-14 rounded-2xl bg-gray-100 flex items-center justify-center mx-auto mb-3">
                        <i data-lucide="bell-off" class="w-6 h-6 text-gray-400"></i>
                    </div>
                    <p class="text-sm text-gray-500">{{ __('app.notifications.empty') }}</p>
                </div>
            @endforelse
        </div>
        </div>

        @if($notifications->hasPages())
            <div class="mt-6">
                {{ $notifications->links() }}
            </div>
        @endif
        </div>
    </main>

    @include('partials.footer')

    <script>
        lucide.createIcons();
    </script>
</body>
</html>
