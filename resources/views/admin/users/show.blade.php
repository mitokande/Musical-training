@extends('admin.layouts.admin')

@section('page-title', 'Member Details')

@section('content')
<div class="space-y-6" x-data="{ activeTab: 'overview' }">
    <!-- Back Link -->
    <a href="{{ route('admin.users.index') }}" class="inline-flex items-center text-gray-500 hover:text-purple-600 transition-colors text-sm">
        <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i>
        Back to Members
    </a>

    <!-- Header Card -->
    <div class="card p-6">
        <div class="flex flex-col sm:flex-row items-start gap-6">
            <!-- Avatar -->
            <div class="shrink-0">
                @if($user->avatar)
                    <img src="{{ $user->avatar }}" alt="" class="w-20 h-20 rounded-2xl object-cover">
                @else
                    <div class="w-20 h-20 rounded-2xl bg-gradient-to-br from-purple-500 to-pink-500 flex items-center justify-center text-white font-bold text-2xl">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </div>
                @endif
            </div>

            <!-- Info -->
            <div class="flex-1 min-w-0">
                <div class="flex flex-wrap items-center gap-3 mb-2">
                    <h2 class="text-2xl font-bold text-gray-900">{{ $user->name }}</h2>
                    @switch($user->role)
                        @case('admin')
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-purple-100 text-purple-700 text-xs font-medium">
                                <i data-lucide="shield" class="w-3 h-3"></i> Admin
                            </span>
                            @break
                        @case('teacher')
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-green-100 text-green-700 text-xs font-medium">
                                <i data-lucide="briefcase" class="w-3 h-3"></i> Teacher
                            </span>
                            @break
                        @case('school')
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-amber-100 text-amber-700 text-xs font-medium">
                                <i data-lucide="building" class="w-3 h-3"></i> School
                            </span>
                            @break
                        @default
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-blue-100 text-blue-700 text-xs font-medium">
                                <i data-lucide="graduation-cap" class="w-3 h-3"></i> Student
                            </span>
                    @endswitch
                    @if($user->plan === 'premium')
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-orange-100 text-orange-700 text-xs font-medium">
                            <i data-lucide="crown" class="w-3 h-3"></i> Premium
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-gray-100 text-gray-600 text-xs font-medium">
                            Free
                        </span>
                    @endif
                </div>

                <p class="text-sm text-gray-500 mb-3">{{ $user->email }}</p>

                <div class="flex flex-wrap items-center gap-4 text-xs text-gray-500">
                    <span class="flex items-center gap-1">
                        <i data-lucide="calendar" class="w-3.5 h-3.5"></i>
                        Joined {{ $user->created_at->format('M d, Y') }}
                    </span>
                    <span class="flex items-center gap-1">
                        <i data-lucide="clock" class="w-3.5 h-3.5"></i>
                        Last active {{ $user->last_active_at ? $user->last_active_at->diffForHumans() : 'Never' }}
                    </span>
                </div>

                <!-- Profile Completeness -->
                @php
                    $fields = ['name', 'email', 'phone', 'country', 'city', 'date_of_birth', 'instrument', 'level'];
                    $filled = collect($fields)->filter(fn($f) => !empty($user->$f))->count();
                    $completeness = round(($filled / count($fields)) * 100);
                @endphp
                <div class="mt-4">
                    <div class="flex items-center justify-between text-xs text-gray-500 mb-1">
                        <span>Profile Completeness</span>
                        <span>{{ $completeness }}%</span>
                    </div>
                    <div class="w-full h-2 bg-gray-200 rounded-full overflow-hidden">
                        <div class="h-full rounded-full {{ $completeness >= 80 ? 'bg-green-500' : ($completeness >= 50 ? 'bg-orange-500' : 'bg-red-500') }}" style="width: {{ $completeness }}%"></div>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex items-center gap-2 shrink-0">
                <a href="{{ route('admin.users.edit', $user) }}" class="btn-primary inline-flex items-center gap-2 px-4 py-2 text-white text-sm font-medium rounded-lg transition-all hover:shadow-lg">
                    <i data-lucide="pencil" class="w-4 h-4"></i> Edit
                </a>
            </div>
        </div>
    </div>

    <!-- Tabs Navigation -->
    <div class="card overflow-hidden">
        <div class="border-b border-gray-200 overflow-x-auto">
            <nav class="flex -mb-px">
                <button @click="activeTab = 'overview'" :class="activeTab === 'overview' ? 'border-purple-600 text-purple-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'" class="px-6 py-3 text-sm font-medium border-b-2 transition-colors whitespace-nowrap">
                    <i data-lucide="user" class="w-4 h-4 inline mr-1"></i> Overview
                </button>
                <button @click="activeTab = 'subscription'" :class="activeTab === 'subscription' ? 'border-purple-600 text-purple-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'" class="px-6 py-3 text-sm font-medium border-b-2 transition-colors whitespace-nowrap">
                    <i data-lucide="credit-card" class="w-4 h-4 inline mr-1"></i> Subscription
                </button>
                <button @click="activeTab = 'exercises'" :class="activeTab === 'exercises' ? 'border-purple-600 text-purple-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'" class="px-6 py-3 text-sm font-medium border-b-2 transition-colors whitespace-nowrap">
                    <i data-lucide="headphones" class="w-4 h-4 inline mr-1"></i> Exercise History
                </button>
                <button @click="activeTab = 'ai'" :class="activeTab === 'ai' ? 'border-purple-600 text-purple-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'" class="px-6 py-3 text-sm font-medium border-b-2 transition-colors whitespace-nowrap">
                    <i data-lucide="brain" class="w-4 h-4 inline mr-1"></i> AI Data
                </button>
                <button @click="activeTab = 'messages'" :class="activeTab === 'messages' ? 'border-purple-600 text-purple-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'" class="px-6 py-3 text-sm font-medium border-b-2 transition-colors whitespace-nowrap">
                    <i data-lucide="message-square" class="w-4 h-4 inline mr-1"></i> Messages
                </button>
                <button @click="activeTab = 'crm'" :class="activeTab === 'crm' ? 'border-purple-600 text-purple-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'" class="px-6 py-3 text-sm font-medium border-b-2 transition-colors whitespace-nowrap">
                    <i data-lucide="notebook-pen" class="w-4 h-4 inline mr-1"></i> CRM Notes
                </button>
            </nav>
        </div>

        <div class="p-6">
            <!-- Overview Tab -->
            <div x-show="activeTab === 'overview'" x-cloak>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <!-- User Info -->
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wider mb-4">Contact Information</h3>
                        <dl class="space-y-3">
                            <div class="flex justify-between py-2 border-b border-gray-100">
                                <dt class="text-sm text-gray-500">Phone</dt>
                                <dd class="text-sm font-medium text-gray-900">{{ $user->phone ?? '-' }}</dd>
                            </div>
                            <div class="flex justify-between py-2 border-b border-gray-100">
                                <dt class="text-sm text-gray-500">Country</dt>
                                <dd class="text-sm font-medium text-gray-900">{{ $user->country ?? '-' }}</dd>
                            </div>
                            <div class="flex justify-between py-2 border-b border-gray-100">
                                <dt class="text-sm text-gray-500">City</dt>
                                <dd class="text-sm font-medium text-gray-900">{{ $user->city ?? '-' }}</dd>
                            </div>
                            <div class="flex justify-between py-2 border-b border-gray-100">
                                <dt class="text-sm text-gray-500">Date of Birth</dt>
                                <dd class="text-sm font-medium text-gray-900">{{ $user->date_of_birth ? \Carbon\Carbon::parse($user->date_of_birth)->format('M d, Y') : '-' }}</dd>
                            </div>
                        </dl>
                    </div>

                    <!-- Profile Info -->
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wider mb-4">Music Profile</h3>
                        <dl class="space-y-3">
                            <div class="flex justify-between py-2 border-b border-gray-100">
                                <dt class="text-sm text-gray-500">Instrument</dt>
                                <dd class="text-sm font-medium text-gray-900">{{ $user->instrument ?? '-' }}</dd>
                            </div>
                            <div class="flex justify-between py-2 border-b border-gray-100">
                                <dt class="text-sm text-gray-500">Level</dt>
                                <dd class="text-sm font-medium text-gray-900">{{ $user->level ?? '-' }}</dd>
                            </div>
                            <div class="flex justify-between py-2 border-b border-gray-100">
                                <dt class="text-sm text-gray-500">Education</dt>
                                <dd class="text-sm font-medium text-gray-900">{{ $user->education ?? '-' }}</dd>
                            </div>
                            <div class="flex justify-between py-2 border-b border-gray-100">
                                <dt class="text-sm text-gray-500">Username</dt>
                                <dd class="text-sm font-medium text-gray-900">{{ $user->username ?? '-' }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>
            </div>

            <!-- Subscription Tab -->
            <div x-show="activeTab === 'subscription'" x-cloak>
                <!-- Current Plan -->
                <div class="mb-6 p-4 rounded-xl {{ $user->plan === 'premium' ? 'bg-orange-50 border border-orange-200' : 'bg-gray-50 border border-gray-200' }}">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg {{ $user->plan === 'premium' ? 'bg-orange-100' : 'bg-gray-200' }} flex items-center justify-center">
                            <i data-lucide="{{ $user->plan === 'premium' ? 'crown' : 'user' }}" class="w-5 h-5 {{ $user->plan === 'premium' ? 'text-orange-600' : 'text-gray-500' }}"></i>
                        </div>
                        <div>
                            <p class="text-sm font-semibold {{ $user->plan === 'premium' ? 'text-orange-800' : 'text-gray-700' }}">Current Plan: {{ ucfirst($user->plan ?? 'free') }}</p>
                            <p class="text-xs {{ $user->plan === 'premium' ? 'text-orange-600' : 'text-gray-500' }}">Member since {{ $user->created_at->format('M d, Y') }}</p>
                        </div>
                    </div>
                </div>

                <!-- Subscription History -->
                <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wider mb-4">Subscription History</h3>
                @if($user->subscriptions && $user->subscriptions->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Plan</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Status</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Start Date</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">End Date</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Amount</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($user->subscriptions as $sub)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 font-medium text-gray-900">{{ $sub->plan?->name ?? $sub->plan_name ?? '-' }}</td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-medium {{ $sub->status === 'active' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                                        {{ ucfirst($sub->status) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-gray-500">{{ $sub->starts_at ? $sub->starts_at->format('M d, Y') : '-' }}</td>
                                <td class="px-4 py-3 text-gray-500">{{ $sub->ends_at ? $sub->ends_at->format('M d, Y') : '-' }}</td>
                                <td class="px-4 py-3 text-gray-900">{{ $sub->amount ? number_format($sub->amount, 2) : '-' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="text-center py-8">
                    <div class="w-12 h-12 rounded-full bg-gray-100 flex items-center justify-center mx-auto mb-3">
                        <i data-lucide="credit-card" class="w-6 h-6 text-gray-400"></i>
                    </div>
                    <p class="text-sm text-gray-500">No subscription history</p>
                </div>
                @endif
            </div>

            <!-- Exercise History Tab -->
            <div x-show="activeTab === 'exercises'" x-cloak>
                @if($user->userPractices && $user->userPractices->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Practice</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Questions</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Correct</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Score</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Date</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($user->userPractices as $practice)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 font-medium text-gray-900">{{ $practice->practice_name ?? $practice->practice_type ?? '-' }}</td>
                                <td class="px-4 py-3 text-gray-700">{{ $practice->total_questions ?? 0 }}</td>
                                <td class="px-4 py-3 text-gray-700">{{ $practice->correct_answers ?? 0 }}</td>
                                <td class="px-4 py-3">
                                    @php
                                        $score = ($practice->total_questions > 0) ? round(($practice->correct_answers / $practice->total_questions) * 100) : 0;
                                    @endphp
                                    <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-medium {{ $score >= 80 ? 'bg-green-100 text-green-700' : ($score >= 50 ? 'bg-orange-100 text-orange-700' : 'bg-red-100 text-red-700') }}">
                                        {{ $score }}%
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-gray-500">{{ $practice->created_at->format('M d, Y H:i') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="text-center py-8">
                    <div class="w-12 h-12 rounded-full bg-gray-100 flex items-center justify-center mx-auto mb-3">
                        <i data-lucide="headphones" class="w-6 h-6 text-gray-400"></i>
                    </div>
                    <p class="text-sm text-gray-500">No exercise history yet</p>
                </div>
                @endif
            </div>

            <!-- AI Data Tab -->
            <div x-show="activeTab === 'ai'" x-cloak>
                <!-- Questionnaire Responses -->
                <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wider mb-4">Questionnaire Responses</h3>
                @if($user->questionnaireResponses && $user->questionnaireResponses->count() > 0)
                <div class="space-y-3 mb-8">
                    @foreach($user->questionnaireResponses as $response)
                    <div class="p-4 bg-gray-50 rounded-xl">
                        <p class="text-sm font-medium text-gray-900 mb-1">{{ $response->question?->question ?? $response->question_text ?? 'Question' }}</p>
                        <p class="text-sm text-gray-600">{{ $response->answer }}</p>
                        <p class="text-xs text-gray-400 mt-1">{{ $response->created_at->format('M d, Y') }}</p>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="text-center py-6 mb-8">
                    <p class="text-sm text-gray-500">No questionnaire responses</p>
                </div>
                @endif

                <!-- AI Sessions -->
                <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wider mb-4">AI Coach Sessions</h3>
                @if($user->aiSessions && $user->aiSessions->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Session</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Messages</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Date</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($user->aiSessions as $session)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 font-medium text-gray-900">{{ $session->title ?? 'Session #'.$session->id }}</td>
                                <td class="px-4 py-3 text-gray-700">{{ $session->messages_count ?? $session->messages?->count() ?? 0 }}</td>
                                <td class="px-4 py-3 text-gray-500">{{ $session->created_at->format('M d, Y H:i') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="text-center py-6">
                    <p class="text-sm text-gray-500">No AI coach sessions</p>
                </div>
                @endif
            </div>

            <!-- Messages Tab -->
            <div x-show="activeTab === 'messages'" x-cloak>
                @php
                    $messages = $user->sentMessages?->merge($user->receivedMessages ?? collect())->sortByDesc('created_at') ?? collect();
                @endphp
                @if($messages->count() > 0)
                <div class="space-y-3">
                    @foreach($messages->take(50) as $message)
                    <div class="p-4 rounded-xl {{ $message->sender_id === $user->id ? 'bg-purple-50 border border-purple-100' : 'bg-gray-50 border border-gray-100' }}">
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center gap-2">
                                <span class="text-xs font-semibold {{ $message->sender_id === $user->id ? 'text-purple-600' : 'text-gray-600' }}">
                                    {{ $message->sender_id === $user->id ? 'Sent' : 'Received' }}
                                </span>
                                @if(!$message->read_at && $message->receiver_id === $user->id)
                                    <span class="inline-flex px-2 py-0.5 rounded-full bg-red-100 text-red-600 text-xs font-medium">Unread</span>
                                @endif
                            </div>
                            <span class="text-xs text-gray-400">{{ $message->created_at->format('M d, Y H:i') }}</span>
                        </div>
                        <p class="text-sm text-gray-900">{{ $message->subject ?? '' }}</p>
                        <p class="text-sm text-gray-600 mt-1">{{ Str::limit($message->body ?? $message->content ?? '', 200) }}</p>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="text-center py-8">
                    <div class="w-12 h-12 rounded-full bg-gray-100 flex items-center justify-center mx-auto mb-3">
                        <i data-lucide="message-square" class="w-6 h-6 text-gray-400"></i>
                    </div>
                    <p class="text-sm text-gray-500">No messages</p>
                </div>
                @endif
            </div>

            <!-- CRM Notes Tab -->
            <div x-show="activeTab === 'crm'" x-cloak>
                <!-- Add Note Form -->
                <div class="mb-6 p-4 bg-gray-50 rounded-xl border border-gray-200">
                    <h3 class="text-sm font-semibold text-gray-900 mb-3">Add New Note</h3>
                    <form action="{{ route('admin.users.crm-notes.store', $user) }}" method="POST" class="space-y-3">
                        @csrf
                        <textarea name="note" rows="3" placeholder="Write a note about this member..."
                                  class="w-full px-4 py-3 border border-gray-300 rounded-xl text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all" required></textarea>
                        <div class="flex flex-wrap items-end gap-3">
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Type</label>
                                <select name="type" class="px-3 py-2 border border-gray-300 rounded-lg text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-purple-500">
                                    <option value="general">General</option>
                                    <option value="support">Support</option>
                                    <option value="sales">Sales</option>
                                    <option value="feedback">Feedback</option>
                                    <option value="issue">Issue</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Follow-up Date</label>
                                <input type="date" name="follow_up_date"
                                       class="px-3 py-2 border border-gray-300 rounded-lg text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-purple-500">
                            </div>
                            <button type="submit" class="btn-primary px-4 py-2 text-white text-sm font-medium rounded-lg transition-all hover:shadow-lg">
                                <i data-lucide="plus" class="w-4 h-4 inline mr-1"></i> Add Note
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Notes List -->
                @if($user->crmNotes && $user->crmNotes->count() > 0)
                <div class="space-y-3">
                    @foreach($user->crmNotes->sortByDesc('created_at') as $note)
                    <div class="p-4 rounded-xl border {{ $note->is_pinned ? 'border-purple-200 bg-purple-50' : 'border-gray-200 bg-white' }}">
                        <div class="flex items-start justify-between mb-2">
                            <div class="flex items-center gap-2">
                                @if($note->is_pinned)
                                    <i data-lucide="pin" class="w-3.5 h-3.5 text-purple-600"></i>
                                @endif
                                <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-medium
                                    @switch($note->type)
                                        @case('support') bg-blue-100 text-blue-700 @break
                                        @case('sales') bg-green-100 text-green-700 @break
                                        @case('feedback') bg-orange-100 text-orange-700 @break
                                        @case('issue') bg-red-100 text-red-700 @break
                                        @default bg-gray-100 text-gray-600
                                    @endswitch
                                ">{{ ucfirst($note->type) }}</span>
                                @if($note->follow_up_date)
                                    <span class="text-xs text-gray-500">
                                        <i data-lucide="calendar" class="w-3 h-3 inline"></i>
                                        Follow-up: {{ \Carbon\Carbon::parse($note->follow_up_date)->format('M d, Y') }}
                                    </span>
                                @endif
                            </div>
                            <div class="flex items-center gap-1">
                                <form action="{{ route('admin.users.crm-notes.pin', [$user, $note]) }}" method="POST" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="p-1.5 text-gray-400 hover:text-purple-600 hover:bg-purple-50 rounded-lg transition-colors" title="{{ $note->is_pinned ? 'Unpin' : 'Pin' }}">
                                        <i data-lucide="pin" class="w-3.5 h-3.5"></i>
                                    </button>
                                </form>
                                <form action="{{ route('admin.users.crm-notes.destroy', [$user, $note]) }}" method="POST" class="inline" onsubmit="return confirm('Delete this note?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors" title="Delete">
                                        <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                        <p class="text-sm text-gray-800">{{ $note->note }}</p>
                        <p class="text-xs text-gray-400 mt-2">
                            by {{ $note->admin?->name ?? 'Admin' }} &middot; {{ $note->created_at->format('M d, Y H:i') }}
                        </p>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="text-center py-8">
                    <div class="w-12 h-12 rounded-full bg-gray-100 flex items-center justify-center mx-auto mb-3">
                        <i data-lucide="notebook-pen" class="w-6 h-6 text-gray-400"></i>
                    </div>
                    <p class="text-sm text-gray-500">No CRM notes yet</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
