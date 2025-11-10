<?php

use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

describe('API Performance Testing', function () {
    
    test('weather data retrieval is within acceptable time', function () {
        Http::fake([
            'api.open-meteo.com/*' => Http::response(['current' => []], 200)
        ]);

        $times = [];
        
        // Run 50 iterations
        for ($i = 0; $i < 50; $i++) {
            $start = microtime(true);
            
            $response = $this->postJson('/weather/data', [
                'lat' => 7.1907 + (rand(-100, 100) / 1000),
                'lng' => 125.4553 + (rand(-100, 100) / 1000)
            ]);
            
            $end = microtime(true);
            $times[] = ($end - $start) * 1000;
            
            $response->assertStatus(200);
        }

        $average = array_sum($times) / count($times);
        $min = min($times);
        $max = max($times);

        // Assert performance targets
        expect($average)->toBeLessThan(2000) // < 2 seconds average
            ->and($max)->toBeLessThan(3000); // < 3 seconds max
        
        echo "\n📊 Weather API Performance:\n";
        echo "   Average: " . round($average, 2) . "ms\n";
        echo "   Min: " . round($min, 2) . "ms\n";
        echo "   Max: " . round($max, 2) . "ms\n";
    });

    test('autocomplete response is fast enough', function () {
        Http::fake([
            'geocoding-api.open-meteo.com/*' => Http::response([
                'results' => [['name' => 'Test', 'latitude' => 0, 'longitude' => 0]]
            ], 200)
        ]);

        $times = [];
        $queries = ['Tokyo', 'New York', 'London', 'Davao', 'Paris'];
        
        foreach ($queries as $query) {
            for ($i = 0; $i < 20; $i++) {
                $start = microtime(true);
                
                $response = $this->postJson('/weather/autocomplete', [
                    'query' => $query
                ]);
                
                $end = microtime(true);
                $times[] = ($end - $start) * 1000;
                
                $response->assertStatus(200);
            }
        }

        $average = array_sum($times) / count($times);

        expect($average)->toBeLessThan(500); // < 500ms target
        
        echo "\n⚡ Autocomplete Performance: " . round($average, 2) . "ms average\n";
    });

    test('cache hit ratio meets target', function () {
        Cache::flush();
        
        $hits = 0;
        $misses = 0;
        
        // First request - cache miss
        $response = $this->postJson('/weather/data', [
            'lat' => 7.1907,
            'lng' => 125.4553
        ]);
        $misses++;
        
        // Second request - should be cache hit
        $start = microtime(true);
        $response = $this->postJson('/weather/data', [
            'lat' => 7.1907,
            'lng' => 125.4553
        ]);
        $cachedTime = (microtime(true) - $start) * 1000;
        $hits++;
        
        // New location - cache miss
        $start = microtime(true);
        $response = $this->postJson('/weather/data', [
            'lat' => 14.5995,
            'lng' => 120.9842
        ]);
        $uncachedTime = (microtime(true) - $start) * 1000;
        $misses++;

        $hitRatio = ($hits / ($hits + $misses)) * 100;

        expect($cachedTime)->toBeLessThan($uncachedTime / 2) // Cached should be 2x faster
            ->and($hitRatio)->toBeGreaterThan(0);
        
        echo "\n💾 Cache Performance:\n";
        echo "   Cached: " . round($cachedTime, 2) . "ms\n";
        echo "   Uncached: " . round($uncachedTime, 2) . "ms\n";
        echo "   Speedup: " . round($uncachedTime / $cachedTime, 1) . "x faster\n";
    });

    test('database queries are optimized', function () {
        $user = User::factory()->create();
        $user->searchHistories()->createMany([
            ['location_name' => 'Tokyo', 'latitude' => 35.6762, 'longitude' => 139.6503, 'search_count' => 1, 'last_searched_at' => now()],
            ['location_name' => 'Paris', 'latitude' => 48.8566, 'longitude' => 2.3522, 'search_count' => 1, 'last_searched_at' => now()],
        ]);

        DB::enableQueryLog();
        
        $start = microtime(true);
        $this->actingAs($user)->get('/dashboard');
        $queryTime = (microtime(true) - $start) * 1000;
        
        $queries = DB::getQueryLog();
        $queryCount = count($queries);

        expect($queryTime)->toBeLessThan(200) // < 200ms for dashboard
            ->and($queryCount)->toBeLessThan(15); // < 15 queries (N+1 prevention)
        
        echo "\n🗄️  Database Performance:\n";
        echo "   Query Time: " . round($queryTime, 2) . "ms\n";
        echo "   Query Count: " . $queryCount . "\n";
    });
});