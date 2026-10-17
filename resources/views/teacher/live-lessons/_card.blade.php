{{-- Dashboard card: start a Derslik lesson, then share its harmoniva.app link. --}}
@include('teacher.live-lessons._share-script')
<div class="card p-6 mb-6">
    <div class="flex items-start justify-between gap-4 mb-4">
        <div>
            <h2 class="font-bold text-gray-900 flex items-center gap-2">
                <i data-lucide="video" class="w-4 h-4 text-primary-600"></i>
                {{ crm_trans('live_lessons.card_title') }}
            </h2>
            <p class="text-sm text-gray-500 mt-1">{{ crm_trans('live_lessons.card_subtitle') }}</p>
        </div>
        @if($canHost)
            <a href="{{ crm_route('live-lessons.index') }}" class="text-xs font-semibold text-primary-600 hover:text-primary-800 shrink-0">{{ crm_trans('live_lessons.view_all') }} →</a>
        @endif
    </div>

    @if($canHost)
        @include('teacher.live-lessons._start-form')
        @if($lessons->isNotEmpty())
            <div class="mt-4">
                <h3 class="text-xs font-bold uppercase tracking-wide text-gray-400 mb-1">{{ crm_trans('live_lessons.open_lessons') }}</h3>
                @foreach($lessons as $lesson)
                    @include('teacher.live-lessons._lesson-row', ['lesson' => $lesson])
                @endforeach
            </div>
        @endif
    @else
        <div class="flex flex-col sm:flex-row sm:items-center gap-3 p-4 bg-amber-50 rounded-xl">
            <i data-lucide="lock" class="w-5 h-5 text-amber-600 shrink-0"></i>
            <p class="text-sm text-amber-800 flex-1">{{ crm_trans('live_lessons.locked') }}</p>
            <a href="{{ route('checkout.show') }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-xs font-bold text-white shrink-0" style="background:linear-gradient(135deg,#9333ea,#7c3aed);">
                <i data-lucide="crown" class="w-3.5 h-3.5"></i> {{ __('app.dashboard.upgrade_premium') }}
            </a>
        </div>
    @endif
</div>
