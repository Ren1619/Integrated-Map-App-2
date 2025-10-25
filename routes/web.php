<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WeatherController;

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

// ============================================
// AUTHENTICATED ROUTES
// ============================================

// ============================================
// AUTHENTICATED & VERIFIED ROUTES
// ============================================
// All authenticated features require email verification
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

// ============================================
// FUTURE: AUTHENTICATED WEATHER FEATURES
// ============================================
// Uncomment these when you implement saved searches feature
// Route::middleware(['auth'])->group(function () {
//     Route::get('/weather/saved-searches', [WeatherController::class, 'getSavedSearches'])->name('weather.saved.index');
//     Route::post('/weather/save-search', [WeatherController::class, 'saveSearch'])->name('weather.saved.store');
//     Route::delete('/weather/saved-searches/{id}', [WeatherController::class, 'deleteSavedSearch'])->name('weather.saved.destroy');
// });

// ============================================
// LEGACY ENDPOINTS (Deprecated)
// ============================================
Route::prefix('weather/legacy')->group(function () {
    // These endpoints return deprecation notices
    Route::get('/cities/all', [WeatherController::class, 'getAllCitiesWeatherData'])->name('weather.legacy.cities.all');
    Route::post('/nearby-directional', [WeatherController::class, 'getNearbyDirectionalCities'])->name('weather.legacy.nearby.directional');

    // Keep for backward compatibility but will return limited data
    Route::post('/temperature', [WeatherController::class, 'getTemperatureData'])->name('weather.legacy.temperature');
    Route::post('/wind', [WeatherController::class, 'getWindData'])->name('weather.legacy.wind');
    Route::post('/radar', [WeatherController::class, 'getRadarData'])->name('weather.legacy.radar');
});


Route::get('/weather/alerts', [WeatherController::class, 'getWeatherAlerts'])->name('weather.alerts');

// Include authentication routes (login, register, password reset, etc.)
require __DIR__ . '/auth.php';