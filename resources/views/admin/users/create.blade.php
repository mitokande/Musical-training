@extends('admin.layouts.admin')

@section('page-title', 'Add Member')

@section('content')
<div class="max-w-2xl">
    <!-- Header -->
    <div class="mb-8">
        <a href="{{ route('admin.users.index') }}" class="inline-flex items-center text-gray-500 hover:text-purple-600 transition-colors mb-4 text-sm">
            <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i>
            Back to Members
        </a>
        <div class="flex items-center gap-3 mb-2">
            <div class="w-10 h-10 rounded-xl bg-purple-100 flex items-center justify-center">
                <i data-lucide="user-plus" class="w-5 h-5 text-purple-600"></i>
            </div>
            <h1 class="text-2xl font-bold text-gray-900">Add New Member</h1>
        </div>
        <p class="text-gray-500 text-sm">Create a new user account</p>
    </div>

    <!-- Form -->
    <form action="{{ route('admin.users.store') }}" method="POST" class="card p-6 space-y-6">
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label for="name" class="block text-sm font-semibold text-gray-700 mb-2">Full Name *</label>
                <input type="text" name="name" id="name" value="{{ old('name') }}" placeholder="John Doe"
                       class="w-full px-4 py-3 border border-gray-300 rounded-xl text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all"
                       required>
            </div>

            <div>
                <label for="username" class="block text-sm font-semibold text-gray-700 mb-2">Username *</label>
                <input type="text" name="username" id="username" value="{{ old('username') }}" placeholder="johndoe"
                       class="w-full px-4 py-3 border border-gray-300 rounded-xl text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all"
                       required>
            </div>
        </div>

        <div>
            <label for="email" class="block text-sm font-semibold text-gray-700 mb-2">Email Address *</label>
            <input type="email" name="email" id="email" value="{{ old('email') }}" placeholder="john@example.com"
                   class="w-full px-4 py-3 border border-gray-300 rounded-xl text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all"
                   required>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label for="password" class="block text-sm font-semibold text-gray-700 mb-2">Password *</label>
                <input type="password" name="password" id="password" placeholder="Minimum 8 characters"
                       class="w-full px-4 py-3 border border-gray-300 rounded-xl text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all"
                       required>
                <p class="mt-1.5 text-xs text-gray-500">Minimum 8 characters</p>
            </div>

            <div>
                <label for="password_confirmation" class="block text-sm font-semibold text-gray-700 mb-2">Confirm Password *</label>
                <input type="password" name="password_confirmation" id="password_confirmation" placeholder="Confirm password"
                       class="w-full px-4 py-3 border border-gray-300 rounded-xl text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all"
                       required>
            </div>
        </div>

        <!-- Role Selection -->
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-3">User Role *</label>
            <div class="grid grid-cols-2 gap-4">
                <label class="relative flex items-center justify-center p-4 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-blue-300 hover:bg-blue-50 transition-all has-[:checked]:border-blue-500 has-[:checked]:bg-blue-50">
                    <input type="radio" name="role" value="user" class="sr-only peer" {{ old('role', 'user') == 'user' ? 'checked' : '' }} required>
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center">
                            <i data-lucide="graduation-cap" class="w-5 h-5 text-blue-600"></i>
                        </div>
                        <div class="text-left">
                            <span class="font-semibold text-gray-700 block">Student</span>
                            <span class="text-xs text-gray-500">Regular user access</span>
                        </div>
                    </div>
                </label>
                <label class="relative flex items-center justify-center p-4 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-green-300 hover:bg-green-50 transition-all has-[:checked]:border-green-500 has-[:checked]:bg-green-50">
                    <input type="radio" name="role" value="teacher" class="sr-only peer" {{ old('role') == 'teacher' ? 'checked' : '' }}>
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center">
                            <i data-lucide="book-open" class="w-5 h-5 text-green-600"></i>
                        </div>
                        <div class="text-left">
                            <span class="font-semibold text-gray-700 block">Teacher</span>
                            <span class="text-xs text-gray-500">Instructor access</span>
                        </div>
                    </div>
                </label>
                <label class="relative flex items-center justify-center p-4 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-amber-300 hover:bg-amber-50 transition-all has-[:checked]:border-amber-500 has-[:checked]:bg-amber-50">
                    <input type="radio" name="role" value="school" class="sr-only peer" {{ old('role') == 'school' ? 'checked' : '' }}>
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-amber-100 flex items-center justify-center">
                            <i data-lucide="building" class="w-5 h-5 text-amber-600"></i>
                        </div>
                        <div class="text-left">
                            <span class="font-semibold text-gray-700 block">Music School</span>
                            <span class="text-xs text-gray-500">Institutional access</span>
                        </div>
                    </div>
                </label>
                <label class="relative flex items-center justify-center p-4 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-purple-300 hover:bg-purple-50 transition-all has-[:checked]:border-purple-500 has-[:checked]:bg-purple-50">
                    <input type="radio" name="role" value="admin" class="sr-only peer" {{ old('role') == 'admin' ? 'checked' : '' }}>
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-purple-100 flex items-center justify-center">
                            <i data-lucide="shield" class="w-5 h-5 text-purple-600"></i>
                        </div>
                        <div class="text-left">
                            <span class="font-semibold text-gray-700 block">Admin</span>
                            <span class="text-xs text-gray-500">Full admin access</span>
                        </div>
                    </div>
                </label>
            </div>
        </div>

        <!-- Plan Selection -->
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-3">Subscription Plan *</label>
            <div class="grid grid-cols-2 gap-4">
                <label class="relative flex items-center justify-center p-4 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-gray-400 hover:bg-gray-50 transition-all has-[:checked]:border-gray-500 has-[:checked]:bg-gray-50">
                    <input type="radio" name="plan" value="free" class="sr-only peer" {{ old('plan', 'free') == 'free' ? 'checked' : '' }} required>
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center">
                            <i data-lucide="user" class="w-5 h-5 text-gray-600"></i>
                        </div>
                        <div class="text-left">
                            <span class="font-semibold text-gray-700 block">Free</span>
                            <span class="text-xs text-gray-500">Limited features</span>
                        </div>
                    </div>
                </label>
                <label class="relative flex items-center justify-center p-4 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-amber-300 hover:bg-amber-50 transition-all has-[:checked]:border-amber-500 has-[:checked]:bg-amber-50">
                    <input type="radio" name="plan" value="premium" class="sr-only peer" {{ old('plan') == 'premium' ? 'checked' : '' }}>
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-amber-100 flex items-center justify-center">
                            <i data-lucide="crown" class="w-5 h-5 text-amber-600"></i>
                        </div>
                        <div class="text-left">
                            <span class="font-semibold text-gray-700 block">Premium</span>
                            <span class="text-xs text-gray-500">All features unlocked</span>
                        </div>
                    </div>
                </label>
            </div>
        </div>

        <!-- Additional Info -->
        <div class="border-t border-gray-200 pt-6">
            <div class="flex items-center gap-2 mb-4">
                <i data-lucide="map-pin" class="w-5 h-5 text-gray-500"></i>
                <h3 class="font-semibold text-gray-900">Additional Information</h3>
                <span class="text-xs text-gray-500">(Optional)</span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label for="phone" class="block text-sm font-semibold text-gray-700 mb-2">Phone</label>
                    <input type="text" name="phone" id="phone" value="{{ old('phone') }}" placeholder="+90 555 123 4567"
                           class="w-full px-4 py-3 border border-gray-300 rounded-xl text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all">
                </div>

                <div>
                    <label for="country" class="block text-sm font-semibold text-gray-700 mb-2">Country</label>
                    <input type="text" name="country" id="country" value="{{ old('country') }}" placeholder="Turkey"
                           class="w-full px-4 py-3 border border-gray-300 rounded-xl text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all">
                </div>

                <div>
                    <label for="city" class="block text-sm font-semibold text-gray-700 mb-2">City</label>
                    <input type="text" name="city" id="city" value="{{ old('city') }}" placeholder="Istanbul"
                           class="w-full px-4 py-3 border border-gray-300 rounded-xl text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all">
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="flex items-center justify-end gap-4 pt-4 border-t border-gray-200">
            <a href="{{ route('admin.users.index') }}"
               class="px-5 py-2.5 text-gray-600 hover:text-gray-800 font-medium transition-colors">
                Cancel
            </a>
            <button type="submit"
                    class="btn-primary px-6 py-2.5 text-white font-semibold rounded-lg transition-all hover:shadow-lg">
                Create Member
            </button>
        </div>
    </form>
</div>
@endsection
