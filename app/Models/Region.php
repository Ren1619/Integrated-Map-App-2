<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Region extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'regions';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'translations',
        'flag',
        'wikiDataId',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'translations' => 'json',
        'flag' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [];

    /**
     * Get the subregions that belong to the region.
     */
    public function subregions(): HasMany
    {
        return $this->hasMany(Subregion::class, 'region_id', 'id');
    }

    /**
     * Get the countries that belong to the region through subregions.
     * Assuming countries belong to subregions.
     */
    public function countries(): HasMany
    {
        return $this->hasMany(Country::class, 'region_id', 'id');
    }

    /**
     * Scope a query to only include active regions.
     */
    public function scopeActive($query)
    {
        return $query->where('flag', true);
    }

    /**
     * Scope a query to search regions by name.
     */
    public function scopeByName($query, $name)
    {
        return $query->where('name', 'LIKE', "%{$name}%");
    }

    /**
     * Scope a query to include regions with their subregions.
     */
    public function scopeWithSubregions($query)
    {
        return $query->with('subregions');
    }

    /**
     * Scope a query to include regions with their countries.
     */
    public function scopeWithCountries($query)
    {
        return $query->with('countries');
    }

    /**
     * Get translated name for a specific locale.
     */
    public function getTranslatedName($locale = 'en')
    {
        if (!$this->translations || !is_array($this->translations)) {
            return $this->name;
        }

        return $this->translations[$locale] ?? $this->name;
    }

    /**
     * Accessor for formatted translations.
     */
    public function getFormattedTranslationsAttribute()
    {
        if (!$this->translations) {
            return [];
        }

        return is_array($this->translations) ? $this->translations : json_decode($this->translations, true) ?? [];
    }

    /**
     * Check if region has translation for specific locale.
     */
    public function hasTranslation($locale)
    {
        $translations = $this->formatted_translations;
        return isset($translations[$locale]);
    }

    /**
     * Get all available translation locales.
     */
    public function getAvailableLocales()
    {
        $translations = $this->formatted_translations;
        return array_keys($translations);
    }

    /**
     * Count active subregions in this region.
     */
    public function getActiveSubregionsCountAttribute()
    {
        return $this->subregions()->active()->count();
    }

    /**
     * Count active countries in this region.
     */
    public function getActiveCountriesCountAttribute()
    {
        return $this->countries()->active()->count();
    }
}