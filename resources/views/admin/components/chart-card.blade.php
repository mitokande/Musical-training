@props(['title', 'chartId'])

<div class="card p-5">
    <h3 class="text-sm font-semibold text-gray-700 mb-4">{{ $title }}</h3>
    <div id="{{ $chartId }}"></div>
</div>
