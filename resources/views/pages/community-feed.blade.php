@extends('layouts.standalone')

@section('title', 'Community & Music Teachers — Harmoniva')
@section('description', 'Share your progress on the Harmoniva community feed, follow fellow musicians, and connect with verified music teachers. Learn together and grow faster.')

@section('content')

{{-- ============ HERO ============ --}}
<section class="py-20 sm:py-28 relative overflow-hidden" style="background: linear-gradient(135deg, #ede9fe 0%, #FAF7F2 55%, #fce7f3 100%);">
    <div class="absolute -top-32 -right-32 w-[600px] h-[600px] rounded-full bg-primary-100/50 blur-3xl pointer-events-none"></div>
    <div class="absolute -bottom-20 -left-20 w-[350px] h-[350px] rounded-full bg-rose-100/50 blur-2xl pointer-events-none"></div>

    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 relative">
        <div class="text-center max-w-3xl mx-auto">
            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-primary-100 text-primary-700 text-sm font-semibold mb-6 hero-badge">
                <i data-lucide="users" class="w-4 h-4"></i>
                Community &amp; Teachers
            </div>

            <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold text-gray-900 leading-tight mb-6">
                Learn together,<br>
                <span class="font-serif italic font-normal" style="background:linear-gradient(135deg,#7c3aed,#db2777);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;">grow faster</span>
            </h1>

            <p class="text-gray-500 text-lg max-w-2xl mx-auto mb-10">
                Practice is more rewarding when it is shared. Post your breakthroughs on the community feed, follow musicians who inspire you, and connect with verified music teachers who help you reach the next level — all inside one platform.
            </p>

            <div class="flex flex-col sm:flex-row items-center justify-center gap-4 mb-8">
                <a href="{{ route('register') }}" class="inline-flex items-center gap-2 px-8 py-4 text-base font-bold text-white rounded-xl hover:opacity-90 transition-all shadow-xl hover:-translate-y-0.5" style="background:linear-gradient(135deg,#7c3aed,#6d28d9);">
                    <i data-lucide="user-plus" class="w-5 h-5"></i>
                    Join Free
                </a>
                <a href="{{ route('pricing.index') }}" class="inline-flex items-center gap-2 px-8 py-4 text-base font-bold rounded-xl transition-all shadow-xl hover:-translate-y-0.5 text-white" style="background:linear-gradient(135deg,#db2777,#be185d);">
                    <i data-lucide="crown" class="w-5 h-5"></i>
                    Go Premium
                </a>
            </div>

            <div class="flex flex-wrap items-center justify-center gap-6 text-sm text-gray-400">
                <span class="flex items-center gap-1.5"><i data-lucide="check" class="w-4 h-4 text-green-500"></i>Free to join — no card needed</span>
                <span class="flex items-center gap-1.5"><i data-lucide="check" class="w-4 h-4 text-green-500"></i>Verified teacher profiles</span>
                <span class="flex items-center gap-1.5"><i data-lucide="check" class="w-4 h-4 text-green-500"></i>Cancel anytime</span>
            </div>
        </div>
    </div>
</section>

{{-- ============ TWO INFO CARDS ============ --}}
<section class="py-20 bg-white">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-14 reveal">
            <span class="text-xs font-bold uppercase tracking-[0.2em] text-primary-600 mb-3 block">What you get</span>
            <h2 class="text-3xl sm:text-4xl font-extrabold text-gray-900 mb-4">
                Two ways to stay<br>
                <span class="font-serif italic font-normal" style="background:linear-gradient(135deg,#7c3aed,#db2777);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;">motivated and connected</span>
            </h2>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-stretch">
            {{-- Card 1: The Feed --}}
            <div class="flex flex-col p-8 bg-gradient-to-br from-primary-50 to-white rounded-2xl border border-primary-100 reveal h-full">
                <div class="w-14 h-14 rounded-2xl bg-primary-100 text-primary-600 flex items-center justify-center mb-5">
                    <i data-lucide="rss" class="w-7 h-7"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-3">The Community Feed</h3>
                <p class="text-gray-600 leading-relaxed">
                    The Harmoniva community feed turns solitary practice into shared momentum. Post your latest breakthroughs, share recordings, and celebrate streaks with fellow learners. Follow musicians whose journey inspires you, react to their milestones, and leave encouraging comments. Every completed exercise can become a moment worth sharing, building accountability and friendship. Discover trending posts, join conversations, and never practice alone again.
                </p>
            </div>

            {{-- Card 2: Teachers --}}
            <div class="flex flex-col p-8 bg-gradient-to-br from-rose-50 to-white rounded-2xl border border-rose-100 reveal h-full" style="transition-delay:.08s">
                <div class="w-14 h-14 rounded-2xl bg-rose-100 text-rose-600 flex items-center justify-center mb-5">
                    <i data-lucide="graduation-cap" class="w-7 h-7"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-3">Music Teachers</h3>
                <p class="text-gray-600 leading-relaxed">
                    Beyond the feed, Harmoniva connects you with real music teachers. Browse verified instructor profiles, compare specialties and reviews, then book lessons or message a teacher directly. For educators, the platform is a growth engine: reach new students, showcase your expertise, and manage bookings effortlessly. Premium membership unlocks direct messaging, priority visibility, and richer profiles for teachers and learners alike.
                </p>
            </div>
        </div>
    </div>
</section>

{{-- ============ DETAILED EXPLANATIONS ============ --}}
<section class="py-20" style="background:#FAF7F2;">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-16">

        {{-- Feed detailed --}}
        <div class="reveal">
            <div class="flex items-center gap-3 mb-5">
                <div class="w-11 h-11 rounded-xl bg-primary-100 text-primary-600 flex items-center justify-center shrink-0">
                    <i data-lucide="rss" class="w-5 h-5"></i>
                </div>
                <h2 class="text-2xl sm:text-3xl font-extrabold text-gray-900">Inside the community feed</h2>
            </div>
            <div class="space-y-4 text-gray-600 text-[17px] leading-relaxed">
                <p>
                    The community feed is the social heartbeat of Harmoniva, designed to keep motivation high long after the novelty of a new app fades. Learning an instrument or training your ear can feel isolating, and progress is often invisible from one day to the next. The feed solves this by making your growth visible and shared. Every practice session, completed learning path, unlocked badge, or personal best can be posted to your followers, transforming small daily wins into public milestones that others cheer on.
                </p>
                <p>
                    You can follow other musicians, discover people at your level or slightly ahead, and build a personal network of accountability partners. Reactions, comments, and encouragement create a feedback loop that keeps you returning day after day. When you see a peer conquer a tricky interval or finish a difficult dictation, it reframes the challenge as achievable rather than intimidating. The feed also surfaces trending posts and popular achievements, so inspiration is always within reach. Whether you are a curious beginner or a seasoned player, you gain a supportive audience that celebrates consistency over perfection — turning practice from a lonely chore into a shared, rewarding habit you actually look forward to.
                </p>
            </div>
        </div>

        {{-- Teachers detailed --}}
        <div class="reveal">
            <div class="flex items-center gap-3 mb-5">
                <div class="w-11 h-11 rounded-xl bg-rose-100 text-rose-600 flex items-center justify-center shrink-0">
                    <i data-lucide="graduation-cap" class="w-5 h-5"></i>
                </div>
                <h2 class="text-2xl sm:text-3xl font-extrabold text-gray-900">Meeting music teachers</h2>
            </div>
            <div class="space-y-4 text-gray-600 text-[17px] leading-relaxed">
                <p>
                    Harmoniva is also where dedicated learners meet dedicated teachers. Through our public teacher directory you can browse verified instructor profiles, read authentic student reviews, compare instruments and specialties, and find someone whose teaching style genuinely fits your goals. Once you find the right match, you can request lessons, book available time slots, and message your teacher directly inside the platform — no scattered emails or lost messages. Explore the full roster on the <a href="{{ route('teachers.directory') }}" class="text-primary-600 font-semibold underline decoration-primary-300 underline-offset-2 hover:text-primary-700">Find a Teacher</a> page, or visit a featured educator like <a href="{{ url('/teachers/tuba-gunvar') }}" class="text-rose-600 font-semibold underline decoration-rose-300 underline-offset-2 hover:text-rose-700">Tuba Günvar</a> to see exactly what a complete Harmoniva profile looks like.
                </p>
                <p>
                    For music teachers, the platform is a genuine growth engine. A polished public profile puts your expertise in front of motivated students actively searching for guidance, while built-in booking, messaging, and progress-tracking tools replace the administrative clutter that eats into teaching time. You can assign targeted exercises, monitor each student's accuracy, and demonstrate measurable results that build trust and retention. Membership deepens every advantage: Premium unlocks direct messaging, priority placement in search, richer media galleries, and advanced analytics for both learners and educators. Whether you want to find a mentor or grow your own studio, Harmoniva gives both sides of the lesson the tools to succeed together.
                </p>
            </div>

            <div class="flex flex-col sm:flex-row flex-wrap gap-4 mt-8">
                <a href="{{ route('teachers.directory') }}" class="inline-flex items-center gap-2 px-6 py-3.5 text-sm font-bold text-white rounded-xl hover:opacity-90 transition-all shadow-lg hover:-translate-y-0.5" style="background:linear-gradient(135deg,#7c3aed,#6d28d9);">
                    <i data-lucide="search" class="w-4 h-4"></i>
                    Find a Teacher
                </a>
                <a href="{{ url('/teachers/tuba-gunvar') }}" class="inline-flex items-center gap-2 px-6 py-3.5 text-sm font-bold text-gray-700 bg-white border border-gray-200 rounded-xl hover:border-rose-400 hover:text-rose-600 transition-all shadow-sm">
                    <i data-lucide="user" class="w-4 h-4"></i>
                    View Tuba Günvar's profile
                    <i data-lucide="arrow-right" class="w-4 h-4"></i>
                </a>
            </div>
        </div>
    </div>
</section>

{{-- ============ CTA ============ --}}
<section class="py-24 relative overflow-hidden" style="background: linear-gradient(135deg, #ede9fe 0%, #FAF7F2 50%, #fce7f3 100%);">
    <div class="absolute -top-20 -right-20 w-[400px] h-[400px] rounded-full bg-primary-100/50 blur-3xl pointer-events-none"></div>
    <div class="max-w-3xl mx-auto px-4 sm:px-6 text-center relative reveal">
        <div class="w-16 h-16 mx-auto rounded-2xl flex items-center justify-center mb-8 shadow-xl hero-badge" style="background:linear-gradient(135deg,#7c3aed,#db2777);">
            <i data-lucide="sparkles" class="w-8 h-8 text-white"></i>
        </div>
        <h2 class="text-3xl sm:text-4xl font-extrabold text-gray-900 mb-5">
            Ready to join<br>
            <span class="font-serif italic font-normal" style="background:linear-gradient(135deg,#7c3aed,#db2777);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;">the Harmoniva community?</span>
        </h2>
        <p class="text-gray-500 text-lg mb-10 max-w-xl mx-auto">
            Start free today, share your progress, and connect with teachers and musicians who help you grow.
        </p>
        <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
            <a href="{{ route('register') }}" class="inline-flex items-center gap-2 px-8 py-4 text-base font-bold text-white rounded-xl hover:opacity-90 transition-all shadow-xl hover:-translate-y-0.5" style="background:linear-gradient(135deg,#7c3aed,#6d28d9);">
                <i data-lucide="user-plus" class="w-5 h-5"></i>
                Join Free
            </a>
            <a href="{{ route('pricing.index') }}" class="inline-flex items-center gap-2 px-6 py-4 text-base font-medium text-gray-600 hover:text-gray-900 transition-colors">
                Compare plans <i data-lucide="arrow-right" class="w-4 h-4"></i>
            </a>
        </div>
    </div>
</section>

@endsection
