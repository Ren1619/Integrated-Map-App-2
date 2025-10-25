@extends('layouts.app')

@section('header')
    <h1 class="text-xl font-semibold text-gray-800">My Profile</h1>
@endsection

@section('content')
<div class="p-6">
    <div class="max-w-4xl">
        <!-- Profile Header Card -->
        <div class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-2xl shadow-lg mb-6 overflow-hidden">
            <div class="p-8">
                <div class="flex items-start justify-between">
                    <div class="flex items-center gap-6">
                        <!-- Avatar -->
                        <div class="w-24 h-24 rounded-full bg-white/20 backdrop-blur-sm border-4 border-white/30 flex items-center justify-center text-white font-bold text-4xl shadow-xl">
                            {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                        </div>
                        
                        <!-- User Info -->
                        <div class="text-white">
                            <h2 class="text-3xl font-bold mb-2">{{ Auth::user()->name }}</h2>
                            <p class="text-blue-100 text-lg mb-3">{{ Auth::user()->email }}</p>
                            <div class="flex items-center gap-2">
                                <span class="inline-flex items-center gap-1.5 bg-white/20 backdrop-blur-sm px-3 py-1 rounded-full text-sm">
                                    <span class="w-2 h-2 bg-green-400 rounded-full"></span>
                                    Active Account
                                </span>
                                @if (Auth::user()->email_verified_at)
                                    <span class="inline-flex items-center gap-1.5 bg-white/20 backdrop-blur-sm px-3 py-1 rounded-full text-sm">
                                        <span>✓</span>
                                        Verified
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Edit Button -->
                    <a href="{{ route('profile.edit') }}" 
                       class="px-6 py-2.5 bg-white text-blue-600 font-medium rounded-lg shadow-md hover:shadow-lg hover:scale-105 transition-all duration-200 flex items-center gap-2">
                        <span>✏️</span>
                        Edit Profile
                    </a>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Account Information -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Personal Information -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100">
                    <div class="p-6 border-b border-gray-100">
                        <h3 class="text-lg font-semibold text-gray-800 flex items-center gap-2">
                            <span>👤</span>
                            Personal Information
                        </h3>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Full Name -->
                            <div>
                                <label class="block text-xs font-medium text-gray-500 uppercase mb-2">Full Name</label>
                                <p class="text-base font-medium text-gray-800">{{ Auth::user()->name }}</p>
                            </div>

                            <!-- Email Address -->
                            <div>
                                <label class="block text-xs font-medium text-gray-500 uppercase mb-2">Email Address</label>
                                <p class="text-base font-medium text-gray-800 flex items-center gap-2">
                                    {{ Auth::user()->email }}
                                    @if (Auth::user()->email_verified_at)
                                        <span class="text-green-600 text-xs" title="Email verified">✓</span>
                                    @else
                                        <span class="text-yellow-600 text-xs" title="Email not verified">!</span>
                                    @endif
                                </p>
                            </div>

                            <!-- Member Since -->
                            <div>
                                <label class="block text-xs font-medium text-gray-500 uppercase mb-2">Member Since</label>
                                <p class="text-base font-medium text-gray-800">{{ Auth::user()->created_at->format('F j, Y') }}</p>
                                <p class="text-xs text-gray-500 mt-1">{{ Auth::user()->created_at->diffForHumans() }}</p>
                            </div>

                            <!-- Last Updated -->
                            <div>
                                <label class="block text-xs font-medium text-gray-500 uppercase mb-2">Profile Updated</label>
                                <p class="text-base font-medium text-gray-800">{{ Auth::user()->updated_at->format('F j, Y') }}</p>
                                <p class="text-xs text-gray-500 mt-1">{{ Auth::user()->updated_at->diffForHumans() }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Activity Stats -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100">
                    <div class="p-6 border-b border-gray-100">
                        <h3 class="text-lg font-semibold text-gray-800 flex items-center gap-2">
                            <span>📊</span>
                            Activity Overview
                        </h3>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <!-- Total Searches -->
                            <div class="text-center p-4 bg-blue-50 rounded-xl border border-blue-100">
                                <div class="text-3xl font-bold text-blue-600 mb-1">0</div>
                                <p class="text-sm text-gray-600">Total Searches</p>
                                <span class="text-xs text-gray-500 mt-1 inline-block">Coming Soon</span>
                            </div>

                            <!-- Saved Locations -->
                            <div class="text-center p-4 bg-green-50 rounded-xl border border-green-100">
                                <div class="text-3xl font-bold text-green-600 mb-1">0</div>
                                <p class="text-sm text-gray-600">Saved Locations</p>
                                <span class="text-xs text-gray-500 mt-1 inline-block">Coming Soon</span>
                            </div>

                            <!-- Favorites -->
                            <div class="text-center p-4 bg-yellow-50 rounded-xl border border-yellow-100">
                                <div class="text-3xl font-bold text-yellow-600 mb-1">0</div>
                                <p class="text-sm text-gray-600">Favorites</p>
                                <span class="text-xs text-gray-500 mt-1 inline-block">Coming Soon</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar Info -->
            <div class="space-y-6">
                <!-- Account Status -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100">
                    <div class="p-6 border-b border-gray-100">
                        <h3 class="text-lg font-semibold text-gray-800 flex items-center gap-2">
                            <span>🔐</span>
                            Account Status
                        </h3>
                    </div>
                    <div class="p-6 space-y-4">
                        <!-- Account Active -->
                        <div class="flex items-center justify-between p-3 bg-green-50 rounded-lg border border-green-200">
                            <span class="text-sm font-medium text-green-800">Account Active</span>
                            <span class="text-green-600">✓</span>
                        </div>

                        <!-- Email Verification -->
                        @if (Auth::user()->email_verified_at)
                            <div class="flex items-center justify-between p-3 bg-green-50 rounded-lg border border-green-200">
                                <span class="text-sm font-medium text-green-800">Email Verified</span>
                                <span class="text-green-600">✓</span>
                            </div>
                        @else
                            <div class="flex items-center justify-between p-3 bg-yellow-50 rounded-lg border border-yellow-200">
                                <span class="text-sm font-medium text-yellow-800">Email Not Verified</span>
                                <span class="text-yellow-600">!</span>
                            </div>
                            <form method="POST" action="{{ route('verification.send') }}">
                                @csrf
                                <button type="submit" class="w-full text-sm text-blue-600 hover:text-blue-700 underline">
                                    Resend Verification Email
                                </button>
                            </form>
                        @endif

                        <!-- Password Status -->
                        <div class="flex items-center justify-between p-3 bg-blue-50 rounded-lg border border-blue-200">
                            <span class="text-sm font-medium text-blue-800">Password Set</span>
                            <span class="text-blue-600">✓</span>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100">
                    <div class="p-6 border-b border-gray-100">
                        <h3 class="text-lg font-semibold text-gray-800 flex items-center gap-2">
                            <span>⚡</span>
                            Quick Actions
                        </h3>
                    </div>
                    <div class="p-6 space-y-3">
                        <a href="{{ route('profile.edit') }}" 
                           class="flex items-center gap-3 p-3 text-sm font-medium text-gray-700 hover:bg-blue-50 hover:text-blue-700 rounded-lg transition-colors">
                            <span class="text-xl">✏️</span>
                            <span>Edit Profile</span>
                        </a>

                        <a href="{{ route('weather.map') }}" 
                           class="flex items-center gap-3 p-3 text-sm font-medium text-gray-700 hover:bg-blue-50 hover:text-blue-700 rounded-lg transition-colors">
                            <span class="text-xl">🗺️</span>
                            <span>Weather Map</span>
                        </a>

                        <a href="{{ route('dashboard') }}" 
                           class="flex items-center gap-3 p-3 text-sm font-medium text-gray-700 hover:bg-blue-50 hover:text-blue-700 rounded-lg transition-colors">
                            <span class="text-xl">📊</span>
                            <span>Dashboard</span>
                        </a>
                    </div>
                </div>

                <!-- Security Tip -->
                <div class="bg-gradient-to-br from-orange-50 to-yellow-50 rounded-xl border border-orange-200 p-6">
                    <div class="text-3xl mb-3">🔒</div>
                    <h4 class="font-semibold text-gray-800 mb-2">Security Tip</h4>
                    <p class="text-sm text-gray-600 leading-relaxed">
                        Keep your account secure by using a strong password and never sharing your login credentials.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection