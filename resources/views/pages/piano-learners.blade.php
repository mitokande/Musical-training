@extends('layouts.standalone')

@section('title', __('pages.piano_learners.meta_title'))
@section('description', __('pages.piano_learners.meta_description'))

@section('content')

{{-- Hero --}}
<section class="py-20 sm:py-28 relative overflow-hidden" style="background: linear-gradient(135deg, #faf5ff 0%, #FAF7F2 60%, #f3e8ff 100%);">
    <div class="absolute -top-32 -right-32 w-[600px] h-[600px] rounded-full bg-purple-100/40 blur-3xl pointer-events-none"></div>
    <div class="absolute -bottom-20 -left-20 w-[350px] h-[350px] rounded-full bg-pink-50/60 blur-2xl pointer-events-none"></div>

    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 relative">
        <div class="text-center max-w-3xl mx-auto">
            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-primary-100 text-primary-700 text-sm font-semibold mb-6 hero-badge">
                <i data-lucide="piano" class="w-4 h-4"></i>
                {{ __('pages.piano_learners.hero_badge') }}
            </div>

            <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold text-gray-900 leading-tight mb-6">
                {{ __('pages.piano_learners.hero_title_a') }}<br>
                <span class="font-serif italic font-normal gradient-text">{{ __('pages.piano_learners.hero_title_b') }}</span>
            </h1>

            <p class="text-gray-500 text-lg max-w-2xl mx-auto mb-10">
                {{ __('pages.piano_learners.hero_subtitle') }}
            </p>

            <div class="flex flex-col sm:flex-row items-center justify-center gap-4 mb-10">
                @auth
                <a href="{{ url('/dashboard') }}" class="inline-flex items-center gap-2 px-8 py-4 text-base font-bold text-white rounded-xl hover:opacity-90 transition-all shadow-xl hover:-translate-y-0.5" style="background:linear-gradient(135deg,#9333ea,#7c3aed);">
                    <i data-lucide="rocket" class="w-5 h-5"></i>
                    {{ __('pages.piano_learners.hero_cta_dashboard') }}
                </a>
                @else
                <a href="{{ route('register') }}" class="inline-flex items-center gap-2 px-8 py-4 text-base font-bold text-white rounded-xl hover:opacity-90 transition-all shadow-xl hover:-translate-y-0.5" style="background:linear-gradient(135deg,#9333ea,#7c3aed);">
                    <i data-lucide="rocket" class="w-5 h-5"></i>
                    {{ __('pages.piano_learners.hero_cta_register') }}
                </a>
                @endauth
                <a href="{{ locale_url('/pricing') }}" class="inline-flex items-center gap-2 px-6 py-4 text-base font-medium text-gray-500 hover:text-gray-800 transition-colors">
                    {{ __('pages.piano_learners.see_pricing') }} <i data-lucide="arrow-right" class="w-4 h-4"></i>
                </a>
            </div>

            <div class="flex flex-wrap items-center justify-center gap-6 text-sm text-gray-400">
                <span class="flex items-center gap-1.5"><i data-lucide="check" class="w-4 h-4 text-green-500"></i>{{ __('pages.piano_learners.hero_check_1') }}</span>
                <span class="flex items-center gap-1.5"><i data-lucide="check" class="w-4 h-4 text-green-500"></i>{{ __('pages.piano_learners.hero_check_2') }}</span>
                <span class="flex items-center gap-1.5"><i data-lucide="check" class="w-4 h-4 text-green-500"></i>{{ __('pages.piano_learners.hero_check_3') }}</span>
            </div>
        </div>
    </div>
</section>

{{-- Why Ear Training Matters for Pianists --}}
<section class="py-20 bg-white">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
            <div class="reveal">
                <span class="text-xs font-bold uppercase tracking-[0.2em] text-primary-600 mb-4 block">{{ __('pages.piano_learners.why_eyebrow') }}</span>
                <h2 class="text-3xl sm:text-4xl font-extrabold text-gray-900 mb-6">
                    {{ __('pages.piano_learners.why_title_a') }}<br>
                    <span class="font-serif italic font-normal gradient-text">{{ __('pages.piano_learners.why_title_b') }}</span>
                </h2>
                <div class="space-y-5 text-gray-500 text-sm leading-relaxed">
                    <p>{{ __('pages.piano_learners.why_p1') }}</p>
                    <p>{{ __('pages.piano_learners.why_p2') }}</p>
                    <p>{{ __('pages.piano_learners.why_p3') }}</p>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4 reveal" style="transition-delay:0.15s">
                @php $benefits = [
                    ['icon' => 'book-open', 'color' => 'bg-purple-100 text-purple-600', 'title' => __('pages.piano_learners.ben_sightread_title'), 'desc' => __('pages.piano_learners.ben_sightread_desc')],
                    ['icon' => 'brain', 'color' => 'bg-blue-100 text-blue-600', 'title' => __('pages.piano_learners.ben_memorize_title'), 'desc' => __('pages.piano_learners.ben_memorize_desc')],
                    ['icon' => 'mic-2', 'color' => 'bg-pink-100 text-pink-600', 'title' => __('pages.piano_learners.ben_feeling_title'), 'desc' => __('pages.piano_learners.ben_feeling_desc')],
                    ['icon' => 'wrench', 'color' => 'bg-amber-100 text-amber-600', 'title' => __('pages.piano_learners.ben_fix_title'), 'desc' => __('pages.piano_learners.ben_fix_desc')],
                    ['icon' => 'music-2', 'color' => 'bg-green-100 text-green-600', 'title' => __('pages.piano_learners.ben_improvise_title'), 'desc' => __('pages.piano_learners.ben_improvise_desc')],
                    ['icon' => 'headphones', 'color' => 'bg-orange-100 text-orange-600', 'title' => __('pages.piano_learners.ben_transcribe_title'), 'desc' => __('pages.piano_learners.ben_transcribe_desc')],
                ]; @endphp
                @foreach ($benefits as $b)
                <div class="bg-gray-50 rounded-2xl border border-gray-100 p-4">
                    <div class="w-9 h-9 rounded-xl {{ $b['color'] }} flex items-center justify-center mb-3">
                        <i data-lucide="{{ $b['icon'] }}" class="w-4 h-4"></i>
                    </div>
                    <h4 class="font-bold text-gray-900 text-sm mb-1">{{ $b['title'] }}</h4>
                    <p class="text-gray-400 text-xs leading-relaxed">{{ $b['desc'] }}</p>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</section>

{{-- Feature Highlights --}}
<section class="py-20" style="background:#FAF7F2;">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-14 reveal">
            <span class="text-xs font-bold uppercase tracking-[0.2em] text-primary-600 mb-3 block">{{ __('pages.piano_learners.feat_eyebrow') }}</span>
            <h2 class="text-3xl sm:text-4xl font-extrabold text-gray-900 mb-4">
                {{ __('pages.piano_learners.feat_title_a') }}<br>
                <span class="font-serif italic font-normal gradient-text">{{ __('pages.piano_learners.feat_title_b') }}</span>
            </h2>
        </div>

        <div class="space-y-6">
            @php $highlights = [
                [
                    'icon' => 'piano',
                    'color' => 'from-purple-500 to-violet-600',
                    'title' => __('pages.piano_learners.h_studio_title'),
                    'desc' => __('pages.piano_learners.h_studio_desc'),
                    'points' => [__('pages.piano_learners.h_studio_p1'), __('pages.piano_learners.h_studio_p2'), __('pages.piano_learners.h_studio_p3')],
                ],
                [
                    'icon' => 'music-2',
                    'color' => 'from-blue-500 to-cyan-500',
                    'title' => __('pages.piano_learners.h_recognition_title'),
                    'desc' => __('pages.piano_learners.h_recognition_desc'),
                    'points' => [__('pages.piano_learners.h_recognition_p1'), __('pages.piano_learners.h_recognition_p2'), __('pages.piano_learners.h_recognition_p3')],
                ],
                [
                    'icon' => 'mic',
                    'color' => 'from-orange-500 to-amber-500',
                    'title' => __('pages.piano_learners.h_dictation_title'),
                    'desc' => __('pages.piano_learners.h_dictation_desc'),
                    'points' => [__('pages.piano_learners.h_dictation_p1'), __('pages.piano_learners.h_dictation_p2'), __('pages.piano_learners.h_dictation_p3')],
                ],
                [
                    'icon' => 'book-open',
                    'color' => 'from-green-500 to-teal-500',
                    'title' => __('pages.piano_learners.h_sightread_title'),
                    'desc' => __('pages.piano_learners.h_sightread_desc'),
                    'points' => [__('pages.piano_learners.h_sightread_p1'), __('pages.piano_learners.h_sightread_p2'), __('pages.piano_learners.h_sightread_p3')],
                ],
            ]; @endphp
            @foreach ($highlights as $hi => $h)
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-7 reveal" style="transition-delay:{{ $hi * 0.1 }}s">
                <div class="flex flex-col md:flex-row items-start gap-6">
                    <div class="w-14 h-14 rounded-2xl bg-gradient-to-br {{ $h['color'] }} flex items-center justify-center shadow-lg shrink-0">
                        <i data-lucide="{{ $h['icon'] }}" class="w-7 h-7 text-white"></i>
                    </div>
                    <div class="flex-1">
                        <h3 class="text-lg font-extrabold text-gray-900 mb-2">{{ $h['title'] }}</h3>
                        <p class="text-gray-500 text-sm leading-relaxed mb-4">{{ $h['desc'] }}</p>
                        <div class="flex flex-wrap gap-2">
                            @foreach ($h['points'] as $point)
                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-gray-50 border border-gray-100 text-xs text-gray-600 font-medium">
                                <i data-lucide="check" class="w-3 h-3 text-green-500"></i>
                                {{ $point }}
                            </span>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- Integration with Lessons --}}
<section class="py-20 bg-white">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12 reveal">
            <span class="text-xs font-bold uppercase tracking-[0.2em] text-primary-600 mb-3 block">{{ __('pages.piano_learners.int_eyebrow') }}</span>
            <h2 class="text-3xl font-extrabold text-gray-900 mb-3">
                {{ __('pages.piano_learners.int_title_a') }}<br>
                <span class="font-serif italic font-normal gradient-text">{{ __('pages.piano_learners.int_title_b') }}</span>
            </h2>
            <p class="text-gray-500 max-w-xl mx-auto">{{ __('pages.piano_learners.int_subtitle') }}</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 reveal">
            @php $integration = [
                ['icon' => 'calendar', 'color' => 'bg-purple-100 text-purple-600', 'title' => __('pages.piano_learners.int_1_title'), 'desc' => __('pages.piano_learners.int_1_desc')],
                ['icon' => 'music', 'color' => 'bg-orange-100 text-orange-600', 'title' => __('pages.piano_learners.int_2_title'), 'desc' => __('pages.piano_learners.int_2_desc')],
                ['icon' => 'trending-up', 'color' => 'bg-green-100 text-green-600', 'title' => __('pages.piano_learners.int_3_title'), 'desc' => __('pages.piano_learners.int_3_desc')],
            ]; @endphp
            @foreach ($integration as $int)
            <div class="text-center p-6 bg-gray-50 rounded-2xl border border-gray-100">
                <div class="w-12 h-12 mx-auto rounded-xl {{ $int['color'] }} flex items-center justify-center mb-4">
                    <i data-lucide="{{ $int['icon'] }}" class="w-6 h-6"></i>
                </div>
                <h3 class="font-bold text-gray-900 mb-2">{{ $int['title'] }}</h3>
                <p class="text-gray-500 text-sm leading-relaxed">{{ $int['desc'] }}</p>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- CTA --}}
<section class="py-24 relative overflow-hidden bg-gray-900">
    <div class="absolute -top-24 -right-24 w-[500px] h-[500px] rounded-full blur-3xl pointer-events-none" style="background:rgba(147,51,234,0.2);"></div>
    <div class="absolute -bottom-20 -left-20 w-[400px] h-[400px] rounded-full blur-3xl pointer-events-none" style="background:rgba(249,115,22,0.1);"></div>

    <div class="max-w-3xl mx-auto px-4 sm:px-6 text-center relative reveal">
        <div class="w-16 h-16 mx-auto rounded-2xl flex items-center justify-center mb-8 shadow-xl hero-badge" style="background:linear-gradient(135deg,#9333ea,#7c3aed);">
            <i data-lucide="piano" class="w-8 h-8 text-white"></i>
        </div>
        <h2 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-white mb-5">
            {{ __('pages.piano_learners.cta_title_a') }}<br>
            <span class="font-serif italic font-normal" style="background:linear-gradient(135deg,#c084fc,#fb923c);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;">{{ __('pages.piano_learners.cta_title_b') }}</span>
        </h2>
        <p class="text-gray-400 text-lg mb-10 max-w-xl mx-auto">
            {{ __('pages.piano_learners.cta_subtitle') }}
        </p>
        <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
            @auth
            <a href="{{ url('/dashboard') }}" class="inline-flex items-center gap-2 px-8 py-4 text-base font-bold text-white rounded-xl hover:opacity-90 transition-all shadow-xl hover:-translate-y-0.5" style="background:linear-gradient(135deg,#9333ea,#7c3aed);">
                <i data-lucide="rocket" class="w-5 h-5"></i>
                {{ __('pages.piano_learners.hero_cta_dashboard') }}
            </a>
            @else
            <a href="{{ route('register') }}" class="inline-flex items-center gap-2 px-8 py-4 text-base font-bold text-white rounded-xl hover:opacity-90 transition-all shadow-xl hover:-translate-y-0.5" style="background:linear-gradient(135deg,#9333ea,#7c3aed);">
                <i data-lucide="rocket" class="w-5 h-5"></i>
                {{ __('pages.piano_learners.cta_register') }}
            </a>
            @endauth
            <a href="{{ locale_url('/pricing') }}" class="inline-flex items-center gap-2 px-6 py-4 text-base font-medium text-gray-400 hover:text-white transition-colors">
                {{ __('pages.piano_learners.see_pricing') }} <i data-lucide="arrow-right" class="w-4 h-4"></i>
            </a>
        </div>
    </div>
</section>

@endsection
