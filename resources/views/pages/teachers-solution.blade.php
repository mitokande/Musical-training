@extends('layouts.standalone')

@section('title', 'Harmoniva for Teachers')
@section('description', 'Manage your students, assign exercises, and track progress with AI-powered ear training. The most effective tool for music teachers.')

@section('content')

{{-- Hero --}}
<section class="py-20 sm:py-28 relative overflow-hidden" style="background: linear-gradient(135deg, #fff7ed 0%, #FAF7F2 60%, #fef3c7 100%);">
    <div class="absolute -top-32 -right-32 w-[600px] h-[600px] rounded-full bg-orange-100/50 blur-3xl pointer-events-none"></div>
    <div class="absolute -bottom-20 -left-20 w-[350px] h-[350px] rounded-full bg-amber-50/60 blur-2xl pointer-events-none"></div>

    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 relative">
        <div class="text-center max-w-3xl mx-auto">
            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-orange-100 text-orange-700 text-sm font-semibold mb-6 hero-badge">
                <i data-lucide="users" class="w-4 h-4"></i>
                For Teachers
            </div>

            <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold text-gray-900 leading-tight mb-6">
                Manage students, assign exercises,<br>
                <span class="font-serif italic font-normal" style="background:linear-gradient(135deg,#ea580c,#f97316);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;">track every breakthrough</span>
            </h1>

            <p class="text-gray-500 text-lg max-w-2xl mx-auto mb-10">
                Harmoniva gives music teachers a complete toolkit to deliver ear training at scale. Assign targeted exercises, monitor individual progress, and give AI-powered feedback — all without extra prep time.
            </p>

            <div class="flex flex-col sm:flex-row items-center justify-center gap-4 mb-10">
                @auth
                <a href="{{ url('/dashboard') }}" class="inline-flex items-center gap-2 px-8 py-4 text-base font-bold text-white rounded-xl hover:opacity-90 transition-all shadow-xl hover:-translate-y-0.5" style="background:linear-gradient(135deg,#ea580c,#f97316);">
                    <i data-lucide="rocket" class="w-5 h-5"></i>
                    Go to Dashboard
                </a>
                @else
                <a href="{{ route('register') }}" class="inline-flex items-center gap-2 px-8 py-4 text-base font-bold text-white rounded-xl hover:opacity-90 transition-all shadow-xl hover:-translate-y-0.5" style="background:linear-gradient(135deg,#ea580c,#f97316);">
                    <i data-lucide="rocket" class="w-5 h-5"></i>
                    Start Free
                </a>
                @endauth
                <a href="/pricing/teachers-and-schools" class="inline-flex items-center gap-2 px-6 py-4 text-base font-semibold text-gray-700 bg-white border border-gray-200 rounded-xl hover:border-accent-400 hover:text-accent-600 transition-all shadow-sm">
                    View Teachers Plan <i data-lucide="arrow-right" class="w-4 h-4"></i>
                </a>
            </div>

            <div class="flex flex-wrap items-center justify-center gap-6 text-sm text-gray-400">
                <span class="flex items-center gap-1.5"><i data-lucide="check" class="w-4 h-4 text-green-500"></i>Free trial — no card needed</span>
                <span class="flex items-center gap-1.5"><i data-lucide="check" class="w-4 h-4 text-green-500"></i>Setup in under 5 minutes</span>
                <span class="flex items-center gap-1.5"><i data-lucide="check" class="w-4 h-4 text-green-500"></i>Cancel anytime</span>
            </div>
        </div>
    </div>
</section>

{{-- Feature Highlights --}}
<section class="py-20 bg-white">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-14 reveal">
            <span class="text-xs font-bold uppercase tracking-[0.2em] text-accent-600 mb-3 block">Built for Educators</span>
            <h2 class="text-3xl sm:text-4xl font-extrabold text-gray-900 mb-4">
                Everything a music teacher needs<br>
                <span class="font-serif italic font-normal" style="background:linear-gradient(135deg,#ea580c,#f97316);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;">in one place</span>
            </h2>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            @php $features = [
                ['icon' => 'send', 'color' => 'bg-orange-100 text-orange-600', 'title' => 'Assign exercises directly to students', 'desc' => 'Push specific exercises or complete learning paths to individual students or entire class groups from your teacher dashboard. Set due dates, difficulty levels, and required completion counts.'],
                ['icon' => 'bar-chart-3', 'color' => 'bg-blue-100 text-blue-600', 'title' => 'Track every student\'s progress', 'desc' => 'View detailed accuracy reports, session history, and skill-by-skill breakdowns for each student. See at a glance who\'s thriving and who needs extra attention.'],
                ['icon' => 'users', 'color' => 'bg-purple-100 text-purple-600', 'title' => 'Organize classes and student groups', 'desc' => 'Create class groups organized by level, instrument, or semester. Assign exercises to an entire group at once and compare progress across your roster.'],
                ['icon' => 'sparkles', 'color' => 'bg-cyan-100 text-cyan-600', 'title' => 'AI-powered learning paths', 'desc' => 'Let Harmoniva\'s AI generate personalized learning plans for each student based on their assessment results — freeing up your planning time for what only a teacher can do.'],
                ['icon' => 'file-text', 'color' => 'bg-green-100 text-green-600', 'title' => 'Export progress reports', 'desc' => 'Download professional PDF progress reports to share with students, parents, or department heads. Perfect for semester reviews, parent-teacher meetings, and audition portfolios.'],
                ['icon' => 'sliders-horizontal', 'color' => 'bg-amber-100 text-amber-600', 'title' => 'Custom exercise configuration', 'desc' => 'Fine-tune every exercise parameter — allowed intervals, note ranges, BPM, voicings, distractor counts — to target exactly what your curriculum requires at each stage.'],
            ]; @endphp
            @foreach ($features as $fi => $feat)
            <div class="flex items-start gap-5 p-6 bg-gray-50 rounded-2xl border border-gray-100 reveal" style="transition-delay:{{ $fi * 0.07 }}s">
                <div class="w-12 h-12 rounded-xl {{ $feat['color'] }} flex items-center justify-center shrink-0">
                    <i data-lucide="{{ $feat['icon'] }}" class="w-6 h-6"></i>
                </div>
                <div>
                    <h3 class="font-bold text-gray-900 mb-2">{{ $feat['title'] }}</h3>
                    <p class="text-gray-500 text-sm leading-relaxed">{{ $feat['desc'] }}</p>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- Comparison: With vs Without --}}
<section class="py-20" style="background:#FAF7F2;">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12 reveal">
            <span class="text-xs font-bold uppercase tracking-[0.2em] text-accent-600 mb-3 block">The Difference</span>
            <h2 class="text-3xl font-extrabold text-gray-900 mb-3">
                Teaching ear training,<br>
                <span class="font-serif italic font-normal" style="background:linear-gradient(135deg,#ea580c,#f97316);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;">then and now</span>
            </h2>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 reveal">
            {{-- Without --}}
            <div class="bg-white rounded-2xl border border-gray-200 p-7">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-10 h-10 rounded-xl bg-gray-100 flex items-center justify-center">
                        <i data-lucide="x-circle" class="w-5 h-5 text-gray-400"></i>
                    </div>
                    <h3 class="font-extrabold text-gray-400">Without Harmoniva</h3>
                </div>
                <ul class="space-y-4">
                    @php $withoutItems = [
                        'Manually creating and photocopying interval worksheets',
                        'No visibility into what students practice at home',
                        'One-size-fits-all exercises that bore advanced students and frustrate beginners',
                        'Spending 30+ minutes per student grading ear training assignments',
                        'Students forgetting to practice between lessons',
                        'No data to show parents or administrators about student progress',
                    ]; @endphp
                    @foreach ($withoutItems as $item)
                    <li class="flex items-start gap-3 text-sm text-gray-400">
                        <i data-lucide="minus" class="w-4 h-4 text-gray-300 shrink-0 mt-0.5"></i>
                        <span>{{ $item }}</span>
                    </li>
                    @endforeach
                </ul>
            </div>

            {{-- With --}}
            <div class="rounded-2xl p-7 text-white relative overflow-hidden" style="background:linear-gradient(135deg,#ea580c,#f97316);">
                <div class="absolute -top-10 -right-10 w-40 h-40 rounded-full bg-white/10 blur-2xl pointer-events-none"></div>
                <div class="flex items-center gap-3 mb-6 relative">
                    <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center">
                        <i data-lucide="check-circle" class="w-5 h-5 text-white"></i>
                    </div>
                    <h3 class="font-extrabold text-white">With Harmoniva</h3>
                </div>
                <ul class="space-y-4 relative">
                    @php $withItems = [
                        'Assign targeted exercises to each student in two clicks',
                        'Real-time progress dashboard — see who practiced and how accurately',
                        'AI adapts difficulty automatically for each student\'s level',
                        'Automatic grading and progress tracking, zero extra work',
                        'Streak reminders keep students practicing daily between lessons',
                        'Professional PDF reports ready to export for any stakeholder',
                    ]; @endphp
                    @foreach ($withItems as $item)
                    <li class="flex items-start gap-3 text-sm text-white/90">
                        <i data-lucide="check" class="w-4 h-4 text-white shrink-0 mt-0.5"></i>
                        <span>{{ $item }}</span>
                    </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
</section>

{{-- Plan Highlight --}}
<section class="py-20 bg-white">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <div class="text-center mb-10 reveal">
            <span class="text-xs font-bold uppercase tracking-[0.2em] text-accent-600 mb-3 block">Educators Plan</span>
            <h2 class="text-3xl font-extrabold text-gray-900 mb-3">
                One plan for all your students
            </h2>
            <p class="text-gray-500">Unlimited students, one flat monthly fee. No per-seat pricing.</p>
        </div>

        <div class="bg-white rounded-3xl border border-orange-200 shadow-xl p-8 max-w-sm mx-auto reveal" style="transition-delay:0.1s">
            <span class="inline-block px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider text-white mb-6" style="background:linear-gradient(135deg,#ea580c,#f97316);">Teachers &amp; Schools</span>

            <div class="flex items-end justify-center gap-1 mb-1">
                <span class="text-5xl font-extrabold text-gray-900">$16.90</span>
                <span class="text-gray-400 text-base mb-2">/month</span>
            </div>
            <p class="text-gray-400 text-sm mb-2">or <strong class="text-gray-700">$8.25/mo</strong> billed annually</p>
            <span class="inline-block px-3 py-1 rounded-full text-xs font-bold bg-orange-100 text-orange-700 mb-6">Save over $100/year</span>

            <ul class="space-y-3 mb-8 text-left">
                @php $planFeats = [
                    'Unlimited students',
                    'Student roster &amp; class management',
                    'Exercise assignment &amp; tracking',
                    'Class-wide &amp; per-student analytics',
                    'AI Learning Path generator',
                    'AI Music Assistant for all students',
                    'PDF progress report export',
                    'Priority customer support',
                ]; @endphp
                @foreach ($planFeats as $pf)
                <li class="flex items-center gap-3 text-sm text-gray-700">
                    <i data-lucide="check" class="w-4 h-4 text-green-500 shrink-0"></i>
                    {!! $pf !!}
                </li>
                @endforeach
            </ul>

            <div class="space-y-3">
                @auth
                <a href="{{ url('/dashboard') }}" class="block w-full py-3.5 text-center text-sm font-bold text-white rounded-xl hover:opacity-90 transition-all" style="background:linear-gradient(135deg,#ea580c,#f97316);">
                    Go to Dashboard
                </a>
                @else
                <a href="{{ route('register') }}" class="block w-full py-3.5 text-center text-sm font-bold text-white rounded-xl hover:opacity-90 transition-all" style="background:linear-gradient(135deg,#ea580c,#f97316);">
                    Start Free Trial
                </a>
                @endauth
                <a href="/pricing/teachers-and-schools" class="block w-full py-3.5 text-center text-sm font-semibold text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-xl transition-colors">
                    View Full Plan Details →
                </a>
            </div>
        </div>
    </div>
</section>

{{-- CTA --}}
<section class="py-24 relative overflow-hidden" style="background: linear-gradient(135deg, #fff7ed 0%, #FAF7F2 50%, #fef3c7 100%);">
    <div class="absolute -top-20 -right-20 w-[400px] h-[400px] rounded-full bg-orange-100/50 blur-3xl pointer-events-none"></div>
    <div class="max-w-3xl mx-auto px-4 sm:px-6 text-center relative reveal">
        <div class="w-16 h-16 mx-auto rounded-2xl flex items-center justify-center mb-8 shadow-xl hero-badge" style="background:linear-gradient(135deg,#ea580c,#f97316);">
            <i data-lucide="graduation-cap" class="w-8 h-8 text-white"></i>
        </div>
        <h2 class="text-3xl sm:text-4xl font-extrabold text-gray-900 mb-5">
            Ready to transform<br>
            <span class="font-serif italic font-normal" style="background:linear-gradient(135deg,#ea580c,#f97316);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;">your music classroom?</span>
        </h2>
        <p class="text-gray-500 text-lg mb-10 max-w-xl mx-auto">
            Join hundreds of music teachers using Harmoniva to deliver better ear training in less time.
        </p>
        <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
            @auth
            <a href="{{ url('/dashboard') }}" class="inline-flex items-center gap-2 px-8 py-4 text-base font-bold text-white rounded-xl hover:opacity-90 transition-all shadow-xl hover:-translate-y-0.5" style="background:linear-gradient(135deg,#ea580c,#f97316);">
                <i data-lucide="rocket" class="w-5 h-5"></i>
                Go to Dashboard
            </a>
            @else
            <a href="{{ route('register') }}" class="inline-flex items-center gap-2 px-8 py-4 text-base font-bold text-white rounded-xl hover:opacity-90 transition-all shadow-xl hover:-translate-y-0.5" style="background:linear-gradient(135deg,#ea580c,#f97316);">
                <i data-lucide="rocket" class="w-5 h-5"></i>
                Start Free
            </a>
            @endauth
            <a href="/pricing/teachers-and-schools" class="inline-flex items-center gap-2 px-6 py-4 text-base font-medium text-gray-600 hover:text-gray-900 transition-colors">
                View Teachers Plan <i data-lucide="arrow-right" class="w-4 h-4"></i>
            </a>
        </div>
    </div>
</section>

@endsection
