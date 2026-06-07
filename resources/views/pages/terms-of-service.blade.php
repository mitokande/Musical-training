@extends('layouts.standalone')

@section('title', 'Terms of Service — Harmoniva')
@section('description', 'Harmoniva Terms of Service. Please read these terms carefully before using our AI-powered ear training platform.')

@section('content')

{{-- Hero --}}
<section class="py-16 bg-gray-900 text-white">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 class="text-4xl sm:text-5xl font-extrabold mb-3">Terms of Service</h1>
        <p class="text-gray-400 text-lg">Last updated: June 1, 2026</p>
    </div>
</section>

{{-- Content --}}
<section class="py-16 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex gap-12 items-start">

            {{-- Sticky Sidebar TOC --}}
            <aside class="hidden lg:block w-64 flex-shrink-0 sticky top-24">
                <div class="bg-[#FAF7F2] rounded-2xl p-5 border border-gray-100">
                    <h2 class="text-xs font-bold text-gray-500 uppercase tracking-widest mb-4">Table of Contents</h2>
                    <nav class="space-y-1">
                        <a href="#acceptance" class="block text-sm text-gray-600 hover:text-purple-600 py-1 transition-colors">1. Acceptance of Terms</a>
                        <a href="#description" class="block text-sm text-gray-600 hover:text-purple-600 py-1 transition-colors">2. Description of Service</a>
                        <a href="#account-registration" class="block text-sm text-gray-600 hover:text-purple-600 py-1 transition-colors">3. Account Registration &amp; Security</a>
                        <a href="#billing" class="block text-sm text-gray-600 hover:text-purple-600 py-1 transition-colors">4. Subscriptions &amp; Billing</a>
                        <a href="#free-plan" class="block text-sm text-gray-600 hover:text-purple-600 py-1 transition-colors">5. Free Plan Limitations</a>
                        <a href="#prohibited-uses" class="block text-sm text-gray-600 hover:text-purple-600 py-1 transition-colors">6. Prohibited Uses</a>
                        <a href="#intellectual-property" class="block text-sm text-gray-600 hover:text-purple-600 py-1 transition-colors">7. Intellectual Property</a>
                        <a href="#user-content" class="block text-sm text-gray-600 hover:text-purple-600 py-1 transition-colors">8. User Content</a>
                        <a href="#disclaimers" class="block text-sm text-gray-600 hover:text-purple-600 py-1 transition-colors">9. Disclaimers &amp; Liability</a>
                        <a href="#termination" class="block text-sm text-gray-600 hover:text-purple-600 py-1 transition-colors">10. Termination</a>
                        <a href="#governing-law" class="block text-sm text-gray-600 hover:text-purple-600 py-1 transition-colors">11. Governing Law</a>
                        <a href="#tos-contact" class="block text-sm text-gray-600 hover:text-purple-600 py-1 transition-colors">12. Contact</a>
                        <div class="border-t border-gray-200 my-3"></div>
                        <a href="#accessibility" class="block text-sm text-purple-600 hover:text-purple-700 font-medium py-1 transition-colors">Accessibility Statement</a>
                    </nav>
                </div>
            </aside>

            {{-- Main Content --}}
            <main class="flex-1 min-w-0 text-gray-700 leading-relaxed">

                <section id="acceptance" class="mb-10">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">1. Acceptance of Terms</h2>
                    <p class="mb-4">These Terms of Service ("Terms") constitute a legally binding agreement between you ("you" or "User") and H&amp;P LLC ("Harmoniva," "we," "our," or "us") governing your access to and use of the Harmoniva platform at <strong>harmoniva.app</strong> and all associated services (collectively, the "Service").</p>
                    <p class="mb-4">By creating an account, clicking "I agree," or otherwise accessing or using the Service, you acknowledge that you have read, understood, and agree to be bound by these Terms and our <a href="{{ route('page.privacy-policy') }}" class="text-purple-600 hover:text-purple-700 underline transition-colors">Privacy Policy</a>, which is incorporated herein by reference.</p>
                    <p>If you do not agree to these Terms, you must not access or use the Service. If you are using the Service on behalf of an organization, you represent and warrant that you have authority to bind that organization to these Terms.</p>
                </section>

                <section id="description" class="mb-10">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">2. Description of Service</h2>
                    <p class="mb-4">Harmoniva is an AI-powered ear training platform for musicians, students, music teachers, and music schools. The Service includes interactive ear training exercises (intervals, chords, scales, rhythms, melodic dictation, and more), AI-generated learning paths, teacher tools, school management features, and related educational content.</p>
                    <p class="mb-4">The Service is available via web browser at harmoniva.app. We reserve the right to modify, suspend, or discontinue any part of the Service at any time. We will provide reasonable notice of material changes where practicable.</p>
                    <p>The Service is intended for users aged 13 and older. Use by children under 13 requires a COPPA-compliant school agreement as described in our <a href="{{ route('page.privacy-policy') }}#childrens-privacy" class="text-purple-600 hover:text-purple-700 underline transition-colors">Children's Privacy Notice</a>.</p>
                </section>

                <section id="account-registration" class="mb-10">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">3. Account Registration &amp; Security</h2>
                    <p class="mb-4">To access most features, you must register for an account. You agree to provide accurate, current, and complete information during registration and to keep your account information updated.</p>
                    <p class="mb-4">You are responsible for maintaining the confidentiality of your account credentials and for all activities that occur under your account. You must immediately notify us at <a href="mailto:support@harmoniva.app" class="text-purple-600 hover:text-purple-700 underline transition-colors">support@harmoniva.app</a> of any unauthorized use of your account or any other security breach.</p>
                    <p class="mb-4">You may not share your account credentials with others, create accounts using automated means, or create accounts under false pretenses. We reserve the right to refuse registration or cancel accounts at our discretion.</p>
                    <p>One account per person. Teachers and school administrators may manage multiple student accounts under their institutional license with appropriate permissions.</p>
                </section>

                <section id="billing" class="mb-10">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">4. Subscription Plans &amp; Billing</h2>
                    <p class="mb-4">Harmoniva offers free and paid subscription plans. Paid plans ("Premium") are available on monthly or annual billing cycles. Prices are displayed on our <a href="{{ route('pricing.index') }}" class="text-purple-600 hover:text-purple-700 underline transition-colors">Pricing page</a> and are denominated in US dollars.</p>
                    <p class="mb-4">Payments are processed securely by Stripe, Inc. By subscribing, you authorize us (via Stripe) to charge your payment method on a recurring basis for the selected plan until you cancel. Subscriptions automatically renew at the end of each billing period unless cancelled prior to renewal.</p>
                    <p class="mb-4">We reserve the right to change subscription prices with at least 30 days' notice. If you do not cancel before the price change takes effect, your continued use of the Service will constitute acceptance of the new price.</p>
                    <p>For full billing terms, including cancellation, refunds, and trial periods, see our <a href="{{ route('page.subscription-terms') }}" class="text-purple-600 hover:text-purple-700 underline transition-colors">Subscription Terms</a> and <a href="{{ route('page.refund-policy') }}" class="text-purple-600 hover:text-purple-700 underline transition-colors">Refund Policy</a>.</p>
                </section>

                <section id="free-plan" class="mb-10">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">5. Free Plan Limitations</h2>
                    <p class="mb-4">Free plan users may access a limited number of exercises per day (currently 3 exercises per type per day), a limited number of saved practice templates (currently 3), and standard exercise types. Free plan users do not have access to AI-powered learning paths, advanced exercise configurations, or teacher/school features.</p>
                    <p class="mb-4">We reserve the right to modify free plan limits at any time with reasonable notice. The free plan is provided as-is for personal, non-commercial use only.</p>
                    <p>Free plan users are subject to all provisions of these Terms except those that apply exclusively to paid subscribers.</p>
                </section>

                <section id="prohibited-uses" class="mb-10">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">6. Prohibited Uses</h2>
                    <p class="mb-4">You agree not to use the Service to:</p>
                    <ul class="list-disc list-outside pl-5 space-y-2 mb-4">
                        <li>Violate any applicable law, regulation, or third-party rights</li>
                        <li>Scrape, crawl, or systematically extract content from the Service without express written permission</li>
                        <li>Reverse engineer, decompile, or attempt to derive source code from the Service</li>
                        <li>Attempt to gain unauthorized access to any part of the Service, its infrastructure, or other users' accounts</li>
                        <li>Transmit malware, viruses, or any other malicious code</li>
                        <li>Use the Service to compete with us by building a substantially similar product using our content or platform</li>
                        <li>Circumvent or attempt to circumvent any usage limits, subscription requirements, or access controls</li>
                        <li>Share, sublicense, or resell your account access to third parties</li>
                        <li>Harass, abuse, or harm other users</li>
                        <li>Use automated tools to interact with the Service in a way that impairs performance for other users</li>
                    </ul>
                    <p>Violation of these provisions may result in immediate account suspension or termination without refund.</p>
                </section>

                <section id="intellectual-property" class="mb-10">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">7. Intellectual Property</h2>
                    <p class="mb-4">All content, features, and functionality of the Service — including but not limited to the software, exercises, learning paths, audio samples, UI design, graphics, and text — are the exclusive property of H&amp;P LLC or its licensors and are protected by copyright, trademark, patent, and other intellectual property laws.</p>
                    <p class="mb-4">Subject to these Terms, we grant you a limited, non-exclusive, non-transferable, revocable license to access and use the Service for your personal, non-commercial educational purposes.</p>
                    <p>You may not copy, modify, distribute, sell, or create derivative works based on the Service or any content within it without our express written consent. The Harmoniva name, logo, and associated marks are trademarks of H&amp;P LLC and may not be used without permission.</p>
                </section>

                <section id="user-content" class="mb-10">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">8. User Content</h2>
                    <p class="mb-4">The Service currently does not have public user-generated content features. Practice data, exercise results, and saved configurations you create are yours and are subject to our Privacy Policy.</p>
                    <p class="mb-4">If you provide feedback, suggestions, or feature requests to us, you grant us a royalty-free, perpetual, irrevocable license to use that feedback to improve the Service without any obligation to you.</p>
                    <p>We do not claim ownership of your practice data. You may export or request a copy of your data at any time by contacting us at <a href="mailto:support@harmoniva.app" class="text-purple-600 hover:text-purple-700 underline transition-colors">support@harmoniva.app</a>.</p>
                </section>

                <section id="disclaimers" class="mb-10">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">9. Disclaimers &amp; Limitation of Liability</h2>

                    <h3 class="text-lg font-semibold text-gray-900 mb-2 mt-4">Disclaimer of Warranties</h3>
                    <p class="mb-4">THE SERVICE IS PROVIDED "AS IS" AND "AS AVAILABLE" WITHOUT WARRANTIES OF ANY KIND, EXPRESS OR IMPLIED. TO THE FULLEST EXTENT PERMITTED BY LAW, WE DISCLAIM ALL WARRANTIES INCLUDING, WITHOUT LIMITATION, IMPLIED WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE, AND NON-INFRINGEMENT. WE DO NOT WARRANT THAT THE SERVICE WILL BE UNINTERRUPTED, ERROR-FREE, OR FREE OF VIRUSES OR OTHER HARMFUL COMPONENTS.</p>

                    <h3 class="text-lg font-semibold text-gray-900 mb-2 mt-4">Limitation of Liability</h3>
                    <p class="mb-4">TO THE MAXIMUM EXTENT PERMITTED BY APPLICABLE LAW, H&amp;P LLC, ITS OFFICERS, DIRECTORS, EMPLOYEES, AGENTS, AND LICENSORS SHALL NOT BE LIABLE FOR ANY INDIRECT, INCIDENTAL, SPECIAL, CONSEQUENTIAL, OR PUNITIVE DAMAGES, INCLUDING BUT NOT LIMITED TO LOSS OF DATA, LOSS OF REVENUE, OR LOSS OF OPPORTUNITY, ARISING OUT OF OR RELATED TO YOUR USE OF THE SERVICE, EVEN IF WE HAVE BEEN ADVISED OF THE POSSIBILITY OF SUCH DAMAGES.</p>
                    <p>OUR TOTAL LIABILITY TO YOU FOR ALL CLAIMS ARISING FROM OR RELATED TO THESE TERMS OR THE SERVICE SHALL NOT EXCEED THE AMOUNT YOU PAID TO US IN THE TWELVE (12) MONTHS PRECEDING THE EVENT GIVING RISE TO THE CLAIM, OR $50 USD IF YOU ARE A FREE PLAN USER.</p>
                </section>

                <section id="termination" class="mb-10">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">10. Termination</h2>
                    <p class="mb-4">You may terminate your account at any time by visiting your account settings or by contacting us at <a href="mailto:support@harmoniva.app" class="text-purple-600 hover:text-purple-700 underline transition-colors">support@harmoniva.app</a>. Termination does not entitle you to a refund except as provided in our Refund Policy.</p>
                    <p class="mb-4">We may suspend or terminate your account and access to the Service immediately, without prior notice or liability, if we determine in good faith that you have violated these Terms or engaged in conduct harmful to other users, us, or third parties.</p>
                    <p>Upon termination, your right to use the Service ceases immediately. Provisions of these Terms that by their nature should survive termination shall survive, including ownership provisions, warranty disclaimers, indemnity, and limitations of liability.</p>
                </section>

                <section id="governing-law" class="mb-10">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">11. Governing Law</h2>
                    <p class="mb-4">These Terms and any disputes arising from or related to them or the Service shall be governed by and construed in accordance with the laws of the State of Delaware, United States, without regard to its conflict of law provisions.</p>
                    <p class="mb-4">Any legal action or proceeding relating to these Terms shall be brought exclusively in the state or federal courts located in New Castle County, Delaware. You consent to personal jurisdiction in those courts.</p>
                    <p>Notwithstanding the above, either party may seek injunctive or other equitable relief in any court of competent jurisdiction to protect intellectual property or confidential information.</p>
                </section>

                <section id="tos-contact" class="mb-4">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">12. Contact</h2>
                    <p class="mb-4">If you have questions about these Terms of Service, please contact us:</p>
                    <div class="bg-[#FAF7F2] rounded-xl p-5 border border-gray-100">
                        <p class="font-semibold text-gray-900 mb-1">H&amp;P LLC — Harmoniva</p>
                        <p class="text-gray-700">8 The Green STE B<br>Dover, DE 19901<br>United States</p>
                        <p class="mt-3"><a href="mailto:support@harmoniva.app" class="text-purple-600 font-medium hover:text-purple-700 transition-colors">support@harmoniva.app</a></p>
                    </div>
                    <p class="mt-4 text-sm text-gray-500">We may update these Terms from time to time. We will notify you of material changes by email or via a notice on the Service. Your continued use of the Service after the effective date of any update constitutes your acceptance of the revised Terms.</p>
                </section>

            </main>
        </div>
    </div>
</section>

{{-- Visual Divider --}}
<div class="bg-[#FAF7F2] py-4">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center gap-4">
            <div class="flex-1 h-px bg-gray-300"></div>
            <div class="flex items-center gap-2 px-4 py-2 rounded-full bg-blue-100 text-blue-700 text-sm font-semibold">
                <i data-lucide="accessibility" class="w-4 h-4"></i>
                Accessibility Statement
            </div>
            <div class="flex-1 h-px bg-gray-300"></div>
        </div>
    </div>
</div>

{{-- Accessibility Statement --}}
<section id="accessibility" class="py-16 bg-[#FAF7F2]">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex gap-12 items-start">

            {{-- Sidebar --}}
            <aside class="hidden lg:block w-64 flex-shrink-0 sticky top-24">
                <div class="bg-white rounded-2xl p-5 border border-gray-100">
                    <h2 class="text-xs font-bold text-gray-500 uppercase tracking-widest mb-4">Accessibility</h2>
                    <nav class="space-y-1">
                        <a href="#a11y-commitment" class="block text-sm text-gray-600 hover:text-purple-600 py-1 transition-colors">Our Commitment</a>
                        <a href="#a11y-measures" class="block text-sm text-gray-600 hover:text-purple-600 py-1 transition-colors">What We've Done</a>
                        <a href="#a11y-reporting" class="block text-sm text-gray-600 hover:text-purple-600 py-1 transition-colors">Reporting Issues</a>
                        <a href="#a11y-ongoing" class="block text-sm text-gray-600 hover:text-purple-600 py-1 transition-colors">Ongoing Commitment</a>
                    </nav>
                </div>
            </aside>

            {{-- Main Content --}}
            <main class="flex-1 min-w-0 text-gray-700 leading-relaxed">
                <div class="mb-8">
                    <h2 class="text-3xl font-extrabold text-gray-900 mb-2">Accessibility Statement</h2>
                    <p class="text-gray-500">Harmoniva is committed to being accessible to all musicians, regardless of ability or disability.</p>
                </div>

                <section id="a11y-commitment" class="mb-10">
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Our Commitment</h3>
                    <p class="mb-4">Harmoniva believes that music education should be accessible to everyone. We are committed to ensuring that our platform conforms to the Web Content Accessibility Guidelines (WCAG) 2.1 at Level AA. These guidelines explain how to make web content more accessible to people with disabilities, and conforming to those guidelines helps us ensure that the Service is accessible to all users.</p>
                    <p>Accessibility is an ongoing priority, not a checkbox. We review our platform regularly, incorporate user feedback, and continuously work to improve the experience for users with visual, auditory, motor, and cognitive disabilities.</p>
                </section>

                <section id="a11y-measures" class="mb-10">
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Accessibility Features We've Implemented</h3>
                    <ul class="list-disc list-outside pl-5 space-y-2 mb-4">
                        <li><strong>Keyboard navigation</strong>: All core features and exercises are operable via keyboard without requiring a mouse. Focus indicators are clearly visible throughout the interface.</li>
                        <li><strong>Screen reader support</strong>: We use semantic HTML, ARIA labels, and live regions to ensure compatibility with popular screen readers (NVDA, JAWS, VoiceOver, TalkBack). Exercise results and feedback are announced to screen reader users.</li>
                        <li><strong>Color contrast</strong>: All text and interactive elements meet or exceed WCAG 2.1 AA contrast ratio requirements (minimum 4.5:1 for normal text, 3:1 for large text and UI components).</li>
                        <li><strong>Text resizing</strong>: The layout reflows correctly when browser text is scaled up to 200% without loss of functionality.</li>
                        <li><strong>Descriptive link text</strong>: Links include descriptive text that conveys the destination or purpose, rather than generic phrases like "click here."</li>
                        <li><strong>Alternative text</strong>: Images and icons that convey meaning include appropriate alt text or ARIA labels.</li>
                        <li><strong>No auto-playing audio</strong>: Audio exercises only play when the user explicitly triggers playback. No content plays automatically in a way that cannot be paused or stopped.</li>
                        <li><strong>Responsive design</strong>: The Service is fully functional on mobile devices and adapts to different screen sizes and orientations.</li>
                    </ul>
                </section>

                <section id="a11y-reporting" class="mb-10">
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Reporting Accessibility Issues</h3>
                    <p class="mb-4">If you experience an accessibility barrier on Harmoniva that prevents you from completing an exercise or using a feature, please let us know. We take all accessibility reports seriously and aim to resolve issues promptly.</p>
                    <p class="mb-4">To report an accessibility issue:</p>
                    <ul class="list-disc list-outside pl-5 space-y-2 mb-4">
                        <li>Email us at <a href="mailto:support@harmoniva.app" class="text-purple-600 hover:text-purple-700 underline transition-colors">support@harmoniva.app</a> with the subject line: <strong>"Accessibility Issue."</strong></li>
                        <li>Describe the barrier you encountered, the page or feature involved, and the assistive technology or browser you are using.</li>
                        <li>We will acknowledge your report within 2 business days and provide an estimated resolution timeline.</li>
                    </ul>
                    <div class="bg-white rounded-xl p-5 border border-gray-100">
                        <p class="font-semibold text-gray-900 mb-1">Accessibility Contact</p>
                        <p class="text-gray-700"><a href="mailto:support@harmoniva.app" class="text-purple-600 font-medium hover:text-purple-700 transition-colors">support@harmoniva.app</a></p>
                        <p class="text-sm text-gray-500 mt-1">Subject: Accessibility Issue</p>
                    </div>
                </section>

                <section id="a11y-ongoing" class="mb-4">
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Ongoing Commitment to Improvement</h3>
                    <p class="mb-4">We recognize that accessibility is a continuous journey. Our ongoing commitments include:</p>
                    <ul class="list-disc list-outside pl-5 space-y-2">
                        <li>Conducting regular accessibility audits of new and existing features</li>
                        <li>Including accessibility testing in our development workflow before releasing new features</li>
                        <li>Training our team on accessibility best practices and WCAG guidelines</li>
                        <li>Soliciting feedback from users with disabilities to guide our improvements</li>
                        <li>Working toward WCAG 2.2 AA compliance as our standard evolves</li>
                    </ul>
                    <p class="mt-4 text-sm text-gray-500">This accessibility statement was last reviewed on June 1, 2026.</p>
                </section>
            </main>
        </div>
    </div>
</section>

@endsection
