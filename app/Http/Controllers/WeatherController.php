<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class WeatherController extends Controller
{
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
            // Search for location using Nominatim
            $response = Http::get('https://nominatim.openstreetmap.org/search', [
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
            'lat' => 'required|numeric|between:-90, 90',
            'lng' => 'required|numeric|between: -180, 180'
        ]);

        $lat = $request->lat;
        $lng = $request->lng;
        $cacheKey = "weather_{$lat}_{$lng}";

        try{
            $weatherData = Cache::remember($cacheKey, 600, function() use ($lat, $lng) {
                $response = Http::get('https://api.open-meteo.com/v1/forecast', [
                    'latitude' => $lat,
                    'longitude' => $lng,
                    'current' => 'temperature_2m,relative_humidity_2m, apparent_temperature,weather_code,surface_pressure,wind_speed_10m,wind_direction_10m,wind_gusts_10m',
                    'hourly' => 'temperature_2m,relative_humidity_2m, apparent_temperature,weather_code,surface_pressure,wind_speed_10m,wind_direction_10m,wind_gusts_10m',
                    'daily' => 'temperature_2m,relative_humidity_2m, apparent_temperature,weather_code,surface_pressure,wind_speed_10m,wind_direction_10m,wind_gusts_10m',
                    'timezone' => 'auto',
                    'forecast_days' => 7,
                ]);

                if(!$response->successful()){
                    throw new \Exception('Weather API request failed');
                }

                return $response->json();
            });

            $locationData = $this->getLocationName($lat, $lng);

            return response()->json([
                'success' => true,
                'data' => [
                    'weather' => $weatherData,
                    'location' => $locationData
                ]
            ]);
        }catch(\Exception $e){
            return response()->json([
                'success' => false,
                'message' => 'Error fetching weather data'
            ], 500);
        }
    }

    public function getRadarData(Request $request): JsonResponse
    {
        $request->validate([
            'lat' => 'required|numeric|between:-90, 90',
            'lng' => 'required|numeric|between:-180,180',
            'zoom' => 'integer|between:1,18'
        ]);

        $lat = $request->lat;
        $lng = $request->lng;
        $zoom = $request->zoom ?? 10;

        try{
            $response = Http::get('https://api.open-meteo.com/v1/forecast', [
                'latitude' => $lat,
                'longitude' => $lng,
                'current' => 'precipitation,weather_code',
                'timezone' => 'auto',
                'forecast_days' => 1
            ]);

            if(!$response->successful()){
                throw new \Exception('Radar API request failed');
            }

            $data = $response->json();

            return response()->json([
                'success' => true,
                'data' => [
                    'precipitation' => $data['hourly']['precipitation'] ?? [],
                    'timestamps' => $data['hourly']['time'] ?? [],
                    'weather_codes' => $data['hourly']['weather_code'] ?? []
                ]
            ]);
        } catch(\Exception $e){
            return response()->json([
                'success' => false,
                'message' => 'Error fetching radar data'
            ], 500);
        }
    }

    public function getWindData(Request $request): JsonResponse
    {
        $request->validate([
            'lat' => 'required|numeric|between:-90,90',
            'lng' => 'required|numeric|between:-180,180',
        ]);

        $lat = $request->lat;
        $lng = $request->lng;

        try{
            $windData = [];
            $gridSize = 0.1;
            $gridPoints = 5;

            for ($i = -2; $i <= 2; $i++){
                for($j = -2; $j <= 2; $j++){
                    $gridLat = $lat + ($i * $gridSize);
                    $gridLng = $lng + ($j * $gridSize);

                    $response = Http::get('https://api.open-meteo.com/v1/forecast', [
                        'latitude' => $gridLat,
                        'longitude' => $gridLng,
                        'current' => 'wind_speed_10m,wind_direction_10m,wind_gusts-10m',
                        'hourly' => 'wind_speed_10m,wind_direction_10m',
                        'timezone' => 'auto',
                        'forecast_days' => 1
                    ]);

                    if($response->successful()){
                        $data = $response->json();
                        $windData[] = [
                            'lat' => $gridLat,
                            'lng' => $gridLng,
                            'current' => $data['current'] ?? null,
                            'hourly' => array_slice($data['hourly']['wind_speed_10m'] ?? [], 0, 24),
                            'directions' => array_slice($data['hourly']['wind_direction_10m'] ?? [], 0, 24),
                            'timestamps' => array_slice($data['hourly']['time'] ?? [], 0, 24)
                        ];
                    }
                }
            }

            return response()->json([
                'success' => true,
                'data' => $windData
            ]);
        } catch(\Exception $e){
            return response()->json([
                'success' => false,
                'message' => 'Error fetching wind data'
            ], 500);
        }
    }

    public function getTemperatureData(Request $request): JsonResponse
    {
        $request->validate([
            'lat' => 'required|numeric|between:-90,90',
            'lng' => 'required|numeric|between:-180,180',
        ]);

        $lat = $request->lat;
        $lng = $request->lng;

        try{
            $tempData = [];
            $gridSize = 0.1;
            
            for($i = -2; $i <= 2; $i++){
                for($j = -2; $j <= 2; $j++){
                    $gridLat = $lat + ($i * $gridSize);
                    $gridLng = $lng + ($j * $gridSize);

                    $response = Http::get('https://api.open-meteo.com/v1/forecast', [
                        'latitude' => $gridLat,
                        'longitude' => $gridLng,
                        'current' => 'temperature_2m,apparent_temperature',
                        'hourly' => 'temperature_2m,apparent_temperature',
                        'timezone' => 'auto',
                        'forecast_days' => 1
                    ]);

                    if($response->successful()){
                        $data = $response->json();
                        $tempData[] = [
                            'lat' => $gridLat,
                            'lng' => $gridLng,
                            'current_temp' => $data['current']['temperature_2m'] ?? null,
                            'apparent_temp' => $data['current']['apparent_temperature'] ?? null,
                            'hourly_temps' => array_slice($data['hourly']['temperature_2m'] ?? [], 0, 24),
                            'timestamps' => array_slice($data['hourly']['time'] ?? [], 0, 24)
                        ];
                    }
                }
            }

            return response()->json([
                'success' => true,
                'data' => $tempData
            ]);
        } catch(\Exception $e){
            return response()->json([
                'success' => false,
                'message' => 'Error fetching temperature data'
            ], 500);
        }
    }

    private function getLocationName($lat, $lng)
    {
        try{
            $response = Http::get('https://nominatim.openstreetmap.org/reverse', [
                'format' => 'json',
                'lat' => $lat,
                'lon' => $lng,
                'addressdetails' => 1
            ]);

            if($response->successful()){
                $data = $response->json();
                return $data['address'] ?? [];

                $parts = [];
                if(isset($address['city'])) $parts[] = $address['city'];
                elseif(isset($address['town'])) $parts[] = $address['town'];
                elseif(isset($address['village'])) $parts[] = $address['village'];

                if(isset($address['state'])) $parts[] = $address['state'];
                if(isset($address['country'])) $parts[] = $address['country'];

                return implode(', ', $parts) ?: 'Unknown location';
            }
        } catch(\Exception $e){
            // Log error if needed
        }

        return 'Unknown location';
    }
}