@extends('teacher.layouts.crm')

@section('title', __('teacher.students.title'))

@section('content')
@php
    $statusMessages = [
        'relationship-requested' => __('teacher.students.status_relationship-requested'),
        'invitation-sent' => __('teacher.students.status_invitation-sent'),
        'invitation-link-created' => __('teacher.students.status_invitation-link-created'),
        'invitation-revoked' => __('teacher.students.status_invitation-revoked'),
        'relationship-revoked' => __('teacher.students.status_relationship-revoked'),
    ];
@endphp
@if (session('status') && isset($statusMessages[session('status')]))
    <div class="mb-6 px-4 py-3 bg-green-50 border border-green-200 rounded-xl flex items-center gap-3">
        <i data-lucide="check-circle" class="w-5 h-5 text-green-600"></i>
        <p class="text-sm text-green-700">{{ $statusMessages[session('status')] }}</p>
    </div>
@endif

<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6" x-data>
    <div>
        <h1 class="text-2xl font-bold text-gray-900">{{ __('teacher.students.title') }}</h1>
        <p class="text-gray-500 text-sm mt-1">{{ __('teacher.students.subtitle') }}</p>
    </div>
    <button @click="$dispatch('open-invite')" class="inline-flex items-center gap-2 px-5 py-2.5 bg-primary-600 hover:bg-primary-700 text-white font-semibold rounded-lg transition">
        <i data-lucide="user-plus" class="w-4 h-4"></i> {{ __('teacher.students.add_student') }}
    </button>
</div>

{{-- Filters --}}
@if($classes->isNotEmpty() || $tags->isNotEmpty())
<form method="GET" class="card p-4 mb-6 flex flex-wrap items-end gap-4">
    @if($classes->isNotEmpty())
        <div>
            <label class="block text-xs font-semibold text-gray-500 mb-1">{{ __('teacher.students.filter_class') }}</label>
            <select name="class" class="rounded-lg border-gray-300 text-sm" onchange="this.form.submit()">
                <option value="">{{ __('teacher.students.filter_all') }}</option>
                @foreach($classes as $c)
                    <option value="{{ $c->id }}" @selected($filterClass === $c->id)>{{ $c->name }}</option>
                @endforeach
            </select>
        </div>
    @endif
    @if($tags->isNotEmpty())
        <div>
            <label class="block text-xs font-semibold text-gray-500 mb-1">{{ __('teacher.students.filter_tag') }}</label>
            <select name="tag" class="rounded-lg border-gray-300 text-sm" onchange="this.form.submit()">
                <option value="">{{ __('teacher.students.filter_all') }}</option>
                @foreach($tags as $t)
                    <option value="{{ $t->id }}" @selected($filterTag === $t->id)>{{ $t->name }}</option>
                @endforeach
            </select>
        </div>
    @endif
</form>
@endif

{{-- Student list --}}
<div class="card overflow-hidden mb-8">
    @if($relationships->isEmpty())
        <div class="p-10 text-center text-gray-500">
            <i data-lucide="users" class="w-10 h-10 mx-auto mb-3 text-gray-300"></i>
            <p class="text-sm">{{ __('teacher.students.no_students') }}</p>
        </div>
    @else
        <ul class="divide-y divide-gray-100">
            @foreach($relationships as $rel)
                <li class="flex items-center gap-4 px-5 py-4">
                    @if($rel->student->hasAvatar())
                        <img src="{{ $rel->student->avatar }}" class="w-11 h-11 rounded-full object-cover" alt="">
                    @else
                        <div class="w-11 h-11 rounded-full bg-primary-100 text-primary-700 flex items-center justify-center font-bold">
                            {{ strtoupper(substr($rel->student->name, 0, 1)) }}
                        </div>
                    @endif
                    <div class="flex-1 min-w-0">
                        <p class="font-semibold text-gray-900 truncate">{{ $rel->student->name }} {{ $rel->student->surname }}</p>
                        @if($rel->isActive())
                            <p class="text-xs text-gray-500">{{ __('teacher.students.active_since', ['date' => $rel->approved_at?->format('M j, Y')]) }}</p>
                        @else
                            <p class="text-xs text-amber-600 font-medium">{{ __('teacher.students.pending_approval') }}</p>
                        @endif
                    </div>
                    @if($rel->isActive())
                        <a href="{{ route('teacher.students.show', $rel->student) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-semibold text-primary-700 bg-primary-50 hover:bg-primary-100 rounded-lg transition">
                            <i data-lucide="eye" class="w-4 h-4"></i> {{ __('teacher.students.view_profile') }}
                        </a>
                    @endif
                    <form method="POST" action="{{ route('teacher.relationships.destroy', $rel) }}" onsubmit="return confirm(@js(__('teacher.students.remove_confirm')))">
                        @csrf @method('DELETE')
                        <button class="p-2 text-gray-400 hover:text-red-600 transition" title="{{ __('teacher.students.remove_student') }}">
                            <i data-lucide="user-x" class="w-4 h-4"></i>
                        </button>
                    </form>
                </li>
            @endforeach
        </ul>
    @endif
</div>

{{-- Pending invitations --}}
<h2 class="text-lg font-bold text-gray-900 mb-3">{{ __('teacher.students.pending_invitations') }}</h2>
<div class="card overflow-hidden">
    @if($invitations->isEmpty())
        <div class="p-6 text-sm text-gray-500">{{ __('teacher.students.no_invitations') }}</div>
    @else
        <ul class="divide-y divide-gray-100">
            @foreach($invitations as $inv)
                <li class="flex items-center gap-4 px-5 py-3.5">
                    <i data-lucide="{{ $inv->type === 'email' ? 'mail' : 'link' }}" class="w-4 h-4 text-gray-400 shrink-0"></i>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-800 truncate">
                            {{ $inv->type === 'email' ? ($inv->name ? $inv->name.' — ' : '').$inv->email : $inv->acceptUrl() }}
                        </p>
                        <p class="text-xs text-gray-400">
                            {{ $inv->created_at->format('M j, Y') }}
                            @if($inv->expires_at) · {{ __('teacher.students.link_expires') }}: {{ $inv->expires_at->format('M j, Y') }} @endif
                            @if($inv->teacherClass) · {{ $inv->teacherClass->name }} @endif
                        </p>
                    </div>
                    @if($inv->type === 'link')
                        <button type="button" onclick="navigator.clipboard.writeText(@js($inv->acceptUrl())); this.innerText=@js(__('teacher.students.copy_link')) + ' ✓'" class="text-xs font-semibold text-primary-700 bg-primary-50 hover:bg-primary-100 px-2.5 py-1.5 rounded-lg transition">
                            {{ __('teacher.students.copy_link') }}
                        </button>
                    @endif
                    <form method="POST" action="{{ route('teacher.invitations.destroy', $inv) }}">
                        @csrf @method('DELETE')
                        <button class="text-xs font-semibold text-red-600 hover:text-red-700 px-2 py-1.5">{{ __('teacher.students.revoke') }}</button>
                    </form>
                </li>
            @endforeach
        </ul>
    @endif
</div>

{{-- Invite modal --}}
<div x-data="inviteModal()" @open-invite.window="open = true" x-show="open" x-cloak class="fixed inset-0 z-50 flex items-start justify-center p-4 pt-20">
    <div class="fixed inset-0 bg-black/40" @click="open = false"></div>
    <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-lg p-6 max-h-[80vh] overflow-y-auto">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold text-gray-900">{{ __('teacher.students.add_student') }}</h3>
            <button @click="open = false" class="p-1 text-gray-400 hover:text-gray-600"><i data-lucide="x" class="w-5 h-5"></i></button>
        </div>

        <div class="flex gap-2 mb-5">
            <button @click="tab = 'search'" :class="tab === 'search' ? 'bg-primary-600 text-white' : 'bg-gray-100 text-gray-600'" class="px-3 py-1.5 text-xs font-semibold rounded-lg transition">{{ __('teacher.students.search_users') }}</button>
            <button @click="tab = 'email'" :class="tab === 'email' ? 'bg-primary-600 text-white' : 'bg-gray-100 text-gray-600'" class="px-3 py-1.5 text-xs font-semibold rounded-lg transition">{{ __('teacher.students.invite_by_email') }}</button>
            <button @click="tab = 'link'" :class="tab === 'link' ? 'bg-primary-600 text-white' : 'bg-gray-100 text-gray-600'" class="px-3 py-1.5 text-xs font-semibold rounded-lg transition">{{ __('teacher.students.share_link') }}</button>
        </div>

        {{-- A: search existing users --}}
        <div x-show="tab === 'search'">
            <label class="block text-xs font-semibold text-gray-500 mb-1">{{ __('teacher.students.search_placeholder') }}</label>
            <div class="flex gap-2">
                <input type="text" x-model="query" @keydown.enter.prevent="search" class="flex-1 rounded-lg border-gray-300 text-sm">
                <button @click="search" class="px-4 py-2 bg-gray-800 text-white text-sm font-semibold rounded-lg">🔍</button>
            </div>
            <div class="mt-3 space-y-2">
                <template x-for="u in results" :key="u.id">
                    <div class="flex items-center gap-3 p-2.5 bg-gray-50 rounded-lg">
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold text-gray-800" x-text="u.name"></p>
                            <p class="text-xs text-gray-400" x-text="u.email_hint"></p>
                        </div>
                        <template x-if="!u.relationship_status || ['declined','revoked_by_teacher','revoked_by_student','archived'].includes(u.relationship_status)">
                            <form method="POST" action="{{ route('teacher.relationships.store') }}">
                                @csrf
                                <input type="hidden" name="user_id" :value="u.id">
                                <button class="px-3 py-1.5 text-xs font-semibold text-white bg-primary-600 hover:bg-primary-700 rounded-lg">{{ __('teacher.students.send_request') }}</button>
                            </form>
                        </template>
                        <template x-if="u.relationship_status && !['declined','revoked_by_teacher','revoked_by_student','archived'].includes(u.relationship_status)">
                            <span class="text-xs text-gray-400 font-medium" x-text="u.relationship_status"></span>
                        </template>
                    </div>
                </template>
                <p x-show="searched && results.length === 0" class="text-sm text-gray-400">—</p>
            </div>
        </div>

        {{-- B: email invitation --}}
        <form x-show="tab === 'email'" method="POST" action="{{ route('teacher.invitations.store') }}" class="space-y-3">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">{{ __('teacher.students.invite_name') }}</label>
                <input type="text" name="name" class="w-full rounded-lg border-gray-300 text-sm" value="{{ old('name') }}">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">{{ __('teacher.students.invite_email') }}</label>
                <input type="email" name="email" required class="w-full rounded-lg border-gray-300 text-sm" value="{{ old('email') }}">
            </div>
            @if($classes->isNotEmpty())
                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1">{{ __('teacher.students.preassign_class') }}</label>
                    <select name="teacher_class_id" class="w-full rounded-lg border-gray-300 text-sm">
                        <option value="">—</option>
                        @foreach($classes as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach
                    </select>
                </div>
            @endif
            <button class="w-full py-2.5 bg-primary-600 hover:bg-primary-700 text-white font-semibold rounded-lg transition">{{ __('teacher.students.send_invitation') }}</button>
        </form>

        {{-- C: shareable link --}}
        <form x-show="tab === 'link'" method="POST" action="{{ route('teacher.invitations.link') }}" class="space-y-3">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">{{ __('teacher.students.link_expires') }}</label>
                <input type="date" name="expires_at" class="w-full rounded-lg border-gray-300 text-sm" min="{{ now()->addDay()->format('Y-m-d') }}">
            </div>
            @if($classes->isNotEmpty())
                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1">{{ __('teacher.students.preassign_class') }}</label>
                    <select name="teacher_class_id" class="w-full rounded-lg border-gray-300 text-sm">
                        <option value="">—</option>
                        @foreach($classes as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach
                    </select>
                </div>
            @endif
            <button class="w-full py-2.5 bg-primary-600 hover:bg-primary-700 text-white font-semibold rounded-lg transition">{{ __('teacher.students.create_link') }}</button>
        </form>
    </div>
</div>

@push('scripts')
<script>
function inviteModal() {
    return {
        open: {{ $errors->any() ? 'true' : 'false' }},
        tab: 'search',
        query: '',
        results: [],
        searched: false,
        async search() {
            if (this.query.trim().length < 3) return;
            const res = await fetch(`{{ route('teacher.students.search') }}?q=${encodeURIComponent(this.query.trim())}`, {
                headers: { 'Accept': 'application/json' }
            });
            this.results = res.ok ? await res.json() : [];
            this.searched = true;
        }
    };
}
</script>
@endpush
@endsection
