<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Post;

class SearchHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'location_name',
        'latitude',
        'longitude',
        'address_components',
        'search_type',
        'search_count',
        'last_searched_at',
    ];

    protected $casts = [
        'address_components' => 'array',
        'latitude' => 'float',
        'longitude' => 'float',
        'last_searched_at' => 'datetime',
    ];

    /**
     * Get the user that owns the search history.
     */
    public static function recordSearch(
        int $userId,
        string $locationName,
        float $latitude,
        float $longitude,
        ?array $addressComponents = null,
        string $searchType = 'manual'
    ): ?self {
        // Special handling for geolocation: Check if ANY geolocation search was made in the last hour
        if ($searchType === 'geolocation') {
            $hourAgo = now()->subHour();

            // Check if there's ANY geolocation search in the last hour (not just this location)
            $recentGeoSearch = self::where('user_id', $userId)
                ->where('search_type', 'geolocation')
                ->where('last_searched_at', '>=', $hourAgo)
                ->first();

            if ($recentGeoSearch) {
                // Found a geolocation search within the last hour - throttle it
                \Log::debug("Geolocation search throttled (last geolocation was " .
                    $recentGeoSearch->last_searched_at->diffForHumans() .
                    "): {$locationName}");
                return null;
            }
        }

        // Find existing search for this specific location
        $search = self::where('user_id', $userId)
            ->where('latitude', $latitude)
            ->where('longitude', $longitude)
            ->first();

        if ($search) {
            // Location already exists, update it
            $search->update([
                'location_name' => $locationName,
                'address_components' => $addressComponents,
                'search_type' => $searchType,
                'search_count' => $search->search_count + 1,
                'last_searched_at' => now(),
            ]);

            \Log::debug("Search updated ({$searchType}): {$locationName}");
            return $search;
        } else {
            // New location, create record
            $search = self::create([
                'user_id' => $userId,
                'location_name' => $locationName,
                'latitude' => $latitude,
                'longitude' => $longitude,
                'address_components' => $addressComponents,
                'search_type' => $searchType,
                'search_count' => 1,
                'last_searched_at' => now(),
            ]);

            \Log::debug("New location search recorded ({$searchType}): {$locationName}");
            return $search;
        }
    }

    /**
     * Get recent searches for a user.
     */
    public static function getRecentSearches(int $userId, int $limit = 10)
    {
        return self::where('user_id', $userId)
            ->orderBy('last_searched_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get most searched locations.
     */
    public static function getMostSearched(int $userId, int $limit = 5)
    {
        return self::where('user_id', $userId)
            ->orderBy('search_count', 'desc')
            ->orderBy('last_searched_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Clear old search history (older than specified days).
     */
    public static function clearOldHistory(int $userId, int $days = 90): int
    {
        return self::where('user_id', $userId)
            ->where('last_searched_at', '<', now()->subDays($days))
            ->delete();
    }
}