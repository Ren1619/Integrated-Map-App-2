<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WeatherController;

// Main weather map page
Route::get('/', [WeatherController::class, 'index'])->name('weather.map');

// Location search
Route::post('/search', [WeatherController::class, 'search'])->name('weather.search');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Weather data endpoints
Route::prefix('weather')->group(function () {
    Route::post('/data', [WeatherController::class, 'getWeatherData'])->name('weather.data');
    Route::post('/temperature', [WeatherController::class, 'getTemperatureData'])->name('weather.temperature');
    Route::post('/wind', [WeatherController::class, 'getWindData'])->name('weather.wind');
    Route::post('/radar', [WeatherController::class, 'getRadarData'])->name('weather.radar');
});

Route::get('/weather', [WeatherController::class, 'index'])->name('weather.index');
Route::post('/search', [WeatherController::class, 'search'])->name('weather.search');
Route::post('/weather/data', [WeatherController::class, 'getWeatherData'])->name('weather.data');

// Legacy routes for backward compatibility
Route::get('/weather/cities/all', [WeatherController::class, 'getAllCitiesWeatherData'])->name('weather.cities.all');
Route::get('/weather/temperature', [WeatherController::class, 'getTemperatureData'])->name('weather.temperature');
Route::get('/weather/wind', [WeatherController::class, 'getWindData'])->name('weather.wind');
Route::get('/weather/radar', [WeatherController::class, 'getRadarData'])->name('weather.radar');
Route::post('/weather/update-cache', [WeatherController::class, 'updateWeatherDataCache'])->name('weather.update.cache');

// NEW ENHANCED ROUTES
// Route for getting nearby cities in all 8 directions
Route::post('/weather/nearby-directional', [WeatherController::class, 'getNearbyDirectionalCities'])->name('weather.nearby.directional');

// Optional: Additional utility routes
Route::get('/weather/cities/search/{query}', [WeatherController::class, 'searchCities'])->name('weather.cities.search');
Route::get('/weather/location/{lat}/{lng}', [WeatherController::class, 'getLocationInfo'])->name('weather.location.info');

// Add these to your web.php temporarily for debugging
Route::get('/debug/database', [WeatherController::class, 'debugDatabase']);
Route::get('/debug/weather-api', [WeatherController::class, 'debugWeatherApi']);
Route::post('/debug/nearest-city', [WeatherController::class, 'debugNearestCity']);

require __DIR__.'/auth.php';

