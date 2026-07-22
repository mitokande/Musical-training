@extends('admin.layouts.admin')

@section('page-title', __('teacher.admin.title'))

@section('content')
<div class="max-w-6xl">

    {{-- Status filter tabs --}}
    @php
        $tabs = [
            'submitted_for_review' => __('teacher.status.submitted_for_review'),
            'approved' => __('teacher.status.approved'),
            'rejected' => __('teacher.status.rejected'),
            'suspended' => __('teacher.status.suspended'),
            'draft' => __('teacher.status.draft'),
            'all' => __('teacher.admin.all'),
        ];
    @endphp
    <div class="flex flex-wrap gap-2 mb-3">
        @foreach($tabs as $key => $label)
            <a href="{{ route('admin.teacher-profiles.index', ['status' => $key, 'entity' => $entity]) }}"
               class="px-4 py-2 rounded-lg text-sm font-semibold transition {{ $status === $key ? 'bg-primary-600 text-white' : 'bg-white text-gray-600 hover:bg-gray-50 border border-gray-200' }}">
                {{ $label }}
                @if($key !== 'all' && ($counts[$key] ?? 0) > 0)
                    <span class="ml-1 px-1.5 py-0.5 rounded-full text-[10px] {{ $status === $key ? 'bg-white/20' : 'bg-gray-100 text-gray-500' }}">{{ $counts[$key] }}</span>
                @endif
            </a>
        @endforeach
    </div>

    {{-- Entity filter: teachers vs music schools --}}
    <div class="flex flex-wrap gap-2 mb-6">
        @foreach(['all' => __('teacher.admin.all'), 'teacher' => __('teacher.nav.role_teacher'), 'school' => __('school.admin.entity_school')] as $key => $label)
            <a href="{{ route('admin.teacher-profiles.index', ['status' => $status, 'entity' => $key]) }}"
               class="px-3 py-1.5 rounded-lg text-xs font-semibold transition {{ $entity === $key ? 'bg-gray-800 text-white' : 'bg-white text-gray-500 hover:bg-gray-50 border border-gray-200' }}">
                {{ $label }}
                @if($key !== 'all' && ($entityCounts[$key] ?? 0) > 0)
                    <span class="ml-1 px-1.5 py-0.5 rounded-full text-[10px] {{ $entity === $key ? 'bg-white/20' : 'bg-gray-100 text-gray-500' }}">{{ $entityCounts[$key] }}</span>
                @endif
            </a>
        @endforeach
    </div>

    <div class="card overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-left text-xs text-gray-500 uppercase">
                <tr>
                    <th class="px-5 py-3">Teacher</th>
                    <th class="px-5 py-3">Tier</th>
                    <th class="px-5 py-3">Status</th>
                    <th class="px-5 py-3">{{ __('teacher.admin.submitted_at') }}</th>
                    <th class="px-5 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($profiles as $profile)
                    <tr class="hover:bg-gray-50">
                        <td class="px-5 py-3">
                            <div class="flex items-center gap-3">
                                @if($profile->user->hasAvatar())
                                    <img src="{{ $profile->user->avatar }}" class="w-9 h-9 rounded-full object-cover" alt="">
                                @else
                                    <div class="w-9 h-9 rounded-full bg-primary-100 text-primary-700 flex items-center justify-center text-sm font-bold">{{ strtoupper(substr($profile->user->name, 0, 1)) }}</div>
                                @endif
                                <div>
                                    <p class="font-semibold text-gray-900">
                                        {{ $profile->displayName() }}
                                        @if($profile->isSchoolEntity())
                                            <span class="ml-1 px-1.5 py-0.5 rounded-full text-[10px] font-semibold bg-teal-50 text-teal-700">{{ __('school.admin.entity_school') }}</span>
                                        @endif
                                    </p>
                                    <p class="text-xs text-gray-400">{{ $profile->user->email }} · /{{ $profile->isSchoolEntity() ? 'schools' : 'teachers' }}/{{ $profile->slug }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-5 py-3">
                            <span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $profile->tier === 'premium' ? 'bg-amber-100 text-amber-700' : 'bg-gray-100 text-gray-600' }}">
                                {{ ucfirst($profile->tier) }}
                            </span>
                        </td>
                        <td class="px-5 py-3">
                            <span class="text-xs font-semibold text-gray-600">{{ __('teacher.status.'.$profile->status) }}</span>
                            @if($profile->admin_forced_private)
                                <span class="block text-[10px] text-red-500">{{ __('teacher.status.hidden_by_admin') }}</span>
                            @endif
                        </td>
                        <td class="px-5 py-3 text-xs text-gray-500">{{ $profile->submitted_at?->format('d.m.Y H:i') ?? '—' }}</td>
                        <td class="px-5 py-3 text-right">
                            <a href="{{ route('admin.teacher-profiles.show', $profile) }}" class="inline-flex items-center gap-1 text-primary-600 hover:text-primary-700 font-semibold text-xs">
                                Review <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-5 py-10 text-center text-sm text-gray-400">{{ __('teacher.admin.no_profiles') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $profiles->links() }}</div>
</div>
@endsection
