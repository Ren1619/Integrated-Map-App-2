<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the user's search history.
     */
    public function searchHistories(): HasMany
    {
        return $this->hasMany(SearchHistory::class);
    }

    /**
     * Get the user's saved locations.
     */
    public function savedLocations(): HasMany
    {
        return $this->hasMany(SavedLocation::class);
    }

    /**
     * Get recent searches.
     */
    public function getRecentSearches(int $limit = 10)
    {
        return $this->searchHistories()
            ->orderBy('last_searched_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get saved locations ordered by preference.
     */
    public function getSavedLocations(string $orderBy = 'sort_order')
    {
        return SavedLocation::getUserSavedLocations($this->id, $orderBy);
    }
}