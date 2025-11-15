<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WeatherController;
use App\Http\Controllers\UserDataController;

// ============================================
// PUBLIC ROUTES (No Authentication Required)
// ============================================

// Main weather map page - accessible to everyone
Route::get('/', [WeatherController::class, 'index'])->name('weather.map');

// Enhanced location search using precise coordinates
Route::post('/search', [WeatherController::class, 'search'])->name('weather.search');

// Autocomplete suggestions for search
Route::post('/weather/autocomplete', [WeatherController::class, 'getAutocompleteSuggestions'])->name('weather.autocomplete');

// Get location name from coordinates
Route::post('/weather/location-name', [WeatherController::class, 'getLocationName'])->name('weather.location.name');

// Enhanced weather data endpoint with extended parameters
Route::post('/weather/data', [WeatherController::class, 'getWeatherData'])->name('weather.data');

// Debug endpoint for testing enhanced weather API
Route::get('/debug/enhanced-weather', [WeatherController::class, 'debugEnhancedWeatherApi'])->name('debug.enhanced.weather');

Route::middleware(['auth', 'verified'])->group(function () {
    // Dashboard
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    // Profile viewing and management
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});


Route::get('/weather/alerts', [WeatherController::class, 'getWeatherAlerts'])->name('weather.alerts');


// User Data Routes (Authenticated & Verified)
Route::middleware(['auth', 'verified'])->group(function () {

    // ==================== SEARCH HISTORY ====================
    Route::post('/user/search-history', [UserDataController::class, 'recordSearch'])
        ->name('user.search.record');
    Route::get('/user/search-history', [UserDataController::class, 'getSearchHistory'])
        ->name('user.search.history');
    Route::delete('/user/search-history', [UserDataController::class, 'clearSearchHistory'])
        ->name('user.search.clear');
    Route::delete('/user/search-history/{search}', [UserDataController::class, 'deleteSearchEntry'])
        ->name('user.search.delete');

    // ==================== SAVED LOCATIONS ====================
    Route::post('/user/saved-locations/toggle', [UserDataController::class, 'toggleSavedLocation'])
        ->name('user.saved.toggle');
    Route::get('/user/saved-locations', [UserDataController::class, 'getSavedLocations'])
        ->name('user.saved.list');
    Route::put('/user/saved-locations/{saved}', [UserDataController::class, 'updateSavedLocation'])
        ->name('user.saved.update');
    Route::delete('/user/saved-locations/{saved}', [UserDataController::class, 'deleteSavedLocation'])
        ->name('user.saved.delete');
    Route::post('/user/saved-locations/{saved}/visit', [UserDataController::class, 'recordSavedLocationVisit'])
        ->name('user.saved.visit');
    Route::post('/user/saved-locations/check', [UserDataController::class, 'checkSavedLocation'])
        ->name('user.saved.check');
    Route::post('/user/saved-locations/reorder', [UserDataController::class, 'reorderSavedLocations'])
        ->name('user.saved.reorder');
});

Route::get('/combined-data', function (Request $request) {
    try {
        // Validate coordinates
        $request->validate([
            'lat' => 'required|numeric|between:-90,90',
            'lng' => 'required|numeric|between:-180,180'
        ]);

        $lat = $request->lat;
        $lng = $request->lng;

        // Fetch weather data
        $weatherResponse = Http::timeout(15)->get('https://api.open-meteo.com/v1/forecast', [
            'latitude' => $lat,
            'longitude' => $lng,
            'current' => 'temperature_2m,apparent_temperature,relative_humidity_2m,weather_code,surface_pressure,wind_speed_10m,wind_direction_10m,precipitation,cloud_cover',
            'daily' => 'weather_code,temperature_2m_max,temperature_2m_min,precipitation_sum',
            'timezone' => 'auto',
            'forecast_days' => 3
        ]);

        // Fetch location info
        $locationResponse = Http::timeout(10)
            ->withHeaders(['User-Agent' => 'WeatherMapApp/1.0'])
            ->get('https://nominatim.openstreetmap.org/reverse', [
                'format' => 'json',
                'lat' => $lat,
                'lon' => $lng,
                'zoom' => 18,
                'addressdetails' => 1
            ]);

        // Build response
        $responseData = [
            'timestamp' => now()->toIso8601String(),
            'coordinates' => [
                'latitude' => (float) $lat,
                'longitude' => (float) $lng
            ],
            'authenticated' => Auth::check(),
            'weather' => $weatherResponse->successful() ? $weatherResponse->json() : ['error' => 'Weather fetch failed'],
            'location' => $locationResponse->successful() ? $locationResponse->json() : ['error' => 'Location fetch failed']
        ];

        // Add user data if authenticated
        if (Auth::check()) {
            $user = Auth::user();

            $responseData['user'] = [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'email_verified' => $user->hasVerifiedEmail(),
                'member_since' => $user->created_at->toIso8601String(),
                'account_age_days' => $user->created_at->diffInDays(now()),

                'search_history_count' => $user->searchHistories()->count(),
                'recent_searches' => $user->searchHistories()
                    ->orderBy('last_searched_at', 'desc')
                    ->limit(5)
                    ->get()
                    ->map(fn($search) => [
                        'location' => $search->location_name,
                        'coordinates' => ['lat' => (float) $search->latitude, 'lng' => (float) $search->longitude],
                        'search_count' => $search->search_count,
                        'last_searched' => $search->last_searched_at->toIso8601String()
                    ]),

                'saved_locations_count' => $user->savedLocations()->count(),
                'saved_locations' => $user->savedLocations()
                    ->orderBy('sort_order')
                    ->get()
                    ->map(fn($saved) => [
                        'id' => $saved->id,
                        'name' => $saved->name,
                        'location_name' => $saved->location_name,
                        'coordinates' => ['lat' => (float) $saved->latitude, 'lng' => (float) $saved->longitude],
                        'emoji' => $saved->emoji,
                        'visit_count' => $saved->visit_count,
                        'last_visited' => $saved->last_visited_at?->toIso8601String()
                    ])
            ];
        }

        return response()->json([
            'success' => true,
            'data' => $responseData
        ]);

    } catch (\Illuminate\Validation\ValidationException $e) {
        return response()->json([
            'success' => false,
            'message' => 'Invalid input',
            'errors' => $e->errors()
        ], 422);

    } catch (\Exception $e) {
        Log::error('Combined data error: ' . $e->getMessage());

        return response()->json([
            'success' => false,
            'message' => 'Error fetching combined data',
            'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
        ], 500);
    }
})->name('combined.data');

Route::get('/location/from-ip', [WeatherController::class, 'getLocationFromIP'])->name('location.from.ip');

require __DIR__ . '/auth.php';