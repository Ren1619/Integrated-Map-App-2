<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Weather Map') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans antialiased bg-gradient-to-br from-blue-50 via-white to-blue-50">
    <div class="min-h-screen">
        @auth
            <!-- Authenticated Layout with Sidebar -->
            <div class="flex h-screen overflow-hidden">
                <!-- Sidebar Navigation -->
                <aside class="w-64 bg-white border-r border-gray-200 flex flex-col flex-shrink-0" x-data="{ open: true }">
                    <!-- Sidebar Header -->
                    <div class="h-16 flex items-center justify-between px-6 border-b border-gray-200">
                        <a href="{{ route('weather.map') }}" class="flex items-center gap-2">
                            <span class="text-2xl">🌍</span>
                            <span
                                class="text-lg font-bold bg-gradient-to-r from-blue-600 to-blue-400 bg-clip-text text-transparent">
                                {{ config('app.name', 'Weather Map') }}
                            </span>
                        </a>
                    </div>

                    <!-- User Profile Section -->
                    <div class="p-4 border-b border-gray-200">
                        <div class="flex items-center gap-3 mb-2">
                            <div
                                class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center text-white font-semibold">
                                {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-semibold text-gray-900 truncate">{{ Auth::user()->name }}</p>
                                <p class="text-xs text-gray-500 truncate">{{ Auth::user()->email }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Navigation Links -->
                    <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
                        <a href="{{ route('dashboard') }}"
                            class="flex items-center gap-3 px-3 py-2.5 text-sm font-medium rounded-lg transition-colors duration-200
                                    {{ request()->routeIs('dashboard') ? 'bg-blue-50 text-blue-700' : 'text-gray-700 hover:bg-gray-50' }}">
                            <span class="text-xl">📊</span>
                            <span>Dashboard</span>
                        </a>

                        <a href="{{ route('weather.map') }}"
                            class="flex items-center gap-3 px-3 py-2.5 text-sm font-medium rounded-lg transition-colors duration-200
                                    {{ request()->routeIs('weather.map') ? 'bg-blue-50 text-blue-700' : 'text-gray-700 hover:bg-gray-50' }}">
                            <span class="text-xl">🗺️</span>
                            <span>{{ config('app.name', 'Weather Map') }}</span>
                        </a>

                        <!-- <div class="relative">
                            <a href="#"
                                class="flex items-center gap-3 px-3 py-2.5 text-sm font-medium rounded-lg text-gray-400 cursor-not-allowed">
                                <span class="text-xl">📍</span>
                                <span>Saved Searches</span>
                                <span
                                    class="ml-auto text-xs bg-yellow-100 text-yellow-700 px-2 py-0.5 rounded-full">Soon</span>
                            </a>
                        </div>

                        <div class="relative">
                            <a href="#"
                                class="flex items-center gap-3 px-3 py-2.5 text-sm font-medium rounded-lg text-gray-400 cursor-not-allowed">
                                <span class="text-xl">⭐</span>
                                <span>Favorites</span>
                                <span
                                    class="ml-auto text-xs bg-yellow-100 text-yellow-700 px-2 py-0.5 rounded-full">Soon</span>
                            </a>
                        </div> -->

                        <hr class="my-3 border-gray-200">

                        <a href="{{ route('profile.show') }}"
                            class="flex items-center gap-3 px-3 py-2.5 text-sm font-medium rounded-lg transition-colors duration-200
                                    {{ request()->routeIs('profile.show') ? 'bg-blue-50 text-blue-700' : 'text-gray-700 hover:bg-gray-50' }}">
                            <span class="text-xl">👤</span>
                            <span>My Profile</span>
                        </a>

                        <a href="{{ route('profile.edit') }}"
                            class="flex items-center gap-3 px-3 py-2.5 text-sm font-medium rounded-lg transition-colors duration-200
                                    {{ request()->routeIs('profile.edit') ? 'bg-blue-50 text-blue-700' : 'text-gray-700 hover:bg-gray-50' }}">
                            <span class="text-xl">⚙️</span>
                            <span>Settings</span>
                        </a>
                    </nav>

                    <!-- Sidebar Footer -->
                    <div class="p-3 border-t border-gray-200">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit"
                                class="flex items-center gap-3 w-full px-3 py-2.5 text-sm font-medium text-red-600 hover:bg-red-50 rounded-lg transition-colors duration-200">
                                <span class="text-xl">🚪</span>
                                <span>Sign Out</span>
                            </button>
                        </form>
                    </div>
                </aside>

                <!-- Main Content Area -->
                <div class="flex-1 flex flex-col overflow-hidden">
                    <!-- Top Bar (Optional - for breadcrumbs or actions) -->
                    <header class="h-16 bg-white border-b border-gray-200 flex items-center justify-between px-6">
                        <div>
                            @yield('header')
                        </div>
                        <div class="flex items-center gap-2 text-sm text-gray-600">
                            <span>{{ now()->format('l, F j, Y') }}</span>
                        </div>
                    </header>

                    <!-- Page Content -->
                    <main class="flex-1 overflow-y-auto bg-gray-50">
                        @yield('content')
                    </main>
                </div>
            </div>
        @else
            <!-- Guest Layout (Original Design) -->
            <div class="min-h-screen flex flex-col">
                <!-- Navigation Bar -->
                <nav class="bg-white/80 backdrop-blur-md shadow-sm border-b border-blue-100 sticky top-0 z-50">
                    <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8">
                        <div class="flex justify-between h-16">
                            <!-- Logo/Brand -->
                            <div class="flex items-center">
                                <a href="{{ route('weather.map') }}" class="flex items-center gap-2">
                                    <span class="text-2xl">🌍</span>
                                    <span
                                        class="text-xl font-bold bg-gradient-to-r from-blue-600 to-blue-400 bg-clip-text text-transparent">
                                        {{ config('app.name', 'Weather Map') }}
                                    </span>
                                </a>
                            </div>

                            <!-- Guest Links -->
                            <div class="flex items-center gap-2">
                                <a href="{{ route('login') }}"
                                    class="px-4 py-2 text-sm font-medium text-blue-600 hover:text-blue-700 hover:bg-blue-50 rounded-lg transition-colors duration-200">
                                    Sign In
                                </a>
                                <a href="{{ route('register') }}"
                                    class="px-4 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-lg shadow-sm hover:shadow-md transition-all duration-200">
                                    Get Started
                                </a>
                            </div>
                        </div>
                    </div>
                </nav>

                <!-- Page Content -->
                <main class="flex-1 overflow-y-auto">
                    @yield('content')
                </main>

                <!-- Optional Footer -->
                <footer class="bg-white/50 backdrop-blur-sm border-t border-gray-100 py-4">
                    <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8">
                        <div class="flex justify-between items-center text-sm text-gray-600">
                            <p>Powered by Open-Meteo & Leaflet</p>
                            <p class="hidden sm:block">© {{ date('Y') }} {{ config('app.name', 'Weather Map') }}. All rights reserved.</p>
                        </div>
                    </div>
                </footer>
            </div>
        @endauth
    </div>

    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    @stack('scripts')
</body>
    
</html>