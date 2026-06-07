@props(['title', 'value', 'icon' => 'activity', 'color' => 'purple', 'trend' => null, 'link' => null])

@php
$colorMap = [
    'purple' => ['bg' => 'bg-purple-50', 'text' => 'text-purple-600', 'icon' => 'text-purple-500'],
    'blue' => ['bg' => 'bg-blue-50', 'text' => 'text-blue-600', 'icon' => 'text-blue-500'],
    'green' => ['bg' => 'bg-green-50', 'text' => 'text-green-600', 'icon' => 'text-green-500'],
    'orange' => ['bg' => 'bg-orange-50', 'text' => 'text-orange-600', 'icon' => 'text-orange-500'],
    'red' => ['bg' => 'bg-red-50', 'text' => 'text-red-600', 'icon' => 'text-red-500'],
    'indigo' => ['bg' => 'bg-indigo-50', 'text' => 'text-indigo-600', 'icon' => 'text-indigo-500'],
    'pink' => ['bg' => 'bg-pink-50', 'text' => 'text-pink-600', 'icon' => 'text-pink-500'],
    'teal' => ['bg' => 'bg-teal-50', 'text' => 'text-teal-600', 'icon' => 'text-teal-500'],
];
$c = $colorMap[$color] ?? $colorMap['purple'];
@endphp

<div class="card p-5 hover:shadow-md transition-shadow {{ $link ? 'cursor-pointer' : '' }}" @if($link) onclick="window.location='{{ $link }}'" @endif>
    <div class="flex items-start justify-between">
        <div>
            <p class="text-sm text-gray-500 font-medium">{{ $title }}</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">{{ $value }}</p>
            @if($trend !== null)
                <p class="text-xs mt-1 {{ $trend >= 0 ? 'text-green-600' : 'text-red-600' }}">
                    <i data-lucide="{{ $trend >= 0 ? 'trending-up' : 'trending-down' }}" class="w-3 h-3 inline"></i>
                    {{ abs($trend) }}%
                </p>
            @endif
        </div>
        <div class="w-10 h-10 rounded-lg {{ $c['bg'] }} flex items-center justify-center">
            <i data-lucide="{{ $icon }}" class="w-5 h-5 {{ $c['icon'] }}"></i>
        </div>
    </div>
</div>
