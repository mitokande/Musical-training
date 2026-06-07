@extends('layouts.standalone')

@section('title', 'Articles & Resources')
@section('description', 'Explore Harmoniva\'s library of ear training articles, music theory guides, practice tips, and AI insights for musicians of all levels.')

@section('content')

{{-- Hero --}}
<section class="bg-gradient-to-br from-purple-700 via-purple-600 to-purple-800 text-white py-20 px-4">
    <div class="max-w-3xl mx-auto text-center reveal">
        <div class="inline-flex items-center gap-2 bg-white/10 text-white text-sm font-medium px-4 py-2 rounded-full mb-6">
            <i data-lucide="book-open" class="w-4 h-4"></i>
            Resources
        </div>
        <h1 class="text-4xl md:text-5xl font-bold mb-4">Learn. Practice. Improve.</h1>
        <p class="text-purple-200 text-lg max-w-xl mx-auto">In-depth articles on ear training, music theory, effective practice habits, and the role of AI in modern music education.</p>
    </div>
</section>

{{-- Category Filter Tabs --}}
<section class="bg-white border-b border-gray-100 sticky top-0 z-10 px-4 shadow-sm">
    <div class="max-w-6xl mx-auto flex items-center gap-2 overflow-x-auto py-4 scrollbar-none" x-data="{ active: 'All' }">
        @php
        $cats = ['All', 'Ear Training', 'Music Theory', 'Practice Tips', 'AI & Technology'];
        $catColors = [
            'All' => 'purple',
            'Ear Training' => 'purple',
            'Music Theory' => 'blue',
            'Practice Tips' => 'green',
            'AI & Technology' => 'orange',
        ];
        @endphp
        @foreach($cats as $cat)
        <button
            @click="active = '{{ $cat }}'"
            :class="active === '{{ $cat }}' ? 'bg-purple-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'"
            class="flex-shrink-0 px-5 py-2 rounded-full text-sm font-medium transition-colors"
        >{{ $cat }}</button>
        @endforeach
    </div>
</section>

{{-- Articles Grid --}}
<section class="bg-[#FAF7F2] py-20 px-4">
    <div class="max-w-6xl mx-auto">

        @php
        $articles = [
            [
                'category' => 'Ear Training',
                'cat_color' => 'purple',
                'title' => 'How to Train Your Ear in 10 Minutes a Day',
                'excerpt' => 'You don\'t need marathon practice sessions to build strong ears. Discover a focused daily routine that delivers real results even on your busiest days.',
                'read_time' => '5 min read',
                'icon' => 'headphones',
            ],
            [
                'category' => 'Music Theory',
                'cat_color' => 'blue',
                'title' => 'Understanding Intervals: A Beginner\'s Guide',
                'excerpt' => 'Intervals are the distances between notes — and they\'re the foundation of everything in music. This guide breaks them down in plain English with practical examples.',
                'read_time' => '7 min read',
                'icon' => 'music-2',
            ],
            [
                'category' => 'Practice Tips',
                'cat_color' => 'green',
                'title' => '5 Common Ear Training Mistakes (and How to Fix Them)',
                'excerpt' => 'Are you practicing the wrong way? Most musicians make the same handful of mistakes that slow their progress dramatically. Here\'s how to course-correct.',
                'read_time' => '6 min read',
                'icon' => 'alert-triangle',
            ],
            [
                'category' => 'AI & Technology',
                'cat_color' => 'orange',
                'title' => 'How AI Is Changing the Way Musicians Learn',
                'excerpt' => 'Adaptive learning algorithms can now identify your specific weaknesses and tailor exercises to address them. We explore what that means for music education.',
                'read_time' => '8 min read',
                'icon' => 'brain',
            ],
            [
                'category' => 'Music Theory',
                'cat_color' => 'blue',
                'title' => 'Major vs. Minor: Why the Difference Matters',
                'excerpt' => 'The gap between major and minor is just one semitone — yet it shapes the entire emotional character of a piece. Learn to hear and use this distinction confidently.',
                'read_time' => '5 min read',
                'icon' => 'sliders',
            ],
            [
                'category' => 'Ear Training',
                'cat_color' => 'purple',
                'title' => 'Melodic Dictation: From Intimidating to Achievable',
                'excerpt' => 'Writing down melodies you hear seems impossible at first. This step-by-step approach breaks the skill into manageable pieces that anyone can master with patience.',
                'read_time' => '9 min read',
                'icon' => 'pen-tool',
            ],
            [
                'category' => 'Practice Tips',
                'cat_color' => 'green',
                'title' => 'Building a Consistent Practice Habit That Actually Sticks',
                'excerpt' => 'Motivation fades — systems don\'t. Learn the behavioral science behind habit formation and how to design a practice routine you\'ll maintain for the long term.',
                'read_time' => '6 min read',
                'icon' => 'calendar-check',
            ],
            [
                'category' => 'Ear Training',
                'cat_color' => 'purple',
                'title' => 'Chord Recognition: A Practical Approach for Guitarists and Pianists',
                'excerpt' => 'Identifying chords by ear is one of the most practical skills a musician can have. This guide gives you a systematic method and memorable reference points for every chord type.',
                'read_time' => '7 min read',
                'icon' => 'layers',
            ],
            [
                'category' => 'AI & Technology',
                'cat_color' => 'orange',
                'title' => 'Spaced Repetition in Music Education: The Science of Remembering',
                'excerpt' => 'The same memory science used to learn languages can supercharge your ear training. Here\'s how spaced repetition works and why Harmoniva applies it under the hood.',
                'read_time' => '6 min read',
                'icon' => 'refresh-cw',
            ],
        ];

        $catBgMap = [
            'purple' => 'bg-purple-100 text-purple-700',
            'blue'   => 'bg-blue-100 text-blue-700',
            'green'  => 'bg-green-100 text-green-700',
            'orange' => 'bg-orange-100 text-orange-700',
        ];
        @endphp

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-7">
            @foreach($articles as $article)
            <article class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition-shadow group reveal">
                <div class="p-6 flex flex-col h-full">
                    <div class="flex items-center justify-between mb-4">
                        <span class="text-xs font-semibold px-3 py-1 rounded-full {{ $catBgMap[$article['cat_color']] }}">
                            {{ $article['category'] }}
                        </span>
                        <span class="text-xs text-gray-400 flex items-center gap-1">
                            <i data-lucide="clock" class="w-3.5 h-3.5"></i>
                            {{ $article['read_time'] }}
                        </span>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-{{ $article['cat_color'] }}-50 flex items-center justify-center mb-4">
                        <i data-lucide="{{ $article['icon'] }}" class="w-5 h-5 text-{{ $article['cat_color'] }}-600"></i>
                    </div>
                    <h2 class="text-lg font-bold text-gray-900 mb-3 group-hover:text-purple-600 transition-colors leading-snug">
                        {{ $article['title'] }}
                    </h2>
                    <p class="text-sm text-gray-500 leading-relaxed mb-5 flex-1">
                        {{ $article['excerpt'] }}
                    </p>
                    <a href="#" class="inline-flex items-center gap-1.5 text-purple-600 font-semibold text-sm hover:gap-2.5 transition-all">
                        Read more <i data-lucide="arrow-right" class="w-4 h-4"></i>
                    </a>
                </div>
            </article>
            @endforeach
        </div>

    </div>
</section>

{{-- Newsletter Signup --}}
<section class="bg-gradient-to-br from-purple-600 to-purple-800 py-20 px-4">
    <div class="max-w-2xl mx-auto text-center reveal">
        <div class="w-14 h-14 rounded-2xl bg-white/10 flex items-center justify-center mx-auto mb-6">
            <i data-lucide="mail" class="w-7 h-7 text-white"></i>
        </div>
        <h2 class="text-3xl font-bold text-white mb-3">Get Weekly Ear Training Tips</h2>
        <p class="text-purple-200 mb-8 text-lg">Practical exercises, theory breakdowns, and musician stories — delivered to your inbox every week.</p>
        <form action="#" method="POST" class="flex flex-col sm:flex-row gap-3 max-w-md mx-auto">
            @csrf
            <input
                type="email"
                placeholder="Your email address"
                class="flex-1 rounded-xl px-5 py-3.5 text-gray-800 focus:outline-none focus:ring-4 focus:ring-orange-400 shadow-lg"
                required
            />
            <button type="submit" class="bg-orange-500 hover:bg-orange-600 text-white font-semibold px-6 py-3.5 rounded-xl transition-colors shadow-lg whitespace-nowrap">
                Subscribe Free
            </button>
        </form>
        <p class="text-purple-300 text-sm mt-4">No spam, ever. Unsubscribe in one click.</p>
    </div>
</section>

@endsection
