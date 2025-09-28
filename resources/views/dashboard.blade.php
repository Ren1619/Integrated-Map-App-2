@extends('layouts.app')

@section('content')
    <div class="p-8">
        <!-- Main Content Area -->
        <div class="grid grid-cols-1 gap-6 mb-6">
            <!-- Current Location Details -->
            <div class="glass-effect rounded-xl p-6">
                <h3 class="text-xl font-semibold text-white mb-4">Current Location Weather</h3>
                <div class="space-y-3">
                    <div class="flex items-center justify-between p-4 rounded-lg bg-white/5">
                        <div>
                            <h4 class="text-white font-semibold" id="locationName">Loading location...</h4>
                            <p class="text-white/60 text-sm" id="locationStatus">Getting your current position</p>
                        </div>
                        <button onclick="dashboard.refreshCurrentLocation()"
                            class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg font-medium transition-all duration-300 hover:scale-105">
                            🔄 Refresh
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Temperature Card -->
            <div class="glass-effect rounded-xl p-6" id="tempCard">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-white/70 text-sm">Current Temp</p>
                        <p class="text-2xl font-bold text-white" id="currentTemp">--°C</p>
                        <p class="text-white/50 text-xs mt-1" id="feelsLike">Feels like --°C</p>
                    </div>
                    <div class="text-blue-300">
                        <div class="text-3xl" id="weatherEmoji">🌤️</div>
                    </div>
                </div>
            </div>

            <!-- Humidity Card -->
            <div class="glass-effect rounded-xl p-6" id="humidityCard">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-white/70 text-sm">Humidity</p>
                        <p class="text-2xl font-bold text-white" id="currentHumidity">--%</p>
                        <p class="text-white/50 text-xs mt-1" id="humidityStatus">--</p>
                    </div>
                    <div class="text-blue-300">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19.428 15.428a2 2 0 00-1.022-.547l-2.987-.532c-.584-.104-1.204.021-1.632.492l-.7.7c-.604.604-.47 1.584.308 2.308a8 8 0 001.429 1.429c.724.778 1.704.912 2.308.308l.7-.7c.471-.428.596-1.048.492-1.632l-.532-2.987a2 2 0 00-.364-.839z">
                            </path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Wind Speed Card -->
            <div class="glass-effect rounded-xl p-6" id="windCard">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-white/70 text-sm">Wind Speed</p>
                        <p class="text-2xl font-bold text-white" id="currentWind">-- km/h</p>
                        <p class="text-white/50 text-xs mt-1" id="windDirection">Direction: --°</p>
                    </div>
                    <div class="text-blue-300">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" id="windIcon">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Pressure Card -->
            <div class="glass-effect rounded-xl p-6" id="pressureCard">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-white/70 text-sm">Pressure</p>
                        <p class="text-2xl font-bold text-white" id="currentPressure">-- hPa</p>
                        <p class="text-white/50 text-xs mt-1" id="pressureStatus">--</p>
                    </div>
                    <div class="text-blue-300">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z">
                            </path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Enhanced Weather Details -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <!-- Additional Weather Info -->
            <div class="glass-effect rounded-xl p-6">
                <h3 class="text-xl font-semibold text-white mb-4">Additional Weather Info</h3>
                <div class="grid grid-cols-2 gap-4">
                    <div class="bg-white/10 rounded-lg p-4">
                        <p class="text-white/70 text-sm">UV Index</p>
                        <p class="text-xl font-bold text-white" id="uvIndex">--</p>
                        <p class="text-white/50 text-xs" id="uvStatus">--</p>
                    </div>
                    <div class="bg-white/10 rounded-lg p-4">
                        <p class="text-white/70 text-sm">Visibility</p>
                        <p class="text-xl font-bold text-white" id="visibility">-- km</p>
                    </div>
                    <div class="bg-white/10 rounded-lg p-4">
                        <p class="text-white/70 text-sm">Cloud Cover</p>
                        <p class="text-xl font-bold text-white" id="cloudCover">--%</p>
                    </div>
                    <div class="bg-white/10 rounded-lg p-4">
                        <p class="text-white/70 text-sm">Precipitation</p>
                        <p class="text-xl font-bold text-white" id="precipitation">-- mm</p>
                    </div>
                </div>
            </div>

            <!-- 24-Hour Forecast -->
            <div class="glass-effect rounded-xl p-6">
                <h3 class="text-xl font-semibold text-white mb-4">24-Hour Forecast</h3>
                <div class="space-y-3 max-h-80 overflow-y-auto custom-scrollbar" id="hourlyForecast">
                    <div class="text-center py-8 text-white/60">
                        <p class="text-sm">Loading forecast data...</p>
                    </div>
                </div>
            </div>
        </div>


        <!-- Loading Overlay -->
        <div id="loadingOverlay"
            class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center hidden">
            <div class="glass-effect rounded-xl p-8 text-center">
                <div class="animate-spin rounded-full h-12 w-12 border-4 border-blue-500 border-t-transparent mx-auto mb-4">
                </div>
                <p class="text-white text-lg font-semibold">Loading weather data...</p>
                <p class="text-white/70 text-sm">Please wait</p>
            </div>
        </div>

        <!-- Footer Info -->
        <div class="mt-8 text-center">
            <p class="text-white/60 text-sm" id="lastUpdated">
                Last updated: Never
            </p>
        </div>
    </div>

    <style>
        .custom-scrollbar::-webkit-scrollbar {
            width: 4px;
        }

        .custom-scrollbar::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 2px;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.3);
            border-radius: 2px;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: rgba(255, 255, 255, 0.5);
        }

        .loading-shimmer {
            background: linear-gradient(90deg, rgba(255, 255, 255, 0.1) 25%, rgba(255, 255, 255, 0.2) 50%, rgba(255, 255, 255, 0.1) 75%);
            background-size: 200% 100%;
            animation: shimmer 2s infinite;
        }

        @keyframes shimmer {
            0% {
                background-position: 200% 0;
            }

            100% {
                background-position: -200% 0;
            }
        }

        .weather-card-loading {
            animation: pulse 2s infinite;
        }

        @keyframes pulse {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0.7;
            }
        }
    </style>

    <script>
        class DashboardWeatherApp {
            constructor() {
                this.currentLat = null;
                this.currentLng = null;
                this.init();
            }

            init() {
                this.bindEvents();
                this.updateLastUpdated();

                // Automatically get user's current location on page load
                this.getCurrentLocation();
            }

            bindEvents() {
                // Auto-refresh every 10 minutes
                setInterval(() => {
                    if (this.currentLat && this.currentLng) {
                        this.getWeatherData(this.currentLat, this.currentLng, true);
                    }
                }, 600000);
            }

            showLoading(show = true) {
                const overlay = document.getElementById('loadingOverlay');
                if (show) {
                    overlay.classList.remove('hidden');
                } else {
                    overlay.classList.add('hidden');
                }
            }

            showCardLoading() {
                const cards = ['tempCard', 'humidityCard', 'windCard', 'pressureCard'];
                cards.forEach(cardId => {
                    document.getElementById(cardId).classList.add('weather-card-loading');
                });
            }

            hideCardLoading() {
                const cards = ['tempCard', 'humidityCard', 'windCard', 'pressureCard'];
                cards.forEach(cardId => {
                    document.getElementById(cardId).classList.remove('weather-card-loading');
                });
            }

            async getCurrentLocation() {
                if (!navigator.geolocation) {
                    document.getElementById('locationStatus').textContent = 'Geolocation not supported by this browser';
                    return;
                }

                document.getElementById('locationStatus').textContent = 'Getting your location...';

                navigator.geolocation.getCurrentPosition(
                    async (position) => {
                        const lat = position.coords.latitude;
                        const lng = position.coords.longitude;

                        this.currentLat = lat;
                        this.currentLng = lng;

                        await this.handleLocationSelected(lat, lng);
                    },
                    (error) => {
                        console.error('Geolocation error:', error);
                        let errorMessage = '';

                        switch (error.code) {
                            case error.PERMISSION_DENIED:
                                errorMessage = 'Location access denied. Please enable location access and refresh the page.';
                                break;
                            case error.POSITION_UNAVAILABLE:
                                errorMessage = 'Location information unavailable.';
                                break;
                            case error.TIMEOUT:
                                errorMessage = 'Location request timed out. Please refresh the page.';
                                break;
                            default:
                                errorMessage = 'Unable to get your location.';
                                break;
                        }

                        document.getElementById('locationStatus').textContent = errorMessage;
                        document.getElementById('locationName').textContent = 'Location unavailable';
                        document.getElementById('locationDetails').textContent = errorMessage;
                    },
                    {
                        enableHighAccuracy: true,
                        timeout: 10000,
                        maximumAge: 300000 // 5 minutes cache
                    }
                );
            }

            async handleLocationSelected(lat, lng) {
                // Get location name
                const locationDetails = await this.getLocationName(lat, lng);

                // Update location display
                const locationName = this.formatLocationName(locationDetails);
                document.getElementById('locationStatus').textContent = `Current location: ${locationName}`;
                document.getElementById('locationName').textContent = locationName;
                document.getElementById('locationDetails').textContent = locationDetails.full_address || locationName;

                // Get weather data
                await this.getWeatherData(lat, lng);
            }

            async getLocationName(lat, lng) {
                try {
                    const response = await fetch('/weather/location-name', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({ lat, lng })
                    });

                    if (response.ok) {
                        const result = await response.json();
                        if (result.success) {
                            return {
                                name: result.data.location_name,
                                full_address: result.data.full_address
                            };
                        }
                    }
                } catch (error) {
                    console.error('Error getting location name:', error);
                }

                return {
                    name: `${lat.toFixed(4)}°, ${lng.toFixed(4)}°`,
                    full_address: `Coordinates: ${lat.toFixed(4)}°, ${lng.toFixed(4)}°`
                };
            }

            formatLocationName(locationDetails) {
                if (!locationDetails) return 'Unknown Location';
                return locationDetails.name || 'Unknown Location';
            }

            async getWeatherData(lat, lng, isAutoRefresh = false) {
                if (!isAutoRefresh) {
                    this.showCardLoading();
                }

                try {
                    const response = await fetch('/weather/data', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({ lat, lng })
                    });

                    const result = await response.json();
                    if (result.success) {
                        this.displayWeatherData(result.data.weather);
                        this.updateLastUpdated();
                    } else {
                        this.showWeatherError();
                    }
                } catch (error) {
                    console.error('Error fetching weather data:', error);
                    this.showWeatherError();
                } finally {
                    if (!isAutoRefresh) {
                        this.hideCardLoading();
                    }
                }
            }

            displayWeatherData(weatherData) {
                if (!weatherData || !weatherData.current) {
                    this.showWeatherError();
                    return;
                }

                const current = weatherData.current;

                // Update main cards
                this.updateTemperatureCard(current);
                this.updateHumidityCard(current);
                this.updateWindCard(current);
                this.updatePressureCard(current);

                // Update additional info
                this.updateAdditionalInfo(current);

                // Update hourly forecast
                if (weatherData.hourly) {
                    this.updateHourlyForecast(weatherData.hourly);
                }
            }

            updateTemperatureCard(current) {
                const temp = Math.round(current.temperature_2m);
                const feelsLike = Math.round(current.apparent_temperature);
                const weatherEmoji = this.getWeatherEmoji(current.weather_code);

                document.getElementById('currentTemp').textContent = `${temp}°C`;
                document.getElementById('feelsLike').textContent = `Feels like ${feelsLike}°C`;
                document.getElementById('weatherEmoji').textContent = weatherEmoji;
            }

            updateHumidityCard(current) {
                const humidity = Math.round(current.relative_humidity_2m);
                let status = 'Normal';

                if (humidity < 30) status = 'Dry';
                else if (humidity > 70) status = 'Humid';

                document.getElementById('currentHumidity').textContent = `${humidity}%`;
                document.getElementById('humidityStatus').textContent = status;
            }

            updateWindCard(current) {
                const windSpeed = Math.round(current.wind_speed_10m);
                const windDir = Math.round(current.wind_direction_10m);

                document.getElementById('currentWind').textContent = `${windSpeed} km/h`;
                document.getElementById('windDirection').textContent = `Direction: ${windDir}°`;

                // Rotate wind icon based on direction
                const windIcon = document.getElementById('windIcon');
                windIcon.style.transform = `rotate(${windDir}deg)`;
            }

            updatePressureCard(current) {
                const pressure = Math.round(current.surface_pressure);
                let status = 'Normal';

                if (pressure < 1000) status = 'Low';
                else if (pressure > 1020) status = 'High';

                document.getElementById('currentPressure').textContent = `${pressure} hPa`;
                document.getElementById('pressureStatus').textContent = status;
            }

            updateAdditionalInfo(current) {
                // UV Index
                const uvIndex = Math.round(current.uv_index || 0);
                let uvStatus = 'Low';
                if (uvIndex >= 3 && uvIndex < 6) uvStatus = 'Moderate';
                else if (uvIndex >= 6 && uvIndex < 8) uvStatus = 'High';
                else if (uvIndex >= 8) uvStatus = 'Very High';

                document.getElementById('uvIndex').textContent = uvIndex;
                document.getElementById('uvStatus').textContent = uvStatus;

                // Visibility
                const visibility = current.visibility ? (current.visibility / 1000).toFixed(1) : '--';
                document.getElementById('visibility').textContent = `${visibility} km`;

                // Cloud Cover
                document.getElementById('cloudCover').textContent = `${Math.round(current.cloud_cover || 0)}%`;

                // Precipitation
                document.getElementById('precipitation').textContent = `${(current.precipitation || 0).toFixed(1)} mm`;
            }

            updateHourlyForecast(hourly) {
                const container = document.getElementById('hourlyForecast');
                if (!hourly.time) {
                    container.innerHTML = '<div class="text-center py-4 text-white/60">Hourly forecast not available</div>';
                    return;
                }

                // Show next 24 hours
                const next24Hours = hourly.time.slice(0, 24).map((time, index) => ({
                    time: new Date(time),
                    temp: Math.round(hourly.temperature_2m[index]),
                    weatherCode: hourly.weather_code[index],
                    precipitation: hourly.precipitation_probability[index] || 0,
                    windSpeed: Math.round(hourly.wind_speed_10m[index])
                }));

                container.innerHTML = next24Hours.map(hour => `
                <div class="flex items-center justify-between p-3 rounded-lg bg-white/5 hover:bg-white/10 transition-colors">
                    <div class="flex items-center gap-3">
                        <div class="text-lg">${this.getWeatherEmoji(hour.weatherCode)}</div>
                        <div>
                            <div class="text-white font-medium text-sm">${hour.time.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' })}</div>
                            <div class="text-white/60 text-xs">${Math.round(hour.precipitation)}% rain</div>
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="text-white font-bold">${hour.temp}°C</div>
                        <div class="text-white/60 text-xs">${hour.windSpeed} km/h</div>
                    </div>
                </div>
            `).join('');
            }

            async refreshCurrentLocation() {
                if (!this.currentLat || !this.currentLng) {
                    // If no current location, try to get it again
                    await this.getCurrentLocation();
                    return;
                }

                await this.getWeatherData(this.currentLat, this.currentLng);
            }

            showWeatherError() {
                const errorMessage = 'Unable to fetch weather data';
                document.getElementById('currentTemp').textContent = '--°C';
                document.getElementById('feelsLike').textContent = 'Feels like --°C';
                document.getElementById('currentHumidity').textContent = '--%';
                document.getElementById('humidityStatus').textContent = '--';
                document.getElementById('currentWind').textContent = '-- km/h';
                document.getElementById('windDirection').textContent = 'Direction: --°';
                document.getElementById('currentPressure').textContent = '-- hPa';
                document.getElementById('pressureStatus').textContent = '--';
                document.getElementById('weatherEmoji').textContent = '🌤️';

                // Additional info
                document.getElementById('uvIndex').textContent = '--';
                document.getElementById('uvStatus').textContent = '--';
                document.getElementById('visibility').textContent = '-- km';
                document.getElementById('cloudCover').textContent = '--%';
                document.getElementById('precipitation').textContent = '-- mm';

                // Hourly forecast
                document.getElementById('hourlyForecast').innerHTML =
                    '<div class="text-center py-4 text-white/60">Unable to load weather data</div>';
            }

            getWeatherEmoji(weatherCode) {
                const weatherEmojis = {
                    0: '☀️', 1: '🌤️', 2: '⛅', 3: '☁️', 45: '🌫️', 48: '🌫️',
                    51: '🌦️', 53: '🌦️', 55: '🌧️', 61: '🌦️', 63: '🌧️', 65: '🌧️',
                    71: '🌨️', 73: '🌨️', 75: '🌨️', 80: '🌦️', 81: '🌧️', 82: '⛈️',
                    95: '⛈️', 96: '⛈️', 99: '⛈️'
                };
                return weatherEmojis[weatherCode] || '🌤️';
            }

            updateLastUpdated() {
                const now = new Date();
                const timeString = now.toLocaleTimeString('en-US', {
                    hour: '2-digit',
                    minute: '2-digit'
                });
                document.getElementById('lastUpdated').textContent = `Last updated: ${timeString}`;
            }
        }

        // Initialize the dashboard
        let dashboard;
        document.addEventListener('DOMContentLoaded', () => {
            dashboard = new DashboardWeatherApp();
        });
    </script>

@endsection