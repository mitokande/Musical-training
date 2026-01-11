@extends('admin.layouts.admin')

@section('content')
<div class="max-w-2xl">
    <!-- Header -->
    <div class="mb-8">
        <a href="{{ route('admin.users.index') }}" class="inline-flex items-center text-gray-500 hover:text-purple-600 transition-colors mb-4">
            <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i>
            Back to users
        </a>
        <div class="flex items-center gap-4 mb-2">
            <div class="w-14 h-14 rounded-full bg-gradient-to-br from-purple-500 to-pink-500 flex items-center justify-center text-white font-bold text-xl">
                {{ strtoupper(substr($user->name, 0, 1)) }}
            </div>
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Edit User</h1>
                <p class="text-gray-500">{{ $user->email }}</p>
            </div>
        </div>
    </div>

    <!-- Form -->
    <form action="{{ route('admin.users.update', $user) }}" method="POST" class="card p-6 space-y-6">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label for="name" class="block text-sm font-semibold text-gray-700 mb-2">Full Name *</label>
                <input type="text" 
                       name="name" 
                       id="name" 
                       value="{{ old('name', $user->name) }}"
                       placeholder="John Doe"
                       class="w-full px-4 py-3 border border-gray-300 rounded-xl text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all"
                       required>
            </div>

            <div>
                <label for="username" class="block text-sm font-semibold text-gray-700 mb-2">Username *</label>
                <input type="text" 
                       name="username" 
                       id="username" 
                       value="{{ old('username', $user->username) }}"
                       placeholder="johndoe"
                       class="w-full px-4 py-3 border border-gray-300 rounded-xl text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all"
                       required>
            </div>
        </div>

        <div>
            <label for="email" class="block text-sm font-semibold text-gray-700 mb-2">Email Address *</label>
            <input type="email" 
                   name="email" 
                   id="email" 
                   value="{{ old('email', $user->email) }}"
                   placeholder="john@example.com"
                   class="w-full px-4 py-3 border border-gray-300 rounded-xl text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all"
                   required>
        </div>

        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-3">User Role *</label>
            <div class="grid grid-cols-2 gap-4">
                <label class="relative flex items-center justify-center p-4 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-blue-300 hover:bg-blue-50 transition-all has-[:checked]:border-blue-500 has-[:checked]:bg-blue-50">
                    <input type="radio" name="role" value="student" class="sr-only peer" {{ old('role', $user->role) == 'student' ? 'checked' : '' }} required>
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
                <label class="relative flex items-center justify-center p-4 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-purple-300 hover:bg-purple-50 transition-all has-[:checked]:border-purple-500 has-[:checked]:bg-purple-50">
                    <input type="radio" name="role" value="admin" class="sr-only peer" {{ old('role', $user->role) == 'admin' ? 'checked' : '' }}>
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

        <!-- Password Reset Section -->
        <div class="border-t border-gray-200 pt-6">
            <div class="flex items-center gap-2 mb-4">
                <i data-lucide="key" class="w-5 h-5 text-gray-500"></i>
                <h3 class="font-semibold text-gray-900">Reset Password</h3>
                <span class="text-xs text-gray-500">(Optional)</span>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="password" class="block text-sm font-semibold text-gray-700 mb-2">New Password</label>
                    <input type="password" 
                           name="password" 
                           id="password" 
                           placeholder="Leave blank to keep current"
                           class="w-full px-4 py-3 border border-gray-300 rounded-xl text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all">
                    <p class="mt-2 text-xs text-gray-500">Minimum 8 characters</p>
                </div>

                <div>
                    <label for="password_confirmation" class="block text-sm font-semibold text-gray-700 mb-2">Confirm Password</label>
                    <input type="password" 
                           name="password_confirmation" 
                           id="password_confirmation" 
                           placeholder="Confirm new password"
                           class="w-full px-4 py-3 border border-gray-300 rounded-xl text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all">
                </div>
            </div>
        </div>

        <div class="flex items-center justify-end gap-4 pt-4 border-t border-gray-200">
            <a href="{{ route('admin.users.index') }}" 
               class="px-5 py-2.5 text-gray-600 hover:text-gray-800 font-medium transition-colors">
                Cancel
            </a>
            <button type="submit" 
                    class="btn-primary px-6 py-2.5 text-white font-semibold rounded-lg transition-all hover:shadow-lg">
                Update User
            </button>
        </div>
    </form>

    <!-- Delete User (separate form outside main form) -->
    @if ($user->id !== auth()->id())
    <div class="mt-4 p-4 bg-red-50 border border-red-200 rounded-xl">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center">
                    <i data-lucide="alert-triangle" class="w-5 h-5 text-red-600"></i>
                </div>
                <div>
                    <p class="font-semibold text-red-700">Danger Zone</p>
                    <p class="text-sm text-red-600">Permanently delete this user account</p>
                </div>
            </div>
            <form action="{{ route('admin.users.destroy', $user) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this user? This action cannot be undone.')">
                @csrf
                @method('DELETE')
                <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-lg transition-colors">
                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                    Delete User
                </button>
            </form>
        </div>
    </div>
    @endif

    <!-- User Info -->
    <div class="mt-6 card p-6">
        <div class="flex items-center gap-2 mb-4">
            <i data-lucide="info" class="w-5 h-5 text-gray-500"></i>
            <h3 class="font-semibold text-gray-900">User Information</h3>
        </div>
        <div class="grid grid-cols-2 gap-4 text-sm">
            <div>
                <span class="text-gray-500">User ID:</span>
                <span class="text-gray-900 font-medium ml-2">{{ $user->id }}</span>
            </div>
            <div>
                <span class="text-gray-500">Joined:</span>
                <span class="text-gray-900 font-medium ml-2">{{ $user->created_at->format('M d, Y \a\t H:i') }}</span>
            </div>
            <div>
                <span class="text-gray-500">Last Updated:</span>
                <span class="text-gray-900 font-medium ml-2">{{ $user->updated_at->format('M d, Y \a\t H:i') }}</span>
            </div>
            <div>
                <span class="text-gray-500">Email Verified:</span>
                <span class="text-gray-900 font-medium ml-2">{{ $user->email_verified_at ? $user->email_verified_at->format('M d, Y') : 'Not verified' }}</span>
            </div>
        </div>
    </div>
</div>
@endsection

