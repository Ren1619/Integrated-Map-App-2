<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Services\WeatherAlertService;

class WeatherController extends Controller
{
    public function index()
    {
        return view('weather.map');
    }

    /**
     * New precise location search using coordinates only
     * No longer relies on database, uses Open-Meteo geocoding
     */
    public function search(Request $request): JsonResponse
    {
        $request->validate([
            'query' => 'required|string|max:255'
        ]);

        try {
            // Use Open-Meteo's geocoding API for precise location search
            $response = Http::timeout(30)->get('https://geocoding-api.open-meteo.com/v1/search', [
                'name' => $request->query,
                'count' => 10,
                'language' => 'en',
                'format' => 'json'
            ]);

            if (!$response->successful()) {
                // Fallback to Nominatim if Open-Meteo geocoding fails
                return $this->fallbackNominatimSearch($request->query);
            }

            $results = $response->json();

            if (empty($results['results'])) {
                // Try Nominatim as fallback
                return $this->fallbackNominatimSearch($request->query);
            }

            // Return the first (best) result
            $location = $results['results'][0];

            return response()->json([
                'success' => true,
                'data' => [
                    'lat' => (float) $location['latitude'],
                    'lng' => (float) $location['longitude'],
                    'display_name' => $this->formatLocationName($location),
                    'location_details' => [
                        'name' => $location['name'],
                        'country' => $location['country'] ?? '',
                        'admin1' => $location['admin1'] ?? '',
                        'admin2' => $location['admin2'] ?? '',
                        'timezone' => $location['timezone'] ?? '',
                        'elevation' => $location['elevation'] ?? null
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Location search error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error searching for location'
            ], 500);
        }
    }

    /**
     * Get autocomplete suggestions for search
     */
    public function getAutocompleteSuggestions(Request $request): JsonResponse
    {
        $request->validate([
            'query' => 'required|string|min:2|max:255'
        ]);

        try {
            $query = trim($request->input('query')); // Fixed: use input('query') instead of ->query
            $cacheKey = 'autocomplete_' . md5($query);

            $suggestions = Cache::remember($cacheKey, 1800, function () use ($query) {
                $response = Http::timeout(15)->get('https://geocoding-api.open-meteo.com/v1/search', [
                    'name' => $query,
                    'count' => 8,
                    'language' => 'en',
                    'format' => 'json'
                ]);

                if (!$response->successful()) {
                    Log::warning('Open-Meteo geocoding API failed: ' . $response->status());
                    return [];
                }

                $data = $response->json();

                if (empty($data['results'])) {
                    return [];
                }

                return collect($data['results'])->map(function ($result) {
                    return [
                        'id' => $result['id'] ?? uniqid(),
                        'name' => $result['name'] ?? '',
                        'display_name' => $this->formatLocationName($result),
                        'lat' => (float) ($result['latitude'] ?? 0),
                        'lng' => (float) ($result['longitude'] ?? 0),
                        'country' => $result['country'] ?? '',
                        'admin1' => $result['admin1'] ?? '',
                        'population' => $result['population'] ?? null,
                        'timezone' => $result['timezone'] ?? ''
                    ];
                })->toArray();
            });

            return response()->json([
                'success' => true,
                'data' => $suggestions
            ]);

        } catch (\Exception $e) {
            Log::error('Autocomplete error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error fetching suggestions',
                'error' => $e->getMessage() // Add this for debugging
            ], 500);
        }
    }

    /**
     * Get location name from coordinates using reverse geocoding
     */
    public function getLocationName(Request $request): JsonResponse
    {
        $request->validate([
            'lat' => 'required|numeric|between:-90,90',
            'lng' => 'required|numeric|between:-180,180'
        ]);

        try {
            $lat = $request->lat;
            $lng = $request->lng;

            Log::info("Reverse geocoding request for: {$lat}, {$lng}");

            // Add caching to reduce API calls
            $cacheKey = "reverse_geo_" . md5("{$lat}_{$lng}");

            $locationData = Cache::remember($cacheKey, 3600, function () use ($lat, $lng) {

                sleep(1); 

                $response = Http::timeout(10)
                    ->withHeaders([
                        'User-Agent' => 'WeatherMapApp/1.0 (Laravel; Weather Tracking)'
                    ])
                    ->get('https://nominatim.openstreetmap.org/reverse', [
                        'format' => 'json',
                        'lat' => $lat,
                        'lon' => $lng,
                        'addressdetails' => 1,
                        'zoom' => 18,
                        'accept-language' => 'en'
                    ]);

                if (!$response->successful()) {
                    Log::warning('Nominatim API returned status: ' . $response->status());
                    return null;
                }

                return $response->json();
            });

            if ($locationData && isset($locationData['display_name'])) {
                $address = $locationData['address'] ?? [];

                // Build a clean, hierarchical location name
                $locationParts = [];

                // Priority order for location components
                $priorityKeys = [
                    'house_number',
                    'road',
                    'neighbourhood',
                    'suburb',
                    'village',
                    'town',
                    'city',
                    'municipality',
                    'county',
                    'state',
                    'country'
                ];

                foreach ($priorityKeys as $key) {
                    if (!empty($address[$key]) && !in_array($address[$key], $locationParts)) {
                        $locationParts[] = $address[$key];
                        if (count($locationParts) >= 3)
                            break; // Limit to 3 components for clarity
                    }
                }

                $locationName = !empty($locationParts)
                    ? implode(', ', $locationParts)
                    : $locationData['display_name'];

                Log::info("Location name resolved: {$locationName}");

                return response()->json([
                    'success' => true,
                    'data' => [
                        'location_name' => $locationName,
                        'full_address' => $locationData['display_name'],
                        'display_name' => $locationData['display_name'],
                        'coordinates' => [
                            'lat' => (float) $lat,
                            'lng' => (float) $lng
                        ],
                        'address_components' => [
                            'house_number' => $address['house_number'] ?? null,
                            'road' => $address['road'] ?? null,
                            'neighbourhood' => $address['neighbourhood'] ?? null,
                            'barangay' => $address['neighbourhood'] ?? $address['suburb'] ?? null,
                            'city' => $address['city'] ?? $address['town'] ?? $address['municipality'] ?? null,
                            'state' => $address['state'] ?? null,
                            'country' => $address['country'] ?? null,
                            'postcode' => $address['postcode'] ?? null
                        ]
                    ]
                ]);
            }

            // Fallback: return coordinates as location name
            Log::warning('No location data found, using coordinates as fallback');

            return response()->json([
                'success' => true,
                'data' => [
                    'location_name' => "Location: {$lat}, {$lng}",
                    'full_address' => "Coordinates: {$lat}, {$lng}",
                    'display_name' => "Lat: {$lat}, Lng: {$lng}",
                    'coordinates' => [
                        'lat' => (float) $lat,
                        'lng' => (float) $lng
                    ],
                    'address_components' => null
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Reverse geocoding error: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());

            // Return coordinates as fallback instead of error
            return response()->json([
                'success' => true,
                'data' => [
                    'location_name' => "Location: {$request->lat}, {$request->lng}",
                    'full_address' => "Coordinates: {$request->lat}, {$request->lng}",
                    'display_name' => "Lat: {$request->lat}, Lng: {$request->lng}",
                    'coordinates' => [
                        'lat' => (float) $request->lat,
                        'lng' => (float) $request->lng
                    ],
                    'address_components' => null
                ]
            ]);
        }
    }

    public function getLocationFromIP(Request $request): JsonResponse
    {
        try {
            // Get the actual client IP
            $ip = $request->ip();

            Log::info("Attempting IP geolocation for IP: {$ip}");

            // For localhost/local IPs, get the public IP
            if (in_array($ip, ['127.0.0.1', '::1', 'localhost']) || str_starts_with($ip, '192.168.') || str_starts_with($ip, '10.')) {
                Log::info("Local IP detected, fetching public IP");

                try {
                    $publicIpResponse = Http::timeout(5)->get('https://api.ipify.org?format=json');
                    if ($publicIpResponse->successful()) {
                        $ip = $publicIpResponse->json()['ip'] ?? null;
                        Log::info("Public IP resolved: {$ip}");
                    }
                } catch (\Exception $e) {
                    Log::warning("Failed to get public IP: " . $e->getMessage());
                }
            }

            if (!$ip) {
                throw new \Exception('Could not determine IP address');
            }

            // Try ip-api.com first (free, no key required)
            Log::info("Fetching location from ip-api.com for IP: {$ip}");

            $response = Http::timeout(10)->get("http://ip-api.com/json/{$ip}?fields=status,message,country,regionName,city,lat,lon,timezone");

            if (!$response->successful()) {
                Log::warning("ip-api.com request failed with status: " . $response->status());
                throw new \Exception('Geolocation service request failed');
            }

            $data = $response->json();

            Log::info("ip-api.com response: " . json_encode($data));

            if (!isset($data['status']) || $data['status'] !== 'success') {
                Log::warning("ip-api.com returned non-success status");
                throw new \Exception($data['message'] ?? 'Geolocation failed');
            }

            $locationName = collect([
                $data['city'] ?? null,
                $data['regionName'] ?? null,
                $data['country'] ?? null
            ])->filter()->join(', ');

            Log::info("Location resolved: {$locationName}");

            return response()->json([
                'success' => true,
                'data' => [
                    'lat' => (float) $data['lat'],
                    'lng' => (float) $data['lon'],
                    'location_name' => $locationName,
                    'city' => $data['city'] ?? '',
                    'region' => $data['regionName'] ?? '',
                    'country' => $data['country'] ?? '',
                    'timezone' => $data['timezone'] ?? '',
                    'ip' => $ip,
                    'method' => 'ip_geolocation'
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('IP Geolocation error: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());

            // Return default location as fallback
            return response()->json([
                'success' => true,
                'data' => [
                    'lat' => 7.1907,
                    'lng' => 125.4553,
                    'location_name' => 'Davao City, Davao Region, Philippines',
                    'city' => 'Davao City',
                    'region' => 'Davao Region',
                    'country' => 'Philippines',
                    'method' => 'default_fallback',
                    'error' => $e->getMessage()
                ]
            ]);
        }
    }

    /**
     * Fallback to Nominatim search
     */
    private function fallbackNominatimSearch(string $query): JsonResponse
    {
        try {
            $response = Http::timeout(30)->get('    ', [
                'format' => 'json',
                'q' => $query,
                'limit' => 1,
                'addressdetails' => 1
            ]);

            $results = $response->json();

            if (empty($results)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Location not found'
                ], 404);
            }

            $location = $results[0];

            return response()->json([
                'success' => true,
                'data' => [
                    'lat' => (float) $location['lat'],
                    'lng' => (float) $location['lon'],
                    'display_name' => $location['display_name'],
                    'location_details' => [
                        'name' => $location['name'] ?? $location['display_name'],
                        'country' => $location['address']['country'] ?? '',
                        'admin1' => $location['address']['state'] ?? '',
                        'admin2' => $location['address']['city'] ?? '',
                        'timezone' => null,
                        'elevation' => null
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Nominatim fallback error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error searching for location'
            ], 500);
        }
    }

    /**
     * Enhanced weather data retrieval with extended parameters
     */
    public function getWeatherData(Request $request): JsonResponse
    {
        $request->validate([
            'lat' => 'required|numeric|between:-90,90',
            'lng' => 'required|numeric|between:-180,180'
        ]);

        $lat = $request->lat;
        $lng = $request->lng;

        try {
            $cacheKey = "enhanced_weather_" . md5("{$lat}_{$lng}");

            $weatherData = Cache::remember($cacheKey, 600, function () use ($lat, $lng) {
                return $this->fetchEnhancedWeatherData($lat, $lng);
            });

            if (!$weatherData) {
                throw new \Exception('Failed to fetch weather data');
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'weather' => $weatherData,
                    'coordinates' => ['lat' => $lat, 'lng' => $lng]
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Weather data error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error fetching weather data'
            ], 500);
        }
    }

    /**
     * Fetch enhanced weather data with all requested parameters
     */
    private function fetchEnhancedWeatherData(float $lat, float $lng): ?array
    {
        try {
            $response = Http::timeout(30)->get('https://api.open-meteo.com/v1/forecast', [
                'latitude' => $lat,
                'longitude' => $lng,


                'current' => implode(',', [
                    // Temperature data at different levels
                    'temperature_2m',
                    'temperature_80m',
                    'temperature_120m',
                    'temperature_180m',
                    'apparent_temperature',

                    // Wind data at different levels
                    'wind_speed_10m',
                    'wind_direction_10m',
                    'wind_gusts_10m',
                    'wind_speed_80m',
                    'wind_direction_80m',
                    'wind_speed_120m',
                    'wind_direction_120m',
                    'wind_speed_180m',
                    'wind_direction_180m',

                    // Other atmospheric data
                    'relative_humidity_2m',
                    'weather_code',
                    'surface_pressure',
                    'precipitation',
                    'cloud_cover',
                    'visibility',
                    'uv_index',
                    'is_day'
                ]),

                // Enhanced hourly data
                'hourly' => implode(',', [
                    // Temperature data
                    'temperature_2m',
                    'temperature_80m',
                    'temperature_120m',
                    'temperature_180m',
                    'apparent_temperature',

                    // Wind data
                    'wind_speed_10m',
                    'wind_direction_10m',
                    'wind_gusts_10m',
                    'wind_speed_80m',
                    'wind_direction_80m',
                    'wind_speed_120m',
                    'wind_direction_120m',
                    'wind_speed_180m',
                    'wind_direction_180m',

                    // Atmospheric data
                    'relative_humidity_2m',
                    'weather_code',
                    'surface_pressure',
                    'precipitation',
                    'precipitation_probability',
                    'cloud_cover',
                    'visibility',
                    'uv_index',

                    // Soil data
                    'soil_temperature_0cm',
                    'soil_temperature_6cm',
                    'soil_temperature_18cm',
                    'soil_temperature_54cm',
                    'soil_moisture_0_1cm',
                    'soil_moisture_1_3cm',
                    'soil_moisture_3_9cm',
                    'soil_moisture_9_27cm',
                    'soil_moisture_27_81cm'
                ]),

                // Enhanced daily data
                'daily' => implode(',', [
                    'weather_code',
                    'temperature_2m_max',
                    'temperature_2m_min',
                    'apparent_temperature_max',
                    'apparent_temperature_min',
                    'precipitation_sum',
                    'precipitation_hours',
                    'precipitation_probability_max',
                    'wind_speed_10m_max',
                    'wind_gusts_10m_max',
                    'wind_direction_10m_dominant',
                    'uv_index_max',
                    'sunrise',
                    'sunset'
                ]),

                'timezone' => 'auto',
                'forecast_days' => 7
            ]);

            if (!$response->successful()) {
                Log::warning("Weather API returned non-successful response: " . $response->status());
                return null;
            }

            return $response->json();

        } catch (\Exception $e) {
            Log::error("Enhanced Weather API error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Format location name from Open-Meteo geocoding result
     */
    private function formatLocationName(array $location): string
    {
        $parts = [];

        if (!empty($location['name'])) {
            $parts[] = $location['name'];
        }

        if (!empty($location['admin1']) && $location['admin1'] !== $location['name']) {
            $parts[] = $location['admin1'];
        }

        if (!empty($location['country']) && $location['country'] !== $location['name']) {
            $parts[] = $location['country'];
        }

        return implode(', ', $parts);
    }

    /**
     * Debug method for testing enhanced weather data
     */
    public function debugEnhancedWeatherApi(Request $request)
    {
        try {
            $lat = $request->get('lat', 14.5995);
            $lng = $request->get('lng', 120.9842);

            $weatherData = $this->fetchEnhancedWeatherData($lat, $lng);

            return response()->json([
                'success' => true,
                'coordinates' => ['lat' => $lat, 'lng' => $lng],
                'data_structure' => [
                    'current_parameters' => array_keys($weatherData['current'] ?? []),
                    'hourly_parameters' => array_keys($weatherData['hourly'] ?? []),
                    'daily_parameters' => array_keys($weatherData['daily'] ?? []),
                ],
                'sample_data' => [
                    'current' => $weatherData['current'] ?? null,
                    'first_hour' => isset($weatherData['hourly']) ? array_map(function ($values) {
                        return is_array($values) ? $values[0] ?? null : null;
                    }, $weatherData['hourly']) : null,
                    'first_day' => isset($weatherData['daily']) ? array_map(function ($values) {
                        return is_array($values) ? $values[0] ?? null : null;
                    }, $weatherData['daily']) : null,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'type' => get_class($e)
            ], 500);
        }
    }

    /**
     * Get weather alerts for current location
     */
    public function getWeatherAlerts(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'lat' => 'required|numeric|between:-90,90',
                'lng' => 'required|numeric|between:-180,180'
            ]);

            $lat = $request->lat;
            $lng = $request->lng;

            Log::info("Fetching weather alerts for coordinates: {$lat}, {$lng}");

            // Fetch weather data using existing method
            $weatherData = $this->fetchEnhancedWeatherData($lat, $lng);

            if (!$weatherData) {
                Log::error('Weather data is null');
                throw new \Exception('Failed to fetch weather data from API');
            }

            // Check if WeatherAlertService exists
            if (!class_exists('App\Services\WeatherAlertService')) {
                Log::error('WeatherAlertService class not found');
                throw new \Exception('Weather Alert Service not available');
            }

            // Analyze alerts
            $alertService = new WeatherAlertService();
            $alerts = $alertService->analyzeWeatherData($weatherData);
            $alerts = $alertService->sortAlertsBySeverity($alerts);
            $stats = $alertService->getAlertStatistics($alerts);

            // Get location name with better error handling
            $locationName = "Current Location";
            try {
                $geoResponse = Http::timeout(10)
                    ->withHeaders([
                        'User-Agent' => 'WeatherMapApp/1.0'
                    ])
                    ->get('https://nominatim.openstreetmap.org/reverse', [
                        'format' => 'json',
                        'lat' => $lat,
                        'lon' => $lng,
                        'zoom' => 10,
                        'addressdetails' => 1
                    ]);

                if ($geoResponse->successful()) {
                    $result = $geoResponse->json();
                    if (isset($result['address'])) {
                        $address = $result['address'];
                        $locationParts = [];

                        $keys = ['city', 'town', 'village', 'municipality', 'county', 'state'];
                        foreach ($keys as $key) {
                            if (!empty($address[$key])) {
                                $locationParts[] = $address[$key];
                                if (count($locationParts) >= 2)
                                    break;
                            }
                        }

                        if (!empty($locationParts)) {
                            $locationName = implode(', ', $locationParts);
                        }
                    }
                }
            } catch (\Exception $e) {
                Log::warning('Failed to fetch location name: ' . $e->getMessage());
                // Continue with default location name
            }

            Log::info("Successfully generated {$stats['total']} alerts");

            return response()->json([
                'success' => true,
                'data' => [
                    'alerts' => $alerts,
                    'statistics' => $stats,
                    'location' => $locationName,
                    'coordinates' => [
                        'lat' => (float) $lat,
                        'lng' => (float) $lng
                    ],
                    'timestamp' => now()->toIso8601String()
                ]
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation error: ' . json_encode($e->errors()));
            return response()->json([
                'success' => false,
                'message' => 'Invalid coordinates provided',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            Log::error('Weather alerts error: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());

            return response()->json([
                'success' => false,
                'message' => 'Error fetching weather alerts: ' . $e->getMessage(),
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }
}