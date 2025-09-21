<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Country extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'countries';

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
        'iso3',
        'numeric_code',
        'iso2',
        'phonecode',
        'capital',
        'currency',
        'currency_name',
        'currency_symbol',
        'tld',
        'native',
        'region',
        'region_id',
        'subregion',
        'subregion_id',
        'nationality',
        'timezones',
        'translations',
        'latitude',
        'longitude',
        'emoji',
        'emojiU',
        'flag',
        'wikiDataId',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'timezones' => 'json',
        'translations' => 'json',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
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
     * Get the region that owns the country.
     */
    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class, 'region_id', 'id');
    }

    /**
     * Get the subregion that owns the country.
     */
    public function subregion(): BelongsTo
    {
        return $this->belongsTo(Subregion::class, 'subregion_id', 'id');
    }

    /**
     * Get the states that belong to the country.
     */
    public function states(): HasMany
    {
        return $this->hasMany(State::class, 'country_id', 'id');
    }

    /**
     * Get the cities that belong to the country.
     */
    public function cities(): HasMany
    {
        return $this->hasMany(City::class, 'country_id', 'id');
    }

    /**
     * Scope a query to only include active countries.
     */
    public function scopeActive($query)
    {
        return $query->where('flag', true);
    }

    /**
     * Scope a query to filter countries by region.
     */
    public function scopeByRegion($query, $regionId)
    {
        return $query->where('region_id', $regionId);
    }

    /**
     * Scope a query to filter countries by subregion.
     */
    public function scopeBySubregion($query, $subregionId)
    {
        return $query->where('subregion_id', $subregionId);
    }

    /**
     * Scope a query to filter countries by ISO2 code.
     */
    public function scopeByIso2($query, $iso2)
    {
        return $query->where('iso2', strtoupper($iso2));
    }

    /**
     * Scope a query to filter countries by ISO3 code.
     */
    public function scopeByIso3($query, $iso3)
    {
        return $query->where('iso3', strtoupper($iso3));
    }

    /**
     * Scope a query to search countries by name.
     */
    public function scopeByName($query, $name)
    {
        return $query->where('name', 'LIKE', "%{$name}%");
    }

    /**
     * Scope a query to filter countries by currency.
     */
    public function scopeByCurrency($query, $currency)
    {
        return $query->where('currency', strtoupper($currency));
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
     * Accessor for formatted timezones.
     */
    public function getFormattedTimezonesAttribute()
    {
        if (!$this->timezones) {
            return [];
        }

        return is_array($this->timezones) ? $this->timezones : json_decode($this->timezones, true) ?? [];
    }

    /**
     * Check if country has translation for specific locale.
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
     * Get primary timezone (first one in the list).
     */
    public function getPrimaryTimezone()
    {
        $timezones = $this->formatted_timezones;
        return !empty($timezones) ? $timezones[0] : null;
    }

    /**
     * Get full currency information.
     */
    public function getCurrencyInfo()
    {
        return [
            'code' => $this->currency,
            'name' => $this->currency_name,
            'symbol' => $this->currency_symbol,
        ];
    }

    /**
     * Get country coordinates as array.
     */
    public function getCoordinates()
    {
        return [
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
        ];
    }

    /**
     * Count active states in this country.
     */
    public function getActiveStatesCountAttribute()
    {
        return $this->states()->active()->count();
    }

    /**
     * Count active cities in this country.
     */
    public function getActiveCitiesCountAttribute()
    {
        return $this->cities()->active()->count();
    }

    /**
     * Get formatted phone code with plus sign.
     */
    public function getFormattedPhonecodeAttribute()
    {
        return $this->phonecode ? '+' . $this->phonecode : null;
    }
}