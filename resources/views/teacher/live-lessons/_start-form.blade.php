<form method="POST" action="{{ crm_route('live-lessons.store') }}" class="flex flex-col sm:flex-row gap-2">
    @csrf
    <label for="live-lesson-title" class="sr-only">{{ crm_trans('live_lessons.title_label') }}</label>
    <input id="live-lesson-title" name="title" type="text" maxlength="80" value="{{ old('title') }}"
           placeholder="{{ crm_trans('live_lessons.title_placeholder') }}"
           class="flex-1 px-3.5 py-2.5 text-sm border border-gray-200 rounded-xl focus:ring-primary-500 focus:border-primary-500">
    <button class="inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-primary-600 hover:bg-primary-700 text-white text-sm font-bold rounded-xl shadow-sm transition shrink-0">
        <i data-lucide="video" class="w-4 h-4"></i> {{ crm_trans('live_lessons.start') }}
    </button>
</form>
