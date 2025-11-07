@props(['position' => 'bottom-right'])

@php
    $positionClasses = [
        'top-right' => 'top-4 right-4',
        'top-left' => 'top-4 left-4',
        'bottom-right' => 'bottom-4 right-4',
        'bottom-left' => 'bottom-4 left-4',
        'top-center' => 'top-4 left-1/2 -translate-x-1/2',
        'bottom-center' => 'bottom-4 left-1/2 -translate-x-1/2',
    ];
@endphp

<!-- Weather Alert Notification Container -->
<div id="weatherAlertContainer"
    class="fixed {{ $positionClasses[$position] }} z-[9999] max-w-sm w-full pointer-events-none" style="display: none;">

    <!-- Alert Card -->
    <div id="weatherAlertCard" class="pointer-events-auto transform transition-all duration-500 ease-in-out"
        style="transform: translateY(100px); opacity: 0;">

        <!-- Alert Content (will be dynamically populated) -->
        <div id="weatherAlertContent"></div>

    </div>
</div>

<script>
    class WeatherAlertNotificationSystem {
        constructor() {
            this.container = document.getElementById('weatherAlertContainer');
            this.card = document.getElementById('weatherAlertCard');
            this.content = document.getElementById('weatherAlertContent');
            this.currentAlert = null;
            this.isVisible = false;
            this.autoHideTimeout = null;
            this.checkInterval = null;
            this.lastCheckTime = null;
            this.userLatitude = null;
            this.userLongitude = null;

            // Alert check frequency (5 minutes)
            this.checkFrequency = 5 * 60 * 1000;

            this.init();
        }

        async init() {
            console.log('🚨 Initializing Weather Alert Notification System...');

            // Get user location and start monitoring
            await this.getUserLocation();

            // Initial alert check
            await this.checkForAlerts();

            // Set up periodic checks
            this.startPeriodicChecks();

            // Listen for location updates from the map
            window.addEventListener('locationUpdated', (e) => {
                this.userLatitude = e.detail.lat;
                this.userLongitude = e.detail.lng;
                this.checkForAlerts();
            });
        }

        async getUserLocation() {
            try {
                if (navigator.geolocation) {
                    const position = await new Promise((resolve, reject) => {
                        navigator.geolocation.getCurrentPosition(resolve, reject);
                    });

                    this.userLatitude = position.coords.latitude;
                    this.userLongitude = position.coords.longitude;
                    console.log('📍 User location obtained:', this.userLatitude, this.userLongitude);
                } else {
                    console.warn('⚠️ Geolocation not supported');
                }
            } catch (error) {
                console.warn('⚠️ Could not get user location:', error.message);
            }
        }

        startPeriodicChecks() {
            // Check every 5 minutes
            this.checkInterval = setInterval(() => {
                this.checkForAlerts();
            }, this.checkFrequency);
        }

        async checkForAlerts() {
            if (!this.userLatitude || !this.userLongitude) {
                console.log('⏸️ No location available, skipping alert check');
                return;
            }

            try {
                console.log('🔍 Checking for weather alerts...');

                const response = await fetch(`/weather/alerts?lat=${this.userLatitude}&lng=${this.userLongitude}`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
                    }
                });

                const result = await response.json();

                if (result.success && result.data.alerts.length > 0) {
                    // Find the most severe alert
                    const dangerAlerts = result.data.alerts.filter(a => a.severity === 'danger');
                    const warningAlerts = result.data.alerts.filter(a => a.severity === 'warning');

                    const alertToShow = dangerAlerts[0] || warningAlerts[0];

                    if (alertToShow) {
                        this.showAlert(alertToShow, result.data.location);
                    }
                } else {
                    console.log('✅ No critical alerts at this time');
                    this.hideAlert();
                }

                this.lastCheckTime = new Date();

            } catch (error) {
                console.error('❌ Error checking for alerts:', error);
            }
        }

        showAlert(alert, location) {
            // Don't show if it's the same alert
            if (this.currentAlert &&
                this.currentAlert.type === alert.type &&
                this.currentAlert.severity === alert.severity) {
                return;
            }

            this.currentAlert = alert;

            const severityConfig = {
                danger: {
                    bg: 'bg-gradient-to-r from-red-600 to-red-700 dark:from-red-700 dark:to-red-800',
                    icon: '🚨',
                    pulse: true
                },
                warning: {
                    bg: 'bg-gradient-to-r from-orange-500 to-orange-600 dark:from-orange-600 dark:to-orange-700',
                    icon: '⚠️',
                    pulse: true
                },
                info: {
                    bg: 'bg-gradient-to-r from-blue-500 to-blue-600 dark:from-blue-600 dark:to-blue-700',
                    icon: 'ℹ️',
                    pulse: false
                }
            };

            const config = severityConfig[alert.severity] || severityConfig.info;

            // Build safety recommendations
            let recommendations = this.getSafetyRecommendations(alert);

            this.content.innerHTML = `
            <div class="${config.bg} text-white rounded-2xl shadow-2xl overflow-hidden border-2 border-white/20">
                <!-- Header -->
                <div class="px-4 py-3 border-b border-white/20 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="text-2xl ${config.pulse ? 'animate-pulse' : ''}">${config.icon}</span>
                        <span class="font-bold text-sm uppercase tracking-wide">Weather Alert</span>
                    </div>
                    <button onclick="weatherAlertSystem.hideAlert()" 
                            class="text-white/80 hover:text-white transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                
                <!-- Content -->
                <div class="p-4">
                    <div class="flex items-start gap-3 mb-3">
                        <div class="text-3xl flex-shrink-0">${alert.icon}</div>
                        <div class="flex-1 min-w-0">
                            <h4 class="font-bold text-lg mb-1">${alert.title}</h4>
                            <p class="text-white/90 text-sm leading-relaxed mb-2">${alert.message}</p>
                            ${alert.value ? `<div class="text-2xl font-bold">${alert.value}${alert.unit}</div>` : ''}
                        </div>
                    </div>
                    
                    <!-- Safety Recommendations -->
                    ${recommendations ? `
                        <div class="bg-white/10 backdrop-blur-sm rounded-lg p-3 mt-3 space-y-2">
                            <div class="font-semibold text-sm flex items-center gap-2">
                                <span>💡</span>
                                <span>Safety Recommendations:</span>
                            </div>
                            <ul class="text-xs space-y-1 text-white/90">
                                ${recommendations.map(rec => `<li class="flex items-start gap-2"><span>•</span><span>${rec}</span></li>`).join('')}
                            </ul>
                        </div>
                    ` : ''}
                    
                    <!-- Location -->
                    <div class="mt-3 pt-3 border-t border-white/20 flex items-center justify-between text-xs text-white/80">
                        <span class="flex items-center gap-1">
                            <span>📍</span>
                            <span>${location || 'Current Location'}</span>
                        </span>
                        <span>${new Date().toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' })}</span>
                    </div>
                </div>
            </div>
        `;

            // Show the alert with animation
            this.container.style.display = 'block';

            requestAnimationFrame(() => {
                this.card.style.transform = 'translateY(0)';
                this.card.style.opacity = '1';
            });

            this.isVisible = true;

            // Clear any existing auto-hide timeout
            if (this.autoHideTimeout) {
                clearTimeout(this.autoHideTimeout);
            }

            // Auto-hide after 30 seconds for info alerts only
            if (alert.severity === 'info') {
                this.autoHideTimeout = setTimeout(() => {
                    this.hideAlert();
                }, 30000);
            }
        }

        getSafetyRecommendations(alert) {
            const recommendations = {
                // Temperature alerts
                'temperature': {
                    'danger': [
                        'Stay indoors in air-conditioned spaces if possible',
                        'Drink plenty of water to stay hydrated',
                        'Avoid strenuous outdoor activities',
                        'Wear light, loose-fitting clothing',
                        'Check on elderly neighbors and vulnerable individuals'
                    ],
                    'warning': [
                        'Limit outdoor activities during peak heat hours',
                        'Stay hydrated and drink water regularly',
                        'Wear sunscreen and protective clothing',
                        'Take frequent breaks in shaded areas'
                    ]
                },

                // Thermal comfort alerts (heat/cold)
                'thermal_comfort': {
                    'danger': [
                        alert.details?.type === 'heat' ?
                            'Seek immediate shelter in air-conditioned spaces' :
                            'Seek immediate warm shelter indoors',
                        alert.details?.type === 'heat' ?
                            'Heat stroke is imminent - avoid all outdoor activity' :
                            'Frostbite risk - cover all exposed skin immediately',
                        alert.details?.type === 'heat' ?
                            'Drink water frequently, even if not thirsty' :
                            'Wear multiple layers of warm, dry clothing',
                        'Monitor yourself for symptoms of heat/cold illness',
                        'Call emergency services if experiencing severe symptoms'
                    ],
                    'warning': [
                        alert.details?.type === 'heat' ?
                            'Limit time outdoors during hottest hours' :
                            'Dress in layers and cover exposed skin',
                        alert.details?.type === 'heat' ?
                            'Stay hydrated and take frequent breaks' :
                            'Stay dry and avoid prolonged outdoor exposure',
                        'Check on vulnerable family members regularly',
                        'Be aware of early warning signs of heat/cold stress'
                    ]
                },

                // Wind alerts
                'wind': {
                    'danger': [
                        'Stay indoors and away from windows',
                        'Secure or bring in outdoor objects',
                        'Avoid driving if possible',
                        'Be prepared for possible power outages',
                        'Stay away from trees and power lines'
                    ],
                    'warning': [
                        'Exercise caution when outdoors',
                        'Secure loose outdoor items',
                        'Drive carefully and watch for debris',
                        'Be aware of falling branches'
                    ]
                },

                // UV alerts
                'uv': {
                    'danger': [
                        'Avoid sun exposure between 10 AM and 4 PM',
                        'Apply SPF 30+ sunscreen every 2 hours',
                        'Wear protective clothing and wide-brimmed hat',
                        'Seek shade whenever possible',
                        'Wear UV-blocking sunglasses'
                    ],
                    'warning': [
                        'Use sun protection when outdoors',
                        'Apply sunscreen regularly',
                        'Wear a hat and protective clothing',
                        'Limit direct sun exposure during midday'
                    ]
                },

                // Precipitation alerts
                'precipitation': {
                    'warning': [
                        'Carry an umbrella or rain gear',
                        'Watch for flooding in low-lying areas',
                        'Drive carefully on wet roads',
                        'Avoid walking through flooded areas'
                    ]
                },

                // Weather condition alerts
                'weather_condition': {
                    'danger': [
                        'Take shelter immediately',
                        'Stay away from windows',
                        'Avoid using electrical appliances',
                        'Do not go outside until conditions improve',
                        'Monitor weather updates closely'
                    ],
                    'warning': [
                        'Stay alert to changing conditions',
                        'Avoid unnecessary travel',
                        'Keep emergency supplies ready',
                        'Monitor local weather updates'
                    ]
                }
            };

            const typeRecommendations = recommendations[alert.type];
            if (typeRecommendations) {
                return typeRecommendations[alert.severity] || typeRecommendations['warning'];
            }

            // Default recommendations
            return [
                'Monitor weather conditions closely',
                'Follow local weather service guidance',
                'Stay informed of any weather updates',
                'Take appropriate precautions for your safety'
            ];
        }

        hideAlert() {
            if (!this.isVisible) return;

            this.card.style.transform = 'translateY(100px)';
            this.card.style.opacity = '0';

            setTimeout(() => {
                this.container.style.display = 'none';
                this.currentAlert = null;
                this.isVisible = false;
            }, 500);

            if (this.autoHideTimeout) {
                clearTimeout(this.autoHideTimeout);
                this.autoHideTimeout = null;
            }
        }

        destroy() {
            if (this.checkInterval) {
                clearInterval(this.checkInterval);
            }
            if (this.autoHideTimeout) {
                clearTimeout(this.autoHideTimeout);
            }
            this.hideAlert();
        }
    }

    // Initialize the system
    let weatherAlertSystem;

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            weatherAlertSystem = new WeatherAlertNotificationSystem();
        });
    } else {
        weatherAlertSystem = new WeatherAlertNotificationSystem();
    }

    // Clean up on page unload
    window.addEventListener('beforeunload', () => {
        if (weatherAlertSystem) {
            weatherAlertSystem.destroy();
        }
    });
</script>