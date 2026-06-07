@extends('layouts.standalone')

@section('title', 'Partners & Integrations — Harmoniva')
@section('description', 'Partner with Harmoniva to build the future of music education together. Programs for music schools, content creators, educators, and technology partners.')

@section('content')

{{-- Hero --}}
<section class="py-24 sm:py-32 relative overflow-hidden" style="background: linear-gradient(135deg, #faf5ff 0%, #FAF7F2 60%, #fef3c7 100%);">
    <div class="absolute -top-32 -right-32 w-[500px] h-[500px] rounded-full bg-purple-100/50 blur-3xl pointer-events-none"></div>
    <div class="absolute -bottom-20 -left-20 w-[300px] h-[300px] rounded-full bg-orange-50/60 blur-2xl pointer-events-none"></div>

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center relative">
        <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-purple-100 text-purple-700 text-sm font-semibold mb-6">
            <i data-lucide="handshake" class="w-4 h-4"></i>
            Partnership Programs
        </div>

        <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold text-gray-900 leading-tight mb-6">
            Partner with<br>
            <span class="font-serif italic font-normal bg-gradient-to-r from-purple-600 to-orange-500 bg-clip-text text-transparent">Harmoniva</span>
        </h1>

        <p class="text-gray-600 text-xl leading-relaxed max-w-2xl mx-auto">
            Let's build the future of music education together. Whether you're a music school, a content creator, or a technology company — there's a partnership model designed for you.
        </p>
    </div>
</section>

{{-- Partnership Types --}}
<section class="py-20 bg-white">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-14">
            <h2 class="text-3xl sm:text-4xl font-extrabold text-gray-900">Partnership Programs</h2>
            <p class="text-gray-500 mt-3 text-lg">Find the program that fits your organization.</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

            {{-- Music Schools --}}
            <div class="bg-[#FAF7F2] rounded-2xl p-8 border border-gray-100 flex flex-col">
                <div class="w-14 h-14 bg-purple-100 rounded-xl flex items-center justify-center mb-6">
                    <i data-lucide="school" class="w-7 h-7 text-purple-600"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-3">Music Schools &amp; Institutions</h3>
                <p class="text-gray-600 leading-relaxed mb-6 text-sm flex-grow">
                    Bring Harmoniva into your curriculum. Our institutional plan gives your students and faculty access to the full platform, with teacher dashboards, progress tracking, and custom learning paths built around your syllabus.
                </p>
                <ul class="space-y-2.5 mb-8">
                    <li class="flex items-start gap-2.5 text-sm text-gray-700">
                        <i data-lucide="check-circle" class="w-4 h-4 text-green-500 flex-shrink-0 mt-0.5"></i>
                        Bulk seat licensing with volume discounts
                    </li>
                    <li class="flex items-start gap-2.5 text-sm text-gray-700">
                        <i data-lucide="check-circle" class="w-4 h-4 text-green-500 flex-shrink-0 mt-0.5"></i>
                        Co-branded student portal
                    </li>
                    <li class="flex items-start gap-2.5 text-sm text-gray-700">
                        <i data-lucide="check-circle" class="w-4 h-4 text-green-500 flex-shrink-0 mt-0.5"></i>
                        Teacher progress dashboards
                    </li>
                    <li class="flex items-start gap-2.5 text-sm text-gray-700">
                        <i data-lucide="check-circle" class="w-4 h-4 text-green-500 flex-shrink-0 mt-0.5"></i>
                        Custom learning path creation
                    </li>
                    <li class="flex items-start gap-2.5 text-sm text-gray-700">
                        <i data-lucide="check-circle" class="w-4 h-4 text-green-500 flex-shrink-0 mt-0.5"></i>
                        Priority onboarding and support
                    </li>
                </ul>
                <a href="#partner-form" class="inline-flex items-center justify-center gap-2 bg-purple-600 hover:bg-purple-700 text-white font-semibold px-6 py-3 rounded-xl transition-colors duration-200 w-full">
                    Apply <i data-lucide="arrow-right" class="w-4 h-4"></i>
                </a>
            </div>

            {{-- Content Creators --}}
            <div class="bg-[#FAF7F2] rounded-2xl p-8 border border-gray-100 flex flex-col">
                <div class="w-14 h-14 bg-orange-100 rounded-xl flex items-center justify-center mb-6">
                    <i data-lucide="video" class="w-7 h-7 text-orange-600"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-3">Content Creators &amp; Educators</h3>
                <p class="text-gray-600 leading-relaxed mb-6 text-sm flex-grow">
                    If you teach music online — through YouTube, a course platform, or a membership site — Harmoniva gives your students a dedicated practice tool to complement your content. Earn revenue while delivering real value.
                </p>
                <ul class="space-y-2.5 mb-8">
                    <li class="flex items-start gap-2.5 text-sm text-gray-700">
                        <i data-lucide="check-circle" class="w-4 h-4 text-green-500 flex-shrink-0 mt-0.5"></i>
                        Affiliate and referral commission program
                    </li>
                    <li class="flex items-start gap-2.5 text-sm text-gray-700">
                        <i data-lucide="check-circle" class="w-4 h-4 text-green-500 flex-shrink-0 mt-0.5"></i>
                        Custom discount codes for your audience
                    </li>
                    <li class="flex items-start gap-2.5 text-sm text-gray-700">
                        <i data-lucide="check-circle" class="w-4 h-4 text-green-500 flex-shrink-0 mt-0.5"></i>
                        Co-marketing opportunities
                    </li>
                    <li class="flex items-start gap-2.5 text-sm text-gray-700">
                        <i data-lucide="check-circle" class="w-4 h-4 text-green-500 flex-shrink-0 mt-0.5"></i>
                        Complimentary premium account
                    </li>
                    <li class="flex items-start gap-2.5 text-sm text-gray-700">
                        <i data-lucide="check-circle" class="w-4 h-4 text-green-500 flex-shrink-0 mt-0.5"></i>
                        Featured in our educator directory
                    </li>
                </ul>
                <a href="#partner-form" class="inline-flex items-center justify-center gap-2 bg-orange-500 hover:bg-orange-600 text-white font-semibold px-6 py-3 rounded-xl transition-colors duration-200 w-full">
                    Apply <i data-lucide="arrow-right" class="w-4 h-4"></i>
                </a>
            </div>

            {{-- Technology Partners --}}
            <div class="bg-[#FAF7F2] rounded-2xl p-8 border border-gray-100 flex flex-col">
                <div class="w-14 h-14 bg-blue-100 rounded-xl flex items-center justify-center mb-6">
                    <i data-lucide="cpu" class="w-7 h-7 text-blue-600"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-3">Technology Partners</h3>
                <p class="text-gray-600 leading-relaxed mb-6 text-sm flex-grow">
                    Building something for musicians? Let's connect our platforms. Whether it's a DAW plugin, notation software, or a music learning platform, we're open to API integrations and strategic technology partnerships.
                </p>
                <ul class="space-y-2.5 mb-8">
                    <li class="flex items-start gap-2.5 text-sm text-gray-700">
                        <i data-lucide="check-circle" class="w-4 h-4 text-green-500 flex-shrink-0 mt-0.5"></i>
                        API access and integration support
                    </li>
                    <li class="flex items-start gap-2.5 text-sm text-gray-700">
                        <i data-lucide="check-circle" class="w-4 h-4 text-green-500 flex-shrink-0 mt-0.5"></i>
                        Joint go-to-market opportunities
                    </li>
                    <li class="flex items-start gap-2.5 text-sm text-gray-700">
                        <i data-lucide="check-circle" class="w-4 h-4 text-green-500 flex-shrink-0 mt-0.5"></i>
                        Revenue sharing on referred business
                    </li>
                    <li class="flex items-start gap-2.5 text-sm text-gray-700">
                        <i data-lucide="check-circle" class="w-4 h-4 text-green-500 flex-shrink-0 mt-0.5"></i>
                        Dedicated integration engineering support
                    </li>
                    <li class="flex items-start gap-2.5 text-sm text-gray-700">
                        <i data-lucide="check-circle" class="w-4 h-4 text-green-500 flex-shrink-0 mt-0.5"></i>
                        Early access to new platform features
                    </li>
                </ul>
                <a href="#partner-form" class="inline-flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-3 rounded-xl transition-colors duration-200 w-full">
                    Apply <i data-lucide="arrow-right" class="w-4 h-4"></i>
                </a>
            </div>
        </div>
    </div>
</section>

{{-- Why Partner --}}
<section class="py-20 bg-gray-900 text-white">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-3xl sm:text-4xl font-extrabold mb-3">Why Partner with Harmoniva?</h2>
            <p class="text-gray-400 text-lg">We invest in our partners' success as much as our own.</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="bg-white/5 rounded-2xl p-6 border border-white/10 hover:bg-white/10 transition-colors duration-300">
                <div class="w-12 h-12 bg-purple-600/30 rounded-xl flex items-center justify-center mb-4">
                    <i data-lucide="megaphone" class="w-6 h-6 text-purple-400"></i>
                </div>
                <h3 class="font-bold text-white text-lg mb-2">Co-Marketing</h3>
                <p class="text-gray-400 text-sm leading-relaxed">
                    We feature partners in our newsletter, blog, social channels, and in-app discovery. Your audience grows alongside ours.
                </p>
            </div>

            <div class="bg-white/5 rounded-2xl p-6 border border-white/10 hover:bg-white/10 transition-colors duration-300">
                <div class="w-12 h-12 bg-orange-500/30 rounded-xl flex items-center justify-center mb-4">
                    <i data-lucide="dollar-sign" class="w-6 h-6 text-orange-400"></i>
                </div>
                <h3 class="font-bold text-white text-lg mb-2">Revenue Share</h3>
                <p class="text-gray-400 text-sm leading-relaxed">
                    Meaningful commission structures that reward partners who bring real value. We don't do token percentages.
                </p>
            </div>

            <div class="bg-white/5 rounded-2xl p-6 border border-white/10 hover:bg-white/10 transition-colors duration-300">
                <div class="w-12 h-12 bg-green-500/30 rounded-xl flex items-center justify-center mb-4">
                    <i data-lucide="life-buoy" class="w-6 h-6 text-green-400"></i>
                </div>
                <h3 class="font-bold text-white text-lg mb-2">Dedicated Support</h3>
                <p class="text-gray-400 text-sm leading-relaxed">
                    Every partner gets a dedicated account manager and priority support access. You're never left waiting in a ticket queue.
                </p>
            </div>

            <div class="bg-white/5 rounded-2xl p-6 border border-white/10 hover:bg-white/10 transition-colors duration-300">
                <div class="w-12 h-12 bg-blue-500/30 rounded-xl flex items-center justify-center mb-4">
                    <i data-lucide="zap" class="w-6 h-6 text-blue-400"></i>
                </div>
                <h3 class="font-bold text-white text-lg mb-2">Early Feature Access</h3>
                <p class="text-gray-400 text-sm leading-relaxed">
                    Partners get early access to new exercises, AI features, and platform updates — giving you a head start on incorporating them into your workflow.
                </p>
            </div>
        </div>
    </div>
</section>

{{-- Partner Inquiry Form --}}
<section id="partner-form" class="py-20 bg-white">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-10">
            <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-purple-100 text-purple-700 text-sm font-semibold mb-4">
                <i data-lucide="send" class="w-4 h-4"></i>
                Get in Touch
            </div>
            <h2 class="text-3xl font-extrabold text-gray-900 mb-3">Apply to Partner</h2>
            <p class="text-gray-500 text-lg">Tell us about your organization and the kind of partnership you have in mind. We'll respond within 2 business days.</p>
        </div>

        <form action="#" method="POST" class="bg-[#FAF7F2] rounded-2xl p-8 border border-gray-100 space-y-5">
            @csrf
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label for="name" class="block text-sm font-semibold text-gray-700 mb-1.5">Full Name</label>
                    <input type="text" id="name" name="name" required
                        class="w-full px-4 py-3 rounded-xl border border-gray-200 bg-white text-gray-900 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-shadow"
                        placeholder="Jane Smith">
                </div>
                <div>
                    <label for="email" class="block text-sm font-semibold text-gray-700 mb-1.5">Email Address</label>
                    <input type="email" id="email" name="email" required
                        class="w-full px-4 py-3 rounded-xl border border-gray-200 bg-white text-gray-900 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-shadow"
                        placeholder="jane@organization.com">
                </div>
            </div>

            <div>
                <label for="organization" class="block text-sm font-semibold text-gray-700 mb-1.5">Organization Name</label>
                <input type="text" id="organization" name="organization" required
                    class="w-full px-4 py-3 rounded-xl border border-gray-200 bg-white text-gray-900 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-shadow"
                    placeholder="Your school, company, or channel name">
            </div>

            <div>
                <label for="partnership_type" class="block text-sm font-semibold text-gray-700 mb-1.5">Partnership Type</label>
                <select id="partnership_type" name="partnership_type" required
                    class="w-full px-4 py-3 rounded-xl border border-gray-200 bg-white text-gray-900 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-shadow">
                    <option value="" disabled selected>Select a program</option>
                    <option value="school">Music School or Institution</option>
                    <option value="creator">Content Creator or Educator</option>
                    <option value="technology">Technology Partner</option>
                    <option value="other">Other</option>
                </select>
            </div>

            <div>
                <label for="message" class="block text-sm font-semibold text-gray-700 mb-1.5">Tell us about your organization</label>
                <textarea id="message" name="message" rows="5" required
                    class="w-full px-4 py-3 rounded-xl border border-gray-200 bg-white text-gray-900 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-shadow resize-none"
                    placeholder="Describe your organization, your audience size, and what kind of partnership you have in mind..."></textarea>
            </div>

            <button type="submit"
                class="w-full inline-flex items-center justify-center gap-2 bg-purple-600 hover:bg-purple-700 text-white font-bold px-6 py-3.5 rounded-xl transition-colors duration-200 text-lg">
                <i data-lucide="send" class="w-5 h-5"></i>
                Submit Partnership Inquiry
            </button>

            <p class="text-center text-sm text-gray-500">
                Or reach us directly at <a href="mailto:partners@harmoniva.app" class="text-purple-600 font-medium hover:text-purple-700 transition-colors">partners@harmoniva.app</a>
            </p>
        </form>
    </div>
</section>

@endsection
