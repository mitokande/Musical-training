@extends('layouts.standalone')

@section('title', 'Harmoniva for Music Schools')
@section('description', 'Power ear training across your entire institution with Harmoniva. Multi-teacher management, department analytics, bulk enrollment, and GDPR compliance.')

@section('content')

{{-- Hero --}}
<section class="py-20 sm:py-28 relative overflow-hidden" style="background: linear-gradient(135deg, #fff7ed 0%, #FAF7F2 60%, #fef3c7 100%);">
    <div class="absolute -top-32 -right-32 w-[600px] h-[600px] rounded-full bg-orange-100/50 blur-3xl pointer-events-none"></div>
    <div class="absolute -bottom-24 -left-24 w-[400px] h-[400px] rounded-full bg-amber-50/60 blur-3xl pointer-events-none"></div>

    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 relative text-center">
        <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-orange-100 text-orange-700 text-sm font-semibold mb-6 hero-badge">
            <i data-lucide="building-2" class="w-4 h-4"></i>
            For Music Schools
        </div>

        <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold text-gray-900 leading-tight mb-6">
            Power ear training<br>
            <span class="font-serif italic font-normal" style="background:linear-gradient(135deg,#ea580c,#f97316);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;">across your entire institution</span>
        </h1>

        <p class="text-gray-500 text-lg max-w-2xl mx-auto mb-10">
            From small music academies to full conservatories, Harmoniva gives institutions a centralized platform to deliver AI-powered ear training at every level — with the analytics and tools to prove it works.
        </p>

        <div class="flex flex-col sm:flex-row items-center justify-center gap-4 mb-10">
            <a href="/request-demo" class="inline-flex items-center gap-2 px-8 py-4 text-base font-bold text-white rounded-xl hover:opacity-90 transition-all shadow-xl hover:-translate-y-0.5" style="background:linear-gradient(135deg,#ea580c,#f97316);">
                <i data-lucide="calendar" class="w-5 h-5"></i>
                Request a Demo →
            </a>
            <a href="/pricing/teachers-and-schools" class="inline-flex items-center gap-2 px-6 py-4 text-base font-semibold text-gray-700 bg-white border border-gray-200 rounded-xl hover:border-accent-400 hover:text-accent-600 transition-all shadow-sm">
                View School Plans <i data-lucide="arrow-right" class="w-4 h-4"></i>
            </a>
        </div>

        <div class="flex flex-wrap items-center justify-center gap-6 text-sm text-gray-400">
            <span class="flex items-center gap-1.5"><i data-lucide="check" class="w-4 h-4 text-green-500"></i>GDPR compliant</span>
            <span class="flex items-center gap-1.5"><i data-lucide="check" class="w-4 h-4 text-green-500"></i>Unlimited students</span>
            <span class="flex items-center gap-1.5"><i data-lucide="check" class="w-4 h-4 text-green-500"></i>Custom branding available</span>
        </div>
    </div>
</section>

{{-- Features Grid --}}
<section class="py-20 bg-white">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-14 reveal">
            <span class="text-xs font-bold uppercase tracking-[0.2em] text-accent-600 mb-3 block">Institution-Grade Features</span>
            <h2 class="text-3xl sm:text-4xl font-extrabold text-gray-900 mb-4">
                Built to scale with<br>
                <span class="font-serif italic font-normal" style="background:linear-gradient(135deg,#ea580c,#f97316);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;">your institution</span>
            </h2>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @php $features = [
                ['icon' => 'users-2', 'color' => 'bg-orange-100 text-orange-600', 'title' => 'Multi-Teacher Management', 'desc' => 'Add unlimited teacher accounts under one school subscription. Assign roles, set permissions, and manage your entire faculty from a single admin dashboard.'],
                ['icon' => 'bar-chart-3', 'color' => 'bg-blue-100 text-blue-600', 'title' => 'Department-Wide Analytics', 'desc' => 'View aggregated performance data across your whole institution. Filter by class, teacher, skill type, or date range to identify trends and gaps at scale.'],
                ['icon' => 'list-checks', 'color' => 'bg-purple-100 text-purple-600', 'title' => 'Student Roster Management', 'desc' => 'Import students in bulk via CSV, organize them into classes and departments, and manage enrollment changes without any manual data entry.'],
                ['icon' => 'shield-check', 'color' => 'bg-green-100 text-green-600', 'title' => 'GDPR & Privacy Compliance', 'desc' => 'Full GDPR compliance with data processing agreements, encrypted student data, parental consent workflows for minors, and data deletion on request.'],
                ['icon' => 'palette', 'color' => 'bg-pink-100 text-pink-600', 'title' => 'Custom Branding', 'desc' => 'Add your school logo, colors, and name to the student-facing experience. Create a seamless extension of your institution\'s brand identity.'],
                ['icon' => 'upload', 'color' => 'bg-amber-100 text-amber-600', 'title' => 'Bulk Enrollment', 'desc' => 'Onboard an entire class, semester cohort, or the whole school at once. Upload a CSV and students receive setup instructions automatically.'],
                ['icon' => 'credit-card', 'color' => 'bg-cyan-100 text-cyan-600', 'title' => 'Centralized Billing', 'desc' => 'One invoice for your whole institution. Request VAT invoices, switch billing cycles, or add seats without billing headaches or per-user fees.'],
                ['icon' => 'headphones', 'color' => 'bg-violet-100 text-violet-600', 'title' => 'Dedicated Onboarding', 'desc' => 'Every school gets a dedicated onboarding session. Our team trains your faculty, helps import your roster, and ensures you launch with confidence.'],
                ['icon' => 'file-text', 'color' => 'bg-red-100 text-red-600', 'title' => 'Institutional Reporting', 'desc' => 'Generate semester-end reports, track cohort progress over years, and export data for accreditation documentation or administrative reviews.'],
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

{{-- Use Cases --}}
<section class="py-20" style="background:#FAF7F2;">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-14 reveal">
            <span class="text-xs font-bold uppercase tracking-[0.2em] text-accent-600 mb-3 block">Use Cases</span>
            <h2 class="text-3xl font-extrabold text-gray-900 mb-3">Harmoniva works for every type of institution</h2>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            @php $useCases = [
                [
                    'icon' => 'award',
                    'color' => 'from-orange-500 to-amber-500',
                    'title' => 'Conservatories',
                    'desc' => 'Rigorous ear training for pre-professional students. Integrate Harmoniva alongside your solfège and dictation curriculum. Track mastery of every interval, chord, and scale type. Export performance data for placement auditions and progress reviews.',
                    'bullets' => ['Supplement weekly ear training seminars', 'Track ABRSM & RCM-aligned skills', 'Audition preparation workflows'],
                ],
                [
                    'icon' => 'music',
                    'color' => 'from-purple-500 to-violet-600',
                    'title' => 'Music Academies',
                    'desc' => 'Give every student — from beginners to advanced learners — a structured ear training program that adapts to their pace. Assign homework, track streaks, and keep parents informed with automated progress reports.',
                    'bullets' => ['Beginner to advanced programs', 'Parent-facing progress visibility', 'Cross-instrument curricula'],
                ],
                [
                    'icon' => 'book-open',
                    'color' => 'from-blue-500 to-cyan-500',
                    'title' => 'University Music Departments',
                    'desc' => 'Deploy Harmoniva as the ear training platform for entire undergraduate and graduate programs. Scale from one class to a whole department with centralized analytics, multi-instructor management, and semester-length reporting.',
                    'bullets' => ['Multi-course deployment', 'TA and instructor role separation', 'Semester report exports'],
                ],
                [
                    'icon' => 'school',
                    'color' => 'from-green-500 to-teal-500',
                    'title' => 'K–12 Music Programs',
                    'desc' => 'Make ear training fun and structured for younger students. Gamified exercises keep middle and high school musicians engaged while GDPR-compliant parental consent workflows and data controls keep administrators confident.',
                    'bullets' => ['Age-appropriate exercise modes', 'Parental consent workflows', 'Classroom-ready tools'],
                ],
            ]; @endphp
            @foreach ($useCases as $ui => $uc)
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-7 reveal" style="transition-delay:{{ $ui * 0.1 }}s">
                <div class="flex items-center gap-4 mb-4">
                    <div class="w-12 h-12 rounded-2xl bg-gradient-to-br {{ $uc['color'] }} flex items-center justify-center shadow-md">
                        <i data-lucide="{{ $uc['icon'] }}" class="w-6 h-6 text-white"></i>
                    </div>
                    <h3 class="text-xl font-extrabold text-gray-900">{{ $uc['title'] }}</h3>
                </div>
                <p class="text-gray-500 text-sm leading-relaxed mb-5">{{ $uc['desc'] }}</p>
                <ul class="space-y-2">
                    @foreach ($uc['bullets'] as $bullet)
                    <li class="flex items-center gap-2 text-sm text-gray-600">
                        <i data-lucide="check" class="w-4 h-4 text-green-500 shrink-0"></i>
                        {{ $bullet }}
                    </li>
                    @endforeach
                </ul>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- CTA --}}
<section class="py-24 relative overflow-hidden bg-gray-900">
    <div class="absolute -top-24 -right-24 w-[500px] h-[500px] rounded-full blur-3xl pointer-events-none" style="background:rgba(234,88,12,0.15);"></div>
    <div class="absolute -bottom-20 -left-20 w-[400px] h-[400px] rounded-full blur-3xl pointer-events-none" style="background:rgba(147,51,234,0.12);"></div>

    <div class="max-w-3xl mx-auto px-4 sm:px-6 text-center relative reveal">
        <div class="w-16 h-16 mx-auto rounded-2xl flex items-center justify-center mb-8 shadow-xl hero-badge" style="background:linear-gradient(135deg,#ea580c,#f97316);">
            <i data-lucide="building-2" class="w-8 h-8 text-white"></i>
        </div>
        <h2 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-white mb-5">
            Let's build your<br>
            <span class="font-serif italic font-normal" style="background:linear-gradient(135deg,#fb923c,#fbbf24);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;">ear training program together</span>
        </h2>
        <p class="text-gray-400 text-lg mb-10 max-w-xl mx-auto">
            Schedule a personalized demo and we'll walk you through exactly how Harmoniva fits your institution's needs.
        </p>
        <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
            <a href="/request-demo" class="inline-flex items-center gap-2 px-8 py-4 text-base font-bold text-white rounded-xl hover:opacity-90 transition-all shadow-xl hover:-translate-y-0.5" style="background:linear-gradient(135deg,#ea580c,#f97316);">
                <i data-lucide="calendar" class="w-5 h-5"></i>
                Request a Demo →
            </a>
            <a href="/pricing/teachers-and-schools" class="inline-flex items-center gap-2 px-6 py-4 text-base font-medium text-gray-300 hover:text-white transition-colors">
                View School Plans <i data-lucide="arrow-right" class="w-4 h-4"></i>
            </a>
        </div>
        <div class="flex flex-wrap items-center justify-center gap-6 text-sm text-gray-400 mt-8">
            <span class="flex items-center gap-1.5"><i data-lucide="check" class="w-4 h-4 text-green-500"></i>Custom onboarding session</span>
            <span class="flex items-center gap-1.5"><i data-lucide="check" class="w-4 h-4 text-green-500"></i>GDPR compliant</span>
            <span class="flex items-center gap-1.5"><i data-lucide="check" class="w-4 h-4 text-green-500"></i>Priority support included</span>
        </div>
    </div>
</section>

@endsection
