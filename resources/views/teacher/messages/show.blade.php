@extends('teacher.layouts.crm')

@section('title', __('teacher.messaging.title'))

@section('content')
<a href="{{ route('teacher.messages.index') }}" class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-gray-700 mb-4">
    <i data-lucide="arrow-left" class="w-4 h-4"></i> {{ __('teacher.messaging.title') }}
</a>

<div class="max-w-3xl">
    <div class="card p-4 mb-4 flex items-center gap-3">
        @if($conversation->student->hasAvatar())
            <img src="{{ $conversation->student->avatar }}" class="w-10 h-10 rounded-full object-cover" alt="">
        @else
            <div class="w-10 h-10 rounded-full bg-primary-100 text-primary-700 flex items-center justify-center font-bold">
                {{ strtoupper(substr($conversation->student->name, 0, 1)) }}
            </div>
        @endif
        <div>
            <p class="font-bold text-gray-900">{{ $conversation->student->name }} {{ $conversation->student->surname }}</p>
            <a href="{{ route('teacher.students.show', $conversation->student) }}" class="text-xs font-semibold text-primary-600 hover:text-primary-800">
                {{ __('teacher.students.view_profile') }} →
            </a>
        </div>
    </div>

    @include('teacher-messages.partials.thread', ['messages' => $messages, 'attachmentRoute' => 'teacher-messages.attachment'])

    @if($canReply)
        @include('teacher-messages.partials.composer', ['action' => route('teacher.messages.store', $conversation)])
    @else
        <div class="mt-4 px-4 py-3 bg-amber-50 border border-amber-200 rounded-xl">
            <p class="text-sm text-amber-700">{{ __('teacher.messaging.basic_readonly') }}</p>
        </div>
    @endif
</div>
@endsection
