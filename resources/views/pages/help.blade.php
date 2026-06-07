@extends('layouts.standalone')

@section('title', 'Help Center')
@section('description', 'Find answers to your questions about Harmoniva. Browse help articles, explore categories, or contact our support team.')

@section('content')

{{-- Hero --}}
<section class="bg-gradient-to-br from-purple-700 via-purple-600 to-purple-800 text-white py-20 px-4">
    <div class="max-w-3xl mx-auto text-center reveal">
        <h1 class="text-4xl md:text-5xl font-bold mb-4">How can we help you?</h1>
        <p class="text-purple-200 text-lg mb-8">Search our knowledge base or browse categories below.</p>
        <div class="relative max-w-xl mx-auto">
            <input
                type="text"
                placeholder="Search for answers..."
                class="w-full rounded-2xl py-4 pl-6 pr-14 text-gray-800 text-lg shadow-lg focus:outline-none focus:ring-4 focus:ring-orange-400"
            />
            <button class="absolute right-3 top-1/2 -translate-y-1/2 bg-orange-500 hover:bg-orange-600 text-white rounded-xl p-2.5 transition-colors">
                <i data-lucide="search" class="w-5 h-5"></i>
            </button>
        </div>
    </div>
</section>

{{-- Category Cards --}}
<section class="bg-[#FAF7F2] py-20 px-4">
    <div class="max-w-6xl mx-auto">
        <h2 class="text-3xl font-bold text-gray-900 text-center mb-12 reveal">Browse by Category</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">

            {{-- Getting Started --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition-shadow reveal">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 rounded-xl bg-purple-100 flex items-center justify-center">
                        <i data-lucide="rocket" class="w-5 h-5 text-purple-600"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900">Getting Started</h3>
                </div>
                <ul class="space-y-2 text-sm text-gray-600">
                    <li><a href="#" class="hover:text-purple-600 transition-colors flex items-center gap-1.5"><i data-lucide="chevron-right" class="w-3.5 h-3.5 text-purple-400"></i> Creating your account</a></li>
                    <li><a href="#" class="hover:text-purple-600 transition-colors flex items-center gap-1.5"><i data-lucide="chevron-right" class="w-3.5 h-3.5 text-purple-400"></i> Your first practice session</a></li>
                    <li><a href="#" class="hover:text-purple-600 transition-colors flex items-center gap-1.5"><i data-lucide="chevron-right" class="w-3.5 h-3.5 text-purple-400"></i> Understanding the dashboard</a></li>
                    <li><a href="#" class="hover:text-purple-600 transition-colors flex items-center gap-1.5"><i data-lucide="chevron-right" class="w-3.5 h-3.5 text-purple-400"></i> Setting your skill level</a></li>
                </ul>
            </div>

            {{-- Account & Billing --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition-shadow reveal">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 rounded-xl bg-orange-100 flex items-center justify-center">
                        <i data-lucide="credit-card" class="w-5 h-5 text-orange-500"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900">Account & Billing</h3>
                </div>
                <ul class="space-y-2 text-sm text-gray-600">
                    <li><a href="#" class="hover:text-purple-600 transition-colors flex items-center gap-1.5"><i data-lucide="chevron-right" class="w-3.5 h-3.5 text-purple-400"></i> Upgrading to Premium</a></li>
                    <li><a href="#" class="hover:text-purple-600 transition-colors flex items-center gap-1.5"><i data-lucide="chevron-right" class="w-3.5 h-3.5 text-purple-400"></i> Managing your subscription</a></li>
                    <li><a href="#" class="hover:text-purple-600 transition-colors flex items-center gap-1.5"><i data-lucide="chevron-right" class="w-3.5 h-3.5 text-purple-400"></i> Canceling your plan</a></li>
                    <li><a href="#" class="hover:text-purple-600 transition-colors flex items-center gap-1.5"><i data-lucide="chevron-right" class="w-3.5 h-3.5 text-purple-400"></i> Billing FAQs & invoices</a></li>
                </ul>
            </div>

            {{-- Exercises & Practice --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition-shadow reveal">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 rounded-xl bg-green-100 flex items-center justify-center">
                        <i data-lucide="music" class="w-5 h-5 text-green-600"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900">Exercises & Practice</h3>
                </div>
                <ul class="space-y-2 text-sm text-gray-600">
                    <li><a href="#" class="hover:text-purple-600 transition-colors flex items-center gap-1.5"><i data-lucide="chevron-right" class="w-3.5 h-3.5 text-purple-400"></i> Available exercise types</a></li>
                    <li><a href="#" class="hover:text-purple-600 transition-colors flex items-center gap-1.5"><i data-lucide="chevron-right" class="w-3.5 h-3.5 text-purple-400"></i> Using the Exercise Setup Studio</a></li>
                    <li><a href="#" class="hover:text-purple-600 transition-colors flex items-center gap-1.5"><i data-lucide="chevron-right" class="w-3.5 h-3.5 text-purple-400"></i> AI-powered practice mode</a></li>
                    <li><a href="#" class="hover:text-purple-600 transition-colors flex items-center gap-1.5"><i data-lucide="chevron-right" class="w-3.5 h-3.5 text-purple-400"></i> Tracking your progress</a></li>
                </ul>
            </div>

            {{-- Learning Path --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition-shadow reveal">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 rounded-xl bg-blue-100 flex items-center justify-center">
                        <i data-lucide="map" class="w-5 h-5 text-blue-600"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900">Learning Path</h3>
                </div>
                <ul class="space-y-2 text-sm text-gray-600">
                    <li><a href="#" class="hover:text-purple-600 transition-colors flex items-center gap-1.5"><i data-lucide="chevron-right" class="w-3.5 h-3.5 text-purple-400"></i> What is the Learning Path?</a></li>
                    <li><a href="#" class="hover:text-purple-600 transition-colors flex items-center gap-1.5"><i data-lucide="chevron-right" class="w-3.5 h-3.5 text-purple-400"></i> How exercises are structured</a></li>
                    <li><a href="#" class="hover:text-purple-600 transition-colors flex items-center gap-1.5"><i data-lucide="chevron-right" class="w-3.5 h-3.5 text-purple-400"></i> Advancing through levels</a></li>
                    <li><a href="#" class="hover:text-purple-600 transition-colors flex items-center gap-1.5"><i data-lucide="chevron-right" class="w-3.5 h-3.5 text-purple-400"></i> Resetting your progress</a></li>
                </ul>
            </div>

            {{-- Teachers & Schools --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition-shadow reveal">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 rounded-xl bg-yellow-100 flex items-center justify-center">
                        <i data-lucide="graduation-cap" class="w-5 h-5 text-yellow-600"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900">Teachers & Schools</h3>
                </div>
                <ul class="space-y-2 text-sm text-gray-600">
                    <li><a href="#" class="hover:text-purple-600 transition-colors flex items-center gap-1.5"><i data-lucide="chevron-right" class="w-3.5 h-3.5 text-purple-400"></i> Teacher account features</a></li>
                    <li><a href="#" class="hover:text-purple-600 transition-colors flex items-center gap-1.5"><i data-lucide="chevron-right" class="w-3.5 h-3.5 text-purple-400"></i> Assigning exercises to students</a></li>
                    <li><a href="#" class="hover:text-purple-600 transition-colors flex items-center gap-1.5"><i data-lucide="chevron-right" class="w-3.5 h-3.5 text-purple-400"></i> School & institution plans</a></li>
                    <li><a href="#" class="hover:text-purple-600 transition-colors flex items-center gap-1.5"><i data-lucide="chevron-right" class="w-3.5 h-3.5 text-purple-400"></i> Bulk seat management</a></li>
                </ul>
            </div>

            {{-- Technical Issues --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition-shadow reveal">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 rounded-xl bg-red-100 flex items-center justify-center">
                        <i data-lucide="wrench" class="w-5 h-5 text-red-500"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900">Technical Issues</h3>
                </div>
                <ul class="space-y-2 text-sm text-gray-600">
                    <li><a href="#" class="hover:text-purple-600 transition-colors flex items-center gap-1.5"><i data-lucide="chevron-right" class="w-3.5 h-3.5 text-purple-400"></i> Audio not playing</a></li>
                    <li><a href="#" class="hover:text-purple-600 transition-colors flex items-center gap-1.5"><i data-lucide="chevron-right" class="w-3.5 h-3.5 text-purple-400"></i> Browser compatibility</a></li>
                    <li><a href="#" class="hover:text-purple-600 transition-colors flex items-center gap-1.5"><i data-lucide="chevron-right" class="w-3.5 h-3.5 text-purple-400"></i> Login & authentication issues</a></li>
                    <li><a href="#" class="hover:text-purple-600 transition-colors flex items-center gap-1.5"><i data-lucide="chevron-right" class="w-3.5 h-3.5 text-purple-400"></i> Clearing cache & cookies</a></li>
                </ul>
            </div>

        </div>
    </div>
</section>

{{-- Popular Articles --}}
<section class="bg-white py-20 px-4">
    <div class="max-w-6xl mx-auto">
        <h2 class="text-3xl font-bold text-gray-900 text-center mb-3 reveal">Popular Articles</h2>
        <p class="text-gray-500 text-center mb-12 reveal">The most-read guides from our knowledge base.</p>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">

            @php
            $articles = [
                ['icon' => 'headphones', 'color' => 'purple', 'title' => 'Ear Training for Absolute Beginners', 'desc' => 'Everything you need to know before your first session — what to expect, how to listen, and how to make it stick.'],
                ['icon' => 'settings-2', 'color' => 'orange', 'title' => 'How to Set Up Your First Custom Exercise', 'desc' => 'Use the Exercise Setup Studio to create a practice session perfectly tailored to your skill level and goals.'],
                ['icon' => 'music-2', 'color' => 'green', 'title' => 'Understanding Intervals: A Practical Guide', 'desc' => 'Learn what intervals are, why they matter, and how to identify them reliably by ear with consistent practice.'],
                ['icon' => 'brain', 'color' => 'blue', 'title' => 'How the AI Practice Mode Works', 'desc' => 'Harmoniva\'s AI adapts questions to your weaknesses in real time. Here\'s the science behind how it helps you improve faster.'],
                ['icon' => 'bar-chart-2', 'color' => 'yellow', 'title' => 'Reading Your Progress Dashboard', 'desc' => 'Understand your accuracy trends, streaks, and improvement areas with a walkthrough of every chart and metric.'],
                ['icon' => 'users', 'color' => 'red', 'title' => 'Getting Started as a Teacher', 'desc' => 'Set up your teacher account, create student groups, assign exercises, and monitor progress — all from one place.'],
            ];
            @endphp

            @foreach($articles as $article)
            <a href="#" class="group flex items-start gap-4 bg-[#FAF7F2] rounded-2xl p-5 hover:shadow-md transition-shadow reveal">
                <div class="w-10 h-10 rounded-xl bg-{{ $article['color'] }}-100 flex items-center justify-center flex-shrink-0 mt-0.5">
                    <i data-lucide="{{ $article['icon'] }}" class="w-5 h-5 text-{{ $article['color'] }}-600"></i>
                </div>
                <div>
                    <h3 class="font-semibold text-gray-900 group-hover:text-purple-600 transition-colors mb-1">{{ $article['title'] }}</h3>
                    <p class="text-sm text-gray-500 leading-relaxed">{{ $article['desc'] }}</p>
                </div>
            </a>
            @endforeach

        </div>
    </div>
</section>

{{-- Bottom CTA --}}
<section class="bg-gradient-to-br from-purple-50 to-orange-50 py-20 px-4">
    <div class="max-w-2xl mx-auto text-center reveal">
        <div class="w-16 h-16 rounded-2xl bg-purple-100 flex items-center justify-center mx-auto mb-6">
            <i data-lucide="life-buoy" class="w-8 h-8 text-purple-600"></i>
        </div>
        <h2 class="text-3xl font-bold text-gray-900 mb-4">Still need help?</h2>
        <p class="text-gray-600 mb-8 text-lg">Our support team typically responds within 24 hours. We'd love to hear from you.</p>
        <a href="/contact" class="inline-flex items-center gap-2 bg-purple-600 hover:bg-purple-700 text-white font-semibold px-8 py-4 rounded-xl transition-colors text-lg shadow-lg hover:shadow-xl">
            <i data-lucide="mail" class="w-5 h-5"></i>
            Contact Support
        </a>
    </div>
</section>

@endsection
