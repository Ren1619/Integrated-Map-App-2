<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class WeatherController extends Controller
{
    // Major cities/towns in the Philippines with their coordinates
    private $majorCities = [
        // Mindanao - Bukidnon area
        ['name' => 'Valencia City', 'lat' => 7.8954, 'lng' => 125.0951],
        ['name' => 'Malaybalay City', 'lat' => 8.1531, 'lng' => 125.1296],
        ['name' => 'Maramag', 'lat' => 7.7622, 'lng' => 125.0065],
        ['name' => 'Quezon', 'lat' => 7.7342, 'lng' => 125.1047],
        ['name' => 'Cagayan de Oro', 'lat' => 8.4542, 'lng' => 124.6319],
        ['name' => 'Butuan', 'lat' => 8.9474, 'lng' => 125.5405],
        ['name' => 'Davao City', 'lat' => 7.1907, 'lng' => 125.4553],
        ['name' => 'Iligan City', 'lat' => 8.2280, 'lng' => 124.2452],

        // Luzon
        ['name' => 'Manila', 'lat' => 14.5995, 'lng' => 120.9842],
        ['name' => 'Quezon City', 'lat' => 14.6760, 'lng' => 121.0437],
        ['name' => 'Makati', 'lat' => 14.5547, 'lng' => 121.0244],
        ['name' => 'Pasig', 'lat' => 14.5764, 'lng' => 121.0851],
        ['name' => 'Antipolo', 'lat' => 14.5932, 'lng' => 121.1815],
        ['name' => 'Baguio', 'lat' => 16.4023, 'lng' => 120.5960],
        ['name' => 'Angeles', 'lat' => 15.1455, 'lng' => 120.5876],
        ['name' => 'San Jose del Monte', 'lat' => 14.8136, 'lng' => 121.0453],
        ['name' => 'Calamba', 'lat' => 14.2118, 'lng' => 121.1653],
        ['name' => 'Bacoor', 'lat' => 14.4598, 'lng' => 120.9429],

        // Visayas
        ['name' => 'Cebu City', 'lat' => 10.3157, 'lng' => 123.8854],
        ['name' => 'Lapu-Lapu', 'lat' => 10.3103, 'lng' => 123.9494],
        ['name' => 'Mandaue', 'lat' => 10.3237, 'lng' => 123.9227],
        ['name' => 'Iloilo City', 'lat' => 10.7202, 'lng' => 122.5621],
        ['name' => 'Bacolod', 'lat' => 10.6319, 'lng' => 122.9951],
        ['name' => 'Tacloban', 'lat' => 11.2421, 'lng' => 125.0066],
    ];

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
                    'display_name' => $location['display_name']
                ]
            ]);

        } catch (\Exception $e) {
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
            'lng' => 'required|numeric|between:-180,180'
        ]);

        $lat = $request->lat;
        $lng = $request->lng;

        // Find nearest city for specific weather data
        $nearestCity = $this->findNearestCity($lat, $lng);
        $cacheKey = "weather_single_{$nearestCity['name']}";

        try {
            $weatherData = Cache::remember($cacheKey, 600, function () use ($nearestCity) {
                $response = Http::timeout(30)->get('https://api.open-meteo.com/v1/forecast', [
                    'latitude' => $nearestCity['lat'],
                    'longitude' => $nearestCity['lng'],
                    'current' => 'temperature_2m,relative_humidity_2m,apparent_temperature,weather_code,surface_pressure,wind_speed_10m,wind_direction_10m,wind_gusts_10m',
                    'hourly' => 'temperature_2m,relative_humidity_2m,weather_code,surface_pressure,wind_speed_10m,wind_direction_10m,precipitation,precipitation_probability',
                    'daily' => 'weather_code,temperature_2m_max,temperature_2m_min,precipitation_sum,precipitation_probability_max,wind_speed_10m_max,wind_gusts_10m_max',
                    'timezone' => 'auto',
                    'forecast_days' => 7
                ]);

                if (!$response->successful()) {
                    throw new \Exception('Weather API request failed');
                }

                return $response->json();
            });

            $locationData = $nearestCity['name'];

            return response()->json([
                'success' => true,
                'data' => [
                    'weather' => $weatherData,
                    'location' => $locationData
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching weather data'
            ], 500);
        }
    }

    public function getAllCitiesWeatherData(): JsonResponse
    {
        $cacheKey = 'all_cities_weather_data';

        try {
            $allWeatherData = Cache::remember($cacheKey, 3600, function () {
                $weatherData = [];
                $batches = array_chunk($this->majorCities, 10);

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

                // Optional: Store to database for historical data
                // $this->storeWeatherDataToDatabase($weatherData);

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
            return response()->json([
                'success' => false,
                'message' => 'Error fetching weather data for cities'
            ], 500);
        }
    }

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
            return $city['precipitation'] > 0; // Only show cities with precipitation
        });

        return response()->json([
            'success' => true,
            'data' => $precipitationData->values()->all()
        ]);
    }

    // Scheduled job method - call this hourly via Laravel scheduler
    public function updateWeatherDataCache()
    {
        try {
            // Clear existing cache
            Cache::forget('all_cities_weather_data');

            // Fetch fresh data
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

    private function findNearestCity($lat, $lng)
    {
        $nearestCity = null;
        $minDistance = PHP_FLOAT_MAX;

        foreach ($this->majorCities as $city) {
            $distance = $this->calculateDistance($lat, $lng, $city['lat'], $city['lng']);
            if ($distance < $minDistance) {
                $minDistance = $distance;
                $nearestCity = $city;
            }
        }

        return $nearestCity;
    }

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
}