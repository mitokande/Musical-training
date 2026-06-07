@extends('admin.layouts.admin')

@section('page-title', 'Settings')

@section('content')
<div class="max-w-3xl space-y-6">

    <form action="{{ route('admin.settings.update') }}" method="POST">
        @csrf
        @method('PUT')

        @foreach(($settings ?? collect())->groupBy('group') as $group => $groupSettings)
        <div class="card p-6 mb-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-5 flex items-center gap-2">
                <i data-lucide="settings" class="w-5 h-5 text-purple-600"></i>
                {{ ucfirst(str_replace('_', ' ', $group)) }}
            </h2>

            <div class="space-y-4">
                @foreach($groupSettings as $setting)
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 py-3 border-b border-gray-100 last:border-0">
                    <div>
                        <label for="setting_{{ $setting->key }}" class="text-sm font-medium text-gray-700">{{ $setting->description ?? ucfirst(str_replace('_', ' ', $setting->key)) }}</label>
                    </div>
                    <div class="sm:w-64">
                        @if($setting->type === 'checkbox' || $setting->type === 'boolean')
                            <input type="hidden" name="settings[{{ $setting->key }}]" value="0">
                            <input type="checkbox" name="settings[{{ $setting->key }}]" id="setting_{{ $setting->key }}" value="1"
                                   {{ $setting->value ? 'checked' : '' }}
                                   class="w-4 h-4 text-purple-600 border-gray-300 rounded focus:ring-purple-500">
                        @elseif($setting->type === 'number')
                            <input type="number" name="settings[{{ $setting->key }}]" id="setting_{{ $setting->key }}" value="{{ $setting->value }}"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                        @else
                            <input type="text" name="settings[{{ $setting->key }}]" id="setting_{{ $setting->key }}" value="{{ $setting->value }}"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endforeach

        <div class="pt-2">
            <button type="submit" class="btn-primary px-6 py-2.5 text-white text-sm font-semibold rounded-lg transition-all hover:shadow-lg">
                <i data-lucide="save" class="w-4 h-4 inline mr-1"></i> Save All Settings
            </button>
        </div>
    </form>
</div>
@endsection
