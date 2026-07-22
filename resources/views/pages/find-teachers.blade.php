@extends('layouts.standalone')

@section('title', 'Find Teachers & Music Schools')
@section('description', 'Browse every verified music teacher and music school on Harmoniva. View their profiles, read student reviews, book a lesson, and start learning with expert guidance.')

@section('content')

{{-- ============ HERO / EXPLANATION ============ --}}
<section class="bg-gradient-to-br from-purple-700 via-purple-600 to-purple-800 text-white py-20 px-4 relative overflow-hidden">
    <div class="absolute inset-0 opacity-10" style="background-image:radial-gradient(circle at 15% 25%, #fff 0, transparent 40%), radial-gradient(circle at 85% 75%, #f97316 0, transparent 40%);"></div>
    <div class="max-w-3xl mx-auto text-center reveal relative">
        <div class="hero-badge inline-flex items-center gap-2 bg-white/10 text-white text-sm font-medium px-4 py-2 rounded-full mb-6">
            <i data-lucide="users" class="w-4 h-4"></i>
            Teachers &amp; Schools
        </div>
        <h1 class="text-4xl md:text-5xl font-bold mb-5">Find Your Teacher or Music School</h1>
        <p class="text-purple-200 text-xl max-w-2xl mx-auto leading-relaxed">Self-guided practice is powerful — expert guidance makes it unstoppable. Every teacher and school below has a verified Harmoniva profile: browse their background, read real student reviews, message them directly, and book a lesson when you're ready.</p>
        <div class="flex flex-wrap justify-center gap-4 mt-8 text-sm text-purple-200">
            <span class="flex items-center gap-1.5"><i data-lucide="badge-check" class="w-4 h-4"></i> Verified profiles</span>
            <span class="flex items-center gap-1.5"><i data-lucide="star" class="w-4 h-4"></i> Real student reviews</span>
            <span class="flex items-center gap-1.5"><i data-lucide="calendar-check" class="w-4 h-4"></i> Online lesson booking</span>
        </div>
    </div>
</section>

{{-- ============ HOW IT WORKS (mini) ============ --}}
<section class="bg-white border-b border-gray-100 py-12 px-4">
    <div class="max-w-4xl mx-auto reveal">
        <div class="grid sm:grid-cols-3 gap-6">
            @php
            $steps = [
                ['icon'=>'search','title'=>'1. Browse & compare','desc'=>'Explore profiles below — instruments, experience, teaching style, student reviews, photos and videos.'],
                ['icon'=>'message-circle','title'=>'2. Connect or book','desc'=>'Send a connection request or book an available lesson slot straight from the teacher\'s profile.'],
                ['icon'=>'graduation-cap','title'=>'3. Learn together','desc'=>'Your teacher assigns tailored exercises inside Harmoniva and tracks your progress as you practice.'],
            ];
            @endphp
            @foreach($steps as $s)
            <div class="text-center">
                <div class="w-12 h-12 mx-auto rounded-xl bg-purple-100 text-purple-600 flex items-center justify-center mb-3">
                    <i data-lucide="{{ $s['icon'] }}" class="w-5 h-5"></i>
                </div>
                <h3 class="font-bold text-gray-900 text-sm mb-1.5">{{ $s['title'] }}</h3>
                <p class="text-gray-500 text-sm leading-relaxed">{{ $s['desc'] }}</p>
            </div>
            @endforeach
        </div>
    </div>
</section>

<div class="bg-[#FAF7F2] py-16 px-4">
    <div class="max-w-5xl mx-auto space-y-16">

        @php
            // Shared card renderer data helper: chips from expertise areas or genres.
            $chipSource = fn ($p) => collect($p->expertise_areas ?? [])->merge($p->genres ?? [])->filter()->unique()->take(3);
        @endphp

        {{-- ============ TEACHERS ============ --}}
        <section id="teachers" class="reveal scroll-mt-24">
            <div class="flex items-end justify-between mb-6 flex-wrap gap-3">
                <div>
                    <span class="text-xs font-bold uppercase tracking-[0.2em] text-purple-600 mb-2 block">Private tutors</span>
                    <h2 class="text-2xl md:text-3xl font-extrabold text-gray-900">Music Teachers</h2>
                </div>
                <span class="text-sm text-gray-400 font-medium">{{ $teachers->count() }} {{ Str::plural('teacher', $teachers->count()) }}</span>
            </div>

            @if($teachers->isEmpty())
                <div class="light-card rounded-2xl p-10 text-center text-gray-500">
                    <i data-lucide="users" class="w-8 h-8 mx-auto mb-3 text-gray-300"></i>
                    No teachers have published a profile yet — check back soon.
                </div>
            @else
                <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
                    @foreach($teachers as $p)
                    @php $stats = $reviewStats->get($p->id); @endphp
                    <a href="{{ $p->publicUrl() }}" class="light-card rounded-2xl overflow-hidden group hover:shadow-lg hover:-translate-y-1 transition-all flex flex-col">
                        <div class="h-20 relative" style="background:linear-gradient(135deg,#9333ea22,#f9731622);">
                            @if($p->coverImageUrl())
                                <img src="{{ $p->coverImageUrl() }}" alt="" class="absolute inset-0 w-full h-full object-cover">
                            @endif
                            @if($p->accepts_students)
                                <span class="absolute top-3 right-3 inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-green-100/95 text-green-700 text-[11px] font-bold">
                                    <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span> Accepting students
                                </span>
                            @endif
                        </div>
                        <div class="px-5 pb-5 flex-1 flex flex-col">
                            <div class="-mt-7 mb-3">
                                @if($p->user->hasAvatar())
                                    <img src="{{ $p->user->avatar }}" alt="{{ $p->displayName() }}" class="w-14 h-14 rounded-2xl object-cover ring-4 ring-white shadow">
                                @else
                                    <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-purple-500 to-orange-400 ring-4 ring-white shadow flex items-center justify-center text-white font-bold text-lg">
                                        {{ strtoupper(mb_substr($p->displayName(), 0, 1)) }}
                                    </div>
                                @endif
                            </div>
                            <h3 class="font-bold text-gray-900 group-hover:text-purple-700 transition-colors leading-tight">{{ $p->displayName() }}</h3>
                            @if($p->headline)
                                <p class="text-sm text-gray-500 mt-0.5 line-clamp-2">{{ $p->headline }}</p>
                            @endif
                            <div class="flex flex-wrap items-center gap-x-3 gap-y-1 mt-2.5 text-xs text-gray-400">
                                @if($p->primary_instrument)
                                    <span class="flex items-center gap-1"><i data-lucide="music-2" class="w-3.5 h-3.5"></i>{{ $p->primary_instrument }}</span>
                                @endif
                                @if($p->city || $p->country)
                                    <span class="flex items-center gap-1"><i data-lucide="map-pin" class="w-3.5 h-3.5"></i>{{ collect([$p->city, $p->country])->filter()->implode(', ') }}</span>
                                @endif
                            </div>
                            @if($chipSource($p)->isNotEmpty())
                                <div class="flex flex-wrap gap-1.5 mt-3">
                                    @foreach($chipSource($p) as $chip)
                                        <span class="px-2 py-0.5 rounded-md bg-purple-50 text-purple-700 text-[11px] font-semibold">{{ $chip }}</span>
                                    @endforeach
                                </div>
                            @endif
                            <div class="mt-auto pt-4 flex items-center justify-between">
                                @if($stats)
                                    <span class="flex items-center gap-1 text-sm">
                                        <i data-lucide="star" class="w-4 h-4 text-amber-400 fill-amber-400"></i>
                                        <span class="font-bold text-gray-800">{{ number_format($stats->rating_avg, 1) }}</span>
                                        <span class="text-gray-400 text-xs">({{ $stats->reviews_count }})</span>
                                    </span>
                                @else
                                    <span class="text-xs text-gray-300">New profile</span>
                                @endif
                                <span class="inline-flex items-center gap-1 text-sm font-semibold text-purple-600">View profile <i data-lucide="arrow-right" class="w-3.5 h-3.5 transition-transform group-hover:translate-x-0.5"></i></span>
                            </div>
                        </div>
                    </a>
                    @endforeach
                </div>
            @endif
        </section>

        {{-- ============ SCHOOLS ============ --}}
        <section id="schools" class="reveal scroll-mt-24">
            <div class="flex items-end justify-between mb-6 flex-wrap gap-3">
                <div>
                    <span class="text-xs font-bold uppercase tracking-[0.2em] text-orange-600 mb-2 block">Institutions</span>
                    <h2 class="text-2xl md:text-3xl font-extrabold text-gray-900">Music Schools</h2>
                </div>
                <span class="text-sm text-gray-400 font-medium">{{ $schools->count() }} {{ Str::plural('school', $schools->count()) }}</span>
            </div>

            <p class="text-gray-600 leading-relaxed mb-6 max-w-3xl">Music schools on Harmoniva work just like teachers, at a larger scale: a school manages its own roster of teachers, enrolls students, assigns curriculum-based exercises to whole classes, and follows every student's progress from one panel. Join a school to get structured group instruction backed by Harmoniva's practice engine.</p>

            @if($schools->isEmpty())
                <div class="light-card rounded-2xl p-10 text-center text-gray-500">
                    <i data-lucide="building-2" class="w-8 h-8 mx-auto mb-3 text-gray-300"></i>
                    No schools have published a profile yet — check back soon.
                </div>
            @else
                <div class="grid sm:grid-cols-2 gap-5">
                    @foreach($schools as $p)
                    @php $stats = $reviewStats->get($p->id); @endphp
                    <a href="{{ $p->publicUrl() }}" class="light-card rounded-2xl p-5 group hover:shadow-lg hover:-translate-y-1 transition-all flex gap-4">
                        @if($p->user->hasAvatar())
                            <img src="{{ $p->user->avatar }}" alt="{{ $p->displayName() }}" class="w-16 h-16 rounded-2xl object-cover flex-shrink-0 shadow">
                        @else
                            <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-orange-400 to-purple-500 flex items-center justify-center text-white flex-shrink-0 shadow">
                                <i data-lucide="building-2" class="w-7 h-7"></i>
                            </div>
                        @endif
                        <div class="flex-1 min-w-0">
                            <h3 class="font-bold text-gray-900 group-hover:text-purple-700 transition-colors leading-tight">{{ $p->displayName() }}</h3>
                            @if($p->headline)
                                <p class="text-sm text-gray-500 mt-0.5 line-clamp-2">{{ $p->headline }}</p>
                            @endif
                            <div class="flex flex-wrap items-center gap-x-3 gap-y-1 mt-2 text-xs text-gray-400">
                                @if($p->city || $p->country)
                                    <span class="flex items-center gap-1"><i data-lucide="map-pin" class="w-3.5 h-3.5"></i>{{ collect([$p->city, $p->country])->filter()->implode(', ') }}</span>
                                @endif
                                @if($stats)
                                    <span class="flex items-center gap-1"><i data-lucide="star" class="w-3.5 h-3.5 text-amber-400 fill-amber-400"></i><span class="font-bold text-gray-700">{{ number_format($stats->rating_avg, 1) }}</span> ({{ $stats->reviews_count }})</span>
                                @endif
                            </div>
                            <span class="inline-flex items-center gap-1 text-sm font-semibold text-purple-600 mt-3">View school <i data-lucide="arrow-right" class="w-3.5 h-3.5 transition-transform group-hover:translate-x-0.5"></i></span>
                        </div>
                    </a>
                    @endforeach
                </div>
            @endif
        </section>

        {{-- ============ FAQ ============ --}}
        <section class="reveal">
            @include('pages.partials.guide-faq', ['faqs' => [
                ['q'=>'Are these profiles verified?','a'=>'Yes — every teacher and school profile is reviewed by the Harmoniva team before it becomes publicly visible, so you only see genuine, approved educators here.'],
                ['q'=>'How do I connect with a teacher or school?','a'=>'Open their profile and send a connection request, or book an available lesson slot directly if they have online booking enabled. Once connected, they can assign you exercises and message you inside Harmoniva.'],
                ['q'=>'What\'s the difference between a teacher and a school?','a'=>'A teacher is an individual tutor; a school is an institution with multiple teachers on its roster. Both can enroll you as a student, assign exercises, and track your progress — a school simply does it across classes and teachers.'],
                ['q'=>'Does connecting with a teacher cost anything?','a'=>'Connecting on Harmoniva is free. Lesson pricing is set by each teacher or school — you\'ll find their rates and services listed on their profile.'],
            ]])
        </section>

    </div>
</div>

{{-- ============ BOTTOM CTA ============ --}}
<section class="bg-white py-20 px-4">
    <div class="max-w-2xl mx-auto text-center reveal">
        <span class="text-xs font-bold uppercase tracking-[0.2em] text-purple-600 mb-3 block">Are you an educator?</span>
        <h2 class="text-3xl sm:text-4xl font-extrabold text-gray-900 mb-4">Teach with Harmoniva</h2>
        <p class="text-gray-500 text-lg mb-8">Create a free teacher profile or bring your whole music school — assign exercises, track student progress, and grow your studio.</p>
        <div class="flex flex-wrap justify-center gap-3">
            <a href="{{ route('page.teachers-solution') }}" class="inline-flex items-center gap-2 px-7 py-3.5 rounded-xl text-white font-semibold shadow-lg shadow-purple-500/25 hover:-translate-y-0.5 transition-all" style="background:linear-gradient(135deg,#9333ea,#7c3aed);">For Teachers <i data-lucide="arrow-right" class="w-4 h-4"></i></a>
            <a href="{{ route('page.schools') }}" class="inline-flex items-center gap-2 px-7 py-3.5 rounded-xl font-semibold border-2 border-gray-200 text-gray-700 hover:bg-gray-50 transition-all">For Schools <i data-lucide="building-2" class="w-4 h-4"></i></a>
        </div>
    </div>
</section>

@endsection
