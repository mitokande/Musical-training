@extends('layouts.standalone')

@section('title', 'Press & Media — Harmoniva')
@section('description', 'Press resources, brand assets, and media inquiries for Harmoniva — the AI-powered ear training platform for musicians.')

@section('content')

{{-- Hero --}}
<section class="py-24 bg-gray-900 text-white relative overflow-hidden">
    <div class="absolute inset-0 opacity-10" style="background: radial-gradient(ellipse at top right, #9333ea, transparent 60%), radial-gradient(ellipse at bottom left, #f97316, transparent 60%);"></div>
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center relative">
        <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-white/10 text-white text-sm font-semibold mb-6">
            <i data-lucide="newspaper" class="w-4 h-4"></i>
            Media Resources
        </div>
        <h1 class="text-4xl sm:text-5xl font-extrabold mb-5">Press &amp; Media</h1>
        <p class="text-gray-300 text-xl max-w-2xl mx-auto">
            Everything you need to cover Harmoniva — brand assets, fact sheets, and press contacts. For media inquiries, reach us at <a href="mailto:press@harmoniva.app" class="text-purple-400 hover:text-purple-300 transition-colors">press@harmoniva.app</a>.
        </p>
    </div>
</section>

{{-- Fact Sheet --}}
<section class="py-20 bg-white">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-start">

            {{-- Fact Sheet --}}
            <div>
                <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-purple-100 text-purple-700 text-sm font-semibold mb-5">
                    <i data-lucide="file-text" class="w-4 h-4"></i>
                    Quick Facts
                </div>
                <h2 class="text-3xl font-extrabold text-gray-900 mb-8">Company Fact Sheet</h2>

                <dl class="space-y-4">
                    <div class="flex gap-4 py-4 border-b border-gray-100">
                        <dt class="w-36 text-sm font-semibold text-gray-500 uppercase tracking-wide flex-shrink-0">Founded</dt>
                        <dd class="text-gray-900 font-medium">2024</dd>
                    </div>
                    <div class="flex gap-4 py-4 border-b border-gray-100">
                        <dt class="w-36 text-sm font-semibold text-gray-500 uppercase tracking-wide flex-shrink-0">Headquarters</dt>
                        <dd class="text-gray-900 font-medium">Dover, Delaware, USA</dd>
                    </div>
                    <div class="flex gap-4 py-4 border-b border-gray-100">
                        <dt class="w-36 text-sm font-semibold text-gray-500 uppercase tracking-wide flex-shrink-0">Legal Entity</dt>
                        <dd class="text-gray-900 font-medium">H&amp;P LLC</dd>
                    </div>
                    <div class="flex gap-4 py-4 border-b border-gray-100">
                        <dt class="w-36 text-sm font-semibold text-gray-500 uppercase tracking-wide flex-shrink-0">Product</dt>
                        <dd class="text-gray-900 font-medium">AI-powered ear training SaaS for musicians, students, teachers, and schools</dd>
                    </div>
                    <div class="flex gap-4 py-4 border-b border-gray-100">
                        <dt class="w-36 text-sm font-semibold text-gray-500 uppercase tracking-wide flex-shrink-0">Platform</dt>
                        <dd class="text-gray-900 font-medium">Web (harmoniva.app) — mobile coming soon</dd>
                    </div>
                    <div class="flex gap-4 py-4 border-b border-gray-100">
                        <dt class="w-36 text-sm font-semibold text-gray-500 uppercase tracking-wide flex-shrink-0">Languages</dt>
                        <dd class="text-gray-900 font-medium">15 supported languages</dd>
                    </div>
                    <div class="flex gap-4 py-4 border-b border-gray-100">
                        <dt class="w-36 text-sm font-semibold text-gray-500 uppercase tracking-wide flex-shrink-0">Users</dt>
                        <dd class="text-gray-900 font-medium">10,000+ active musicians</dd>
                    </div>
                    <div class="flex gap-4 py-4">
                        <dt class="w-36 text-sm font-semibold text-gray-500 uppercase tracking-wide flex-shrink-0">Press Contact</dt>
                        <dd><a href="mailto:press@harmoniva.app" class="text-purple-600 font-medium hover:text-purple-700 transition-colors">press@harmoniva.app</a></dd>
                    </div>
                </dl>
            </div>

            {{-- Brand Assets --}}
            <div>
                <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-orange-100 text-orange-700 text-sm font-semibold mb-5">
                    <i data-lucide="palette" class="w-4 h-4"></i>
                    Brand Assets
                </div>
                <h2 class="text-3xl font-extrabold text-gray-900 mb-8">Brand Resources</h2>

                <div class="space-y-4 mb-8">
                    <a href="#" class="flex items-center gap-4 p-4 border border-gray-200 rounded-xl hover:border-purple-300 hover:bg-purple-50 transition-all duration-200 group">
                        <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center group-hover:bg-purple-200 transition-colors">
                            <i data-lucide="image" class="w-6 h-6 text-purple-600"></i>
                        </div>
                        <div>
                            <div class="font-semibold text-gray-900">Logo Pack (SVG + PNG)</div>
                            <div class="text-sm text-gray-500">Light, dark, and icon-only variants</div>
                        </div>
                        <i data-lucide="download" class="w-5 h-5 text-gray-400 ml-auto group-hover:text-purple-600 transition-colors"></i>
                    </a>

                    <a href="#" class="flex items-center gap-4 p-4 border border-gray-200 rounded-xl hover:border-purple-300 hover:bg-purple-50 transition-all duration-200 group">
                        <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center group-hover:bg-orange-200 transition-colors">
                            <i data-lucide="swatch-book" class="w-6 h-6 text-orange-600"></i>
                        </div>
                        <div>
                            <div class="font-semibold text-gray-900">Brand Guidelines (PDF)</div>
                            <div class="text-sm text-gray-500">Colors, typography, usage rules</div>
                        </div>
                        <i data-lucide="download" class="w-5 h-5 text-gray-400 ml-auto group-hover:text-purple-600 transition-colors"></i>
                    </a>

                    <a href="#" class="flex items-center gap-4 p-4 border border-gray-200 rounded-xl hover:border-purple-300 hover:bg-purple-50 transition-all duration-200 group">
                        <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center group-hover:bg-blue-200 transition-colors">
                            <i data-lucide="monitor" class="w-6 h-6 text-blue-600"></i>
                        </div>
                        <div>
                            <div class="font-semibold text-gray-900">Product Screenshots</div>
                            <div class="text-sm text-gray-500">High-resolution app screenshots</div>
                        </div>
                        <i data-lucide="download" class="w-5 h-5 text-gray-400 ml-auto group-hover:text-purple-600 transition-colors"></i>
                    </a>
                </div>

                {{-- Brand Colors --}}
                <h3 class="font-bold text-gray-900 mb-3">Brand Colors</h3>
                <div class="flex gap-3 mb-6">
                    <div class="flex-1">
                        <div class="h-12 rounded-lg mb-2" style="background: #9333ea;"></div>
                        <div class="text-xs font-semibold text-gray-700">Primary Purple</div>
                        <div class="text-xs text-gray-500">#9333EA</div>
                    </div>
                    <div class="flex-1">
                        <div class="h-12 rounded-lg mb-2" style="background: #f97316;"></div>
                        <div class="text-xs font-semibold text-gray-700">Accent Orange</div>
                        <div class="text-xs text-gray-500">#F97316</div>
                    </div>
                    <div class="flex-1">
                        <div class="h-12 rounded-lg mb-2 border border-gray-200" style="background: #FAF7F2;"></div>
                        <div class="text-xs font-semibold text-gray-700">Background Cream</div>
                        <div class="text-xs text-gray-500">#FAF7F2</div>
                    </div>
                    <div class="flex-1">
                        <div class="h-12 rounded-lg mb-2" style="background: #111827;"></div>
                        <div class="text-xs font-semibold text-gray-700">Text Dark</div>
                        <div class="text-xs text-gray-500">#111827</div>
                    </div>
                </div>

                <h3 class="font-bold text-gray-900 mb-2">Typography</h3>
                <p class="text-sm text-gray-600">Primary: <span class="font-semibold">Inter</span> (UI copy). Display: <span class="font-semibold">Lora</span> (serif italics for headings).</p>
            </div>
        </div>
    </div>
</section>

{{-- Press Mentions --}}
<section class="py-20 bg-[#FAF7F2]">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-purple-100 text-purple-700 text-sm font-semibold mb-4">
                <i data-lucide="quote" class="w-4 h-4"></i>
                In the News
            </div>
            <h2 class="text-3xl font-extrabold text-gray-900">Recent Mentions</h2>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm hover:shadow-md transition-shadow duration-300">
                <div class="flex items-center justify-between mb-4">
                    <div class="text-sm font-bold text-gray-500 uppercase tracking-wide">Music Tech Review</div>
                    <div class="text-xs text-gray-400">May 2026</div>
                </div>
                <h3 class="font-bold text-gray-900 text-lg mb-3 leading-snug">
                    "Harmoniva Brings AI-Powered Ear Training to the Masses"
                </h3>
                <p class="text-gray-600 text-sm leading-relaxed mb-4">
                    "The platform offers a refreshingly modern take on an often overlooked corner of music education — with adaptive exercises and genuine AI integration that sets it apart from legacy tools."
                </p>
                <a href="#" class="text-purple-600 text-sm font-semibold hover:text-purple-700 transition-colors inline-flex items-center gap-1">
                    Read more <i data-lucide="arrow-right" class="w-4 h-4"></i>
                </a>
            </div>

            <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm hover:shadow-md transition-shadow duration-300">
                <div class="flex items-center justify-between mb-4">
                    <div class="text-sm font-bold text-gray-500 uppercase tracking-wide">EdTech Insider</div>
                    <div class="text-xs text-gray-400">April 2026</div>
                </div>
                <h3 class="font-bold text-gray-900 text-lg mb-3 leading-snug">
                    "Top 10 Music Education Platforms to Watch in 2026"
                </h3>
                <p class="text-gray-600 text-sm leading-relaxed mb-4">
                    "Harmoniva stands out in a crowded space by focusing on what matters most: the quality of the learning experience. With support for 15 languages, it's building toward truly global reach."
                </p>
                <a href="#" class="text-purple-600 text-sm font-semibold hover:text-purple-700 transition-colors inline-flex items-center gap-1">
                    Read more <i data-lucide="arrow-right" class="w-4 h-4"></i>
                </a>
            </div>

            <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm hover:shadow-md transition-shadow duration-300">
                <div class="flex items-center justify-between mb-4">
                    <div class="text-sm font-bold text-gray-500 uppercase tracking-wide">SaaS Weekly</div>
                    <div class="text-xs text-gray-400">March 2026</div>
                </div>
                <h3 class="font-bold text-gray-900 text-lg mb-3 leading-snug">
                    "How Harmoniva Is Disrupting the Music Education Market"
                </h3>
                <p class="text-gray-600 text-sm leading-relaxed mb-4">
                    "A lean team, a clear mission, and a product that musicians actually want to use every day — Harmoniva is quietly building something significant in the music ed space."
                </p>
                <a href="#" class="text-purple-600 text-sm font-semibold hover:text-purple-700 transition-colors inline-flex items-center gap-1">
                    Read more <i data-lucide="arrow-right" class="w-4 h-4"></i>
                </a>
            </div>
        </div>
    </div>
</section>

{{-- Press Contact CTA --}}
<section class="py-20 bg-white">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-gray-900 rounded-2xl p-10 text-center text-white">
            <div class="w-14 h-14 bg-purple-600 rounded-xl flex items-center justify-center mx-auto mb-5">
                <i data-lucide="mail" class="w-7 h-7 text-white"></i>
            </div>
            <h2 class="text-2xl font-extrabold mb-3">Media Inquiries</h2>
            <p class="text-gray-400 mb-6 leading-relaxed">
                Working on a story about music education, EdTech, or AI-powered learning? We'd love to help. Our team responds to press inquiries within one business day.
            </p>
            <a href="mailto:press@harmoniva.app" class="inline-flex items-center gap-2 bg-purple-600 hover:bg-purple-700 text-white font-bold px-7 py-3.5 rounded-xl transition-colors duration-200">
                <i data-lucide="send" class="w-5 h-5"></i>
                press@harmoniva.app
            </a>
        </div>
    </div>
</section>

@endsection
