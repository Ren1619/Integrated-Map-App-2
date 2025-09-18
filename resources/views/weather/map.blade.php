@extends('layouts.app')

@section('content')
    <!-- Header -->
    <header class="glass-effect p-4 shadow-lg">
        <h1 class="text-gray-800 text-3xl font-bold mb-2">🌤️ SafeCast</h1>
        <p class="text-gray-600 text-sm absolute top-4 right-4 flex object-right-top w-96 gap-2 items-center">
            Click anywhere on the map or search locations to get weather and nearby places
        </p>
    </header>

    <!-- Main Container -->
    <div class="flex flex-1 h-[calc(100vh-200px)] gap-4 p-4 flex-col lg:flex-row">
        <!-- Map -->
        <div class="flex-2 lg:flex-[2] relative rounded-2xl shadow-2xl border-4 border-white/30 h-96 lg:h-auto overflow-hidden">

            <!-- Search Controls Overlay -->
            <div class="overlay absolute right-4 top-4 flex gap-2 items-center z-50">
                <div class="search-container relative">
                    <input type="text"
                        class="flex-1 px-4 py-3 border-2 border-blue-500/30 rounded-full outline-none text-sm bg-white/95 backdrop-blur-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 transition-all duration-300 shadow-lg min-w-80"
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

            <!-- Weather Layers Control -->
            <div class="overlay absolute top-20 right-4 z-40">
                <details class="h-fit w-fit">
                    <summary class="list-none cursor-pointer bg-white/95 backdrop-blur-sm rounded-lg p-3 shadow-lg hover:shadow-xl transition-all duration-300 border-2 border-blue-500/30 hover:border-blue-500">
                        <div class="w-6 h-6 flex items-center justify-center text-blue-500" title="Weather Layers">🌦️</div>
                    </summary>

                    <div class="menu-content absolute top-full right-0 mt-1 -mr-3.5 w-64 bg-white/95 backdrop-blur-sm rounded-2xl p-4 shadow-xl border border-blue-100">
                        <h3 class="text-sm font-semibold text-gray-700 mb-3">Weather Layers</h3>
                        
                        <!-- Temperature Layer -->
                        <div class="mb-3">
                            <label class="flex items-center cursor-pointer">
                                <input type="checkbox" id="temperatureLayer" class="weather-layer-toggle sr-only">
                                <div class="relative">
                                    <div class="block bg-gray-300 w-10 h-6 rounded-full"></div>
                                    <div class="dot absolute left-1 top-1 bg-white w-4 h-4 rounded-full transition"></div>
                                </div>
                                <span class="ml-3 text-sm text-gray-700 flex items-center">
                                    <span class="mr-2">🌡️</span>Temperature
                                </span>
                            </label>
                        </div>

                        <!-- Wind Layer -->
                        <div class="mb-3">
                            <label class="flex items-center cursor-pointer">
                                <input type="checkbox" id="windLayer" class="weather-layer-toggle sr-only">
                                <div class="relative">
                                    <div class="block bg-gray-300 w-10 h-6 rounded-full"></div>
                                    <div class="dot absolute left-1 top-1 bg-white w-4 h-4 rounded-full transition"></div>
                                </div>
                                <span class="ml-3 text-sm text-gray-700 flex items-center">
                                    <span class="mr-2">💨</span>Wind
                                </span>
                            </label>
                        </div>

                        <!-- Radar Layer -->
                        <div class="mb-3">
                            <label class="flex items-center cursor-pointer">
                                <input type="checkbox" id="radarLayer" class="weather-layer-toggle sr-only">
                                <div class="relative">
                                    <div class="block bg-gray-300 w-10 h-6 rounded-full"></div>
                                    <div class="dot absolute left-1 top-1 bg-white w-4 h-4 rounded-full transition"></div>
                                </div>
                                <span class="ml-3 text-sm text-gray-700 flex items-center">
                                    <span class="mr-2">🌧️</span>Precipitation
                                </span>
                            </label>
                        </div>

                        <!-- Layer Opacity Control -->
                        <div class="mt-4 pt-3 border-t border-gray-200">
                            <label class="text-xs text-gray-600 block mb-2">Layer Opacity</label>
                            <input type="range" id="layerOpacity" min="0.1" max="1" step="0.1" value="0.7" 
                                class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer">
                        </div>
                    </div>
                </details>
            </div>

            <!-- Map Layer Control -->
            <div class="overlay absolute top-40 right-4 z-40">
                <details class="h-fit w-fit">
                    <summary class="list-none cursor-pointer bg-white/95 backdrop-blur-sm rounded-lg p-3 shadow-lg hover:shadow-xl transition-all duration-300 border-2 border-blue-500/30 hover:border-blue-500">
                        <div class="w-6 h-6 flex items-center justify-center text-blue-500" title="Map Layers">☰</div>
                    </summary>

                    <div class="menu-content absolute top-full right-0 mt-1 -mr-3.5 w-fit min-w-max rounded-2xl p-4">
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

            <!-- Weather Info Panel -->
            <div id="weatherInfo" class="overlay absolute bottom-4 left-4 bg-white/95 backdrop-blur-sm rounded-2xl p-4 shadow-xl border border-blue-100 max-w-sm hidden">
                <div id="weatherContent">
                    <!-- Weather data will be populated here -->
                </div>
            </div>

            <!-- Map Container -->
            <div id="map" class="w-full h-full rounded-2xl"></div>
        </div>
    </div>

    <!-- Enhanced CSS for Weather Toggles -->
    <style>
        .weather-layer-toggle:checked + div {
            background-color: #3b82f6;
        }
        .weather-layer-toggle:checked + div .dot {
            transform: translateX(100%);
        }
        
        /* Wind arrow styles */
        .wind-arrow {
            position: absolute;
            width: 20px;
            height: 20px;
            pointer-events: none;
            z-index: 1000;
        }
        
        .wind-arrow::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            border-left: 3px solid transparent;
            border-right: 3px solid transparent;
            border-bottom: 12px solid #2563eb;
            transform: translate(-50%, -50%);
        }
        
        /* Temperature gradient colors */
        .temp-cold { background: radial-gradient(circle, rgba(59, 130, 246, 0.8) 0%, rgba(59, 130, 246, 0.3) 100%); }
        .temp-cool { background: radial-gradient(circle, rgba(34, 197, 94, 0.8) 0%, rgba(34, 197, 94, 0.3) 100%); }
        .temp-warm { background: radial-gradient(circle, rgba(251, 191, 36, 0.8) 0%, rgba(251, 191, 36, 0.3) 100%); }
        .temp-hot { background: radial-gradient(circle, rgba(239, 68, 68, 0.8) 0%, rgba(239, 68, 68, 0.3) 100%); }
        
        /* Precipitation intensity colors */
        .precip-light { background: radial-gradient(circle, rgba(34, 197, 94, 0.6) 0%, rgba(34, 197, 94, 0.2) 100%); }
        .precip-moderate { background: radial-gradient(circle, rgba(251, 191, 36, 0.7) 0%, rgba(251, 191, 36, 0.3) 100%); }
        .precip-heavy { background: radial-gradient(circle, rgba(239, 68, 68, 0.8) 0%, rgba(239, 68, 68, 0.4) 100%); }
    </style>

    <!-- Enhanced JavaScript -->
    <script>
        class EnhancedWeatherMapApp {
            constructor() {
                this.map = null;
                this.currentMarker = null;
                this.currentLat = null;
                this.currentLng = null;
                this.mapLayers = {};
                
                // Weather overlay layers
                this.weatherLayers = {
                    temperature: null,
                    wind: null,
                    radar: null
                };
                
                this.activeWeatherLayers = new Set();

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
                this.initWeatherControls();
                this.setActiveLayerButton('standard');
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

                // Initialize weather layer groups
                this.weatherLayers.temperature = L.layerGroup();
                this.weatherLayers.wind = L.layerGroup();
                this.weatherLayers.radar = L.layerGroup();
            }

            initWeatherControls() {
                // Weather layer toggles
                document.getElementById('temperatureLayer').addEventListener('change', (e) => {
                    this.toggleWeatherLayer('temperature', e.target.checked);
                });
                
                document.getElementById('windLayer').addEventListener('change', (e) => {
                    this.toggleWeatherLayer('wind', e.target.checked);
                });
                
                document.getElementById('radarLayer').addEventListener('change', (e) => {
                    this.toggleWeatherLayer('radar', e.target.checked);
                });

                // Opacity control
                document.getElementById('layerOpacity').addEventListener('input', (e) => {
                    this.updateLayerOpacity(parseFloat(e.target.value));
                });
            }

            toggleWeatherLayer(layerType, enabled) {
                if (enabled) {
                    this.activeWeatherLayers.add(layerType);
                    if (this.currentLat && this.currentLng) {
                        this.loadWeatherLayer(layerType, this.currentLat, this.currentLng);
                    }
                } else {
                    this.activeWeatherLayers.delete(layerType);
                    this.weatherLayers[layerType].clearLayers();
                    if (this.map.hasLayer(this.weatherLayers[layerType])) {
                        this.map.removeLayer(this.weatherLayers[layerType]);
                    }
                }
            }

            updateLayerOpacity(opacity) {
                Object.values(this.weatherLayers).forEach(layer => {
                    if (layer) {
                        layer.eachLayer(marker => {
                            if (marker.getElement) {
                                marker.getElement().style.opacity = opacity;
                            }
                        });
                    }
                });
            }

            async loadWeatherLayer(layerType, lat, lng) {
                try {
                    let endpoint = '';
                    switch(layerType) {
                        case 'temperature':
                            endpoint = '/weather/temperature';
                            break;
                        case 'wind':
                            endpoint = '/weather/wind';
                            break;
                        case 'radar':
                            endpoint = '/weather/radar';
                            break;
                    }

                    const response = await fetch(endpoint, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({ lat, lng })
                    });

                    const result = await response.json();
                    if (result.success) {
                        this.renderWeatherLayer(layerType, result.data);
                    }
                } catch (error) {
                    console.error(`Error loading ${layerType} layer:`, error);
                }
            }

            renderWeatherLayer(layerType, data) {
                const layer = this.weatherLayers[layerType];
                layer.clearLayers();

                switch(layerType) {
                    case 'temperature':
                        this.renderTemperatureLayer(layer, data);
                        break;
                    case 'wind':
                        this.renderWindLayer(layer, data);
                        break;
                    case 'radar':
                        this.renderRadarLayer(layer, data);
                        break;
                }

                if (!this.map.hasLayer(layer)) {
                    this.map.addLayer(layer);
                }
            }

            renderTemperatureLayer(layer, data) {
                data.forEach(point => {
                    if (point.current_temp !== null) {
                        const temp = Math.round(point.current_temp);
                        const tempClass = this.getTemperatureClass(temp);
                        
                        const marker = L.circleMarker([point.lat, point.lng], {
                            radius: 25,
                            fillColor: this.getTemperatureColor(temp),
                            color: 'white',
                            weight: 1,
                            fillOpacity: 0.6,
                            className: tempClass
                        }).bindTooltip(`${temp}°C`, {
                            permanent: true,
                            direction: 'center',
                            className: 'temp-tooltip'
                        });
                        
                        layer.addLayer(marker);
                    }
                });
            }

            renderWindLayer(layer, data) {
                data.forEach(point => {
                    if (point.current && point.current.wind_speed_10m !== null) {
                        const speed = Math.round(point.current.wind_speed_10m);
                        const direction = point.current.wind_direction_10m;
                        
                        const arrow = L.marker([point.lat, point.lng], {
                            icon: L.divIcon({
                                className: 'wind-arrow',
                                html: `<div style="transform: rotate(${direction}deg); font-size: ${Math.min(speed + 8, 20)}px;">↑</div>`,
                                iconSize: [20, 20],
                                iconAnchor: [10, 10]
                            })
                        }).bindTooltip(`${speed} km/h`, {
                            direction: 'top',
                            offset: [0, -10]
                        });
                        
                        layer.addLayer(arrow);
                    }
                });
            }

            renderRadarLayer(layer, data) {
                if (data.precipitation && data.precipitation.length > 0) {
                    // Use current location as center for radar visualization
                    const currentPrecip = data.precipitation[0] || 0;
                    
                    if (currentPrecip > 0) {
                        const precipClass = this.getPrecipitationClass(currentPrecip);
                        
                        const radar = L.circleMarker([this.currentLat, this.currentLng], {
                            radius: 30,
                            fillColor: this.getPrecipitationColor(currentPrecip),
                            color: 'rgba(255, 255, 255, 0.8)',
                            weight: 1,
                            fillOpacity: 0.7,
                            className: precipClass
                        }).bindTooltip(`${currentPrecip.toFixed(1)} mm/h`, {
                            permanent: true,
                            direction: 'center',
                            className: 'precip-tooltip'
                        });
                        
                        layer.addLayer(radar);
                    }
                }
            }

            getTemperatureClass(temp) {
                if (temp < 0) return 'temp-cold';
                if (temp < 15) return 'temp-cool';
                if (temp < 25) return 'temp-warm';
                return 'temp-hot';
            }

            getTemperatureColor(temp) {
                if (temp < 0) return '#3b82f6';      // Blue
                if (temp < 15) return '#22c55e';     // Green
                if (temp < 25) return '#fbbf24';     // Yellow
                return '#ef4444';                    // Red
            }

            getPrecipitationClass(precip) {
                if (precip < 1) return 'precip-light';
                if (precip < 5) return 'precip-moderate';
                return 'precip-heavy';
            }

            getPrecipitationColor(precip) {
                if (precip < 1) return '#22c55e';    // Light green
                if (precip < 5) return '#fbbf24';    // Yellow
                return '#ef4444';                    // Red
            }

            bindEvents() {
                // Original search button event (fallback)
                document.getElementById('searchInput').addEventListener('keypress', (e) => {
                    if (e.key === 'Enter' && !document.getElementById('autocompleteDropdown').classList.contains('hidden')) {
                        return;
                    } else if (e.key === 'Enter') {
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
                        (error) => {
                            console.log('Geolocation error:', error);
                        }
                    );
                }
            }

            // ... (keeping all existing autocomplete methods) ...

            initAutocomplete() {
                const input = document.getElementById('searchInput');
                const dropdown = document.getElementById('autocompleteDropdown');
                const loadingIndicator = document.getElementById('loadingIndicator');
                const clearButton = document.getElementById('clearButton');

                input.addEventListener('input', (e) => this.handleAutocompleteInput(e));
                input.addEventListener('keydown', (e) => this.handleAutocompleteKeydown(e));
                input.addEventListener('focus', () => this.handleAutocompleteFocus());
                input.addEventListener('blur', (e) => this.handleAutocompleteBlur(e));

                clearButton.addEventListener('click', () => this.clearSearch());

                document.addEventListener('click', (e) => {
                    if (!input.contains(e.target) && !dropdown.contains(e.target)) {
                        this.hideAutocompleteDropdown();
                    }
                });
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
                            e.target.blur();
                            break;
                    }
                }
            }

            handleAutocompleteFocus() {
                const input = document.getElementById('searchInput');
                const query = input.value.trim();
                if (query.length >= 2 && this.suggestions.length > 0) {
                    this.showAutocompleteDropdown();
                }
            }

            handleAutocompleteBlur(e) {
                setTimeout(() => {
                    const dropdown = document.getElementById('autocompleteDropdown');
                    if (!dropdown.contains(document.activeElement)) {
                        this.hideAutocompleteDropdown();
                    }
                }, 200);
            }

            async searchAutocompleteLocations(query) {
                if (this.cache.has(query)) {
                    this.displayAutocompleteSuggestions(this.cache.get(query));
                    return;
                }

                this.showAutocompleteLoading(true);

                try {
                    const response = await fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}&limit=8&addressdetails=1&extratags=1`);

                    if (!response.ok) {
                        throw new Error('Search failed');
                    }

                    const data = await response.json();

                    const suggestions = data.map(item => ({
                        id: item.place_id,
                        display_name: item.display_name,
                        name: item.name || this.extractLocationName(item.display_name),
                        address: this.formatAddress(item.address),
                        lat: parseFloat(item.lat),
                        lon: parseFloat(item.lon),
                        type: this.getLocationType(item),
                        icon: this.getLocationIcon(item)
                    }));

                    this.cache.set(query, suggestions);
                    this.displayAutocompleteSuggestions(suggestions);

                } catch (error) {
                    console.error('Autocomplete search error:', error);
                    this.displayAutocompleteError('Unable to search locations. Please try again.');
                } finally {
                    this.showAutocompleteLoading(false);
                }
            }

            displayAutocompleteSuggestions(suggestions) {
                this.suggestions = suggestions;
                this.selectedIndex = -1;

                const dropdown = document.getElementById('autocompleteDropdown');

                if (suggestions.length === 0) {
                    this.displayAutocompleteNoResults();
                    return;
                }

                let html = '';
                suggestions.forEach((suggestion, index) => {
                    html += `
                        <div class="autocomplete-item p-3 cursor-pointer transition-all duration-200 hover:bg-blue-50 border-b border-gray-100 last:border-b-0 ${index === this.selectedIndex ? 'selected' : ''}" 
                             data-index="${index}"
                             onclick="app.selectAutocompleteSuggestion(${index})">
                            <div class="flex items-center space-x-3">
                                <div class="text-xl">${suggestion.icon}</div>
                                <div class="flex-1 min-w-0">
                                    <div class="font-semibold text-gray-800 truncate">${suggestion.name}</div>
                                    <div class="text-sm text-gray-600 truncate">${suggestion.address}</div>
                                    <div class="text-xs text-blue-500 mt-1">${suggestion.type}</div>
                                </div>
                            </div>
                        </div>
                    `;
                });

                dropdown.innerHTML = html;
                this.showAutocompleteDropdown();
            }

            displayAutocompleteNoResults() {
                const dropdown = document.getElementById('autocompleteDropdown');
                dropdown.innerHTML = `
                    <div class="p-4 text-center text-gray-500">
                        <div class="text-2xl mb-2">🔍</div>
                        <div>No results found</div>
                        <div class="text-sm">Try a different search term</div>
                    </div>
                `;
                this.showAutocompleteDropdown();
            }

            displayAutocompleteError(message) {
                const dropdown = document.getElementById('autocompleteDropdown');
                dropdown.innerHTML = `
                    <div class="p-4 text-center text-red-500">
                        <div class="text-2xl mb-2">⚠️</div>
                        <div>${message}</div>
                    </div>
                `;
                this.showAutocompleteDropdown();
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
                const dropdown = document.getElementById('autocompleteDropdown');
                const items = dropdown.querySelectorAll('.autocomplete-item');
                items.forEach((item, index) => {
                    if (index === this.selectedIndex) {
                        item.classList.add('selected');
                        item.scrollIntoView({ block: 'nearest' });
                    } else {
                        item.classList.remove('selected');
                    }
                });
            }

            selectCurrentAutocompleteSuggestion() {
                if (this.selectedIndex >= 0 && this.selectedIndex < this.suggestions.length) {
                    this.selectAutocompleteSuggestion(this.selectedIndex);
                }
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

            clearSearch() {
                const input = document.getElementById('searchInput');
                const clearButton = document.getElementById('clearButton');

                input.value = '';
                input.focus();
                this.hideAutocompleteDropdown();
                clearButton.classList.add('hidden');
            }

            showAutocompleteDropdown() {
                const dropdown = document.getElementById('autocompleteDropdown');
                dropdown.classList.remove('hidden');
            }

            hideAutocompleteDropdown() {
                const dropdown = document.getElementById('autocompleteDropdown');
                dropdown.classList.add('hidden');
                this.selectedIndex = -1;
            }

            showAutocompleteLoading(show) {
                const loadingIndicator = document.getElementById('loadingIndicator');
                const clearButton = document.getElementById('clearButton');
                const input = document.getElementById('searchInput');

                if (show) {
                    loadingIndicator.classList.remove('hidden');
                    clearButton.classList.add('hidden');
                } else {
                    loadingIndicator.classList.add('hidden');
                    if (input.value.trim()) {
                        clearButton.classList.remove('hidden');
                    }
                }
            }

            extractLocationName(displayName) {
                return displayName.split(',')[0].trim();
            }

            formatAddress(address) {
                if (!address) return 'Unknown location';

                const parts = [];
                if (address.road) parts.push(address.road);
                if (address.city) parts.push(address.city);
                else if (address.town) parts.push(address.town);
                else if (address.village) parts.push(address.village);
                if (address.state) parts.push(address.state);
                if (address.country) parts.push(address.country);

                return parts.join(', ') || 'Unknown location';
            }

            getLocationType(item) {
                if (item.type) {
                    return this.formatType(item.type);
                }
                if (item.class) {
                    return this.formatType(item.class);
                }
                return 'Location';
            }

            formatType(type) {
                return type.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
            }

            getLocationIcon(item) {
                const iconMap = {
                    'amenity': {
                        restaurant: '🍽️', cafe: '☕', bank: '🏦', hospital: '🏥',
                        school: '🏫', university: '🎓', pharmacy: '💊', fuel: '⛽',
                        hotel: '🏨', bar: '🍺', fast_food: '🍔', pub: '🍻'
                    },
                    'shop': {
                        supermarket: '🏪', mall: '🏬', clothes: '👕', book: '📚',
                        electronics: '📱', bakery: '🥖', florist: '🌸'
                    },
                    'tourism': {
                        attraction: '🎯', museum: '🏛️', monument: '🗿', castle: '🏰',
                        zoo: '🦁', theme_park: '🎢'
                    },
                    'place': {
                        city: '🏙️', town: '🏘️', village: '🏡', country: '🌍',
                        state: '🗺️', county: '📍'
                    }
                };

                const category = item.class || 'place';
                const type = item.type || 'city';

                if (iconMap[category] && iconMap[category][type]) {
                    return iconMap[category][type];
                }

                const defaultIcons = {
                    amenity: '🏢', shop: '🛒', tourism: '🎯',
                    place: '📍', highway: '🛣️', natural: '🌿'
                };

                return defaultIcons[category] || '📍';
            }

            changeMapLayer(layerType) {
                this.map.eachLayer((layer) => {
                    if (layer !== this.currentMarker && !Object.values(this.weatherLayers).includes(layer)) {
                        this.map.removeLayer(layer);
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
                        alert(result.message || 'Location not found. Please try a different search term.');
                    }
                } catch (error) {
                    console.error('Search error:', error);
                    alert('Error searching for location. Please try again.');
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

                // Load weather data and layers
                await this.getWeatherData(lat, lng);
                
                // Load active weather layers
                this.activeWeatherLayers.forEach(layerType => {
                    this.loadWeatherLayer(layerType, lat, lng);
                });
            }

            async getWeatherData(lat, lng) {
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
                        this.displayWeatherInfo(result.data);
                    }
                } catch (error) {
                    console.error('Weather data error:', error);
                }
            }

            displayWeatherInfo(data) {
                const weatherInfo = document.getElementById('weatherInfo');
                const weatherContent = document.getElementById('weatherContent');
                
                if (!data.weather || !data.weather.current) {
                    weatherInfo.classList.add('hidden');
                    return;
                }

                const current = data.weather.current;
                const location = data.location || 'Unknown Location';
                
                const weatherCode = this.getWeatherEmoji(current.weather_code);
                const temp = Math.round(current.temperature_2m);
                const feelsLike = Math.round(current.apparent_temperature);
                const humidity = current.relative_humidity_2m;
                const pressure = current.surface_pressure;
                const windSpeed = Math.round(current.wind_speed_10m);
                const windDir = current.wind_direction_10m;
                
                weatherContent.innerHTML = `
                    <div class="text-center mb-3">
                        <div class="text-4xl mb-2">${weatherCode}</div>
                        <div class="text-2xl font-bold text-gray-800">${temp}°C</div>
                        <div class="text-sm text-gray-600">Feels like ${feelsLike}°C</div>
                        <div class="text-xs text-gray-500 mt-1">${location}</div>
                    </div>
                    <div class="grid grid-cols-2 gap-2 text-sm">
                        <div class="bg-blue-50 p-2 rounded">
                            <div class="text-blue-600 font-medium">💨 Wind</div>
                            <div class="text-gray-700">${windSpeed} km/h</div>
                        </div>
                        <div class="bg-blue-50 p-2 rounded">
                            <div class="text-blue-600 font-medium">💧 Humidity</div>
                            <div class="text-gray-700">${humidity}%</div>
                        </div>
                        <div class="bg-blue-50 p-2 rounded">
                            <div class="text-blue-600 font-medium">📊 Pressure</div>
                            <div class="text-gray-700">${pressure} hPa</div>
                        </div>
                        <div class="bg-blue-50 p-2 rounded">
                            <div class="text-blue-600 font-medium">🧭 Wind Dir</div>
                            <div class="text-gray-700">${windDir}°</div>
                        </div>
                    </div>
                `;
                
                weatherInfo.classList.remove('hidden');
            }

            getWeatherEmoji(weatherCode) {
                const weatherEmojis = {
                    0: '☀️',    // Clear sky
                    1: '🌤️',   // Mainly clear
                    2: '⛅',    // Partly cloudy
                    3: '☁️',    // Overcast
                    45: '🌫️',  // Fog
                    48: '🌫️',  // Depositing rime fog
                    51: '🌦️',  // Light drizzle
                    53: '🌦️',  // Moderate drizzle
                    55: '🌧️',  // Dense drizzle
                    61: '🌦️',  // Slight rain
                    63: '🌧️',  // Moderate rain
                    65: '🌧️',  // Heavy rain
                    80: '🌦️',  // Slight rain showers
                    81: '🌧️',  // Moderate rain showers
                    82: '⛈️',   // Violent rain showers
                    95: '⛈️',   // Thunderstorm
                };
                return weatherEmojis[weatherCode] || '🌤️';
            }
        }

        let app;
        document.addEventListener('DOMContentLoaded', () => {
            app = new EnhancedWeatherMapApp();
        });
    </script>
@endsection