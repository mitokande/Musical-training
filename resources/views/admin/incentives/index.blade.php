@extends('admin.layouts.admin')
@section('page-title', 'Premium Incentives')

@section('content')
@php
    use App\Models\TeacherSubscriptionBenefit as Benefit;

    $statusColors = [
        Benefit::STATUS_ACTIVE      => 'bg-green-100 text-green-700',
        Benefit::STATUS_PENDING     => 'bg-orange-100 text-orange-700',
        Benefit::STATUS_EXPIRED     => 'bg-gray-100 text-gray-600',
        Benefit::STATUS_REVOKED     => 'bg-red-100 text-red-700',
        Benefit::STATUS_SUPERSEDED  => 'bg-gray-100 text-gray-500',
    ];
    $statusLabels = [
        Benefit::STATUS_ACTIVE      => 'Active',
        Benefit::STATUS_PENDING     => 'Pending Approval',
        Benefit::STATUS_EXPIRED     => 'Expired',
        Benefit::STATUS_REVOKED     => 'Revoked',
        Benefit::STATUS_SUPERSEDED  => 'Superseded',
    ];
@endphp

<div class="space-y-6">
    {{-- Header --}}
    <div>
        <h2 class="text-2xl font-bold text-gray-800">Premium Incentives</h2>
        <p class="text-sm text-gray-500 mt-1">
            Teachers with <strong>{{ $stats['settings']['teacher_free_threshold'] }}+</strong> active Premium students use Harmoniva free automatically.
            Schools reaching <strong>{{ $stats['settings']['school_free_threshold'] }}+</strong> Premium students appear here for your approval.
        </p>
    </div>

    {{-- Stat cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-orange-100 flex items-center justify-center">
                    <i data-lucide="clock" class="w-5 h-5 text-orange-600"></i>
                </div>
                <div>
                    <div class="text-2xl font-bold text-gray-800">{{ $stats['pending_schools'] }}</div>
                    <div class="text-xs text-gray-500">Schools awaiting approval</div>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-green-100 flex items-center justify-center">
                    <i data-lucide="gift" class="w-5 h-5 text-green-600"></i>
                </div>
                <div>
                    <div class="text-2xl font-bold text-gray-800">{{ $stats['active_free'] }}</div>
                    <div class="text-xs text-gray-500">Active free-period accounts</div>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-purple-100 flex items-center justify-center">
                    <i data-lucide="percent" class="w-5 h-5 text-purple-600"></i>
                </div>
                <div>
                    <div class="text-2xl font-bold text-gray-800">{{ $stats['active_discount'] }}</div>
                    <div class="text-xs text-gray-500">Active discount grants</div>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center">
                    <i data-lucide="settings" class="w-5 h-5 text-blue-600"></i>
                </div>
                <div>
                    <div class="text-sm font-semibold text-gray-800">
                        {{ $stats['settings']['enabled'] ? 'Program On' : 'Program Off' }}
                    </div>
                    <div class="text-xs text-gray-500">
                        {{ $stats['settings']['discount_percentage'] }}% off @ {{ $stats['settings']['discount_threshold'] }} · free @ {{ $stats['settings']['teacher_free_threshold'] }}/{{ $stats['settings']['school_free_threshold'] }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Pending school approvals --}}
    @if($pendingSchools->isNotEmpty())
        <div class="bg-white rounded-xl shadow-sm border border-orange-200 overflow-hidden">
            <div class="px-6 py-4 bg-orange-50 border-b border-orange-100 flex items-center gap-2">
                <i data-lucide="alert-circle" class="w-5 h-5 text-orange-600"></i>
                <h3 class="font-semibold text-gray-800">Schools awaiting free-access approval</h3>
            </div>
            <div class="divide-y divide-gray-100">
                @foreach($pendingSchools as $benefit)
                    <div class="px-6 py-4 flex flex-wrap items-center justify-between gap-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-orange-100 flex items-center justify-center text-orange-700 font-bold">
                                {{ strtoupper(substr($benefit->user->name ?? '?', 0, 1)) }}
                            </div>
                            <div>
                                <div class="font-medium text-gray-800">{{ $benefit->user->name ?? 'N/A' }}</div>
                                <div class="text-xs text-gray-400">{{ $benefit->user->email ?? '' }}</div>
                            </div>
                        </div>
                        <div class="text-sm text-gray-600">
                            <span class="font-semibold text-gray-800">{{ $benefit->qualifying_student_count }}</span> Premium students
                            <span class="text-gray-400">· requested {{ $benefit->created_at?->diffForHumans() }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <form method="POST" action="{{ route('admin.incentives.approve', $benefit) }}">
                                @csrf
                                <button type="submit" class="inline-flex items-center gap-1.5 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition text-sm font-medium">
                                    <i data-lucide="check" class="w-4 h-4"></i> Approve
                                </button>
                            </form>
                            <form method="POST" action="{{ route('admin.incentives.revoke', $benefit) }}"
                                  onsubmit="return confirm('Reject this school\'s free-access request?')">
                                @csrf
                                <button type="submit" class="inline-flex items-center gap-1.5 px-4 py-2 bg-white border border-gray-300 text-gray-600 rounded-lg hover:bg-gray-50 transition text-sm font-medium">
                                    <i data-lucide="x" class="w-4 h-4"></i> Reject
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Filters --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
        <form method="GET" action="{{ route('admin.incentives.index') }}" class="flex flex-wrap items-end gap-4">
            <div class="flex-1 min-w-[200px]">
                <label class="block text-xs font-medium text-gray-500 mb-1">Search User</label>
                <div class="relative">
                    <i data-lucide="search" class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Name or email..."
                        class="w-full pl-9 rounded-lg border-gray-300 focus:border-purple-500 focus:ring-purple-500 text-sm">
                </div>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Role</label>
                <select name="role" class="rounded-lg border-gray-300 focus:border-purple-500 focus:ring-purple-500 text-sm">
                    <option value="">All</option>
                    <option value="teacher" {{ request('role') === 'teacher' ? 'selected' : '' }}>Teachers</option>
                    <option value="school" {{ request('role') === 'school' ? 'selected' : '' }}>Schools</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Type</label>
                <select name="type" class="rounded-lg border-gray-300 focus:border-purple-500 focus:ring-purple-500 text-sm">
                    <option value="">All</option>
                    <option value="free_period" {{ request('type') === 'free_period' ? 'selected' : '' }}>Free Period</option>
                    <option value="discount" {{ request('type') === 'discount' ? 'selected' : '' }}>Discount</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Status</label>
                <select name="status" class="rounded-lg border-gray-300 focus:border-purple-500 focus:ring-purple-500 text-sm">
                    <option value="">All</option>
                    @foreach($statusLabels as $val => $label)
                        <option value="{{ $val }}" {{ request('status') === $val ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition text-sm">
                <i data-lucide="filter" class="w-4 h-4"></i> Filter
            </button>
            <a href="{{ route('admin.incentives.index') }}" class="px-4 py-2 text-gray-500 hover:text-gray-700 transition text-sm">Reset</a>
        </form>
    </div>

    {{-- Benefits table --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Account</th>
                        <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Role</th>
                        <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Benefit</th>
                        <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Students</th>
                        <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Status</th>
                        <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Ends</th>
                        <th class="text-right px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($benefits as $benefit)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4">
                                <div class="font-medium text-gray-800">{{ $benefit->user->name ?? 'N/A' }}</div>
                                <div class="text-xs text-gray-400">{{ $benefit->user->email ?? '' }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 text-xs font-medium rounded-full {{ ($benefit->user->role ?? '') === 'school' ? 'bg-blue-100 text-blue-700' : 'bg-indigo-100 text-indigo-700' }}">
                                    {{ ucfirst($benefit->user->role ?? '—') }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-gray-600">
                                @if($benefit->type === Benefit::TYPE_FREE_PERIOD)
                                    <span class="inline-flex items-center gap-1 text-green-700 font-medium"><i data-lucide="gift" class="w-4 h-4"></i> Free period</span>
                                @else
                                    <span class="inline-flex items-center gap-1 text-purple-700 font-medium"><i data-lucide="percent" class="w-4 h-4"></i> {{ $benefit->discount_percentage }}% off</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-gray-600">{{ $benefit->qualifying_student_count }}</td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 text-xs font-medium rounded-full {{ $statusColors[$benefit->status] ?? 'bg-gray-100 text-gray-600' }}">
                                    {{ $statusLabels[$benefit->status] ?? ucfirst($benefit->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-gray-500">{{ $benefit->ends_at?->format('M d, Y') ?? '—' }}</td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    @if($benefit->status === Benefit::STATUS_PENDING)
                                        <form method="POST" action="{{ route('admin.incentives.approve', $benefit) }}">
                                            @csrf
                                            <button type="submit" class="inline-flex items-center gap-1 px-3 py-1.5 bg-green-600 text-white rounded-lg hover:bg-green-700 transition text-xs font-medium">
                                                <i data-lucide="check" class="w-3.5 h-3.5"></i> Approve
                                            </button>
                                        </form>
                                    @endif
                                    @if(in_array($benefit->status, [Benefit::STATUS_ACTIVE, Benefit::STATUS_PENDING], true))
                                        <form method="POST" action="{{ route('admin.incentives.revoke', $benefit) }}"
                                              onsubmit="return confirm('Revoke this benefit?')">
                                            @csrf
                                            <button type="submit" class="inline-flex items-center gap-1 px-3 py-1.5 bg-white border border-gray-300 text-gray-600 rounded-lg hover:bg-red-50 hover:text-red-600 hover:border-red-200 transition text-xs font-medium">
                                                <i data-lucide="x" class="w-3.5 h-3.5"></i> Revoke
                                            </button>
                                        </form>
                                    @endif
                                    <form method="POST" action="{{ route('admin.incentives.recalculate', $benefit->user) }}" title="Recalculate eligibility">
                                        @csrf
                                        <button type="submit" class="p-1.5 text-gray-400 hover:text-purple-600 rounded-lg hover:bg-purple-50 transition">
                                            <i data-lucide="refresh-cw" class="w-4 h-4"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-gray-400">
                                <i data-lucide="gift" class="w-10 h-10 mx-auto mb-2 opacity-50"></i>
                                <p>No incentive grants yet.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($benefits->hasPages())
            <div class="px-6 py-4 border-t border-gray-100">
                {{ $benefits->withQueryString()->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
