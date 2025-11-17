<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="user-authenticated" content="{{ Auth::check() ? 'true' : 'false' }}">

    <title>{{ config('app.name', 'Weather Map') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script>
        // Auto-apply Tailwind dark mode based on OS preference
        (function () {
            const apply = () => {
                if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
                    document.documentElement.classList.add('dark');
                } else {
                    document.documentElement.classList.remove('dark');
                }
            };
            apply();
            try {
                window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', apply);
            } catch (e) {
                // Safari fallback
                window.matchMedia('(prefers-color-scheme: dark)').addListener(apply);
            }
        })();
    </script>
</head>

<body class="font-sans antialiased bg-gradient-primary">
    <div class="min-h-screen">
        @auth
            <!-- Authenticated Layout with Responsive Sidebar -->
            <div class="flex h-screen overflow-hidden" x-data="{ sidebarOpen: false }">
                <!-- Mobile Overlay -->
                <div x-show="sidebarOpen" @click="sidebarOpen = false"
                    x-transition:enter="transition-opacity ease-linear duration-300" x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100" x-transition:leave="transition-opacity ease-linear duration-300"
                    x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                    class="fixed inset-0 bg-gray-600 dark:bg-gray-900 bg-opacity-75 dark:bg-opacity-75 z-40 lg:hidden sidebar-overlay"
                    style="display: none;">
                </div>

                <!-- Sidebar Navigation -->
                <aside
                    class="sidebar-menu fixed lg:static inset-y-0 left-0 z-50 w-64 flex flex-col flex-shrink-0 lg:translate-x-0"
                    :class="{ 'open': sidebarOpen }" x-cloak>
                    <!-- Sidebar Header -->
                    <div class="h-16 flex items-center justify-between px-6 border-b border-gray-200 dark:border-gray-700">
                        <a href="{{ route('weather.map') }}" class="flex items-center gap-2">
                            <span class="text-2xl">🌍</span>
                            <span
                                class="text-lg font-bold bg-gradient-to-r from-blue-600 to-blue-400 dark:from-blue-400 dark:to-blue-300 bg-clip-text text-transparent">
                                {{ config('app.name', 'Weather Map') }}
                            </span>
                        </a>

                        <!-- Mobile Close Button -->
                        <button @click="sidebarOpen = false"
                            class="lg:hidden text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <!-- User Profile Section -->
                    <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                        <div class="flex items-center gap-3 mb-2">
                            <div
                                class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-500 to-blue-600 dark:from-blue-400 dark:to-blue-500 flex items-center justify-center text-white font-semibold flex-shrink-0">
                                {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-semibold text-gray-900 dark:text-gray-100 truncate">
                                    {{ Auth::user()->name }}
                                </p>
                                <p class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ Auth::user()->email }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Navigation Links -->
                    <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto custom-scrollbar">
                        <a href="{{ route('dashboard') }}" @click="sidebarOpen = false"
                            class="flex items-center gap-3 px-3 py-2.5 text-sm font-medium rounded-lg transition-colors duration-200
                                                        {{ request()->routeIs('dashboard') ? 'bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700/50' }}">
                            <span class="text-xl">📊</span>
                            <span>Dashboard</span>
                        </a>

                        <a href="{{ route('weather.map') }}" @click="sidebarOpen = false"
                            class="flex items-center gap-3 px-3 py-2.5 text-sm font-medium rounded-lg transition-colors duration-200
                                                        {{ request()->routeIs('weather.map') ? 'bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700/50' }}">
                            <span class="text-xl">🗺️</span>
                            <span>Weather Map</span>
                        </a>

                        <hr class="my-3 border-gray-200 dark:border-gray-700">

                        <a href="{{ route('profile.show') }}" @click="sidebarOpen = false"
                            class="flex items-center gap-3 px-3 py-2.5 text-sm font-medium rounded-lg transition-colors duration-200
                                                        {{ request()->routeIs('profile.show') ? 'bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700/50' }}">
                            <span class="text-xl">👤</span>
                            <span>My Profile</span>
                        </a>

                        <a href="{{ route('profile.edit') }}" @click="sidebarOpen = false"
                            class="flex items-center gap-3 px-3 py-2.5 text-sm font-medium rounded-lg transition-colors duration-200
                                                        {{ request()->routeIs('profile.edit') ? 'bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700/50' }}">
                            <span class="text-xl">⚙️</span>
                            <span>Settings</span>
                        </a>

                        <hr class="my-3 border-gray-200 dark:border-gray-700">

                        <button id="testAlertBtn" @click="sidebarOpen = false"
                            class="flex items-center gap-3 px-3 py-2.5 text-sm font-medium rounded-lg transition-colors duration-200 w-full text-left text-gray-700 dark:text-gray-300 hover:bg-orange-50 dark:hover:bg-orange-900/20 hover:text-orange-700 dark:hover:text-orange-400">
                            <span class="text-xl">🚨</span>
                            <span>Test Alert System</span>
                        </button>
                    </nav>

                    <!-- Sidebar Footer -->
                    <div class="p-3 border-t border-gray-200 dark:border-gray-700">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit"
                                class="flex items-center gap-3 w-full px-3 py-2.5 text-sm font-medium text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors duration-200">
                                <span class="text-xl">🚪</span>
                                <span>Sign Out</span>
                            </button>
                        </form>
                    </div>
                </aside>

                <!-- Main Content Area -->
                <div class="flex-1 flex flex-col overflow-hidden w-full lg:w-auto">
                    <!-- Top Bar -->
                    <header
                        class="h-16 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between px-4 lg:px-6 flex-shrink-0">
                        <!-- Mobile Menu Button -->
                        <button @click="sidebarOpen = !sidebarOpen"
                            class="lg:hidden text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 mr-4">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>

                        <div class="flex-1 min-w-0">
                            @yield('header')
                        </div>

                        <div class="hidden sm:flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400 ml-4">
                            <span class="hidden md:inline">{{ now()->format('l, F j, Y') }}</span>
                            <span class="md:hidden">{{ now()->format('M j, Y') }}</span>
                        </div>
                    </header>

                    <!-- Page Content -->
                    <main class="flex-1 overflow-y-auto bg-gray-50 dark:bg-gray-900">
                        @yield('content')
                    </main>
                </div>
            </div>
        @else
            <!-- Guest Layout -->
            <div class="min-h-screen flex flex-col">
                <!-- Navigation Bar -->
                <nav class="bg-white dark:bg-gray-800 backdrop-blur-md shadow-sm border-b border-blue-100 dark:border-gray-700 sticky top-0 z-50"
                    style="background: rgba(255, 255, 255, 0.8);">
                    <style>
                        .dark nav {
                            background: rgba(31, 41, 55, 0.8) !important;
                        }
                    </style>
                    <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8">
                        <div class="flex justify-between items-center h-16">
                            <!-- Logo/Brand -->
                            <div class="flex items-center">
                                <a href="{{ route('weather.map') }}" class="flex items-center gap-2">
                                    <span class="text-2xl">🌍</span>
                                    <span
                                        class="text-base sm:text-xl font-bold bg-gradient-to-r from-blue-600 to-blue-400 dark:from-blue-400 dark:to-blue-300 bg-clip-text text-transparent">
                                        {{ config('app.name', 'Weather Map') }}
                                    </span>
                                </a>
                            </div>

                            <!-- Guest Links -->
                            <div class="flex items-center gap-2">
                                <a href="{{ route('login') }}"
                                    class="h-10 inline-flex items-center justify-center px-4 sm:px-5 text-sm font-medium text-blue-600 dark:text-blue-300 hover:text-blue-700 dark:hover:text-blue-200 bg-transparent hover:bg-blue-50 dark:hover:bg-blue-900/30 rounded-full transition-colors duration-200">
                                    Sign In
                                </a>
                                <a href="{{ route('register') }}"
                                    class="h-10 inline-flex items-center justify-center px-4 sm:px-5 text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600 rounded-full shadow-sm hover:shadow-md transition-all duration-200">
                                    <span class="hidden sm:inline">Get Started</span>
                                    <span class="sm:hidden">Sign Up</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </nav>

                <!-- Page Content -->
                <main class="flex-1 overflow-y-auto">
                    @yield('content')
                </main>

                <!-- Footer -->
                <footer
                    class="bg-white/50 dark:bg-gray-800/50 backdrop-blur-sm border-t border-gray-100 dark:border-gray-700 py-4">
                    <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8">
                        <div
                            class="flex flex-col sm:flex-row justify-between items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
                            <p class="text-center sm:text-left">Powered by Open-Meteo & Leaflet</p>
                            <p class="text-center sm:text-right">© {{ date('Y') }} {{ config('app.name', 'Weather Map') }}
                            </p>
                        </div>
                    </div>
                </footer>
            </div>
        @endauth
    </div>

    <!-- Weather Alert Notification Component -->
    @include('components.weather-alert-notification')

    <!-- Fallback: Ensure alert system exists -->
    <script>
        // Double-check that the alert container exists
        if (!document.getElementById('weatherAlertContainer')) {
            console.warn('⚠️ Weather alert container not found, creating fallback...');

            const container = document.createElement('div');
            container.id = 'weatherAlertContainer';
            container.className = 'fixed bottom-4 right-4 z-[9999] max-w-sm w-full pointer-events-none';
            container.style.display = 'none';

            const card = document.createElement('div');
            card.id = 'weatherAlertCard';
            card.className = 'pointer-events-auto transform transition-all duration-500 ease-in-out';
            card.style.transform = 'translateY(100px)';
            card.style.opacity = '0';

            const content = document.createElement('div');
            content.id = 'weatherAlertContent';

            card.appendChild(content);
            container.appendChild(card);
            document.body.appendChild(container);

            console.log('✅ Fallback alert container created');
        }

        // Force initialize if not already done
        if (!window.weatherAlertSystem) {
            console.log('🔧 Force initializing Weather Alert System...');

            setTimeout(() => {
                if (typeof WeatherAlertNotificationSystem !== 'undefined' && !window.weatherAlertSystem) {
                    window.weatherAlertSystem = new WeatherAlertNotificationSystem();
                    console.log('✅ Weather Alert System force initialized!');
                } else if (!window.weatherAlertSystem) {
                    console.error('❌ WeatherAlertNotificationSystem class not found!');
                }
            }, 1000);
        }
    </script>

    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <!-- Test Alert Button Handler -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const testAlertBtn = document.getElementById('testAlertBtn');

            if (testAlertBtn) {
                testAlertBtn.addEventListener('click', function () {
                    // Show loading notification
                    const loadingNotification = document.createElement('div');
                    loadingNotification.id = 'alertTestLoading';
                    loadingNotification.className = 'fixed top-4 right-4 z-[10001] px-4 py-3 rounded-lg shadow-2xl bg-yellow-500 text-white flex items-center gap-2';
                    loadingNotification.innerHTML = `
                        <div class="animate-spin rounded-full h-4 w-4 border-2 border-white border-t-transparent"></div>
                        <span class="text-sm font-medium">Initializing alert system...</span>
                    `;
                    document.body.appendChild(loadingNotification);

                    let attempts = 0;
                    const maxAttempts = 20;

                    const tryTriggerAlert = () => {
                        attempts++;

                        if (!window.weatherAlertSystem) {
                            console.warn(`Weather alert system not ready, waiting... (attempt ${attempts}/${maxAttempts})`);
                            console.log('window.weatherAlertSystem:', window.weatherAlertSystem);
                            console.log('typeof WeatherAlertNotificationSystem:', typeof WeatherAlertNotificationSystem);

                            if (attempts >= maxAttempts) {
                                const loading = document.getElementById('alertTestLoading');
                                if (loading) loading.remove();

                                const errorNotification = document.createElement('div');
                                errorNotification.className = 'fixed top-4 right-4 z-[10001] px-4 py-3 rounded-lg shadow-2xl bg-red-500 text-white flex items-center gap-2';
                                errorNotification.innerHTML = `
                                    <span class="text-xl">❌</span>
                                    <div>
                                        <div class="text-sm font-medium">Alert system failed to initialize</div>
                                        <div class="text-xs opacity-90">Try refreshing the page</div>
                                    </div>
                                `;
                                document.body.appendChild(errorNotification);

                                setTimeout(() => {
                                    errorNotification.style.opacity = '0';
                                    errorNotification.style.transform = 'translateX(400px)';
                                    errorNotification.style.transition = 'all 0.3s ease';
                                    setTimeout(() => errorNotification.remove(), 300);
                                }, 5000);

                                return;
                            }

                            setTimeout(tryTriggerAlert, 500);
                            return;
                        }

                        const loading = document.getElementById('alertTestLoading');
                        if (loading) loading.remove();

                        const dummyAlert = {
                            type: 'thermal_comfort',
                            severity: 'danger',
                            title: 'Extreme Heat Danger',
                            message: 'Feels like temperature is extremely dangerous at 56.2°C. Heat stroke is imminent. Stay indoors in air conditioning. Avoid all outdoor activities.',
                            icon: '🔥',
                            value: 56.2,
                            unit: '°C',
                            details: {
                                actual_temperature: 42.5,
                                humidity: 78,
                                wind_speed: 5,
                                category: 'Extreme Danger',
                                type: 'heat'
                            }
                        };

                        const dummyLocation = 'Test Location - Extreme Conditions';

                        console.log('🧪 Testing weather alert system with dummy data...');
                        console.log('Using window.weatherAlertSystem:', window.weatherAlertSystem);
                        window.weatherAlertSystem.showAlert(dummyAlert, dummyLocation);

                        const notification = document.createElement('div');
                        notification.className = 'fixed top-4 right-4 z-[10001] px-4 py-3 rounded-lg shadow-2xl bg-blue-500 text-white flex items-center gap-2';
                        notification.innerHTML = `
                            <span class="text-xl">✅</span>
                            <span class="text-sm font-medium">Alert system test activated</span>
                        `;
                        document.body.appendChild(notification);

                        setTimeout(() => {
                            notification.style.opacity = '0';
                            notification.style.transform = 'translateX(400px)';
                            notification.style.transition = 'all 0.3s ease';
                            setTimeout(() => notification.remove(), 300);
                        }, 3000);
                    };

                    tryTriggerAlert();
                });
            }
        });
    </script>

    @stack('scripts')
</body>

</html>