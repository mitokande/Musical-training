@extends('layouts.standalone')

@section('title', 'Privacy Policy — Harmoniva')
@section('description', 'Harmoniva\'s Privacy Policy. Learn how we collect, use, and protect your personal information when you use our music ear training platform.')

@section('content')

{{-- Hero --}}
<section class="py-16 bg-gray-900 text-white">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 class="text-4xl sm:text-5xl font-extrabold mb-3">Privacy Policy</h1>
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
                        <a href="#introduction" class="block text-sm text-gray-600 hover:text-purple-600 py-1 transition-colors">1. Introduction</a>
                        <a href="#information-we-collect" class="block text-sm text-gray-600 hover:text-purple-600 py-1 transition-colors">2. Information We Collect</a>
                        <a href="#how-we-use" class="block text-sm text-gray-600 hover:text-purple-600 py-1 transition-colors">3. How We Use Your Information</a>
                        <a href="#data-sharing" class="block text-sm text-gray-600 hover:text-purple-600 py-1 transition-colors">4. Data Sharing</a>
                        <a href="#data-retention" class="block text-sm text-gray-600 hover:text-purple-600 py-1 transition-colors">5. Data Retention</a>
                        <a href="#your-rights" class="block text-sm text-gray-600 hover:text-purple-600 py-1 transition-colors">6. Your Rights</a>
                        <a href="#cookies" class="block text-sm text-gray-600 hover:text-purple-600 py-1 transition-colors">7. Cookies</a>
                        <a href="#childrens-privacy-main" class="block text-sm text-gray-600 hover:text-purple-600 py-1 transition-colors">8. Children's Privacy</a>
                        <a href="#security" class="block text-sm text-gray-600 hover:text-purple-600 py-1 transition-colors">9. Security</a>
                        <a href="#contact-us" class="block text-sm text-gray-600 hover:text-purple-600 py-1 transition-colors">10. Contact Us</a>
                        <div class="border-t border-gray-200 my-3"></div>
                        <a href="#childrens-privacy" class="block text-sm text-purple-600 hover:text-purple-700 font-medium py-1 transition-colors">Children's Privacy Notice</a>
                    </nav>
                </div>
            </aside>

            {{-- Main Content --}}
            <main class="flex-1 min-w-0 text-gray-700 leading-relaxed">

                <section id="introduction" class="mb-10">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">1. Introduction</h2>
                    <p class="mb-4">Harmoniva ("we," "our," or "us") is operated by H&amp;P LLC, a Delaware limited liability company with offices at 8 The Green STE B, Dover, DE 19901, United States. This Privacy Policy describes how we collect, use, disclose, and protect information about you when you use our website at <strong>harmoniva.app</strong> and any related services (collectively, the "Service").</p>
                    <p class="mb-4">By using the Service, you agree to the collection and use of information in accordance with this policy. If you do not agree to this policy, please do not use the Service.</p>
                    <p>This policy applies to all users of harmoniva.app, including free users, premium subscribers, teachers, and school administrators.</p>
                </section>

                <section id="information-we-collect" class="mb-10">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">2. Information We Collect</h2>

                    <h3 class="text-lg font-semibold text-gray-900 mb-2 mt-5">Account Data</h3>
                    <p class="mb-4">When you create an account, we collect your name, email address, and a password hash (passwords are never stored in plain text). If you register via Google OAuth, we receive your name, email address, and profile picture from Google. We also store your selected language/locale and account type (free, premium, teacher, school).</p>

                    <h3 class="text-lg font-semibold text-gray-900 mb-2 mt-5">Usage Data</h3>
                    <p class="mb-4">We collect information about how you interact with the Service, including: exercises completed, scores and performance metrics, practice session duration, learning path progress, features accessed, and browser/device information (browser type, operating system, IP address, referring URLs). This data helps us improve the platform and personalize your experience.</p>

                    <h3 class="text-lg font-semibold text-gray-900 mb-2 mt-5">Payment Information</h3>
                    <p class="mb-4">If you subscribe to a paid plan, payment is processed by Stripe, Inc. We do not store your full credit card number, CVV, or other sensitive payment details. We receive and store a payment token, your billing name, the last four digits of your card, and your subscription status from Stripe. Stripe's privacy policy governs how they handle your payment data.</p>

                    <h3 class="text-lg font-semibold text-gray-900 mb-2 mt-5">Cookies and Tracking Technologies</h3>
                    <p>We use cookies and similar technologies to maintain your session, remember your preferences, and analyze how the Service is used. See Section 7 for details, or review our full <a href="{{ route('page.cookie-policy') }}" class="text-purple-600 hover:text-purple-700 underline transition-colors">Cookie Policy</a>.</p>
                </section>

                <section id="how-we-use" class="mb-10">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">3. How We Use Your Information</h2>
                    <p class="mb-4">We use the information we collect to:</p>
                    <ul class="list-disc list-outside pl-5 space-y-2 mb-4">
                        <li><strong>Provide and maintain the Service</strong> — authenticate your account, process subscriptions, deliver exercises, and track your progress.</li>
                        <li><strong>Improve the product</strong> — analyze usage patterns, identify bugs, and develop new features. Activity logs are reviewed in aggregate and anonymized where possible.</li>
                        <li><strong>Personalize your experience</strong> — our AI-powered learning paths use your practice history to recommend exercises and adapt difficulty.</li>
                        <li><strong>Communicate with you</strong> — send transactional emails (account confirmation, password reset, subscription receipts), product updates, and, with your consent, promotional communications. You may opt out of marketing emails at any time.</li>
                        <li><strong>Analytics and reporting</strong> — generate aggregated, non-identifying statistics about platform usage.</li>
                        <li><strong>Legal and compliance</strong> — meet our legal obligations, enforce our terms, and protect the rights and safety of users and the public.</li>
                    </ul>
                    <p>We do not use your personal data to train third-party AI models or sell behavioral data to advertisers.</p>
                </section>

                <section id="data-sharing" class="mb-10">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">4. Data Sharing</h2>
                    <p class="mb-4"><strong>We do not sell your personal data.</strong> We share your information only in the following limited circumstances:</p>

                    <h3 class="text-lg font-semibold text-gray-900 mb-2 mt-5">Service Providers</h3>
                    <p class="mb-4">We share data with trusted third-party service providers who help us operate the Service, including:</p>
                    <ul class="list-disc list-outside pl-5 space-y-2 mb-4">
                        <li><strong>Stripe</strong> — payment processing. Your payment data is governed by Stripe's privacy policy.</li>
                        <li><strong>OpenAI</strong> — powers AI features such as personalized learning path generation. Exercise content and aggregated performance data may be sent to OpenAI's API. We do not send personally identifiable information to OpenAI unless you explicitly interact with an AI feature that requires it.</li>
                        <li><strong>Cloud infrastructure providers</strong> — hosting and database services under confidentiality agreements.</li>
                    </ul>

                    <h3 class="text-lg font-semibold text-gray-900 mb-2 mt-5">Legal Requirements</h3>
                    <p class="mb-4">We may disclose your information if required by law, court order, or government request, or to protect the rights, property, or safety of Harmoniva, our users, or the public.</p>

                    <h3 class="text-lg font-semibold text-gray-900 mb-2 mt-5">Business Transfers</h3>
                    <p>In the event of a merger, acquisition, or sale of all or a portion of our assets, your information may be transferred as part of that transaction. We will notify you via email and/or a prominent notice on the Service of any such change in ownership.</p>
                </section>

                <section id="data-retention" class="mb-10">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">5. Data Retention</h2>
                    <p class="mb-4">We retain your account data and practice history for as long as your account remains active. If you delete your account, we will delete or anonymize your personal data within 30 days of your deletion request, except where we are required to retain data for legal or legitimate business purposes (e.g., financial records for up to 7 years as required by law).</p>
                    <p>Aggregated, anonymized usage data that cannot identify you individually may be retained indefinitely to support product research and analytics.</p>
                </section>

                <section id="your-rights" class="mb-10">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">6. Your Rights</h2>
                    <p class="mb-4">Depending on your location, you may have the following rights regarding your personal data:</p>

                    <h3 class="text-lg font-semibold text-gray-900 mb-2 mt-5">All Users</h3>
                    <ul class="list-disc list-outside pl-5 space-y-2 mb-4">
                        <li><strong>Access</strong> — request a copy of the personal data we hold about you.</li>
                        <li><strong>Correction</strong> — request correction of inaccurate or incomplete data.</li>
                        <li><strong>Deletion</strong> — request deletion of your account and personal data.</li>
                        <li><strong>Opt-out</strong> — unsubscribe from marketing emails at any time via the unsubscribe link in any email or via your account settings.</li>
                    </ul>

                    <h3 class="text-lg font-semibold text-gray-900 mb-2 mt-5">California Residents (CCPA)</h3>
                    <p class="mb-4">California residents have the right to know what personal information we collect, to request deletion, and to opt out of the sale of personal information. We do not sell personal information. To exercise your rights, contact us at <a href="mailto:support@harmoniva.app" class="text-purple-600 hover:text-purple-700 underline transition-colors">support@harmoniva.app</a>.</p>

                    <h3 class="text-lg font-semibold text-gray-900 mb-2 mt-5">EU/EEA and UK Residents (GDPR)</h3>
                    <p class="mb-4">If you are located in the EU, EEA, or UK, you have additional rights including data portability, the right to restrict processing, and the right to lodge a complaint with your local data protection authority. Our legal basis for processing is typically contract performance (to provide the Service) and legitimate interests (product improvement, security).</p>

                    <p>To exercise any of these rights, contact us at <a href="mailto:support@harmoniva.app" class="text-purple-600 hover:text-purple-700 underline transition-colors">support@harmoniva.app</a>. We will respond within 30 days.</p>
                </section>

                <section id="cookies" class="mb-10">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">7. Cookies</h2>
                    <p class="mb-4">We use cookies and similar technologies to operate the Service. This includes session cookies (essential for keeping you logged in), preference cookies (to remember your language and settings), and analytics cookies (to understand how users interact with the platform).</p>
                    <p>For full details on the cookies we use, their purpose, and how to manage them, please review our <a href="{{ route('page.cookie-policy') }}" class="text-purple-600 hover:text-purple-700 underline transition-colors">Cookie Policy</a>.</p>
                </section>

                <section id="childrens-privacy-main" class="mb-10">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">8. Children's Privacy</h2>
                    <p class="mb-4">The Service is not directed to children under the age of 13. We do not knowingly collect personal information from children under 13 without verifiable parental consent. If we become aware that we have collected personal data from a child under 13 without proper consent, we will take steps to delete that information promptly.</p>
                    <p class="mb-4">For users between 13 and 17 years of age, we encourage parental involvement and oversight. In educational contexts (school accounts), we collect only the minimum data necessary and do not display advertising.</p>
                    <p>For our complete Children's Privacy Notice, see the <a href="#childrens-privacy" class="text-purple-600 hover:text-purple-700 underline transition-colors">dedicated section below</a>.</p>
                </section>

                <section id="security" class="mb-10">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">9. Security</h2>
                    <p class="mb-4">We take the security of your data seriously. We implement industry-standard measures including:</p>
                    <ul class="list-disc list-outside pl-5 space-y-2 mb-4">
                        <li>TLS/HTTPS encryption for all data in transit</li>
                        <li>Password hashing using bcrypt</li>
                        <li>Encrypted database storage</li>
                        <li>Access controls limiting employee access to personal data on a need-to-know basis</li>
                        <li>Regular security reviews and dependency updates</li>
                    </ul>
                    <p>In the event of a data breach that poses a risk to your rights and freedoms, we will notify you and applicable authorities as required by law, within the timeframes specified by applicable regulations (72 hours for GDPR, without undue delay for CCPA). No method of transmission over the internet is 100% secure, and we cannot guarantee absolute security.</p>
                </section>

                <section id="contact-us" class="mb-10">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">10. Contact Us</h2>
                    <p class="mb-4">If you have questions, concerns, or requests regarding this Privacy Policy or your personal data, please contact us:</p>
                    <div class="bg-[#FAF7F2] rounded-xl p-5 border border-gray-100">
                        <p class="font-semibold text-gray-900 mb-1">H&amp;P LLC — Harmoniva</p>
                        <p class="text-gray-700">8 The Green STE B<br>Dover, DE 19901<br>United States</p>
                        <p class="mt-3"><a href="mailto:support@harmoniva.app" class="text-purple-600 font-medium hover:text-purple-700 transition-colors">support@harmoniva.app</a></p>
                    </div>
                    <p class="mt-4 text-sm text-gray-500">We may update this Privacy Policy from time to time. We will notify you of significant changes by email or by posting a notice on the Service. Your continued use of the Service after any change constitutes your acceptance of the updated policy.</p>
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
            <div class="flex items-center gap-2 px-4 py-2 rounded-full bg-purple-100 text-purple-700 text-sm font-semibold">
                <i data-lucide="shield" class="w-4 h-4"></i>
                Children's Privacy Notice
            </div>
            <div class="flex-1 h-px bg-gray-300"></div>
        </div>
    </div>
</div>

{{-- Children's Privacy Notice --}}
<section id="childrens-privacy" class="py-16 bg-[#FAF7F2]">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex gap-12 items-start">

            {{-- Sidebar --}}
            <aside class="hidden lg:block w-64 flex-shrink-0 sticky top-24">
                <div class="bg-white rounded-2xl p-5 border border-gray-100">
                    <h2 class="text-xs font-bold text-gray-500 uppercase tracking-widest mb-4">Children's Privacy</h2>
                    <nav class="space-y-1">
                        <a href="#coppa-compliance" class="block text-sm text-gray-600 hover:text-purple-600 py-1 transition-colors">COPPA Compliance</a>
                        <a href="#data-from-minors" class="block text-sm text-gray-600 hover:text-purple-600 py-1 transition-colors">Data We Collect from Minors</a>
                        <a href="#parental-rights" class="block text-sm text-gray-600 hover:text-purple-600 py-1 transition-colors">Parental Rights</a>
                        <a href="#deletion-requests" class="block text-sm text-gray-600 hover:text-purple-600 py-1 transition-colors">Requesting Data Deletion</a>
                        <a href="#parent-contact" class="block text-sm text-gray-600 hover:text-purple-600 py-1 transition-colors">Contact for Parents</a>
                    </nav>
                </div>
            </aside>

            {{-- Main Content --}}
            <main class="flex-1 min-w-0 text-gray-700 leading-relaxed">
                <div class="mb-8">
                    <h2 class="text-3xl font-extrabold text-gray-900 mb-2">Children's Privacy Notice</h2>
                    <p class="text-gray-500">This notice supplements our main Privacy Policy and applies specifically to users under 18 years of age.</p>
                </div>

                <section id="coppa-compliance" class="mb-10">
                    <h3 class="text-xl font-bold text-gray-900 mb-3">COPPA Compliance</h3>
                    <p class="mb-4">Harmoniva complies with the Children's Online Privacy Protection Act (COPPA), 15 U.S.C. §§ 6501–6506 and the FTC's COPPA Rule, 16 C.F.R. Part 312. We do not knowingly collect personal information from children under the age of 13 without verifiable parental consent.</p>
                    <p class="mb-4">Our general Service is not directed to children under 13. If you are a school or institution that wishes to use Harmoniva with students under 13, please contact us at <a href="mailto:support@harmoniva.app" class="text-purple-600 hover:text-purple-700 underline transition-colors">support@harmoniva.app</a> to arrange a COPPA-compliant school agreement, which includes obtaining parental consent on behalf of the school under the school official exception.</p>
                    <p>If we discover that we have collected personal information from a child under 13 without appropriate consent, we will delete that information immediately. If you believe we may have such information about your child, please contact us immediately.</p>
                </section>

                <section id="data-from-minors" class="mb-10">
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Data We Collect from Minor Users</h3>
                    <p class="mb-4">For users aged 13–17 (or users of any age on institutional/school accounts), we collect only the minimum data necessary to provide the Service:</p>
                    <ul class="list-disc list-outside pl-5 space-y-2 mb-4">
                        <li><strong>Account data</strong>: email address, display name, and hashed password (or Google OAuth token).</li>
                        <li><strong>Practice data</strong>: exercise scores, session history, and learning path progress. This data is used to personalize the learning experience.</li>
                        <li><strong>Technical data</strong>: IP address (used for security and fraud prevention, not stored long-term), browser type, and session cookies.</li>
                    </ul>
                    <p class="mb-4"><strong>We do not collect</strong>: precise geolocation, biometric data, social network data, or any data beyond what is strictly necessary for the educational service.</p>
                    <p><strong>We do not display behavioral advertising</strong> to users under 18, and we do not share minor users' data with third-party advertisers. AI features for minor users process exercise performance data only; personal identifiers are not sent to AI providers.</p>
                </section>

                <section id="parental-rights" class="mb-10">
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Parental Rights</h3>
                    <p class="mb-4">Parents or guardians of minor users have the following rights regarding their child's data:</p>
                    <ul class="list-disc list-outside pl-5 space-y-2 mb-4">
                        <li><strong>Review</strong>: Request a copy of the personal information we have collected from your child.</li>
                        <li><strong>Correction</strong>: Request that we correct inaccurate information.</li>
                        <li><strong>Deletion</strong>: Request that we delete your child's personal information and account.</li>
                        <li><strong>Consent withdrawal</strong>: Withdraw your consent for us to collect information from your child. Note that this will result in the child's account being closed.</li>
                        <li><strong>Opt-out of data sharing</strong>: Request that we not share your child's data with third-party service providers beyond those essential to operating the Service.</li>
                    </ul>
                    <p>To exercise any of these rights, please contact us using the information in the "Contact for Parents" section below. We will verify your identity as the parent or guardian before processing any request.</p>
                </section>

                <section id="deletion-requests" class="mb-10">
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Requesting Deletion of Your Child's Data</h3>
                    <p class="mb-4">To request deletion of your child's account and personal data:</p>
                    <ol class="list-decimal list-outside pl-5 space-y-2 mb-4">
                        <li>Email us at <a href="mailto:support@harmoniva.app" class="text-purple-600 hover:text-purple-700 underline transition-colors">support@harmoniva.app</a> with the subject line: <strong>"Child Data Deletion Request."</strong></li>
                        <li>Include your name, your relationship to the child, the child's account email address or username, and a brief description of your request.</li>
                        <li>We may ask you to verify your identity as the parent or guardian (e.g., by confirming information only a parent would know, or by providing documentation).</li>
                        <li>Upon verification, we will delete the child's account and associated personal data within 30 days and send a confirmation to your email address.</li>
                    </ol>
                    <p class="text-sm text-gray-500">Please note: some data may be retained in anonymized/aggregated form or as required by law (e.g., records of transactions). We will clearly indicate what, if anything, cannot be deleted and why.</p>
                </section>

                <section id="parent-contact" class="mb-4">
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Contact for Parents and Guardians</h3>
                    <p class="mb-4">If you have questions or concerns about your child's privacy, or to exercise parental rights, please contact us:</p>
                    <div class="bg-white rounded-xl p-5 border border-gray-100">
                        <p class="font-semibold text-gray-900 mb-1">H&amp;P LLC — Harmoniva Privacy Team</p>
                        <p class="text-gray-700">8 The Green STE B<br>Dover, DE 19901<br>United States</p>
                        <p class="mt-3">
                            Email: <a href="mailto:support@harmoniva.app" class="text-purple-600 font-medium hover:text-purple-700 transition-colors">support@harmoniva.app</a><br>
                            <span class="text-sm text-gray-500">Subject: Child Privacy Inquiry</span>
                        </p>
                    </div>
                    <p class="mt-4 text-sm text-gray-500">We are committed to responding to all parent and guardian inquiries within 5 business days.</p>
                </section>
            </main>
        </div>
    </div>
</section>

@endsection
