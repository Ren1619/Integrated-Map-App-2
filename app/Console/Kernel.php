<?php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{

    protected function schedule(Schedule $schedule)
    {
        // Clean up search history older than 30 days (runs daily at 2 AM)
        $schedule->command('search:cleanup --days=30')
            ->dailyAt('02:00')
            ->name('cleanup-search-history')
            ->onSuccess(function () {
                Log::info('Search history cleanup completed successfully');
            })
            ->onFailure(function () {
                Log::error('Search history cleanup failed');
            });

        // Update weather data cache every hour (existing)
        $schedule->call(function () {
            app(WeatherController::class)->updateWeatherDataCache();
        })->hourly()->name('update-weather-cache');
    }
}