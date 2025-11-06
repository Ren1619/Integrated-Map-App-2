@extends('layouts.app')

@section('content')
    <!-- Meta tag for authentication check -->
    <meta name="user-authenticated" content="{{ Auth::check() ? 'true' : 'false' }}">

    <!-- Main Container - Full Height with Proper Padding -->
    <div class="h-[calc(100vh-4rem)] flex flex-col gap-3 p-3 sm:gap-4 sm:p-4 overflow-hidden">

        <!-- Top Section: Map + 7-Day Forecast - 60% height on desktop, full on mobile -->
        <div class="flex flex-col lg:flex-row gap-3 sm:gap-4 h-auto lg:h-[60%] min-h-[300px]">

            <!-- Map Container - Full width on mobile, 65% on desktop -->
            <div
                class="w-full lg:w-[65%] relative rounded-xl sm:rounded-2xl shadow-2xl border-2 sm:border-4 border-white/30 overflow-hidden h-[400px] sm:h-[500px] lg:h-full">

                <!-- Search Bar Overlay -->
                <div
                    class="overlay absolute top-2 sm:top-4 left-2 sm:left-4 right-2 sm:right-4 flex flex-col sm:flex-row gap-2 items-stretch sm:items-center z-[1000]">
                    <div class="search-container relative flex-1 max-w-full sm:max-w-md">
                        <input type="text"
                            class="w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-blue-500/30 rounded-full outline-none text-xs sm:text-sm bg-white/95 backdrop-blur-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 transition-all duration-300 shadow-lg"
                            placeholder="Search worldwide..." id="searchInput" autocomplete="off">

                        <div id="loadingIndicator"
                            class="absolute right-12 sm:right-14 top-1/2 transform -translate-y-1/2 hidden">
                            <div class="animate-spin rounded-full h-4 w-4 sm:h-5 sm:w-5 border-b-2 border-blue-500"></div>
                        </div>

                        <button id="clearButton"
                            class="absolute right-3 sm:right-4 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600 text-lg sm:text-xl hidden"
                            title="Clear search">×</button>

                        <div id="autocompleteDropdown"
                            class="absolute top-full left-0 right-0 mt-2 bg-white/95 backdrop-blur-md rounded-xl sm:rounded-2xl shadow-2xl border border-blue-100 hidden max-h-60 sm:max-h-80 overflow-y-auto z-[1001] text-xs sm:text-sm">
                        </div>
                    </div>

                    <button
                        class="bg-blue-500 hover:bg-blue-600 active:bg-blue-700 text-white px-4 sm:px-6 py-2 sm:py-3 rounded-full text-xs sm:text-sm font-medium transition-all duration-300 hover:scale-105 hover:shadow-lg backdrop-blur-sm shadow-lg whitespace-nowrap"
                        onclick="app.searchLocation()">🔍 Search</button>
                </div>

                <!-- Leaflet Map -->
                <div id="map" class="w-full h-full"></div>
            </div>

            <!-- 7-Day Forecast Panel - Full width on mobile, 35% on desktop -->
            <div
                class="w-full lg:w-[35%] panel-glass flex flex-col rounded-xl sm:rounded-2xl overflow-hidden min-h-[300px] lg:h-full">
                <!-- Panel Header -->
                <div class="p-3 sm:p-4 lg:p-6 border-b border-blue-100 flex-shrink-0">
                    <h2 class="text-base sm:text-lg lg:text-xl font-bold text-gray-800 flex items-center gap-2">
                        📅 7-Day Forecast
                    </h2>
                    <p class="text-xs sm:text-sm text-gray-600 mt-1" id="forecastLocation">Select a location to view
                        forecast</p>
                </div>

                <!-- Panel Content - Scrollable -->
                <div class="flex-1 overflow-y-auto custom-scrollbar p-3 sm:p-4">
                    <div id="extendedForecast" class="space-y-2 sm:space-y-3">
                        <div class="text-center py-6 sm:py-8 text-gray-500">
                            <div class="text-3xl sm:text-4xl mb-3">🎯</div>
                            <p class="text-xs sm:text-sm px-4">Select any location worldwide to view 7-day weather forecast
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Panel Footer -->
                <div class="p-3 sm:p-4 border-t border-blue-100 bg-gray-50/50 rounded-b-xl sm:rounded-b-2xl flex-shrink-0">
                    <div class="flex items-center justify-between text-xs text-gray-500">
                        <span>Extended Forecast</span>
                        <span id="forecastUpdated">Updated: --:--</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bottom Section - Comprehensive Weather Data - 40% height on desktop, auto on mobile -->
        <div class="panel-glass flex flex-col rounded-xl sm:rounded-2xl overflow-hidden h-auto lg:h-[40%] min-h-[250px]">
            <!-- Panel Header -->
            <div class="p-3 sm:p-4 border-b border-blue-100 flex-shrink-0">
                <div class="flex flex-col sm:flex-row justify-between items-start gap-3 sm:gap-4">
                    <!-- Left side: Title and Location -->
                    <div class="flex-1 min-w-0 w-full sm:w-auto">
                        <div class="flex items-center gap-2 mb-1 flex-wrap">
                            <h2 class="text-base sm:text-lg lg:text-xl font-bold text-gray-800 flex items-center gap-2"
                                id="weatherPanelTitle">
                                🌦️ Comprehensive Weather Data
                            </h2>

                            <!-- Save Location Button (Inline with title) -->
                            @auth
                                <button id="saveLocationBtn" onclick="app.toggleSaveLocation()"
                                    class="hidden px-2 sm:px-3 py-1 sm:py-1.5 bg-blue-500 hover:bg-blue-600 text-white rounded-lg shadow-sm hover:shadow-md transition-all duration-200 flex items-center gap-1 sm:gap-1.5 text-xs font-medium"
                                    title="Save this location">
                                    <span id="saveLocationIcon">📍</span>
                                    <span id="saveLocationText" class="hidden sm:inline">Save</span>
                                </button>
                            @endauth
                        </div>
                        <p class="text-xs sm:text-sm text-gray-600" id="locationDetails">Click on map or search for detailed
                            weather information</p>
                    </div>

                    <!-- Right side: Last Updated -->
                    <div class="text-xs text-gray-500 whitespace-nowrap">
                        <span id="lastUpdated">Updated: --:--</span>
                    </div>
                </div>
            </div>

            <!-- Scrollable Content --> 
            <div class="flex-1 overflow-y-auto overflow-x-auto custom-scrollbar">
                <div class="flex flex-col lg:flex-row gap-3 sm:gap-4 p-3 sm:p-4">
                    <!-- Current Weather Section -->
                    <div class="flex-shrink-0 w-full lg:w-72">
                        <h3
                            class="text-sm sm:text-base lg:text-lg font-semibold text-gray-700 mb-2 sm:mb-3 flex items-center gap-2">
                            📍 Current Conditions
                        </h3>
                        <div id="currentWeatherData" class="hidden">
                            <!-- Current weather will be populated here -->
                        </div>
                        <div id="weatherPlaceholder" class="text-center py-4 sm:py-6 text-gray-500">
                            <div class="text-2xl sm:text-3xl mb-2">🎯</div>
                            <p class="text-xs">Select location for weather data</p>
                        </div>
                    </div>

                    <!-- Multi-Level Temperature Section -->
                    <div class="flex-shrink-0 w-full lg:w-64">
                        <h3
                            class="text-sm sm:text-base lg:text-lg font-semibold text-gray-700 mb-2 sm:mb-3 flex items-center gap-2">
                            🌡️ Temperature
                        </h3>
                        <div id="temperatureByAltitude" class="grid grid-cols-2 gap-2">
                            <!-- Temperature data will be populated here -->
                        </div>
                    </div>

                    <!-- Multi-Level Wind Section -->
                    <div class="flex-shrink-0 w-full lg:w-64">
                        <h3
                            class="text-sm sm:text-base lg:text-lg font-semibold text-gray-700 mb-2 sm:mb-3 flex items-center gap-2">
                            💨 Wind
                        </h3>
                        <div id="windByAltitude" class="grid grid-cols-2 gap-2">
                            <!-- Wind data will be populated here -->
                        </div>
                    </div>

                    <!-- Soil Conditions Section -->
                    <div class="flex-shrink-0 w-full lg:w-64">
                        <h3
                            class="text-sm sm:text-base lg:text-lg font-semibold text-gray-700 mb-2 sm:mb-3 flex items-center gap-2">
                            🌱 Soil Conditions
                        </h3>
                        <div id="soilConditions" class="grid grid-cols-2 gap-2">
                            <!-- Soil data will be populated here -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Keep your existing JavaScript -->
    <script>
        class EnhancedWeatherMapApp {
            constructor() {
                this.map = null;
                this.currentMarker = null;
                this.currentLat = null;
                this.currentLng = null;
                this.currentLocationDetails = null;
                this.isSaved = false;
                this.savedLocationId = null;
                this.mapLayers = {};
                this.suggestions = [];
                this.selectedIndex = -1;
                this.debounceTimer = null;
                this.cache = new Map();
                this.isAuthenticated = document.querySelector('meta[name="user-authenticated"]')?.content === 'true';

                if (document.readyState === 'loading') {
                    document.addEventListener('DOMContentLoaded', () => this.init());
                } else {
                    this.init();
                }
            }

            init() {
                console.log('Initializing Weather Map App...');

                if (typeof L === 'undefined') {
                    console.error('Leaflet library not loaded!');
                    this.showMapError('Map library failed to load. Please refresh the page.');
                    return;
                }

                const mapContainer = document.getElementById('map');
                if (!mapContainer) {
                    console.error('Map container not found!');
                    return;
                }

                console.log('Map container found, initializing map...');

                try {
                    this.initMap();
                    this.bindEvents();
                    this.initAutocomplete();
                    this.updateLastUpdated();
                    console.log('Map initialized successfully!');
                } catch (error) {
                    console.error('Error initializing map:', error);
                    this.showMapError('Failed to initialize map: ' + error.message);
                }
            }

            showMapError(message) {
                const mapContainer = document.getElementById('map');
                if (mapContainer) {
                    mapContainer.innerHTML = `
                        <div style="display: flex; align-items: center; justify-content: center; height: 100%; background: #1e293b; color: white; flex-direction: column; gap: 1rem; padding: 1rem;">
                            <div style="font-size: 2rem;">⚠️</div>
                            <div style="font-size: 1rem; font-weight: bold; text-align: center;">${message}</div>
                            <button onclick="location.reload()" style="padding: 0.5rem 1rem; background: #3b82f6; color: white; border: none; border-radius: 0.5rem; cursor: pointer; font-weight: 500; font-size: 0.875rem;">
                                Reload Page
                            </button>
                        </div>
                    `;
                }
            }

            initMap() {
                const mapContainer = document.getElementById('map');

                if (this.map) {
                    this.map.remove();
                }

                mapContainer.style.height = '100%';
                mapContainer.style.width = '100%';

                console.log('Creating Leaflet map...');

                this.map = L.map('map', {
                    center: [7.1907, 125.4553],
                    zoom: 12,
                    zoomControl: true,
                    attributionControl: true
                });

                console.log('Map object created:', this.map);

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
                console.log('Tile layer added');

                this.map.on('click', (e) => {
                    console.log('Map clicked at:', e.latlng);
                    this.handleMapClick(e.latlng);
                });

                setTimeout(() => {
                    this.map.invalidateSize();
                    console.log('Map size invalidated');
                }, 100);

                this.initGeolocation();
            }

            initGeolocation() {
                if (navigator.geolocation) {
                    console.log('Requesting geolocation...');
                    navigator.geolocation.getCurrentPosition(
                        async (position) => {
                            const lat = position.coords.latitude;
                            const lng = position.coords.longitude;
                            console.log('Geolocation success:', lat, lng);
                            this.map.setView([lat, lng], 12);
                            await this.handleLocationSelected(lat, lng, null);
                        },
                        (error) => {
                            console.log('Geolocation error:', error.message);
                        }
                    );
                } else {
                    console.log('Geolocation not supported');
                }
            }

            async handleMapClick(latlng) {
                console.log('Handling map click:', latlng);
                await this.handleLocationSelected(latlng.lat, latlng.lng, null);
            }

            initAutocomplete() {
                const input = document.getElementById('searchInput');
                const clearButton = document.getElementById('clearButton');

                if (!input || !clearButton) {
                    console.warn('Search input or clear button not found');
                    return;
                }

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
                    const response = await fetch('/weather/autocomplete', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ query })
                    });

                    const result = await response.json();

                    if (response.ok && result.success) {
                        this.cache.set(query, result.data);
                        this.displayAutocompleteSuggestions(result.data);
                    } else {
                        console.error('Autocomplete API error:', result);
                        this.displayAutocompleteError(result.message || 'Search failed');
                    }
                } catch (error) {
                    console.error('Autocomplete network error:', error);
                    this.displayAutocompleteError('Network error occurred');
                } finally {
                    this.showAutocompleteLoading(false);
                }
            }

            displayAutocompleteError(message) {
                const dropdown = document.getElementById('autocompleteDropdown');
                dropdown.innerHTML = `
                    <div class="p-4 text-center text-red-500">
                        <div class="text-2xl mb-2">⚠️</div>
                        <p class="text-sm">${message}</p>
                    </div>
                `;
                this.showAutocompleteDropdown();
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
                    const population = suggestion.population ?
                        `<span class="text-xs text-gray-500">${this.formatPopulation(suggestion.population)}</span>` : '';

                    html += `
                        <div class="autocomplete-item p-3 cursor-pointer hover:bg-blue-50" 
                             onclick="app.selectAutocompleteSuggestion(${index})">
                            <div class="flex items-center justify-between">
                                <div>
                                    <div class="font-semibold text-gray-800">${suggestion.name}</div>
                                    <div class="text-sm text-gray-600">${suggestion.display_name}</div>
                                    ${population}
                                </div>
                                <div class="text-xl">🌍</div>
                            </div>
                        </div>
                    `;
                });

                dropdown.innerHTML = html;
                this.showAutocompleteDropdown();
            }

            formatPopulation(population) {
                if (population > 1000000) {
                    return `${(population / 1000000).toFixed(1)}M people`;
                } else if (population > 1000) {
                    return `${(population / 1000).toFixed(0)}K people`;
                }
                return `${population} people`;
            }

            selectAutocompleteSuggestion(index) {
                const suggestion = this.suggestions[index];
                const input = document.getElementById('searchInput');

                input.value = suggestion.name;
                this.hideAutocompleteDropdown();

                this.map.setView([suggestion.lat, suggestion.lng], 12);

                const locationDetails = {
                    name: suggestion.name,
                    admin1: suggestion.admin1,
                    country: suggestion.country,
                    display_name: suggestion.display_name
                };

                this.handleLocationSelected(suggestion.lat, suggestion.lng, locationDetails);

                setTimeout(() => {
                    input.value = '';
                    document.getElementById('clearButton').classList.add('hidden');
                }, 2000);
            }

            navigateAutocompleteDown() {
                this.selectedIndex = Math.min(this.selectedIndex + 1, this.suggestions.length - 1);
                this.updateAutocompleteSelection();
            }

            navigateAutocompleteUp() {
                this.selectedIndex = Math.max(this.selectedIndex - 1, -1);
                this.updateAutocompleteSelection();
            }

            updateAutocompleteSelection() {
                const items = document.querySelectorAll('.autocomplete-item');
                items.forEach((item, index) => {
                    if (index === this.selectedIndex) {
                        item.classList.add('selected');
                    } else {
                        item.classList.remove('selected');
                    }
                });
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
                this.selectedIndex = -1;
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
                const searchInput = document.getElementById('searchInput');
                if (searchInput) {
                    searchInput.addEventListener('keypress', (e) => {
                        if (e.key === 'Enter') {
                            this.searchLocation();
                        }
                    });
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
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ query })
                    });

                    const result = await response.json();

                    if (response.ok && result.success) {
                        const { lat, lng, location_details } = result.data;
                        this.map.setView([lat, lng], 12);
                        await this.handleLocationSelected(lat, lng, location_details);
                        document.getElementById('searchInput').value = '';
                        this.hideAutocompleteDropdown();
                    } else {
                        console.error('Search error:', result);
                        alert(result.message || 'Location not found');
                    }
                } catch (error) {
                    console.error('Search network error:', error);
                    alert('Network error occurred while searching');
                }
            }

            async handleLocationSelected(lat, lng, locationDetails = null, searchType = 'map_click') {
                console.log('Location selected:', lat, lng, 'Type:', searchType);

                this.currentLat = lat;
                this.currentLng = lng;
                this.currentLocationDetails = locationDetails;

                // Remove existing marker
                if (this.currentMarker) {
                    this.map.removeLayer(this.currentMarker);
                }

                // Add new marker
                this.currentMarker = L.circleMarker([lat, lng], {
                    radius: 12,
                    fillColor: '#3b82f6',
                    color: 'white',
                    weight: 3,
                    fillOpacity: 0.9
                }).addTo(this.map);

                // Update location details first to get the location name
                await this.updateLocationDetailsAndTitle(locationDetails, lat, lng);

                // Record search after we have location details
                if (this.isAuthenticated && this.currentLocationDetails) {
                    await this.recordSearch(
                        this.currentLocationDetails.name || this.currentLocationDetails.display_name || `${lat.toFixed(4)}, ${lng.toFixed(4)}`,
                        lat,
                        lng,
                        this.currentLocationDetails.address_components || null,
                        searchType
                    );
                }

                // Show loading states
                this.showLoadingStates();

                // Fetch weather data
                await this.getEnhancedWeatherData(lat, lng);

                this.updateLastUpdated();
            }

            /**
             * Record search in history (authenticated users only)
             */
            async recordSearch(locationName, lat, lng, addressComponents = null, searchType = 'manual') {
                // Only record if user is authenticated
                if (!this.isAuthenticated) {
                    console.log('User not authenticated, skipping search record');
                    return;
                }

                console.log('Recording search:', { locationName, lat, lng, searchType });

                try {
                    const response = await fetch('/user/search-history', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            location_name: locationName,
                            latitude: parseFloat(lat),
                            longitude: parseFloat(lng),
                            address_components: addressComponents,
                            search_type: searchType
                        })
                    });

                    const result = await response.json();

                    if (result.success) {
                        console.log('Search recorded successfully:', result.data);
                    } else {
                        console.warn('Failed to record search:', result.message);
                    }
                } catch (error) {
                    console.error('Error recording search:', error);
                }
            }

            async updateLocationDetailsAndTitle(locationDetails, lat, lng) {
                const titleElement = document.getElementById('weatherPanelTitle');
                const detailsElement = document.getElementById('locationDetails');
                const forecastLocationElement = document.getElementById('forecastLocation');

                if (locationDetails && locationDetails.name) {
                    const parts = [];
                    if (locationDetails.name) parts.push(locationDetails.name);
                    if (locationDetails.admin1) parts.push(locationDetails.admin1);
                    if (locationDetails.country) parts.push(locationDetails.country);

                    const locationName = parts.join(', ');
                    titleElement.innerHTML = `🌦️ ${locationName}`;
                    detailsElement.textContent = locationName;
                    forecastLocationElement.textContent = locationName;
                    return;
                }

                const coordsText = `${lat.toFixed(6)}°, ${lng.toFixed(6)}°`;
                titleElement.innerHTML = `🌦️ ${coordsText}`;
                detailsElement.textContent = `Coordinates: ${coordsText}`;
                forecastLocationElement.textContent = coordsText;
            }

            showLoadingStates() {
                document.getElementById('weatherPlaceholder')?.classList.add('hidden');
                const currentWeather = document.getElementById('currentWeatherData');
                if (currentWeather) {
                    currentWeather.innerHTML = this.getLoadingCard();
                    currentWeather.classList.remove('hidden');
                }

                document.getElementById('temperatureByAltitude').innerHTML = this.getLoadingGrid();
                document.getElementById('windByAltitude').innerHTML = this.getLoadingGrid();
                document.getElementById('soilConditions').innerHTML = this.getLoadingGrid();
                document.getElementById('extendedForecast').innerHTML = this.getForecastLoading();
            }

            getLoadingCard() {
                return `
                    <div class="current-weather-card rounded-xl p-4 loading-shimmer">
                        <div class="animate-pulse">
                            <div class="flex items-center justify-between mb-3">
                                <div class="h-4 bg-white/30 rounded w-24"></div>
                                <div class="h-8 bg-white/30 rounded-full w-8"></div>
                            </div>
                            <div class="h-8 bg-white/30 rounded w-20 mb-3"></div>
                            <div class="grid grid-cols-2 gap-2">
                                <div class="h-12 bg-white/30 rounded"></div>
                                <div class="h-12 bg-white/30 rounded"></div>
                            </div>
                        </div>
                    </div>
                `;
            }

            getLoadingGrid() {
                return Array(4).fill(0).map(() => `
                    <div class="metric-card-compact animate-pulse">
                        <div class="h-6 bg-gray-300 rounded mb-2"></div>
                        <div class="h-3 bg-gray-300 rounded"></div>
                    </div>
                `).join('');
            }

            getForecastLoading() {
                return Array(7).fill(0).map(() => `
                    <div class="forecast-item animate-pulse">
                        <div class="flex items-center gap-3">
                            <div class="h-6 w-6 bg-gray-300 rounded"></div>
                            <div class="h-3 bg-gray-300 rounded w-16"></div>
                        </div>
                        <div class="h-4 bg-gray-300 rounded w-12"></div>
                    </div>
                `).join('');
            }

            async getEnhancedWeatherData(lat, lng) {
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
                        this.displayEnhancedWeatherData(result.data.weather);
                    } else {
                        this.showWeatherError();
                    }
                } catch (error) {
                    console.error('Error fetching weather data:', error);
                    this.showWeatherError();
                }
            }

            displayEnhancedWeatherData(weatherData) {
                if (!weatherData || !weatherData.current) {
                    this.showWeatherError();
                    return;
                }

                this.displayCurrentWeather(weatherData.current);
                this.displayTemperatureByAltitude(weatherData.current);
                this.displayWindByAltitude(weatherData.current);
                this.displaySoilConditions(weatherData.hourly);
                this.displayExtendedForecast(weatherData.daily);
            }

            displayCurrentWeather(current) {
                const container = document.getElementById('currentWeatherData');
                const weatherEmoji = this.getWeatherEmoji(current.weather_code);
                const temp = Math.round(current.temperature_2m);
                const feelsLike = Math.round(current.apparent_temperature);

                container.innerHTML = `
                    <div class="current-weather-card bg-blue-500 rounded-xl p-4" style="min-height: 168px; height: 168px;">
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center gap-2">
                                <div class="text-4xl font-bold text-white">${temp}°C</div>
                                <div>
                                    <h4 class="text-sm font-semibold text-white">Current</h4>
                                    <p class="text-blue-100 text-xs">${new Date().toLocaleTimeString()}</p>
                                </div>
                            </div>
                            <div class="text-3xl">${weatherEmoji}</div>
                        </div>
                        <div class="mb-2">
                            <div class="text-blue-100 text-xs">Feels like ${feelsLike}°C</div>
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <div class="bg-white/20 rounded-lg p-2 text-center">
                                <div class="text-white font-bold text-sm">${current.relative_humidity_2m}%</div>
                                <div class="text-blue-100 text-xs">Humidity</div>
                            </div>
                            <div class="bg-white/20 rounded-lg p-2 text-center">
                                <div class="text-white font-bold text-sm">${Math.round(current.surface_pressure)}</div>
                                <div class="text-blue-100 text-xs">Pressure</div>
                            </div>
                        </div>
                    </div>
                `;
            }

            displayTemperatureByAltitude(current) {
                const container = document.getElementById('temperatureByAltitude');
                const altitudeLevels = [
                    { level: '2m', temp: current.temperature_2m, color: '#ef4444' },
                    { level: '80m', temp: current.temperature_80m, color: '#f97316' },
                    { level: '120m', temp: current.temperature_120m, color: '#eab308' },
                    { level: '180m', temp: current.temperature_180m, color: '#22c55e' }
                ];

                container.innerHTML = altitudeLevels.map(item => {
                    const temp = item.temp;
                    const displayTemp = (temp !== undefined && temp !== null) ? Math.round(temp) : '--';

                    return `
                        <div class="data-card-uniform" style="background: linear-gradient(135deg, ${item.color} 0%, ${item.color}dd 100%);">
                            <div class="text-xs font-semibold text-white mb-1">${item.level}</div>
                            <div class="text-lg font-bold text-white">${displayTemp}°C</div>
                        </div>
                    `;
                }).join('');
            }

            displayWindByAltitude(current) {
                const container = document.getElementById('windByAltitude');
                const windData = [
                    { level: '10m', speed: current.wind_speed_10m, direction: current.wind_direction_10m, gusts: current.wind_gusts_10m },
                    { level: '80m', speed: current.wind_speed_80m, direction: current.wind_direction_80m },
                    { level: '120m', speed: current.wind_speed_120m, direction: current.wind_direction_120m },
                    { level: '180m', speed: current.wind_speed_180m, direction: current.wind_direction_180m }
                ];

                container.innerHTML = windData.map(item => `
                    <div class="data-card-uniform bg-blue-100">
                        <div class="text-xs font-semibold text-gray-700 mb-1">${item.level}</div>
                        <div class="text-lg font-bold text-blue-600">${Math.round(item.speed || 0)} km/h</div>
                        ${item.direction ? `<div class="text-xs text-gray-600">${item.direction}°</div>` : ''}
                    </div>
                `).join('');
            }

            displaySoilConditions(hourly) {
                const container = document.getElementById('soilConditions');
                if (!hourly) {
                    container.innerHTML = `
                        <div class="data-card-uniform bg-gray-100 col-span-2">
                            <div class="text-2xl mb-1">🌱</div>
                            <p class="text-xs text-gray-500">Soil data not available</p>
                        </div>
                    `;
                    return;
                }

                const soilData = [
                    { depth: '0cm', temp: hourly.soil_temperature_0cm?.[0], moisture: hourly.soil_moisture_0_1cm?.[0] },
                    { depth: '6cm', temp: hourly.soil_temperature_6cm?.[0], moisture: hourly.soil_moisture_1_3cm?.[0] },
                    { depth: '18cm', temp: hourly.soil_temperature_18cm?.[0], moisture: hourly.soil_moisture_3_9cm?.[0] },
                    { depth: '54cm', temp: hourly.soil_temperature_54cm?.[0], moisture: hourly.soil_moisture_9_27cm?.[0] }
                ];

                container.innerHTML = soilData.map(item => `
                    <div class="data-card-uniform bg-orange-500 text-white">
                        <div class="text-xs font-semibold mb-1">${item.depth}</div>
                        ${item.temp ? `<div class="text-lg font-bold">${Math.round(item.temp)}°C</div>` : '<div class="text-lg font-bold">--°C</div>'}
                        ${item.moisture ? `<div class="text-xs">${item.moisture.toFixed(2)} m³/m³</div>` : '<div class="text-xs">-- m³/m³</div>'}
                    </div>
                `).join('');
            }

            displayExtendedForecast(daily) {
                const container = document.getElementById('extendedForecast');
                if (!daily || !daily.time) {
                    container.innerHTML = '<div class="text-gray-500 text-center py-4">Forecast data not available</div>';
                    return;
                }

                const days = daily.time.slice(0, 7).map((date, index) => ({
                    date: new Date(date),
                    weatherCode: daily.weather_code[index],
                    maxTemp: daily.temperature_2m_max[index],
                    minTemp: daily.temperature_2m_min[index],
                    precipitation: daily.precipitation_sum[index],
                    windSpeed: daily.wind_speed_10m_max[index]
                }));

                container.innerHTML = days.map(day => `
                    <div class="forecast-item">
                        <div class="flex items-center gap-3">
                            <div class="text-xl">${this.getWeatherEmoji(day.weatherCode)}</div>
                            <div>
                                <div class="font-semibold text-gray-800 text-sm">${day.date.toLocaleDateString('en-US', { weekday: 'short', month: 'short', day: 'numeric' })}</div>
                                <div class="text-xs text-gray-600">Rain: ${Math.round(day.precipitation || 0)}mm</div>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="font-bold text-gray-800 text-sm">${Math.round(day.maxTemp)}° / ${Math.round(day.minTemp)}°</div>
                            <div class="text-xs text-gray-600">${Math.round(day.windSpeed)} km/h</div>
                        </div>
                    </div>
                `).join('');
            }

            showWeatherError() {
                const container = document.getElementById('currentWeatherData');
                if (container) {
                    container.innerHTML = `
                        <div class="bg-red-100 border border-red-300 rounded-xl p-3 text-center">
                            <div class="text-red-500 text-xl mb-1">⚠️</div>
                            <p class="text-red-700 text-xs">Unable to fetch weather data</p>
                        </div>
                    `;
                }
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
                const lastUpdated = document.getElementById('lastUpdated');
                const forecastUpdated = document.getElementById('forecastUpdated');

                if (lastUpdated) lastUpdated.textContent = `Updated: ${timeString}`;
                if (forecastUpdated) forecastUpdated.textContent = `Updated: ${timeString}`;
            }
        }

        // Initialize the app
        let app;
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => {
                console.log('DOM loaded, creating app instance...');
                app = new EnhancedWeatherMapApp();
            });
        } else {
            console.log('DOM already loaded, creating app instance immediately...');
            app = new EnhancedWeatherMapApp();
        }
    </script>
@endsection