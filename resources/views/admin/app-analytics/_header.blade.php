{{--
    Tabs and the range filter shared by every Mobile App page. Pass `range`
    (an AnalyticsRange) for the pages that look at a window of time; Journey
    and Live have their own search and pass none. The range form keeps every
    other parameter the page was opened with, so changing the dates on a flow
    does not forget which flow.
--}}
@php
    $tabs = [
        'overview' => ['Overview', 'layout-dashboard'],
        'users' => ['Users', 'users'],
        'flows' => ['Flows', 'git-fork'],
        'screens' => ['Screens', 'smartphone'],
        'events' => ['Events', 'activity'],
        'funnels' => ['Funnels', 'filter'],
        'retention' => ['Retention', 'repeat'],
        'learning' => ['Learning', 'graduation-cap'],
        'activity' => ['Live', 'radio'],
    ];
    $carry = isset($range) ? $range->query() : [];
    $active = fn (string $route) => request()->routeIs('admin.app-analytics.'.$route)
        || ($route === 'users' && request()->routeIs('admin.app-analytics.journey'))
        || ($route === 'screens' && request()->routeIs('admin.app-analytics.screen'))
        || ($route === 'events' && request()->routeIs('admin.app-analytics.event'));
    $keep = collect(request()->except(['from', 'to', 'platform', 'page']));
@endphp

<div class="card p-2 flex flex-wrap gap-1">
    @foreach ($tabs as $route => [$label, $icon])
        <a href="{{ route('admin.app-analytics.'.$route, $route === 'activity' ? [] : $carry) }}"
           class="inline-flex items-center gap-2 px-3 py-2 text-sm rounded-lg transition-colors {{ $active($route) ? 'bg-purple-50 text-purple-700 font-semibold' : 'text-gray-600 hover:bg-gray-50' }}">
            <i data-lucide="{{ $icon }}" class="w-4 h-4"></i> {{ $label }}
        </a>
    @endforeach
</div>

@isset($range)
<div class="card p-4">
    <form method="GET" class="flex flex-wrap items-end gap-4">
        @foreach ($keep as $key => $value)
            @foreach ((array) $value as $item)
                <input type="hidden" name="{{ is_array($value) ? $key.'[]' : $key }}" value="{{ $item }}">
            @endforeach
        @endforeach
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">From</label>
            <input type="date" name="from" value="{{ $range->from->toDateString() }}"
                   class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">To</label>
            <input type="date" name="to" value="{{ $range->to->toDateString() }}"
                   class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Platform</label>
            <select name="platform" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                <option value="">All</option>
                <option value="ios" @selected($range->platform === 'ios')>iOS</option>
                <option value="android" @selected($range->platform === 'android')>Android</option>
            </select>
        </div>
        <div class="flex gap-1">
            @foreach ([7 => '7d', 14 => '14d', 30 => '30d', 90 => '90d'] as $days => $label)
                <a href="{{ request()->fullUrlWithQuery(['from' => now()->subDays($days - 1)->toDateString(), 'to' => now()->toDateString(), 'page' => null]) }}"
                   class="px-2.5 py-2 text-xs rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50">{{ $label }}</a>
            @endforeach
        </div>
        <button type="submit" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-sm font-medium text-gray-700 rounded-lg transition-colors">
            <i data-lucide="search" class="w-4 h-4 inline mr-1"></i> Apply
        </button>
        <p class="ml-auto text-xs text-gray-400">Times in UTC · refreshed every 10 minutes</p>
    </form>
</div>
@endisset
