@extends('layouts.standalone')

@section('title', 'Contact Support')
@section('description', 'Get in touch with the Harmoniva support team. We typically respond within 24 hours and are here to help with any questions about your account, billing, or the platform.')

@section('content')

{{-- Hero --}}
<section class="bg-gradient-to-br from-purple-700 via-purple-600 to-purple-800 text-white py-20 px-4">
    <div class="max-w-3xl mx-auto text-center reveal">
        <div class="inline-flex items-center gap-2 bg-white/10 text-white text-sm font-medium px-4 py-2 rounded-full mb-6">
            <i data-lucide="message-circle" class="w-4 h-4"></i>
            Support
        </div>
        <h1 class="text-4xl md:text-5xl font-bold mb-4">We're Here to Help</h1>
        <p class="text-purple-200 text-lg">Send us a message and we'll get back to you within 24 hours.</p>
    </div>
</section>

{{-- Support Options --}}
<section class="bg-[#FAF7F2] py-12 px-4 border-b border-gray-100">
    <div class="max-w-5xl mx-auto">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm flex items-center gap-4 reveal">
                <div class="w-11 h-11 rounded-xl bg-purple-100 flex items-center justify-center flex-shrink-0">
                    <i data-lucide="mail" class="w-5 h-5 text-purple-600"></i>
                </div>
                <div>
                    <p class="font-semibold text-gray-900 text-sm">Email Support</p>
                    <p class="text-gray-500 text-xs mt-0.5">support@harmoniva.app</p>
                </div>
            </div>
            <a href="/help" class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm flex items-center gap-4 hover:border-purple-200 hover:shadow-md transition-all group reveal">
                <div class="w-11 h-11 rounded-xl bg-blue-100 flex items-center justify-center flex-shrink-0">
                    <i data-lucide="book-open" class="w-5 h-5 text-blue-600"></i>
                </div>
                <div>
                    <p class="font-semibold text-gray-900 text-sm group-hover:text-purple-600 transition-colors">Help Center</p>
                    <p class="text-gray-500 text-xs mt-0.5">Browse articles & guides</p>
                </div>
            </a>
            <a href="/faq" class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm flex items-center gap-4 hover:border-purple-200 hover:shadow-md transition-all group reveal">
                <div class="w-11 h-11 rounded-xl bg-green-100 flex items-center justify-center flex-shrink-0">
                    <i data-lucide="help-circle" class="w-5 h-5 text-green-600"></i>
                </div>
                <div>
                    <p class="font-semibold text-gray-900 text-sm group-hover:text-purple-600 transition-colors">FAQ</p>
                    <p class="text-gray-500 text-xs mt-0.5">Quick answers to common questions</p>
                </div>
            </a>
        </div>
    </div>
</section>

{{-- Main Two-Column Layout --}}
<section class="bg-[#FAF7F2] py-16 px-4">
    <div class="max-w-5xl mx-auto">
        <div class="grid grid-cols-1 lg:grid-cols-5 gap-10">

            {{-- Contact Form (wider) --}}
            <div class="lg:col-span-3 reveal">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
                    <h2 class="text-2xl font-bold text-gray-900 mb-2">Send Us a Message</h2>
                    <p class="text-gray-500 text-sm mb-8">Fill out the form below and we'll get back to you as soon as possible.</p>

                    <form action="#" method="POST" class="space-y-5">
                        @csrf

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                            <div>
                                <label for="contact_name" class="block text-sm font-semibold text-gray-700 mb-1.5">Your Name</label>
                                <input
                                    type="text"
                                    id="contact_name"
                                    name="name"
                                    placeholder="Jane Smith"
                                    class="w-full border border-gray-200 rounded-xl px-4 py-3 text-gray-800 text-sm focus:outline-none focus:ring-2 focus:ring-purple-400 focus:border-transparent transition-shadow"
                                    required
                                />
                            </div>
                            <div>
                                <label for="contact_email" class="block text-sm font-semibold text-gray-700 mb-1.5">Email Address</label>
                                <input
                                    type="email"
                                    id="contact_email"
                                    name="email"
                                    placeholder="jane@example.com"
                                    class="w-full border border-gray-200 rounded-xl px-4 py-3 text-gray-800 text-sm focus:outline-none focus:ring-2 focus:ring-purple-400 focus:border-transparent transition-shadow"
                                    required
                                />
                            </div>
                        </div>

                        <div>
                            <label for="contact_subject" class="block text-sm font-semibold text-gray-700 mb-1.5">Subject</label>
                            <div class="relative">
                                <select
                                    id="contact_subject"
                                    name="subject"
                                    class="w-full appearance-none border border-gray-200 rounded-xl px-4 py-3 text-gray-800 text-sm focus:outline-none focus:ring-2 focus:ring-purple-400 focus:border-transparent bg-white transition-shadow pr-10"
                                    required
                                >
                                    <option value="" disabled selected>Select a topic…</option>
                                    <option value="general">General Question</option>
                                    <option value="billing">Billing & Subscription</option>
                                    <option value="technical">Technical Issue</option>
                                    <option value="schools">Schools & Teachers</option>
                                    <option value="other">Other</option>
                                </select>
                                <i data-lucide="chevron-down" class="w-4 h-4 text-gray-400 absolute right-3.5 top-1/2 -translate-y-1/2 pointer-events-none"></i>
                            </div>
                        </div>

                        <div>
                            <label for="contact_message" class="block text-sm font-semibold text-gray-700 mb-1.5">Message</label>
                            <textarea
                                id="contact_message"
                                name="message"
                                rows="6"
                                placeholder="Describe your question or issue in as much detail as you can — it helps us help you faster."
                                class="w-full border border-gray-200 rounded-xl px-4 py-3 text-gray-800 text-sm focus:outline-none focus:ring-2 focus:ring-purple-400 focus:border-transparent transition-shadow resize-none"
                                required
                            ></textarea>
                        </div>

                        <button
                            type="submit"
                            class="w-full bg-purple-600 hover:bg-purple-700 text-white font-semibold py-3.5 rounded-xl transition-colors shadow-lg hover:shadow-xl flex items-center justify-center gap-2 text-base"
                        >
                            <i data-lucide="send" class="w-5 h-5"></i>
                            Send Message
                        </button>

                        <p class="text-xs text-gray-400 text-center">By submitting this form, you agree to our <a href="/privacy" class="underline hover:text-gray-600">Privacy Policy</a>.</p>
                    </form>
                </div>
            </div>

            {{-- Contact Info (narrower) --}}
            <div class="lg:col-span-2 space-y-6 reveal">

                {{-- Response Info --}}
                <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm">
                    <h3 class="font-bold text-gray-900 mb-4 flex items-center gap-2">
                        <i data-lucide="clock" class="w-5 h-5 text-purple-600"></i>
                        Response Times
                    </h3>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">Typical response</span>
                            <span class="text-sm font-semibold text-gray-900">Under 24 hours</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">Billing issues</span>
                            <span class="text-sm font-semibold text-gray-900">Under 12 hours</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">School inquiries</span>
                            <span class="text-sm font-semibold text-gray-900">Same business day</span>
                        </div>
                    </div>
                    <div class="mt-5 pt-5 border-t border-gray-100">
                        <p class="text-xs text-gray-500 font-semibold uppercase tracking-wider mb-2">Business Hours</p>
                        <p class="text-sm text-gray-700">Monday – Friday</p>
                        <p class="text-sm text-gray-700">9:00 AM – 6:00 PM EST</p>
                    </div>
                </div>

                {{-- Direct Email --}}
                <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm">
                    <h3 class="font-bold text-gray-900 mb-4 flex items-center gap-2">
                        <i data-lucide="mail" class="w-5 h-5 text-purple-600"></i>
                        Direct Email
                    </h3>
                    <a href="mailto:support@harmoniva.app" class="text-purple-600 hover:text-purple-700 font-medium text-sm break-all">
                        support@harmoniva.app
                    </a>
                    <p class="text-gray-500 text-xs mt-2">For general inquiries, billing, and technical support.</p>
                </div>

                {{-- Address --}}
                <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm">
                    <h3 class="font-bold text-gray-900 mb-4 flex items-center gap-2">
                        <i data-lucide="map-pin" class="w-5 h-5 text-purple-600"></i>
                        Company Address
                    </h3>
                    <address class="not-italic text-sm text-gray-700 leading-relaxed">
                        <strong class="text-gray-900">Harmoniva — H&amp;P LLC</strong><br>
                        8 The Green STE B<br>
                        Dover, DE 19901<br>
                        United States
                    </address>
                    <div class="mt-4 bg-gray-50 rounded-xl p-3 flex items-center justify-center">
                        <div class="text-center">
                            <div class="w-10 h-10 rounded-full bg-purple-100 flex items-center justify-center mx-auto mb-2">
                                <i data-lucide="building-2" class="w-5 h-5 text-purple-600"></i>
                            </div>
                            <p class="text-xs text-gray-500">Registered in Delaware, USA</p>
                        </div>
                    </div>
                </div>

                {{-- Social / Community (bonus) --}}
                <div class="bg-gradient-to-br from-purple-50 to-orange-50 rounded-2xl p-6 border border-purple-100">
                    <h3 class="font-bold text-gray-900 mb-3">Not urgent?</h3>
                    <p class="text-gray-600 text-sm leading-relaxed">Check our <a href="/faq" class="text-purple-600 hover:underline font-medium">FAQ</a> or browse the <a href="/help" class="text-purple-600 hover:underline font-medium">Help Center</a> — you might find an instant answer without waiting for a reply.</p>
                </div>

            </div>

        </div>
    </div>
</section>

@endsection
