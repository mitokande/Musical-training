@extends('teacher.layouts.crm')

@section('title', crm_trans('messaging.title'))

@section('content')
<a href="{{ crm_route('messages.index') }}" class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-gray-700 mb-4">
    <i data-lucide="arrow-left" class="w-4 h-4"></i> {{ crm_trans('messaging.title') }}
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
            <a href="{{ crm_route('students.show', $conversation->student) }}" class="text-xs font-semibold text-primary-600 hover:text-primary-800">
                {{ crm_trans('students.view_profile') }} →
            </a>
        </div>
    </div>

    @include('teacher-messages.partials.thread', ['messages' => $messages, 'attachmentRoute' => 'teacher-messages.attachment'])

    @if($canReply)
        @php
            $crmQuota = app(\App\Services\Teacher\CrmQuotaService::class);
            $messageLimit = $crmQuota->limit(auth()->user(), 'daily_teacher_messages');
            $messagesUsed = $messageLimit === -1 ? 0 : app(\App\Services\UsageQuotaService::class)
                ->userUsed(auth()->user(), \App\Services\UsageQuotaService::FEATURE_TEACHER_MESSAGES);
        @endphp
        @if($messageLimit !== -1)
            <div class="mt-4 flex items-center gap-2">
                <span class="px-3 py-1 rounded-full bg-purple-50 border border-purple-200 text-purple-700 text-xs font-bold">
                    {{ __('teacher.limits.messages_counter', ['used' => min($messagesUsed, $messageLimit), 'limit' => $messageLimit]) }}
                </span>
            </div>
        @endif
        @if($errors->has('body'))
            <div class="mt-3 px-4 py-3 bg-orange-50 border border-orange-200 rounded-xl text-sm text-orange-700">{{ $errors->first('body') }}</div>
        @endif
        @include('teacher-messages.partials.composer', ['action' => crm_route('messages.store', $conversation)])
    @else
        <div class="mt-4 px-4 py-3 bg-amber-50 border border-amber-200 rounded-xl">
            <p class="text-sm text-amber-700">{{ crm_trans('messaging.basic_readonly') }}</p>
        </div>
    @endif
</div>
@endsection
