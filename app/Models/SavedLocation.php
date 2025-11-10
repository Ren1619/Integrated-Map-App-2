<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Post;

class SavedLocation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'location_name',
        'latitude',
        'longitude',
        'address_components',
        'emoji',
        'notes',
        'visit_count',
        'last_visited_at',
        'sort_order',
    ];

// app/Models/SavedLocation.php

    protected $casts = [
        'address_components' => 'array',
        'latitude' => 'float', 
        'longitude' => 'float', 
        'last_visited_at' => 'datetime',
    ];

    /**
     * Get the user that owns the saved location.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if location is already saved.
     */
    public static function isSaved(int $userId, float $latitude, float $longitude): bool
    {
        return self::where('user_id', $userId)
            ->where('latitude', $latitude)
            ->where('longitude', $longitude)
            ->exists();
    }

    /**
     * Toggle saved status.
     */
    public static function toggle(
        int $userId,
        string $name,
        string $locationName,
        float $latitude,
        float $longitude,
        ?array $addressComponents = null,
        string $emoji = '📍'
    ): array {
        $saved = self::where('user_id', $userId)
            ->where('latitude', $latitude)
            ->where('longitude', $longitude)
            ->first();

        if ($saved) {
            $saved->delete();
            return ['action' => 'removed', 'saved' => null];
        }

        $saved = self::create([
            'user_id' => $userId,
            'name' => $name,
            'location_name' => $locationName,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'address_components' => $addressComponents,
            'emoji' => $emoji,
            'sort_order' => self::where('user_id', $userId)->max('sort_order') + 1,
        ]);

        return ['action' => 'added', 'saved' => $saved];
    }

    /**
     * Increment visit count.
     */
    public function recordVisit(): void
    {
        $this->increment('visit_count');
        $this->update(['last_visited_at' => now()]);
    }

    /**
     * Get user's saved locations ordered by custom sort or last visited.
     */
    public static function getUserSavedLocations(int $userId, string $orderBy = 'sort_order')
    {
        $query = self::where('user_id', $userId);

        return match($orderBy) {
            'recent' => $query->orderBy('last_visited_at', 'desc')->get(),
            'popular' => $query->orderBy('visit_count', 'desc')->get(),
            'name' => $query->orderBy('name')->get(),
            default => $query->orderBy('sort_order')->get(),
        };
    }

    /**
     * Reorder saved locations.
     */
    public static function reorder(int $userId, array $orderedIds): bool
    {
        foreach ($orderedIds as $index => $id) {
            self::where('user_id', $userId)
                ->where('id', $id)
                ->update(['sort_order' => $index]);
        }
        
        return true;
    }
}