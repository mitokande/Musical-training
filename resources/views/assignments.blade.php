<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('teacher.student_assignments.title') }} - {{ config('app.name', 'Harmoniva') }}</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@0.460.0"></script>
    <script>
        tailwind.config = { theme: { extend: {
            fontFamily: { sans: ['Plus Jakarta Sans', 'system-ui', 'sans-serif'] },
            colors: { primary: { 50:'#faf5ff',100:'#f3e8ff',200:'#e9d5ff',600:'#9333ea',700:'#7c3aed' } }
        } } }
    </script>
</head>
<body class="font-sans bg-gray-50 min-h-screen">
@include('partials.navbar')

@php $sa = 'teacher.student_assignments'; @endphp

<main class="max-w-4xl mx-auto px-4 sm:px-6 py-8">
    <h1 class="text-2xl font-bold text-gray-900">{{ __($sa.'.title') }}</h1>
    <p class="text-gray-500 text-sm mt-1 mb-6">{{ __($sa.'.subtitle') }}</p>

    @if ($errors->any())
        <div class="mb-6 px-4 py-3 bg-red-50 border border-red-200 rounded-xl">
            @foreach ($errors->all() as $error)<p class="text-sm text-red-600">{{ $error }}</p>@endforeach
        </div>
    @endif

    @if($rewards->isNotEmpty())
        <div class="bg-white rounded-2xl border border-amber-200 p-5 mb-6">
            <h2 class="font-bold text-gray-900 mb-3 flex items-center gap-2">
                <i data-lucide="award" class="w-4 h-4 text-amber-500"></i> {{ __($sa.'.rewards_title') }}
            </h2>
            <div class="flex flex-wrap gap-2">
                @foreach($rewards as $reward)
                    <span class="px-3 py-1.5 rounded-full text-xs font-semibold bg-amber-50 text-amber-700"
                          title="{{ trim($reward->teacher->name.' '.$reward->teacher->surname) }} · {{ $reward->created_at->format('M j, Y') }}">
                        🌟 {{ $reward->label }}
                    </span>
                @endforeach
            </div>
        </div>
    @endif

    @if($recipients->isEmpty())
        <div class="bg-white rounded-2xl border border-gray-200 p-10 text-center text-gray-500">
            <i data-lucide="clipboard-list" class="w-10 h-10 mx-auto mb-3 text-gray-300"></i>
            <p class="text-sm">{{ __($sa.'.no_assignments') }}</p>
        </div>
    @else
        <div class="space-y-4">
            @foreach($recipients as $recipient)
                @php
                    $assignment = $recipient->assignment;
                    $teacher = $assignment->teacher;
                    $completed = $recipient->isCompleted();
                    $overdue = $recipient->isOverdue();
                @endphp
                <div class="bg-white rounded-2xl border {{ $overdue ? 'border-red-200' : 'border-gray-200' }} p-5">
                    <div class="flex flex-col sm:flex-row sm:items-center gap-4">
                        <div class="flex-1 min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <h3 class="font-bold text-gray-900">{{ $assignment->title }}</h3>
                                @if($completed)
                                    <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-green-50 text-green-700">
                                        {{ __($sa.'.completed_with', ['score' => round((float) $recipient->best_score, 1)]) }}
                                    </span>
                                @elseif($overdue)
                                    <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-red-50 text-red-600">{{ __($sa.'.overdue') }}</span>
                                @endif
                            </div>
                            <p class="text-xs text-gray-500 mt-1">
                                {{ __($sa.'.from', ['teacher' => trim($teacher->name.' '.$teacher->surname)]) }}
                                @if($assignment->due_at) · {{ __($sa.'.due', ['date' => $assignment->due_at->format('M j, Y H:i')]) }} @endif
                                @if($assignment->max_attempts) · {{ __($sa.'.attempts_used', ['used' => $recipient->attempts_count, 'max' => $assignment->max_attempts]) }} @endif
                            </p>
                            @if($assignment->description)
                                <p class="text-sm text-gray-600 mt-2">{{ $assignment->description }}</p>
                            @endif
                            @if($assignment->instructions)
                                <details class="mt-2">
                                    <summary class="text-xs font-semibold text-primary-600 cursor-pointer">{{ __($sa.'.instructions') }}</summary>
                                    <p class="text-sm text-gray-600 mt-1 whitespace-pre-line">{{ $assignment->instructions }}</p>
                                </details>
                            @endif
                            @if($assignment->media->isNotEmpty())
                                <div class="mt-3 flex flex-wrap gap-2">
                                    @foreach($assignment->media as $m)
                                        <a href="{{ $m->isPublic() ? $m->publicUrl() : route('assignments.media.download', $m) }}"
                                           @if($m->isPublic()) target="_blank" rel="noopener" @endif
                                           class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-primary-700 bg-primary-50 hover:bg-primary-100 rounded-lg transition">
                                            <i data-lucide="paperclip" class="w-3.5 h-3.5"></i>
                                            <span class="truncate max-w-[12rem]">{{ $m->title ?: $m->original_name }}</span>
                                        </a>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                        @if($assignment->type !== 'practice_goal' && $recipient->canAttempt())
                            <form method="POST" action="{{ route('assignments.start', $recipient) }}" class="shrink-0">
                                @csrf
                                <button class="inline-flex items-center gap-2 px-5 py-2.5 bg-primary-600 hover:bg-primary-700 text-white font-semibold rounded-xl transition">
                                    <i data-lucide="play" class="w-4 h-4"></i>
                                    {{ $completed ? __($sa.'.retry') : ($recipient->status === 'started' ? __($sa.'.continue') : __($sa.'.start')) }}
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</main>

@include('partials.footer')
<script>lucide.createIcons();</script>
</body>
</html>
