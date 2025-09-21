<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\Country;
use App\Models\State;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class WeatherController extends Controller
{
    // Directional constants for finding nearest cities
    private const DIRECTIONS = [
        'north' => ['min_bearing' => 337.5, 'max_bearing' => 22.5, 'name' => 'North'],
        'northeast' => ['min_bearing' => 22.5, 'max_bearing' => 67.5, 'name' => 'Northeast'],
        'east' => ['min_bearing' => 67.5, 'max_bearing' => 112.5, 'name' => 'East'],
        'southeast' => ['min_bearing' => 112.5, 'max_bearing' => 157.5, 'name' => 'Southeast'],
        'south' => ['min_bearing' => 157.5, 'max_bearing' => 202.5, 'name' => 'South'],
        'southwest' => ['min_bearing' => 202.5, 'max_bearing' => 247.5, 'name' => 'Southwest'],
        'west' => ['min_bearing' => 247.5, 'max_bearing' => 292.5, 'name' => 'West'],
        'northwest' => ['min_bearing' => 292.5, 'max_bearing' => 337.5, 'name' => 'Northwest'],
    ];

    // Maximum search radius in kilometers
    private const MAX_SEARCH_RADIUS = 200;

    public function index()
    {
        return view('weather.map');
    }

    public function search(Request $request): JsonResponse
    {
        $request->validate([
            'query' => 'required|string|max:255'
        ]);

        try {
            // First, try to find the location in our database
            $databaseResult = $this->searchInDatabase($request->query);

            if ($databaseResult) {
                return response()->json([
                    'success' => true,
                    'data' => [
                        'lat' => $databaseResult['latitude'],
                        'lng' => $databaseResult['longitude'],
                        'display_name' => $databaseResult['display_name'],
                        'from_database' => true,
                        'city_data' => $databaseResult
                    ]
                ]);
            }

            // Fallback to external API
            $response = Http::timeout(30)->get('https://nominatim.openstreetmap.org/search', [
                'format' => 'json',
                'q' => $request->query,
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
                    'from_database' => false
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

    public function getWeatherData(Request $request): JsonResponse
    {
        $request->validate([
            'lat' => 'required|numeric|between:-90,90',
            'lng' => 'required|numeric|between:-180,180',
            'from_database' => 'sometimes|boolean',
            'city_data' => 'sometimes|array'
        ]);

        $lat = $request->lat;
        $lng = $request->lng;
        $fromDatabase = $request->get('from_database', false);
        $cityData = $request->get('city_data');

        try {
            if ($fromDatabase && $cityData) {
                // Use exact city data from database
                $targetCity = $cityData;
            } else {
                // Find nearest city for clicked location
                $targetCity = $this->findNearestCity($lat, $lng);
                if (!$targetCity) {
                    return response()->json([
                        'success' => false,
                        'message' => 'No nearby city found'
                    ], 404);
                }
            }

            $cacheKey = "weather_single_" . $targetCity['id'];

            $weatherData = Cache::remember($cacheKey, 600, function () use ($targetCity) {
                return $this->fetchWeatherForLocation($targetCity['latitude'], $targetCity['longitude']);
            });

            if (!$weatherData) {
                throw new \Exception('Failed to fetch weather data');
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'weather' => $weatherData,
                    'location' => $targetCity['name'] . ', ' . ($targetCity['state_name'] ?? '') . ', ' . ($targetCity['country_name'] ?? ''),
                    'city_info' => $targetCity
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

    public function getNearbyDirectionalCities(Request $request): JsonResponse
    {
        $request->validate([
            'lat' => 'required|numeric|between:-90,90',
            'lng' => 'required|numeric|between:-180,180'
        ]);

        $lat = $request->lat;
        $lng = $request->lng;

        try {
            $nearbyCities = $this->findNearestCitiesInAllDirections($lat, $lng);

            // Fetch weather data for each city
            $citiesWithWeather = [];

            foreach ($nearbyCities as $direction => $city) {
                if ($city) {
                    $cacheKey = "weather_directional_{$city['id']}_temp";

                    $temperature = Cache::remember($cacheKey, 300, function () use ($city) {
                        $weatherData = $this->fetchWeatherForLocation($city['latitude'], $city['longitude']);
                        return $weatherData ? ($weatherData['current']['temperature_2m'] ?? null) : null;
                    });

                    $citiesWithWeather[] = [
                        'direction' => self::DIRECTIONS[$direction]['name'],
                        'city' => $city['name'],
                        'state' => $city['state_name'] ?? '',
                        'country' => $city['country_name'] ?? '',
                        'lat' => $city['latitude'],
                        'lng' => $city['longitude'],
                        'distance' => round($city['distance'], 1),
                        'temperature' => $temperature ? round($temperature) : null,
                        'bearing' => round($city['bearing'], 1)
                    ];
                }
            }

            return response()->json([
                'success' => true,
                'data' => $citiesWithWeather,
                'count' => count($citiesWithWeather)
            ]);

        } catch (\Exception $e) {
            Log::error('Nearby directional cities error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error fetching nearby cities'
            ], 500);
        }
    }

    /**
     * Search for locations in our database first
     */
    /**
     * Search for locations in our database first (MySQL compatible)
     */
    private function searchInDatabase(string $query): ?array
    {
        $query = trim($query);

        // Search cities first
        $city = City::with(['state', 'country'])
            ->where('name', 'LIKE', "%{$query}%")  // Changed from ILIKE to LIKE
            ->active()
            ->first();

        if ($city) {
            return [
                'id' => $city->id,
                'name' => $city->name,
                'state_name' => $city->state?->name,
                'country_name' => $city->country?->name,
                'latitude' => (float) $city->latitude,
                'longitude' => (float) $city->longitude,
                'display_name' => $city->name .
                    ($city->state ? ', ' . $city->state->name : '') .
                    ($city->country ? ', ' . $city->country->name : ''),
                'type' => 'city'
            ];
        }

        // Search states if no city found
        $state = State::with(['country'])
            ->where('name', 'LIKE', "%{$query}%")  // Changed from ILIKE to LIKE
            ->active()
            ->first();

        if ($state) {
            return [
                'id' => 'state_' . $state->id,
                'name' => $state->name,
                'state_name' => $state->name,
                'country_name' => $state->country?->name,
                'latitude' => (float) $state->latitude,
                'longitude' => (float) $state->longitude,
                'display_name' => $state->name .
                    ($state->country ? ', ' . $state->country->name : ''),
                'type' => 'state'
            ];
        }

        // Search countries if no state found
        $country = Country::where('name', 'LIKE', "%{$query}%")  // Changed from ILIKE to LIKE
            ->orWhere('iso2', 'LIKE', $query)                     // Changed from ILIKE to LIKE
            ->orWhere('iso3', 'LIKE', $query)                     // Changed from ILIKE to LIKE
            ->active()
            ->first();

        if ($country) {
            return [
                'id' => 'country_' . $country->id,
                'name' => $country->name,
                'state_name' => null,
                'country_name' => $country->name,
                'latitude' => (float) $country->latitude,
                'longitude' => (float) $country->longitude,
                'display_name' => $country->name,
                'type' => 'country'
            ];
        }

        return null;
    }
    /**
     * Find nearest city to given coordinates
     */
    private function findNearestCity(float $lat, float $lng): ?array
    {
        $nearestCity = City::select([
            'cities.*',
            'states.name as state_name',
            'countries.name as country_name',
            DB::raw("
                    (6371 * acos(cos(radians({$lat})) 
                    * cos(radians(latitude)) 
                    * cos(radians(longitude) - radians({$lng})) 
                    + sin(radians({$lat})) 
                    * sin(radians(latitude)))) AS distance
                ")
        ])
            ->leftJoin('states', 'cities.state_id', '=', 'states.id')
            ->leftJoin('countries', 'cities.country_id', '=', 'countries.id')
            ->active()
            ->having('distance', '<', self::MAX_SEARCH_RADIUS)
            ->orderBy('distance')
            ->first();

        if (!$nearestCity) {
            return null;
        }

        return [
            'id' => $nearestCity->id,
            'name' => $nearestCity->name,
            'state_name' => $nearestCity->state_name,
            'country_name' => $nearestCity->country_name,
            'latitude' => (float) $nearestCity->latitude,
            'longitude' => (float) $nearestCity->longitude,
            'distance' => (float) $nearestCity->distance,
            'type' => 'city'
        ];
    }

    /**
     * Find nearest cities in all 8 directions
     */
    private function findNearestCitiesInAllDirections(float $lat, float $lng): array
    {
        $result = [];

        foreach (self::DIRECTIONS as $direction => $config) {
            $city = $this->findNearestCityInDirection($lat, $lng, $direction, $config);
            $result[$direction] = $city;
        }

        return $result;
    }

    /**
     * Find nearest city in a specific direction
     */
    private function findNearestCityInDirection(float $lat, float $lng, string $direction, array $config): ?array
    {
        $minBearing = $config['min_bearing'];
        $maxBearing = $config['max_bearing'];

        // Handle north direction which crosses 0 degrees
        if ($direction === 'north') {
            $nearestCity = City::select([
                'cities.*',
                'states.name as state_name',
                'countries.name as country_name',
                DB::raw("
                        (6371 * acos(cos(radians({$lat})) 
                        * cos(radians(latitude)) 
                        * cos(radians(longitude) - radians({$lng})) 
                        + sin(radians({$lat})) 
                        * sin(radians(latitude)))) AS distance
                    "),
                DB::raw("
                        CASE 
                            WHEN degrees(atan2(
                                sin(radians(longitude - ({$lng}))) * cos(radians(latitude)),
                                cos(radians({$lat})) * sin(radians(latitude)) - 
                                sin(radians({$lat})) * cos(radians(latitude)) * cos(radians(longitude - ({$lng})))
                            )) < 0 
                            THEN degrees(atan2(
                                sin(radians(longitude - ({$lng}))) * cos(radians(latitude)),
                                cos(radians({$lat})) * sin(radians(latitude)) - 
                                sin(radians({$lat})) * cos(radians(latitude)) * cos(radians(longitude - ({$lng})))
                            )) + 360
                            ELSE degrees(atan2(
                                sin(radians(longitude - ({$lng}))) * cos(radians(latitude)),
                                cos(radians({$lat})) * sin(radians(latitude)) - 
                                sin(radians({$lat})) * cos(radians(latitude)) * cos(radians(longitude - ({$lng})))
                            ))
                        END AS bearing
                    ")
            ])
                ->leftJoin('states', 'cities.state_id', '=', 'states.id')
                ->leftJoin('countries', 'cities.country_id', '=', 'countries.id')
                ->active()
                ->having('distance', '>', 0.1) // Exclude the same location
                ->having('distance', '<', self::MAX_SEARCH_RADIUS)
                ->havingRaw("
                    (bearing >= {$minBearing} OR bearing <= {$maxBearing})
                ")
                ->orderBy('distance')
                ->first();
        } else {
            $nearestCity = City::select([
                'cities.*',
                'states.name as state_name',
                'countries.name as country_name',
                DB::raw("
                        (6371 * acos(cos(radians({$lat})) 
                        * cos(radians(latitude)) 
                        * cos(radians(longitude) - radians({$lng})) 
                        + sin(radians({$lat})) 
                        * sin(radians(latitude)))) AS distance
                    "),
                DB::raw("
                        CASE 
                            WHEN degrees(atan2(
                                sin(radians(longitude - ({$lng}))) * cos(radians(latitude)),
                                cos(radians({$lat})) * sin(radians(latitude)) - 
                                sin(radians({$lat})) * cos(radians(latitude)) * cos(radians(longitude - ({$lng})))
                            )) < 0 
                            THEN degrees(atan2(
                                sin(radians(longitude - ({$lng}))) * cos(radians(latitude)),
                                cos(radians({$lat})) * sin(radians(latitude)) - 
                                sin(radians({$lat})) * cos(radians(latitude)) * cos(radians(longitude - ({$lng})))
                            )) + 360
                            ELSE degrees(atan2(
                                sin(radians(longitude - ({$lng}))) * cos(radians(latitude)),
                                cos(radians({$lat})) * sin(radians(latitude)) - 
                                sin(radians({$lat})) * cos(radians(latitude)) * cos(radians(longitude - ({$lng})))
                            ))
                        END AS bearing
                    ")
            ])
                ->leftJoin('states', 'cities.state_id', '=', 'states.id')
                ->leftJoin('countries', 'cities.country_id', '=', 'countries.id')
                ->active()
                ->having('distance', '>', 0.1) // Exclude the same location
                ->having('distance', '<', self::MAX_SEARCH_RADIUS)
                ->havingRaw("bearing >= {$minBearing} AND bearing < {$maxBearing}")
                ->orderBy('distance')
                ->first();
        }

        if (!$nearestCity) {
            return null;
        }

        return [
            'id' => $nearestCity->id,
            'name' => $nearestCity->name,
            'state_name' => $nearestCity->state_name,
            'country_name' => $nearestCity->country_name,
            'latitude' => (float) $nearestCity->latitude,
            'longitude' => (float) $nearestCity->longitude,
            'distance' => (float) $nearestCity->distance,
            'bearing' => (float) $nearestCity->bearing,
            'type' => 'city'
        ];
    }

    /**
     * Fetch weather data for specific coordinates
     */
    private function fetchWeatherForLocation(float $lat, float $lng): ?array
    {
        try {
            $response = Http::timeout(30)->get('https://api.open-meteo.com/v1/forecast', [
                'latitude' => $lat,
                'longitude' => $lng,
                'current' => 'temperature_2m,relative_humidity_2m,apparent_temperature,weather_code,surface_pressure,wind_speed_10m,wind_direction_10m,wind_gusts_10m',
                'hourly' => 'temperature_2m,relative_humidity_2m,weather_code,surface_pressure,wind_speed_10m,wind_direction_10m,precipitation,precipitation_probability',
                'daily' => 'weather_code,temperature_2m_max,temperature_2m_min,precipitation_sum,precipitation_probability_max,wind_speed_10m_max,wind_gusts_10m_max',
                'timezone' => 'auto',
                'forecast_days' => 7
            ]);

            if (!$response->successful()) {
                Log::warning("Weather API returned non-successful response: " . $response->status());
                return null;
            }

            return $response->json();

        } catch (\Exception $e) {
            Log::error("Weather API error: " . $e->getMessage());
            return null;
        }
    }

    // Legacy methods for backward compatibility
    public function getAllCitiesWeatherData(): JsonResponse
    {
        // This method can remain as is or be updated to use database cities
        // For now, keeping the existing implementation
        return $this->originalGetAllCitiesWeatherData();
    }

    private function originalGetAllCitiesWeatherData(): JsonResponse
    {
        $cacheKey = 'all_cities_weather_data';

        try {
            $allWeatherData = Cache::remember($cacheKey, 3600, function () {
                // Get major cities from database instead of hardcoded array
                $majorCities = City::with(['state', 'country'])
                    ->active()
                    ->whereNotNull('latitude')
                    ->whereNotNull('longitude')
                    ->limit(25) // Limit to prevent API overload
                    ->get()
                    ->map(function ($city) {
                        return [
                            'name' => $city->name . ($city->state ? ', ' . $city->state->name : '') . ($city->country ? ', ' . $city->country->name : ''),
                            'lat' => (float) $city->latitude,
                            'lng' => (float) $city->longitude
                        ];
                    })
                    ->toArray();

                $weatherData = [];
                $batches = array_chunk($majorCities, 10);

                foreach ($batches as $batchIndex => $batch) {
                    foreach ($batch as $city) {
                        try {
                            $response = Http::timeout(15)->get('https://api.open-meteo.com/v1/forecast', [
                                'latitude' => $city['lat'],
                                'longitude' => $city['lng'],
                                'current' => 'temperature_2m,relative_humidity_2m,apparent_temperature,weather_code,surface_pressure,wind_speed_10m,wind_direction_10m,wind_gusts_10m,precipitation',
                                'timezone' => 'auto',
                                'forecast_days' => 1
                            ]);

                            if ($response->successful()) {
                                $data = $response->json();
                                $weatherData[] = [
                                    'city' => $city['name'],
                                    'lat' => $city['lat'],
                                    'lng' => $city['lng'],
                                    'weather' => $data['current']
                                ];
                            }

                            usleep(200000); // 0.2 seconds delay

                        } catch (\Exception $e) {
                            Log::warning("Failed to fetch weather for {$city['name']}: " . $e->getMessage());
                            continue;
                        }
                    }

                    if ($batchIndex < count($batches) - 1) {
                        sleep(2);
                    }
                }

                return $weatherData;
            });

            return response()->json([
                'success' => true,
                'data' => $allWeatherData,
                'count' => count($allWeatherData),
                'cached_at' => Cache::get($cacheKey . '_timestamp', now()),
                'expires_at' => now()->addHour()
            ]);

        } catch (\Exception $e) {
            Log::error('Weather data fetch error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error fetching weather data for cities'
            ], 500);
        }
    }

    // Keep other existing methods for backward compatibility
    public function getTemperatureData(Request $request): JsonResponse
    {
        $allData = $this->getAllCitiesWeatherData();

        if (!$allData->getData()->success) {
            return $allData;
        }

        $temperatureData = collect($allData->getData()->data)->map(function ($city) {
            return [
                'city' => $city->city,
                'lat' => $city->lat,
                'lng' => $city->lng,
                'temperature' => $city->weather->temperature_2m ?? null,
                'apparent_temperature' => $city->weather->apparent_temperature ?? null
            ];
        })->filter(function ($city) {
            return $city['temperature'] !== null;
        });

        return response()->json([
            'success' => true,
            'data' => $temperatureData->values()->all()
        ]);
    }

    public function getWindData(Request $request): JsonResponse
    {
        $allData = $this->getAllCitiesWeatherData();

        if (!$allData->getData()->success) {
            return $allData;
        }

        $windData = collect($allData->getData()->data)->map(function ($city) {
            return [
                'city' => $city->city,
                'lat' => $city->lat,
                'lng' => $city->lng,
                'wind_speed' => $city->weather->wind_speed_10m ?? null,
                'wind_direction' => $city->weather->wind_direction_10m ?? null,
                'wind_gusts' => $city->weather->wind_gusts_10m ?? null
            ];
        })->filter(function ($city) {
            return $city['wind_speed'] !== null;
        });

        return response()->json([
            'success' => true,
            'data' => $windData->values()->all()
        ]);
    }

    public function getRadarData(Request $request): JsonResponse
    {
        $allData = $this->getAllCitiesWeatherData();

        if (!$allData->getData()->success) {
            return $allData;
        }

        $precipitationData = collect($allData->getData()->data)->map(function ($city) {
            return [
                'city' => $city->city,
                'lat' => $city->lat,
                'lng' => $city->lng,
                'precipitation' => $city->weather->precipitation ?? 0
            ];
        })->filter(function ($city) {
            return $city['precipitation'] > 0;
        });

        return response()->json([
            'success' => true,
            'data' => $precipitationData->values()->all()
        ]);
    }

    public function updateWeatherDataCache()
    {
        try {
            Cache::forget('all_cities_weather_data');
            $this->getAllCitiesWeatherData();

            Log::info('Weather data cache updated successfully');

            return response()->json([
                'success' => true,
                'message' => 'Weather data cache updated'
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to update weather cache: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to update weather cache'
            ], 500);
        }
    }

    // Utility method for distance calculation (keeping for compatibility)
    private function calculateDistance($lat1, $lng1, $lat2, $lng2)
    {
        $R = 6371; // Earth's radius in kilometers
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLng / 2) * sin($dLng / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $R * $c;
    }

    public function debugDatabase(Request $request)
    {
        try {
            // Check if cities table has data
            $cityCount = City::active()->count();
            $citiesWithCoords = City::active()
                ->whereNotNull('latitude')
                ->whereNotNull('longitude')
                ->count();

            // Check a sample city
            $sampleCity = City::active()
                ->whereNotNull('latitude')
                ->whereNotNull('longitude')
                ->first();

            return response()->json([
                'total_active_cities' => $cityCount,
                'cities_with_coordinates' => $citiesWithCoords,
                'sample_city' => $sampleCity ? [
                    'name' => $sampleCity->name,
                    'lat' => $sampleCity->latitude,
                    'lng' => $sampleCity->longitude,
                    'state' => $sampleCity->state?->name,
                    'country' => $sampleCity->country?->name
                ] : null,
                'database_connection' => 'OK'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ], 500);
        }
    }

    public function debugWeatherApi(Request $request)
    {
        try {
            // Test weather API with fixed coordinates (Manila)
            $lat = 14.5995;
            $lng = 120.9842;

            $response = Http::timeout(30)->get('https://api.open-meteo.com/v1/forecast', [
                'latitude' => $lat,
                'longitude' => $lng,
                'current' => 'temperature_2m,relative_humidity_2m,weather_code',
                'timezone' => 'auto',
                'forecast_days' => 1
            ]);

            return response()->json([
                'api_status_code' => $response->status(),
                'api_successful' => $response->successful(),
                'api_response' => $response->json(),
                'request_url' => $response->effectiveUri()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'type' => get_class($e)
            ], 500);
        }
    }

    public function debugNearestCity(Request $request)
    {
        $request->validate([
            'lat' => 'required|numeric',
            'lng' => 'required|numeric'
        ]);

        try {
            $lat = $request->lat;
            $lng = $request->lng;

            // Debug the nearest city query
            $nearestCity = City::select([
                'cities.*',
                'states.name as state_name',
                'countries.name as country_name',
                DB::raw("
                    (6371 * acos(cos(radians({$lat})) 
                    * cos(radians(latitude)) 
                    * cos(radians(longitude) - radians({$lng})) 
                    + sin(radians({$lat})) 
                    * sin(radians(latitude)))) AS distance
                ")
            ])
                ->leftJoin('states', 'cities.state_id', '=', 'states.id')
                ->leftJoin('countries', 'cities.country_id', '=', 'countries.id')
                ->active()
                ->having('distance', '<', 200)
                ->orderBy('distance')
                ->limit(5) // Get top 5 for debugging
                ->get();

            return response()->json([
                'search_coordinates' => ['lat' => $lat, 'lng' => $lng],
                'nearest_cities' => $nearestCity->map(function ($city) {
                    return [
                        'name' => $city->name,
                        'state' => $city->state_name,
                        'country' => $city->country_name,
                        'distance' => round($city->distance, 2),
                        'coordinates' => ['lat' => $city->latitude, 'lng' => $city->longitude]
                    ];
                }),
                'total_found' => $nearestCity->count()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'sql_error' => $e->getPrevious()?->getMessage()
            ], 500);
        }
    }
}