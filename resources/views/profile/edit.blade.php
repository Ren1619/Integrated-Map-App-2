@extends('layouts.app')

@section('header')
    <h1 class="text-lg sm:text-xl font-semibold text-gray-800">Profile Settings</h1>
@endsection

@section('content')
    <div class="p-4 sm:p-6">
        <div class="max-w-4xl mx-auto">
            <!-- Success Messages -->
            @if (session('status') === 'profile-updated')
                <div class="mb-4 sm:mb-6 bg-green-50 border border-green-200 rounded-xl p-3 sm:p-4 flex items-center gap-2 sm:gap-3">
                    <span class="text-xl sm:text-2xl">✅</span>
                    <p class="text-xs sm:text-sm text-green-800 font-medium">Profile updated successfully!</p>
                </div>
            @endif

            @if (session('status') === 'password-updated')
                <div class="mb-4 sm:mb-6 bg-green-50 border border-green-200 rounded-xl p-3 sm:p-4 flex items-center gap-2 sm:gap-3">
                    <span class="text-xl sm:text-2xl">✅</span>
                    <p class="text-xs sm:text-sm text-green-800 font-medium">Password updated successfully!</p>
                </div>
            @endif

            <!-- Profile Information -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 mb-4 sm:mb-6">
                <div class="p-4 sm:p-6 border-b border-gray-100">
                    <h2 class="text-base sm:text-lg font-semibold text-gray-800">Profile Information</h2>
                    <p class="text-xs sm:text-sm text-gray-600 mt-1">Update your account's profile information and email address.</p>
                </div>
                <div class="p-4 sm:p-6">
                    <form method="post" action="{{ route('profile.update') }}" class="space-y-4 sm:space-y-6">
                        @csrf
                        @method('patch')

                        <!-- Name -->
                        <div>
                            <label for="name" class="block text-xs sm:text-sm font-medium text-gray-700 mb-2">Name</label>
                            <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" required
                                class="w-full px-3 sm:px-4 py-2 sm:py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors text-sm sm:text-base">
                            @error('name')
                                <p class="mt-2 text-xs sm:text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Email -->
                        <div>
                            <label for="email" class="block text-xs sm:text-sm font-medium text-gray-700 mb-2">Email</label>
                            <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" required
                                class="w-full px-3 sm:px-4 py-2 sm:py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors text-sm sm:text-base">
                            @error('email')
                                <p class="mt-2 text-xs sm:text-sm text-red-600">{{ $message }}</p>
                            @enderror

                            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && !$user->hasVerifiedEmail())
                                <div class="mt-3 p-2 sm:p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
                                    <p class="text-xs sm:text-sm text-yellow-800">
                                        Your email address is unverified.
                                        <button form="send-verification"
                                            class="underline text-yellow-900 hover:text-yellow-700 font-medium">
                                            Click here to re-send the verification email.
                                        </button>
                                    </p>
                                </div>
                            @endif
                        </div>

                        <!-- Save Button -->
                        <div class="flex items-center gap-4">
                            <button type="submit"
                                class="w-full sm:w-auto px-4 sm:px-6 py-2 sm:py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg shadow-sm hover:shadow-md transition-all duration-200 text-sm sm:text-base">
                                Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Update Password -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 mb-4 sm:mb-6">
                <div class="p-4 sm:p-6 border-b border-gray-100">
                    <h2 class="text-base sm:text-lg font-semibold text-gray-800">Update Password</h2>
                    <p class="text-xs sm:text-sm text-gray-600 mt-1">Ensure your account is using a long, random password to stay
                        secure.</p>
                </div>
                <div class="p-4 sm:p-6">
                    <form method="post" action="{{ route('password.update') }}" class="space-y-4 sm:space-y-6">
                        @csrf
                        @method('put')

                        <!-- Current Password -->
                        <div>
                            <label for="current_password" class="block text-xs sm:text-sm font-medium text-gray-700 mb-2">Current
                                Password</label>
                            <input type="password" id="current_password" name="current_password"
                                autocomplete="current-password"
                                class="w-full px-3 sm:px-4 py-2 sm:py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors text-sm sm:text-base">
                            @error('current_password', 'updatePassword')
                                <p class="mt-2 text-xs sm:text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- New Password -->
                        <div>
                            <label for="password" class="block text-xs sm:text-sm font-medium text-gray-700 mb-2">New Password</label>
                            <input type="password" id="password" name="password" autocomplete="new-password"
                                class="w-full px-3 sm:px-4 py-2 sm:py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors text-sm sm:text-base">
                            @error('password', 'updatePassword')
                                <p class="mt-2 text-xs sm:text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Confirm Password -->
                        <div>
                            <label for="password_confirmation" class="block text-xs sm:text-sm font-medium text-gray-700 mb-2">Confirm
                                Password</label>
                            <input type="password" id="password_confirmation" name="password_confirmation"
                                autocomplete="new-password"
                                class="w-full px-3 sm:px-4 py-2 sm:py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors text-sm sm:text-base">
                            @error('password_confirmation', 'updatePassword')
                                <p class="mt-2 text-xs sm:text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Save Button -->
                        <div class="flex items-center gap-4">
                            <button type="submit"
                                class="w-full sm:w-auto px-4 sm:px-6 py-2 sm:py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg shadow-sm hover:shadow-md transition-all duration-200 text-sm sm:text-base">
                                Update Password
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Delete Account -->
            <div class="bg-white rounded-xl shadow-sm border border-red-200">
                <div class="p-4 sm:p-6 border-b border-red-100">
                    <h2 class="text-base sm:text-lg font-semibold text-red-800">Delete Account</h2>
                    <p class="text-xs sm:text-sm text-gray-600 mt-1">Once your account is deleted, all of its resources and data will
                        be permanently deleted.</p>
                </div>
                <div class="p-4 sm:p-6">
                    <button type="button" onclick="document.getElementById('deleteModal').classList.remove('hidden')"
                        class="w-full sm:w-auto px-4 sm:px-6 py-2 sm:py-2.5 bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg shadow-sm hover:shadow-md transition-all duration-200 text-sm sm:text-base">
                        Delete Account
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Account Modal - FIXED: Mobile responsive -->
    <div id="deleteModal" class="hidden fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-md mx-4">
            <div class="p-4 sm:p-6">
                <div class="flex items-center gap-2 sm:gap-3 mb-3 sm:mb-4">
                    <span class="text-2xl sm:text-3xl">⚠️</span>
                    <h3 class="text-lg sm:text-xl font-bold text-gray-900">Delete Account</h3>
                </div>
                <p class="text-xs sm:text-sm text-gray-600 mb-4 sm:mb-6">
                    Are you sure you want to delete your account? Once your account is deleted, all of its resources and
                    data will be permanently deleted. Please enter your password to confirm you would like to permanently
                    delete your account.
                </p>

                <form method="post" action="{{ route('profile.destroy') }}" class="space-y-3 sm:space-y-4">
                    @csrf
                    @method('delete')

                    <div>
                        <label for="password_delete" class="block text-xs sm:text-sm font-medium text-gray-700 mb-2">Password</label>
                        <input type="password" id="password_delete" name="password" placeholder="Enter your password"
                            class="w-full px-3 sm:px-4 py-2 sm:py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-colors text-sm sm:text-base">
                        @error('password', 'userDeletion')
                            <p class="mt-2 text-xs sm:text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex flex-col sm:flex-row items-center gap-2 sm:gap-3">
                        <button type="button" onclick="document.getElementById('deleteModal').classList.add('hidden')"
                            class="w-full sm:flex-1 px-3 sm:px-4 py-2 sm:py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium rounded-lg transition-colors text-sm sm:text-base order-2 sm:order-1">
                            Cancel
                        </button>
                        <button type="submit"
                            class="w-full sm:flex-1 px-3 sm:px-4 py-2 sm:py-2.5 bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg transition-colors text-sm sm:text-base order-1 sm:order-2">
                            Delete Account
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @if ($errors->userDeletion->any())
        <script>
            document.getElementById('deleteModal').classList.remove('hidden');
        </script>
    @endif
@endsection