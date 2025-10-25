@extends('layouts.app')

@section('header')
    <h1 class="text-xl font-semibold text-gray-800">Dashboard</h1>
@endsection

@section('content')
    <div class="p-6">
        <div class="p-6">
            <!-- Welcome Section -->
            <div class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-2xl shadow-sm mb-6 overflow-hidden">
                <div class="p-8 text-white">
                    <h2 class="text-3xl font-bold mb-2">Welcome back, {{ Auth::user()->name }}! 👋</h2>
                    <p class="text-blue-100 text-lg">Here's your current weather</p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">

                <!-- Current Weather Section (Left Side) -->
                <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                    <div class="p-6 border-b border-gray-100 bg-gradient-to-r from-blue-50 to-indigo-50">
                        <div class="flex items-center justify-between">
                            <h3 class="text-xl font-bold text-gray-800">Current Weather</h3>
                            <button id="refreshWeather"
                                class="flex items-center gap-2 px-4 py-2 bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow text-sm font-medium text-gray-700 hover:text-blue-600">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                                    </path>
                                </svg>
                                Refresh
                            </button>
                        </div>
                    </div>

                    <div id="weatherContent" class="p-8">
                        <!-- Loading State -->
                        <div id="loadingState" class="flex flex-col items-center justify-center py-12">
                            <div class="animate-spin rounded-full h-16 w-16 border-b-2 border-blue-500 mb-4"></div>
                            <p class="text-gray-600">Getting your location and weather data...</p>
                        </div>

                        <!-- Error State -->
                        <div id="errorState" class="hidden flex-col items-center justify-center py-12">
                            <div class="text-6xl mb-4">⚠️</div>
                            <h4 class="text-xl font-semibold text-gray-800 mb-2">Unable to Load Weather</h4>
                            <p class="text-gray-600 text-center mb-4" id="errorMessage"></p>
                            <button onclick="loadWeather()"
                                class="px-6 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors">
                                Try Again
                            </button>
                        </div>

                        <!-- Weather Data -->
                        <div id="weatherData" class="hidden">
                            <!-- Location Info -->
                            <div class="flex items-center gap-2 mb-6 text-gray-600">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z">
                                    </path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                                <span id="locationName" class="font-medium">Loading...</span>
                            </div>

                            <!-- Main Temperature Display -->
                            <div class="flex items-start justify-between mb-8">
                                <div class="flex items-start gap-6">
                                    <div id="weatherIcon" class="text-8xl">🌤️</div>
                                    <div>
                                        <div class="text-6xl font-bold text-gray-800 mb-2">
                                            <span id="temperature">--</span>°C
                                        </div>
                                        <p id="weatherDescription" class="text-xl text-gray-600 capitalize">Loading...</p>
                                        <p class="text-sm text-gray-500 mt-2">
                                            Feels like <span id="feelsLike">--</span>°C
                                        </p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm text-gray-500">Last updated</p>
                                    <p id="lastUpdated" class="text-sm font-medium text-gray-700">--</p>
                                </div>
                            </div>

                            <!-- Weather Details Grid -->
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                                <!-- Humidity -->
                                <div class="bg-blue-50 rounded-xl p-4">
                                    <div class="flex items-center gap-2 mb-2">
                                        <span class="text-2xl">💧</span>
                                        <span class="text-xs font-medium text-gray-600 uppercase">Humidity</span>
                                    </div>
                                    <p class="text-2xl font-bold text-gray-800"><span id="humidity">--</span>%</p>
                                </div>

                                <!-- Wind Speed -->
                                <div class="bg-green-50 rounded-xl p-4">
                                    <div class="flex items-center gap-2 mb-2">
                                        <span class="text-2xl">💨</span>
                                        <span class="text-xs font-medium text-gray-600 uppercase">Wind Speed</span>
                                    </div>
                                    <p class="text-2xl font-bold text-gray-800"><span id="windSpeed">--</span> km/h</p>
                                </div>

                                <!-- Pressure -->
                                <div class="bg-purple-50 rounded-xl p-4">
                                    <div class="flex items-center gap-2 mb-2">
                                        <span class="text-2xl">🌡️</span>
                                        <span class="text-xs font-medium text-gray-600 uppercase">Pressure</span>
                                    </div>
                                    <p class="text-2xl font-bold text-gray-800"><span id="pressure">--</span> hPa</p>
                                </div>

                                <!-- Precipitation -->
                                <div class="bg-indigo-50 rounded-xl p-4">
                                    <div class="flex items-center gap-2 mb-2">
                                        <span class="text-2xl">🌧️</span>
                                        <span class="text-xs font-medium text-gray-600 uppercase">Precipitation</span>
                                    </div>
                                    <p class="text-2xl font-bold text-gray-800"><span id="precipitation">--</span> mm</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Weather Alerts Section (Right Side) -->
                <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                    <div class="p-6 border-b border-gray-100 bg-gradient-to-r from-orange-50 to-red-50">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <span class="text-2xl">🚨</span>
                                <h3 class="text-xl font-bold text-gray-800">Weather Alerts</h3>
                            </div>
                            <button id="refreshAlerts"
                                class="flex items-center gap-2 px-4 py-2 bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow text-sm font-medium text-gray-700 hover:text-orange-600">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                                    </path>
                                </svg>
                                Refresh
                            </button>
                        </div>
                    </div>

                    <div id="alertsContent" class="p-8">
                        <!-- Loading State -->
                        <div id="alertsLoadingState" class="flex flex-col items-center justify-center py-12">
                            <div class="animate-spin rounded-full h-16 w-16 border-b-2 border-orange-500 mb-4"></div>
                            <p class="text-gray-600">Analyzing weather conditions...</p>
                        </div>

                        <!-- Error State -->
                        <div id="alertsErrorState" class="hidden flex-col items-center justify-center py-12">
                            <div class="text-6xl mb-4">⚠️</div>
                            <h4 class="text-xl font-semibold text-gray-800 mb-2">Unable to Load Alerts</h4>
                            <p class="text-gray-600 text-center mb-4" id="alertsErrorMessage"></p>
                            <button onclick="loadWeatherAlerts()"
                                class="px-6 py-2 bg-orange-500 text-white rounded-lg hover:bg-orange-600 transition-colors">
                                Try Again
                            </button>
                        </div>

                        <!-- Alerts Data -->
                        <div id="alertsData" class="hidden">
                            <!-- Alert Statistics -->
                            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-6">
                                <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl p-3 text-center">
                                    <div class="text-2xl font-bold text-blue-700" id="totalAlerts">0</div>
                                    <div class="text-xs text-blue-600 font-medium mt-1">Total</div>
                                </div>
                                <div class="bg-gradient-to-br from-red-50 to-red-100 rounded-xl p-3 text-center">
                                    <div class="text-2xl font-bold text-red-700" id="dangerAlerts">0</div>
                                    <div class="text-xs text-red-600 font-medium mt-1">Danger</div>
                                </div>
                                <div class="bg-gradient-to-br from-yellow-50 to-yellow-100 rounded-xl p-3 text-center">
                                    <div class="text-2xl font-bold text-yellow-700" id="warningAlerts">0</div>
                                    <div class="text-xs text-yellow-600 font-medium mt-1">Warning</div>
                                </div>
                                <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-xl p-3 text-center">
                                    <div class="text-2xl font-bold text-green-700" id="infoAlerts">0</div>
                                    <div class="text-xs text-green-600 font-medium mt-1">Info</div>
                                </div>
                            </div>

                            <!-- Location Info -->
                            <div class="flex items-center gap-2 mb-4 text-gray-600">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z">
                                    </path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                                <span id="alertsLocationName" class="font-medium">Loading...</span>
                            </div>

                            <!-- Scrollable Alerts Container -->
                            <div class="max-h-96 overflow-y-auto custom-scrollbar">
                                <!-- No Alerts Message -->
                                <div id="noAlertsMessage" class="hidden">
                                    <div
                                        class="bg-gradient-to-br from-green-50 to-emerald-50 rounded-2xl p-8 text-center border-2 border-green-200">
                                        <div class="text-6xl mb-4">✅</div>
                                        <h4 class="text-2xl font-bold text-green-800 mb-2">All Clear!</h4>
                                        <p class="text-green-700 text-lg">No notable alerts in this area</p>
                                        <p class="text-green-600 text-sm mt-2">Current weather conditions are within normal
                                            ranges</p>
                                    </div>
                                </div>

                                <!-- Active Alerts List -->
                                <div id="activeAlertsList" class="space-y-4"></div>
                            </div>

                            <!-- Last Updated -->
                            <div class="mt-6 text-center text-sm text-gray-500">
                                Last checked: <span id="alertsLastUpdated" class="font-medium">--</span>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Additional Features Section -->
            <div class="mb-4">
                <h3 class="text-lg font-semibold text-gray-700 mb-4">Additional Features</h3>
            </div>


            <!-- Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                <!-- Total Searches -->
                <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                    <div class="flex items-center justify-between mb-3">
                        <div class="text-3xl">🔍</div>
                        <span class="text-xs bg-blue-100 text-blue-700 px-2 py-1 rounded-full font-medium">Coming
                            Soon</span>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-800">0</h3>
                    <p class="text-sm text-gray-600 mt-1">Total Searches</p>
                </div>

                <!-- Saved Locations -->
                <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                    <div class="flex items-center justify-between mb-3">
                        <div class="text-3xl">📍</div>
                        <span class="text-xs bg-blue-100 text-blue-700 px-2 py-1 rounded-full font-medium">Coming
                            Soon</span>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-800">0</h3>
                    <p class="text-sm text-gray-600 mt-1">Saved Locations</p>
                </div>

                <!-- Favorites -->
                <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                    <div class="flex items-center justify-between mb-3">
                        <div class="text-3xl">⭐</div>
                        <span class="text-xs bg-blue-100 text-blue-700 px-2 py-1 rounded-full font-medium">Coming
                            Soon</span>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-800">0</h3>
                    <p class="text-sm text-gray-600 mt-1">Favorite Spots</p>
                </div>

                <!-- Member Since -->
                <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                    <div class="flex items-center justify-between mb-3">
                        <div class="text-3xl">📅</div>
                        <span class="text-xs bg-green-100 text-green-700 px-2 py-1 rounded-full font-medium">Active</span>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-800">{{ Auth::user()->created_at->diffForHumans(null, true) }}
                    </h3>
                    <p class="text-sm text-gray-600 mt-1">Member Since</p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Quick Actions -->
                <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-gray-100">
                    <div class="p-6 border-b border-gray-100">
                        <h3 class="text-lg font-semibold text-gray-800">Quick Actions</h3>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Weather Map -->
                            <a href="{{ route('weather.map') }}"
                                class="group flex items-start gap-4 p-4 rounded-xl border-2 border-gray-200 hover:border-blue-400 hover:bg-blue-50 transition-all duration-300">
                                <div class="text-4xl">🗺️</div>
                                <div>
                                    <h4 class="font-semibold text-gray-800 group-hover:text-blue-700 mb-1">Explore Weather
                                        Map
                                    </h4>
                                    <p class="text-sm text-gray-600">View real-time weather data worldwide</p>
                                </div>
                            </a>

                            <!-- Recent Searches -->
                            <div
                                class="group flex items-start gap-4 p-4 rounded-xl border-2 border-gray-200 bg-gray-50 opacity-60 cursor-not-allowed">
                                <div class="text-4xl">🔎</div>
                                <div>
                                    <h4 class="font-semibold text-gray-800 mb-1 flex items-center gap-2">
                                        View Recent Searches
                                        <span
                                            class="text-xs bg-yellow-100 text-yellow-700 px-2 py-0.5 rounded-full">Soon</span>
                                    </h4>
                                    <p class="text-sm text-gray-600">Access your search history</p>
                                </div>
                            </div>

                            <!-- Favorites -->
                            <div
                                class="group flex items-start gap-4 p-4 rounded-xl border-2 border-gray-200 bg-gray-50 opacity-60 cursor-not-allowed">
                                <div class="text-4xl">⭐</div>
                                <div>
                                    <h4 class="font-semibold text-gray-800 mb-1 flex items-center gap-2">
                                        Manage Favorites
                                        <span
                                            class="text-xs bg-yellow-100 text-yellow-700 px-2 py-0.5 rounded-full">Soon</span>
                                    </h4>
                                    <p class="text-sm text-gray-600">Organize your favorite locations</p>
                                </div>
                            </div>

                            <!-- Settings -->
                            <a href="{{ route('profile.edit') }}"
                                class="group flex items-start gap-4 p-4 rounded-xl border-2 border-gray-200 hover:border-blue-400 hover:bg-blue-50 transition-all duration-300">
                                <div class="text-4xl">⚙️</div>
                                <div>
                                    <h4 class="font-semibold text-gray-800 group-hover:text-blue-700 mb-1">Account Settings
                                    </h4>
                                    <p class="text-sm text-gray-600">Manage your profile and preferences</p>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Account Info & Tips -->
                <div class="space-y-6">
                    <!-- Account Info -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100">
                        <div class="p-6 border-b border-gray-100">
                            <h3 class="text-lg font-semibold text-gray-800">Account Info</h3>
                        </div>
                        <div class="p-6 space-y-4">
                            <div class="flex items-start gap-3">
                                <span class="text-2xl">📧</span>
                                <div>
                                    <p class="text-xs font-medium text-gray-500 uppercase">Email</p>
                                    <p class="text-sm text-gray-800 mt-0.5">{{ Auth::user()->email }}</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-3">
                                <span class="text-2xl">📅</span>
                                <div>
                                    <p class="text-xs font-medium text-gray-500 uppercase">Joined</p>
                                    <p class="text-sm text-gray-800 mt-0.5">{{ Auth::user()->created_at->format('F j, Y') }}
                                    </p>
                                </div>
                            </div>
                            <div class="flex items-start gap-3">
                                <span class="text-2xl">🔐</span>
                                <div>
                                    <p class="text-xs font-medium text-gray-500 uppercase">Status</p>
                                    <p class="text-sm text-gray-800 mt-0.5">
                                        <span class="inline-flex items-center gap-1">
                                            <span class="w-2 h-2 bg-green-500 rounded-full"></span>
                                            Active Account
                                        </span>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tips & Updates -->
                    <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl shadow-sm overflow-hidden">
                        <div class="p-6 text-white">
                            <div class="text-3xl mb-3">💡</div>
                            <h3 class="text-lg font-semibold mb-2">Coming Soon!</h3>
                            <p class="text-sm text-blue-100 leading-relaxed">
                                We're working on exciting new features including saved search history, favorite locations,
                                and
                                personalized weather alerts!
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <script>
            // Weather icons mapping
            const weatherIcons = {
                0: '☀️',    // Clear sky
                1: '🌤️',   // Mainly clear
                2: '⛅',    // Partly cloudy
                3: '☁️',    // Overcast
                45: '🌫️',  // Foggy
                48: '🌫️',  // Depositing rime fog
                51: '🌦️',  // Light drizzle
                53: '🌦️',  // Moderate drizzle
                55: '🌦️',  // Dense drizzle
                61: '🌧️',  // Slight rain
                63: '🌧️',  // Moderate rain
                65: '🌧️',  // Heavy rain
                71: '🌨️',  // Slight snow
                73: '🌨️',  // Moderate snow
                75: '🌨️',  // Heavy snow
                77: '❄️',   // Snow grains
                80: '🌦️',  // Slight rain showers
                81: '🌧️',  // Moderate rain showers
                82: '⛈️',   // Violent rain showers
                85: '🌨️',  // Slight snow showers
                86: '🌨️',  // Heavy snow showers
                95: '⛈️',   // Thunderstorm
                96: '⛈️',   // Thunderstorm with slight hail
                99: '⛈️'    // Thunderstorm with heavy hail
            };

            const weatherDescriptions = {
                0: 'Clear sky',
                1: 'Mainly clear',
                2: 'Partly cloudy',
                3: 'Overcast',
                45: 'Foggy',
                48: 'Depositing rime fog',
                51: 'Light drizzle',
                53: 'Moderate drizzle',
                55: 'Dense drizzle',
                61: 'Slight rain',
                63: 'Moderate rain',
                65: 'Heavy rain',
                71: 'Slight snow',
                73: 'Moderate snow',
                75: 'Heavy snow',
                77: 'Snow grains',
                80: 'Slight rain showers',
                81: 'Moderate rain showers',
                82: 'Violent rain showers',
                85: 'Slight snow showers',
                86: 'Heavy snow showers',
                95: 'Thunderstorm',
                96: 'Thunderstorm with slight hail',
                99: 'Thunderstorm with heavy hail'
            };

            async function loadWeather() {
                const loadingState = document.getElementById('loadingState');
                const errorState = document.getElementById('errorState');
                const weatherData = document.getElementById('weatherData');

                // Show loading
                loadingState.classList.remove('hidden');
                loadingState.classList.add('flex');
                errorState.classList.add('hidden');
                errorState.classList.remove('flex');
                weatherData.classList.add('hidden');

                try {
                    // Get user's location
                    const position = await new Promise((resolve, reject) => {
                        if (!navigator.geolocation) {
                            reject(new Error('Geolocation is not supported by your browser'));
                        }
                        navigator.geolocation.getCurrentPosition(resolve, reject);
                    });

                    const { latitude, longitude } = position.coords;

                    // Fetch weather data from Open-Meteo API
                    const weatherResponse = await fetch(
                        `https://api.open-meteo.com/v1/forecast?latitude=${latitude}&longitude=${longitude}&current=temperature_2m,relative_humidity_2m,apparent_temperature,precipitation,weather_code,surface_pressure,wind_speed_10m&timezone=auto`
                    );

                    if (!weatherResponse.ok) {
                        throw new Error('Failed to fetch weather data');
                    }

                    const weather = await weatherResponse.json();

                    // Fetch location name using reverse geocoding
                    let locationName = `${latitude.toFixed(2)}, ${longitude.toFixed(2)}`;
                    try {
                        const geoResponse = await fetch(
                            `https://geocoding-api.open-meteo.com/v1/search?latitude=${latitude}&longitude=${longitude}&count=1`
                        );
                        if (geoResponse.ok) {
                            const geoData = await geoResponse.json();
                            if (geoData.results && geoData.results.length > 0) {
                                const location = geoData.results[0];
                                locationName = `${location.name}${location.admin1 ? ', ' + location.admin1 : ''}${location.country ? ', ' + location.country : ''}`;
                            }
                        }
                    } catch (e) {
                        console.warn('Could not fetch location name:', e);
                    }

                    // Update UI
                    const current = weather.current;
                    const weatherCode = current.weather_code;

                    document.getElementById('locationName').textContent = locationName;
                    document.getElementById('temperature').textContent = Math.round(current.temperature_2m);
                    document.getElementById('feelsLike').textContent = Math.round(current.apparent_temperature);
                    document.getElementById('humidity').textContent = current.relative_humidity_2m;
                    document.getElementById('windSpeed').textContent = Math.round(current.wind_speed_10m);
                    document.getElementById('pressure').textContent = Math.round(current.surface_pressure);
                    document.getElementById('precipitation').textContent = current.precipitation;
                    document.getElementById('weatherIcon').textContent = weatherIcons[weatherCode] || '🌤️';
                    document.getElementById('weatherDescription').textContent = weatherDescriptions[weatherCode] || 'Unknown';
                    document.getElementById('lastUpdated').textContent = new Date().toLocaleTimeString('en-US', {
                        hour: '2-digit',
                        minute: '2-digit'
                    });

                    // Show weather data
                    loadingState.classList.add('hidden');
                    loadingState.classList.remove('flex');
                    weatherData.classList.remove('hidden');

                } catch (error) {
                    console.error('Error loading weather:', error);

                    // Show error state
                    loadingState.classList.add('hidden');
                    loadingState.classList.remove('flex');
                    errorState.classList.remove('hidden');
                    errorState.classList.add('flex');

                    let errorMessage = 'Unable to load weather data. ';
                    if (error.message.includes('Geolocation')) {
                        errorMessage += 'Please enable location access in your browser settings.';
                    } else if (error.code === 1) {
                        errorMessage += 'Location access was denied. Please enable it to see your weather.';
                    } else if (error.code === 2) {
                        errorMessage += 'Location information is unavailable.';
                    } else if (error.code === 3) {
                        errorMessage += 'Location request timed out.';
                    } else {
                        errorMessage += 'Please try again later.';
                    }

                    document.getElementById('errorMessage').textContent = errorMessage;
                }
            }

            let alertsRefreshInterval;

            async function loadWeatherAlerts() {
                const loadingState = document.getElementById('alertsLoadingState');
                const errorState = document.getElementById('alertsErrorState');
                const alertsData = document.getElementById('alertsData');
                const noAlertsMessage = document.getElementById('noAlertsMessage');
                const activeAlertsList = document.getElementById('activeAlertsList');

                // Show loading
                loadingState.classList.remove('hidden');
                loadingState.classList.add('flex');
                errorState.classList.add('hidden');
                errorState.classList.remove('flex');
                alertsData.classList.add('hidden');

                try {
                    // Get user's location
                    const position = await new Promise((resolve, reject) => {
                        if (!navigator.geolocation) {
                            reject(new Error('Geolocation is not supported by your browser'));
                        }
                        navigator.geolocation.getCurrentPosition(resolve, reject);
                    });

                    const { latitude, longitude } = position.coords;

                    // Fetch weather alerts
                    const alertsResponse = await fetch(
                        `/weather/alerts?lat=${latitude}&lng=${longitude}`
                    );

                    if (!alertsResponse.ok) {
                        throw new Error('Failed to fetch weather alerts');
                    }

                    const alertsResult = await alertsResponse.json();

                    if (!alertsResult.success) {
                        throw new Error(alertsResult.message || 'Failed to load alerts');
                    }

                    const { alerts, statistics, location } = alertsResult.data;

                    // Update statistics
                    document.getElementById('totalAlerts').textContent = statistics.total;
                    document.getElementById('dangerAlerts').textContent = statistics.danger;
                    document.getElementById('warningAlerts').textContent = statistics.warning;
                    document.getElementById('infoAlerts').textContent = statistics.info;
                    document.getElementById('alertsLocationName').textContent = location;
                    document.getElementById('alertsLastUpdated').textContent = new Date().toLocaleTimeString('en-US', {
                        hour: '2-digit',
                        minute: '2-digit'
                    });

                    // Clear previous alerts
                    activeAlertsList.innerHTML = '';

                    if (alerts.length === 0) {
                        // Show no alerts message
                        noAlertsMessage.classList.remove('hidden');
                        activeAlertsList.classList.add('hidden');
                    } else {
                        // Hide no alerts message and show alerts
                        noAlertsMessage.classList.add('hidden');
                        activeAlertsList.classList.remove('hidden');

                        // Render each alert
                        alerts.forEach(alert => {
                            const alertCard = createAlertCard(alert);
                            activeAlertsList.appendChild(alertCard);
                        });
                    }

                    // Show alerts data
                    loadingState.classList.add('hidden');
                    loadingState.classList.remove('flex');
                    alertsData.classList.remove('hidden');

                } catch (error) {
                    console.error('Error loading weather alerts:', error);

                    // Show error state
                    loadingState.classList.add('hidden');
                    loadingState.classList.remove('flex');
                    errorState.classList.remove('hidden');
                    errorState.classList.add('flex');

                    let errorMessage = 'Unable to load weather alerts. ';
                    if (error.message.includes('Geolocation')) {
                        errorMessage += 'Please enable location access in your browser settings.';
                    } else if (error.code === 1) {
                        errorMessage += 'Location access was denied. Please enable it to see alerts.';
                    } else {
                        errorMessage += 'Please try again later.';
                    }

                    document.getElementById('alertsErrorMessage').textContent = errorMessage;
                }
            }

            function createAlertCard(alert) {
                const card = document.createElement('div');

                // Severity color mapping
                const severityColors = {
                    danger: {
                        bg: 'from-red-50 to-red-100',
                        border: 'border-red-300',
                        text: 'text-red-800',
                        badge: 'bg-red-600',
                        detailBg: 'bg-red-200/50'
                    },
                    warning: {
                        bg: 'from-yellow-50 to-yellow-100',
                        border: 'border-yellow-300',
                        text: 'text-yellow-800',
                        badge: 'bg-yellow-600',
                        detailBg: 'bg-yellow-200/50'
                    },
                    info: {
                        bg: 'from-blue-50 to-blue-100',
                        border: 'border-blue-300',
                        text: 'text-blue-800',
                        badge: 'bg-blue-600',
                        detailBg: 'bg-blue-200/50'
                    }
                };

                const colors = severityColors[alert.severity] || severityColors.info;

                card.className = `bg-gradient-to-r ${colors.bg} rounded-xl p-5 border-2 ${colors.border} shadow-sm hover:shadow-md transition-shadow`;

                // Build value display
                let valueDisplay = '';
                if (alert.value !== undefined && alert.value !== null) {
                    valueDisplay = `<span class="text-2xl font-bold ${colors.text}">${alert.value}${alert.unit}</span>`;
                }

                // Build details section for heat index and wind chill
                let detailsDisplay = '';
                if (alert.details) {
                    const details = alert.details;
                    let detailItems = [];

                    if (alert.type === 'heat_index') {
                        detailItems.push(`
                        <div class="text-xs ${colors.text}">
                            <span class="font-semibold">Temperature:</span> ${details.temperature}°C
                        </div>
                    `);
                        detailItems.push(`
                        <div class="text-xs ${colors.text}">
                            <span class="font-semibold">Humidity:</span> ${details.humidity}%
                        </div>
                    `);
                        detailItems.push(`
                        <div class="text-xs ${colors.text}">
                            <span class="font-semibold">Category:</span> ${details.category}
                        </div>
                    `);
                    } else if (alert.type === 'wind_chill') {
                        detailItems.push(`
                        <div class="text-xs ${colors.text}">
                            <span class="font-semibold">Temperature:</span> ${details.temperature}°C
                        </div>
                    `);
                        detailItems.push(`
                        <div class="text-xs ${colors.text}">
                            <span class="font-semibold">Wind Speed:</span> ${details.wind_speed} km/h
                        </div>
                    `);
                        detailItems.push(`
                        <div class="text-xs ${colors.text}">
                            <span class="font-semibold">Category:</span> ${details.category}
                        </div>
                    `);
                    }

                    if (detailItems.length > 0) {
                        detailsDisplay = `
                        <div class="${colors.detailBg} rounded-lg p-3 mt-3 space-y-1">
                            ${detailItems.join('')}
                        </div>
                    `;
                    }
                }

                card.innerHTML = `
                <div class="flex items-start gap-4">
                    <div class="text-4xl flex-shrink-0">${alert.icon}</div>
                    <div class="flex-1">
                        <div class="flex items-start justify-between gap-4 mb-2">
                            <h5 class="text-lg font-bold ${colors.text}">${alert.title}</h5>
                            <span class="px-3 py-1 ${colors.badge} text-white text-xs font-bold rounded-full uppercase flex-shrink-0">
                                ${alert.severity}
                            </span>
                        </div>
                        <p class="${colors.text} text-sm leading-relaxed mb-3">${alert.message}</p>
                        ${valueDisplay}
                        ${detailsDisplay}
                    </div>
                </div>
            `;

                return card;
            }

            // Load alerts on page load
            document.addEventListener('DOMContentLoaded', () => {
                loadWeatherAlerts();

                // Auto-refresh alerts every 5 minutes
                alertsRefreshInterval = setInterval(loadWeatherAlerts, 300000);
            });

            // Refresh button
            document.getElementById('refreshAlerts')?.addEventListener('click', loadWeatherAlerts);

            // Clean up interval on page unload
            window.addEventListener('beforeunload', () => {
                if (alertsRefreshInterval) {
                    clearInterval(alertsRefreshInterval);
                }
            });

            // Load weather on page load
            document.addEventListener('DOMContentLoaded', loadWeather);

            // Refresh button
            document.getElementById('refreshWeather')?.addEventListener('click', loadWeather);
        </script>
@endsection