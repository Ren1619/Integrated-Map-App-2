protected function schedule(Schedule $schedule)
{
    // Update weather data cache every hour
    $schedule->call(function () {
        app(WeatherController::class)->updateWeatherDataCache();
    })->hourly()->name('update-weather-cache');
    
    // You can also add different schedules:
    // Every 30 minutes during peak hours (6 AM - 10 PM)
    $schedule->call(function () {
        app(WeatherController::class)->updateWeatherDataCache();
    })->cron('*/30 6-22 * * *')->name('update-weather-cache-frequent');
}