@extends('layouts.standalone')

@section('title', 'Request a Demo')
@section('description', 'See Harmoniva in action. Request a personalized demo for your school, conservatory, or music institution and discover how AI-powered ear training scales.')

@section('content')

{{-- Hero --}}
<section class="py-20 sm:py-24 relative overflow-hidden" style="background: linear-gradient(135deg, #fff7ed 0%, #FAF7F2 60%, #fef3c7 100%);">
    <div class="absolute -top-32 -right-32 w-[500px] h-[500px] rounded-full bg-orange-100/50 blur-3xl pointer-events-none"></div>
    <div class="absolute -bottom-20 -left-20 w-[300px] h-[300px] rounded-full bg-amber-50/60 blur-2xl pointer-events-none"></div>

    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 text-center relative">
        <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-orange-100 text-orange-700 text-sm font-semibold mb-6 hero-badge">
            <i data-lucide="calendar" class="w-4 h-4"></i>
            For Schools &amp; Institutions
        </div>

        <h1 class="text-4xl sm:text-5xl font-extrabold text-gray-900 leading-tight mb-5">
            See Harmoniva<br>
            <span class="font-serif italic font-normal" style="background:linear-gradient(135deg,#ea580c,#f97316);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;">in action</span>
        </h1>

        <p class="text-gray-500 text-lg max-w-xl mx-auto">
            Request a personalized 30-minute demo tailored to your institution. We'll walk through setup, classroom management, analytics, and everything that makes Harmoniva the right fit for your program.
        </p>
    </div>
</section>

{{-- Form + What to Expect --}}
<section class="py-20 bg-white">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-5 gap-10 items-start">

            {{-- Form (3 columns) --}}
            <div class="lg:col-span-3 reveal">
                <div class="bg-white rounded-3xl border border-gray-200 shadow-sm p-8">
                    <h2 class="text-2xl font-extrabold text-gray-900 mb-2">Request your demo</h2>
                    <p class="text-gray-400 text-sm mb-8">Fill in your details and we'll reach out within one business day to schedule your session.</p>

                    <form action="#" method="POST" class="space-y-5">
                        @csrf

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                            <div>
                                <label for="name" class="block text-sm font-semibold text-gray-700 mb-1.5">Full Name <span class="text-red-400">*</span></label>
                                <input type="text" id="name" name="name" required placeholder="Jane Smith"
                                    class="w-full px-4 py-3 rounded-xl border border-gray-200 bg-gray-50 text-gray-900 text-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-orange-400 focus:border-transparent transition">
                            </div>
                            <div>
                                <label for="email" class="block text-sm font-semibold text-gray-700 mb-1.5">Work Email <span class="text-red-400">*</span></label>
                                <input type="email" id="email" name="email" required placeholder="jane@conservatory.edu"
                                    class="w-full px-4 py-3 rounded-xl border border-gray-200 bg-gray-50 text-gray-900 text-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-orange-400 focus:border-transparent transition">
                            </div>
                        </div>

                        <div>
                            <label for="organization" class="block text-sm font-semibold text-gray-700 mb-1.5">Organization / Institution <span class="text-red-400">*</span></label>
                            <input type="text" id="organization" name="organization" required placeholder="e.g. Westside School of Music"
                                class="w-full px-4 py-3 rounded-xl border border-gray-200 bg-gray-50 text-gray-900 text-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-orange-400 focus:border-transparent transition">
                        </div>

                        <div>
                            <label for="role" class="block text-sm font-semibold text-gray-700 mb-1.5">Your Role <span class="text-red-400">*</span></label>
                            <div class="relative">
                                <select id="role" name="role" required
                                    class="w-full px-4 py-3 rounded-xl border border-gray-200 bg-gray-50 text-gray-900 text-sm appearance-none focus:outline-none focus:ring-2 focus:ring-orange-400 focus:border-transparent transition">
                                    <option value="" disabled selected>Select your role…</option>
                                    <option value="teacher">Teacher</option>
                                    <option value="school_admin">School Admin</option>
                                    <option value="music_director">Music Director</option>
                                    <option value="department_head">Department Head</option>
                                    <option value="other">Other</option>
                                </select>
                                <i data-lucide="chevron-down" class="w-4 h-4 text-gray-400 absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none"></i>
                            </div>
                        </div>

                        <div>
                            <label for="students" class="block text-sm font-semibold text-gray-700 mb-1.5">Estimated Number of Students</label>
                            <div class="relative">
                                <select id="students" name="students"
                                    class="w-full px-4 py-3 rounded-xl border border-gray-200 bg-gray-50 text-gray-900 text-sm appearance-none focus:outline-none focus:ring-2 focus:ring-orange-400 focus:border-transparent transition">
                                    <option value="" disabled selected>Select range…</option>
                                    <option value="1-25">1–25 students</option>
                                    <option value="26-100">26–100 students</option>
                                    <option value="101-500">101–500 students</option>
                                    <option value="500+">500+ students</option>
                                </select>
                                <i data-lucide="chevron-down" class="w-4 h-4 text-gray-400 absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none"></i>
                            </div>
                        </div>

                        <div>
                            <label for="message" class="block text-sm font-semibold text-gray-700 mb-1.5">Tell us about your needs</label>
                            <textarea id="message" name="message" rows="4" placeholder="What does your ear training curriculum look like today? What challenges are you trying to solve? Any specific features you'd like to see in the demo?"
                                class="w-full px-4 py-3 rounded-xl border border-gray-200 bg-gray-50 text-gray-900 text-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-orange-400 focus:border-transparent transition resize-none"></textarea>
                        </div>

                        <button type="submit" class="w-full py-4 text-base font-bold text-white rounded-xl hover:opacity-90 transition-all shadow-lg hover:-translate-y-0.5" style="background:linear-gradient(135deg,#ea580c,#f97316);">
                            <span class="inline-flex items-center justify-center gap-2">
                                <i data-lucide="send" class="w-5 h-5"></i>
                                Request My Demo
                            </span>
                        </button>

                        <p class="text-xs text-gray-400 text-center">
                            By submitting, you agree to our
                            <a href="/privacy" class="underline hover:text-gray-600 transition-colors">Privacy Policy</a>.
                            We'll never share your information with third parties.
                        </p>
                    </form>
                </div>
            </div>

            {{-- What to Expect (2 columns) --}}
            <div class="lg:col-span-2 space-y-6">

                <div class="reveal" style="transition-delay:0.1s">
                    <h3 class="text-lg font-extrabold text-gray-900 mb-5">What happens next</h3>
                    <div class="space-y-5">
                        @php $steps = [
                            ['num' => '1', 'icon' => 'mail', 'color' => 'bg-orange-100 text-orange-600', 'title' => 'We\'ll reach out within 24h', 'desc' => 'A member of our team will email you to confirm your preferred date and time, and ask a few questions to tailor the demo to your institution.'],
                            ['num' => '2', 'icon' => 'video', 'color' => 'bg-purple-100 text-purple-600', 'title' => '30-minute personalized demo', 'desc' => 'We\'ll walk you through the teacher dashboard, show you how to set up a class, assign exercises, and read the analytics — live.'],
                            ['num' => '3', 'icon' => 'rocket', 'color' => 'bg-green-100 text-green-600', 'title' => 'Get set up in minutes', 'desc' => 'After the demo, our team will help you import your roster, configure your first classes, and launch Harmoniva with your students — same day if you\'re ready.'],
                        ]; @endphp
                        @foreach ($steps as $step)
                        <div class="flex items-start gap-4">
                            <div class="w-10 h-10 rounded-xl {{ $step['color'] }} flex items-center justify-center shrink-0">
                                <i data-lucide="{{ $step['icon'] }}" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <h4 class="font-bold text-gray-900 text-sm mb-1">{{ $step['title'] }}</h4>
                                <p class="text-gray-400 text-xs leading-relaxed">{{ $step['desc'] }}</p>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                <div class="bg-gray-50 rounded-2xl border border-gray-100 p-6 reveal" style="transition-delay:0.2s">
                    <h4 class="font-extrabold text-gray-900 text-sm mb-4">You're in good hands</h4>
                    <div class="space-y-3">
                        @php $signals = [
                            ['icon' => 'shield-check', 'text' => 'GDPR compliant — your data is always secure'],
                            ['icon' => 'users', 'text' => 'Trusted by music educators worldwide'],
                            ['icon' => 'headphones', 'text' => 'Dedicated support during and after onboarding'],
                            ['icon' => 'credit-card', 'text' => 'No commitment required after the demo'],
                        ]; @endphp
                        @foreach ($signals as $sig)
                        <div class="flex items-center gap-3 text-sm text-gray-600">
                            <i data-lucide="{{ $sig['icon'] }}" class="w-4 h-4 text-orange-500 shrink-0"></i>
                            {{ $sig['text'] }}
                        </div>
                        @endforeach
                    </div>
                </div>

                <div class="bg-gray-900 rounded-2xl p-6 reveal" style="transition-delay:0.3s">
                    <i data-lucide="quote" class="w-6 h-6 text-orange-400 mb-3"></i>
                    <p class="text-gray-300 text-sm italic leading-relaxed mb-4">
                        "The onboarding demo gave us total confidence. We had 120 students enrolled and running their first exercises within 48 hours of our call."
                    </p>
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-full bg-gradient-to-br from-orange-500 to-amber-500 flex items-center justify-center text-white text-xs font-bold shrink-0">RL</div>
                        <div>
                            <div class="text-white text-xs font-bold">Rachel Liu</div>
                            <div class="text-gray-400 text-xs">Director, Pacific Music Academy</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- Prefer to self-serve? --}}
<section class="py-16" style="background:#FAF7F2;">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center reveal">
        <h2 class="text-2xl font-extrabold text-gray-900 mb-3">Prefer to self-serve?</h2>
        <p class="text-gray-500 text-sm mb-6">Create a free account and explore Harmoniva at your own pace. The full teacher dashboard is accessible from day one.</p>
        <div class="flex flex-wrap items-center justify-center gap-4">
            @auth
            <a href="{{ url('/dashboard') }}" class="inline-flex items-center gap-2 px-6 py-3 text-white font-bold rounded-xl hover:opacity-90 transition-all shadow-lg" style="background:linear-gradient(135deg,#9333ea,#7c3aed);">
                Go to Dashboard
            </a>
            @else
            <a href="{{ route('register') }}" class="inline-flex items-center gap-2 px-6 py-3 text-white font-bold rounded-xl hover:opacity-90 transition-all shadow-lg" style="background:linear-gradient(135deg,#9333ea,#7c3aed);">
                Create Free Account
            </a>
            @endauth
            <a href="/pricing/teachers-and-schools" class="inline-flex items-center gap-2 px-6 py-3 bg-white border border-gray-200 rounded-xl text-gray-700 font-semibold hover:border-primary-400 hover:text-primary-600 transition-all shadow-sm">
                View Teachers &amp; Schools Plan →
            </a>
        </div>
    </div>
</section>

@endsection
