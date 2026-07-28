@extends('layouts.standalone')

@section('title', __('pages.partners.meta_title'))
@section('description', __('pages.partners.meta_description'))

@section('content')

{{-- Hero --}}
<section class="py-24 sm:py-32 relative overflow-hidden" style="background: linear-gradient(135deg, #faf5ff 0%, #FAF7F2 60%, #fef3c7 100%);">
    <div class="absolute -top-32 -right-32 w-[500px] h-[500px] rounded-full bg-purple-100/50 blur-3xl pointer-events-none"></div>
    <div class="absolute -bottom-20 -left-20 w-[300px] h-[300px] rounded-full bg-orange-50/60 blur-2xl pointer-events-none"></div>

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center relative">
        <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-purple-100 text-purple-700 text-sm font-semibold mb-6">
            <i data-lucide="handshake" class="w-4 h-4"></i>
            {{ __('pages.partners.hero_badge') }}
        </div>

        <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold text-gray-900 leading-tight mb-6">
            {{ __('pages.partners.hero_title_a') }}<br>
            <span class="font-serif italic font-normal bg-gradient-to-r from-purple-600 to-orange-500 bg-clip-text text-transparent">{{ __('pages.partners.hero_title_b') }}</span>
        </h1>

        <p class="text-gray-600 text-xl leading-relaxed max-w-2xl mx-auto">
            {{ __('pages.partners.hero_subtitle') }}
        </p>
    </div>
</section>

{{-- Partnership Types --}}
<section class="py-20 bg-white">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-14">
            <h2 class="text-3xl sm:text-4xl font-extrabold text-gray-900">{{ __('pages.partners.programs_title') }}</h2>
            <p class="text-gray-500 mt-3 text-lg">{{ __('pages.partners.programs_subtitle') }}</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            @php $types = [
                ['icon' => 'school', 'bg' => 'bg-purple-100', 'fg' => 'text-purple-600', 'btn' => 'bg-purple-600 hover:bg-purple-700', 'title' => __('pages.partners.t_schools_title'), 'desc' => __('pages.partners.t_schools_desc'), 'points' => [__('pages.partners.t_schools_1'), __('pages.partners.t_schools_2'), __('pages.partners.t_schools_3'), __('pages.partners.t_schools_4'), __('pages.partners.t_schools_5')]],
                ['icon' => 'video', 'bg' => 'bg-orange-100', 'fg' => 'text-orange-600', 'btn' => 'bg-orange-500 hover:bg-orange-600', 'title' => __('pages.partners.t_creators_title'), 'desc' => __('pages.partners.t_creators_desc'), 'points' => [__('pages.partners.t_creators_1'), __('pages.partners.t_creators_2'), __('pages.partners.t_creators_3'), __('pages.partners.t_creators_4'), __('pages.partners.t_creators_5')]],
                ['icon' => 'cpu', 'bg' => 'bg-blue-100', 'fg' => 'text-blue-600', 'btn' => 'bg-blue-600 hover:bg-blue-700', 'title' => __('pages.partners.t_tech_title'), 'desc' => __('pages.partners.t_tech_desc'), 'points' => [__('pages.partners.t_tech_1'), __('pages.partners.t_tech_2'), __('pages.partners.t_tech_3'), __('pages.partners.t_tech_4'), __('pages.partners.t_tech_5')]],
            ]; @endphp
            @foreach ($types as $type)
            <div class="bg-[#FAF7F2] rounded-2xl p-8 border border-gray-100 flex flex-col">
                <div class="w-14 h-14 {{ $type['bg'] }} rounded-xl flex items-center justify-center mb-6">
                    <i data-lucide="{{ $type['icon'] }}" class="w-7 h-7 {{ $type['fg'] }}"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-3">{{ $type['title'] }}</h3>
                <p class="text-gray-600 leading-relaxed mb-6 text-sm flex-grow">
                    {{ $type['desc'] }}
                </p>
                <ul class="space-y-2.5 mb-8">
                    @foreach ($type['points'] as $point)
                    <li class="flex items-start gap-2.5 text-sm text-gray-700">
                        <i data-lucide="check-circle" class="w-4 h-4 text-green-500 flex-shrink-0 mt-0.5"></i>
                        {{ $point }}
                    </li>
                    @endforeach
                </ul>
                <a href="#partner-form" class="inline-flex items-center justify-center gap-2 {{ $type['btn'] }} text-white font-semibold px-6 py-3 rounded-xl transition-colors duration-200 w-full">
                    {{ __('pages.partners.apply') }} <i data-lucide="arrow-right" class="w-4 h-4"></i>
                </a>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- Why Partner --}}
<section class="py-20 bg-gray-900 text-white">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-3xl sm:text-4xl font-extrabold mb-3">{{ __('pages.partners.why_title') }}</h2>
            <p class="text-gray-400 text-lg">{{ __('pages.partners.why_subtitle') }}</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            @php $why = [
                ['icon' => 'megaphone', 'bg' => 'bg-purple-600/30', 'fg' => 'text-purple-400', 'title' => __('pages.partners.why_comarketing_title'), 'desc' => __('pages.partners.why_comarketing_desc')],
                ['icon' => 'dollar-sign', 'bg' => 'bg-orange-500/30', 'fg' => 'text-orange-400', 'title' => __('pages.partners.why_revenue_title'), 'desc' => __('pages.partners.why_revenue_desc')],
                ['icon' => 'life-buoy', 'bg' => 'bg-green-500/30', 'fg' => 'text-green-400', 'title' => __('pages.partners.why_support_title'), 'desc' => __('pages.partners.why_support_desc')],
                ['icon' => 'zap', 'bg' => 'bg-blue-500/30', 'fg' => 'text-blue-400', 'title' => __('pages.partners.why_early_title'), 'desc' => __('pages.partners.why_early_desc')],
            ]; @endphp
            @foreach ($why as $item)
            <div class="bg-white/5 rounded-2xl p-6 border border-white/10 hover:bg-white/10 transition-colors duration-300">
                <div class="w-12 h-12 {{ $item['bg'] }} rounded-xl flex items-center justify-center mb-4">
                    <i data-lucide="{{ $item['icon'] }}" class="w-6 h-6 {{ $item['fg'] }}"></i>
                </div>
                <h3 class="font-bold text-white text-lg mb-2">{{ $item['title'] }}</h3>
                <p class="text-gray-400 text-sm leading-relaxed">
                    {{ $item['desc'] }}
                </p>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- Partner Inquiry Form --}}
<section id="partner-form" class="py-20 bg-white">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-10">
            <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-purple-100 text-purple-700 text-sm font-semibold mb-4">
                <i data-lucide="send" class="w-4 h-4"></i>
                {{ __('pages.partners.form_badge') }}
            </div>
            <h2 class="text-3xl font-extrabold text-gray-900 mb-3">{{ __('pages.partners.form_title') }}</h2>
            <p class="text-gray-500 text-lg">{{ __('pages.partners.form_subtitle') }}</p>
        </div>

        <form action="#" method="POST" class="bg-[#FAF7F2] rounded-2xl p-8 border border-gray-100 space-y-5">
            @csrf
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label for="name" class="block text-sm font-semibold text-gray-700 mb-1.5">{{ __('pages.partners.label_name') }}</label>
                    <input type="text" id="name" name="name" required
                        class="w-full px-4 py-3 rounded-xl border border-gray-200 bg-white text-gray-900 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-shadow"
                        placeholder="{{ __('pages.partners.ph_name') }}">
                </div>
                <div>
                    <label for="email" class="block text-sm font-semibold text-gray-700 mb-1.5">{{ __('pages.partners.label_email') }}</label>
                    <input type="email" id="email" name="email" required
                        class="w-full px-4 py-3 rounded-xl border border-gray-200 bg-white text-gray-900 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-shadow"
                        placeholder="{{ __('pages.partners.ph_email') }}">
                </div>
            </div>

            <div>
                <label for="organization" class="block text-sm font-semibold text-gray-700 mb-1.5">{{ __('pages.partners.label_org') }}</label>
                <input type="text" id="organization" name="organization" required
                    class="w-full px-4 py-3 rounded-xl border border-gray-200 bg-white text-gray-900 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-shadow"
                    placeholder="{{ __('pages.partners.ph_org') }}">
            </div>

            <div>
                <label for="partnership_type" class="block text-sm font-semibold text-gray-700 mb-1.5">{{ __('pages.partners.label_type') }}</label>
                <select id="partnership_type" name="partnership_type" required
                    class="w-full px-4 py-3 rounded-xl border border-gray-200 bg-white text-gray-900 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-shadow">
                    <option value="" disabled selected>{{ __('pages.partners.type_select') }}</option>
                    <option value="school">{{ __('pages.partners.type_school') }}</option>
                    <option value="creator">{{ __('pages.partners.type_creator') }}</option>
                    <option value="technology">{{ __('pages.partners.type_technology') }}</option>
                    <option value="other">{{ __('pages.partners.type_other') }}</option>
                </select>
            </div>

            <div>
                <label for="message" class="block text-sm font-semibold text-gray-700 mb-1.5">{{ __('pages.partners.label_message') }}</label>
                <textarea id="message" name="message" rows="5" required
                    class="w-full px-4 py-3 rounded-xl border border-gray-200 bg-white text-gray-900 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-shadow resize-none"
                    placeholder="{{ __('pages.partners.ph_message') }}"></textarea>
            </div>

            <button type="submit"
                class="w-full inline-flex items-center justify-center gap-2 bg-purple-600 hover:bg-purple-700 text-white font-bold px-6 py-3.5 rounded-xl transition-colors duration-200 text-lg">
                <i data-lucide="send" class="w-5 h-5"></i>
                {{ __('pages.partners.submit') }}
            </button>

            <p class="text-center text-sm text-gray-500">
                {!! __('pages.partners.form_direct', ['email' => '<a href="mailto:partners@harmoniva.app" class="text-purple-600 font-medium hover:text-purple-700 transition-colors">partners@harmoniva.app</a>']) !!}
            </p>
        </form>
    </div>
</section>

@endsection
