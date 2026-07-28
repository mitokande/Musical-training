@extends('layouts.standalone')

@section('title', __('pages.about.meta_title'))
@section('description', __('pages.about.meta_description'))

@section('content')

{{-- Hero --}}
<section class="py-24 sm:py-32 relative overflow-hidden" style="background: linear-gradient(135deg, #faf5ff 0%, #FAF7F2 60%, #fef3c7 100%);">
    <div class="absolute -top-32 -right-32 w-[500px] h-[500px] rounded-full bg-purple-100/50 blur-3xl pointer-events-none"></div>
    <div class="absolute -bottom-20 -left-20 w-[300px] h-[300px] rounded-full bg-orange-50/60 blur-2xl pointer-events-none"></div>

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center relative">
        <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-purple-100 text-purple-700 text-sm font-semibold mb-6">
            <i data-lucide="music" class="w-4 h-4"></i>
            {{ __('pages.about.hero_badge') }}
        </div>

        <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold text-gray-900 leading-tight mb-6">
            {{ __('pages.about.hero_title_a') }}<br>
            <span class="font-serif italic font-normal bg-gradient-to-r from-purple-600 to-orange-500 bg-clip-text text-transparent">{{ __('pages.about.hero_title_b') }}</span>
        </h1>

        <p class="text-gray-600 text-xl leading-relaxed max-w-2xl mx-auto">
            {{ __('pages.about.hero_subtitle') }}
        </p>
    </div>
</section>

{{-- Mission Statement --}}
<section class="py-20 bg-white">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <p class="text-xl text-gray-700 leading-relaxed mb-6">
            {{ __('pages.about.mission_p1') }}
        </p>
        <p class="text-lg text-gray-600 leading-relaxed">
            {{ __('pages.about.mission_p2') }}
        </p>
    </div>
</section>

{{-- Story Section --}}
<section class="py-20 bg-[#FAF7F2]">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-16 items-center">
            <div>
                <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-purple-100 text-purple-700 text-sm font-semibold mb-5">
                    <i data-lucide="book-open" class="w-4 h-4"></i>
                    {{ __('pages.about.story_badge') }}
                </div>
                <h2 class="text-3xl sm:text-4xl font-extrabold text-gray-900 mb-6">{{ __('pages.about.story_title') }}</h2>

                <div class="space-y-5 text-gray-700 leading-relaxed">
                    <p>{{ __('pages.about.story_p1') }}</p>
                    <p>{{ __('pages.about.story_p2') }}</p>
                    <p>{{ __('pages.about.story_p3') }}</p>
                </div>
            </div>

            <div class="relative">
                <div class="bg-white rounded-2xl shadow-xl p-8 border border-gray-100">
                    <div class="flex items-center gap-4 mb-6">
                        <div class="w-12 h-12 bg-gradient-to-br from-purple-500 to-purple-700 rounded-xl flex items-center justify-center">
                            <i data-lucide="target" class="w-6 h-6 text-white"></i>
                        </div>
                        <div>
                            <div class="font-bold text-gray-900 text-lg">{{ __('pages.about.mission_card_title') }}</div>
                            <div class="text-gray-500 text-sm">{{ __('pages.about.mission_card_subtitle') }}</div>
                        </div>
                    </div>
                    <p class="text-gray-700 leading-relaxed italic text-lg">
                        {{ __('pages.about.mission_card_quote') }}
                    </p>
                </div>

                <div class="bg-white rounded-2xl shadow-xl p-8 border border-gray-100 mt-6">
                    <div class="flex items-center gap-4 mb-6">
                        <div class="w-12 h-12 bg-gradient-to-br from-orange-400 to-orange-600 rounded-xl flex items-center justify-center">
                            <i data-lucide="lightbulb" class="w-6 h-6 text-white"></i>
                        </div>
                        <div>
                            <div class="font-bold text-gray-900 text-lg">{{ __('pages.about.vision_card_title') }}</div>
                            <div class="text-gray-500 text-sm">{{ __('pages.about.vision_card_subtitle') }}</div>
                        </div>
                    </div>
                    <p class="text-gray-700 leading-relaxed">
                        {{ __('pages.about.vision_card_text') }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- Values --}}
<section class="py-20 bg-white">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-14">
            <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-orange-100 text-orange-700 text-sm font-semibold mb-4">
                <i data-lucide="heart" class="w-4 h-4"></i>
                {{ __('pages.about.values_badge') }}
            </div>
            <h2 class="text-3xl sm:text-4xl font-extrabold text-gray-900">{{ __('pages.about.values_title') }}</h2>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            @php $values = [
                ['icon' => 'sparkles', 'bg' => 'bg-purple-100', 'fg' => 'text-purple-600', 'title' => __('pages.about.value_innovation_title'), 'desc' => __('pages.about.value_innovation_desc')],
                ['icon' => 'globe', 'bg' => 'bg-green-100', 'fg' => 'text-green-600', 'title' => __('pages.about.value_accessibility_title'), 'desc' => __('pages.about.value_accessibility_desc')],
                ['icon' => 'award', 'bg' => 'bg-orange-100', 'fg' => 'text-orange-600', 'title' => __('pages.about.value_excellence_title'), 'desc' => __('pages.about.value_excellence_desc')],
                ['icon' => 'users', 'bg' => 'bg-blue-100', 'fg' => 'text-blue-600', 'title' => __('pages.about.value_community_title'), 'desc' => __('pages.about.value_community_desc')],
            ]; @endphp
            @foreach ($values as $value)
            <div class="bg-[#FAF7F2] rounded-2xl p-7 border border-gray-100 hover:shadow-lg transition-shadow duration-300">
                <div class="w-12 h-12 {{ $value['bg'] }} rounded-xl flex items-center justify-center mb-5">
                    <i data-lucide="{{ $value['icon'] }}" class="w-6 h-6 {{ $value['fg'] }}"></i>
                </div>
                <h3 class="font-bold text-gray-900 text-lg mb-3">{{ $value['title'] }}</h3>
                <p class="text-gray-600 leading-relaxed text-sm">
                    {{ $value['desc'] }}
                </p>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- Stats Strip --}}
<section class="py-16 bg-gray-900 text-white">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-8 text-center">
            <div>
                <div class="text-4xl font-extrabold text-purple-400 mb-2">{{ __('pages.about.stat_1_value') }}</div>
                <div class="text-gray-400 text-sm font-medium">{{ __('pages.about.stat_1_label') }}</div>
            </div>
            <div>
                <div class="text-4xl font-extrabold text-orange-400 mb-2">{{ __('pages.about.stat_2_value') }}</div>
                <div class="text-gray-400 text-sm font-medium">{{ __('pages.about.stat_2_label') }}</div>
            </div>
            <div>
                <div class="text-4xl font-extrabold text-purple-400 mb-2">{{ __('pages.about.stat_3_value') }}</div>
                <div class="text-gray-400 text-sm font-medium">{{ __('pages.about.stat_3_label') }}</div>
            </div>
            <div>
                <div class="text-4xl font-extrabold text-orange-400 mb-2">{{ __('pages.about.stat_4_value') }}</div>
                <div class="text-gray-400 text-sm font-medium">{{ __('pages.about.stat_4_label') }}</div>
            </div>
        </div>
    </div>
</section>

{{-- Team Ethos --}}
<section class="py-20 bg-white">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-purple-100 text-purple-700 text-sm font-semibold mb-5">
            <i data-lucide="headphones" class="w-4 h-4"></i>
            {{ __('pages.about.team_badge') }}
        </div>
        <h2 class="text-3xl sm:text-4xl font-extrabold text-gray-900 mb-6">{{ __('pages.about.team_title') }}</h2>
        <p class="text-lg text-gray-600 leading-relaxed mb-5">
            {{ __('pages.about.team_p1') }}
        </p>
        <p class="text-gray-600 leading-relaxed">
            {{ __('pages.about.team_p2') }}
        </p>
    </div>
</section>

{{-- CTA --}}
<section class="py-20" style="background: linear-gradient(135deg, #7e22ce 0%, #9333ea 50%, #c026d3 100%);">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h2 class="text-3xl sm:text-4xl font-extrabold text-white mb-4">{{ __('pages.about.cta_title') }}</h2>
        <p class="text-purple-200 text-lg mb-8">{{ __('pages.about.cta_subtitle') }}</p>
        <a href="{{ route('register') }}" class="inline-flex items-center gap-2 bg-white text-purple-700 font-bold px-8 py-4 rounded-xl hover:bg-purple-50 transition-colors duration-200 shadow-lg text-lg">
            <i data-lucide="music" class="w-5 h-5"></i>
            {{ __('pages.about.cta_button') }}
        </a>
    </div>
</section>

@endsection
