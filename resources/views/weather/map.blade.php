@extends('layouts.app')

@section('content')
    <!-- Header -->
    <header class="glass-effect p-4 shadow-lg">
        <h1 class="text-gray-800 text-3xl font-bold mb-2">🌤️ SafeCast</h1>
        <p class="text-gray-600 text-sm absolute top-4 right-4 flex object-right-top w-96 gap-2 items-center">Click anywhere
            on the map or search locations to get weather and nearby places</p>
    </header>

    <!-- Main Container -->
    <div class="flex flex-1 h-[calc(100vh-200px)] gap-4 p-4 flex-col lg:flex-row">
        <!-- Map -->
        <div
            class="flex-2 lg:flex-[2] relative rounded-2xl shadow-2xl border-4 border-white/30 h-96 lg:h-auto overflow-hidden">

            <!-- Search Controls Overlay with Autocomplete -->
            <div class="overlay absolute right-4 top-4 flex gap-2 items-center">
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
                        class="absolute right-14 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600 text-xl hidden"
                        title="Clear search">
                        ×
                    </button>

                    <!-- Autocomplete Dropdown -->
                    <div id="autocompleteDropdown"
                        class="absolute top-full left-0 right-0 mt-2 bg-white/95 backdrop-blur-md rounded-2xl shadow-2xl border border-blue-100 hidden max-h-80 overflow-y-auto z-50">
                        <!-- Suggestions will be populated here -->
                    </div>
                </div>

                <button
                    class="bg-blue-500 hover:bg-blue-600 active:bg-blue-700 text-white px-6 py-3 rounded-3xl font-medium transition-all duration-300 hover:scale-105 hover:shadow-lg backdrop-blur-sm shadow-lg"
                    onclick="app.searchLocation()">
                    🔍 Search
                </button>
            </div>

            <details class="overlay absolute top-20 h-fit w-fit right-4">
                <summary
                    class="list-none cursor-pointer bg-white/95 backdrop-blur-sm rounded-lg p-3 shadow-lg hover:shadow-xl transition-all duration-300 border-2 border-blue-500/30 hover:border-blue-500">
                    <div class="w-6 h-6 flex items-center justify-center text-blue-500" title="Map Layers">
                        ☰
                    </div>
                </summary>

                <!-- Menu Content -->
                <div class="menu-content absolute top-full right-0 mt-1 -mr-3.5 w-fit min-w-max rounded-2xl p-4">
                    <button
                        class="bg-blue-500 hover:bg-blue-600 active:bg-blue-700 text-white p-3 rounded-2xl font-medium transition-all duration-300 hover:scale-105 hover:shadow-lg backdrop-blur-sm shadow-lg">
                        🗺️
                    </button>
                </div>
            </details>

            <!-- Map -->
            <div id="map" class="w-full h-full rounded-2xl"></div>
        </div>
    </div>

    <!-- JavaScript -->
    <script>
        class EnhancedWeatherMapApp {
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

            bindEvents() {
                // Original search button event (fallback)
                document.getElementById('searchInput').addEventListener('keypress', (e) => {
                    if (e.key === 'Enter' && !document.getElementById('autocompleteDropdown').classList.contains('hidden')) {
                        // If dropdown is open, let autocomplete handle it
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

            initAutocomplete() {
                const input = document.getElementById('searchInput');
                const dropdown = document.getElementById('autocompleteDropdown');
                const loadingIndicator = document.getElementById('loadingIndicator');
                const clearButton = document.getElementById('clearButton');

                // Input events for autocomplete
                input.addEventListener('input', (e) => this.handleAutocompleteInput(e));
                input.addEventListener('keydown', (e) => this.handleAutocompleteKeydown(e));
                input.addEventListener('focus', () => this.handleAutocompleteFocus());
                input.addEventListener('blur', (e) => this.handleAutocompleteBlur(e));

                // Clear button
                clearButton.addEventListener('click', () => this.clearSearch());

                // Click outside to close
                document.addEventListener('click', (e) => {
                    if (!input.contains(e.target) && !dropdown.contains(e.target)) {
                        this.hideAutocompleteDropdown();
                    }
                });
            }

            handleAutocompleteInput(e) {
                const query = e.target.value.trim();

                // Show/hide clear button
                const clearButton = document.getElementById('clearButton');
                if (query) {
                    clearButton.classList.remove('hidden');
                } else {
                    clearButton.classList.add('hidden');
                }

                // Debounce search
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
                // Delay hiding to allow click on suggestions
                setTimeout(() => {
                    const dropdown = document.getElementById('autocompleteDropdown');
                    if (!dropdown.contains(document.activeElement)) {
                        this.hideAutocompleteDropdown();
                    }
                }, 200);
            }

            async searchAutocompleteLocations(query) {
                // Check cache first
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

                    // Process and format suggestions
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

                    // Cache results
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

                // Update input value
                input.value = suggestion.name;

                // Hide dropdown
                this.hideAutocompleteDropdown();

                // Center map on selected location
                this.map.setView([suggestion.lat, suggestion.lon], 12);
                this.handleLocationSelected(suggestion.lat, suggestion.lon);

                // Clear the search input after a delay to show the selected location
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

            // Helper methods for autocomplete
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

                // Default icons by category
                const defaultIcons = {
                    amenity: '🏢',
                    shop: '🛍️',
                    tourism: '🎯',
                    place: '📍',
                    highway: '🛣️',
                    natural: '🌿'
                };

                return defaultIcons[category] || '📍';
            }

            changeMapLayer(layerType) {
                this.map.eachLayer((layer) => {
                    if (layer !== this.currentMarker) {
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

            handleLocationSelected(lat, lng) {
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

                this.getWeatherData(lat, lng);
                this.getNearbyPOIs(lat, lng);
            }

            async getWeatherData(lat, lng) {
                const weatherInfo = document.getElementById('weather-info');

                weatherInfo.innerHTML = `
                        <div class="text-center text-gray-600 italic">
                            <div class="text-3xl mb-4">⏳</div>
                            Loading weather data...
                        </div>
                    `;

                try {
                    const weatherUrl = `https://api.open-meteo.com/v1/forecast?latitude=${lat}&longitude=${lng}&current_weather=true&hourly=temperature_2m,relative_humidity_2m,wind_speed_10m,wind_direction_10m&timezone=auto`;

                    const weatherResponse = await fetch(weatherUrl);
                    const weatherData = await weatherResponse.json();

                    const geoUrl = `https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&zoom=10&addressdetails=1`;

                    let locationName = `${lat.toFixed(2)}, ${lng.toFixed(2)}`;
                    try {
                        const geoResponse = await fetch(geoUrl);
                        const geoData = await geoResponse.json();
                        if (geoData.address) {
                            locationName = this.formatLocationName(geoData.address);
                        }
                    } catch (error) {
                        console.log('Geocoding error:', error);
                    }

                    this.displayWeatherData(weatherData, locationName);

                } catch (error) {
                    console.error('Error fetching weather data:', error);
                    weatherInfo.innerHTML = `
                            <div class="bg-red-50 text-red-600 p-4 rounded-xl mb-4">
                                <strong>⚠️ Error</strong><br>
                                Could not fetch weather data. Please try again.
                            </div>
                        `;
                }
            }

            async getNearbyPOIs(lat, lng) {
                const poiInfo = document.getElementById('poi-info');

                poiInfo.innerHTML = `
                        <div class="text-center text-gray-600 italic">
                            <div class="text-2xl mb-4">📍</div>
                            Finding nearby places...
                        </div>
                    `;

                try {
                    const radius = 1000;
                    const overpassQuery = `
                            [out:json][timeout:25];
                            (
                                node["amenity"~"^(restaurant|cafe|shop|bank|hospital|pharmacy|school|fuel|hotel|tourism)$"](around:${radius},${lat},${lng});
                                node["shop"](around:${radius},${lat},${lng});
                                node["tourism"](around:${radius},${lat},${lng});
                            );
                            out center meta;
                        `;

                    const overpassUrl = 'https://overpass-api.de/api/interpreter';
                    const response = await fetch(overpassUrl, {
                        method: 'POST',
                        body: overpassQuery
                    });

                    const data = await response.json();
                    this.displayPOIs(data.elements, lat, lng);

                } catch (error) {
                    console.error('Error fetching POI data:', error);
                    poiInfo.innerHTML = `
                            <div class="bg-red-50 text-red-600 p-4 rounded-xl mb-4">
                                <strong>⚠️ Error</strong><br>
                                Could not load nearby places.
                            </div>
                        `;
                }
            }

            displayPOIs(pois, centerLat, centerLng) {
                const poiInfo = document.getElementById('poi-info');

                if (!pois || pois.length === 0) {
                    poiInfo.innerHTML = `
                            <h3 class="section-header">📍 Nearby Places</h3>
                            <div class="bg-blue-50 p-4 rounded-xl text-blue-600 text-sm">
                                No nearby places found in this area.
                            </div>
                        `;
                    return;
                }

                const poisWithDistance = pois
                    .filter(poi => poi.tags && (poi.tags.name || poi.tags.amenity || poi.tags.shop || poi.tags.tourism))
                    .map(poi => {
                        const distance = this.calculateDistance(centerLat, centerLng, poi.lat, poi.lon);
                        return { ...poi, distance };
                    })
                    .sort((a, b) => a.distance - b.distance)
                    .slice(0, 15);

                let html = '<h3 class="section-header">📍 Nearby Places</h3><div class="max-h-80 overflow-y-auto space-y-2">';

                poisWithDistance.forEach(poi => {
                    const name = poi.tags.name || poi.tags.amenity || poi.tags.shop || poi.tags.tourism || 'Unnamed';
                    const type = this.getPOIType(poi.tags);
                    const emoji = this.getPOIEmoji(poi.tags);
                    const distanceText = poi.distance < 1 ? `${Math.round(poi.distance * 1000)}m` : `${poi.distance.toFixed(1)}km`;

                    html += `
                            <div class="bg-blue-50 p-3 rounded-lg cursor-pointer transition-all duration-300 hover:bg-blue-100 hover:-translate-y-0.5" onclick="app.focusPOI(${poi.lat}, ${poi.lon})">
                                <div class="font-bold text-gray-800 mb-1">${emoji} ${name}</div>
                                <div class="text-gray-600 text-sm mb-1">${type}</div>
                                <div class="text-gray-500 text-xs">📍 ${distanceText} away</div>
                            </div>
                        `;
                });

                html += '</div>';
                poiInfo.innerHTML = html;
            }

            focusPOI(lat, lng) {
                this.map.setView([lat, lng], 16);

                const poiMarker = L.circleMarker([lat, lng], {
                    radius: 8,
                    fillColor: '#e74c3c',
                    color: 'white',
                    weight: 2,
                    fillOpacity: 0.8
                }).addTo(this.map);

                setTimeout(() => {
                    this.map.removeLayer(poiMarker);
                }, 3000);
            }

            calculateDistance(lat1, lng1, lat2, lng2) {
                const R = 6371;
                const dLat = (lat2 - lat1) * Math.PI / 180;
                const dLng = (lng2 - lng1) * Math.PI / 180;
                const a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
                    Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
                    Math.sin(dLng / 2) * Math.sin(dLng / 2);
                const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
                return R * c;
            }

            getPOIType(tags) {
                if (tags.amenity) return this.formatType(tags.amenity);
                if (tags.shop) return this.formatType(tags.shop);
                if (tags.tourism) return this.formatType(tags.tourism);
                return 'Place';
            }

            formatType(type) {
                return type.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
            }

            getPOIEmoji(tags) {
                const emojiMap = {
                    restaurant: '🍽️', cafe: '☕', shop: '🛍️', bank: '🏦',
                    hospital: '🏥', pharmacy: '💊', school: '🏫', fuel: '⛽',
                    hotel: '🏨', tourism: '🎯', supermarket: '🏪', bar: '🍺',
                    fast_food: '🍔', bakery: '🥖', clothing: '👕'
                };

                for (const [key, emoji] of Object.entries(emojiMap)) {
                    if (tags.amenity === key || tags.shop === key || tags.tourism === key) {
                        return emoji;
                    }
                }
                return '📍';
            }

            formatLocationName(address) {
                const parts = [];
                if (address.city) parts.push(address.city);
                else if (address.town) parts.push(address.town);
                else if (address.village) parts.push(address.village);

                if (address.state) parts.push(address.state);
                if (address.country) parts.push(address.country);

                return parts.join(', ') || 'Unknown Location';
            }

            getWeatherIcon(weatherCode, isDay) {
                const weatherIcons = {
                    0: isDay ? '☀️' : '🌙', 1: isDay ? '🌤️' : '🌙', 2: '⛅', 3: '☁️',
                    45: '🌫️', 48: '🌫️', 51: '🌦️', 53: '🌦️', 55: '🌦️',
                    61: '🌧️', 63: '🌧️', 65: '🌧️', 71: '🌨️', 73: '🌨️', 75: '🌨️',
                    95: '⛈️', 96: '⛈️', 99: '⛈️'
                };

                return weatherIcons[weatherCode] || '🌤️';
            }

            displayWeatherData(data, locationName) {
                const weatherInfo = document.getElementById('weather-info');
                const current = data.current_weather;
                const hourly = data.hourly;

                const currentHour = new Date().getHours();
                const humidity = hourly.relative_humidity_2m[currentHour] || 'N/A';

                const weatherIcon = this.getWeatherIcon(current.weathercode, current.is_day);

                weatherInfo.innerHTML = `
                                                                                <div class="text-lg font-semibold text-gray-800 mb-4">${locationName}</div>

                                                                                <div class="text-5xl mb-4">${weatherIcon}</div>

                                                                                <div class="text-4xl font-bold text-blue-500 mb-4">${Math.round(current.temperature)}°C</div>

                                                                                <div class="grid grid-cols-2 gap-3 mb-4">
                                                                                    <div class="bg-blue-50 p-3 rounded-lg text-center">
                                                                                        <div class="text-xs text-gray-600 mb-1">💧 Humidity</div>
                                                                                        <div class="text-lg font-bold text-gray-800">${humidity}%</div>
                                                                                    </div>

                                                                                    <div class="bg-blue-50 p-3 rounded-lg text-center">
                                                                                        <div class="text-xs text-gray-600 mb-1">💨 Wind</div>
                                                                                        <div class="text-lg font-bold text-gray-800">${Math.round(current.windspeed)} km/h</div>
                                                                                    </div>

                                                                                    <div class="bg-blue-50 p-3 rounded-lg text-center">
                                                                                        <div class="text-xs text-gray-600 mb-1">🧭 Direction</div>
                                                                                        <div class="text-lg font-bold text-gray-800">${current.winddirection}°</div>
                                                                                    </div>

                                                                                    <div class="bg-blue-50 p-3 rounded-lg text-center">
                                                                                        <div class="text-xs text-gray-600 mb-1">🌡️ Temp</div>
                                                                                        <div class="text-lg font-bold text-gray-800">${Math.round(current.temperature)}°C</div>
                                                                                    </div>
                                                                                </div>

                                                                                <div class="text-xs text-gray-500 text-center mt-4">
                                                                                    📅 ${new Date().toLocaleString()}
                                                                                </div>
                                                                            `;
            }
        }

        let app;
        document.addEventListener('DOMContentLoaded', () => {
            app = new EnhancedWeatherMapApp();
        });
    </script>
@endsection