@extends('admin.layouts.admin')

@section('page-title', 'Member Reviews')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div>
        <div class="flex items-center gap-3 mb-2">
            <div class="w-10 h-10 rounded-xl bg-amber-100 flex items-center justify-center">
                <i data-lucide="shield-check" class="w-5 h-5 text-amber-600"></i>
            </div>
            <h1 class="text-2xl font-bold text-gray-900">Reviews</h1>
        </div>
        <p class="text-gray-500">Control new members, approve profile submissions and moderate public reviews</p>
    </div>

    <!-- Headline Stats -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <a href="{{ route('admin.member-reviews.index', ['tab' => 'members']) }}" class="card p-5 flex items-center gap-4 hover:shadow-md transition-all">
            <div class="w-12 h-12 rounded-xl bg-blue-100 flex items-center justify-center shrink-0">
                <i data-lucide="user-plus" class="w-6 h-6 text-blue-600"></i>
            </div>
            <div>
                <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['new_members']) }}</p>
                <p class="text-sm text-gray-500">New Members (7 days)</p>
            </div>
        </a>
        <a href="{{ route('admin.member-reviews.index', ['tab' => 'unverified']) }}" class="card p-5 flex items-center gap-4 hover:shadow-md transition-all">
            <div class="w-12 h-12 rounded-xl bg-orange-100 flex items-center justify-center shrink-0">
                <i data-lucide="mail-question" class="w-6 h-6 text-orange-600"></i>
            </div>
            <div>
                <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['unverified']) }}</p>
                <p class="text-sm text-gray-500">Unverified Members</p>
            </div>
        </a>
        <a href="{{ route('admin.member-reviews.index', ['tab' => 'approvals']) }}" class="card p-5 flex items-center gap-4 hover:shadow-md transition-all">
            <div class="w-12 h-12 rounded-xl bg-amber-100 flex items-center justify-center shrink-0">
                <i data-lucide="clock" class="w-6 h-6 text-amber-600"></i>
            </div>
            <div>
                <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['pending_profiles']) }}</p>
                <p class="text-sm text-gray-500">Pending Profile Approvals</p>
            </div>
        </a>
        <a href="{{ route('admin.member-reviews.index', ['tab' => 'reviews']) }}" class="card p-5 flex items-center gap-4 hover:shadow-md transition-all">
            <div class="w-12 h-12 rounded-xl bg-red-100 flex items-center justify-center shrink-0">
                <i data-lucide="flag" class="w-6 h-6 text-red-600"></i>
            </div>
            <div>
                <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['reported_reviews']) }}</p>
                <p class="text-sm text-gray-500">Reported Reviews</p>
            </div>
        </a>
    </div>

    <!-- Tabs -->
    @php
        $tabs = [
            'members' => ['label' => 'New Members', 'icon' => 'user-plus'],
            'unverified' => ['label' => 'Unverified', 'icon' => 'mail-question'],
            'approvals' => ['label' => 'Profile Approvals', 'icon' => 'badge-check'],
            'reviews' => ['label' => 'Teacher Reviews', 'icon' => 'star'],
        ];
    @endphp
    <div class="flex flex-wrap gap-2">
        @foreach($tabs as $key => $t)
            <a href="{{ route('admin.member-reviews.index', ['tab' => $key]) }}"
               class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-semibold transition {{ $tab === $key ? 'bg-purple-600 text-white shadow-sm' : 'bg-white text-gray-600 hover:bg-gray-50 border border-gray-200' }}">
                <i data-lucide="{{ $t['icon'] }}" class="w-4 h-4"></i>
                {{ $t['label'] }}
                @if($key === 'unverified' && $stats['unverified'] > 0)
                    <span class="px-1.5 py-0.5 rounded-full text-[10px] font-semibold {{ $tab === $key ? 'bg-white/20' : 'bg-orange-100 text-orange-600' }}">{{ $stats['unverified'] }}</span>
                @elseif($key === 'approvals' && $stats['pending_profiles'] > 0)
                    <span class="px-1.5 py-0.5 rounded-full text-[10px] font-semibold {{ $tab === $key ? 'bg-white/20' : 'bg-red-100 text-red-600' }}">{{ $stats['pending_profiles'] }}</span>
                @elseif($key === 'reviews' && $stats['reported_reviews'] > 0)
                    <span class="px-1.5 py-0.5 rounded-full text-[10px] font-semibold {{ $tab === $key ? 'bg-white/20' : 'bg-red-100 text-red-600' }}">{{ $stats['reported_reviews'] }}</span>
                @endif
            </a>
        @endforeach
    </div>

    @if(in_array($tab, ['members', 'unverified']))
    <!-- New members / unverified members: verification + profile state at a glance, inline approval -->
    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Member</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Role</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Plan</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Registered</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($members as $member)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                @if($member->avatar)
                                    <img src="{{ $member->avatar }}" alt="" class="w-10 h-10 rounded-full object-cover">
                                @else
                                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-purple-500 to-pink-500 flex items-center justify-center text-white font-semibold text-sm">
                                        {{ strtoupper(substr($member->name, 0, 1)) }}
                                    </div>
                                @endif
                                <div>
                                    <p class="font-medium text-gray-900">{{ $member->name }}</p>
                                    <p class="text-xs text-gray-500">{{ $member->email }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            @switch($member->role)
                                @case('admin')
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-purple-100 text-purple-700 text-xs font-medium"><i data-lucide="shield" class="w-3 h-3"></i> Admin</span>
                                    @break
                                @case('teacher')
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-green-100 text-green-700 text-xs font-medium"><i data-lucide="briefcase" class="w-3 h-3"></i> Teacher</span>
                                    @break
                                @case('school')
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-amber-100 text-amber-700 text-xs font-medium"><i data-lucide="building" class="w-3 h-3"></i> School</span>
                                    @break
                                @default
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-blue-100 text-blue-700 text-xs font-medium"><i data-lucide="graduation-cap" class="w-3 h-3"></i> Student</span>
                            @endswitch
                        </td>
                        <td class="px-6 py-4">
                            @if($member->plan === 'premium')
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-orange-100 text-orange-700 text-xs font-medium"><i data-lucide="crown" class="w-3 h-3"></i> Premium</span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-gray-100 text-gray-600 text-xs font-medium">Free</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">
                            <p>{{ $member->created_at->format('d.m.Y H:i') }}</p>
                            <p class="text-xs text-gray-400">{{ $member->created_at->diffForHumans() }}</p>
                        </td>
                        <td class="px-6 py-4">
                            <div class="space-y-1">
                                @if($member->email_verified_at)
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-green-50 text-green-700 text-xs font-medium"><i data-lucide="check" class="w-3 h-3"></i> Verified</span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-gray-100 text-gray-500 text-xs font-medium"><i data-lucide="mail-question" class="w-3 h-3"></i> Unverified</span>
                                @endif
                                @if($member->is_restricted)
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-orange-100 text-orange-700 text-xs font-medium"><i data-lucide="lock" class="w-3 h-3"></i> Restricted</span>
                                @endif
                                @if($member->teacherProfile && $member->teacherProfile->status === \App\Models\TeacherProfile::STATUS_SUBMITTED)
                                    <a href="{{ route('admin.teacher-profiles.show', $member->teacherProfile) }}" class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-amber-100 text-amber-700 text-xs font-medium hover:bg-amber-200 transition-colors">
                                        <i data-lucide="clock" class="w-3 h-3"></i> Profile awaiting approval
                                    </a>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-1">
                                @unless($member->email_verified_at)
                                <form method="POST" action="{{ route('admin.users.verify-email', $member) }}" onsubmit="return confirm('Approve {{ $member->name }} and mark the email as verified?')">
                                    @csrf
                                    <button class="px-3 py-1.5 text-xs font-semibold text-green-700 bg-green-50 hover:bg-green-100 rounded-lg mr-1">Approve</button>
                                </form>
                                @endunless
                                <a href="{{ route('admin.users.show', $member) }}" class="p-2 text-gray-500 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors" title="View">
                                    <i data-lucide="eye" class="w-4 h-4"></i>
                                </a>
                                <a href="{{ route('admin.users.edit', $member) }}" class="p-2 text-gray-500 hover:text-purple-600 hover:bg-purple-50 rounded-lg transition-colors" title="Edit">
                                    <i data-lucide="pencil" class="w-4 h-4"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="px-6 py-12 text-center text-gray-400 text-sm">{{ $tab === 'unverified' ? 'No members awaiting approval. 🎉' : 'No members found.' }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($members->hasPages())
        <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">{{ $members->links() }}</div>
        @endif
    </div>

    @elseif($tab === 'approvals')
    <!-- Pending teacher/school profile submissions -->
    <div class="card overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-left text-xs text-gray-500 uppercase">
                <tr>
                    <th class="px-5 py-3">Profile</th>
                    <th class="px-5 py-3">Type</th>
                    <th class="px-5 py-3">Tier</th>
                    <th class="px-5 py-3">Submitted</th>
                    <th class="px-5 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($pendingProfiles as $profile)
                    <tr class="hover:bg-gray-50">
                        <td class="px-5 py-3">
                            <div class="flex items-center gap-3">
                                @if($profile->user->hasAvatar())
                                    <img src="{{ $profile->user->avatar }}" class="w-9 h-9 rounded-full object-cover" alt="">
                                @else
                                    <div class="w-9 h-9 rounded-full bg-primary-100 text-primary-700 flex items-center justify-center text-sm font-bold">{{ strtoupper(substr($profile->user->name, 0, 1)) }}</div>
                                @endif
                                <div>
                                    <p class="font-semibold text-gray-900">{{ $profile->displayName() }}</p>
                                    <p class="text-xs text-gray-400">{{ $profile->user->email }} · /{{ $profile->isSchoolEntity() ? 'schools' : 'teachers' }}/{{ $profile->slug }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-5 py-3">
                            @if($profile->isSchoolEntity())
                                <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-teal-50 text-teal-700">{{ __('school.admin.entity_school') }}</span>
                            @else
                                <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-green-50 text-green-700">{{ __('teacher.nav.role_teacher') }}</span>
                            @endif
                        </td>
                        <td class="px-5 py-3">
                            <span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $profile->tier === 'premium' ? 'bg-amber-100 text-amber-700' : 'bg-gray-100 text-gray-600' }}">{{ ucfirst($profile->tier) }}</span>
                        </td>
                        <td class="px-5 py-3 text-xs text-gray-500">{{ $profile->submitted_at?->format('d.m.Y H:i') ?? '—' }}</td>
                        <td class="px-5 py-3">
                            <div class="flex items-center justify-end gap-2">
                                <form method="POST" action="{{ route('admin.teacher-profiles.approve', $profile) }}" onsubmit="return confirm('Approve this profile and publish it publicly?')">
                                    @csrf
                                    <button class="px-3 py-1.5 text-xs font-semibold text-green-700 bg-green-50 hover:bg-green-100 rounded-lg">Approve</button>
                                </form>
                                <a href="{{ route('admin.teacher-profiles.show', $profile) }}" class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-semibold text-primary-600 bg-primary-50 hover:bg-primary-100 rounded-lg">
                                    Review <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-5 py-10 text-center text-sm text-gray-400">No profiles awaiting approval. 🎉</td></tr>
                @endforelse
            </tbody>
        </table>
        @if($pendingProfiles->hasPages())
        <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">{{ $pendingProfiles->links() }}</div>
        @endif
    </div>

    @else
    <!-- Public teacher review moderation -->
    <div class="card overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex gap-2">
            @foreach(['reported' => 'Reported', 'all' => 'All', 'hidden' => 'Hidden'] as $key => $label)
                <a href="{{ route('admin.member-reviews.index', ['tab' => 'reviews', 'filter' => $key]) }}"
                   class="px-3 py-1.5 text-sm font-semibold rounded-lg {{ $filter === $key ? 'bg-purple-600 text-white' : 'bg-gray-100 text-gray-600' }}">
                    {{ $label }}
                </a>
            @endforeach
        </div>
        @if($reviews->isEmpty())
            <div class="p-10 text-center text-gray-400 text-sm">No reviews in this state.</div>
        @else
            <ul class="divide-y divide-gray-100">
                @foreach($reviews as $review)
                    <li class="px-6 py-4">
                        <div class="flex items-start gap-4">
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-semibold text-gray-900">
                                    {{ $review->student->name }} {{ $review->student->surname }}
                                    <span class="text-amber-500">{{ str_repeat('★', $review->rating) }}{{ str_repeat('☆', 5 - $review->rating) }}</span>
                                    → <a href="{{ $review->teacherProfile->publicUrl() }}" class="text-purple-600 hover:underline">{{ $review->teacherProfile->display_name ?? $review->teacherProfile->user->name }}</a>
                                </p>
                                @if($review->body)<p class="text-sm text-gray-600 mt-1">{{ $review->body }}</p>@endif
                                <p class="text-xs text-gray-400 mt-1">
                                    {{ $review->created_at->format('M j, Y') }}
                                    · <b>{{ $review->status }}</b>
                                    @if($review->reported_at)
                                        · <span class="text-red-600 font-semibold">Reported {{ $review->reported_at->format('M j') }}: {{ $review->report_reason ?: '—' }}</span>
                                    @endif
                                </p>
                            </div>
                            <div class="flex gap-2 shrink-0">
                                @if($review->status !== 'approved' || $review->reported_at)
                                    <form method="POST" action="{{ route('admin.teacher-reviews.approve', $review) }}">
                                        @csrf
                                        <button class="px-3 py-1.5 text-xs font-semibold text-green-700 bg-green-50 hover:bg-green-100 rounded-lg">Approve</button>
                                    </form>
                                @endif
                                @if($review->status !== 'hidden')
                                    <form method="POST" action="{{ route('admin.teacher-reviews.hide', $review) }}">
                                        @csrf
                                        <button class="px-3 py-1.5 text-xs font-semibold text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-lg">Hide</button>
                                    </form>
                                @endif
                                <form method="POST" action="{{ route('admin.teacher-reviews.destroy', $review) }}" onsubmit="return confirm('Delete this review permanently?')">
                                    @csrf @method('DELETE')
                                    <button class="px-3 py-1.5 text-xs font-semibold text-red-600 bg-red-50 hover:bg-red-100 rounded-lg">Delete</button>
                                </form>
                            </div>
                        </div>
                    </li>
                @endforeach
            </ul>
            @if($reviews->hasPages())
            <div class="px-6 py-3 border-t border-gray-100">{{ $reviews->links() }}</div>
            @endif
        @endif
    </div>
    @endif
</div>
@endsection
