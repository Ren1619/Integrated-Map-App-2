<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\WeatherController;

class UpdateWeatherCache extends Command
{
    protected $signature = 'weather:update-cache';
    protected $description = 'Update weather data cache for all cities';

    public function handle()
    {
        $this->info('Updating weather data cache...');
        
        $controller = new WeatherController();
        $result = $controller->updateWeatherDataCache();
        
        if ($result->getData()->success) {
            $this->info('✅ Weather cache updated successfully!');
        } else {
            $this->error('❌ Failed to update weather cache');
        }
        
        return 0;
    }
}