@extends('layouts.standalone')

@section('title', __('pages.faq.meta_title'))
@section('description', __('pages.faq.meta_description'))

@section('content')

{{-- Hero --}}
<section class="bg-gradient-to-br from-purple-700 via-purple-600 to-purple-800 text-white py-20 px-4">
    <div class="max-w-3xl mx-auto text-center reveal">
        <div class="inline-flex items-center gap-2 bg-white/10 text-white text-sm font-medium px-4 py-2 rounded-full mb-6">
            <i data-lucide="help-circle" class="w-4 h-4"></i>
            {{ __('pages.faq.hero_badge') }}
        </div>
        <h1 class="text-4xl md:text-5xl font-bold mb-4">{{ __('pages.faq.hero_title') }}</h1>
        <p class="text-purple-200 text-lg">{{ __('pages.faq.hero_subtitle') }}</p>
    </div>
</section>

{{-- FAQ Accordion --}}
<section class="bg-[#FAF7F2] py-20 px-4">
    <div class="max-w-3xl mx-auto">

        @php
        $faqs = [
            ['q' => __('pages.faq.q1'), 'a' => __('pages.faq.a1')],
            ['q' => __('pages.faq.q2'), 'a' => __('pages.faq.a2')],
            ['q' => __('pages.faq.q3'), 'a' => __('pages.faq.a3')],
            ['q' => __('pages.faq.q4'), 'a' => __('pages.faq.a4')],
            ['q' => __('pages.faq.q5'), 'a' => __('pages.faq.a5')],
            ['q' => __('pages.faq.q6'), 'a' => __('pages.faq.a6')],
            ['q' => __('pages.faq.q7'), 'a' => __('pages.faq.a7')],
            ['q' => __('pages.faq.q8'), 'a' => __('pages.faq.a8')],
            ['q' => __('pages.faq.q9'), 'a' => __('pages.faq.a9')],
            ['q' => __('pages.faq.q10'), 'a' => __('pages.faq.a10')],
            ['q' => __('pages.faq.q11'), 'a' => __('pages.faq.a11')],
            ['q' => __('pages.faq.q12'), 'a' => __('pages.faq.a12')],
            ['q' => __('pages.faq.q13'), 'a' => __('pages.faq.a13', ['days' => (int) config('payments.trial.days', 15)])],
            ['q' => __('pages.faq.q14'), 'a' => __('pages.faq.a14')],
            ['q' => __('pages.faq.q15'), 'a' => __('pages.faq.a15')],
        ];
        @endphp

        <div class="space-y-3">
            @foreach($faqs as $index => $faq)
            <div
                x-data="{ open: false }"
                class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden reveal"
            >
                <button
                    @click="open = !open"
                    class="w-full flex items-center justify-between gap-4 px-6 py-5 text-left"
                    :aria-expanded="open"
                >
                    <span class="font-semibold text-gray-900 text-base">{{ $faq['q'] }}</span>
                    <span class="flex-shrink-0 w-7 h-7 rounded-full bg-purple-50 flex items-center justify-center transition-transform duration-200" :class="open ? 'rotate-45' : ''">
                        <i data-lucide="plus" class="w-4 h-4 text-purple-600"></i>
                    </span>
                </button>
                <div
                    x-show="open"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 -translate-y-1"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100 translate-y-0"
                    x-transition:leave-end="opacity-0 -translate-y-1"
                    class="px-6 pb-6"
                    x-cloak
                >
                    <div class="h-px bg-gray-100 mb-5"></div>
                    <p class="text-gray-600 leading-relaxed">{{ $faq['a'] }}</p>
                </div>
            </div>
            @endforeach
        </div>

    </div>
</section>

{{-- Bottom CTA --}}
<section class="bg-white py-20 px-4">
    <div class="max-w-4xl mx-auto text-center reveal">
        <h2 class="text-3xl font-bold text-gray-900 mb-4">{{ __('pages.faq.cta_title') }}</h2>
        <p class="text-gray-500 text-lg mb-8">{{ __('pages.faq.cta_subtitle') }}</p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="{{ locale_url('/contact') }}" class="inline-flex items-center justify-center gap-2 bg-purple-600 hover:bg-purple-700 text-white font-semibold px-8 py-4 rounded-xl transition-colors shadow-lg hover:shadow-xl">
                <i data-lucide="mail" class="w-5 h-5"></i>
                {{ __('pages.faq.cta_contact') }}
            </a>
            <a href="{{ route('register') }}" class="inline-flex items-center justify-center gap-2 bg-white border-2 border-gray-200 hover:border-purple-300 text-gray-800 font-semibold px-8 py-4 rounded-xl transition-colors">
                <i data-lucide="play-circle" class="w-5 h-5 text-purple-600"></i>
                {{ __('pages.faq.cta_register') }}
            </a>
        </div>
    </div>
</section>

@endsection

{{-- Rendered after the content section above, so $faqs (defined there) is in scope. --}}
@section('structured-data')
    @php
        $faqJsonLd = json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => array_map(fn (array $faq) => [
                '@type' => 'Question',
                'name' => $faq['q'],
                'acceptedAnswer' => ['@type' => 'Answer', 'text' => $faq['a']],
            ], $faqs),
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    @endphp
    <script type="application/ld+json">{!! $faqJsonLd !!}</script>
@endsection
