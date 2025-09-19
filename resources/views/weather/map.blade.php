@extends('layouts.app')

@section('content')
    <!-- Header -->
    <header class="glass-effect p-4 shadow-lg">
        <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-gray-800 text-3xl font-bold mb-2">🌤️ SafeCast</h1>
                </div>
                <p class="text-gray-600 text-sm flex items-center gap-2">
                    Click anywhere on the map or search locations to get weather data
                </p>
            </div>
        </header>

        <!-- Main Container -->
        <div class="flex h-[calc(100vh-120px)] gap-4 p-4">
            <!-- Map Container -->
            <div class="flex-1 relative rounded-2xl shadow-2xl border-4 border-white/30 overflow-hidden">
                <!-- Search Controls Overlay -->
                <div class="overlay absolute top-4 left-4 right-4 flex gap-2 items-center z-50">
                    <div class="search-container relative flex-1 max-w-md">
                        <input type="text"
                            class="w-full px-4 py-3 border-2 border-blue-500/30 rounded-full outline-none text-sm bg-white/95 backdrop-blur-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 transition-all duration-300 shadow-lg"
                            placeholder="Search for a place (e.g., New York, Paris, Tokyo)" id="searchInput" autocomplete="off">

                        <!-- Loading indicator -->
                        <div id="loadingIndicator" class="absolute right-14 top-1/2 transform -translate-y-1/2 hidden">
                            <div class="animate-spin rounded-full h-5 w-5 border-b-2 border-blue-500"></div>
                        </div>

                        <!-- Clear button -->
                        <button id="clearButton"
                            class="absolute right-4 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600 text-xl hidden"
                            title="Clear search">×</button>

                        <!-- Autocomplete Dropdown -->
                        <div id="autocompleteDropdown"
                            class="absolute top-full left-0 right-0 mt-2 bg-white/95 backdrop-blur-md rounded-2xl shadow-2xl border border-blue-100 hidden max-h-80 overflow-y-auto z-50">
                        </div>
                    </div>

                    <button
                        class="bg-blue-500 hover:bg-blue-600 active:bg-blue-700 text-white px-6 py-3 rounded-3xl font-medium transition-all duration-300 hover:scale-105 hover:shadow-lg backdrop-blur-sm shadow-lg"
                        onclick="app.searchLocation()">🔍 Search</button>
                </div>

                <!-- Map Layer Control -->
                <div class="overlay absolute top-20 left-4 z-40">
                    <details class="h-fit w-fit">
                        <summary
                            class="list-none cursor-pointer bg-white/95 backdrop-blur-sm rounded-lg p-3 shadow-lg hover:shadow-xl transition-all duration-300 border-2 border-blue-500/30 hover:border-blue-500">
                            <div class="w-6 h-6 flex items-center justify-center text-blue-500" title="Map Layers">☰</div>
                        </summary>

                        <div class="menu-content absolute top-full left-0 mt-1 w-fit min-w-max rounded-2xl p-4 bg-white/95 backdrop-blur-sm shadow-xl border border-blue-100">
                            <button id="standardBtn" onclick="app.changeMapLayer('standard')"
                                class="bg-blue-500 hover:bg-blue-600 active:bg-blue-700 text-white p-3 rounded-2xl font-medium transition-all duration-300 hover:scale-105 hover:shadow-lg backdrop-blur-sm shadow-lg mr-2">
                                🗺️
                            </button>
                            <button id="cycleBtn" onclick="app.changeMapLayer('cycle')"
                                class="bg-blue-500 hover:bg-blue-600 active:bg-blue-700 text-white p-3 rounded-2xl font-medium transition-all duration-300 hover:scale-105 hover:shadow-lg backdrop-blur-sm shadow-lg mr-2">
                                🚴
                            </button>
                            <button id="transportBtn" onclick="app.changeMapLayer('transport')"
                                class="bg-blue-500 hover:bg-blue-600 active:bg-blue-700 text-white p-3 rounded-2xl font-medium transition-all duration-300 hover:scale-105 hover:shadow-lg backdrop-blur-sm shadow-lg">
                                🚌
                            </button>
                        </div>
                    </details>
                </div>

                <!-- Map Container -->
                <div id="map" class="w-full h-full rounded-2xl"></div>
            </div>

            <!-- Weather Panel -->
            <div class="w-96 panel-glass flex flex-col rounded-2xl">
                <!-- Panel Header -->
                <div class="p-6 border-b border-blue-100">
                    <h2 class="text-xl font-bold text-gray-800 flex items-center gap-2">
                        🌦️ Weather Information
                    </h2>
                    <p class="text-sm text-gray-600 mt-1">Real-time weather data</p>
                </div>

                <!-- Panel Content -->
                <div class="flex-1 overflow-y-auto custom-scrollbar">
                    <!-- Current Location Section -->
                    <div class="p-6 border-b border-blue-100">
                        <h3 class="text-lg font-semibold text-gray-700 mb-4 flex items-center gap-2">
                            📍 Selected Location
                        </h3>

                        <div id="currentLocationWeather" class="hidden">
                            <!-- Current weather will be populated here -->
                        </div>

                        <div id="currentLocationPlaceholder" class="text-center py-8 text-gray-500">
                            <div class="text-4xl mb-3">🎯</div>
                            <p class="text-sm">Click on the map or search for a location to view weather data</p>
                        </div>
                    </div>

                    <!-- Nearby Places Section -->
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-700 mb-4 flex items-center gap-2">
                            🏘️ Nearby Places
                        </h3>

                        <div id="nearbyPlacesWeather" class="space-y-3">
                            <!-- Nearby places weather will be populated here -->
                        </div>

                        <div id="nearbyPlacesPlaceholder" class="text-center py-6 text-gray-500">
                            <div class="text-3xl mb-3">🌐</div>
                            <p class="text-sm">Select a location to view nearby weather conditions</p>
                        </div>
                    </div>
                </div>

                <!-- Panel Footer -->
                <div class="p-4 border-t border-blue-100 bg-gray-50/50 rounded-b-2xl">
                    <div class="flex items-center justify-between text-xs text-gray-500">
                        <span>Data from Open-Meteo</span>
                        <span id="lastUpdated">Updated: --:--</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Enhanced CSS -->
        <style>
            .panel-glass {
                background: rgba(255, 255, 255, 0.98);
                backdrop-filter: blur(15px);
                -webkit-backdrop-filter: blur(15px);
                border: 1px solid rgba(255, 255, 255, 0.3);
                box-shadow: -5px 0 25px rgba(0, 0, 0, 0.1);
            }

            .weather-card {
                background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
                border: 1px solid rgba(59, 130, 246, 0.2);
                transition: all 0.3s ease;
            }

            .weather-card:hover {
                transform: translateY(-2px);
                box-shadow: 0 8px 25px rgba(59, 130, 246, 0.15);
            }

            .current-location-card {
                background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
                color: white;
            }

            .loading-shimmer {
                background: linear-gradient(90deg, #f1f5f9 25%, #e2e8f0 50%, #f1f5f9 75%);
                background-size: 200% 100%;
                animation: shimmer 2s infinite;
            }

            @keyframes shimmer {
                0% { background-position: 200% 0; }
                100% { background-position: -200% 0; }
            }

            .custom-scrollbar::-webkit-scrollbar {
                width: 6px;
            }

            .custom-scrollbar::-webkit-scrollbar-track {
                background: rgba(0, 0, 0, 0.1);
                border-radius: 10px;
            }

            .custom-scrollbar::-webkit-scrollbar-thumb {
                background: rgba(59, 130, 246, 0.5);
                border-radius: 10px;
            }

            .menu-content {
                transform: translateY(-10px);
                opacity: 0;
                visibility: hidden;
                transition: all 0.3s ease;
            }

            details[open] .menu-content {
                transform: translateY(0);
                opacity: 1;
                visibility: visible;
            }
        </style>

        <!-- Modified JavaScript -->
        <script>
            class CityWeatherMapApp {
                constructor() {
                    this.map = null;
                    this.currentMarker = null;
                    this.currentLat = null;
                    this.currentLng = null;
                    this.mapLayers = {};

                    // Autocomplete properties
                    this.suggestions = [];
                    this.selectedIndex = -1;
                    this.debounceTimer = null;
                    this.cache = new Map();

                    this.init();
                }

                init() {
                    this.initMap();
                    this.bindEvents();
                    this.initAutocomplete();
                    this.setActiveLayerButton('standard');
                    this.updateLastUpdated();
                }

                initMap() {
                    this.map = L.map('map').setView([7.859438526586211, 125.05149149476975], 10);

                    this.mapLayers = {
                        standard: L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                            attribution: '© OpenStreetMap contributors',
                            maxZoom: 18
                        }),
                        cycle: L.tileLayer('https://{s}.tile-cyclosm.openstreetmap.fr/cyclosm/{z}/{x}/{y}.png', {
                            attribution: '© OpenStreetMap contributors, CyclOSM',
                            maxZoom: 18
                        }),
                        transport: L.tileLayer('https://{s}.tile.openstreetmap.fr/hot/{z}/{x}/{y}.png', {
                            attribution: '© OpenStreetMap contributors, Humanitarian OSM Team',
                            maxZoom: 18
                        })
                    };

                    this.mapLayers.standard.addTo(this.map);

                    this.map.on('click', (e) => {
                        this.handleMapClick(e.latlng);
                    });
                }

                // Keep existing autocomplete methods
                initAutocomplete() {
                    const input = document.getElementById('searchInput');
                    const clearButton = document.getElementById('clearButton');

                    input.addEventListener('input', (e) => this.handleAutocompleteInput(e));
                    input.addEventListener('keydown', (e) => this.handleAutocompleteKeydown(e));
                    clearButton.addEventListener('click', () => this.clearSearch());
                }

                handleAutocompleteInput(e) {
                    const query = e.target.value.trim();
                    const clearButton = document.getElementById('clearButton');

                    if (query) {
                        clearButton.classList.remove('hidden');
                    } else {
                        clearButton.classList.add('hidden');
                    }

                    clearTimeout(this.debounceTimer);

                    if (query.length < 2) {
                        this.hideAutocompleteDropdown();
                        return;
                    }

                    this.debounceTimer = setTimeout(() => {
                        this.searchAutocompleteLocations(query);
                    }, 300);
                }

                handleAutocompleteKeydown(e) {
                    const dropdown = document.getElementById('autocompleteDropdown');
                    if (!dropdown.classList.contains('hidden')) {
                        switch (e.key) {
                            case 'ArrowDown':
                                e.preventDefault();
                                this.navigateAutocompleteDown();
                                break;
                            case 'ArrowUp':
                                e.preventDefault();
                                this.navigateAutocompleteUp();
                                break;
                            case 'Enter':
                                e.preventDefault();
                                this.selectCurrentAutocompleteSuggestion();
                                break;
                            case 'Escape':
                                this.hideAutocompleteDropdown();
                                break;
                        }
                    }
                }

                async searchAutocompleteLocations(query) {
                    if (this.cache.has(query)) {
                        this.displayAutocompleteSuggestions(this.cache.get(query));
                        return;
                    }

                    this.showAutocompleteLoading(true);

                    try {
                        const response = await fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}&limit=5&addressdetails=1`);
                        const data = await response.json();

                        const suggestions = data.map(item => ({
                            id: item.place_id,
                            name: item.name || item.display_name.split(',')[0],
                            address: this.formatAddress(item.address),
                            lat: parseFloat(item.lat),
                            lon: parseFloat(item.lon),
                            type: item.type || 'location',
                            icon: this.getLocationIcon(item)
                        }));

                        this.cache.set(query, suggestions);
                        this.displayAutocompleteSuggestions(suggestions);

                    } catch (error) {
                        console.error('Autocomplete error:', error);
                    } finally {
                        this.showAutocompleteLoading(false);
                    }
                }

                displayAutocompleteSuggestions(suggestions) {
                    this.suggestions = suggestions;
                    const dropdown = document.getElementById('autocompleteDropdown');

                    if (suggestions.length === 0) {
                        dropdown.innerHTML = '<div class="p-4 text-center text-gray-500">No results found</div>';
                        this.showAutocompleteDropdown();
                        return;
                    }

                    let html = '';
                    suggestions.forEach((suggestion, index) => {
                        html += `
                            <div class="autocomplete-item p-3 cursor-pointer hover:bg-blue-50" 
                                 onclick="app.selectAutocompleteSuggestion(${index})">
                                <div class="flex items-center space-x-3">
                                    <div class="text-xl">${suggestion.icon}</div>
                                    <div class="flex-1">
                                        <div class="font-semibold text-gray-800">${suggestion.name}</div>
                                        <div class="text-sm text-gray-600">${suggestion.address}</div>
                                    </div>
                                </div>
                            </div>
                        `;
                    });

                    dropdown.innerHTML = html;
                    this.showAutocompleteDropdown();
                }

                selectAutocompleteSuggestion(index) {
                    const suggestion = this.suggestions[index];
                    const input = document.getElementById('searchInput');

                    input.value = suggestion.name;
                    this.hideAutocompleteDropdown();

                    this.map.setView([suggestion.lat, suggestion.lon], 12);
                    this.handleLocationSelected(suggestion.lat, suggestion.lon);

                    setTimeout(() => {
                        input.value = '';
                        document.getElementById('clearButton').classList.add('hidden');
                    }, 2000);
                }

                formatAddress(address) {
                    if (!address) return 'Unknown location';
                    const parts = [];
                    if (address.city) parts.push(address.city);
                    else if (address.town) parts.push(address.town);
                    if (address.state) parts.push(address.state);
                    if (address.country) parts.push(address.country);
                    return parts.join(', ') || 'Unknown location';
                }

                getLocationIcon(item) {
                    const icons = {
                        city: '🏙️', town: '🏘️', village: '🏡',
                        restaurant: '🍽️', hotel: '🏨', hospital: '🏥'
                    };
                    return icons[item.type] || '📍';
                }

                navigateAutocompleteDown() {
                    this.selectedIndex = Math.min(this.selectedIndex + 1, this.suggestions.length - 1);
                }

                navigateAutocompleteUp() {
                    this.selectedIndex = Math.max(this.selectedIndex - 1, -1);
                }

                selectCurrentAutocompleteSuggestion() {
                    if (this.selectedIndex >= 0) {
                        this.selectAutocompleteSuggestion(this.selectedIndex);
                    }
                }

                clearSearch() {
                    const input = document.getElementById('searchInput');
                    input.value = '';
                    this.hideAutocompleteDropdown();
                    document.getElementById('clearButton').classList.add('hidden');
                }

                showAutocompleteDropdown() {
                    document.getElementById('autocompleteDropdown').classList.remove('hidden');
                }

                hideAutocompleteDropdown() {
                    document.getElementById('autocompleteDropdown').classList.add('hidden');
                }

                showAutocompleteLoading(show) {
                    const loading = document.getElementById('loadingIndicator');
                    const clear = document.getElementById('clearButton');

                    if (show) {
                        loading.classList.remove('hidden');
                        clear.classList.add('hidden');
                    } else {
                        loading.classList.add('hidden');
                        if (document.getElementById('searchInput').value.trim()) {
                            clear.classList.remove('hidden');
                        }
                    }
                }

                bindEvents() {
                    document.getElementById('searchInput').addEventListener('keypress', (e) => {
                        if (e.key === 'Enter') {
                            this.searchLocation();
                        }
                    });

                    if (navigator.geolocation) {
                        navigator.geolocation.getCurrentPosition(
                            (position) => {
                                const lat = position.coords.latitude;
                                const lng = position.coords.longitude;
                                this.map.setView([lat, lng], 12);
                                this.handleLocationSelected(lat, lng);
                            },
                            (error) => console.log('Geolocation error:', error)
                        );
                    }
                }

                changeMapLayer(layerType) {
                    this.map.eachLayer((layer) => {
                        if (layer !== this.currentMarker) {
                            if (layer.options && layer.options.attribution) {
                                this.map.removeLayer(layer);
                            }
                        }
                    });

                    this.mapLayers[layerType].addTo(this.map);
                    this.setActiveLayerButton(layerType);
                }

                setActiveLayerButton(layerType) {
                    document.querySelectorAll('[id$="Btn"]').forEach(btn => {
                        btn.classList.remove('bg-blue-700');
                        btn.classList.add('bg-blue-500');
                    });
                    const targetBtn = document.getElementById(layerType + 'Btn');
                    if (targetBtn) {
                        targetBtn.classList.add('bg-blue-700');
                        targetBtn.classList.remove('bg-blue-500');
                    }
                }

                async searchLocation() {
                    const query = document.getElementById('searchInput').value.trim();
                    if (!query) return;

                    try {
                        const response = await fetch('/search', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            },
                            body: JSON.stringify({ query })
                        });

                        const result = await response.json();

                        if (result.success) {
                            const { lat, lng } = result.data;
                            this.map.setView([lat, lng], 12);
                            this.handleLocationSelected(lat, lng);
                            document.getElementById('searchInput').value = '';
                        } else {
                            alert(result.message || 'Location not found');
                        }
                    } catch (error) {
                        console.error('Search error:', error);
                        alert('Error searching for location');
                    }
                }

                handleMapClick(latlng) {
                    const { lat, lng } = latlng;
                    this.handleLocationSelected(lat, lng);
                }

                async handleLocationSelected(lat, lng) {
                    this.currentLat = lat;
                    this.currentLng = lng;

                    if (this.currentMarker) {
                        this.map.removeLayer(this.currentMarker);
                    }

                    this.currentMarker = L.circleMarker([lat, lng], {
                        radius: 10,
                        fillColor: '#4a90e2',
                        color: 'white',
                        weight: 3,
                        fillOpacity: 0.8
                    }).addTo(this.map);

                    // Show loading states
                    this.showLoadingStates();

                    // Get weather data for current location
                    await this.getCurrentLocationWeather(lat, lng);

                    // Get nearby places from your Laravel backend
                    await this.getNearbyPlacesWeather(lat, lng);

                    this.updateLastUpdated();
                }

                showLoadingStates() {
                    // Current location loading
                    document.getElementById('currentLocationPlaceholder').classList.add('hidden');
                    document.getElementById('currentLocationWeather').innerHTML = this.getLoadingCard('current');
                    document.getElementById('currentLocationWeather').classList.remove('hidden');

                    // Nearby places loading
                    document.getElementById('nearbyPlacesPlaceholder').classList.add('hidden');
                    document.getElementById('nearbyPlacesWeather').innerHTML = Array(4).fill(0).map(() => this.getLoadingCard('nearby')).join('');
                }

                getLoadingCard(type) {
                    const isCurrentLocation = type === 'current';
                    const cardClass = isCurrentLocation ? 'current-location-card' : 'weather-card';

                    return `
                        <div class="${cardClass} rounded-xl p-4 mb-4 loading-shimmer">
                            <div class="animate-pulse">
                                <div class="flex items-center justify-between mb-3">
                                    <div class="h-6 bg-gray-300 rounded w-24"></div>
                                    <div class="h-8 bg-gray-300 rounded-full w-8"></div>
                                </div>
                                <div class="h-8 bg-gray-300 rounded w-16 mb-2"></div>
                                <div class="grid grid-cols-2 gap-2">
                                    <div class="h-4 bg-gray-300 rounded"></div>
                                    <div class="h-4 bg-gray-300 rounded"></div>
                                    <div class="h-4 bg-gray-300 rounded"></div>
                                    <div class="h-4 bg-gray-300 rounded"></div>
                                </div>
                            </div>
                        </div>
                    `;
                }

                async getCurrentLocationWeather(lat, lng) {
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
                            this.displayCurrentLocationWeather(result.data);
                        } else {
                            this.showCurrentLocationError();
                        }
                    } catch (error) {
                        console.error('Error fetching current location weather:', error);
                        this.showCurrentLocationError();
                    }
                }

                async getNearbyPlacesWeather(lat, lng) {
                    try {
                        const response = await fetch('/weather/cities/all', {
                            method: 'GET',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            }
                        });

                        const result = await response.json();
                        if (result.success && result.data) {
                            // Filter and sort nearby places within reasonable distance
                            const nearbyPlaces = result.data
                                .map(place => ({
                                    ...place,
                                    distance: this.calculateDistance(lat, lng, place.lat, place.lng)
                                }))
                                .filter(place => place.distance < 200) // Within 200km
                                .sort((a, b) => a.distance - b.distance)
                                .slice(0, 8); // Max 8 places

                            this.displayNearbyPlacesWeather(nearbyPlaces);
                        } else {
                            this.showNearbyPlacesError();
                        }
                    } catch (error) {
                        console.error('Error fetching nearby places weather:', error);
                        this.showNearbyPlacesError();
                    }
                }

                displayCurrentLocationWeather(data) {
                    const container = document.getElementById('currentLocationWeather');

                    if (!data.weather || !data.weather.current) {
                        this.showCurrentLocationError();
                        return;
                    }

                    const current = data.weather.current;
                    const location = data.location || 'Unknown Location';

                    const weatherEmoji = this.getWeatherEmoji(current.weather_code);
                    const temp = Math.round(current.temperature_2m);
                    const feelsLike = Math.round(current.apparent_temperature);

                    container.innerHTML = `
                        <div class="current-location-card rounded-xl p-6">
                            <div class="flex items-center justify-between mb-4">
                                <div>
                                    <h4 class="text-lg font-semibold text-white">${location}</h4>
                                    <p class="text-blue-100 text-sm">Current Location</p>
                                </div>
                                <div class="text-4xl">${weatherEmoji}</div>
                            </div>

                            <div class="mb-4">
                                <div class="text-3xl font-bold text-white mb-1">${temp}°C</div>
                                <div class="text-blue-100 text-sm">Feels like ${feelsLike}°C</div>
                            </div>

                            <div class="grid grid-cols-2 gap-3 text-sm">
                                <div class="bg-white/20 rounded-lg p-2">
                                    <div class="text-blue-100 font-medium">Humidity</div>
                                    <div class="text-white">${current.relative_humidity_2m}%</div>
                                </div>
                                <div class="bg-white/20 rounded-lg p-2">
                                    <div class="text-blue-100 font-medium">Wind</div>
                                    <div class="text-white">${Math.round(current.wind_speed_10m)} km/h</div>
                                </div>
                                <div class="bg-white/20 rounded-lg p-2">
                                    <div class="text-blue-100 font-medium">Pressure</div>
                                    <div class="text-white">${Math.round(current.surface_pressure)} hPa</div>
                                </div>
                                <div class="bg-white/20 rounded-lg p-2">
                                    <div class="text-blue-100 font-medium">Wind Dir</div>
                                    <div class="text-white">${current.wind_direction_10m}°</div>
                                </div>
                            </div>
                        </div>
                    `;
                }

                displayNearbyPlacesWeather(places) {
                    const container = document.getElementById('nearbyPlacesWeather');

                    if (places.length === 0) {
                        container.innerHTML = `
                            <div class="text-center py-6 text-gray-500">
                                <div class="text-3xl mb-3">🌐</div>
                                <p class="text-sm">No nearby places found</p>
                            </div>
                        `;
                        return;
                    }

                    const weatherCards = places.map(place => {
                        const weather = place.weather;
                        if (!weather) return '';

                        const temp = Math.round(weather.temperature_2m || 0);
                        const weatherEmoji = this.getWeatherEmoji(weather.weather_code);
                        const distance = Math.round(place.distance);

                        return `
                            <div class="weather-card rounded-lg p-4 cursor-pointer hover:scale-105 transition-transform" 
                                 onclick="app.focusOnPlace(${place.lat}, ${place.lng})">
                                <div class="flex items-center justify-between mb-2">
                                    <div class="flex items-center gap-2">
                                        <h4 class="font-semibold text-gray-800 text-sm truncate">${place.city}</h4>
                                        <span class="text-xs text-gray-500">${distance}km</span>
                                    </div>
                                    <div class="text-2xl">${weatherEmoji}</div>
                                </div>

                                <div class="flex items-center justify-between">
                                    <div class="text-2xl font-bold text-gray-900">${temp}°C</div>
                                    <div class="text-right text-xs text-gray-600">
                                        <div>💨 ${Math.round(weather.wind_speed_10m || 0)} km/h</div>
                                        <div>💧 ${weather.relative_humidity_2m || 0}%</div>
                                    </div>
                                </div>
                            </div>
                        `;
                    }).filter(card => card !== '').join('');

                    container.innerHTML = weatherCards;
                }

                showCurrentLocationError() {
                    const container = document.getElementById('currentLocationWeather');
                    container.innerHTML = `
                        <div class="bg-red-100 border border-red-300 rounded-xl p-4 text-center">
                            <div class="text-red-500 text-2xl mb-2">⚠️</div>
                            <p class="text-red-700 text-sm">Unable to fetch weather data</p>
                            <button onclick="app.retryCurrentWeather()" class="mt-2 text-red-600 underline text-xs">
                                Retry
                            </button>
                        </div>
                    `;
                }

                showNearbyPlacesError() {
                    const container = document.getElementById('nearbyPlacesWeather');
                    container.innerHTML = `
                        <div class="bg-red-100 border border-red-300 rounded-lg p-4 text-center">
                            <div class="text-red-500 text-xl mb-2">⚠️</div>
                            <p class="text-red-700 text-sm">Unable to fetch nearby places</p>
                        </div>
                    `;
                }

                focusOnPlace(lat, lng) {
                    this.map.setView([lat, lng], 14);
                    this.handleLocationSelected(lat, lng);
                }

                retryCurrentWeather() {
                    if (this.currentLat && this.currentLng) {
                        this.getCurrentLocationWeather(this.currentLat, this.currentLng);
                    }
                }

                calculateDistance(lat1, lng1, lat2, lng2) {
                    const R = 6371; // Earth's radius in kilometers
                    const dLat = this.deg2rad(lat2 - lat1);
                    const dLng = this.deg2rad(lng2 - lng1);

                    const a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
                        Math.cos(this.deg2rad(lat1)) * Math.cos(this.deg2rad(lat2)) *
                        Math.sin(dLng / 2) * Math.sin(dLng / 2);

                    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));

                    return R * c;
                }

                deg2rad(deg) {
                    return deg * (Math.PI / 180);
                }

                getWeatherEmoji(weatherCode) {
                    const weatherEmojis = {
                        0: '☀️',    // Clear sky
                        1: '🌤️',   // Mainly clear
                        2: '⛅',    // Partly cloudy
                        3: '☁️',    // Overcast
                        45: '🌫️',  // Foggy
                        48: '🌫️',  // Depositing rime fog
                        51: '🌦️',  // Light drizzle
                        53: '🌦️',  // Moderate drizzle
                        55: '🌧️',  // Dense drizzle
                        61: '🌦️',  // Slight rain
                        63: '🌧️',  // Moderate rain
                        65: '🌧️',  // Heavy rain
                        71: '🌨️',  // Slight snow fall
                        73: '🌨️',  // Moderate snow fall
                        75: '🌨️',  // Heavy snow fall
                        80: '🌦️',  // Slight rain showers
                        81: '🌧️',  // Moderate rain showers
                        82: '⛈️',   // Violent rain showers
                        95: '⛈️',   // Thunderstorm
                        96: '⛈️',   // Thunderstorm with slight hail
                        99: '⛈️'    // Thunderstorm with heavy hail
                    };
                    return weatherEmojis[weatherCode] || '🌤️';
                }

                updateLastUpdated() {
                    const now = new Date();
                    const timeString = now.toLocaleTimeString('en-US', { 
                        hour: '2-digit', 
                        minute: '2-digit' 
                    });
                    document.getElementById('lastUpdated').textContent = `Updated: ${timeString}`;
                }
            }

            let app;
            document.addEventListener('DOMContentLoaded', () => {
                app = new CityWeatherMapApp();
            });
        </script>
@endsection