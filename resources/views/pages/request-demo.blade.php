@extends('layouts.standalone')

@section('title', __('pages.request_demo.meta_title'))
@section('description', __('pages.request_demo.meta_description'))

@section('content')

{{-- Hero --}}
<section class="py-20 sm:py-24 relative overflow-hidden" style="background: linear-gradient(135deg, #fff7ed 0%, #FAF7F2 60%, #fef3c7 100%);">
    <div class="absolute -top-32 -right-32 w-[500px] h-[500px] rounded-full bg-orange-100/50 blur-3xl pointer-events-none"></div>
    <div class="absolute -bottom-20 -left-20 w-[300px] h-[300px] rounded-full bg-amber-50/60 blur-2xl pointer-events-none"></div>

    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 text-center relative">
        <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-orange-100 text-orange-700 text-sm font-semibold mb-6 hero-badge">
            <i data-lucide="calendar" class="w-4 h-4"></i>
            {{ __('pages.request_demo.hero_badge') }}
        </div>

        <h1 class="text-4xl sm:text-5xl font-extrabold text-gray-900 leading-tight mb-5">
            {{ __('pages.request_demo.hero_title_a') }}<br>
            <span class="font-serif italic font-normal" style="background:linear-gradient(135deg,#ea580c,#f97316);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;">{{ __('pages.request_demo.hero_title_b') }}</span>
        </h1>

        <p class="text-gray-500 text-lg max-w-xl mx-auto">
            {{ __('pages.request_demo.hero_subtitle') }}
        </p>
    </div>
</section>

{{-- Form + What to Expect --}}
<section class="py-20 bg-white">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-5 gap-10 items-start">

            {{-- Form (3 columns) --}}
            <div class="lg:col-span-3 reveal">
                <div class="bg-white rounded-3xl border border-gray-200 shadow-sm p-8">
                    <h2 class="text-2xl font-extrabold text-gray-900 mb-2">{{ __('pages.request_demo.form_title') }}</h2>
                    <p class="text-gray-400 text-sm mb-8">{{ __('pages.request_demo.form_subtitle') }}</p>

                    <form action="#" method="POST" class="space-y-5">
                        @csrf

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                            <div>
                                <label for="name" class="block text-sm font-semibold text-gray-700 mb-1.5">{{ __('pages.request_demo.label_name') }} <span class="text-red-400">*</span></label>
                                <input type="text" id="name" name="name" required placeholder="{{ __('pages.request_demo.ph_name') }}"
                                    class="w-full px-4 py-3 rounded-xl border border-gray-200 bg-gray-50 text-gray-900 text-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-orange-400 focus:border-transparent transition">
                            </div>
                            <div>
                                <label for="email" class="block text-sm font-semibold text-gray-700 mb-1.5">{{ __('pages.request_demo.label_email') }} <span class="text-red-400">*</span></label>
                                <input type="email" id="email" name="email" required placeholder="{{ __('pages.request_demo.ph_email') }}"
                                    class="w-full px-4 py-3 rounded-xl border border-gray-200 bg-gray-50 text-gray-900 text-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-orange-400 focus:border-transparent transition">
                            </div>
                        </div>

                        <div>
                            <label for="organization" class="block text-sm font-semibold text-gray-700 mb-1.5">{{ __('pages.request_demo.label_org') }} <span class="text-red-400">*</span></label>
                            <input type="text" id="organization" name="organization" required placeholder="{{ __('pages.request_demo.ph_org') }}"
                                class="w-full px-4 py-3 rounded-xl border border-gray-200 bg-gray-50 text-gray-900 text-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-orange-400 focus:border-transparent transition">
                        </div>

                        <div>
                            <label for="role" class="block text-sm font-semibold text-gray-700 mb-1.5">{{ __('pages.request_demo.label_role') }} <span class="text-red-400">*</span></label>
                            <div class="relative">
                                <select id="role" name="role" required
                                    class="w-full px-4 py-3 rounded-xl border border-gray-200 bg-gray-50 text-gray-900 text-sm appearance-none focus:outline-none focus:ring-2 focus:ring-orange-400 focus:border-transparent transition">
                                    <option value="" disabled selected>{{ __('pages.request_demo.role_select') }}</option>
                                    <option value="teacher">{{ __('pages.request_demo.role_teacher') }}</option>
                                    <option value="school_admin">{{ __('pages.request_demo.role_school_admin') }}</option>
                                    <option value="music_director">{{ __('pages.request_demo.role_music_director') }}</option>
                                    <option value="department_head">{{ __('pages.request_demo.role_department_head') }}</option>
                                    <option value="other">{{ __('pages.request_demo.role_other') }}</option>
                                </select>
                                <i data-lucide="chevron-down" class="w-4 h-4 text-gray-400 absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none"></i>
                            </div>
                        </div>

                        <div>
                            <label for="students" class="block text-sm font-semibold text-gray-700 mb-1.5">{{ __('pages.request_demo.label_students') }}</label>
                            <div class="relative">
                                <select id="students" name="students"
                                    class="w-full px-4 py-3 rounded-xl border border-gray-200 bg-gray-50 text-gray-900 text-sm appearance-none focus:outline-none focus:ring-2 focus:ring-orange-400 focus:border-transparent transition">
                                    <option value="" disabled selected>{{ __('pages.request_demo.students_select') }}</option>
                                    <option value="1-25">{{ __('pages.request_demo.students_1') }}</option>
                                    <option value="26-100">{{ __('pages.request_demo.students_2') }}</option>
                                    <option value="101-500">{{ __('pages.request_demo.students_3') }}</option>
                                    <option value="500+">{{ __('pages.request_demo.students_4') }}</option>
                                </select>
                                <i data-lucide="chevron-down" class="w-4 h-4 text-gray-400 absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none"></i>
                            </div>
                        </div>

                        <div>
                            <label for="message" class="block text-sm font-semibold text-gray-700 mb-1.5">{{ __('pages.request_demo.label_message') }}</label>
                            <textarea id="message" name="message" rows="4" placeholder="{{ __('pages.request_demo.ph_message') }}"
                                class="w-full px-4 py-3 rounded-xl border border-gray-200 bg-gray-50 text-gray-900 text-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-orange-400 focus:border-transparent transition resize-none"></textarea>
                        </div>

                        <button type="submit" class="w-full py-4 text-base font-bold text-white rounded-xl hover:opacity-90 transition-all shadow-lg hover:-translate-y-0.5" style="background:linear-gradient(135deg,#ea580c,#f97316);">
                            <span class="inline-flex items-center justify-center gap-2">
                                <i data-lucide="send" class="w-5 h-5"></i>
                                {{ __('pages.request_demo.submit') }}
                            </span>
                        </button>

                        <p class="text-xs text-gray-400 text-center">
                            {!! __('pages.request_demo.privacy_note', [
                                'privacy_link' => '<a href="'.locale_url('/privacy-policy').'" class="underline hover:text-gray-600 transition-colors">'.__('pages.request_demo.privacy_link_text').'</a>',
                            ]) !!}
                        </p>
                    </form>
                </div>
            </div>

            {{-- What to Expect (2 columns) --}}
            <div class="lg:col-span-2 space-y-6">

                <div class="reveal" style="transition-delay:0.1s">
                    <h3 class="text-lg font-extrabold text-gray-900 mb-5">{{ __('pages.request_demo.next_title') }}</h3>
                    <div class="space-y-5">
                        @php $steps = [
                            ['num' => '1', 'icon' => 'mail', 'color' => 'bg-orange-100 text-orange-600', 'title' => __('pages.request_demo.step_1_title'), 'desc' => __('pages.request_demo.step_1_desc')],
                            ['num' => '2', 'icon' => 'video', 'color' => 'bg-purple-100 text-purple-600', 'title' => __('pages.request_demo.step_2_title'), 'desc' => __('pages.request_demo.step_2_desc')],
                            ['num' => '3', 'icon' => 'rocket', 'color' => 'bg-green-100 text-green-600', 'title' => __('pages.request_demo.step_3_title'), 'desc' => __('pages.request_demo.step_3_desc')],
                        ]; @endphp
                        @foreach ($steps as $step)
                        <div class="flex items-start gap-4">
                            <div class="w-10 h-10 rounded-xl {{ $step['color'] }} flex items-center justify-center shrink-0">
                                <i data-lucide="{{ $step['icon'] }}" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <h4 class="font-bold text-gray-900 text-sm mb-1">{{ $step['title'] }}</h4>
                                <p class="text-gray-400 text-xs leading-relaxed">{{ $step['desc'] }}</p>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                <div class="bg-gray-50 rounded-2xl border border-gray-100 p-6 reveal" style="transition-delay:0.2s">
                    <h4 class="font-extrabold text-gray-900 text-sm mb-4">{{ __('pages.request_demo.signals_title') }}</h4>
                    <div class="space-y-3">
                        @php $signals = [
                            ['icon' => 'shield-check', 'text' => __('pages.request_demo.signal_1')],
                            ['icon' => 'users', 'text' => __('pages.request_demo.signal_2')],
                            ['icon' => 'headphones', 'text' => __('pages.request_demo.signal_3')],
                            ['icon' => 'credit-card', 'text' => __('pages.request_demo.signal_4')],
                        ]; @endphp
                        @foreach ($signals as $sig)
                        <div class="flex items-center gap-3 text-sm text-gray-600">
                            <i data-lucide="{{ $sig['icon'] }}" class="w-4 h-4 text-orange-500 shrink-0"></i>
                            {{ $sig['text'] }}
                        </div>
                        @endforeach
                    </div>
                </div>

                <div class="bg-gray-900 rounded-2xl p-6 reveal" style="transition-delay:0.3s">
                    <i data-lucide="quote" class="w-6 h-6 text-orange-400 mb-3"></i>
                    <p class="text-gray-300 text-sm italic leading-relaxed mb-4">
                        {{ __('pages.request_demo.quote') }}
                    </p>
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-full bg-gradient-to-br from-orange-500 to-amber-500 flex items-center justify-center text-white text-xs font-bold shrink-0">RL</div>
                        <div>
                            <div class="text-white text-xs font-bold">Rachel Liu</div>
                            <div class="text-gray-400 text-xs">{{ __('pages.request_demo.quote_role') }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- Prefer to self-serve? --}}
<section class="py-16" style="background:#FAF7F2;">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center reveal">
        <h2 class="text-2xl font-extrabold text-gray-900 mb-3">{{ __('pages.request_demo.self_title') }}</h2>
        <p class="text-gray-500 text-sm mb-6">{{ __('pages.request_demo.self_subtitle') }}</p>
        <div class="flex flex-wrap items-center justify-center gap-4">
            @auth
            <a href="{{ url('/dashboard') }}" class="inline-flex items-center gap-2 px-6 py-3 text-white font-bold rounded-xl hover:opacity-90 transition-all shadow-lg" style="background:linear-gradient(135deg,#9333ea,#7c3aed);">
                {{ __('pages.request_demo.self_cta_dashboard') }}
            </a>
            @else
            <a href="{{ route('register') }}" class="inline-flex items-center gap-2 px-6 py-3 text-white font-bold rounded-xl hover:opacity-90 transition-all shadow-lg" style="background:linear-gradient(135deg,#9333ea,#7c3aed);">
                {{ __('pages.request_demo.self_cta_register') }}
            </a>
            @endauth
            <a href="{{ locale_url('/pricing/teachers-and-schools') }}" class="inline-flex items-center gap-2 px-6 py-3 bg-white border border-gray-200 rounded-xl text-gray-700 font-semibold hover:border-primary-400 hover:text-primary-600 transition-all shadow-sm">
                {{ __('pages.request_demo.self_view_plan') }} →
            </a>
        </div>
    </div>
</section>

@endsection
