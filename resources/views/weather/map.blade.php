@extends('layouts.app')

@section('content')
    <!-- Meta tag for authentication check -->
    <meta name="user-authenticated" content="{{ Auth::check() ? 'true' : 'false' }}">

    <!-- Main Container - Mobile: scrollable, Desktop: fixed -->
    <div
        class="min-h-screen md:h-[calc(100vh-4rem)] flex flex-col gap-2 md:gap-3 lg:gap-4 p-2 md:p-3 lg:p-4 overflow-y-auto md:overflow-hidden bg-gradient-to-br from-white to-blue-50 dark:from-slate-900 dark:to-slate-800">

        <!-- Top Section: Map + 7-Day Forecast - Mobile: auto height, Desktop: fixed -->
        <div class="flex flex-col lg:flex-row gap-2 md:gap-3 lg:gap-4 min-h-[400px] md:h-[55%] lg:h-[58%] flex-shrink-0">

            <!-- Map Container - Mobile: fixed height, Desktop: responsive -->
            <div
                class="w-full lg:w-[65%] relative rounded-xl shadow-xl border border-blue-200/30 dark:border-slate-700/50 overflow-hidden h-[300px] md:h-full bg-white dark:bg-slate-900">

                <!-- Search Bar - Ultra compact for mobile -->
                <div
                    class="absolute top-2 left-2 right-2 md:top-3 md:left-3 md:right-3 lg:top-4 lg:left-4 lg:right-4 flex gap-1.5 md:gap-2 items-center z-[1000]">
                    <div class="search-container relative flex-1 group">
                        <div
                            class="absolute inset-0 bg-blue-400/20 dark:bg-blue-500/20 rounded-full blur opacity-0 group-hover:opacity-100 transition-opacity duration-300 -z-10">
                        </div>
                        <input type="text"
                            class="w-full h-8 md:h-10 lg:h-11 pl-3 pr-7 md:pl-4 md:pr-10 border border-blue-400/30 dark:border-blue-500/30 rounded-full outline-none text-xs md:text-sm bg-white/95 dark:bg-slate-800/90 text-slate-800 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 backdrop-blur-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 transition-all duration-300 shadow-md"
                            placeholder="🔍 Search..." id="searchInput" autocomplete="off">

                        <div id="loadingIndicator"
                            class="absolute right-8 md:right-11 top-1/2 transform -translate-y-1/2 hidden">
                            <div
                                class="animate-spin rounded-full h-3.5 w-3.5 md:h-4 md:w-4 border-2 border-blue-500 border-t-transparent">
                            </div>
                        </div>

                        <button id="clearButton"
                            class="absolute right-2 md:right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 text-base md:text-xl hidden hover:scale-110 transition-all w-5 h-5 md:w-6 md:h-6 flex items-center justify-center"
                            title="Clear search">×</button>

                        <div id="autocompleteDropdown"
                            class="absolute top-full left-0 right-0 mt-1.5 md:mt-2 bg-white/95 dark:bg-slate-800/95 backdrop-blur-md rounded-lg md:rounded-xl shadow-xl border border-blue-200 dark:border-blue-500/20 hidden max-h-48 md:max-h-64 lg:max-h-80 overflow-y-auto z-[1001] text-xs md:text-sm">
                        </div>
                    </div>

                    <button
                        class="h-8 md:h-10 lg:h-11 bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 active:from-blue-700 active:to-blue-800 text-white px-3 md:px-5 lg:px-6 rounded-full text-xs md:text-sm font-medium transition-all duration-200 hover:shadow-lg shadow-md whitespace-nowrap flex-shrink-0 flex items-center justify-center gap-1 md:gap-1.5"
                        onclick="app.searchLocation()">
                        <span class="hidden sm:inline">Search</span>
                        <span class="sm:hidden">Go</span>
                        <span class="text-xs md:text-sm">🔍</span>
                    </button>
                </div>

                <!-- Leaflet Map - Full container -->
                <div id="map" class="w-full h-full"></div>
            </div>

            <!-- 7-Day Forecast Panel - Mobile: auto height with no scroll, Desktop: fixed with scroll -->
            <div
                class="w-full lg:w-[35%] flex flex-col rounded-xl overflow-hidden min-h-[300px] md:h-full bg-white/80 dark:bg-slate-900/80 backdrop-blur-md shadow-xl border border-blue-200/30 dark:border-slate-700/50">
                <!-- Panel Header - Fixed height -->
                <div
                    class="flex-shrink-0 p-3 md:p-4 lg:p-5 border-b border-blue-200/50 dark:border-blue-500/20 bg-gradient-to-r from-blue-50/50 to-transparent dark:from-blue-950/50">
                    <h2
                        class="text-base md:text-lg lg:text-xl font-bold text-gray-800 dark:text-slate-100 flex items-center gap-2">
                        <span class="text-blue-500 dark:text-blue-400">📅</span>
                        <span
                            class="bg-gradient-to-r from-blue-600 to-blue-400 dark:from-blue-400 dark:to-blue-300 bg-clip-text text-transparent">7-Day
                            Forecast</span>
                    </h2>
                    <p class="text-xs sm:text-sm text-gray-600 dark:text-slate-300 mt-1" id="forecastLocation">
                        <span class="animate-pulse">📍</span> Select a location to view forecast
                    </p>
                </div>

                <!-- Panel Content - Mobile: no scroll (part of page scroll), Desktop: scrollable -->
                <div
                    class="flex-1 md:overflow-y-auto custom-scrollbar p-3 md:p-4 bg-gradient-to-b from-transparent to-blue-50/30 dark:to-blue-950/30">
                    <div id="extendedForecast" class="space-y-2 md:space-y-3">
                        <div class="text-center py-6 md:py-8 text-gray-500 dark:text-gray-400">
                            <div class="text-3xl md:text-4xl mb-3 animate-bounce">🎯</div>
                            <p class="text-xs md:text-sm px-4 animate-pulse">Select any location worldwide to view 7-day
                                weather forecast</p>
                        </div>
                    </div>
                </div>

                <!-- Panel Footer - Fixed height -->
                <div
                    class="flex-shrink-0 p-2.5 md:p-3 lg:p-4 border-t border-blue-200/50 dark:border-blue-500/20 bg-gradient-to-b from-blue-50/50 to-blue-100/50 dark:from-blue-950/50 dark:to-slate-900/60">
                    <div class="flex items-center justify-between text-[10px] md:text-xs text-gray-600 dark:text-slate-300">
                        <span class="flex items-center gap-1">
                            <span class="text-blue-500 dark:text-blue-400">📊</span>
                            Extended Forecast
                        </span>
                        <span class="flex items-center gap-1" id="forecastUpdated">
                            <span class="text-blue-500 dark:text-blue-400">🕒</span>
                            <span class="hidden sm:inline">Updated: --:--</span>
                            <span class="sm:hidden">--:--</span>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bottom Section - Mobile: auto height (no scroll), Desktop: flex-1 with scroll -->
        <div
            class="flex flex-col rounded-xl overflow-hidden min-h-[500px] md:flex-1 md:min-h-0 bg-white/80 dark:bg-slate-900/80 backdrop-blur-md shadow-xl border border-blue-200/30 dark:border-slate-700/50">
            <!-- Panel Header - Fixed -->
            <div
                class="flex-shrink-0 p-3 md:p-4 border-b border-blue-200/50 dark:border-blue-500/20 bg-gradient-to-r from-blue-50/50 to-transparent dark:from-blue-950/50">
                <div class="flex flex-col sm:flex-row justify-between items-start gap-2 sm:gap-4">
                    <div class="flex-1 min-w-0 w-full sm:w-auto">
                        <div class="flex items-center gap-2 mb-1 flex-wrap">
                            <h2 class="text-base md:text-lg lg:text-xl font-bold text-gray-800 dark:text-slate-100 flex items-center gap-2"
                                id="weatherPanelTitle">
                                <span class="text-blue-500 dark:text-blue-400">🌦️</span>
                                <span
                                    class="bg-gradient-to-r from-blue-600 to-blue-400 dark:from-blue-400 dark:to-blue-300 bg-clip-text text-transparent">
                                    Weather Data
                                </span>
                            </h2>

                            @auth
                                <button id="saveLocationBtn" onclick="app.toggleSaveLocation()"
                                    class="hidden px-2.5 md:px-3 py-1 md:py-1.5 bg-blue-500 hover:bg-blue-600 text-white rounded-lg shadow-sm hover:shadow-md transition-all duration-200 flex items-center gap-1.5 text-xs font-medium"
                                    title="Save this location">
                                    <span id="saveLocationIcon">📍</span>
                                    <span id="saveLocationText" class="hidden sm:inline">Save</span>
                                </button>
                            @endauth
                        </div>
                        <p class="text-xs sm:text-sm text-gray-600 dark:text-slate-300 truncate" id="locationDetails">
                            Click on map or search for weather info
                        </p>
                    </div>

                    <div class="text-[10px] md:text-xs text-gray-500 dark:text-slate-300 whitespace-nowrap flex-shrink-0">
                        <span id="lastUpdated">Updated: --:--</span>
                    </div>
                </div>
            </div>

            <!-- Scrollable Content - Mobile: no overflow (part of page), Desktop: scrollable -->
            <div class="flex-1 md:overflow-y-auto md:overflow-x-hidden custom-scrollbar">
                <div
                    class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 md:gap-4 p-3 md:p-4 bg-gradient-to-b from-transparent to-blue-50/30 dark:to-blue-950/30">
                    <!-- Current Weather Section -->
                    <div class="w-full">
                        <h3 class="text-sm md:text-base font-semibold mb-2 md:mb-3 flex items-center gap-2">
                            <span class="text-blue-500 dark:text-blue-400">📍</span>
                            <span
                                class="bg-gradient-to-r from-gray-800 to-gray-600 dark:from-gray-200 dark:to-gray-400 bg-clip-text text-transparent">
                                Current Conditions
                            </span>
                        </h3>
                        <div id="currentWeatherData" class="hidden"></div>
                        <div id="weatherPlaceholder"
                            class="text-center py-6 text-gray-500 dark:text-gray-400 bg-white/50 dark:bg-slate-800/50 rounded-lg backdrop-blur-sm border border-blue-100/20 dark:border-blue-500/20 shadow-sm">
                            <div class="text-3xl mb-2 animate-bounce">🎯</div>
                            <p class="text-xs animate-pulse">Select location for weather data</p>
                        </div>
                    </div>

                    <!-- Temperature Section -->
                    <div class="w-full">
                        <h3 class="text-sm md:text-base font-semibold mb-2 md:mb-3 flex items-center gap-2">
                            <span class="text-red-500 dark:text-red-400">🌡️</span>
                            <span
                                class="bg-gradient-to-r from-gray-800 to-gray-600 dark:from-gray-200 dark:to-gray-400 bg-clip-text text-transparent">
                                Temperature Levels
                            </span>
                        </h3>
                        <div id="temperatureByAltitude" class="grid grid-cols-2 gap-2"></div>
                    </div>

                    <!-- Wind Section -->
                    <div class="w-full">
                        <h3 class="text-sm md:text-base font-semibold mb-2 md:mb-3 flex items-center gap-2">
                            <span class="text-blue-500 dark:text-blue-400">💨</span>
                            <span
                                class="bg-gradient-to-r from-gray-800 to-gray-600 dark:from-gray-200 dark:to-gray-400 bg-clip-text text-transparent">
                                Wind Analysis
                            </span>
                        </h3>
                        <div id="windByAltitude" class="grid grid-cols-2 gap-2"></div>
                    </div>

                    <!-- Soil Section -->
                    <div class="w-full">
                        <h3 class="text-sm md:text-base font-semibold mb-2 md:mb-3 flex items-center gap-2">
                            <span class="text-green-500 dark:text-green-400">🌱</span>
                            <span
                                class="bg-gradient-to-r from-gray-800 to-gray-600 dark:from-gray-200 dark:to-gray-400 bg-clip-text text-transparent">
                                Soil Analysis
                            </span>
                        </h3>
                        <div id="soilConditions" class="grid grid-cols-2 gap-2"></div>
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
                window.onload = requestLocation;


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

                if (window.location.protocol === 'http:' &&
                    (window.location.hostname === 'localhost' ||
                        window.location.hostname === '127.0.0.1')) {

                    // Localhost - geolocation should work
                    if (navigator.geolocation) {
                        navigator.geolocation.getCurrentPosition(
                            function (position) {
                                console.log('Location access granted');
                                // Your location handling code
                            },
                            function (error) {
                                console.error('Location error:', error.message);
                                alert('Location access denied or unavailable');
                            }
                        );
                    } else {
                        alert('Geolocation is not supported by this browser/app');
                    }
                } else if (window.location.protocol === 'http:') {
                    // Non-localhost HTTP
                    alert('Geolocation requires HTTPS or localhost');
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
                    zoomControl: false,
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

                // Update location details FIRST to get the location name
                await this.updateLocationDetailsAndTitle(locationDetails, lat, lng);

                // Now record search with proper location details
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

                // If location details are already provided, use them
                if (locationDetails && locationDetails.name) {
                    const parts = [];
                    if (locationDetails.name) parts.push(locationDetails.name);
                    if (locationDetails.admin1) parts.push(locationDetails.admin1);
                    if (locationDetails.country) parts.push(locationDetails.country);

                    const locationName = parts.join(', ');

                    if (titleElement) {
                        titleElement.innerHTML = `
                        <span class="text-blue-500 dark:text-blue-400">🌦️</span>
                        <span class="bg-gradient-to-r from-blue-600 to-blue-400 dark:from-blue-400 dark:to-blue-300 bg-clip-text text-transparent">
                            ${locationName}
                        </span>
                    `;
                    }
                    if (detailsElement) detailsElement.textContent = locationName;
                    if (forecastLocationElement) forecastLocationElement.textContent = locationName;

                    // Store in currentLocationDetails
                    this.currentLocationDetails = {
                        name: locationName,
                        display_name: locationName,
                        address_components: locationDetails.address_components || null
                    };

                    return;
                }

                // If no location details provided, fetch them via reverse geocoding
                try {
                    console.log('Fetching location name for:', lat, lng);

                    const response = await fetch('/weather/location-name', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ lat, lng })
                    });

                    const result = await response.json();

                    console.log('Location name response:', result);

                    if (result.success && result.data) {
                        const locationName = result.data.location_name || result.data.display_name;

                        if (titleElement) {
                            titleElement.innerHTML = `
                            <span class="text-blue-500 dark:text-blue-400">🌦️</span>
                            <span class="bg-gradient-to-r from-blue-600 to-blue-400 dark:from-blue-400 dark:to-blue-300 bg-clip-text text-transparent">
                                ${locationName}
                            </span>
                        `;
                        }
                        if (detailsElement) detailsElement.textContent = locationName;
                        if (forecastLocationElement) forecastLocationElement.textContent = locationName;

                        // Store the location details
                        this.currentLocationDetails = {
                            name: locationName,
                            display_name: result.data.display_name || locationName,
                            address_components: result.data.address_components || null
                        };

                        console.log('Location details updated:', this.currentLocationDetails);
                    } else {
                        throw new Error('Invalid response from location name API');
                    }

                } catch (error) {
                    console.error('Error fetching location name:', error);

                    // Fallback to coordinates
                    const coordsText = `${lat.toFixed(4)}°, ${lng.toFixed(4)}°`;

                    if (titleElement) {
                        titleElement.innerHTML = `
                        <span class="text-blue-500 dark:text-blue-400">🌦️</span>
                        <span class="bg-gradient-to-r from-blue-600 to-blue-400 dark:from-blue-400 dark:to-blue-300 bg-clip-text text-transparent">
                            ${coordsText}
                        </span>
                    `;
                    }
                    if (detailsElement) detailsElement.textContent = `Coordinates: ${coordsText}`;
                    if (forecastLocationElement) forecastLocationElement.textContent = coordsText;

                    // Store fallback location details
                    this.currentLocationDetails = {
                        name: coordsText,
                        display_name: `Coordinates: ${coordsText}`,
                        address_components: null
                    };
                }
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
                                    <div class="current-weather-card bg-blue-500 dark:bg-slate-700 rounded-xl p-2" style="min-height: 104px; height: 104px;">
                                        <div class="flex items-center justify-between mb-2">
                                            <div class="flex items-center gap-2">
                                                <div class="text-2xl font-bold text-white">${temp}°C</div>
                                                <div>
                                                    <h4 class="text-xs font-semibold text-white">Current</h4>
                                                    <p class="text-blue-100 text-[10px]">${new Date().toLocaleTimeString()}</p>
                                                </div>
                                            </div>
                                            <div class="text-3xl">${weatherEmoji}</div>
                                        </div>
                                        <div class="mb-2">
                                            <div class="text-blue-100 text-[11px]">Feels like ${feelsLike}°C</div>
                                        </div>
                                        <div class="grid grid-cols-2 gap-1.5">
                                            <div class="bg-white/20 dark:bg-white/10 rounded-lg px-1.5 py-1 text-center">
                                                <div class="text-white font-bold text-xs">${current.relative_humidity_2m}%</div>
                                                <div class="text-blue-100 text-[10px]">Humidity</div>
                                            </div>
                                            <div class="bg-white/20 dark:bg-white/10 rounded-lg px-1.5 py-1 text-center">
                                                <div class="text-white font-bold text-xs">${Math.round(current.surface_pressure)}</div>
                                                <div class="text-blue-100 text-[10px]">Pressure</div>
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
                                        <div class="data-card-uniform p-2 text-white min-h-[64px]" style="background: linear-gradient(135deg, ${item.color} 0%, ${item.color}dd 100%);">
                                            <div class="text-[10px] font-semibold mb-0.5">${item.level}</div>
                                            <div class="text-sm font-bold">${displayTemp}°C</div>
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
                                    <div class="data-card-uniform bg-blue-100 dark:bg-slate-800 p-2 min-h-[64px]">
                                        <div class="text-[10px] font-semibold text-gray-700 dark:text-slate-200 mb-0.5">${item.level}</div>
                                        <div class="text-sm font-bold text-blue-600 dark:text-slate-100">${Math.round(item.speed || 0)} km/h</div>
                                        ${item.direction ? `<div class=\"text-[10px] text-gray-600 dark:text-slate-300\">${item.direction}°</div>` : ''}
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
                                    <div class="data-card-uniform bg-orange-500 text-white p-2 dark:bg-amber-600 min-h-[64px]">
                                        <div class="text-[10px] font-semibold mb-0.5">${item.depth}</div>
                                        ${item.temp ? `<div class="text-sm font-bold">${Math.round(item.temp)}°C</div>` : '<div class="text-sm font-bold">--°C</div>'}
                                        ${item.moisture ? `<div class="text-[10px]">${item.moisture.toFixed(2)} m³/m³</div>` : '<div class="text-[10px]">-- m³/m³</div>'}
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
                                                <div class="font-semibold text-gray-800 dark:text-slate-100 text-sm">${day.date.toLocaleDateString('en-US', { weekday: 'short', month: 'short', day: 'numeric' })}</div>
                                                <div class="text-xs text-gray-600 dark:text-slate-300">Rain: ${Math.round(day.precipitation || 0)}mm</div>
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <div class="font-bold text-gray-800 dark:text-slate-100 text-sm">${Math.round(day.maxTemp)}° / ${Math.round(day.minTemp)}°</div>
                                            <div class="text-xs text-gray-600 dark:text-slate-300">${Math.round(day.windSpeed)} km/h</div>
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
                app = new EnhancedWeatherMapApp();
            });
        } else {
            app = new EnhancedWeatherMapApp();
        }
    </script>
@endsection