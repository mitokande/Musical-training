@extends('admin.layouts.admin')

@section('page-title', 'Members')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <div class="flex items-center gap-3 mb-2">
                <div class="w-10 h-10 rounded-xl bg-purple-100 flex items-center justify-center">
                    <i data-lucide="users" class="w-5 h-5 text-purple-600"></i>
                </div>
                <h1 class="text-2xl font-bold text-gray-900">Members</h1>
            </div>
            <p class="text-gray-500">Manage all registered members and their roles</p>
        </div>
        <a href="{{ route('admin.users.create') }}" class="btn-primary inline-flex items-center gap-2 px-4 py-2.5 text-white text-sm font-semibold rounded-lg transition-all hover:shadow-lg">
            <i data-lucide="user-plus" class="w-4 h-4"></i>
            Add Member
        </a>
    </div>

    <!-- Segment Tabs -->
    @php
        $segmentTabs = [
            'all' => ['label' => 'All', 'icon' => 'users'],
            'students' => ['label' => 'Student Profiles', 'icon' => 'graduation-cap'],
            'teachers' => ['label' => 'Teacher Profiles', 'icon' => 'briefcase'],
            'schools' => ['label' => 'School Profiles', 'icon' => 'building'],
        ];
    @endphp
    <div class="flex flex-wrap gap-2">
        @foreach($segmentTabs as $key => $tab)
            <a href="{{ route('admin.users.index', $key === 'all' ? [] : ['segment' => $key]) }}"
               class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-semibold transition {{ $segment === $key ? 'bg-purple-600 text-white shadow-sm' : 'bg-white text-gray-600 hover:bg-gray-50 border border-gray-200' }}">
                <i data-lucide="{{ $tab['icon'] }}" class="w-4 h-4"></i>
                {{ $tab['label'] }}
            </a>
        @endforeach
    </div>

    <!-- Headline Stats -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        @foreach($stats as $stat)
            <div class="card p-5 flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-{{ $stat['color'] }}-100 flex items-center justify-center shrink-0">
                    <i data-lucide="{{ $stat['icon'] }}" class="w-6 h-6 text-{{ $stat['color'] }}-600"></i>
                </div>
                <div>
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($stat['value']) }}</p>
                    <p class="text-sm text-gray-500">{{ $stat['label'] }}</p>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Filter Bar -->
    <div class="card p-4">
        <form method="GET" action="{{ route('admin.users.index') }}" class="grid grid-cols-1 sm:grid-cols-2 {{ $segment === 'all' ? 'lg:grid-cols-5' : 'lg:grid-cols-4' }} gap-4">
            @if($segment !== 'all')
                <input type="hidden" name="segment" value="{{ $segment }}">
            @endif
            <!-- Search -->
            <div class="lg:col-span-2">
                <div class="relative">
                    <i data-lucide="search" class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2"></i>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name or email..."
                           class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-xl text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all">
                </div>
            </div>

            @if($segment === 'all')
            <!-- Role Filter -->
            <div>
                <select name="role" class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all">
                    <option value="">All Roles</option>
                    <option value="user" {{ request('role') == 'user' ? 'selected' : '' }}>Student</option>
                    <option value="teacher" {{ request('role') == 'teacher' ? 'selected' : '' }}>Teacher</option>
                    <option value="school" {{ request('role') == 'school' ? 'selected' : '' }}>Music School</option>
                </select>
            </div>
            @endif

            <!-- Plan Filter -->
            <div>
                <select name="plan" class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all">
                    <option value="">All Plans</option>
                    <option value="free" {{ request('plan') == 'free' ? 'selected' : '' }}>Free</option>
                    <option value="premium" {{ request('plan') == 'premium' ? 'selected' : '' }}>Premium</option>
                </select>
            </div>

            <!-- Status Filter -->
            <div class="flex gap-2">
                <select name="status" class="flex-1 px-4 py-2.5 border border-gray-300 rounded-xl text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all">
                    <option value="">All Status</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
                <button type="submit" class="btn-primary px-4 py-2.5 text-white rounded-xl transition-all hover:shadow-lg shrink-0">
                    <i data-lucide="filter" class="w-4 h-4"></i>
                </button>
            </div>
        </form>
    </div>

    <!-- Bulk actions -->
    <form id="bulkForm" method="POST" action="{{ route('admin.users.bulk-action') }}"
          onsubmit="return document.querySelectorAll('.bulk-check:checked').length > 0 && (this.action_select_value = this.action.value, this.action.value !== 'delete' || confirm('Delete the selected members? This cannot be undone.'))">
        @csrf
        <div class="card p-4 flex flex-wrap items-center gap-3">
            <span class="text-sm text-gray-500"><span id="bulkCount">0</span> selected</span>
            <select name="action" required class="px-3 py-2 border border-gray-300 rounded-lg text-sm text-gray-700 focus:ring-2 focus:ring-purple-500">
                <option value="">Bulk action...</option>
                <option value="set_plan_premium">Set plan: Premium</option>
                <option value="set_plan_free">Set plan: Free</option>
                <option value="set_role_user">Set role: Student</option>
                <option value="set_role_teacher">Set role: Teacher</option>
                <option value="set_role_school">Set role: School</option>
                <option value="start_trial">Start free trial</option>
                <option value="reset_trial">Reset trial eligibility</option>
                <option value="delete">Delete selected</option>
            </select>
            <button type="submit" class="btn-primary px-4 py-2 text-white text-sm font-semibold rounded-lg transition-all hover:shadow-lg">Apply</button>
            <span class="text-xs text-gray-400">Admins and your own account are always skipped.</span>
        </div>
    </form>

    <!-- Table -->
    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-4 py-4">
                            <input type="checkbox" id="checkAll" class="w-4 h-4 text-purple-600 border-gray-300 rounded focus:ring-purple-500">
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Member</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Email</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Role</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Plan</th>
                        @if(in_array($segment, ['teachers', 'schools']))
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Profile</th>
                        @endif
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Country</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Last Active</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($users as $user)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-4 py-4">
                            @if($user->role !== 'admin' && $user->id !== auth()->id())
                            <input type="checkbox" name="user_ids[]" value="{{ $user->id }}" form="bulkForm"
                                   class="bulk-check w-4 h-4 text-purple-600 border-gray-300 rounded focus:ring-purple-500">
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                @if($user->avatar)
                                    <img src="{{ $user->avatar }}" alt="" class="w-10 h-10 rounded-full object-cover">
                                @else
                                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-purple-500 to-pink-500 flex items-center justify-center text-white font-semibold text-sm">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </div>
                                @endif
                                <div>
                                    <div class="flex items-center gap-2">
                                        <p class="font-medium text-gray-900">{{ $user->name }}</p>
                                        @if($user->is_restricted)
                                            <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded bg-orange-100 text-orange-700 text-xs font-medium">
                                                <i data-lucide="lock" class="w-2.5 h-2.5"></i> Kısıtlı
                                            </span>
                                        @endif
                                    </div>
                                    <p class="text-xs text-gray-500">ID: {{ $user->id }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-700">{{ $user->email }}</td>
                        <td class="px-6 py-4">
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
                        </td>
                        <td class="px-6 py-4">
                            @if($user->onTrial())
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-purple-100 text-purple-700 text-xs font-medium"
                                      title="Free trial ends {{ $user->trial_ends_at->format('M j, Y') }}">
                                    <i data-lucide="sparkles" class="w-3 h-3"></i> Trial · {{ $user->trialDaysLeft() }}d
                                </span>
                            @elseif($user->plan === 'premium')
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-orange-100 text-orange-700 text-xs font-medium">
                                    <i data-lucide="crown" class="w-3 h-3"></i> Premium
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-gray-100 text-gray-600 text-xs font-medium">
                                    <i data-lucide="user" class="w-3 h-3"></i> Free
                                </span>
                            @endif
                        </td>
                        @if(in_array($segment, ['teachers', 'schools']))
                        <td class="px-6 py-4">
                            @if($user->teacherProfile)
                                @php
                                    $profileStatusColors = [
                                        'approved' => 'bg-green-100 text-green-700',
                                        'submitted_for_review' => 'bg-amber-100 text-amber-700',
                                        'rejected' => 'bg-red-100 text-red-700',
                                        'suspended' => 'bg-red-100 text-red-700',
                                        'draft' => 'bg-gray-100 text-gray-600',
                                        'archived' => 'bg-gray-100 text-gray-600',
                                    ];
                                @endphp
                                <a href="{{ route('admin.teacher-profiles.show', $user->teacherProfile) }}"
                                   class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium hover:opacity-80 transition-opacity {{ $profileStatusColors[$user->teacherProfile->status] ?? 'bg-gray-100 text-gray-600' }}">
                                    {{ __('teacher.status.'.$user->teacherProfile->status) }}
                                </a>
                            @else
                                <span class="text-xs text-gray-400">No profile</span>
                            @endif
                        </td>
                        @endif
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $user->country ?? '-' }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500">
                            {{ $user->last_active_at ? $user->last_active_at->diffForHumans() : 'Never' }}
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-1">
                                <a href="{{ route('admin.users.show', $user) }}"
                                   class="p-2 text-gray-500 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors" title="View">
                                    <i data-lucide="eye" class="w-4 h-4"></i>
                                </a>
                                <a href="{{ route('admin.users.edit', $user) }}"
                                   class="p-2 text-gray-500 hover:text-purple-600 hover:bg-purple-50 rounded-lg transition-colors" title="Edit">
                                    <i data-lucide="pencil" class="w-4 h-4"></i>
                                </a>
                                @if ($user->role !== 'admin' && $user->id !== auth()->id())
                                <form action="{{ route('admin.users.impersonate', $user) }}" method="POST" class="inline" onsubmit="return confirm('Log in as {{ $user->name }}? You can return via the banner at the top.')">
                                    @csrf
                                    <button type="submit" class="p-2 text-gray-500 hover:text-amber-600 hover:bg-amber-50 rounded-lg transition-colors" title="Login as user">
                                        <i data-lucide="venetian-mask" class="w-4 h-4"></i>
                                    </button>
                                </form>
                                @endif
                                @if ($user->id !== auth()->id())
                                <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this member?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-2 text-gray-500 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors" title="Delete">
                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="{{ in_array($segment, ['teachers', 'schools']) ? 9 : 8 }}" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center">
                                <div class="w-16 h-16 rounded-full bg-gray-100 flex items-center justify-center mb-4">
                                    <i data-lucide="users" class="w-8 h-8 text-gray-400"></i>
                                </div>
                                <p class="text-gray-500 font-medium">No members found</p>
                                <p class="text-sm text-gray-400 mt-1">Try adjusting your filters</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($users->hasPages())
        <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
            {{ $users->withQueryString()->links() }}
        </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const checks = document.querySelectorAll('.bulk-check');
    const countEl = document.getElementById('bulkCount');
    const checkAll = document.getElementById('checkAll');

    function updateCount() {
        countEl.textContent = document.querySelectorAll('.bulk-check:checked').length;
    }

    checks.forEach(c => c.addEventListener('change', updateCount));
    if (checkAll) {
        checkAll.addEventListener('change', function () {
            checks.forEach(c => { c.checked = checkAll.checked; });
            updateCount();
        });
    }
});
</script>
@endpush
