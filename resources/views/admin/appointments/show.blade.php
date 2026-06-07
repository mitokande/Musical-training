@extends('admin.layouts.admin')

@section('page-title', 'Appointment Details')

@section('content')
<div class="max-w-2xl space-y-6">

    <div class="flex items-center justify-between">
        <a href="{{ route('admin.calendar.index') }}" class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-purple-600 transition-colors">
            <i data-lucide="arrow-left" class="w-4 h-4"></i> Back to Calendar
        </a>
        <a href="{{ route('admin.appointments.edit', $appointment) }}" class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-medium text-purple-600 hover:bg-purple-50 rounded-lg transition-colors">
            <i data-lucide="pencil" class="w-4 h-4"></i> Edit
        </a>
    </div>

    @php
        $typeColors = [
            'lesson' => 'bg-purple-100 text-purple-700',
            'consultation' => 'bg-blue-100 text-blue-700',
            'trial' => 'bg-orange-100 text-orange-700',
            'meeting' => 'bg-green-100 text-green-700',
        ];
        $statusColors = [
            'scheduled' => 'bg-yellow-100 text-yellow-700',
            'confirmed' => 'bg-blue-100 text-blue-700',
            'completed' => 'bg-green-100 text-green-700',
            'cancelled' => 'bg-gray-100 text-gray-700',
        ];
    @endphp

    <div class="card p-6">
        <div class="flex items-start justify-between mb-6">
            <div>
                <h2 class="text-xl font-bold text-gray-900">{{ $appointment->title }}</h2>
                <p class="text-sm text-gray-500 mt-1">Created {{ $appointment->created_at->format('M d, Y H:i') }}</p>
            </div>
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-1 text-xs font-medium rounded-full {{ $typeColors[$appointment->type] ?? 'bg-gray-100 text-gray-700' }}">{{ ucfirst($appointment->type ?? 'meeting') }}</span>
                <span class="px-2.5 py-1 text-xs font-medium rounded-full {{ $statusColors[$appointment->status] ?? 'bg-gray-100 text-gray-700' }}">{{ ucfirst($appointment->status ?? 'scheduled') }}</span>
            </div>
        </div>

        @if($appointment->description)
        <div class="mb-6">
            <p class="text-xs font-semibold text-gray-400 uppercase mb-1">Description</p>
            <p class="text-sm text-gray-700 whitespace-pre-line">{{ $appointment->description }}</p>
        </div>
        @endif

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-4 border-t border-gray-100">
            <div>
                <p class="text-xs font-semibold text-gray-400 uppercase mb-1">Teacher</p>
                <p class="text-sm text-gray-700">{{ $appointment->teacher->name ?? 'N/A' }}</p>
            </div>
            <div>
                <p class="text-xs font-semibold text-gray-400 uppercase mb-1">Student</p>
                <p class="text-sm text-gray-700">{{ $appointment->student->name ?? 'N/A' }}</p>
            </div>
            <div>
                <p class="text-xs font-semibold text-gray-400 uppercase mb-1">School</p>
                <p class="text-sm text-gray-700">{{ $appointment->school->name ?? 'N/A' }}</p>
            </div>
            <div>
                <p class="text-xs font-semibold text-gray-400 uppercase mb-1">Location</p>
                <p class="text-sm text-gray-700">{{ $appointment->location ?? 'Not specified' }}</p>
            </div>
            <div>
                <p class="text-xs font-semibold text-gray-400 uppercase mb-1">Starts At</p>
                <p class="text-sm text-gray-700">{{ $appointment->starts_at ? $appointment->starts_at->format('M d, Y H:i') : '-' }}</p>
            </div>
            <div>
                <p class="text-xs font-semibold text-gray-400 uppercase mb-1">Ends At</p>
                <p class="text-sm text-gray-700">{{ $appointment->ends_at ? $appointment->ends_at->format('M d, Y H:i') : '-' }}</p>
            </div>
        </div>

        @if($appointment->meeting_url)
        <div class="mt-4 pt-4 border-t border-gray-100">
            <p class="text-xs font-semibold text-gray-400 uppercase mb-1">Meeting URL</p>
            <a href="{{ $appointment->meeting_url }}" target="_blank" class="text-sm text-purple-600 hover:underline">{{ $appointment->meeting_url }}</a>
        </div>
        @endif

        @if($appointment->notes)
        <div class="mt-4 pt-4 border-t border-gray-100">
            <p class="text-xs font-semibold text-gray-400 uppercase mb-1">Notes</p>
            <p class="text-sm text-gray-700 whitespace-pre-line">{{ $appointment->notes }}</p>
        </div>
        @endif
    </div>
</div>
@endsection
