<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('partials.google-analytics')
    @include('partials.posthog')
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('teacher.my_teachers.title') }} - {{ config('app.name', 'Harmoniva') }}</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@0.460.0"></script>
    <script defer src="https://unpkg.com/alpinejs@3.14.8/dist/cdn.min.js"></script>
    <script>
        tailwind.config = { theme: { extend: {
            fontFamily: { sans: ['Plus Jakarta Sans', 'system-ui', 'sans-serif'] },
            colors: { primary: { 50:'#faf5ff',100:'#f3e8ff',200:'#e9d5ff',300:'#d8b4fe',400:'#c084fc',500:'#a855f7',600:'#9333ea',700:'#7c3aed',800:'#6b21a8',900:'#581c87' } }
        } } }
    </script>
</head>
<body class="font-sans bg-gray-50 min-h-screen">
@include('partials.navbar')

<main class="max-w-3xl mx-auto px-4 sm:px-6 py-8">
    <h1 class="text-2xl font-bold text-gray-900">{{ __('teacher.my_teachers.title') }}</h1>
    <p class="text-gray-500 text-sm mt-1 mb-6">{{ __('teacher.my_teachers.subtitle') }}</p>

    @php
        $statusKey = 'teacher.my_teachers.status_'.session('status');
    @endphp
    @if (session('status') && \Illuminate\Support\Facades\Lang::has($statusKey))
        <div class="mb-6 px-4 py-3 bg-green-50 border border-green-200 rounded-xl flex items-center gap-3">
            <i data-lucide="check-circle" class="w-5 h-5 text-green-600"></i>
            <p class="text-sm text-green-700">{{ __($statusKey) }}</p>
        </div>
    @endif
    @if ($errors->any())
        <div class="mb-6 px-4 py-3 bg-red-50 border border-red-200 rounded-xl">
            @foreach ($errors->all() as $error)<p class="text-sm text-red-600">{{ $error }}</p>@endforeach
        </div>
    @endif

    @if($relationships->isEmpty())
        <div class="bg-white rounded-2xl border border-gray-200 p-10 text-center text-gray-500">
            <i data-lucide="graduation-cap" class="w-10 h-10 mx-auto mb-3 text-gray-300"></i>
            <p class="text-sm">{{ __('teacher.my_teachers.no_teachers') }}</p>
        </div>
    @else
        <div class="space-y-4">
            @foreach($relationships as $rel)
                @php $teacher = $rel->teacher; $profile = $teacher->teacherProfile; @endphp
                <div class="bg-white rounded-2xl border {{ $rel->status === 'pending_student_approval' ? 'border-amber-300 ring-1 ring-amber-100' : 'border-gray-200' }} p-5 flex flex-col sm:flex-row sm:items-center gap-4">
                    @if($teacher->hasAvatar())
                        <img src="{{ $teacher->avatar }}" class="w-14 h-14 rounded-full object-cover" alt="">
                    @else
                        <div class="w-14 h-14 rounded-full bg-primary-100 text-primary-700 flex items-center justify-center text-lg font-bold">
                            {{ strtoupper(substr($teacher->name, 0, 1)) }}
                        </div>
                    @endif
                    <div class="flex-1 min-w-0">
                        <p class="font-bold text-gray-900">{{ $teacher->name }} {{ $teacher->surname }}</p>
                        @if($rel->status === 'pending_student_approval')
                            <p class="text-xs font-semibold text-amber-600 mt-0.5">{{ __('teacher.my_teachers.pending') }}</p>
                        @else
                            <p class="text-xs text-gray-500 mt-0.5">{{ __('teacher.my_teachers.since', ['date' => $rel->approved_at?->format('M j, Y')]) }}</p>
                        @endif
                        @if($profile && $profile->isPubliclyVisible())
                            <a href="{{ $profile->publicUrl() }}" class="text-xs font-semibold text-primary-600 hover:text-primary-800">{{ __('teacher.my_teachers.view_public_profile') }} →</a>
                        @endif
                    </div>
                    <div class="flex gap-2 shrink-0">
                        @if($rel->status === 'pending_student_approval')
                            <form method="POST" action="{{ route('my-teachers.approve', $rel) }}">
                                @csrf
                                <button class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-semibold rounded-lg transition">{{ __('teacher.my_teachers.approve') }}</button>
                            </form>
                            <form method="POST" action="{{ route('my-teachers.decline', $rel) }}">
                                @csrf
                                <button class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-600 text-sm font-semibold rounded-lg transition">{{ __('teacher.my_teachers.decline') }}</button>
                            </form>
                        @else
                            <form method="POST" action="{{ route('my-teachers.destroy', $rel) }}" onsubmit="return confirm(@js(__('teacher.my_teachers.revoke_confirm')))">
                                @csrf @method('DELETE')
                                <button class="px-3 py-2 text-xs font-semibold text-red-600 bg-red-50 hover:bg-red-100 rounded-lg transition">{{ __('teacher.my_teachers.revoke') }}</button>
                            </form>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</main>

@include('partials.footer')
<script>lucide.createIcons();</script>
</body>
</html>
