@extends('layouts.standalone')

@section('title', 'Harmoniva for Students')
@section('description', 'Build a world-class musical ear with AI-powered ear training. Intervals, chords, scales, rhythm, melodic dictation — all in one place. Start free.')

@section('content')

{{-- Hero --}}
<section class="py-20 sm:py-28 relative overflow-hidden" style="background: linear-gradient(135deg, #faf5ff 0%, #FAF7F2 60%, #f3e8ff 100%);">
    <div class="absolute -top-32 -right-32 w-[600px] h-[600px] rounded-full bg-purple-100/40 blur-3xl pointer-events-none"></div>
    <div class="absolute -bottom-20 -left-20 w-[350px] h-[350px] rounded-full bg-orange-50/50 blur-2xl pointer-events-none"></div>

    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 relative">
        <div class="text-center max-w-3xl mx-auto">
            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-primary-100 text-primary-700 text-sm font-semibold mb-6 hero-badge">
                <i data-lucide="graduation-cap" class="w-4 h-4"></i>
                For Students
            </div>

            <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold text-gray-900 leading-tight mb-6">
                Build the musical ear<br>
                <span class="font-serif italic font-normal gradient-text">every great musician has</span>
            </h1>

            <p class="text-gray-500 text-lg max-w-2xl mx-auto mb-10">
                Harmoniva trains your ear with AI-powered exercises across intervals, chords, scales, rhythm, and melodic dictation. Practice 10 minutes a day and transform how you hear music.
            </p>

            <div class="flex flex-col sm:flex-row items-center justify-center gap-4 mb-12">
                @auth
                <a href="{{ url('/dashboard') }}" class="inline-flex items-center gap-2 px-8 py-4 text-base font-bold text-white rounded-xl hover:opacity-90 transition-all shadow-xl hover:-translate-y-0.5" style="background:linear-gradient(135deg,#9333ea,#7c3aed);">
                    <i data-lucide="rocket" class="w-5 h-5"></i>
                    Continue Training
                </a>
                @else
                <a href="{{ route('register') }}" class="inline-flex items-center gap-2 px-8 py-4 text-base font-bold text-white rounded-xl hover:opacity-90 transition-all shadow-xl hover:-translate-y-0.5" style="background:linear-gradient(135deg,#9333ea,#7c3aed);">
                    <i data-lucide="rocket" class="w-5 h-5"></i>
                    Start Free Today
                </a>
                @endauth
                <a href="/pricing" class="inline-flex items-center gap-2 px-6 py-4 text-base font-medium text-gray-500 hover:text-gray-800 transition-colors">
                    See pricing <i data-lucide="arrow-right" class="w-4 h-4"></i>
                </a>
            </div>

            <div class="flex flex-wrap items-center justify-center gap-6 text-sm text-gray-400">
                <span class="flex items-center gap-1.5"><i data-lucide="check" class="w-4 h-4 text-green-500"></i>Free to start</span>
                <span class="flex items-center gap-1.5"><i data-lucide="check" class="w-4 h-4 text-green-500"></i>No music theory prerequisite</span>
                <span class="flex items-center gap-1.5"><i data-lucide="check" class="w-4 h-4 text-green-500"></i>Works on any device</span>
            </div>
        </div>
    </div>
</section>

{{-- Stats --}}
<section class="py-12 bg-gray-900">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-8 text-center">
            @php $stats = [
                ['value' => '10,000+', 'label' => 'musicians training'],
                ['value' => '10+', 'label' => 'exercise types'],
                ['value' => '95%', 'label' => 'feel more confident after 30 days'],
                ['value' => '10 min', 'label' => 'average daily session'],
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
            <span class="text-xs font-bold uppercase tracking-[0.2em] text-primary-600 mb-3 block">Everything You Need</span>
            <h2 class="text-3xl sm:text-4xl font-extrabold text-gray-900 mb-4">
                One platform, every skill<br>
                <span class="font-serif italic font-normal gradient-text">a musician needs</span>
            </h2>
            <p class="text-gray-500 max-w-xl mx-auto">From absolute beginner to conservatory-level training — Harmoniva meets you where you are and takes you further.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @php $features = [
                ['icon' => 'route', 'color' => 'bg-purple-100 text-purple-600', 'title' => 'Personalized Learning Path', 'desc' => 'AI analyzes your performance and builds a custom curriculum targeting your weaknesses — so you always practice what matters most.'],
                ['icon' => 'music-2', 'color' => 'bg-blue-100 text-blue-600', 'title' => 'Interval Recognition', 'desc' => 'Train melodic and harmonic intervals from minor 2nds to octaves. Identify them by ear instantly, every time.'],
                ['icon' => 'piano', 'color' => 'bg-pink-100 text-pink-600', 'title' => 'Chord Identification', 'desc' => 'Recognize major, minor, augmented, diminished, and seventh chords in any voicing, root position or inversion.'],
                ['icon' => 'layers', 'color' => 'bg-green-100 text-green-600', 'title' => 'Scale Recognition', 'desc' => 'Identify major, minor, dorian, mixolydian, and more scales — the foundation of understanding any musical style.'],
                ['icon' => 'activity', 'color' => 'bg-orange-100 text-orange-600', 'title' => 'Rhythm Training', 'desc' => 'Develop an internal pulse with rhythm reading exercises across 2/4, 3/4, 4/4, 6/8 time signatures and complex patterns.'],
                ['icon' => 'mic', 'color' => 'bg-cyan-100 text-cyan-600', 'title' => 'Melodic Dictation', 'desc' => 'Transcribe melodies by ear — the gold standard of ear training that bridges listening to playing and writing music.'],
                ['icon' => 'sparkles', 'color' => 'bg-violet-100 text-violet-600', 'title' => 'AI Feedback', 'desc' => 'Get instant AI explanations for every wrong answer. Understand why you made a mistake and how to avoid it next time.'],
                ['icon' => 'trending-up', 'color' => 'bg-amber-100 text-amber-600', 'title' => 'Progress Tracking', 'desc' => 'Detailed streak tracking, accuracy charts, and skill-by-skill breakdowns show you exactly how you\'re improving over time.'],
                ['icon' => 'gamepad-2', 'color' => 'bg-red-100 text-red-600', 'title' => 'Music Games', 'desc' => 'Make practice fun with gamified challenges that test your ear in creative ways. Perfect for daily warm-ups.'],
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
            <span class="text-xs font-bold uppercase tracking-[0.2em] text-primary-600 mb-3 block">Get Started in Minutes</span>
            <h2 class="text-3xl sm:text-4xl font-extrabold text-gray-900 mb-4">
                How it works
            </h2>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            @php $steps = [
                ['num' => '01', 'icon' => 'user-plus', 'title' => 'Sign up free', 'desc' => 'Create your account in 30 seconds — just an email and password. No credit card, no obligations. You get full access to 3 exercises per type daily, forever.'],
                ['num' => '02', 'icon' => 'sliders-horizontal', 'title' => 'Pick your skill level', 'desc' => 'Tell us where you are. Complete beginner? Advanced student preparing for auditions? Harmoniva adapts the difficulty to match your current level.'],
                ['num' => '03', 'icon' => 'calendar-check', 'title' => 'Practice daily', 'desc' => 'Spend 10-15 minutes a day with your personalized exercises. Build streaks, track your accuracy, and watch your ear transform over weeks and months.'],
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
            <span class="text-xs font-bold uppercase tracking-[0.2em] text-primary-600 mb-3 block">Student Stories</span>
            <h2 class="text-3xl font-extrabold text-gray-900">What students are saying</h2>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            @php $testimonials = [
                [
                    'quote' => "I failed my ear training exam twice before finding Harmoniva. Three months of daily practice and I passed with the highest grade in my class. The AI feedback finally helped me understand *why* I kept mixing up minor and major thirds.",
                    'name' => 'Mia Chen',
                    'role' => 'Music conservatory student, Boston',
                    'initials' => 'MC',
                    'color' => 'from-purple-500 to-violet-600',
                ],
                [
                    'quote' => "As a self-taught guitarist, I never learned to hear music properly. After 6 weeks with Harmoniva, I can identify chord progressions in songs just by listening. It feels like a superpower I didn't know I could develop.",
                    'name' => 'Jordan Williams',
                    'role' => 'Self-taught guitarist, Nashville',
                    'initials' => 'JW',
                    'color' => 'from-orange-500 to-amber-500',
                ],
                [
                    'quote' => "The melodic dictation exercises are incredible. I'm preparing for a music school audition and I've gone from transcribing nothing to writing full 8-bar melodies accurately. Harmoniva is genuinely the best ear training tool I've used.",
                    'name' => 'Sofia Andersson',
                    'role' => 'Classical piano student, Stockholm',
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
            Join 10,000+ musicians<br>
            <span class="font-serif italic font-normal" style="background:linear-gradient(135deg,#c084fc,#fb923c);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;">training their ear with Harmoniva</span>
        </h2>
        <p class="text-gray-400 text-lg mb-10 max-w-xl mx-auto">
            Start free today. No credit card needed. Build the ear you've always wanted — 10 minutes at a time.
        </p>
        <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
            @auth
            <a href="{{ url('/dashboard') }}" class="inline-flex items-center gap-2 px-8 py-4 text-base font-bold text-white rounded-xl hover:opacity-90 transition-all shadow-xl hover:-translate-y-0.5" style="background:linear-gradient(135deg,#9333ea,#7c3aed);">
                <i data-lucide="rocket" class="w-5 h-5"></i>
                Continue Training
            </a>
            @else
            <a href="{{ route('register') }}" class="inline-flex items-center gap-2 px-8 py-4 text-base font-bold text-white rounded-xl hover:opacity-90 transition-all shadow-xl hover:-translate-y-0.5" style="background:linear-gradient(135deg,#9333ea,#7c3aed);">
                <i data-lucide="rocket" class="w-5 h-5"></i>
                Start Free Today
            </a>
            @endauth
            <a href="/pricing" class="inline-flex items-center gap-2 px-6 py-4 text-base font-medium text-gray-400 hover:text-white transition-colors">
                See pricing <i data-lucide="arrow-right" class="w-4 h-4"></i>
            </a>
        </div>
    </div>
</section>

@endsection
