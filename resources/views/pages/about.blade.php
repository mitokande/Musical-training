@extends('layouts.standalone')

@section('title', 'About Harmoniva — Building the Future of Music Education')
@section('description', 'Learn about Harmoniva\'s mission to make professional-grade ear training accessible to every musician on the planet. AI-powered ear training for students, teachers, and music schools.')

@section('content')

{{-- Hero --}}
<section class="py-24 sm:py-32 relative overflow-hidden" style="background: linear-gradient(135deg, #faf5ff 0%, #FAF7F2 60%, #fef3c7 100%);">
    <div class="absolute -top-32 -right-32 w-[500px] h-[500px] rounded-full bg-purple-100/50 blur-3xl pointer-events-none"></div>
    <div class="absolute -bottom-20 -left-20 w-[300px] h-[300px] rounded-full bg-orange-50/60 blur-2xl pointer-events-none"></div>

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center relative">
        <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-purple-100 text-purple-700 text-sm font-semibold mb-6">
            <i data-lucide="music" class="w-4 h-4"></i>
            Our Story
        </div>

        <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold text-gray-900 leading-tight mb-6">
            Building the Future of<br>
            <span class="font-serif italic font-normal bg-gradient-to-r from-purple-600 to-orange-500 bg-clip-text text-transparent">Music Education</span>
        </h1>

        <p class="text-gray-600 text-xl leading-relaxed max-w-2xl mx-auto">
            Harmoniva was founded with a single mission — to make professional-grade ear training accessible to every musician on the planet.
        </p>
    </div>
</section>

{{-- Mission Statement --}}
<section class="py-20 bg-white">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <p class="text-xl text-gray-700 leading-relaxed mb-6">
            Whether you're a beginner picking up your first instrument or an advanced player training for conservatory entrance exams, we believe your musical ear is your most important tool. Technique can be taught, theory can be memorized — but a finely trained ear transforms how you hear, feel, and create music.
        </p>
        <p class="text-lg text-gray-600 leading-relaxed">
            We built Harmoniva because we saw a gap: professional ear training software was either prohibitively expensive, frustratingly outdated, or locked behind institutional access. Every musician deserves better.
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
                    How It Started
                </div>
                <h2 class="text-3xl sm:text-4xl font-extrabold text-gray-900 mb-6">How Harmoniva Started</h2>

                <div class="space-y-5 text-gray-700 leading-relaxed">
                    <p>
                        The idea for Harmoniva was born in a practice room. Frustrated by clunky, expensive software and one-size-fits-all curricula, a musician and software developer set out to create the ear training tool they'd always wished existed — one that adapted to how you learn, not how the software was designed.
                    </p>
                    <p>
                        After months of research, conversations with music teachers, conservatory students, and self-taught musicians worldwide, a clear picture emerged: the biggest barrier to great ear training wasn't motivation or talent. It was access. Access to quality exercises, access to structured learning paths, and access to intelligent feedback that could adapt to your specific weaknesses.
                    </p>
                    <p>
                        Harmoniva launched in 2024 with a small set of core exercises and a big ambition. By integrating AI directly into the learning experience, we made it possible for every musician — regardless of their budget or location — to train like a conservatory student. Today, musicians in over 30 countries use Harmoniva every day to sharpen their intervals, chords, rhythms, and melodic memory.
                    </p>
                </div>
            </div>

            <div class="relative">
                <div class="bg-white rounded-2xl shadow-xl p-8 border border-gray-100">
                    <div class="flex items-center gap-4 mb-6">
                        <div class="w-12 h-12 bg-gradient-to-br from-purple-500 to-purple-700 rounded-xl flex items-center justify-center">
                            <i data-lucide="target" class="w-6 h-6 text-white"></i>
                        </div>
                        <div>
                            <div class="font-bold text-gray-900 text-lg">Our Mission</div>
                            <div class="text-gray-500 text-sm">What drives us every day</div>
                        </div>
                    </div>
                    <p class="text-gray-700 leading-relaxed italic text-lg">
                        "To make professional-grade ear training accessible to every musician on the planet — regardless of where they live, what they can afford, or where they are in their musical journey."
                    </p>
                </div>

                <div class="bg-white rounded-2xl shadow-xl p-8 border border-gray-100 mt-6">
                    <div class="flex items-center gap-4 mb-6">
                        <div class="w-12 h-12 bg-gradient-to-br from-orange-400 to-orange-600 rounded-xl flex items-center justify-center">
                            <i data-lucide="lightbulb" class="w-6 h-6 text-white"></i>
                        </div>
                        <div>
                            <div class="font-bold text-gray-900 text-lg">Our Vision</div>
                            <div class="text-gray-500 text-sm">Where we're headed</div>
                        </div>
                    </div>
                    <p class="text-gray-700 leading-relaxed">
                        A world where AI-powered music education closes the gap between self-taught musicians and those with access to world-class conservatories.
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
                What We Stand For
            </div>
            <h2 class="text-3xl sm:text-4xl font-extrabold text-gray-900">Our Core Values</h2>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="bg-[#FAF7F2] rounded-2xl p-7 border border-gray-100 hover:shadow-lg transition-shadow duration-300">
                <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center mb-5">
                    <i data-lucide="sparkles" class="w-6 h-6 text-purple-600"></i>
                </div>
                <h3 class="font-bold text-gray-900 text-lg mb-3">Innovation</h3>
                <p class="text-gray-600 leading-relaxed text-sm">
                    We don't accept "good enough." We continuously explore how AI, adaptive learning, and modern UX can make ear training more effective, more engaging, and more personal.
                </p>
            </div>

            <div class="bg-[#FAF7F2] rounded-2xl p-7 border border-gray-100 hover:shadow-lg transition-shadow duration-300">
                <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center mb-5">
                    <i data-lucide="globe" class="w-6 h-6 text-green-600"></i>
                </div>
                <h3 class="font-bold text-gray-900 text-lg mb-3">Accessibility</h3>
                <p class="text-gray-600 leading-relaxed text-sm">
                    Great ear training shouldn't be a privilege. We offer a generous free tier, support 15 languages, and design every feature with inclusion in mind — from color contrast to keyboard navigation.
                </p>
            </div>

            <div class="bg-[#FAF7F2] rounded-2xl p-7 border border-gray-100 hover:shadow-lg transition-shadow duration-300">
                <div class="w-12 h-12 bg-orange-100 rounded-xl flex items-center justify-center mb-5">
                    <i data-lucide="award" class="w-6 h-6 text-orange-600"></i>
                </div>
                <h3 class="font-bold text-gray-900 text-lg mb-3">Excellence</h3>
                <p class="text-gray-600 leading-relaxed text-sm">
                    Every exercise, every piece of feedback, every UI interaction is crafted with care. We hold ourselves to the same standard we help our users pursue in their music.
                </p>
            </div>

            <div class="bg-[#FAF7F2] rounded-2xl p-7 border border-gray-100 hover:shadow-lg transition-shadow duration-300">
                <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center mb-5">
                    <i data-lucide="users" class="w-6 h-6 text-blue-600"></i>
                </div>
                <h3 class="font-bold text-gray-900 text-lg mb-3">Community</h3>
                <p class="text-gray-600 leading-relaxed text-sm">
                    Musicians grow together. We build features that support teachers, schools, and learners as an ecosystem — not just a collection of isolated users.
                </p>
            </div>
        </div>
    </div>
</section>

{{-- Stats Strip --}}
<section class="py-16 bg-gray-900 text-white">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-8 text-center">
            <div>
                <div class="text-4xl font-extrabold text-purple-400 mb-2">10,000+</div>
                <div class="text-gray-400 text-sm font-medium">Active Musicians</div>
            </div>
            <div>
                <div class="text-4xl font-extrabold text-orange-400 mb-2">15+</div>
                <div class="text-gray-400 text-sm font-medium">Exercise Types</div>
            </div>
            <div>
                <div class="text-4xl font-extrabold text-purple-400 mb-2">15</div>
                <div class="text-gray-400 text-sm font-medium">Languages Supported</div>
            </div>
            <div>
                <div class="text-4xl font-extrabold text-orange-400 mb-2">AI</div>
                <div class="text-gray-400 text-sm font-medium">Powered Learning Paths</div>
            </div>
        </div>
    </div>
</section>

{{-- Team Ethos --}}
<section class="py-20 bg-white">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-purple-100 text-purple-700 text-sm font-semibold mb-5">
            <i data-lucide="headphones" class="w-4 h-4"></i>
            The Team
        </div>
        <h2 class="text-3xl sm:text-4xl font-extrabold text-gray-900 mb-6">Built by musicians, for musicians</h2>
        <p class="text-lg text-gray-600 leading-relaxed mb-5">
            Everyone who works on Harmoniva has lived the frustration of trying to develop their musical ear without the right tools. We're performers, composers, and music educators who also happen to love building software. That combination means we understand what musicians actually need — not what looks good in a product demo.
        </p>
        <p class="text-gray-600 leading-relaxed">
            We keep our team lean and focused. We'd rather ship one feature that genuinely helps musicians train more effectively than ten features that check boxes. Every decision — from which exercises to include to how we present feedback — is informed by real musical experience and ongoing conversations with our community.
        </p>
    </div>
</section>

{{-- CTA --}}
<section class="py-20" style="background: linear-gradient(135deg, #7e22ce 0%, #9333ea 50%, #c026d3 100%);">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h2 class="text-3xl sm:text-4xl font-extrabold text-white mb-4">Ready to train your ear?</h2>
        <p class="text-purple-200 text-lg mb-8">Join thousands of musicians who practice with Harmoniva every day. Start free — no credit card required.</p>
        <a href="{{ route('register') }}" class="inline-flex items-center gap-2 bg-white text-purple-700 font-bold px-8 py-4 rounded-xl hover:bg-purple-50 transition-colors duration-200 shadow-lg text-lg">
            <i data-lucide="music" class="w-5 h-5"></i>
            Start Your Free Trial
        </a>
    </div>
</section>

@endsection
