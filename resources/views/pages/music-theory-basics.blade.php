@extends('layouts.standalone')

@section('title', 'Music Theory Basics')
@section('description', 'Learn the fundamentals of music theory — notes, intervals, scales, chords, rhythm, and key signatures — explained simply and clearly for musicians of all levels.')

@section('content')

{{-- Hero --}}
<section class="bg-gradient-to-br from-purple-700 via-purple-600 to-purple-800 text-white py-20 px-4">
    <div class="max-w-3xl mx-auto text-center reveal">
        <div class="inline-flex items-center gap-2 bg-white/10 text-white text-sm font-medium px-4 py-2 rounded-full mb-6">
            <i data-lucide="book" class="w-4 h-4"></i>
            Music Theory
        </div>
        <h1 class="text-4xl md:text-5xl font-bold mb-5">Music Theory Made Simple</h1>
        <p class="text-purple-200 text-xl max-w-2xl mx-auto leading-relaxed">The essential concepts every musician should know — explained clearly, without unnecessary jargon, with direct connections to ear training practice.</p>
    </div>
</section>

{{-- Topic Cards --}}
<section class="bg-[#FAF7F2] py-20 px-4">
    <div class="max-w-6xl mx-auto">
        <h2 class="text-3xl font-bold text-gray-900 text-center mb-3 reveal">Core Topics</h2>
        <p class="text-gray-500 text-center mb-12 reveal">Click any topic to explore it in depth.</p>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">

            @php
            $topics = [
                ['icon' => 'music', 'color' => 'purple', 'title' => 'Notes & Pitch', 'desc' => 'The musical alphabet, octaves, accidentals, and how pitch is organized across instruments. The foundation everything else is built upon.'],
                ['icon' => 'arrow-up-down', 'color' => 'blue', 'title' => 'Intervals', 'desc' => 'The distances between notes — from the tight half-step to the wide octave. Master intervals and you can understand any chord, scale, or melody.'],
                ['icon' => 'sliders', 'color' => 'green', 'title' => 'Scales & Modes', 'desc' => 'Major, minor, pentatonic, and modal scales. Learn how scales define the character and emotional color of music across every genre and tradition.'],
                ['icon' => 'layers', 'color' => 'orange', 'title' => 'Chords & Harmony', 'desc' => 'How notes stack together to create major, minor, diminished, augmented, and seventh chords — and how chord progressions create musical narrative.'],
                ['icon' => 'activity', 'color' => 'red', 'title' => 'Rhythm & Time', 'desc' => 'Time signatures, note values, rests, and syncopation. Rhythm is the pulse of music — understanding it makes everything feel more natural to play.'],
                ['icon' => 'key', 'color' => 'yellow', 'title' => 'Key Signatures', 'desc' => 'Sharps and flats in key signatures, the circle of fifths, and how keys relate to scales and tonality. Essential for reading and writing music.'],
            ];
            $colorMap = [
                'purple' => ['bg' => 'bg-purple-100', 'text' => 'text-purple-600', 'badge' => 'bg-purple-600'],
                'blue'   => ['bg' => 'bg-blue-100',   'text' => 'text-blue-600',   'badge' => 'bg-blue-600'],
                'green'  => ['bg' => 'bg-green-100',  'text' => 'text-green-600',  'badge' => 'bg-green-600'],
                'orange' => ['bg' => 'bg-orange-100', 'text' => 'text-orange-500', 'badge' => 'bg-orange-500'],
                'red'    => ['bg' => 'bg-red-100',    'text' => 'text-red-500',    'badge' => 'bg-red-500'],
                'yellow' => ['bg' => 'bg-yellow-100', 'text' => 'text-yellow-600', 'badge' => 'bg-yellow-600'],
            ];
            @endphp

            @foreach($topics as $topic)
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 hover:shadow-md transition-shadow group reveal">
                <div class="w-12 h-12 rounded-xl {{ $colorMap[$topic['color']]['bg'] }} flex items-center justify-center mb-4">
                    <i data-lucide="{{ $topic['icon'] }}" class="w-6 h-6 {{ $colorMap[$topic['color']]['text'] }}"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-3">{{ $topic['title'] }}</h3>
                <p class="text-gray-500 text-sm leading-relaxed mb-5">{{ $topic['desc'] }}</p>
                <a href="#" class="inline-flex items-center gap-1.5 text-sm font-semibold {{ $colorMap[$topic['color']]['text'] }} hover:gap-2.5 transition-all">
                    Explore <i data-lucide="arrow-right" class="w-4 h-4"></i>
                </a>
            </div>
            @endforeach

        </div>
    </div>
</section>

{{-- Featured: Intervals --}}
<section class="bg-white py-20 px-4">
    <div class="max-w-5xl mx-auto">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
            <div class="reveal">
                <div class="inline-flex items-center gap-2 bg-purple-100 text-purple-700 text-sm font-semibold px-4 py-2 rounded-full mb-6">
                    <i data-lucide="star" class="w-4 h-4"></i>
                    Featured Topic
                </div>
                <h2 class="text-3xl font-bold text-gray-900 mb-5">Intervals — The Building Blocks of Music</h2>
                <p class="text-gray-600 leading-relaxed mb-4">An interval is the distance between two notes, measured in semitones (half steps). Understanding intervals is arguably the single most important music theory concept you can learn, because every other element of music — chords, scales, melodies, progressions — is built from intervals.</p>
                <p class="text-gray-600 leading-relaxed mb-4">There are twelve distinct intervals within a single octave. Each has a distinctive sound: the perfect fifth has an open, powerful quality; the major third feels bright and stable; the minor second creates tension and dissonance. Learning to recognize these sounds both in theory and by ear transforms your relationship with music.</p>
                <p class="text-gray-600 leading-relaxed">The two most important interval qualities to understand are major/minor (which differ by one semitone) and perfect/augmented/diminished (applied to fourths, fifths, and octaves). Once you can hear and name any interval confidently, chord and scale recognition become dramatically easier.</p>
            </div>
            <div class="reveal">
                <div class="bg-[#FAF7F2] rounded-2xl p-6 border border-gray-100">
                    <h3 class="font-bold text-gray-900 mb-4 flex items-center gap-2">
                        <i data-lucide="table" class="w-5 h-5 text-purple-600"></i>
                        All 12 Intervals at a Glance
                    </h3>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-gray-200">
                                    <th class="text-left py-2 pr-4 text-gray-500 font-semibold">Semitones</th>
                                    <th class="text-left py-2 pr-4 text-gray-500 font-semibold">Name</th>
                                    <th class="text-left py-2 text-gray-500 font-semibold">Abbreviation</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach([
                                    [1,  'Minor 2nd',      'm2'],
                                    [2,  'Major 2nd',      'M2'],
                                    [3,  'Minor 3rd',      'm3'],
                                    [4,  'Major 3rd',      'M3'],
                                    [5,  'Perfect 4th',    'P4'],
                                    [6,  'Tritone',        'TT'],
                                    [7,  'Perfect 5th',    'P5'],
                                    [8,  'Minor 6th',      'm6'],
                                    [9,  'Major 6th',      'M6'],
                                    [10, 'Minor 7th',      'm7'],
                                    [11, 'Major 7th',      'M7'],
                                    [12, 'Perfect Octave', '8ve'],
                                ] as $row)
                                <tr>
                                    <td class="py-2 pr-4">
                                        <span class="w-7 h-7 rounded-lg bg-purple-100 text-purple-700 text-xs font-bold inline-flex items-center justify-center">{{ $row[0] }}</span>
                                    </td>
                                    <td class="py-2 pr-4 text-gray-800 font-medium">{{ $row[1] }}</td>
                                    <td class="py-2 text-gray-500 font-mono">{{ $row[2] }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- Quick Reference: Note Names --}}
<section class="bg-[#FAF7F2] py-16 px-4">
    <div class="max-w-5xl mx-auto">
        <h2 class="text-2xl font-bold text-gray-900 mb-8 text-center reveal">Quick Reference: The Musical Alphabet</h2>
        <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-7 gap-3 mb-8 reveal">
            @foreach(['C', 'D', 'E', 'F', 'G', 'A', 'B'] as $note)
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 text-center">
                <span class="text-3xl font-bold text-purple-600">{{ $note }}</span>
                <div class="mt-2 space-y-1">
                    <div class="text-xs text-gray-400">{{ $note }}♯ / {{ chr(ord($note)+1 > ord('G') ? ord('A') : ord($note)+1) }}♭</div>
                </div>
            </div>
            @endforeach
        </div>
        <p class="text-gray-500 text-sm text-center reveal">Notes repeat in octaves. The same note name at a higher pitch is one octave up — exactly 12 semitones (half steps) higher in frequency.</p>
    </div>
</section>

{{-- Theory + Ear Training Connection --}}
<section class="bg-white py-20 px-4">
    <div class="max-w-4xl mx-auto">
        <div class="text-center mb-12 reveal">
            <h2 class="text-3xl font-bold text-gray-900 mb-4">How Theory and Ear Training Work Together</h2>
            <p class="text-gray-500 text-lg max-w-2xl mx-auto">Music theory gives you the language. Ear training lets you hear what that language sounds like. Together, they're unstoppable.</p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 reveal">
            @foreach([
                ['book', 'purple', 'Learn the Concept', 'Understand what a major third is — its interval size, its position in a scale, its role in a major chord.'],
                ['headphones', 'orange', 'Train Your Ear', 'Practice identifying major thirds in isolation and in context until recognition is automatic.'],
                ['music', 'green', 'Apply in Music', 'Hear major thirds everywhere — in melodies, chords, arpeggios — and understand exactly what you\'re hearing.'],
            ] as $step)
            <div class="text-center">
                <div class="w-14 h-14 rounded-2xl bg-{{ $step[1] }}-100 flex items-center justify-center mx-auto mb-4">
                    <i data-lucide="{{ $step[0] }}" class="w-7 h-7 text-{{ $step[1] }}-600"></i>
                </div>
                <h3 class="font-bold text-gray-900 mb-2">{{ $step[2] }}</h3>
                <p class="text-gray-500 text-sm leading-relaxed">{{ $step[3] }}</p>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- CTA --}}
<section class="bg-gradient-to-br from-purple-600 to-purple-800 py-20 px-4">
    <div class="max-w-2xl mx-auto text-center reveal">
        <h2 class="text-3xl font-bold text-white mb-4">Turn Theory into Hearing</h2>
        <p class="text-purple-200 text-lg mb-8">Harmoniva's exercises are the perfect companion to theory study — practice hearing every concept you've just learned.</p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="/register" class="inline-flex items-center justify-center gap-2 bg-orange-500 hover:bg-orange-600 text-white font-semibold px-8 py-4 rounded-xl transition-colors shadow-lg text-lg">
                <i data-lucide="play-circle" class="w-5 h-5"></i>
                Start Practicing Free
            </a>
            <a href="/ear-training-guide" class="inline-flex items-center justify-center gap-2 bg-white/10 hover:bg-white/20 text-white font-semibold px-8 py-4 rounded-xl transition-colors">
                <i data-lucide="book-open" class="w-5 h-5"></i>
                Read the Full Ear Training Guide
            </a>
        </div>
    </div>
</section>

@endsection
