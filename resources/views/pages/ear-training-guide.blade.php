@extends('layouts.standalone')

@section('title', 'The Complete Ear Training Guide')
@section('description', 'A comprehensive, chapter-by-chapter guide to ear training — from absolute basics to advanced techniques. Learn what to practice, how to practice, and how to get results.')

@section('content')

{{-- Hero --}}
<section class="bg-gradient-to-br from-purple-700 via-purple-600 to-purple-800 text-white py-20 px-4">
    <div class="max-w-3xl mx-auto text-center reveal">
        <div class="inline-flex items-center gap-2 bg-white/10 text-white text-sm font-medium px-4 py-2 rounded-full mb-6">
            <i data-lucide="book-open" class="w-4 h-4"></i>
            Free Guide
        </div>
        <h1 class="text-4xl md:text-5xl font-bold mb-5">The Complete Ear Training Guide</h1>
        <p class="text-purple-200 text-xl max-w-2xl mx-auto leading-relaxed">From your first interval to advanced melodic dictation — everything you need to build a world-class musical ear, explained clearly and practically.</p>
        <div class="flex flex-wrap justify-center gap-4 mt-8 text-sm text-purple-200">
            <span class="flex items-center gap-1.5"><i data-lucide="clock" class="w-4 h-4"></i> 25 min read</span>
            <span class="flex items-center gap-1.5"><i data-lucide="list" class="w-4 h-4"></i> 6 chapters</span>
            <span class="flex items-center gap-1.5"><i data-lucide="users" class="w-4 h-4"></i> All levels</span>
        </div>
    </div>
</section>

{{-- Table of Contents --}}
<section class="bg-white border-b border-gray-100 py-8 px-4">
    <div class="max-w-4xl mx-auto reveal">
        <p class="text-xs font-semibold text-gray-400 uppercase tracking-widest mb-4">Table of Contents</p>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
            @php
            $chapters = [
                ['num' => 1, 'title' => 'What Is Ear Training?'],
                ['num' => 2, 'title' => 'Why Ear Training Matters'],
                ['num' => 3, 'title' => 'Core Skills — Intervals, Chords, Scales, Rhythm'],
                ['num' => 4, 'title' => 'How to Practice Effectively'],
                ['num' => 5, 'title' => 'Technology & AI in Ear Training'],
                ['num' => 6, 'title' => 'Beginner to Advanced — Your Roadmap'],
            ];
            @endphp
            @foreach($chapters as $ch)
            <a href="#chapter-{{ $ch['num'] }}" class="flex items-center gap-3 px-4 py-3 rounded-xl bg-gray-50 hover:bg-purple-50 hover:text-purple-700 transition-colors group">
                <span class="w-7 h-7 rounded-lg bg-purple-100 text-purple-700 text-xs font-bold flex items-center justify-center flex-shrink-0">{{ $ch['num'] }}</span>
                <span class="text-sm font-medium text-gray-700 group-hover:text-purple-700 leading-tight">{{ $ch['title'] }}</span>
            </a>
            @endforeach
        </div>
    </div>
</section>

{{-- Main Content --}}
<div class="bg-[#FAF7F2] py-16 px-4">
    <div class="max-w-3xl mx-auto space-y-24">

        {{-- Chapter 1 --}}
        <article id="chapter-1" class="reveal">
            <div class="flex items-center gap-4 mb-6">
                <span class="w-10 h-10 rounded-xl bg-purple-600 text-white font-bold text-lg flex items-center justify-center flex-shrink-0">1</span>
                <h2 class="text-3xl font-bold text-gray-900">What Is Ear Training?</h2>
            </div>
            <div class="prose prose-gray max-w-none">
                <p class="text-gray-700 leading-relaxed mb-4">Ear training — also called aural skills or solfège — is the systematic practice of developing your musical hearing. It's the process of learning to identify, reproduce, and understand musical elements purely through listening, without relying on written notation or an instrument in your hands.</p>
                <p class="text-gray-700 leading-relaxed mb-4">When a professional musician hears a chord progression and instantly names it, or transcribes a melody they've heard only once, or tunes their instrument by ear in a noisy room — that's the result of ear training. These abilities aren't a gift reserved for the naturally talented; they are learnable skills that respond to focused, consistent practice.</p>
                <p class="text-gray-700 leading-relaxed">At its core, ear training teaches your brain to make sense of sound. You learn to hear the relationships between notes (intervals), the quality of combinations of notes (chords), the patterns of organized pitch sets (scales), and the structure of time (rhythm). Together, these four pillars form the complete foundation of musical hearing.</p>
            </div>
            <div class="mt-6 bg-purple-50 border-l-4 border-purple-500 rounded-r-xl p-5">
                <div class="flex items-start gap-3">
                    <i data-lucide="lightbulb" class="w-5 h-5 text-purple-600 mt-0.5 flex-shrink-0"></i>
                    <div>
                        <p class="font-semibold text-purple-900 mb-1">Key Takeaway</p>
                        <p class="text-purple-800 text-sm leading-relaxed">Ear training is a learnable skill — not a talent. Every professional musician has practiced it, and you can too. The key is knowing what to practice and being consistent.</p>
                    </div>
                </div>
            </div>
        </article>

        {{-- CTA 1 --}}
        <div class="bg-white rounded-2xl p-6 flex flex-col sm:flex-row items-center gap-5 shadow-sm border border-gray-100 reveal">
            <div class="w-14 h-14 rounded-2xl bg-purple-100 flex items-center justify-center flex-shrink-0">
                <i data-lucide="play-circle" class="w-7 h-7 text-purple-600"></i>
            </div>
            <div class="text-center sm:text-left flex-1">
                <p class="font-semibold text-gray-900">Ready to start identifying intervals?</p>
                <p class="text-gray-500 text-sm mt-1">Harmoniva's interval exercises start from zero — no music theory background needed.</p>
            </div>
            <a href="/practice/melodic-interval-practice" class="bg-purple-600 hover:bg-purple-700 text-white font-semibold px-6 py-3 rounded-xl transition-colors whitespace-nowrap flex-shrink-0">
                Try It Free
            </a>
        </div>

        {{-- Chapter 2 --}}
        <article id="chapter-2" class="reveal">
            <div class="flex items-center gap-4 mb-6">
                <span class="w-10 h-10 rounded-xl bg-purple-600 text-white font-bold text-lg flex items-center justify-center flex-shrink-0">2</span>
                <h2 class="text-3xl font-bold text-gray-900">Why Ear Training Matters for Every Musician</h2>
            </div>
            <div class="prose prose-gray max-w-none">
                <p class="text-gray-700 leading-relaxed mb-4">Regardless of your instrument, genre, or goals, ear training is the skill that separates musicians who rely on the sheet music in front of them from those who truly hear, understand, and express music. It's not a specialized subject for classical conservatory students — it's the bedrock of every musical discipline.</p>
                <p class="text-gray-700 leading-relaxed mb-4">For improvisers, a trained ear means you hear where the music wants to go before you play a note. For songwriters, it means translating the melodies in your head directly to your instrument. For producers, it means identifying why a mix sounds muddy and exactly how to fix it. For orchestral musicians, it means staying in tune with your section even when the acoustic environment changes mid-performance.</p>
                <p class="text-gray-700 leading-relaxed">Research in music psychology consistently shows that musicians who practice aural skills develop stronger musical memory, faster learning speed for new repertoire, and greater improvisational fluency. The investment pays dividends in every other area of your musical life.</p>
            </div>
            <div class="mt-6 grid grid-cols-1 sm:grid-cols-3 gap-4">
                @foreach([['rocket', 'Learn Faster', 'Recognize patterns instantly and pick up new music in less time.'], ['mic-2', 'Perform Better', 'Stay in tune and respond to other musicians in real time.'], ['sparkles', 'Create More', 'Translate musical ideas from your imagination to reality.']] as $benefit)
                <div class="bg-white rounded-xl p-4 text-center border border-gray-100">
                    <i data-lucide="{{ $benefit[0] }}" class="w-6 h-6 text-purple-600 mx-auto mb-2"></i>
                    <p class="font-semibold text-gray-900 text-sm mb-1">{{ $benefit[1] }}</p>
                    <p class="text-gray-500 text-xs leading-relaxed">{{ $benefit[2] }}</p>
                </div>
                @endforeach
            </div>
            <div class="mt-6 bg-purple-50 border-l-4 border-purple-500 rounded-r-xl p-5">
                <div class="flex items-start gap-3">
                    <i data-lucide="lightbulb" class="w-5 h-5 text-purple-600 mt-0.5 flex-shrink-0"></i>
                    <div>
                        <p class="font-semibold text-purple-900 mb-1">Key Takeaway</p>
                        <p class="text-purple-800 text-sm leading-relaxed">Ear training isn't just academic. It directly and immediately improves your playing, composing, and musical communication — whatever style of music you make.</p>
                    </div>
                </div>
            </div>
        </article>

        {{-- Chapter 3 --}}
        <article id="chapter-3" class="reveal">
            <div class="flex items-center gap-4 mb-6">
                <span class="w-10 h-10 rounded-xl bg-purple-600 text-white font-bold text-lg flex items-center justify-center flex-shrink-0">3</span>
                <h2 class="text-3xl font-bold text-gray-900">Core Skills — Intervals, Chords, Scales & Rhythm</h2>
            </div>
            <div class="space-y-6">
                @foreach([
                    ['music', 'Intervals', 'An interval is the distance between two notes, measured in semitones. There are twelve intervals within an octave, from the minor second (one semitone) to the octave (twelve semitones). Learning to identify intervals is the single highest-leverage skill in ear training, because interval recognition underpins everything else — chords are stacks of intervals, scales are sequences of intervals, and melodies move through intervals. Classic mnemonic devices (like associating a minor third with the opening of "Smoke on the Water") can help beginners, but the ultimate goal is direct recognition without reference songs.'],
                    ['layers', 'Chords', 'Chords are three or more notes sounding simultaneously. The most common types — major, minor, diminished, augmented, dominant seventh, major seventh, minor seventh — each have a distinctive sonic "color" or quality that you can learn to recognize reliably. The key to chord identification is training your ear to notice the overall mood and tension of the sound, not just individual notes. Chord inversions add another layer of complexity, as the same chord sounds subtly different depending on which note is on the bottom.'],
                    ['sliders', 'Scales', 'Scales are organized sequences of notes that define the tonal "home base" of a piece of music. Major and minor scales are the most fundamental, but modal scales (Dorian, Phrygian, Lydian, Mixolydian, etc.) are essential for jazz, folk, and world music. Pentatonic scales appear everywhere from blues to classical Chinese music. Training your ear to recognize scales in context helps you understand why a piece sounds the way it does and how to navigate it harmonically.'],
                    ['activity', 'Rhythm', 'Rhythmic ear training is often neglected but equally important. It involves learning to identify time signatures, note values, and rhythmic patterns — and ultimately being able to notate rhythms you hear from scratch. Many musicians have excellent pitch discrimination but struggle with complex rhythms. Regular rhythm training, including clapping, counting aloud, and practicing with a metronome, builds the internal clock that makes everything else feel effortless.'],
                ] as $skill)
                <div class="bg-white rounded-2xl p-6 border border-gray-100">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-9 h-9 rounded-lg bg-purple-100 flex items-center justify-center">
                            <i data-lucide="{{ $skill[0] }}" class="w-5 h-5 text-purple-600"></i>
                        </div>
                        <h3 class="text-lg font-bold text-gray-900">{{ $skill[1] }}</h3>
                    </div>
                    <p class="text-gray-600 leading-relaxed text-sm">{{ $skill[2] }}</p>
                </div>
                @endforeach
            </div>
            <div class="mt-6 bg-purple-50 border-l-4 border-purple-500 rounded-r-xl p-5">
                <div class="flex items-start gap-3">
                    <i data-lucide="lightbulb" class="w-5 h-5 text-purple-600 mt-0.5 flex-shrink-0"></i>
                    <div>
                        <p class="font-semibold text-purple-900 mb-1">Key Takeaway</p>
                        <p class="text-purple-800 text-sm leading-relaxed">Start with intervals before moving to chords and scales. A solid interval foundation makes everything else click into place much faster.</p>
                    </div>
                </div>
            </div>
        </article>

        {{-- CTA 2 --}}
        <div class="bg-gradient-to-r from-orange-50 to-orange-100 rounded-2xl p-6 flex flex-col sm:flex-row items-center gap-5 border border-orange-200 reveal">
            <div class="w-14 h-14 rounded-2xl bg-orange-500 flex items-center justify-center flex-shrink-0">
                <i data-lucide="target" class="w-7 h-7 text-white"></i>
            </div>
            <div class="text-center sm:text-left flex-1">
                <p class="font-semibold text-gray-900">Practice all four core skills on Harmoniva</p>
                <p class="text-gray-600 text-sm mt-1">Intervals, chords, scales, and rhythm — all covered with adaptive exercises that meet you at your level.</p>
            </div>
            <a href="/register" class="bg-orange-500 hover:bg-orange-600 text-white font-semibold px-6 py-3 rounded-xl transition-colors whitespace-nowrap flex-shrink-0">
                Start Free
            </a>
        </div>

        {{-- Chapter 4 --}}
        <article id="chapter-4" class="reveal">
            <div class="flex items-center gap-4 mb-6">
                <span class="w-10 h-10 rounded-xl bg-purple-600 text-white font-bold text-lg flex items-center justify-center flex-shrink-0">4</span>
                <h2 class="text-3xl font-bold text-gray-900">How to Practice Effectively</h2>
            </div>
            <div class="prose prose-gray max-w-none">
                <p class="text-gray-700 leading-relaxed mb-4">The most important principle in ear training practice is consistency over intensity. Short daily sessions of 10–15 minutes will outperform a two-hour binge session on Sunday every single time. Your brain consolidates new auditory pathways during sleep, which means frequent exposure followed by rest is the optimal learning rhythm.</p>
                <p class="text-gray-700 leading-relaxed mb-4">Focus on one skill at a time until you reach a reliable accuracy level (80–90%) before layering in the next. Many beginners try to learn all interval types simultaneously and end up confusing themselves. Instead, master the perfect fifth and octave first, then add the major third and minor third, and build from there. This incremental approach feels slower but produces faster long-term results.</p>
                <p class="text-gray-700 leading-relaxed">Don't just practice on an app. Take what you learn into the real world. Listen to recordings and try to identify the chords. Play through scales on your instrument and notice their quality. Sing intervals before you play them. The loop between your practice sessions and active musical listening is where deep learning happens.</p>
            </div>
            <div class="mt-6 bg-white rounded-2xl p-6 border border-gray-100">
                <h4 class="font-bold text-gray-900 mb-4 flex items-center gap-2"><i data-lucide="calendar" class="w-5 h-5 text-purple-600"></i> Sample Daily Routine (15 minutes)</h4>
                <ol class="space-y-2 text-sm text-gray-700">
                    @foreach([
                        ['3 min', 'Warm up — sing a major scale from memory, then a minor scale'],
                        ['5 min', 'Interval identification — melodic intervals, focusing on your 2 weakest types'],
                        ['4 min', 'Chord quality — major vs. minor discrimination, random root notes'],
                        ['3 min', 'Rhythm — clap back a series of 4-bar patterns without slowing down'],
                    ] as $step)
                    <li class="flex items-start gap-3">
                        <span class="bg-purple-100 text-purple-700 text-xs font-bold px-2 py-0.5 rounded-md flex-shrink-0 mt-0.5">{{ $step[0] }}</span>
                        <span>{{ $step[1] }}</span>
                    </li>
                    @endforeach
                </ol>
            </div>
            <div class="mt-6 bg-purple-50 border-l-4 border-purple-500 rounded-r-xl p-5">
                <div class="flex items-start gap-3">
                    <i data-lucide="lightbulb" class="w-5 h-5 text-purple-600 mt-0.5 flex-shrink-0"></i>
                    <div>
                        <p class="font-semibold text-purple-900 mb-1">Key Takeaway</p>
                        <p class="text-purple-800 text-sm leading-relaxed">15 minutes every day beats 2 hours once a week. Design your practice to be sustainable, not heroic.</p>
                    </div>
                </div>
            </div>
        </article>

        {{-- Chapter 5 --}}
        <article id="chapter-5" class="reveal">
            <div class="flex items-center gap-4 mb-6">
                <span class="w-10 h-10 rounded-xl bg-purple-600 text-white font-bold text-lg flex items-center justify-center flex-shrink-0">5</span>
                <h2 class="text-3xl font-bold text-gray-900">Using Technology & AI in Ear Training</h2>
            </div>
            <div class="prose prose-gray max-w-none">
                <p class="text-gray-700 leading-relaxed mb-4">Technology has transformed ear training over the last decade. Where musicians once needed a teacher at a piano to drill interval recognition, today's apps can generate infinite unique exercises, track your responses, and adapt in real time. This isn't just convenient — it's a genuine improvement on traditional methods for many students.</p>
                <p class="text-gray-700 leading-relaxed mb-4">AI-powered platforms take this further by analyzing your error patterns across hundreds of sessions and intelligently skewing future questions toward your weak spots. Traditional apps treat all intervals as equally important and cycle through them uniformly. Adaptive AI notices that you confuse minor sixths and major sixths far more than any other pair, and automatically increases the frequency of those specific comparisons until your accuracy improves.</p>
                <p class="text-gray-700 leading-relaxed">The best approach combines technology with active musical listening. Use an app for structured drilling, but also make a habit of listening to recordings with the explicit goal of identifying what you hear. Try to figure out the chord when a new song starts. Transcribe a short melody by ear. These "application sessions" embed the skills your app drills into real musical context, and that's when the abilities become truly permanent.</p>
            </div>
            <div class="mt-6 bg-purple-50 border-l-4 border-purple-500 rounded-r-xl p-5">
                <div class="flex items-start gap-3">
                    <i data-lucide="lightbulb" class="w-5 h-5 text-purple-600 mt-0.5 flex-shrink-0"></i>
                    <div>
                        <p class="font-semibold text-purple-900 mb-1">Key Takeaway</p>
                        <p class="text-purple-800 text-sm leading-relaxed">AI doesn't replace deliberate practice — it makes deliberate practice more efficient by removing the guesswork about what to work on next.</p>
                    </div>
                </div>
            </div>
        </article>

        {{-- Chapter 6 --}}
        <article id="chapter-6" class="reveal">
            <div class="flex items-center gap-4 mb-6">
                <span class="w-10 h-10 rounded-xl bg-purple-600 text-white font-bold text-lg flex items-center justify-center flex-shrink-0">6</span>
                <h2 class="text-3xl font-bold text-gray-900">Beginner to Advanced — Your Roadmap</h2>
            </div>
            <div class="prose prose-gray max-w-none">
                <p class="text-gray-700 leading-relaxed mb-4">Ear training progress follows a predictable arc. Beginners typically start by distinguishing major from minor and identifying large intervals (octaves and fifths). Within a few weeks of daily practice, most people can reliably identify all twelve intervals. From there, the focus shifts to chord quality, then to more nuanced skills like chord inversions, scale modes, and eventually melodic dictation.</p>
                <p class="text-gray-700 leading-relaxed mb-4">The intermediate plateau — where you can identify individual intervals easily but struggle with musical context — is the most common sticking point. The solution is to practice with real musical examples, not just computer-generated tones. Listen to how intervals appear in the context of a phrase, how chord progressions create tension and release, how rhythmic feels differ across genres.</p>
                <p class="text-gray-700 leading-relaxed">Advanced ear training never really ends — even professional musicians continue refining their hearing throughout their careers. But you'll know you've reached an advanced level when you can sit at a piano, listen to a song once, and reproduce the chord progression accurately; when you can transcribe a melody on the first or second pass; and when your instrument playing reflects your inner hearing immediately and effortlessly.</p>
            </div>
            <div class="mt-8 grid grid-cols-1 sm:grid-cols-3 gap-4">
                @foreach([
                    ['Beginner', '0–3 months', 'bg-green-50 border-green-200', 'text-green-700', ['Melodic interval ID (all 12)', 'Major vs. minor chords', 'Simple rhythm patterns', 'Single note recognition']],
                    ['Intermediate', '3–12 months', 'bg-blue-50 border-blue-200', 'text-blue-700', ['Harmonic intervals', 'All chord types + inversions', 'Scale & mode ID', 'Rhythmic dictation']],
                    ['Advanced', '1+ years', 'bg-purple-50 border-purple-200', 'text-purple-700', ['Melodic dictation', 'Complex chord progressions', 'Relative pitch mastery', 'Modal harmony']],
                ] as $level)
                <div class="rounded-2xl p-5 border {{ $level[2] }}">
                    <div class="mb-3">
                        <span class="font-bold {{ $level[3] }} text-base">{{ $level[0] }}</span>
                        <span class="text-gray-400 text-xs ml-2">{{ $level[1] }}</span>
                    </div>
                    <ul class="space-y-1.5">
                        @foreach($level[4] as $item)
                        <li class="flex items-center gap-2 text-sm text-gray-700">
                            <i data-lucide="check" class="w-3.5 h-3.5 {{ $level[3] }} flex-shrink-0"></i>
                            {{ $item }}
                        </li>
                        @endforeach
                    </ul>
                </div>
                @endforeach
            </div>
            <div class="mt-6 bg-purple-50 border-l-4 border-purple-500 rounded-r-xl p-5">
                <div class="flex items-start gap-3">
                    <i data-lucide="lightbulb" class="w-5 h-5 text-purple-600 mt-0.5 flex-shrink-0"></i>
                    <div>
                        <p class="font-semibold text-purple-900 mb-1">Key Takeaway</p>
                        <p class="text-purple-800 text-sm leading-relaxed">Progress is nonlinear. Breakthroughs often happen suddenly after plateaus. Trust the process, show up daily, and the results will come.</p>
                    </div>
                </div>
            </div>
        </article>

    </div>
</div>

{{-- Final CTA --}}
<section class="bg-gradient-to-br from-purple-600 to-purple-800 py-20 px-4">
    <div class="max-w-2xl mx-auto text-center reveal">
        <h2 class="text-3xl font-bold text-white mb-4">Ready to Put This Guide Into Practice?</h2>
        <p class="text-purple-200 text-lg mb-8 max-w-lg mx-auto">Harmoniva makes it easy to follow exactly the kind of structured, adaptive practice this guide recommends — starting from day one, completely free.</p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="/register" class="inline-flex items-center justify-center gap-2 bg-orange-500 hover:bg-orange-600 text-white font-semibold px-8 py-4 rounded-xl transition-colors shadow-lg text-lg">
                <i data-lucide="play-circle" class="w-5 h-5"></i>
                Start Training Free
            </a>
            <a href="/learn" class="inline-flex items-center justify-center gap-2 bg-white/10 hover:bg-white/20 text-white font-semibold px-8 py-4 rounded-xl transition-colors">
                <i data-lucide="map" class="w-5 h-5"></i>
                View Learning Path
            </a>
        </div>
    </div>
</section>

@endsection
