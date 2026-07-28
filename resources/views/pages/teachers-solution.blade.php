@extends('layouts.standalone')

@section('title', __('pages.teachers.meta_title'))
@section('description', __('pages.teachers.meta_description'))

@section('content')

{{-- Hero --}}
<section class="py-20 sm:py-28 relative overflow-hidden" style="background: linear-gradient(135deg, #fff7ed 0%, #FAF7F2 60%, #fef3c7 100%);">
    <div class="absolute -top-32 -right-32 w-[600px] h-[600px] rounded-full bg-orange-100/50 blur-3xl pointer-events-none"></div>
    <div class="absolute -bottom-20 -left-20 w-[350px] h-[350px] rounded-full bg-amber-50/60 blur-2xl pointer-events-none"></div>

    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 relative">
        <div class="text-center max-w-3xl mx-auto">
            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-orange-100 text-orange-700 text-sm font-semibold mb-6 hero-badge">
                <i data-lucide="users" class="w-4 h-4"></i>
                {{ __('pages.teachers.hero_badge') }}
            </div>

            <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold text-gray-900 leading-tight mb-6">
                {{ __('pages.teachers.hero_title_a') }}<br>
                <span class="font-serif italic font-normal" style="background:linear-gradient(135deg,#ea580c,#f97316);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;">{{ __('pages.teachers.hero_title_b') }}</span>
            </h1>

            <p class="text-gray-500 text-lg max-w-2xl mx-auto mb-10">
                {{ __('pages.teachers.hero_subtitle') }}
            </p>

            <div class="flex flex-col sm:flex-row items-center justify-center gap-4 mb-10">
                @auth
                <a href="{{ url('/dashboard') }}" class="inline-flex items-center gap-2 px-8 py-4 text-base font-bold text-white rounded-xl hover:opacity-90 transition-all shadow-xl hover:-translate-y-0.5" style="background:linear-gradient(135deg,#ea580c,#f97316);">
                    <i data-lucide="rocket" class="w-5 h-5"></i>
                    {{ __('pages.teachers.hero_cta_dashboard') }}
                </a>
                @else
                <a href="{{ route('register') }}" class="inline-flex items-center gap-2 px-8 py-4 text-base font-bold text-white rounded-xl hover:opacity-90 transition-all shadow-xl hover:-translate-y-0.5" style="background:linear-gradient(135deg,#ea580c,#f97316);">
                    <i data-lucide="rocket" class="w-5 h-5"></i>
                    {{ __('pages.teachers.hero_cta_register') }}
                </a>
                @endauth
                <a href="{{ locale_url('/pricing/teachers-and-schools') }}" class="inline-flex items-center gap-2 px-6 py-4 text-base font-semibold text-gray-700 bg-white border border-gray-200 rounded-xl hover:border-accent-400 hover:text-accent-600 transition-all shadow-sm">
                    {{ __('pages.teachers.view_plan') }} <i data-lucide="arrow-right" class="w-4 h-4"></i>
                </a>
            </div>

            <div class="flex flex-wrap items-center justify-center gap-6 text-sm text-gray-400">
                <span class="flex items-center gap-1.5"><i data-lucide="check" class="w-4 h-4 text-green-500"></i>{{ __('pages.teachers.hero_check_1') }}</span>
                <span class="flex items-center gap-1.5"><i data-lucide="check" class="w-4 h-4 text-green-500"></i>{{ __('pages.teachers.hero_check_2') }}</span>
                <span class="flex items-center gap-1.5"><i data-lucide="check" class="w-4 h-4 text-green-500"></i>{{ __('pages.teachers.hero_check_3') }}</span>
            </div>
        </div>
    </div>
</section>

{{-- Feature Highlights --}}
<section class="py-20 bg-white">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-14 reveal">
            <span class="text-xs font-bold uppercase tracking-[0.2em] text-accent-600 mb-3 block">{{ __('pages.teachers.features_eyebrow') }}</span>
            <h2 class="text-3xl sm:text-4xl font-extrabold text-gray-900 mb-4">
                {{ __('pages.teachers.features_title_a') }}<br>
                <span class="font-serif italic font-normal" style="background:linear-gradient(135deg,#ea580c,#f97316);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;">{{ __('pages.teachers.features_title_b') }}</span>
            </h2>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            @php $features = [
                ['icon' => 'send', 'color' => 'bg-orange-100 text-orange-600', 'title' => __('pages.teachers.feat_assign_title'), 'desc' => __('pages.teachers.feat_assign_desc')],
                ['icon' => 'bar-chart-3', 'color' => 'bg-blue-100 text-blue-600', 'title' => __('pages.teachers.feat_track_title'), 'desc' => __('pages.teachers.feat_track_desc')],
                ['icon' => 'users', 'color' => 'bg-purple-100 text-purple-600', 'title' => __('pages.teachers.feat_classes_title'), 'desc' => __('pages.teachers.feat_classes_desc')],
                ['icon' => 'sparkles', 'color' => 'bg-cyan-100 text-cyan-600', 'title' => __('pages.teachers.feat_ai_title'), 'desc' => __('pages.teachers.feat_ai_desc')],
                ['icon' => 'file-text', 'color' => 'bg-green-100 text-green-600', 'title' => __('pages.teachers.feat_export_title'), 'desc' => __('pages.teachers.feat_export_desc')],
                ['icon' => 'sliders-horizontal', 'color' => 'bg-amber-100 text-amber-600', 'title' => __('pages.teachers.feat_config_title'), 'desc' => __('pages.teachers.feat_config_desc')],
            ]; @endphp
            @foreach ($features as $fi => $feat)
            <div class="flex items-start gap-5 p-6 bg-gray-50 rounded-2xl border border-gray-100 reveal" style="transition-delay:{{ $fi * 0.07 }}s">
                <div class="w-12 h-12 rounded-xl {{ $feat['color'] }} flex items-center justify-center shrink-0">
                    <i data-lucide="{{ $feat['icon'] }}" class="w-6 h-6"></i>
                </div>
                <div>
                    <h3 class="font-bold text-gray-900 mb-2">{{ $feat['title'] }}</h3>
                    <p class="text-gray-500 text-sm leading-relaxed">{{ $feat['desc'] }}</p>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- Comparison: With vs Without --}}
<section class="py-20" style="background:#FAF7F2;">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12 reveal">
            <span class="text-xs font-bold uppercase tracking-[0.2em] text-accent-600 mb-3 block">{{ __('pages.teachers.comp_eyebrow') }}</span>
            <h2 class="text-3xl font-extrabold text-gray-900 mb-3">
                {{ __('pages.teachers.comp_title_a') }}<br>
                <span class="font-serif italic font-normal" style="background:linear-gradient(135deg,#ea580c,#f97316);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;">{{ __('pages.teachers.comp_title_b') }}</span>
            </h2>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 reveal">
            {{-- Without --}}
            <div class="bg-white rounded-2xl border border-gray-200 p-7">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-10 h-10 rounded-xl bg-gray-100 flex items-center justify-center">
                        <i data-lucide="x-circle" class="w-5 h-5 text-gray-400"></i>
                    </div>
                    <h3 class="font-extrabold text-gray-400">{{ __('pages.teachers.without_title') }}</h3>
                </div>
                <ul class="space-y-4">
                    @php $withoutItems = [
                        __('pages.teachers.without_1'),
                        __('pages.teachers.without_2'),
                        __('pages.teachers.without_3'),
                        __('pages.teachers.without_4'),
                        __('pages.teachers.without_5'),
                        __('pages.teachers.without_6'),
                    ]; @endphp
                    @foreach ($withoutItems as $item)
                    <li class="flex items-start gap-3 text-sm text-gray-400">
                        <i data-lucide="minus" class="w-4 h-4 text-gray-300 shrink-0 mt-0.5"></i>
                        <span>{{ $item }}</span>
                    </li>
                    @endforeach
                </ul>
            </div>

            {{-- With --}}
            <div class="rounded-2xl p-7 text-white relative overflow-hidden" style="background:linear-gradient(135deg,#ea580c,#f97316);">
                <div class="absolute -top-10 -right-10 w-40 h-40 rounded-full bg-white/10 blur-2xl pointer-events-none"></div>
                <div class="flex items-center gap-3 mb-6 relative">
                    <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center">
                        <i data-lucide="check-circle" class="w-5 h-5 text-white"></i>
                    </div>
                    <h3 class="font-extrabold text-white">{{ __('pages.teachers.with_title') }}</h3>
                </div>
                <ul class="space-y-4 relative">
                    @php $withItems = [
                        __('pages.teachers.with_1'),
                        __('pages.teachers.with_2'),
                        __('pages.teachers.with_3'),
                        __('pages.teachers.with_4'),
                        __('pages.teachers.with_5'),
                        __('pages.teachers.with_6'),
                    ]; @endphp
                    @foreach ($withItems as $item)
                    <li class="flex items-start gap-3 text-sm text-white/90">
                        <i data-lucide="check" class="w-4 h-4 text-white shrink-0 mt-0.5"></i>
                        <span>{{ $item }}</span>
                    </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
</section>

{{-- Plan Highlight --}}
<section class="py-20 bg-white">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <div class="text-center mb-10 reveal">
            <span class="text-xs font-bold uppercase tracking-[0.2em] text-accent-600 mb-3 block">{{ __('pages.teachers.plan_eyebrow') }}</span>
            <h2 class="text-3xl font-extrabold text-gray-900 mb-3">
                {{ __('pages.teachers.plan_title') }}
            </h2>
            <p class="text-gray-500">{{ __('pages.teachers.plan_subtitle') }}</p>
        </div>

        <div class="bg-white rounded-3xl border border-orange-200 shadow-xl p-8 max-w-sm mx-auto reveal" style="transition-delay:0.1s">
            <span class="inline-block px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider text-white mb-6" style="background:linear-gradient(135deg,#ea580c,#f97316);">{{ __('pages.teachers.plan_badge') }}</span>

            <div class="flex items-end justify-center gap-1 mb-1">
                <span class="text-5xl font-extrabold text-gray-900">$16.90</span>
                <span class="text-gray-400 text-base mb-2">{{ __('pages.teachers.plan_per_month') }}</span>
            </div>
            <p class="text-gray-400 text-sm mb-2">{!! __('pages.teachers.plan_annual', ['price' => '<strong class="text-gray-700">$6.67</strong>']) !!}</p>
            <span class="inline-block px-3 py-1 rounded-full text-xs font-bold bg-orange-100 text-orange-700 mb-6">{{ __('pages.teachers.plan_save') }}</span>

            <ul class="space-y-3 mb-8 text-left">
                @php $planFeats = [
                    __('pages.teachers.planfeat_1'),
                    __('pages.teachers.planfeat_2'),
                    __('pages.teachers.planfeat_3'),
                    __('pages.teachers.planfeat_4'),
                    __('pages.teachers.planfeat_5'),
                    __('pages.teachers.planfeat_6'),
                    __('pages.teachers.planfeat_7'),
                    __('pages.teachers.planfeat_8'),
                ]; @endphp
                @foreach ($planFeats as $pf)
                <li class="flex items-center gap-3 text-sm text-gray-700">
                    <i data-lucide="check" class="w-4 h-4 text-green-500 shrink-0"></i>
                    {{ $pf }}
                </li>
                @endforeach
            </ul>

            <div class="space-y-3">
                @auth
                <a href="{{ url('/dashboard') }}" class="block w-full py-3.5 text-center text-sm font-bold text-white rounded-xl hover:opacity-90 transition-all" style="background:linear-gradient(135deg,#ea580c,#f97316);">
                    {{ __('pages.teachers.plan_cta_dashboard') }}
                </a>
                @else
                <a href="{{ route('register') }}" class="block w-full py-3.5 text-center text-sm font-bold text-white rounded-xl hover:opacity-90 transition-all" style="background:linear-gradient(135deg,#ea580c,#f97316);">
                    {{ __('pages.teachers.plan_cta_register') }}
                </a>
                @endauth
                <a href="{{ locale_url('/pricing/teachers-and-schools') }}" class="block w-full py-3.5 text-center text-sm font-semibold text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-xl transition-colors">
                    {{ __('pages.teachers.plan_view_details') }} →
                </a>
            </div>
        </div>
    </div>
</section>

{{-- CTA --}}
<section class="py-24 relative overflow-hidden" style="background: linear-gradient(135deg, #fff7ed 0%, #FAF7F2 50%, #fef3c7 100%);">
    <div class="absolute -top-20 -right-20 w-[400px] h-[400px] rounded-full bg-orange-100/50 blur-3xl pointer-events-none"></div>
    <div class="max-w-3xl mx-auto px-4 sm:px-6 text-center relative reveal">
        <div class="w-16 h-16 mx-auto rounded-2xl flex items-center justify-center mb-8 shadow-xl hero-badge" style="background:linear-gradient(135deg,#ea580c,#f97316);">
            <i data-lucide="graduation-cap" class="w-8 h-8 text-white"></i>
        </div>
        <h2 class="text-3xl sm:text-4xl font-extrabold text-gray-900 mb-5">
            {{ __('pages.teachers.cta_title_a') }}<br>
            <span class="font-serif italic font-normal" style="background:linear-gradient(135deg,#ea580c,#f97316);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;">{{ __('pages.teachers.cta_title_b') }}</span>
        </h2>
        <p class="text-gray-500 text-lg mb-10 max-w-xl mx-auto">
            {{ __('pages.teachers.cta_subtitle') }}
        </p>
        <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
            @auth
            <a href="{{ url('/dashboard') }}" class="inline-flex items-center gap-2 px-8 py-4 text-base font-bold text-white rounded-xl hover:opacity-90 transition-all shadow-xl hover:-translate-y-0.5" style="background:linear-gradient(135deg,#ea580c,#f97316);">
                <i data-lucide="rocket" class="w-5 h-5"></i>
                {{ __('pages.teachers.hero_cta_dashboard') }}
            </a>
            @else
            <a href="{{ route('register') }}" class="inline-flex items-center gap-2 px-8 py-4 text-base font-bold text-white rounded-xl hover:opacity-90 transition-all shadow-xl hover:-translate-y-0.5" style="background:linear-gradient(135deg,#ea580c,#f97316);">
                <i data-lucide="rocket" class="w-5 h-5"></i>
                {{ __('pages.teachers.hero_cta_register') }}
            </a>
            @endauth
            <a href="{{ locale_url('/pricing/teachers-and-schools') }}" class="inline-flex items-center gap-2 px-6 py-4 text-base font-medium text-gray-600 hover:text-gray-900 transition-colors">
                {{ __('pages.teachers.view_plan') }} <i data-lucide="arrow-right" class="w-4 h-4"></i>
            </a>
        </div>
    </div>
</section>

@endsection
