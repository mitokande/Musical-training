@extends('teacher.layouts.crm')

@section('title', crm_trans('live_lessons.title'))

@section('content')
@php $ll = 'live_lessons'; @endphp
@include('teacher.live-lessons._share-script')
<div class="max-w-4xl">
    <div class="mb-6">
        <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">{{ crm_trans($ll.'.title') }}</h1>
        <p class="text-gray-500 mt-1">{{ crm_trans($ll.'.card_subtitle') }}</p>
    </div>

    <div class="card p-6 mb-6">
        @include('teacher.live-lessons._start-form')
        <p class="text-xs text-gray-400 mt-3">
            {{ crm_trans($ll.'.student_link_hint') }}
            {{ crm_trans($ll.'.auto_close', ['hours' => \App\Models\LiveLesson::lessonHours()]) }}
        </p>
    </div>

    <div class="card p-6 mb-6">
        <div class="flex items-center justify-between mb-2">
            <h2 class="font-bold text-gray-900">{{ crm_trans($ll.'.open_lessons') }}</h2>
            <span class="text-xs text-gray-400">{{ crm_trans($ll.'.open_count', ['open' => $openLessons->count(), 'max' => $maxOpen]) }}</span>
        </div>
        @forelse($openLessons as $lesson)
            @include('teacher.live-lessons._lesson-row', ['lesson' => $lesson, 'status' => $liveStatus[$lesson->id] ?? null])
        @empty
            <p class="text-sm text-gray-400 py-2">{{ crm_trans($ll.'.none_open') }}</p>
        @endforelse
    </div>

    @if($pastLessons->isNotEmpty())
        <div class="card p-6">
            <h2 class="font-bold text-gray-900 mb-2">{{ crm_trans($ll.'.past_lessons') }}</h2>
            @foreach($pastLessons as $lesson)
                <div class="flex items-center gap-3 py-2.5 border-b border-gray-100 last:border-0">
                    <i data-lucide="video-off" class="w-4 h-4 text-gray-300 shrink-0"></i>
                    <p class="text-sm text-gray-700 truncate flex-1">{{ $lesson->title }}</p>
                    <p class="text-xs text-gray-400 shrink-0">
                        {{ $lesson->ended_at
                            ? crm_trans($ll.'.ended_at', ['time' => $lesson->ended_at->diffForHumans()])
                            : crm_trans($ll.'.expired') }}
                    </p>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
