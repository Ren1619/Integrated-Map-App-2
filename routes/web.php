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



require __DIR__.'/auth.php';

