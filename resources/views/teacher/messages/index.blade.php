@extends('teacher.layouts.crm')

@section('title', crm_trans('messaging.title'))

@section('content')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">{{ crm_trans('messaging.title') }}</h1>
        <p class="text-gray-500 text-sm mt-1">{{ crm_trans('messaging.subtitle') }}</p>
    </div>
</div>

@unless($canReply)
    <div class="mb-6 px-4 py-3 bg-amber-50 border border-amber-200 rounded-xl flex items-center gap-3">
        <i data-lucide="lock" class="w-4 h-4 text-amber-600 shrink-0"></i>
        <p class="text-sm text-amber-700">{{ crm_trans('messaging.basic_readonly') }}</p>
    </div>
@endunless

@if($newRecipients->isNotEmpty())
    <div class="card p-4 mb-6 flex flex-wrap items-center gap-3">
        <span class="text-sm font-semibold text-gray-600">{{ crm_trans('messaging.new_conversation') }}:</span>
        @foreach($newRecipients as $student)
            <form method="POST" action="{{ crm_route('messages.start', $student) }}">
                @csrf
                <button class="px-3 py-1.5 text-xs font-semibold text-primary-700 bg-primary-50 hover:bg-primary-100 rounded-lg transition">
                    {{ $student->name }} {{ $student->surname }}
                </button>
            </form>
        @endforeach
    </div>
@endif

<div class="card overflow-hidden">
    @if($conversations->isEmpty())
        <div class="p-10 text-center text-gray-500">
            <i data-lucide="message-circle" class="w-10 h-10 mx-auto mb-3 text-gray-300"></i>
            <p class="text-sm">{{ crm_trans('messaging.no_conversations') }}</p>
        </div>
    @else
        <ul class="divide-y divide-gray-100">
            @foreach($conversations as $conversation)
                @php $unread = $conversation->unreadCountFor(auth()->user()); @endphp
                <li>
                    <a href="{{ crm_route('messages.show', $conversation) }}" class="flex items-center gap-4 px-5 py-4 hover:bg-gray-50 transition">
                        @if($conversation->student->hasAvatar())
                            <img src="{{ $conversation->student->avatar }}" class="w-11 h-11 rounded-full object-cover" alt="">
                        @else
                            <div class="w-11 h-11 rounded-full bg-primary-100 text-primary-700 flex items-center justify-center font-bold">
                                {{ strtoupper(substr($conversation->student->name, 0, 1)) }}
                            </div>
                        @endif
                        <div class="flex-1 min-w-0">
                            <p class="font-semibold text-gray-900 truncate">{{ $conversation->student->name }} {{ $conversation->student->surname }}</p>
                            <p class="text-xs text-gray-400">{{ $conversation->last_message_at?->diffForHumans() }}</p>
                        </div>
                        @if($unread > 0)
                            <span class="px-2 py-0.5 rounded-full bg-primary-600 text-white text-[11px] font-bold">{{ $unread }}</span>
                        @endif
                    </a>
                </li>
            @endforeach
        </ul>
    @endif
</div>
@endsection
