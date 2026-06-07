@extends('layouts.standalone')

@section('title', 'Frequently Asked Questions')
@section('description', 'Answers to the most common questions about Harmoniva — ear training, pricing, features, AI, and more.')

@section('content')

{{-- Hero --}}
<section class="bg-gradient-to-br from-purple-700 via-purple-600 to-purple-800 text-white py-20 px-4">
    <div class="max-w-3xl mx-auto text-center reveal">
        <div class="inline-flex items-center gap-2 bg-white/10 text-white text-sm font-medium px-4 py-2 rounded-full mb-6">
            <i data-lucide="help-circle" class="w-4 h-4"></i>
            FAQ
        </div>
        <h1 class="text-4xl md:text-5xl font-bold mb-4">Frequently Asked Questions</h1>
        <p class="text-purple-200 text-lg">Everything you need to know about Harmoniva, answered.</p>
    </div>
</section>

{{-- FAQ Accordion --}}
<section class="bg-[#FAF7F2] py-20 px-4">
    <div class="max-w-3xl mx-auto">

        @php
        $faqs = [
            [
                'q' => 'What is ear training, and why does it matter?',
                'a' => 'Ear training is the practice of developing your ability to recognize and understand musical elements — intervals, chords, scales, rhythms, and melodies — purely by listening. It\'s one of the most important skills a musician can develop. With strong ears, you can learn songs faster, improvise more naturally, transcribe music, play in tune, and communicate more effectively with other musicians. Think of it as the bridge between knowing music intellectually and feeling it instinctively.'
            ],
            [
                'q' => 'What is Harmoniva?',
                'a' => 'Harmoniva is an AI-powered ear training platform built for musicians of all levels — from complete beginners to conservatory students and professional performers. It offers a comprehensive set of interactive audio exercises covering interval recognition, chord identification, scale identification, melodic dictation, rhythm training, and more. Harmoniva\'s AI adapts to your personal weaknesses, so every session is tailored to help you improve as efficiently as possible.'
            ],
            [
                'q' => 'Is it free to start?',
                'a' => 'Yes! Harmoniva has a generous free tier that lets you practice without a credit card. Free users get access to core exercises with a daily practice limit of 3 sessions per exercise type. Upgrading to Premium removes all limits, unlocks AI-powered practice mode, advanced progress analytics, and the full Exercise Setup Studio. You can start for free today at harmoniva.app.'
            ],
            [
                'q' => 'What exercise types are available?',
                'a' => 'Harmoniva covers all the core ear training disciplines: single note recognition, melodic interval identification, harmonic interval identification, interval direction (ascending vs. descending), interval comparison, interval construction, chord identification, scale identification, rhythm reading, and melodic dictation. Each type has multiple difficulty levels and is fully configurable through the Exercise Setup Studio.'
            ],
            [
                'q' => 'How does the AI work?',
                'a' => 'Harmoniva\'s AI analyzes your answers in real time to identify patterns in your mistakes. For example, if you consistently confuse minor thirds with major seconds, the AI will weight those intervals more heavily in upcoming questions until your accuracy improves. The system also adjusts the pace and complexity of exercises based on your performance, ensuring you\'re always working at the right level — challenged but not overwhelmed.'
            ],
            [
                'q' => 'Can I use Harmoniva on mobile?',
                'a' => 'Yes. Harmoniva is fully responsive and works on all modern smartphones and tablets. You can practice in your browser on iOS and Android without downloading an app. For the best audio experience, we recommend using headphones or earbuds, especially for harmonic interval exercises where both notes play simultaneously.'
            ],
            [
                'q' => 'Is Harmoniva good for complete beginners?',
                'a' => 'Absolutely. Harmoniva is designed to meet you wherever you are. The Learning Path starts from the very basics — recognizing single notes and simple intervals — and guides you progressively to more advanced skills. You don\'t need any prior music theory knowledge to get started. That said, experienced musicians will find plenty of challenge in the advanced exercise configurations and melodic dictation modules.'
            ],
            [
                'q' => 'How does the Learning Path work?',
                'a' => 'The Learning Path is a structured curriculum that guides you from beginner to advanced ear training skills in a logical sequence. Each step focuses on a specific skill, and the system generates custom questions based on your current level. As you complete exercises and demonstrate accuracy, you progress to the next challenge. It\'s the fastest way to build a solid, well-rounded ear — no guesswork about what to practice next.'
            ],
            [
                'q' => 'Can teachers assign exercises to students?',
                'a' => 'Yes. Teacher accounts include tools to create custom exercise sets and assign them to students. Students complete the assigned exercises and their results are visible in the teacher\'s dashboard, making it easy to identify which students need extra support on specific skills. School plans support multiple teachers and large student rosters. Contact us for institutional pricing.'
            ],
            [
                'q' => 'What payment methods are accepted?',
                'a' => 'We accept all major credit and debit cards (Visa, Mastercard, American Express, Discover) as well as Apple Pay and Google Pay. All payments are processed securely via Stripe. We do not store your card details on our servers.'
            ],
            [
                'q' => 'Can I cancel my subscription at any time?',
                'a' => 'Yes, you can cancel anytime from your account settings — no questions asked, no cancellation fees. When you cancel, your Premium access continues until the end of your current billing period. After that, your account reverts to the free tier and your practice history is preserved.'
            ],
            [
                'q' => 'Is my data secure? Is Harmoniva GDPR compliant?',
                'a' => 'Yes. Harmoniva takes your privacy seriously. We are fully GDPR compliant and do not sell your personal data to third parties. Your practice data is encrypted in transit and at rest. You can request a full export or deletion of your data at any time by contacting support@harmoniva.app. For more details, see our Privacy Policy.'
            ],
            [
                'q' => 'Is there a free trial for Premium?',
                'a' => 'The free tier itself acts as an ongoing trial — you can use Harmoniva indefinitely without paying. If you want to explore Premium features risk-free, we occasionally offer promotional trials. Check the pricing page or sign up for our newsletter to be notified of any trial offers. We believe the free tier gives you enough to decide if Harmoniva is right for you before upgrading.'
            ],
            [
                'q' => 'How does progress tracking work?',
                'a' => 'Every answer you submit is logged and used to build your personal progress profile. Your dashboard shows accuracy rates by exercise type, improvement trends over time, daily practice streaks, and a breakdown of your strongest and weakest areas. Premium users get more detailed analytics including heatmaps and per-interval accuracy breakdowns. Your history is stored indefinitely so you can track long-term growth.'
            ],
            [
                'q' => 'How is Harmoniva different from other ear training apps?',
                'a' => 'Most ear training apps offer static, one-size-fits-all exercises with little intelligence behind them. Harmoniva stands out in three ways: (1) AI adaptation that personalizes every session to your actual weaknesses; (2) a comprehensive Exercise Setup Studio that lets advanced users configure every parameter of a practice session; and (3) a Learning Path curriculum grounded in real music pedagogy. We\'ve also built teacher and school tools that most consumer ear training apps simply don\'t have.'
            ],
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
        <h2 class="text-3xl font-bold text-gray-900 mb-4">Still have questions?</h2>
        <p class="text-gray-500 text-lg mb-8">We're happy to help. Reach out to our support team or start your free account right now.</p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="/contact" class="inline-flex items-center justify-center gap-2 bg-purple-600 hover:bg-purple-700 text-white font-semibold px-8 py-4 rounded-xl transition-colors shadow-lg hover:shadow-xl">
                <i data-lucide="mail" class="w-5 h-5"></i>
                Contact Support
            </a>
            <a href="/register" class="inline-flex items-center justify-center gap-2 bg-white border-2 border-gray-200 hover:border-purple-300 text-gray-800 font-semibold px-8 py-4 rounded-xl transition-colors">
                <i data-lucide="play-circle" class="w-5 h-5 text-purple-600"></i>
                Start Free — No Card Required
            </a>
        </div>
    </div>
</section>

@endsection
