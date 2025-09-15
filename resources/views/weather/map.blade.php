@extends('layouts.app')

@section('content')
    <!-- Header -->
    <header class="glass-effect p-4 text-center shadow-lg">
        <h1 class="text-gray-800 text-3xl font-bold mb-2">🌤️ Enhanced Weather Map Explorer</h1>
        <p class="text-gray-600 text-sm">Click anywhere on the map or search locations to get weather and nearby places</p>
    </header>

    <!-- Controls -->
    <div class="glass-effect p-4 flex flex-wrap gap-4 justify-center items-center shadow-lg">
        <!-- Search Container -->
        <div class="flex gap-2 items-center">
            <input type="text" class="input-primary" placeholder="Search for a place (e.g., New York, Paris, Tokyo)"
                id="searchInput">
            <button class="btn-primary" onclick="app.searchLocation()">
                🔍 Search
            </button>
        </div>

        <!-- Layer Controls -->
        <div class="flex gap-2">
            <button class="btn-primary active:bg-blue-700" id="standardBtn" onclick="app.changeMapLayer('standard')">
                🗺️ Standard
            </button>
            <button class="btn-primary" id="cycleBtn" onclick="app.changeMapLayer('cycle')">
                🚴 Cycle
            </button>
            <button class="btn-primary" id="transportBtn" onclick="app.changeMapLayer('transport')">
                🚌 Transport
            </button>
        </div>
    </div>

    <!-- Main Container -->
    <div class="flex flex-1 h-[calc(100vh-200px)] gap-4 p-4 flex-col lg:flex-row">
        <!-- Map -->
        <div id="map" class="flex-2 lg:flex-[2] rounded-2xl shadow-2xl border-4 border-white/30 h-96 lg:h-auto"></div>

        <!-- Info Panel -->
        <div class="flex-1 flex flex-col lg:flex-col md:flex-row gap-4">
            <!-- Weather Panel -->
            <div class="glass-effect rounded-2xl p-6 shadow-2xl border-4 border-white/30 flex-1 overflow-y-auto">
                <div class="text-xl font-bold text-gray-800 mb-4 pb-2 border-b-2 border-blue-500">
                    🌤️ Weather Information
                </div>
                <div id="weather-info" class="text-center">
                    <div class="bg-blue-50 p-4 rounded-xl text-blue-600 text-sm">
                        <strong>🗺️ How to use:</strong><br>
                        Click on any location on the map or search for a place to get detailed weather information!
                    </div>
                </div>
            </div>

            <!-- POI Panel -->
            <div class="glass-effect rounded-2xl p-6 shadow-2xl border-4 border-white/30 flex-1 overflow-y-auto">
                <div class="text-xl font-bold text-gray-800 mb-4 pb-2 border-b-2 border-blue-500">
                    📍 Nearby Places
                </div>
                <div id="poi-info">
                    <div class="bg-blue-50 p-4 rounded-xl text-blue-600 text-sm">
                        Select a location to discover nearby restaurants, shops, attractions, and more!
                    </div>
                </div>
            </div>
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
                this.init();
            }

            init() {
                this.initMap();
                this.bindEvents();
                this.setActiveLayerButton('standard');
            }

            initMap() {
                this.map = L.map('map').setView([40.7128, -74.0060], 10);

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
                        (error) => {
                            console.log('Geolocation error:', error);
                        }
                    );
                }
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
                document.getElementById(layerType + 'Btn').classList.add('bg-blue-700');
                document.getElementById(layerType + 'Btn').classList.remove('bg-blue-500');
            }

            async searchLocation() {
                const query = document.getElementById('searchInput').value.trim();
                if (!query) return;

                try {
                    // Use Laravel route for search
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
                    const weatherUrl = `https://api.open-meteo.com/v1/forecast?latitude=\${lat}&longitude=\${lng}&current_weather=true&hourly=temperature_2m,relative_humidity_2m,wind_speed_10m,wind_direction_10m&timezone=auto`;

                    const weatherResponse = await fetch(weatherUrl);
                    const weatherData = await weatherResponse.json();

                    const geoUrl = `https://nominatim.openstreetmap.org/reverse?format=json&lat=\${lat}&lon=\${lng}&zoom=10&addressdetails=1`;

                    let locationName = `\${lat.toFixed(2)}, \${lng.toFixed(2)}`;
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
                            node["amenity"~"^(restaurant|cafe|shop|bank|hospital|pharmacy|school|fuel|hotel|tourism)$"](around:\${radius},\${lat},\${lng});
                            node["shop"](around:\${radius},\${lat},\${lng});
                            node["tourism"](around:\${radius},\${lat},\${lng});
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

                let html = '<div class="max-h-80 overflow-y-auto space-y-2">';

                poisWithDistance.forEach(poi => {
                    const name = poi.tags.name || poi.tags.amenity || poi.tags.shop || poi.tags.tourism || 'Unnamed';
                    const type = this.getPOIType(poi.tags);
                    const emoji = this.getPOIEmoji(poi.tags);
                    const distanceText = poi.distance < 1 ? `\${Math.round(poi.distance * 1000)}m\` : \`\${poi.distance.toFixed(1)}km\`;

                    html += \`
                        <div class="bg-blue-50 p-3 rounded-lg cursor-pointer transition-all duration-300 hover:bg-blue-100 hover:-translate-y-0.5" onclick="app.focusPOI(\${poi.lat}, \${poi.lon})">
                            <div class="font-bold text-gray-800 mb-1">\${emoji} \${name}</div>
                            <div class="text-gray-600 text-sm mb-1">\${type}</div>
                            <div class="text-gray-500 text-xs">📍 \${distanceText} away</div>
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
                    <div class="text-lg font-semibold text-gray-800 mb-4">\${locationName}</div>

                    <div class="text-5xl mb-4">\${weatherIcon}</div>

                    <div class="text-4xl font-bold text-blue-500 mb-4">\${Math.round(current.temperature)}°C</div>

                    <div class="grid grid-cols-2 gap-3 mb-4">
                        <div class="bg-blue-50 p-3 rounded-lg text-center">
                            <div class="text-xs text-gray-600 mb-1">💧 Humidity</div>
                            <div class="text-lg font-bold text-gray-800">\${humidity}%</div>
                        </div>

                        <div class="bg-blue-50 p-3 rounded-lg text-center">
                            <div class="text-xs text-gray-600 mb-1">💨 Wind</div>
                            <div class="text-lg font-bold text-gray-800">\${Math.round(current.windspeed)} km/h</div>
                        </div>

                        <div class="bg-blue-50 p-3 rounded-lg text-center">
                            <div class="text-xs text-gray-600 mb-1">🧭 Direction</div>
                            <div class="text-lg font-bold text-gray-800">\${current.winddirection}°</div>
                        </div>

                        <div class="bg-blue-50 p-3 rounded-lg text-center">
                            <div class="text-xs text-gray-600 mb-1">🌡️ Temp</div>
                            <div class="text-lg font-bold text-gray-800">\${Math.round(current.temperature)}°C</div>
                        </div>
                    </div>

                    <div class="text-xs text-gray-500 text-center mt-4">
                        📅 \${new Date().toLocaleString()}
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