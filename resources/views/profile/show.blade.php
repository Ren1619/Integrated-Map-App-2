@extends('layouts.app')

@section('header')
    <h1 class="text-lg sm:text-xl font-semibold text-gray-800">My Profile</h1>
@endsection

@section('content')
    <div class="p-4 sm:p-6">
        <div class="max-w-4xl mx-auto">
            <!-- Profile Header Card -->
            <div class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-2xl shadow-lg mb-4 sm:mb-6 overflow-hidden">
                <div class="p-6 sm:p-8">
                    <!-- Mobile: Stack vertically, Desktop: Side by side -->
                    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                        <div class="flex items-center gap-4 sm:gap-6 w-full sm:w-auto">
                            <!-- Avatar -->
                            <div
                                class="w-16 h-16 sm:w-24 sm:h-24 rounded-full bg-white/20 backdrop-blur-sm border-4 border-white/30 flex items-center justify-center text-white font-bold text-2xl sm:text-4xl shadow-xl flex-shrink-0">
                                {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                            </div>

                            <!-- User Info -->
                            <div class="text-white flex-1 min-w-0">
                                <h2 class="text-2xl sm:text-3xl font-bold mb-2 break-words">{{ Auth::user()->name }}</h2>
                                <p class="text-blue-100 text-base sm:text-lg mb-2 sm:mb-3 break-all">
                                    {{ Auth::user()->email }}</p>
                                <div class="flex flex-wrap items-center gap-2">
                                    <span
                                        class="inline-flex items-center gap-1.5 bg-white/20 backdrop-blur-sm px-2 sm:px-3 py-1 rounded-full text-xs sm:text-sm">
                                        <span class="w-2 h-2 bg-green-400 rounded-full"></span>
                                        Active Account
                                    </span>
                                    @if (Auth::user()->email_verified_at)
                                        <span
                                            class="inline-flex items-center gap-1.5 bg-white/20 backdrop-blur-sm px-2 sm:px-3 py-1 rounded-full text-xs sm:text-sm">
                                            <span>✓</span>
                                            Verified
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Edit Button -->
                        <a href="{{ route('profile.edit') }}"
                            class="w-full sm:w-auto px-4 sm:px-6 py-2.5 bg-white text-blue-600 font-medium rounded-lg shadow-md hover:shadow-lg hover:scale-105 transition-all duration-200 flex items-center justify-center gap-2 text-sm sm:text-base">
                            <span>✏️</span>
                            Edit Profile
                        </a>
                    </div>
                </div>
            </div>

            <!-- Mobile: Stack vertically, Desktop: Side by side -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-6">
                <!-- Account Information -->
                <div class="lg:col-span-2 space-y-4 sm:space-y-6">
                    <!-- Personal Information -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100">
                        <div class="p-4 sm:p-6 border-b border-gray-100">
                            <h3 class="text-base sm:text-lg font-semibold text-gray-800 flex items-center gap-2">
                                <span>👤</span>
                                Personal Information
                            </h3>
                        </div>
                        <div class="p-4 sm:p-6">
                            <!-- Mobile: Stack vertically, Desktop: 2 columns -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                                <!-- Full Name -->
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 uppercase mb-2">Full Name</label>
                                    <p class="text-sm sm:text-base font-medium text-gray-800 break-words">
                                        {{ Auth::user()->name }}</p>
                                </div>

                                <!-- Email Address -->
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 uppercase mb-2">Email
                                        Address</label>
                                    <p
                                        class="text-sm sm:text-base font-medium text-gray-800 flex items-center gap-2 break-all">
                                        {{ Auth::user()->email }}
                                        @if (Auth::user()->email_verified_at)
                                            <span class="text-green-600 text-xs flex-shrink-0" title="Email verified">✓</span>
                                        @else
                                            <span class="text-yellow-600 text-xs flex-shrink-0"
                                                title="Email not verified">!</span>
                                        @endif
                                    </p>
                                </div>

                                <!-- Member Since -->
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 uppercase mb-2">Member
                                        Since</label>
                                    <p class="text-sm sm:text-base font-medium text-gray-800">
                                        {{ Auth::user()->created_at->format('F j, Y') }}</p>
                                    <p class="text-xs text-gray-500 mt-1">{{ Auth::user()->created_at->diffForHumans() }}
                                    </p>
                                </div>

                                <!-- Last Updated -->
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 uppercase mb-2">Profile
                                        Updated</label>
                                    <p class="text-sm sm:text-base font-medium text-gray-800">
                                        {{ Auth::user()->updated_at->format('F j, Y') }}</p>
                                    <p class="text-xs text-gray-500 mt-1">{{ Auth::user()->updated_at->diffForHumans() }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Activity Stats -->
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700">
                        <div class="p-4 sm:p-6 border-b border-gray-100 dark:border-gray-700">
                            <h3
                                class="text-base sm:text-lg font-semibold text-gray-800 dark:text-gray-100 flex items-center gap-2">
                                <span>📊</span>
                                Activity Overview
                            </h3>
                        </div>
                        <div class="p-4 sm:p-6">
                            <!-- Mobile: 2 columns, Desktop: 3 columns -->
                            <div class="grid grid-cols-2 md:grid-cols-3 gap-3 sm:gap-4">
                                <!-- Total Searches -->
                                <div
                                    class="text-center p-3 sm:p-4 bg-blue-50 dark:bg-blue-900/30 rounded-xl border border-blue-100 dark:border-blue-800">
                                    <div class="text-2xl sm:text-3xl font-bold text-blue-600 dark:text-blue-400 mb-1">
                                        {{ $totalSearches }}</div>
                                    <p class="text-xs sm:text-sm text-gray-600 dark:text-gray-400">Total Searches</p>
                                    @if($totalSearches > 0 && $recentSearches->count() > 0)
                                        <span class="text-xs text-blue-500 dark:text-blue-400 mt-1 inline-block">Last:
                                            {{ $recentSearches->first()->last_searched_at->diffForHumans() }}</span>
                                    @endif
                                </div>

                                <!-- Saved Locations -->
                                <div
                                    class="text-center p-3 sm:p-4 bg-green-50 dark:bg-green-900/30 rounded-xl border border-green-100 dark:border-green-800">
                                    <div class="text-2xl sm:text-3xl font-bold text-green-600 dark:text-green-400 mb-1">
                                        {{ $savedLocationsCount }}</div>
                                    <p class="text-xs sm:text-sm text-gray-600 dark:text-gray-400">Saved Locations</p>
                                    @if($savedLocationsCount > 0 && $recentSavedLocations->count() > 0)
                                        <span class="text-xs text-green-500 dark:text-green-400 mt-1 inline-block">Recent:
                                            {{ $recentSavedLocations->first()->location_name }}</span>
                                    @endif
                                </div>

                                <!-- Favorites -->
                                <div
                                    class="text-center p-3 sm:p-4 bg-yellow-50 dark:bg-yellow-900/30 rounded-xl border border-yellow-100 dark:border-yellow-800 col-span-2 md:col-span-1">
                                    <div class="text-2xl sm:text-3xl font-bold text-yellow-600 dark:text-yellow-400 mb-1">
                                        {{ $topSearch ? $topSearch->search_count : 0 }}
                                    </div>
                                    <p class="text-xs sm:text-sm text-gray-600 dark:text-gray-400">Most Searched</p>
                                    @if($topSearch)
                                        <span
                                            class="text-xs text-yellow-500 dark:text-yellow-400 mt-1 inline-block truncate">{{ Str::limit($topSearch->location_name, 15) }}</span>
                                    @endif
                                </div>
                            </div>

                            @if($totalSearches > 0 || $savedLocationsCount > 0)
                                <!-- Activity Details -->
                                <div class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-700">
                                    <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">Recent Activity</h4>

                                    <div class="space-y-2">
                                        @if($recentSearches->count() > 0)
                                            @foreach($recentSearches->take(3) as $search)
                                                <div
                                                    class="flex items-center justify-between text-xs text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-700/50 p-2 rounded-lg">
                                                    <span class="flex items-center gap-2">
                                                        <span>🔍</span>
                                                        <span class="truncate">{{ Str::limit($search->location_name, 25) }}</span>
                                                    </span>
                                                    <span
                                                        class="text-gray-400 dark:text-gray-500">{{ $search->last_searched_at->diffForHumans() }}</span>
                                                </div>
                                            @endforeach
                                        @endif

                                        @if($recentSavedLocations->count() > 0)
                                            @foreach($recentSavedLocations->take(2) as $saved)
                                                <div
                                                    class="flex items-center justify-between text-xs text-gray-600 dark:text-gray-400 bg-green-50 dark:bg-green-900/20 p-2 rounded-lg">
                                                    <span class="flex items-center gap-2">
                                                        <span>{{ $saved->emoji }}</span>
                                                        <span class="truncate">{{ Str::limit($saved->name, 25) }}</span>
                                                    </span>
                                                    <span class="text-gray-400 dark:text-gray-500">Saved</span>
                                                </div>
                                            @endforeach
                                        @endif
                                    </div>
                                </div>
                            @else
                                <div class="mt-4 text-center py-6 bg-gray-50 dark:bg-gray-800/50 rounded-lg">
                                    <div class="text-3xl mb-2">📍</div>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Start exploring to see your activity!
                                    </p>
                                    <a href="{{ route('weather.map') }}"
                                        class="inline-block mt-3 text-sm text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 font-medium">
                                        Explore Weather Map →
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Sidebar Info -->
                <div class="space-y-4 sm:space-y-6">
                    <!-- Account Status -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100">
                        <div class="p-4 sm:p-6 border-b border-gray-100">
                            <h3 class="text-base sm:text-lg font-semibold text-gray-800 flex items-center gap-2">
                                <span>🔐</span>
                                Account Status
                            </h3>
                        </div>
                        <div class="p-4 sm:p-6 space-y-3 sm:space-y-4">
                            <!-- Account Active -->
                            <div
                                class="flex items-center justify-between p-3 bg-green-50 rounded-lg border border-green-200">
                                <span class="text-xs sm:text-sm font-medium text-green-800">Account Active</span>
                                <span class="text-green-600">✓</span>
                            </div>

                            <!-- Email Verification -->
                            @if (Auth::user()->email_verified_at)
                                <div
                                    class="flex items-center justify-between p-3 bg-green-50 rounded-lg border border-green-200">
                                    <span class="text-xs sm:text-sm font-medium text-green-800">Email Verified</span>
                                    <span class="text-green-600">✓</span>
                                </div>
                            @else
                                <div
                                    class="flex items-center justify-between p-3 bg-yellow-50 rounded-lg border border-yellow-200">
                                    <span class="text-xs sm:text-sm font-medium text-yellow-800">Email Not Verified</span>
                                    <span class="text-yellow-600">!</span>
                                </div>
                                <form method="POST" action="{{ route('verification.send') }}">
                                    @csrf
                                    <button type="submit"
                                        class="w-full text-xs sm:text-sm text-blue-600 hover:text-blue-700 underline text-center py-2">
                                        Resend Verification Email
                                    </button>
                                </form>
                            @endif

                            <!-- Password Status -->
                            <div class="flex items-center justify-between p-3 bg-blue-50 rounded-lg border border-blue-200">
                                <span class="text-xs sm:text-sm font-medium text-blue-800">Password Set</span>
                                <span class="text-blue-600">✓</span>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100">
                        <div class="p-4 sm:p-6 border-b border-gray-100">
                            <h3 class="text-base sm:text-lg font-semibold text-gray-800 flex items-center gap-2">
                                <span>⚡</span>
                                Quick Actions
                            </h3>
                        </div>
                        <div class="p-4 sm:p-6 space-y-2 sm:space-y-3">
                            <a href="{{ route('profile.edit') }}"
                                class="flex items-center gap-3 p-3 text-xs sm:text-sm font-medium text-gray-700 hover:bg-blue-50 hover:text-blue-700 rounded-lg transition-colors">
                                <span class="text-lg sm:text-xl">✏️</span>
                                <span>Edit Profile</span>
                            </a>

                            <a href="{{ route('weather.map') }}"
                                class="flex items-center gap-3 p-3 text-xs sm:text-sm font-medium text-gray-700 hover:bg-blue-50 hover:text-blue-700 rounded-lg transition-colors">
                                <span class="text-lg sm:text-xl">🗺️</span>
                                <span>Weather Map</span>
                            </a>

                            <a href="{{ route('dashboard') }}"
                                class="flex items-center gap-3 p-3 text-xs sm:text-sm font-medium text-gray-700 hover:bg-blue-50 hover:text-blue-700 rounded-lg transition-colors">
                                <span class="text-lg sm:text-xl">📊</span>
                                <span>Dashboard</span>
                            </a>
                        </div>
                    </div>

                    <!-- Security Tip -->
                    <div
                        class="bg-gradient-to-br from-orange-50 to-yellow-50 rounded-xl border border-orange-200 p-4 sm:p-6">
                        <div class="text-2xl sm:text-3xl mb-3">🔒</div>
                        <h4 class="font-semibold text-gray-800 mb-2 text-sm sm:text-base">Security Tip</h4>
                        <p class="text-xs sm:text-sm text-gray-600 leading-relaxed">
                            Keep your account secure by using a strong password and never sharing your login credentials.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection