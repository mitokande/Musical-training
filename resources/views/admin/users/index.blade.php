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

    <!-- Filter Bar -->
    <div class="card p-4">
        <form method="GET" action="{{ route('admin.users.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
            <!-- Search -->
            <div class="lg:col-span-2">
                <div class="relative">
                    <i data-lucide="search" class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2"></i>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name or email..."
                           class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-xl text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all">
                </div>
            </div>

            <!-- Role Filter -->
            <div>
                <select name="role" class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all">
                    <option value="">All Roles</option>
                    <option value="user" {{ request('role') == 'user' ? 'selected' : '' }}>Student</option>
                    <option value="teacher" {{ request('role') == 'teacher' ? 'selected' : '' }}>Teacher</option>
                    <option value="school" {{ request('role') == 'school' ? 'selected' : '' }}>Music School</option>
                </select>
            </div>

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

    <!-- Table -->
    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Member</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Email</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Role</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Plan</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Country</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Last Active</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($users as $user)
                    <tr class="hover:bg-gray-50 transition-colors">
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
                                    <p class="font-medium text-gray-900">{{ $user->name }}</p>
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
                            @if($user->plan === 'premium')
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-orange-100 text-orange-700 text-xs font-medium">
                                    <i data-lucide="crown" class="w-3 h-3"></i> Premium
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-gray-100 text-gray-600 text-xs font-medium">
                                    <i data-lucide="user" class="w-3 h-3"></i> Free
                                </span>
                            @endif
                        </td>
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
                        <td colspan="7" class="px-6 py-12 text-center">
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
