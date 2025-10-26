@section('content')
    <!-- Main Container with Flexbox Layout -->
    <div class="p-4 flex flex-col gap-4 max-h-full">

        <!-- Top Section (Map + Forecast) -->
        <div class="flex gap-4" style="min-height: 0;">

            <!-- Map Container (2/3 width) -->
            <div class="flex-[2] relative rounded-2xl shadow-2xl border-4 border-white/30 overflow-hidden">
                <!-- Search Controls Overlay -->
                <div class="overlay absolute justify-end top-4 left-4 right-4 flex gap-2 items-center">
                    <div class="search-container relative flex-1 max-w-md">
                        <!-- ... existing search input code ... -->
                    </div>

                    <button
                        class="bg-blue-500 hover:bg-blue-600 active:bg-blue-700 text-white px-6 py-3 rounded-3xl font-medium transition-all duration-300 hover:scale-105 hover:shadow-lg backdrop-blur-sm shadow-lg"
                        onclick="app.searchLocation()">🔍 Search</button>
                </div>

                <!-- Map Container -->
                <div id="map" class="w-full h-full rounded-2xl"></div>
            </div>

            <!-- 7-Day Forecast Panel (1/3 width) -->
            <div class="flex-1 panel-glass flex flex-col rounded-2xl min-h-[600px]">
                <!-- ... existing forecast panel code ... -->
            </div>
        </div>

        <!-- Bottom Section - Comprehensive Weather Data -->
        <div class="panel-glass flex flex-col rounded-2xl min-h-[400px]">
            <!-- Panel Header WITH SAVE BUTTON -->
            <div class="p-4 border-b border-blue-100 flex-shrink-0">
                <div class="flex justify-between items-center">
                    <div class="flex-1">
                        <h2 class="text-xl font-bold text-gray-800 flex items-center gap-2" id="weatherPanelTitle">
                            🌦️ Comprehensive Weather Data
                        </h2>
                        <p class="text-sm text-gray-600 mt-1" id="locationDetails">Click on map or search for detailed
                            weather information</p>
                    </div>

                    <!-- Save Location Button (Only for authenticated users) -->
                    @auth
                        <div class="flex items-center gap-2">
                            <button
                                id="saveLocationBtn"
                                onclick="app.toggleSaveLocation()"
                                class="hidden px-4 py-2 rounded-lg font-medium text-sm transition-all duration-300 hover:scale-105 hover:shadow-lg"
                                disabled>
                                <span class="flex items-center gap-2">
                                    <span id="saveLocationIcon">📍</span>
                                    <span id="saveLocationText">Save Location</span>
                                </span>
                            </button>
                            <div class="text-xs text-gray-500">
                                <span id="lastUpdated">Updated: --:--</span>
                            </div>
                        </div>
                    @else
                        <div class="text-xs text-gray-500">
                            <span id="lastUpdated">Updated: --:--</span>
                        </div>
                    @endauth
                </div>
            </div>

            <!-- ... rest of the weather content ... -->
        </div>
    </div>

    <!-- Save Location Modal -->
    @auth
        <div id="saveLocationModal" class="fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center z-50 p-4">
            <div class="bg-white rounded-2xl shadow-xl max-w-md w-full" onclick="event.stopPropagation()">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-xl font-bold text-gray-900 flex items-center gap-2">
                            <span>📍</span>
                            <span>Save Location</span>
                        </h3>
                        <button onclick="app.closeSaveModal()" class="text-gray-400 hover:text-gray-600 text-2xl">&times;</button>
                    </div>

                    <form id="saveLocationForm" onsubmit="app.submitSaveLocation(event)">
                        <!-- Location Name (auto-filled) -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Location</label>
                            <input type="text" id="modalLocationName" readonly
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg bg-gray-50 text-gray-600">
                        </div>

                        <!-- Custom Name -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Custom Name *</label>
                            <input type="text" id="modalCustomName" required
                                placeholder="e.g., Home, Office, Favorite Beach"
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <!-- Emoji Picker -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Icon</label>
                            <div class="flex gap-2 flex-wrap">
                                <button type="button" onclick="app.selectEmoji('🏠')" class="emoji-btn">🏠</button>
                                <button type="button" onclick="app.selectEmoji('🏢')" class="emoji-btn">🏢</button>
                                <button type="button" onclick="app.selectEmoji('🏖️')" class="emoji-btn">🏖️</button>
                                <button type="button" onclick="app.selectEmoji('🏔️')" class="emoji-btn">🏔️</button>
                                <button type="button" onclick="app.selectEmoji('🌴')" class="emoji-btn">🌴</button>
                                <button type="button" onclick="app.selectEmoji('⛰️')" class="emoji-btn">⛰️</button>
                                <button type="button" onclick="app.selectEmoji('🗼')" class="emoji-btn">🗼</button>
                                <button type="button" onclick="app.selectEmoji('🏛️')" class="emoji-btn">🏛️</button>
                                <button type="button" onclick="app.selectEmoji('⭐')" class="emoji-btn">⭐</button>
                                <button type="button" onclick="app.selectEmoji('❤️')" class="emoji-btn">❤️</button>
                            </div>
                            <input type="hidden" id="modalEmoji" value="📍">
                        </div>

                        <!-- Notes (Optional) -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Notes (Optional)</label>
                            <textarea id="modalNotes" rows="3"
                                placeholder="Add any personal notes about this location..."
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"></textarea>
                        </div>

                        <!-- Buttons -->
                        <div class="flex items-center gap-3">
                            <button type="button" onclick="app.closeSaveModal()"
                                class="flex-1 px-4 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium rounded-lg transition-colors">
                                Cancel
                            </button>
                            <button type="submit"
                                class="flex-1 px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors">
                                Save Location
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endauth

    <!-- Enhanced CSS -->
    <style>
        /* ... existing styles ... */

        /* Save Location Button Styles */
        #saveLocationBtn {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: white;
        }

        #saveLocationBtn:not(:disabled):hover {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
        }

        #saveLocationBtn.saved {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        }

        #saveLocationBtn.saved:not(:disabled):hover {
            background: linear-gradient(135deg, #059669 0%, #047857 100%);
        }

        #saveLocationBtn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        /* Modal Styles */
        #saveLocationModal {
            animation: fadeIn 0.3s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        #saveLocationModal > div {
            animation: slideUp 0.3s ease-out;
        }

        @keyframes slideUp {
            from { 
                transform: translateY(20px);
                opacity: 0;
            }
            to { 
                transform: translateY(0);
                opacity: 1;
            }
        }

        /* Emoji Button Styles */
        .emoji-btn {
            width: 48px;
            height: 48px;
            font-size: 24px;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            transition: all 0.2s;
            background: white;
        }

        .emoji-btn:hover {
            border-color: #3b82f6;
            background: #eff6ff;
            transform: scale(1.1);
        }

        .emoji-btn.selected {
            border-color: #3b82f6;
            background: #dbeafe;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2);
        }
    </style>

    <!-- Enhanced JavaScript -->
    <script>
        class EnhancedWeatherMapApp {
            constructor() {
                this.map = null;
                this.currentMarker = null;
                this.currentLat = null;
                this.currentLng = null;
                this.currentLocationName = null;
                this.currentAddressComponents = null;
                this.isSaved = false;
                this.savedLocationId = null;
                this.selectedEmoji = '📍';
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

            // ... existing methods (initMap, bindEvents, etc.) ...

            async handleLocationSelected(lat, lng, locationDetails = null) {
                this.currentLat = lat;
                this.currentLng = lng;

                // Clear existing markers
                if (this.currentMarker) {
                    this.map.removeLayer(this.currentMarker);
                }

                // Add main location marker
                this.currentMarker = L.circleMarker([lat, lng], {
                    radius: 12,
                    fillColor: '#3b82f6',
                    color: 'white',
                    weight: 3,
                    fillOpacity: 0.9
                }).addTo(this.map);

                // Update location details and get location name if needed
                await this.updateLocationDetailsAndTitle(locationDetails, lat, lng);

                // Check if location is saved (only for authenticated users)
                @auth
                await this.checkIfLocationSaved();
                @endauth

                // Show loading states
                this.showLoadingStates();

                // Get comprehensive weather data
                await this.getEnhancedWeatherData(lat, lng);

                // Record search history (only for authenticated users)
                @auth
                await this.recordSearchHistory();
                @endauth

                this.updateLastUpdated();
            }

            async updateLocationDetailsAndTitle(locationDetails, lat, lng) {
                const titleElement = document.getElementById('weatherPanelTitle');
                const detailsElement = document.getElementById('locationDetails');
                const forecastLocationElement = document.getElementById('forecastLocation');

                // If location details are provided (from search), use them
                if (locationDetails && locationDetails.name) {
                    const parts = [];
                    if (locationDetails.name) parts.push(locationDetails.name);
                    if (locationDetails.admin1) parts.push(locationDetails.admin1);
                    if (locationDetails.country) parts.push(locationDetails.country);

                    const locationName = parts.join(', ');
                    this.currentLocationName = locationName;
                    this.currentAddressComponents = locationDetails;

                    titleElement.innerHTML = `🌦️ ${locationName}`;
                    detailsElement.textContent = locationName;
                    forecastLocationElement.textContent = locationName;

                    // Show save button
                    this.showSaveButton();
                    return;
                }

                // Otherwise, perform reverse geocoding
                console.log('Performing reverse geocoding for:', lat, lng);

                // Show loading state
                const coordsText = `${lat.toFixed(6)}°, ${lng.toFixed(6)}°`;
                titleElement.innerHTML = `🌦️ Loading location...`;
                detailsElement.textContent = `Coordinates: ${coordsText}`;
                forecastLocationElement.textContent = 'Loading...';

                try {
                    const response = await fetch('/weather/location-name', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ 
                            lat: parseFloat(lat), 
                            lng: parseFloat(lng) 
                        })
                    });

                    console.log('Reverse geocoding response status:', response.status);

                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }

                    const result = await response.json();
                    console.log('Reverse geocoding result:', result);

                    if (result.success && result.data && result.data.location_name) {
                        this.currentLocationName = result.data.location_name;
                        this.currentAddressComponents = result.data.address_components;

                        // Use short name for title
                        titleElement.innerHTML = `🌦️ ${result.data.location_name}`;
                        
                        // Use full address for details
                        const fullAddress = result.data.full_address || result.data.location_name;
                        detailsElement.innerHTML = `
                            <div class="text-sm">
                                <div class="font-medium">${fullAddress}</div>
                                ${result.data.address_components && result.data.address_components.barangay ? 
                                    `<div class="text-xs text-gray-500 mt-1">Barangay: ${result.data.address_components.barangay}</div>` : ''}
                            </div>
                        `;
                        
                        // Use location name for forecast
                        forecastLocationElement.textContent = result.data.location_name;

                        // Show save button
                        this.showSaveButton();
                        
                        console.log('Location name updated successfully');
                        return;
                    } else {
                        console.warn('Reverse geocoding returned no location name');
                    }
                } catch (error) {
                    console.error('Reverse geocoding error:', error);
                }

                // Fallback to coordinates if geocoding failed
                this.currentLocationName = `Location: ${coordsText}`;
                this.currentAddressComponents = null;
                
                titleElement.innerHTML = `🌦️ ${this.currentLocationName}`;
                detailsElement.textContent = `Coordinates: ${coordsText}`;
                forecastLocationElement.textContent = this.currentLocationName;

                // Show save button even for coordinates
                this.showSaveButton();
            }

            showSaveButton() {
                @auth
                const saveBtn = document.getElementById('saveLocationBtn');
                if (saveBtn) {
                    saveBtn.classList.remove('hidden');
                    saveBtn.disabled = false;
                }
                @endauth
            }

            @auth
            async checkIfLocationSaved() {
                try {
                    const response = await fetch('/user/saved-locations/check', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({
                            latitude: this.currentLat,
                            longitude: this.currentLng
                        })
                    });

                    const result = await response.json();
                    
                    if (result.success) {
                        this.isSaved = result.is_saved;
                        this.savedLocationId = result.data ? result.data.id : null;
                        this.updateSaveButtonState();
                    }
                } catch (error) {
                    console.error('Error checking saved status:', error);
                }
            }

            updateSaveButtonState() {
                const saveBtn = document.getElementById('saveLocationBtn');
                const saveIcon = document.getElementById('saveLocationIcon');
                const saveText = document.getElementById('saveLocationText');

                if (this.isSaved) {
                    saveBtn.classList.add('saved');
                    saveIcon.textContent = '✓';
                    saveText.textContent = 'Saved';
                } else {
                    saveBtn.classList.remove('saved');
                    saveIcon.textContent = '📍';
                    saveText.textContent = 'Save Location';
                }
            }

            toggleSaveLocation() {
                if (this.isSaved) {
                    // If already saved, remove it
                    this.removeSavedLocation();
                } else {
                    // If not saved, show modal to save
                    this.openSaveModal();
                }
            }

            openSaveModal() {
                // Pre-fill the modal with current location
                document.getElementById('modalLocationName').value = this.currentLocationName || 'Unknown Location';
                document.getElementById('modalCustomName').value = '';
                document.getElementById('modalNotes').value = '';
                this.selectedEmoji = '📍';
                
                // Reset emoji selection
                document.querySelectorAll('.emoji-btn').forEach(btn => btn.classList.remove('selected'));
                
                // Show modal
                document.getElementById('saveLocationModal').classList.remove('hidden');
            }

            closeSaveModal() {
                document.getElementById('saveLocationModal').classList.add('hidden');
            }

            selectEmoji(emoji) {
                this.selectedEmoji = emoji;
                document.getElementById('modalEmoji').value = emoji;
                
                // Update button states
                document.querySelectorAll('.emoji-btn').forEach(btn => {
                    if (btn.textContent === emoji) {
                        btn.classList.add('selected');
                    } else {
                        btn.classList.remove('selected');
                    }
                });
            }

            async submitSaveLocation(event) {
                event.preventDefault();

                const customName = document.getElementById('modalCustomName').value;
                const notes = document.getElementById('modalNotes').value;

                try {
                    const response = await fetch('/user/saved-locations/toggle', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({
                            name: customName,
                            location_name: this.currentLocationName,
                            latitude: this.currentLat,
                            longitude: this.currentLng,
                            address_components: this.currentAddressComponents,
                            emoji: this.selectedEmoji,
                            notes: notes || null
                        })
                    });

                    const result = await response.json();

                    if (result.success) {
                        this.isSaved = result.action === 'added';
                        this.savedLocationId = result.data ? result.data.id : null;
                        this.updateSaveButtonState();
                        this.closeSaveModal();

                        // Show success message
                        this.showNotification('Location saved successfully!', 'success');
                    } else {
                        throw new Error(result.message || 'Failed to save location');
                    }
                } catch (error) {
                    console.error('Error saving location:', error);
                    this.showNotification('Failed to save location. Please try again.', 'error');
                }
            }

            async removeSavedLocation() {
                if (!this.savedLocationId) return;

                if (!confirm('Are you sure you want to remove this saved location?')) {
                    return;
                }

                try {
                    const response = await fetch(`/user/saved-locations/${this.savedLocationId}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    });

                    const result = await response.json();

                    if (result.success) {
                        this.isSaved = false;
                        this.savedLocationId = null;
                        this.updateSaveButtonState();
                        this.showNotification('Location removed from saved list', 'success');
                    } else {
                        throw new Error(result.message || 'Failed to remove location');
                    }
                } catch (error) {
                    console.error('Error removing location:', error);
                    this.showNotification('Failed to remove location. Please try again.', 'error');
                }
            }

            async recordSearchHistory() {
                if (!this.currentLocationName || !this.currentLat || !this.currentLng) return;

                try {
                    await fetch('/user/search-history', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({
                            location_name: this.currentLocationName,
                            latitude: this.currentLat,
                            longitude: this.currentLng,
                            address_components: this.currentAddressComponents,
                            search_type: 'map_click'
                        })
                    });
                } catch (error) {
                    console.error('Error recording search history:', error);
                }
            }

            showNotification(message, type = 'info') {
                // Create notification element
                const notification = document.createElement('div');
                notification.className = `fixed top-4 right-4 z-50 px-6 py-3 rounded-lg shadow-lg transform transition-all duration-300 ${
                    type === 'success' ? 'bg-green-500' : 'bg-red-500'
                } text-white`;
                notification.textContent = message;
                
                document.body.appendChild(notification);

                // Animate in
                setTimeout(() => {
                    notification.style.transform = 'translateY(0)';
                    notification.style.opacity = '1';
                }, 10);

                // Remove after 3 seconds
                setTimeout(() => {
                    notification.style.transform = 'translateY(-20px)';
                    notification.style.opacity = '0';
                    setTimeout(() => {
                        document.body.removeChild(notification);
                    }, 300);
                }, 3000);
            }
            @endauth

            // ... rest of existing methods ...
        }

        let app;
        document.addEventListener('DOMContentLoaded', () => {
            app = new EnhancedWeatherMapApp();
        });
    </script>
@endsection