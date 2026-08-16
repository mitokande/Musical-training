@extends('admin.layouts.admin')
@section('page-title', 'Email Automations')

@section('content')
<div class="space-y-6">
    <div>
        <h2 class="text-2xl font-bold text-gray-800">Email Automations</h2>
        <p class="text-sm text-gray-500 mt-1">Lifecycle emails sent automatically by the scheduler (every 15 minutes). All automations respect the suppression list and the weekly frequency cap.</p>
    </div>

    <div class="grid gap-4">
        @foreach ($automations as $automation)
            <form method="POST" action="{{ route('admin.email-automations.update', $automation) }}"
                  class="bg-white rounded-xl shadow-sm border {{ $automation->enabled ? 'border-green-200' : 'border-gray-200' }} p-6">
                @csrf @method('PUT')
                <div class="flex items-start justify-between flex-wrap gap-4">
                    <div class="flex-1 min-w-[260px]">
                        <div class="flex items-center gap-3">
                            <h3 class="font-semibold text-gray-800">{{ $automation->name }}</h3>
                            <span class="text-xs px-2 py-0.5 rounded-full {{ $automation->enabled ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                                {{ $automation->enabled ? 'Enabled' : 'Disabled' }}
                            </span>
                        </div>
                        <p class="text-sm text-gray-500 mt-1">{{ $automation->description }}</p>
                        <div class="text-xs text-gray-400 mt-2">
                            Sent (30 days): <span class="font-semibold text-gray-600">{{ $automation->sent_30d }}</span>
                            · Total: <span class="font-semibold text-gray-600">{{ $automation->send_count }}</span>
                            · Last run: {{ $automation->last_run_at?->diffForHumans() ?? 'never' }}
                        </div>
                        @php $split = $audienceCounts[$automation->id] ?? []; @endphp
                        <div class="flex items-center gap-2 mt-2 text-xs">
                            <span class="text-gray-400">Last 30 days by audience:</span>
                            @foreach (['student' => 'bg-purple-100 text-purple-700', 'teacher' => 'bg-emerald-100 text-emerald-700', 'school' => 'bg-blue-100 text-blue-700'] as $audience => $classes)
                                <span class="px-2 py-0.5 rounded-full {{ ($split[$audience] ?? 0) > 0 ? $classes : 'bg-gray-100 text-gray-400' }}">
                                    {{ ucfirst($audience) }}: {{ $split[$audience] ?? 0 }}
                                </span>
                            @endforeach
                        </div>
                    </div>

                    <div class="flex items-end gap-3 flex-wrap">
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">Template</label>
                            <select name="template_id" class="rounded-lg border-gray-300 text-sm">
                                @foreach ($templates as $template)
                                    <option value="{{ $template->id }}" @selected($automation->template_id == $template->id)>{{ $template->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        @foreach ($automation->config ?? [] as $key => $value)
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1">{{ str_replace('_', ' ', $key) }}</label>
                                <input type="number" name="config[{{ $key }}]" value="{{ $value }}" min="0" max="365" class="w-24 rounded-lg border-gray-300 text-sm">
                            </div>
                        @endforeach
                        <label class="flex items-center gap-2 text-sm text-gray-700 pb-2">
                            <input type="checkbox" name="enabled" value="1" class="rounded border-gray-300 text-green-600 w-5 h-5" @checked($automation->enabled)>
                            Enabled
                        </label>
                        <button class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 text-sm">Save</button>
                    </div>
                </div>
            </form>
        @endforeach
    </div>
</div>
@endsection
