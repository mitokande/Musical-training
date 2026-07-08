@extends('admin.layouts.admin')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <div class="flex items-center gap-3 mb-2">
                <div class="w-10 h-10 rounded-xl bg-amber-100 flex items-center justify-center">
                    <i data-lucide="star" class="w-5 h-5 text-amber-600"></i>
                </div>
                <h1 class="text-2xl font-bold text-gray-900">Teacher Reviews</h1>
            </div>
            <p class="text-gray-500">Moderate public teacher reviews</p>
        </div>
        <div class="flex gap-2">
            @foreach(['reported' => 'Reported', 'all' => 'All', 'hidden' => 'Hidden'] as $key => $label)
                <a href="{{ route('admin.teacher-reviews.index', ['filter' => $key]) }}"
                   class="px-3 py-1.5 text-sm font-semibold rounded-lg {{ $filter === $key ? 'bg-purple-600 text-white' : 'bg-gray-100 text-gray-600' }}">
                    {{ $label }}
                </a>
            @endforeach
        </div>
    </div>

    <div class="card overflow-hidden">
        @if($reviews->isEmpty())
            <div class="p-10 text-center text-gray-400 text-sm">No reviews in this state.</div>
        @else
            <ul class="divide-y divide-gray-100">
                @foreach($reviews as $review)
                    <li class="px-6 py-4">
                        <div class="flex items-start gap-4">
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-semibold text-gray-900">
                                    {{ $review->student->name }} {{ $review->student->surname }}
                                    <span class="text-amber-500">{{ str_repeat('★', $review->rating) }}{{ str_repeat('☆', 5 - $review->rating) }}</span>
                                    → <a href="{{ $review->teacherProfile->publicUrl() }}" class="text-purple-600 hover:underline">{{ $review->teacherProfile->display_name ?? $review->teacherProfile->user->name }}</a>
                                </p>
                                @if($review->body)<p class="text-sm text-gray-600 mt-1">{{ $review->body }}</p>@endif
                                <p class="text-xs text-gray-400 mt-1">
                                    {{ $review->created_at->format('M j, Y') }}
                                    · <b>{{ $review->status }}</b>
                                    @if($review->reported_at)
                                        · <span class="text-red-600 font-semibold">Reported {{ $review->reported_at->format('M j') }}: {{ $review->report_reason ?: '—' }}</span>
                                    @endif
                                </p>
                            </div>
                            <div class="flex gap-2 shrink-0">
                                @if($review->status !== 'approved' || $review->reported_at)
                                    <form method="POST" action="{{ route('admin.teacher-reviews.approve', $review) }}">
                                        @csrf
                                        <button class="px-3 py-1.5 text-xs font-semibold text-green-700 bg-green-50 hover:bg-green-100 rounded-lg">Approve</button>
                                    </form>
                                @endif
                                @if($review->status !== 'hidden')
                                    <form method="POST" action="{{ route('admin.teacher-reviews.hide', $review) }}">
                                        @csrf
                                        <button class="px-3 py-1.5 text-xs font-semibold text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-lg">Hide</button>
                                    </form>
                                @endif
                                <form method="POST" action="{{ route('admin.teacher-reviews.destroy', $review) }}" onsubmit="return confirm('Delete this review permanently?')">
                                    @csrf @method('DELETE')
                                    <button class="px-3 py-1.5 text-xs font-semibold text-red-600 bg-red-50 hover:bg-red-100 rounded-lg">Delete</button>
                                </form>
                            </div>
                        </div>
                    </li>
                @endforeach
            </ul>
            <div class="px-6 py-3">{{ $reviews->appends(['filter' => $filter])->links() }}</div>
        @endif
    </div>
</div>
@endsection
