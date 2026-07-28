@extends('layouts.standalone')

@section('title', __('pages.students.meta_title'))
@section('description', __('pages.students.meta_description'))

@section('content')

{{-- Hero --}}
<section class="py-20 sm:py-28 relative overflow-hidden" style="background: linear-gradient(135deg, #faf5ff 0%, #FAF7F2 60%, #f3e8ff 100%);">
    <div class="absolute -top-32 -right-32 w-[600px] h-[600px] rounded-full bg-purple-100/40 blur-3xl pointer-events-none"></div>
    <div class="absolute -bottom-20 -left-20 w-[350px] h-[350px] rounded-full bg-orange-50/50 blur-2xl pointer-events-none"></div>

    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 relative">
        <div class="text-center max-w-3xl mx-auto">
            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-primary-100 text-primary-700 text-sm font-semibold mb-6 hero-badge">
                <i data-lucide="graduation-cap" class="w-4 h-4"></i>
                {{ __('pages.students.hero_badge') }}
            </div>

            <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold text-gray-900 leading-tight mb-6">
                {{ __('pages.students.hero_title_a') }}<br>
                <span class="font-serif italic font-normal gradient-text">{{ __('pages.students.hero_title_b') }}</span>
            </h1>

            <p class="text-gray-500 text-lg max-w-2xl mx-auto mb-10">
                {{ __('pages.students.hero_subtitle') }}
            </p>

            <div class="flex flex-col sm:flex-row items-center justify-center gap-4 mb-12">
                @auth
                <a href="{{ url('/dashboard') }}" class="inline-flex items-center gap-2 px-8 py-4 text-base font-bold text-white rounded-xl hover:opacity-90 transition-all shadow-xl hover:-translate-y-0.5" style="background:linear-gradient(135deg,#9333ea,#7c3aed);">
                    <i data-lucide="rocket" class="w-5 h-5"></i>
                    {{ __('pages.students.hero_cta_dashboard') }}
                </a>
                @else
                <a href="{{ route('register') }}" class="inline-flex items-center gap-2 px-8 py-4 text-base font-bold text-white rounded-xl hover:opacity-90 transition-all shadow-xl hover:-translate-y-0.5" style="background:linear-gradient(135deg,#9333ea,#7c3aed);">
                    <i data-lucide="rocket" class="w-5 h-5"></i>
                    {{ __('pages.students.hero_cta_register') }}
                </a>
                @endauth
                <a href="{{ locale_url('/pricing') }}" class="inline-flex items-center gap-2 px-6 py-4 text-base font-medium text-gray-500 hover:text-gray-800 transition-colors">
                    {{ __('pages.students.see_pricing') }} <i data-lucide="arrow-right" class="w-4 h-4"></i>
                </a>
            </div>

            <div class="flex flex-wrap items-center justify-center gap-6 text-sm text-gray-400">
                <span class="flex items-center gap-1.5"><i data-lucide="check" class="w-4 h-4 text-green-500"></i>{{ __('pages.students.hero_check_1') }}</span>
                <span class="flex items-center gap-1.5"><i data-lucide="check" class="w-4 h-4 text-green-500"></i>{{ __('pages.students.hero_check_2') }}</span>
                <span class="flex items-center gap-1.5"><i data-lucide="check" class="w-4 h-4 text-green-500"></i>{{ __('pages.students.hero_check_3') }}</span>
            </div>
        </div>
    </div>
</section>

{{-- Stats --}}
<section class="py-12 bg-gray-900">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-8 text-center">
            @php $stats = [
                ['value' => __('pages.students.stat_1_value'), 'label' => __('pages.students.stat_1_label')],
                ['value' => __('pages.students.stat_2_value'), 'label' => __('pages.students.stat_2_label')],
                ['value' => __('pages.students.stat_3_value'), 'label' => __('pages.students.stat_3_label')],
                ['value' => __('pages.students.stat_4_value'), 'label' => __('pages.students.stat_4_label')],
            ]; @endphp
            @foreach ($stats as $s)
            <div>
                <div class="text-3xl font-extrabold text-white mb-1">{{ $s['value'] }}</div>
                <div class="text-gray-400 text-sm">{{ $s['label'] }}</div>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- Features --}}
<section class="py-20 bg-white">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-14 reveal">
            <span class="text-xs font-bold uppercase tracking-[0.2em] text-primary-600 mb-3 block">{{ __('pages.students.features_eyebrow') }}</span>
            <h2 class="text-3xl sm:text-4xl font-extrabold text-gray-900 mb-4">
                {{ __('pages.students.features_title_a') }}<br>
                <span class="font-serif italic font-normal gradient-text">{{ __('pages.students.features_title_b') }}</span>
            </h2>
            <p class="text-gray-500 max-w-xl mx-auto">{{ __('pages.students.features_subtitle') }}</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @php $features = [
                ['icon' => 'route', 'color' => 'bg-purple-100 text-purple-600', 'title' => __('pages.students.feat_lp_title'), 'desc' => __('pages.students.feat_lp_desc')],
                ['icon' => 'music-2', 'color' => 'bg-blue-100 text-blue-600', 'title' => __('pages.students.feat_interval_title'), 'desc' => __('pages.students.feat_interval_desc')],
                ['icon' => 'piano', 'color' => 'bg-pink-100 text-pink-600', 'title' => __('pages.students.feat_chord_title'), 'desc' => __('pages.students.feat_chord_desc')],
                ['icon' => 'layers', 'color' => 'bg-green-100 text-green-600', 'title' => __('pages.students.feat_scale_title'), 'desc' => __('pages.students.feat_scale_desc')],
                ['icon' => 'activity', 'color' => 'bg-orange-100 text-orange-600', 'title' => __('pages.students.feat_rhythm_title'), 'desc' => __('pages.students.feat_rhythm_desc')],
                ['icon' => 'mic', 'color' => 'bg-cyan-100 text-cyan-600', 'title' => __('pages.students.feat_dictation_title'), 'desc' => __('pages.students.feat_dictation_desc')],
                ['icon' => 'sparkles', 'color' => 'bg-violet-100 text-violet-600', 'title' => __('pages.students.feat_ai_title'), 'desc' => __('pages.students.feat_ai_desc')],
                ['icon' => 'trending-up', 'color' => 'bg-amber-100 text-amber-600', 'title' => __('pages.students.feat_progress_title'), 'desc' => __('pages.students.feat_progress_desc')],
                ['icon' => 'gamepad-2', 'color' => 'bg-red-100 text-red-600', 'title' => __('pages.students.feat_games_title'), 'desc' => __('pages.students.feat_games_desc')],
            ]; @endphp
            @foreach ($features as $fi => $feat)
            <div class="bg-gray-50 rounded-2xl border border-gray-100 p-6 reveal" style="transition-delay:{{ $fi * 0.06 }}s">
                <div class="w-11 h-11 rounded-xl {{ $feat['color'] }} flex items-center justify-center mb-4">
                    <i data-lucide="{{ $feat['icon'] }}" class="w-5 h-5"></i>
                </div>
                <h3 class="font-bold text-gray-900 mb-2">{{ $feat['title'] }}</h3>
                <p class="text-gray-500 text-sm leading-relaxed">{{ $feat['desc'] }}</p>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- How It Works --}}
<section class="py-20" style="background:#FAF7F2;">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-14 reveal">
            <span class="text-xs font-bold uppercase tracking-[0.2em] text-primary-600 mb-3 block">{{ __('pages.students.how_eyebrow') }}</span>
            <h2 class="text-3xl sm:text-4xl font-extrabold text-gray-900 mb-4">
                {{ __('pages.students.how_title') }}
            </h2>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            @php $steps = [
                ['num' => '01', 'icon' => 'user-plus', 'title' => __('pages.students.step_1_title'), 'desc' => __('pages.students.step_1_desc')],
                ['num' => '02', 'icon' => 'sliders-horizontal', 'title' => __('pages.students.step_2_title'), 'desc' => __('pages.students.step_2_desc')],
                ['num' => '03', 'icon' => 'calendar-check', 'title' => __('pages.students.step_3_title'), 'desc' => __('pages.students.step_3_desc')],
            ]; @endphp
            @foreach ($steps as $si => $step)
            <div class="text-center reveal" style="transition-delay:{{ $si * 0.1 }}s">
                <div class="relative inline-flex mb-6">
                    <div class="w-16 h-16 rounded-2xl flex items-center justify-center shadow-lg" style="background:linear-gradient(135deg,#9333ea,#7c3aed);">
                        <i data-lucide="{{ $step['icon'] }}" class="w-7 h-7 text-white"></i>
                    </div>
                    <span class="absolute -top-2 -right-2 w-7 h-7 rounded-full bg-gray-900 text-white text-xs font-extrabold flex items-center justify-center">{{ $step['num'] }}</span>
                </div>
                <h3 class="text-lg font-extrabold text-gray-900 mb-3">{{ $step['title'] }}</h3>
                <p class="text-gray-500 text-sm leading-relaxed">{{ $step['desc'] }}</p>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- Testimonials --}}
<section class="py-20 bg-white">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-14 reveal">
            <span class="text-xs font-bold uppercase tracking-[0.2em] text-primary-600 mb-3 block">{{ __('pages.students.testimonials_eyebrow') }}</span>
            <h2 class="text-3xl font-extrabold text-gray-900">{{ __('pages.students.testimonials_title') }}</h2>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            @php $testimonials = [
                [
                    'quote' => __('pages.students.testimonial_1_quote'),
                    'name' => 'Mia Chen',
                    'role' => __('pages.students.testimonial_1_role'),
                    'initials' => 'MC',
                    'color' => 'from-purple-500 to-violet-600',
                ],
                [
                    'quote' => __('pages.students.testimonial_2_quote'),
                    'name' => 'Jordan Williams',
                    'role' => __('pages.students.testimonial_2_role'),
                    'initials' => 'JW',
                    'color' => 'from-orange-500 to-amber-500',
                ],
                [
                    'quote' => __('pages.students.testimonial_3_quote'),
                    'name' => 'Sofia Andersson',
                    'role' => __('pages.students.testimonial_3_role'),
                    'initials' => 'SA',
                    'color' => 'from-cyan-500 to-blue-500',
                ],
            ]; @endphp
            @foreach ($testimonials as $ti => $t)
            <div class="bg-gray-50 rounded-2xl border border-gray-100 p-7 reveal" style="transition-delay:{{ $ti * 0.1 }}s">
                <div class="flex items-center gap-1 mb-4">
                    @for ($i = 0; $i < 5; $i++)
                    <i data-lucide="star" class="w-4 h-4 text-amber-400 fill-current"></i>
                    @endfor
                </div>
                <p class="text-gray-600 text-sm leading-relaxed mb-6 italic">"{{ $t['quote'] }}"</p>
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-gradient-to-br {{ $t['color'] }} flex items-center justify-center text-white text-sm font-bold shrink-0">
                        {{ $t['initials'] }}
                    </div>
                    <div>
                        <div class="font-bold text-gray-900 text-sm">{{ $t['name'] }}</div>
                        <div class="text-gray-400 text-xs">{{ $t['role'] }}</div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- CTA --}}
<section class="py-24 relative overflow-hidden bg-gray-900">
    <div class="absolute -top-20 -right-20 w-[500px] h-[500px] rounded-full blur-3xl pointer-events-none" style="background:rgba(147,51,234,0.2);"></div>
    <div class="absolute -bottom-20 -left-20 w-[400px] h-[400px] rounded-full blur-3xl pointer-events-none" style="background:rgba(249,115,22,0.1);"></div>

    <div class="max-w-3xl mx-auto px-4 sm:px-6 text-center relative reveal">
        <div class="w-16 h-16 mx-auto rounded-2xl flex items-center justify-center mb-8 shadow-xl hero-badge" style="background:linear-gradient(135deg,#9333ea,#7c3aed);">
            <i data-lucide="ear" class="w-8 h-8 text-white"></i>
        </div>
        <h2 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-white mb-5">
            {{ __('pages.students.cta_title_a') }}<br>
            <span class="font-serif italic font-normal" style="background:linear-gradient(135deg,#c084fc,#fb923c);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;">{{ __('pages.students.cta_title_b') }}</span>
        </h2>
        <p class="text-gray-400 text-lg mb-10 max-w-xl mx-auto">
            {{ __('pages.students.cta_subtitle') }}
        </p>
        <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
            @auth
            <a href="{{ url('/dashboard') }}" class="inline-flex items-center gap-2 px-8 py-4 text-base font-bold text-white rounded-xl hover:opacity-90 transition-all shadow-xl hover:-translate-y-0.5" style="background:linear-gradient(135deg,#9333ea,#7c3aed);">
                <i data-lucide="rocket" class="w-5 h-5"></i>
                {{ __('pages.students.hero_cta_dashboard') }}
            </a>
            @else
            <a href="{{ route('register') }}" class="inline-flex items-center gap-2 px-8 py-4 text-base font-bold text-white rounded-xl hover:opacity-90 transition-all shadow-xl hover:-translate-y-0.5" style="background:linear-gradient(135deg,#9333ea,#7c3aed);">
                <i data-lucide="rocket" class="w-5 h-5"></i>
                {{ __('pages.students.hero_cta_register') }}
            </a>
            @endauth
            <a href="{{ locale_url('/pricing') }}" class="inline-flex items-center gap-2 px-6 py-4 text-base font-medium text-gray-400 hover:text-white transition-colors">
                {{ __('pages.students.see_pricing') }} <i data-lucide="arrow-right" class="w-4 h-4"></i>
            </a>
        </div>
    </div>
</section>

@endsection
