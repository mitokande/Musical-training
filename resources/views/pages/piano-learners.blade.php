@extends('layouts.standalone')

@section('title', 'Harmoniva for Piano Learners')
@section('description', 'Elevate your piano practice with ear training. Recognize intervals, chords, and melodies by ear — and become the musician your piano teacher always knew you could be.')

@section('content')

{{-- Hero --}}
<section class="py-20 sm:py-28 relative overflow-hidden" style="background: linear-gradient(135deg, #faf5ff 0%, #FAF7F2 60%, #f3e8ff 100%);">
    <div class="absolute -top-32 -right-32 w-[600px] h-[600px] rounded-full bg-purple-100/40 blur-3xl pointer-events-none"></div>
    <div class="absolute -bottom-20 -left-20 w-[350px] h-[350px] rounded-full bg-pink-50/60 blur-2xl pointer-events-none"></div>

    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 relative">
        <div class="text-center max-w-3xl mx-auto">
            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-primary-100 text-primary-700 text-sm font-semibold mb-6 hero-badge">
                <i data-lucide="piano" class="w-4 h-4"></i>
                For Piano Learners
            </div>

            <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold text-gray-900 leading-tight mb-6">
                Elevate your piano practice<br>
                <span class="font-serif italic font-normal gradient-text">with ear training</span>
            </h1>

            <p class="text-gray-500 text-lg max-w-2xl mx-auto mb-10">
                The best pianists don't just read notes — they hear music. Harmoniva trains your musical ear so you can play with more expression, learn pieces faster, and connect deeply with every note under your fingers.
            </p>

            <div class="flex flex-col sm:flex-row items-center justify-center gap-4 mb-10">
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
                <span class="flex items-center gap-1.5"><i data-lucide="check" class="w-4 h-4 text-green-500"></i>No music theory prereq</span>
                <span class="flex items-center gap-1.5"><i data-lucide="check" class="w-4 h-4 text-green-500"></i>Works alongside lessons</span>
            </div>
        </div>
    </div>
</section>

{{-- Why Ear Training Matters for Pianists --}}
<section class="py-20 bg-white">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
            <div class="reveal">
                <span class="text-xs font-bold uppercase tracking-[0.2em] text-primary-600 mb-4 block">The Missing Piece</span>
                <h2 class="text-3xl sm:text-4xl font-extrabold text-gray-900 mb-6">
                    Why every pianist needs<br>
                    <span class="font-serif italic font-normal gradient-text">a trained ear</span>
                </h2>
                <div class="space-y-5 text-gray-500 text-sm leading-relaxed">
                    <p>
                        Most piano students spend years developing their technique — perfecting scales, arpeggios, and fingering. But without a trained ear, you're reading music like a language you can speak but not understand. You can produce the notes without truly hearing the music.
                    </p>
                    <p>
                        Ear training bridges that gap. When you can hear a chord and know instantly it's a diminished seventh, or catch the interval between two notes in a melody, your practice sessions become more focused, your sight-reading improves dramatically, and your musical expression deepens.
                    </p>
                    <p>
                        Conservatory-level pianists train their ears the same way they train their hands — daily, systematically, with intention. Harmoniva makes that level of training accessible to every piano student, from beginner to advanced.
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4 reveal" style="transition-delay:0.15s">
                @php $benefits = [
                    ['icon' => 'book-open', 'color' => 'bg-purple-100 text-purple-600', 'title' => 'Sight-read faster', 'desc' => 'Hear the music in your head before your fingers play it.'],
                    ['icon' => 'brain', 'color' => 'bg-blue-100 text-blue-600', 'title' => 'Memorize pieces quicker', 'desc' => 'Understanding harmonic structure makes pieces stick in memory.'],
                    ['icon' => 'mic-2', 'color' => 'bg-pink-100 text-pink-600', 'title' => 'Play with more feeling', 'desc' => 'Hear what you\'re playing emotionally, not just mechanically.'],
                    ['icon' => 'wrench', 'color' => 'bg-amber-100 text-amber-600', 'title' => 'Fix your own mistakes', 'desc' => 'Catch wrong notes by ear before your teacher does.'],
                    ['icon' => 'music-2', 'color' => 'bg-green-100 text-green-600', 'title' => 'Improvise confidently', 'desc' => 'Know which notes will sound right before you play them.'],
                    ['icon' => 'headphones', 'color' => 'bg-orange-100 text-orange-600', 'title' => 'Transcribe by ear', 'desc' => 'Pick up songs from recordings without sheet music.'],
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
            <span class="text-xs font-bold uppercase tracking-[0.2em] text-primary-600 mb-3 block">Built for Pianists</span>
            <h2 class="text-3xl sm:text-4xl font-extrabold text-gray-900 mb-4">
                Ear training exercises<br>
                <span class="font-serif italic font-normal gradient-text">made for the piano</span>
            </h2>
        </div>

        <div class="space-y-6">
            @php $highlights = [
                [
                    'icon' => 'piano',
                    'color' => 'from-purple-500 to-violet-600',
                    'title' => 'Virtual Piano Studio',
                    'desc' => 'Practice intervals, chords, and notes directly on an interactive piano keyboard. Hear exactly how each sound is produced and see it visually on the keys — the most natural learning environment for pianists.',
                    'points' => ['Interactive piano keyboard in every exercise', 'Visual note positions while you listen', 'Perfect for mapping theory to instrument'],
                ],
                [
                    'icon' => 'music-2',
                    'color' => 'from-blue-500 to-cyan-500',
                    'title' => 'Interval & Chord Recognition',
                    'desc' => 'Instantly recognize the intervals and chords that appear in every piece of repertoire you\'ll ever play. From simple major thirds to complex diminished seventh chords, build a comprehensive sonic vocabulary at the piano.',
                    'points' => ['All intervals: minor 2nd to octave', 'Major, minor, diminished, augmented, dominant 7th', 'Root position and inversions'],
                ],
                [
                    'icon' => 'mic',
                    'color' => 'from-orange-500 to-amber-500',
                    'title' => 'Melodic Dictation',
                    'desc' => 'Transcribe melodies by ear — the exercise that connects what you hear to what you play. Essential for pianists who want to pick up pieces by ear, improvise, or strengthen their musical memory.',
                    'points' => ['Short 2-bar to full 8-bar melodies', 'Multiple clefs and key signatures', 'Adjustable tempo and difficulty'],
                ],
                [
                    'icon' => 'book-open',
                    'color' => 'from-green-500 to-teal-500',
                    'title' => 'Sight-Reading Connection',
                    'desc' => 'When you hear intervals and chord progressions in your mind before playing, sight-reading transforms. Our scale and chord exercises train you to audiate — hear silently — so every new piece feels more familiar from the first bar.',
                    'points' => ['Internalize common progressions', 'Recognize harmonic patterns in scores', 'Build musical anticipation and flow'],
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
            <span class="text-xs font-bold uppercase tracking-[0.2em] text-primary-600 mb-3 block">Works With Your Lessons</span>
            <h2 class="text-3xl font-extrabold text-gray-900 mb-3">
                Use Harmoniva alongside<br>
                <span class="font-serif italic font-normal gradient-text">your piano lessons</span>
            </h2>
            <p class="text-gray-500 max-w-xl mx-auto">Harmoniva is designed to complement — not replace — your piano teacher. Use it for 10 minutes of daily ear training between lessons.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 reveal">
            @php $integration = [
                ['icon' => 'calendar', 'color' => 'bg-purple-100 text-purple-600', 'title' => 'Monday – Thursday', 'desc' => '10 minutes of daily ear training. One interval type, one chord exercise, one short rhythm.'],
                ['icon' => 'music', 'color' => 'bg-orange-100 text-orange-600', 'title' => 'Before your lesson', 'desc' => 'Quick ear warm-up to arrive sharp. Your teacher will notice the difference within weeks.'],
                ['icon' => 'trending-up', 'color' => 'bg-green-100 text-green-600', 'title' => 'Track your progress', 'desc' => 'Monthly accuracy reports give you and your teacher a clear picture of your ear\'s development.'],
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
            Become the pianist<br>
            <span class="font-serif italic font-normal" style="background:linear-gradient(135deg,#c084fc,#fb923c);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;">you've always wanted to be</span>
        </h2>
        <p class="text-gray-400 text-lg mb-10 max-w-xl mx-auto">
            Start free today. Just 10 minutes of ear training a day will transform your piano playing in ways technique practice alone never can.
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
                Start Free — No Card Needed
            </a>
            @endauth
            <a href="/pricing" class="inline-flex items-center gap-2 px-6 py-4 text-base font-medium text-gray-400 hover:text-white transition-colors">
                See pricing <i data-lucide="arrow-right" class="w-4 h-4"></i>
            </a>
        </div>
    </div>
</section>

@endsection
