<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Record or update a search.
     */
    public static function recordSearch(
        int $userId,
        string $locationName,
        float $latitude,
        float $longitude,
        ?array $addressComponents = null,
        string $searchType = 'manual'
    ): self {
        $search = self::where('user_id', $userId)
            ->where('latitude', $latitude)
            ->where('longitude', $longitude)
            ->first();

        if ($search) {
            $search->update([
                'location_name' => $locationName,
                'address_components' => $addressComponents,
                'search_type' => $searchType,
                'search_count' => $search->search_count + 1,
                'last_searched_at' => now(),
            ]);
        } else {
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
        }

        return $search;
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