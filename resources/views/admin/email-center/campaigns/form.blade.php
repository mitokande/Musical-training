@extends('admin.layouts.admin')
@section('page-title', $campaign->exists ? 'Edit Campaign' : 'New Campaign')

@section('content')
<div class="space-y-6 max-w-4xl">
    <div class="flex items-center justify-between">
        <h2 class="text-2xl font-bold text-gray-800">{{ $campaign->exists ? 'Edit Campaign' : 'New Campaign' }}</h2>
        <a href="{{ route('admin.email-campaigns.index') }}" class="text-sm text-gray-500 hover:text-gray-700">← Back to campaigns</a>
    </div>

    @if ($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-700 text-sm rounded-lg px-4 py-3">
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    @endif

    <form method="POST"
          action="{{ $campaign->exists ? route('admin.email-campaigns.update', $campaign) : route('admin.email-campaigns.store') }}"
          class="space-y-6">
        @csrf
        @if ($campaign->exists) @method('PUT') @endif

        {{-- Content --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-4">
            <h3 class="font-semibold text-gray-800">Content</h3>
            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Campaign Name</label>
                    <input type="text" name="name" value="{{ old('name', $campaign->name) }}" required
                           class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Template</label>
                    <select name="template_id" class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                        <option value="">— Custom HTML below —</option>
                        @foreach ($templates as $template)
                            <option value="{{ $template->id }}" @selected(old('template_id', $campaign->template_id) == $template->id)>{{ $template->name }} ({{ $template->category }})</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Subject</label>
                <input type="text" name="subject" value="{{ old('subject', $campaign->subject) }}" required
                       class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                       placeholder="You can use @{{user_first_name}} variables">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Preheader <span class="text-gray-400">(optional)</span></label>
                <input type="text" name="preheader" value="{{ old('preheader', $campaign->preheader) }}"
                       class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Custom HTML <span class="text-gray-400">(used when no template is selected)</span></label>
                <textarea name="custom_html" rows="8" class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 text-sm font-mono">{{ old('custom_html', $campaign->custom_html) }}</textarea>
                <p class="text-xs text-gray-400 mt-1">Variables: @{{user_first_name}}, @{{user_name}}, @{{user_email}}, @{{app_name}}, @{{app_url}}, @{{unsubscribe_url}}. An unsubscribe footer is added automatically if missing.</p>
            </div>
        </div>

        {{-- Segment --}}
        @php $segment = old('segment', $campaign->segment ?? []); @endphp
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-4" id="segment-box">
            <div class="flex items-center justify-between">
                <h3 class="font-semibold text-gray-800">Audience Segment</h3>
                <span class="text-sm text-gray-500">Estimated recipients: <span id="segment-count" class="font-semibold text-indigo-600">…</span></span>
            </div>
            <div class="grid md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Plans</label>
                    @foreach (['free', 'premium'] as $plan)
                        <label class="flex items-center gap-2 text-sm text-gray-600">
                            <input type="checkbox" name="segment[plans][]" value="{{ $plan }}" class="rounded border-gray-300 text-indigo-600 seg-input"
                                   @checked(in_array($plan, $segment['plans'] ?? []))> {{ ucfirst($plan) }}
                        </label>
                    @endforeach
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Roles</label>
                    @foreach (['user', 'teacher', 'school'] as $role)
                        <label class="flex items-center gap-2 text-sm text-gray-600">
                            <input type="checkbox" name="segment[roles][]" value="{{ $role }}" class="rounded border-gray-300 text-indigo-600 seg-input"
                                   @checked(in_array($role, $segment['roles'] ?? []))> {{ ucfirst($role) }}
                        </label>
                    @endforeach
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Activity</label>
                    <select name="segment[activity]" class="w-full rounded-lg border-gray-300 text-sm seg-input">
                        @foreach (['any' => 'Everyone', 'active_7' => 'Active in last 7 days', 'active_30' => 'Active in last 30 days', 'inactive_30' => 'Inactive 30+ days', 'inactive_90' => 'Inactive 90+ days'] as $value => $label)
                            <option value="{{ $value }}" @selected(($segment['activity'] ?? 'any') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <label class="flex items-center gap-2 text-sm text-gray-600 mt-3">
                        <input type="checkbox" name="segment[has_learning_path]" value="1" class="rounded border-gray-300 text-indigo-600 seg-input"
                               @checked(!empty($segment['has_learning_path']))> Has Learning Path progress
                    </label>
                </div>
            </div>
            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Registered within last N days <span class="text-gray-400">(optional)</span></label>
                    <input type="number" min="1" name="segment[registered_within_days]" value="{{ $segment['registered_within_days'] ?? '' }}" class="w-full rounded-lg border-gray-300 text-sm seg-input">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Registered more than N days ago <span class="text-gray-400">(optional)</span></label>
                    <input type="number" min="1" name="segment[registered_before_days]" value="{{ $segment['registered_before_days'] ?? '' }}" class="w-full rounded-lg border-gray-300 text-sm seg-input">
                </div>
            </div>
            <p class="text-xs text-gray-400">Only verified, non-suppressed accounts are ever included. Suppressed (bounced/complained/unsubscribed) addresses are excluded automatically.</p>
        </div>

        {{-- Schedule --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-4">
            <h3 class="font-semibold text-gray-800">Schedule</h3>
            @php $mode = old('schedule_mode', $campaign->status === 'scheduled' ? 'later' : 'draft'); @endphp
            <div class="flex flex-wrap gap-6">
                @foreach (['draft' => 'Save as draft', 'now' => 'Send immediately', 'later' => 'Schedule for later'] as $value => $label)
                    <label class="flex items-center gap-2 text-sm text-gray-700">
                        <input type="radio" name="schedule_mode" value="{{ $value }}" class="text-indigo-600" @checked($mode === $value)
                               onchange="document.getElementById('scheduled-at-box').style.display = this.value === 'later' ? 'block' : 'none'">
                        {{ $label }}
                    </label>
                @endforeach
            </div>
            <div id="scheduled-at-box" style="display: {{ $mode === 'later' ? 'block' : 'none' }}">
                <label class="block text-sm font-medium text-gray-700 mb-1">Send at</label>
                <input type="datetime-local" name="scheduled_at" value="{{ old('scheduled_at', $campaign->scheduled_at?->format('Y-m-d\TH:i')) }}"
                       class="rounded-lg border-gray-300 text-sm">
            </div>
        </div>

        <div class="flex justify-end gap-3">
            <a href="{{ route('admin.email-campaigns.index') }}" class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800">Cancel</a>
            <button type="submit" class="inline-flex items-center gap-2 px-5 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition text-sm font-medium">
                <i data-lucide="save" class="w-4 h-4"></i> {{ $campaign->exists ? 'Update Campaign' : 'Create Campaign' }}
            </button>
        </div>
    </form>
</div>

<script>
    (function () {
        const countEl = document.getElementById('segment-count');
        let timer = null;

        function refreshCount() {
            const form = countEl.closest('form') || document.querySelector('form');
            const data = new FormData(form);
            data.append('_token', '{{ csrf_token() }}');

            fetch('{{ route('admin.email-campaigns.segment-count') }}', { method: 'POST', body: data })
                .then(r => r.json())
                .then(d => countEl.textContent = d.count.toLocaleString())
                .catch(() => countEl.textContent = '?');
        }

        document.querySelectorAll('.seg-input').forEach(el => {
            el.addEventListener('change', () => { clearTimeout(timer); timer = setTimeout(refreshCount, 300); });
        });

        refreshCount();
    })();
</script>
@endsection
