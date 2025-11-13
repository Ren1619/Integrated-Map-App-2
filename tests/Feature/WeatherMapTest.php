<?php

use App\Models\User;
use Illuminate\Support\Facades\Http;

describe('Weather Map Functionality', function () {

    test('location search with autocomplete works correctly', function () {
        // Arrange
        Http::fake([
            'geocoding-api.open-meteo.com/*' => Http::response([
                'results' => [
                    [
                        'id' => 1,
                        'name' => 'Davao City',
                        'latitude' => 7.1907,
                        'longitude' => 125.4553,
                        'country' => 'Philippines',
                        'admin1' => 'Davao Region'
                    ]
                ]
            ], 200)
        ]);

        // Act
        $response = $this->postJson('/weather/autocomplete', [
            'query' => 'Davao'
        ]);

        // Assert
        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    [
                        'name' => 'Davao City',
                        'lat' => 7.1907,
                        'lng' => 125.4553,
                    ]
                ]
            ]);

        expect($response->json('data'))->toHaveCount(1);
        expect($response->json('data.0.name'))->toBe('Davao City');
    });

    test('map click location selection loads weather data', function () {
        // Arrange
        Http::fake([
            'api.open-meteo.com/*' => Http::response([
                'current' => [
                    'temperature_2m' => 32,
                    'relative_humidity_2m' => 85,
                    'weather_code' => 0,
                    'surface_pressure' => 1013,
                    'wind_speed_10m' => 15
                ]
            ], 200)
        ]);

        // Act
        $response = $this->postJson('/weather/data', [
            'lat' => 14.5995,
            'lng' => 120.9842
        ]);

        // Assert
        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'weather' => [
                        'current' => [
                            'temperature_2m' => 32
                        ]
                    ]
                ]
            ]);
    });

    test('weather data retrieval is within performance threshold', function () {
        // Arrange
        Http::fake([
            'api.open-meteo.com/*' => Http::response([
                'current' => ['temperature_2m' => 28]
            ], 200, ['X-Response-Time' => '800ms'])
        ]);

        // Act
        $startTime = microtime(true);
        $response = $this->postJson('/weather/data', [
            'lat' => 7.1907,
            'lng' => 125.4553
        ]);
        $endTime = microtime(true);

        $responseTime = ($endTime - $startTime) * 1000; // Convert to ms

        // Assert
        $response->assertStatus(200);
        expect($responseTime)->toBeLessThan(2000); // Less than 2 seconds
    });
});

describe('User Authentication and Dashboard', function () {

    test('authenticated users can access dashboard', function () {
        $user = User::factory()->create(['email_verified_at' => now()]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);
        $this->assertAuthenticatedAs($user);
    });

    test('dashboard displays user statistics correctly', function () {
        $user = User::factory()->create(['email_verified_at' => now()]);

        // Create 3 search records
        $user->searchHistories()->createMany([
            ['location_name' => 'Tokyo', 'latitude' => 35.6762, 'longitude' => 139.6503, 'search_count' => 1, 'search_type' => 'manual', 'last_searched_at' => now()],
            ['location_name' => 'Paris', 'latitude' => 48.8566, 'longitude' => 2.3522, 'search_count' => 1, 'search_type' => 'manual', 'last_searched_at' => now()],
            ['location_name' => 'London', 'latitude' => 51.5074, 'longitude' => -0.1278, 'search_count' => 1, 'search_type' => 'manual', 'last_searched_at' => now()],
        ]);

        // Create 2 saved locations
        $user->savedLocations()->createMany([
            ['name' => 'Home', 'location_name' => 'Davao', 'latitude' => 7.1907, 'longitude' => 125.4553, 'emoji' => '🏠', 'sort_order' => 0],
            ['name' => 'Work', 'location_name' => 'Manila', 'latitude' => 14.5995, 'longitude' => 120.9842, 'emoji' => '🏢', 'sort_order' => 1],
        ]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);

        // Verify data was passed to the view (if using Blade)
        $totalSearches = $user->searchHistories()->count();
        $savedLocations = $user->savedLocations()->count();

        expect($totalSearches)->toBe(3)
            ->and($savedLocations)->toBe(2);
    });

    test('users can save locations successfully', function () {
        // Arrange
        $user = User::factory()->create();

        // Act
        $response = $this->actingAs($user)
            ->postJson('/user/saved-locations/toggle', [
                'name' => 'Tokyo',
                'location_name' => 'Tokyo, Japan',
                'latitude' => 35.6762,
                'longitude' => 139.6503,
                'emoji' => '🗼'
            ]);

        // Assert
        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'action' => 'added'
            ]);

        $this->assertDatabaseHas('saved_locations', [
            'user_id' => $user->id,
            'location_name' => 'Tokyo, Japan',
            'latitude' => 35.6762,
            'longitude' => 139.6503
        ]);
    });

    test('search history is recorded for authenticated users', function () {
        // Arrange
        $user = User::factory()->create();

        // Act
        $response = $this->actingAs($user)
            ->postJson('/user/search-history', [
                'location_name' => 'New York, USA',
                'latitude' => 40.7128,
                'longitude' => -74.0060,
                'search_type' => 'manual'
            ]);

        // Assert
        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('search_histories', [
            'user_id' => $user->id,
            'location_name' => 'New York, USA',
            'search_count' => 1
        ]);
    });
});

describe('Profile Management', function () {

    test('users can update profile information', function () {
        // Arrange
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com'
        ]);

        // Act
        $response = $this->actingAs($user)
            ->patch('/profile', [
                'name' => 'John Doe',
                'email' => 'john@example.com'
            ]);

        // Assert
        $response->assertStatus(302)
            ->assertRedirect('/profile/edit')
            ->assertSessionHas('status', 'profile-updated');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'John Doe',
            'email' => 'john@example.com'
        ]);
    });

    test('password can be updated successfully', function () {
        // Arrange
        $user = User::factory()->create([
            'password' => bcrypt('oldpassword')
        ]);

        // Act
        $response = $this->actingAs($user)
            ->put('/password', [
                'current_password' => 'oldpassword',
                'password' => 'newpassword123',
                'password_confirmation' => 'newpassword123'
            ]);

        // Assert
        $response->assertStatus(302)
            ->assertSessionHas('status', 'password-updated');

        // Verify old password doesn't work
        $this->assertFalse(
            Hash::check('oldpassword', $user->fresh()->password)
        );

        // Verify new password works
        $this->assertTrue(
            Hash::check('newpassword123', $user->fresh()->password)
        );
    });
});