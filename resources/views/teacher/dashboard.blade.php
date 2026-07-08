@extends('teacher.layouts.crm')

@section('title', __('teacher.dashboard.title'))

@section('content')
<div class="max-w-6xl">
    <div class="mb-6">
        <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">{{ __('teacher.dashboard.welcome', ['name' => $user->name]) }}</h1>
        <p class="text-gray-500 mt-1">{{ now()->translatedFormat('l, F j, Y') }}</p>
    </div>

    @php
        $tz = \App\Models\TeacherBookingSetting::where('teacher_id', auth()->id())->value('timezone') ?? config('app.timezone');
        $ap = 'teacher.appointments';
    @endphp

    {{-- Premium incentive (only for teachers who are not yet Teacher Premium) --}}
    @if(! $profile->isPremiumTier() && $user->plan !== 'premium' && $benefitSettings['enabled'])
        <div class="card p-6 mb-6 border-primary-100 bg-gradient-to-br from-primary-50/60 to-white">
            <div class="flex flex-col sm:flex-row sm:items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-primary-100 flex items-center justify-center shrink-0">
                    <i data-lucide="sparkles" class="w-6 h-6 text-primary-600"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <h2 class="font-bold text-gray-900">{{ __('teacher.dashboard.premium_incentive') }}</h2>
                    <p class="text-sm text-gray-500 mt-1">
                        {{ __('teacher.dashboard.incentive_intro', [
                            'discount_threshold' => $benefitSettings['discount_threshold'],
                            'discount_percentage' => $benefitSettings['discount_percentage'],
                            'free_threshold' => $benefitSettings['free_threshold'],
                            'free_months' => $benefitSettings['free_months'],
                        ]) }}
                    </p>
                    @if($activeBenefit)
                        <p class="text-sm font-semibold text-green-700 bg-green-50 rounded-lg px-3 py-2 mt-3 inline-block">
                            @if($activeBenefit->type === 'discount')
                                {{ __('teacher.dashboard.active_benefit_discount', ['percentage' => $activeBenefit->discount_percentage]) }}
                            @else
                                {{ __('teacher.dashboard.active_benefit_free', ['date' => $activeBenefit->ends_at?->format('d.m.Y')]) }}
                            @endif
                        </p>
                    @endif
                </div>
                <div class="text-center shrink-0 px-4">
                    <p class="text-3xl font-bold text-primary-700 leading-none">{{ $eligibleStudentCount }}</p>
                    <p class="text-xs text-gray-500 mt-1">{{ __('teacher.dashboard.eligible_students') }}</p>
                </div>
            </div>
        </div>
    @endif

    {{-- Row 1: Notifications & Messages · Appointment requests · Upcoming lessons --}}
    <div class="grid sm:grid-cols-2 xl:grid-cols-3 gap-5 mb-6">
        {{-- Notifications & Messages --}}
        <div class="card p-6 flex flex-col">
            <h2 class="font-bold text-gray-900 mb-4">{{ __('teacher.dashboard.notifications') }} &amp; {{ __('teacher.nav.messages') }}</h2>
            <div class="grid grid-cols-2 gap-3 flex-1">
                <a href="#notifications" class="group flex flex-col items-center justify-center p-4 bg-gray-50 hover:bg-primary-50 rounded-xl transition text-center">
                    <span class="relative inline-flex">
                        <i data-lucide="bell" class="w-7 h-7 text-primary-600"></i>
                        @if($unreadCount > 0)
                            <span class="absolute -top-2 -right-2.5 min-w-[18px] px-1 py-0.5 rounded-full text-[10px] font-bold bg-primary-600 text-white leading-none text-center">{{ $unreadCount }}</span>
                        @endif
                    </span>
                    <span class="text-sm font-semibold text-gray-700 mt-2.5 group-hover:text-primary-700">{{ __('teacher.dashboard.notifications') }}</span>
                </a>
                <a href="{{ route('teacher.messages.index') }}" class="group flex flex-col items-center justify-center p-4 bg-gray-50 hover:bg-primary-50 rounded-xl transition text-center">
                    <span class="relative inline-flex">
                        <i data-lucide="message-circle" class="w-7 h-7 text-primary-600"></i>
                        @if($studentStats['unread_messages'] > 0)
                            <span class="absolute -top-2 -right-2.5 min-w-[18px] px-1 py-0.5 rounded-full text-[10px] font-bold bg-primary-600 text-white leading-none text-center">{{ $studentStats['unread_messages'] }}</span>
                        @endif
                    </span>
                    <span class="text-sm font-semibold text-gray-700 mt-2.5 group-hover:text-primary-700">{{ __('teacher.nav.messages') }}</span>
                </a>
            </div>
        </div>

        {{-- Appointment requests --}}
        <div class="card p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="font-bold text-gray-900 flex items-center gap-2">
                    <i data-lucide="calendar-clock" class="w-4 h-4 text-amber-500"></i>
                    {{ __('teacher.dashboard.appointment_requests') }}
                </h2>
                @if($pendingAppointments->isNotEmpty())
                    <span class="px-2 py-0.5 rounded-full bg-amber-100 text-amber-700 text-xs font-bold">{{ $pendingAppointments->count() }}</span>
                @endif
            </div>
            @forelse($pendingAppointments as $appointment)
                <a href="{{ route('teacher.calendar.index') }}" class="flex items-center gap-3 py-2.5 border-b border-gray-50 last:border-0 hover:bg-gray-50 rounded-lg px-2 -mx-2 transition">
                    <div class="w-9 h-9 rounded-full bg-amber-50 text-amber-700 flex items-center justify-center text-sm font-bold shrink-0">
                        {{ strtoupper(substr($appointment->student->name, 0, 1)) }}
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-semibold text-gray-800 truncate">{{ $appointment->student->name }} {{ $appointment->student->surname }}</p>
                        <p class="text-xs text-gray-500">{{ $appointment->starts_at->timezone($tz)->format('D, M j · H:i') }}</p>
                    </div>
                    <i data-lucide="chevron-right" class="w-4 h-4 text-gray-300"></i>
                </a>
            @empty
                <p class="text-sm text-gray-400">{{ __($ap.'.no_pending') }}</p>
            @endforelse
        </div>

        {{-- Upcoming lessons --}}
        <div class="card p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="font-bold text-gray-900 flex items-center gap-2">
                    <i data-lucide="calendar-check" class="w-4 h-4 text-green-600"></i>
                    {{ __('teacher.dashboard.upcoming_lessons') }}
                </h2>
                <a href="{{ route('teacher.calendar.index') }}" class="text-xs font-semibold text-primary-600 hover:text-primary-800">{{ __('teacher.nav.calendar') }} →</a>
            </div>
            @forelse($upcomingLessons as $appointment)
                <div class="flex items-center gap-3 py-2.5 border-b border-gray-50 last:border-0">
                    <div class="w-9 h-9 rounded-full bg-green-50 text-green-700 flex items-center justify-center text-sm font-bold shrink-0">
                        {{ strtoupper(substr($appointment->student->name, 0, 1)) }}
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-semibold text-gray-800 truncate">{{ $appointment->student->name }} {{ $appointment->student->surname }}</p>
                        <p class="text-xs text-gray-500">{{ $appointment->starts_at->timezone($tz)->format('D, M j · H:i') }}</p>
                    </div>
                    @if($appointment->meeting_url)
                        <a href="{{ $appointment->meeting_url }}" target="_blank" rel="noopener" class="p-1.5 text-green-600 hover:text-green-800" title="{{ __('teacher.my_appointments.join') }}">
                            <i data-lucide="video" class="w-4 h-4"></i>
                        </a>
                    @endif
                </div>
            @empty
                <p class="text-sm text-gray-400">{{ __($ap.'.no_upcoming') }}</p>
            @endforelse
        </div>

    </div>

    {{-- Row 2: Student statistics · At a glance (equal width) --}}
    <div class="grid sm:grid-cols-2 gap-5 mb-6">
        {{-- Student statistics --}}
        <div class="card p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="font-bold text-gray-900 flex items-center gap-2">
                    <i data-lucide="bar-chart-3" class="w-4 h-4 text-primary-500"></i>
                    {{ __('teacher.dashboard.student_stats') }}
                </h2>
                <a href="{{ route('teacher.students.index') }}" class="text-xs font-semibold text-primary-600 hover:text-primary-800">{{ __('teacher.nav.students') }} →</a>
            </div>
            <dl class="space-y-3">
                <div class="flex items-center justify-between">
                    <dt class="text-sm text-gray-500">{{ __('teacher.dashboard.stat_active_students') }}</dt>
                    <dd class="text-base font-bold text-gray-900">{{ $studentStats['active_students'] }}</dd>
                </div>
                <div class="flex items-center justify-between">
                    <dt class="text-sm text-gray-500">{{ __('teacher.dashboard.stat_open_assignments') }}</dt>
                    <dd class="text-base font-bold text-gray-900">{{ $studentStats['assignments_open'] }}</dd>
                </div>
                <div class="flex items-center justify-between">
                    <dt class="text-sm text-gray-500">{{ __('teacher.dashboard.stat_completed_assignments') }}</dt>
                    <dd class="text-base font-bold text-gray-900">{{ $studentStats['assignments_completed'] }}</dd>
                </div>
                <div class="flex items-center justify-between">
                    <dt class="text-sm text-gray-500">{{ __('teacher.dashboard.stat_average_score') }}</dt>
                    <dd class="text-base font-bold {{ ($studentStats['average_score'] ?? 0) >= 70 ? 'text-green-700' : 'text-gray-900' }}">
                        {{ $studentStats['average_score'] !== null ? round($studentStats['average_score'], 1).'%' : '—' }}
                    </dd>
                </div>
            </dl>
        </div>

        {{-- At a glance --}}
        <div class="card p-6">
            <h2 class="font-bold text-gray-900 mb-4">{{ __('teacher.dashboard.at_a_glance') }}</h2>
            <div class="grid grid-cols-2 gap-3">
                <a href="{{ route('teacher.students.index') }}" class="group p-3.5 bg-gray-50 hover:bg-primary-50 rounded-xl transition">
                    <p class="text-2xl font-bold text-gray-900 group-hover:text-primary-700">{{ $studentStats['active_students'] }}</p>
                    <p class="text-sm text-gray-500">{{ __('teacher.nav.students') }}</p>
                </a>
                <a href="{{ route('teacher.classes.index') }}" class="group p-3.5 bg-gray-50 hover:bg-primary-50 rounded-xl transition">
                    <p class="text-2xl font-bold text-gray-900 group-hover:text-primary-700">{{ $studentStats['classes'] }}</p>
                    <p class="text-sm text-gray-500">{{ __('teacher.nav.classes') }}</p>
                </a>
                <a href="{{ route('teacher.messages.index') }}" class="group p-3.5 bg-gray-50 hover:bg-primary-50 rounded-xl transition">
                    <p class="text-2xl font-bold {{ $studentStats['unread_messages'] > 0 ? 'text-primary-700' : 'text-gray-900' }}">{{ $studentStats['unread_messages'] }}</p>
                    <p class="text-sm text-gray-500">{{ __('teacher.dashboard.unread_messages') }}</p>
                </a>
                <a href="{{ route('teacher.assignments.index') }}" class="group p-3.5 bg-gray-50 hover:bg-primary-50 rounded-xl transition">
                    <p class="text-2xl font-bold text-gray-900 group-hover:text-primary-700">{{ $studentStats['assignments_open'] }}</p>
                    <p class="text-sm text-gray-500">{{ __('teacher.dashboard.open_assignments') }}</p>
                </a>
            </div>
        </div>
    </div>

    {{-- Row 3: My Students · Classes · Assignments --}}
    <div class="grid sm:grid-cols-2 xl:grid-cols-3 gap-5 mb-6">
        @foreach([
            ['route' => 'teacher.students.index', 'icon' => 'users', 'title' => 'nav_students_title', 'desc' => 'nav_students_desc'],
            ['route' => 'teacher.classes.index', 'icon' => 'school', 'title' => 'nav_classes_title', 'desc' => 'nav_classes_desc'],
            ['route' => 'teacher.assignments.index', 'icon' => 'clipboard-list', 'title' => 'nav_assignments_title', 'desc' => 'nav_assignments_desc'],
        ] as $navCard)
            <a href="{{ route($navCard['route']) }}" class="card p-6 flex items-center gap-4 hover:border-primary-200 hover:shadow-md transition group">
                <div class="w-12 h-12 rounded-xl bg-primary-50 flex items-center justify-center shrink-0">
                    <i data-lucide="{{ $navCard['icon'] }}" class="w-6 h-6 text-primary-600"></i>
                </div>
                <div class="min-w-0">
                    <p class="font-bold text-gray-900 group-hover:text-primary-700">{{ __('teacher.dashboard.'.$navCard['title']) }}</p>
                    <p class="text-sm text-gray-500">{{ __('teacher.dashboard.'.$navCard['desc']) }}</p>
                </div>
                <i data-lucide="chevron-right" class="w-5 h-5 text-gray-300 ml-auto shrink-0"></i>
            </a>
        @endforeach
    </div>

    {{-- Row 4: Your Published Content · Your Documents --}}
    <div class="grid sm:grid-cols-2 gap-5 mb-6">
        @foreach([
            ['route' => 'teacher.content.index', 'icon' => 'newspaper', 'title' => 'nav_content_title', 'desc' => 'nav_content_desc'],
            ['route' => 'teacher.profile.edit', 'icon' => 'folder', 'title' => 'nav_documents_title', 'desc' => 'nav_documents_desc'],
        ] as $navCard)
            <a href="{{ route($navCard['route']) }}" class="card p-6 flex items-center gap-4 hover:border-primary-200 hover:shadow-md transition group">
                <div class="w-12 h-12 rounded-xl bg-primary-50 flex items-center justify-center shrink-0">
                    <i data-lucide="{{ $navCard['icon'] }}" class="w-6 h-6 text-primary-600"></i>
                </div>
                <div class="min-w-0">
                    <p class="font-bold text-gray-900 group-hover:text-primary-700">{{ __('teacher.dashboard.'.$navCard['title']) }}</p>
                    <p class="text-sm text-gray-500">{{ __('teacher.dashboard.'.$navCard['desc']) }}</p>
                </div>
                <i data-lucide="chevron-right" class="w-5 h-5 text-gray-300 ml-auto shrink-0"></i>
            </a>
        @endforeach
    </div>

    {{-- Row 5: Your Profile · Your Calendar · Settings --}}
    <div class="grid sm:grid-cols-2 xl:grid-cols-3 gap-5 mb-6">
        @foreach([
            ['route' => 'teacher.profile.edit', 'icon' => 'user-pen', 'title' => 'nav_profile_title', 'desc' => 'nav_profile_desc'],
            ['route' => 'teacher.calendar.index', 'icon' => 'calendar', 'title' => 'nav_calendar_title', 'desc' => 'nav_calendar_desc'],
            ['route' => 'teacher.settings', 'icon' => 'settings', 'title' => 'nav_settings_title', 'desc' => 'nav_settings_desc'],
        ] as $navCard)
            <a href="{{ route($navCard['route']) }}" class="card p-6 flex items-center gap-4 hover:border-primary-200 hover:shadow-md transition group">
                <div class="w-12 h-12 rounded-xl bg-primary-50 flex items-center justify-center shrink-0">
                    <i data-lucide="{{ $navCard['icon'] }}" class="w-6 h-6 text-primary-600"></i>
                </div>
                <div class="min-w-0">
                    <p class="font-bold text-gray-900 group-hover:text-primary-700">{{ __('teacher.dashboard.'.$navCard['title']) }}</p>
                    <p class="text-sm text-gray-500">{{ __('teacher.dashboard.'.$navCard['desc']) }}</p>
                </div>
                <i data-lucide="chevron-right" class="w-5 h-5 text-gray-300 ml-auto shrink-0"></i>
            </a>
        @endforeach
    </div>

    {{-- Notifications --}}
    <div id="notifications" class="card p-6 scroll-mt-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="font-bold text-gray-900">
                {{ __('teacher.dashboard.notifications') }}
                @if($unreadCount > 0)
                    <span class="ml-1 px-2 py-0.5 rounded-full text-xs font-semibold bg-primary-100 text-primary-700">{{ $unreadCount }}</span>
                @endif
            </h2>
            @if($unreadCount > 0)
                <form method="POST" action="{{ route('teacher.notifications.read') }}">
                    @csrf
                    <button type="submit" class="text-xs font-semibold text-primary-600 hover:text-primary-700">{{ __('teacher.dashboard.mark_all_read') }}</button>
                </form>
            @endif
        </div>

        @forelse($notifications as $notification)
            <div class="flex items-start gap-3 py-3 border-b border-gray-50 last:border-0 {{ $notification->read_at ? 'opacity-60' : '' }}">
                <div class="w-8 h-8 rounded-full bg-primary-50 flex items-center justify-center shrink-0 mt-0.5">
                    <i data-lucide="bell" class="w-4 h-4 text-primary-600"></i>
                </div>
                <div class="min-w-0">
                    <p class="text-sm text-gray-700">
                        @switch($notification->data['type'] ?? '')
                            @case('teacher_profile_approved') {{ __('teacher.status.approved_hint') }} @break
                            @case('teacher_profile_rejected') {{ __('teacher.status.rejected_hint') }} @break
                            @case('teacher_profile_suspended') {{ __('teacher.status.suspended_hint') }} @break
                            @case('teacher_relationship_accepted') {{ ($notification->data['student_name'] ?? '') }} — {{ __('teacher.dashboard.notif_student_joined') }} @break
                            @case('teacher_relationship_declined') {{ ($notification->data['student_name'] ?? '') }} — {{ __('teacher.dashboard.notif_request_declined') }} @break
                            @case('student_assignment_completed') {{ ($notification->data['title'] ?? '') }} — {{ __('teacher.dashboard.notif_assignment_completed') }} @break
                            @case('appointment_requested') {{ __('teacher.dashboard.notif_appointment_requested') }} @break
                            @case('appointment_status_changed') {{ __('teacher.dashboard.notif_appointment_changed') }} @break
                            @case('teacher_message_received') {{ ($notification->data['sender_name'] ?? '') }} — {{ __('teacher.dashboard.notif_message') }} @break
                            @case('teacher_review_received') {{ __('teacher.dashboard.notif_review') }} @break
                            @default {{ str_replace('_', ' ', $notification->data['type'] ?? '') }}
                        @endswitch
                    </p>
                    @if(!empty($notification->data['reason']))
                        <p class="text-xs text-gray-500 mt-0.5">{{ $notification->data['reason'] }}</p>
                    @endif
                    <p class="text-xs text-gray-400 mt-0.5">{{ $notification->created_at->diffForHumans() }}</p>
                </div>
            </div>
        @empty
            <p class="text-sm text-gray-400">{{ __('teacher.dashboard.no_notifications') }}</p>
        @endforelse
    </div>
</div>
@endsection
