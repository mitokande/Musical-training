@extends('admin.layouts.admin')

@section('page-title', 'Create Appointment')

@section('content')
<div class="max-w-2xl space-y-6">

    <a href="{{ route('admin.calendar.index') }}" class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-purple-600 transition-colors">
        <i data-lucide="arrow-left" class="w-4 h-4"></i> Back to Calendar
    </a>

    <div class="card p-6">
        <div class="flex items-center gap-2 mb-6">
            <i data-lucide="calendar-plus" class="w-5 h-5 text-purple-600"></i>
            <h2 class="text-lg font-semibold text-gray-900">New Appointment</h2>
        </div>

        <form action="{{ route('admin.appointments.store') }}" method="POST" class="space-y-5">
            @csrf

            <div>
                <label for="title" class="block text-sm font-medium text-gray-700 mb-1">Title</label>
                <input type="text" name="title" id="title" value="{{ old('title') }}" required
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                @error('title') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                <textarea name="description" id="description" rows="3"
                          class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500">{{ old('description') }}</textarea>
                @error('description') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="teacher_id" class="block text-sm font-medium text-gray-700 mb-1">Teacher</label>
                    <select name="teacher_id" id="teacher_id" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                        <option value="">-- Select Teacher --</option>
                        @foreach($teachers ?? [] as $teacher)
                            <option value="{{ $teacher->id }}" {{ old('teacher_id') == $teacher->id ? 'selected' : '' }}>{{ $teacher->name }}</option>
                        @endforeach
                    </select>
                    @error('teacher_id') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="student_id" class="block text-sm font-medium text-gray-700 mb-1">Student <span class="text-gray-400">(optional)</span></label>
                    <select name="student_id" id="student_id" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                        <option value="">-- Select Student --</option>
                        @foreach($students ?? [] as $student)
                            <option value="{{ $student->id }}" {{ old('student_id') == $student->id ? 'selected' : '' }}>{{ $student->name }}</option>
                        @endforeach
                    </select>
                    @error('student_id') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div>
                <label for="school_id" class="block text-sm font-medium text-gray-700 mb-1">School <span class="text-gray-400">(optional)</span></label>
                <select name="school_id" id="school_id" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                    <option value="">-- Select School --</option>
                    @foreach($schools ?? [] as $school)
                        <option value="{{ $school->id }}" {{ old('school_id') == $school->id ? 'selected' : '' }}>{{ $school->name }}</option>
                    @endforeach
                </select>
                @error('school_id') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="starts_at" class="block text-sm font-medium text-gray-700 mb-1">Starts At</label>
                    <input type="datetime-local" name="starts_at" id="starts_at" value="{{ old('starts_at') }}" required
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                    @error('starts_at') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="ends_at" class="block text-sm font-medium text-gray-700 mb-1">Ends At</label>
                    <input type="datetime-local" name="ends_at" id="ends_at" value="{{ old('ends_at') }}" required
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                    @error('ends_at') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="type" class="block text-sm font-medium text-gray-700 mb-1">Type</label>
                    <select name="type" id="type" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                        <option value="lesson" {{ old('type') == 'lesson' ? 'selected' : '' }}>Lesson</option>
                        <option value="consultation" {{ old('type') == 'consultation' ? 'selected' : '' }}>Consultation</option>
                        <option value="trial" {{ old('type') == 'trial' ? 'selected' : '' }}>Trial</option>
                        <option value="meeting" {{ old('type') == 'meeting' ? 'selected' : '' }}>Meeting</option>
                    </select>
                    @error('type') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select name="status" id="status" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                        <option value="scheduled" {{ old('status', 'scheduled') == 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                        <option value="confirmed" {{ old('status') == 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                        <option value="completed" {{ old('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="cancelled" {{ old('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                    @error('status') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div>
                <label for="location" class="block text-sm font-medium text-gray-700 mb-1">Location</label>
                <input type="text" name="location" id="location" value="{{ old('location') }}"
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                       placeholder="e.g., Room 101, Online">
                @error('location') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="meeting_url" class="block text-sm font-medium text-gray-700 mb-1">Meeting URL</label>
                <input type="url" name="meeting_url" id="meeting_url" value="{{ old('meeting_url') }}"
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                       placeholder="https://zoom.us/...">
                @error('meeting_url') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                <textarea name="notes" id="notes" rows="3"
                          class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500">{{ old('notes') }}</textarea>
                @error('notes') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="pt-4 border-t border-gray-200 flex items-center gap-3">
                <button type="submit" class="btn-primary px-6 py-2.5 text-white text-sm font-semibold rounded-lg transition-all hover:shadow-lg">
                    <i data-lucide="save" class="w-4 h-4 inline mr-1"></i> Create Appointment
                </button>
                <a href="{{ route('admin.calendar.index') }}" class="px-6 py-2.5 text-sm font-medium text-gray-600 hover:text-gray-800">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
