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


require __DIR__ . '/auth.php';