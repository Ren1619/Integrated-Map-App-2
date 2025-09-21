<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subregion extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'subregions';

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
        'region_id',
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
     * Get the region that owns the subregion.
     */
    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class, 'region_id', 'id');
    }

    /**
     * Get the countries that belong to the subregion.
     * Assuming countries table has a subregion_id column.
     */
    public function countries(): HasMany
    {
        return $this->hasMany(Country::class, 'subregion_id', 'id');
    }

    /**
     * Scope a query to only include active subregions.
     */
    public function scopeActive($query)
    {
        return $query->where('flag', true);
    }

    /**
     * Scope a query to filter subregions by region.
     */
    public function scopeByRegion($query, $regionId)
    {
        return $query->where('region_id', $regionId);
    }

    /**
     * Scope a query to search subregions by name.
     */
    public function scopeByName($query, $name)
    {
        return $query->where('name', 'LIKE', "%{$name}%");
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
     * Check if subregion has translation for specific locale.
     */
    public function hasTranslation($locale)
    {
        $translations = $this->formatted_translations;
        return isset($translations[$locale]);
    }
}