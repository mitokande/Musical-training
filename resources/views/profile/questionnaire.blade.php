<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('app.profile.questionnaire') }} - {{ config('app.name', 'Harmoniva') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@0.460.0"></script>
    <script defer src="https://unpkg.com/alpinejs@3.14.8/dist/cdn.min.js"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Plus Jakarta Sans', 'system-ui', 'sans-serif'] },
                    colors: {
                        primary: { 50:'#faf5ff',100:'#f3e8ff',200:'#e9d5ff',300:'#d8b4fe',400:'#c084fc',500:'#a855f7',600:'#9333ea',700:'#7c3aed',800:'#6b21a8',900:'#581c87' },
                        accent: { 400:'#fb923c',500:'#f97316',600:'#ea580c' }
                    }
                }
            }
        }
    </script>
</head>
<body class="font-sans bg-gray-50 min-h-screen">

@include('partials.navbar', ['active' => 'profile'])

<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    @if (session('status') === 'questionnaire-saved')
        <div class="mb-4 p-4 bg-green-100 border border-green-300 rounded-lg text-green-800 text-sm">
            {{ __('app.profile.questionnaire_saved') }}
        </div>
    @endif

    @if ($questions->isEmpty())
        <div class="p-8 bg-white shadow rounded-lg text-center">
            <p class="text-gray-500">{{ __('app.profile.no_sessions') }}</p>
        </div>
    @else
        <div class="mb-4 p-4 bg-indigo-50 border border-indigo-200 rounded-lg">
            <p class="text-sm text-indigo-800">
                {{ __('app.ai_coach.complete_profile') }}
            </p>
        </div>

        <form method="POST" action="{{ route('profile.questionnaire.store') }}" class="space-y-6">
            @csrf

            @php $currentCategory = ''; @endphp

            @foreach ($questions as $question)
                @if ($question->category !== $currentCategory)
                    @php $currentCategory = $question->category; @endphp
                    <div class="pt-2">
                        <h3 class="text-sm font-semibold uppercase tracking-wider text-gray-500">
                            {{ match($currentCategory) {
                                'background'  => __('app.profile.music_history'),
                                'skills'      => __('app.practice.title'),
                                'goals'       => __('app.ai_coach.focus_areas'),
                                'preferences' => __('app.profile.interests'),
                                default       => ucfirst($currentCategory),
                            } }}
                        </h3>
                    </div>
                @endif

                <div class="p-4 sm:p-6 bg-white shadow sm:rounded-lg">
                    <label class="block text-sm font-medium text-gray-900 mb-2">
                        {{ $question->getLocalizedText() }}
                        @if ($question->is_required)
                            <span class="text-red-500">*</span>
                        @endif
                    </label>

                    @switch($question->question_type)
                        @case('text')
                            <textarea name="answers[{{ $question->id }}]" rows="3" class="block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" {{ $question->is_required ? 'required' : '' }}>{{ $responses[$question->id] ?? '' }}</textarea>
                            @break

                        @case('number')
                            <input type="number" name="answers[{{ $question->id }}]" value="{{ $responses[$question->id] ?? '' }}" class="block w-full sm:w-32 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" {{ $question->is_required ? 'required' : '' }}>
                            @break

                        @case('single_choice')
                            @php $localizedOptions = $question->getLocalizedOptions(); @endphp
                            <div class="space-y-2">
                                @foreach ($question->options as $idx => $optionKey)
                                    <label class="flex items-center">
                                        <input type="radio" name="answers[{{ $question->id }}]" value="{{ $optionKey }}" class="text-indigo-600 focus:ring-indigo-500"
                                            {{ ($responses[$question->id] ?? '') === $optionKey ? 'checked' : '' }}>
                                        <span class="ms-2 text-sm text-gray-700">{{ $localizedOptions[$idx] ?? $optionKey }}</span>
                                    </label>
                                @endforeach
                            </div>
                            @break

                        @case('multi_choice')
                            @php
                                $selected = json_decode($responses[$question->id] ?? '[]', true) ?? [];
                                $localizedOptions = $question->getLocalizedOptions();
                            @endphp
                            <div class="space-y-2">
                                @foreach ($question->options as $idx => $optionKey)
                                    <label class="flex items-center">
                                        <input type="checkbox" name="answers[{{ $question->id }}][]" value="{{ $optionKey }}" class="rounded text-indigo-600 focus:ring-indigo-500"
                                            {{ in_array($optionKey, $selected) ? 'checked' : '' }}>
                                        <span class="ms-2 text-sm text-gray-700">{{ $localizedOptions[$idx] ?? $optionKey }}</span>
                                    </label>
                                @endforeach
                            </div>
                            @break

                        @case('scale')
                            <div class="flex items-center gap-4 mt-1">
                                @for ($i = 1; $i <= 5; $i++)
                                    <label class="flex flex-col items-center cursor-pointer">
                                        <input type="radio" name="answers[{{ $question->id }}]" value="{{ $i }}" class="text-indigo-600 focus:ring-indigo-500"
                                            {{ ($responses[$question->id] ?? '') == $i ? 'checked' : '' }}>
                                        <span class="mt-1 text-xs text-gray-500">{{ $i }}</span>
                                    </label>
                                @endfor
                            </div>
                            @break
                    @endswitch
                </div>
            @endforeach

            <div class="flex items-center gap-4">
                <button type="submit" class="px-6 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold rounded-lg transition-colors">
                    {{ __('app.common.save') }}
                </button>
                <a href="{{ route('profile.edit') }}" class="text-sm text-gray-600 hover:text-gray-900">{{ __('app.common.back') }}</a>
            </div>
        </form>
    @endif

</div>

</body>
</html>
