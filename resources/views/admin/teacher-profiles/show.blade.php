@extends('admin.layouts.admin')

@section('page-title', $profile->displayName())

@section('content')
<div class="max-w-5xl">

    <a href="{{ route('admin.teacher-profiles.index') }}" class="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-primary-600 mb-4">
        <i data-lucide="arrow-left" class="w-4 h-4"></i> {{ __('teacher.admin.queue') }}
    </a>

    <div class="grid lg:grid-cols-3 gap-6">
        {{-- Profile summary --}}
        <div class="lg:col-span-2 space-y-6">
            <div class="card p-6">
                <div class="flex items-start justify-between gap-4">
                    <div class="flex items-center gap-4">
                        @if($profile->user->hasAvatar())
                            <img src="{{ $profile->user->avatar }}" class="w-16 h-16 rounded-xl object-cover" alt="">
                        @else
                            <div class="w-16 h-16 rounded-xl bg-primary-100 text-primary-700 flex items-center justify-center text-2xl font-bold">{{ strtoupper(substr($profile->user->name, 0, 1)) }}</div>
                        @endif
                        <div>
                            <h2 class="text-lg font-bold text-gray-900">{{ $profile->displayName() }}</h2>
                            <p class="text-sm text-gray-500">{{ $profile->expertise ?: '—' }} · {{ $profile->user->email }}</p>
                            <p class="text-xs text-gray-400 mt-0.5">/teachers/{{ $profile->slug }} · {{ number_format($profile->view_count) }} views</p>
                        </div>
                    </div>
                    <span class="px-3 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-700 shrink-0">{{ __('teacher.status.'.$profile->status) }}</span>
                </div>

                <div class="grid sm:grid-cols-2 gap-x-6 gap-y-3 mt-6 text-sm">
                    <div><span class="text-gray-400">Headline:</span> <span class="text-gray-800">{{ $profile->headline ?: '—' }}</span></div>
                    <div><span class="text-gray-400">Location:</span> <span class="text-gray-800">{{ implode(', ', array_filter([$profile->city, $profile->country])) ?: '—' }}</span></div>
                    <div><span class="text-gray-400">Lessons:</span> <span class="text-gray-800">{{ implode(', ', $profile->lesson_types ?? []) ?: '—' }}</span></div>
                    <div><span class="text-gray-400">Languages:</span> <span class="text-gray-800">{{ implode(', ', $profile->languages ?? []) ?: '—' }}</span></div>
                    <div><span class="text-gray-400">Primary instrument:</span> <span class="text-gray-800">{{ $profile->primary_instrument ?: '—' }}</span></div>
                    <div><span class="text-gray-400">Experience:</span> <span class="text-gray-800">{{ $profile->experience_years !== null ? $profile->experience_years.' yrs' : '—' }}</span></div>
                    <div><span class="text-gray-400">Public email:</span> <span class="text-gray-800">{{ $profile->show_email ? ($profile->public_email ?: '—') : 'hidden' }}</span></div>
                    <div><span class="text-gray-400">Public phone:</span> <span class="text-gray-800">{{ $profile->show_phone ? ($profile->public_phone ?: '—') : 'hidden' }}</span></div>
                </div>

                @if($profile->about)
                    <div class="mt-5 pt-5 border-t border-gray-100">
                        <p class="text-xs font-semibold text-gray-400 uppercase mb-2">About</p>
                        <p class="text-sm text-gray-600 whitespace-pre-line">{{ $profile->about }}</p>
                    </div>
                @endif

                <div class="mt-5 pt-5 border-t border-gray-100 flex flex-wrap gap-4 text-xs text-gray-500">
                    <span>{{ $profile->services->count() }} services</span>
                    <span>{{ $profile->videos->count() }} videos</span>
                    <span>{{ $profile->media->count() }} files ({{ $profile->media->where('visibility', 'public')->count() }} public)</span>
                    <span>{{ $profile->paymentLinks->count() }} payment links</span>
                    <span>{{ $profile->educations->count() }} education entries</span>
                </div>

                <div class="mt-4">
                    <a href="{{ route('teachers.show', $profile->slug) }}" target="_blank" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-primary-700 bg-primary-50 hover:bg-primary-100 rounded-lg transition">
                        <i data-lucide="external-link" class="w-4 h-4"></i> {{ __('teacher.admin.preview_public') }}
                    </a>
                </div>
            </div>

            {{-- Payment links review --}}
            @if($profile->paymentLinks->isNotEmpty())
                <div class="card p-6">
                    <h3 class="font-semibold text-gray-900 mb-3">{{ __('teacher.payment_links.title') }}</h3>
                    <div class="space-y-2">
                        @foreach($profile->paymentLinks as $link)
                            <div class="flex items-center justify-between gap-2 text-sm">
                                <span class="font-medium text-gray-800">{{ $link->label }}</span>
                                <span class="text-xs text-gray-400 truncate">{{ $link->url }} · {{ $link->visibility }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Moderation history --}}
            <div class="card p-6">
                <h3 class="font-semibold text-gray-900 mb-4">{{ __('teacher.admin.history') }}</h3>
                <div class="space-y-3">
                    @forelse($profile->moderationLogs as $log)
                        <div class="flex items-start gap-3 text-sm">
                            <div class="w-7 h-7 rounded-full bg-gray-100 flex items-center justify-center shrink-0 mt-0.5">
                                <i data-lucide="history" class="w-3.5 h-3.5 text-gray-500"></i>
                            </div>
                            <div>
                                <p class="text-gray-800">
                                    <span class="font-semibold">{{ $log->admin?->name ?? 'Teacher' }}</span>
                                    — {{ str_replace('_', ' ', $log->action) }}
                                    @if($log->from_status && $log->from_status !== $log->to_status)
                                        <span class="text-xs text-gray-400">({{ $log->from_status }} → {{ $log->to_status }})</span>
                                    @endif
                                </p>
                                @if($log->notes)<p class="text-xs text-gray-500 mt-0.5">{{ $log->notes }}</p>@endif
                                <p class="text-xs text-gray-400 mt-0.5">{{ $log->created_at->format('d.m.Y H:i') }}</p>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-gray-400">—</p>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Actions column --}}
        <div class="space-y-6">
            {{-- Moderation actions --}}
            <div class="card p-6 space-y-3">
                <h3 class="font-semibold text-gray-900">{{ __('teacher.admin.queue') }}</h3>

                @if($profile->status !== \App\Models\TeacherProfile::STATUS_APPROVED)
                    <form method="POST" action="{{ route('admin.teacher-profiles.approve', $profile) }}">
                        @csrf
                        <button type="submit" class="w-full inline-flex items-center justify-center gap-2 px-4 py-2 text-sm font-semibold text-white bg-green-600 hover:bg-green-700 rounded-lg transition">
                            <i data-lucide="check" class="w-4 h-4"></i> {{ __('teacher.admin.approve') }}
                        </button>
                    </form>
                @endif

                <form method="POST" action="{{ route('admin.teacher-profiles.reject', $profile) }}">
                    @csrf
                    <textarea name="reason" rows="2" required placeholder="{{ __('teacher.admin.reject_reason') }}"
                              class="w-full rounded-lg border-gray-300 text-sm mb-2 focus:ring-primary-500 focus:border-primary-500"></textarea>
                    <button type="submit" class="w-full inline-flex items-center justify-center gap-2 px-4 py-2 text-sm font-semibold text-red-700 bg-red-50 hover:bg-red-100 rounded-lg transition">
                        <i data-lucide="x" class="w-4 h-4"></i> {{ __('teacher.admin.reject') }}
                    </button>
                </form>

                @if($profile->status === \App\Models\TeacherProfile::STATUS_APPROVED)
                    <form method="POST" action="{{ route('admin.teacher-profiles.suspend', $profile) }}">
                        @csrf
                        <button type="submit" class="w-full inline-flex items-center justify-center gap-2 px-4 py-2 text-sm font-semibold text-amber-700 bg-amber-50 hover:bg-amber-100 rounded-lg transition">
                            <i data-lucide="pause" class="w-4 h-4"></i> {{ __('teacher.admin.suspend') }}
                        </button>
                    </form>

                    <form method="POST" action="{{ route('admin.teacher-profiles.force-private', $profile) }}">
                        @csrf
                        <input type="hidden" name="private" value="{{ $profile->admin_forced_private ? 0 : 1 }}">
                        <button type="submit" class="w-full inline-flex items-center justify-center gap-2 px-4 py-2 text-sm font-semibold text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition">
                            <i data-lucide="{{ $profile->admin_forced_private ? 'eye' : 'eye-off' }}" class="w-4 h-4"></i>
                            {{ $profile->admin_forced_private ? __('teacher.admin.make_public') : __('teacher.admin.force_private') }}
                        </button>
                    </form>
                @endif

                @if($profile->status === \App\Models\TeacherProfile::STATUS_SUSPENDED)
                    <form method="POST" action="{{ route('admin.teacher-profiles.reinstate', $profile) }}">
                        @csrf
                        <button type="submit" class="w-full inline-flex items-center justify-center gap-2 px-4 py-2 text-sm font-semibold text-green-700 bg-green-50 hover:bg-green-100 rounded-lg transition">
                            <i data-lucide="play" class="w-4 h-4"></i> {{ __('teacher.admin.reinstate') }}
                        </button>
                    </form>
                @endif
            </div>

            {{-- Tier --}}
            <div class="card p-6">
                <h3 class="font-semibold text-gray-900 mb-3">{{ __('teacher.admin.tier') }}</h3>
                <form method="POST" action="{{ route('admin.teacher-profiles.tier', $profile) }}" class="flex items-center gap-2">
                    @csrf
                    @method('PATCH')
                    <select name="tier" class="flex-1 rounded-lg border-gray-300 text-sm focus:ring-primary-500 focus:border-primary-500">
                        <option value="basic" @selected($profile->tier === 'basic')>Basic</option>
                        <option value="premium" @selected($profile->tier === 'premium')>Premium</option>
                    </select>
                    <button type="submit" class="px-4 py-2 text-sm font-semibold text-white bg-primary-600 hover:bg-primary-700 rounded-lg transition">{{ __('teacher.admin.update_tier') }}</button>
                </form>
            </div>

            {{-- Benefits --}}
            <div class="card p-6">
                <h3 class="font-semibold text-gray-900 mb-3">{{ __('teacher.admin.benefits') }}</h3>
                <p class="text-sm text-gray-600 mb-1">{{ __('teacher.dashboard.eligible_students') }}: <span class="font-semibold">{{ $eligibleStudentCount }}</span></p>
                @if($activeBenefit)
                    <p class="text-sm text-green-700 bg-green-50 rounded-lg px-3 py-2 mb-3">
                        {{ $activeBenefit->type === 'discount'
                            ? __('teacher.dashboard.active_benefit_discount', ['percentage' => $activeBenefit->discount_percentage])
                            : __('teacher.dashboard.active_benefit_free', ['date' => $activeBenefit->ends_at?->format('d.m.Y')]) }}
                    </p>
                @else
                    <p class="text-sm text-gray-400 mb-3">{{ __('teacher.dashboard.no_active_benefit') }}</p>
                @endif
                <form method="POST" action="{{ route('admin.teacher-profiles.recalculate-benefits', $profile) }}">
                    @csrf
                    <button type="submit" class="w-full px-4 py-2 text-sm font-semibold text-primary-700 bg-primary-50 hover:bg-primary-100 rounded-lg transition">{{ __('teacher.admin.recalculate') }}</button>
                </form>
            </div>

            {{-- Internal note --}}
            <div class="card p-6">
                <h3 class="font-semibold text-gray-900 mb-3">{{ __('teacher.admin.add_note') }}</h3>
                <form method="POST" action="{{ route('admin.teacher-profiles.notes', $profile) }}">
                    @csrf
                    <textarea name="notes" rows="2" required placeholder="{{ __('teacher.admin.notes') }}"
                              class="w-full rounded-lg border-gray-300 text-sm mb-2 focus:ring-primary-500 focus:border-primary-500"></textarea>
                    <button type="submit" class="w-full px-4 py-2 text-sm font-semibold text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition">{{ __('teacher.admin.add_note') }}</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
