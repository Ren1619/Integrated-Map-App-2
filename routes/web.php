<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WeatherController;

// Main weather map page
Route::get('/', [WeatherController::class, 'index'])->name('weather.map');

// Enhanced location search using precise coordinates
Route::post('/search', [WeatherController::class, 'search'])->name('weather.search');

// Autocomplete suggestions for search
Route::post('/weather/autocomplete', [WeatherController::class, 'getAutocompleteSuggestions'])->name('weather.autocomplete');

// Get location name from coordinates (ADD THIS MISSING ROUTE)
Route::post('/weather/location-name', [WeatherController::class, 'getLocationName'])->name('weather.location.name');

// Enhanced weather data endpoint with extended parameters
Route::post('/weather/data', [WeatherController::class, 'getWeatherData'])->name('weather.data');

// Debug endpoint for testing enhanced weather API
Route::get('/debug/enhanced-weather', [WeatherController::class, 'debugEnhancedWeatherApi'])->name('debug.enhanced.weather');

// Authentication routes
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Legacy endpoints - marked as deprecated
Route::prefix('weather/legacy')->group(function () {
    // These endpoints return deprecation notices
    Route::get('/cities/all', [WeatherController::class, 'getAllCitiesWeatherData'])->name('weather.legacy.cities.all');
    Route::post('/nearby-directional', [WeatherController::class, 'getNearbyDirectionalCities'])->name('weather.legacy.nearby.directional');

    // Keep for backward compatibility but will return limited data
    Route::post('/temperature', [WeatherController::class, 'getTemperatureData'])->name('weather.legacy.temperature');
    Route::post('/wind', [WeatherController::class, 'getWindData'])->name('weather.legacy.wind');
    Route::post('/radar', [WeatherController::class, 'getRadarData'])->name('weather.legacy.radar');
});

require __DIR__ . '/auth.php';