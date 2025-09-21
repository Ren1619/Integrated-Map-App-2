<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class State extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'states';

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
        'country_id',
        'country_code',
        'fips_code',
        'iso2',
        'iso3166_2',
        'type',
        'level',
        'parent_id',
        'native',
        'latitude',
        'longitude',
        'timezone',
        'flag',
        'wikiDataId',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'flag' => 'boolean',
        'level' => 'integer',
        'parent_id' => 'integer',
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
     * Get the country that owns the state.
     */
    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'country_id', 'id');
    }

    /**
     * Get the parent state (for hierarchical states).
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(State::class, 'parent_id', 'id');
    }

    /**
     * Get the child states (for hierarchical states).
     */
    public function children(): HasMany
    {
        return $this->hasMany(State::class, 'parent_id', 'id');
    }

    /**
     * Get the cities that belong to the state.
     */
    public function cities(): HasMany
    {
        return $this->hasMany(City::class, 'state_id', 'id');
    }

    /**
     * Scope a query to only include active states.
     */
    public function scopeActive($query)
    {
        return $query->where('flag', true);
    }

    /**
     * Scope a query to filter states by country.
     */
    public function scopeByCountry($query, $countryId)
    {
        return $query->where('country_id', $countryId);
    }

    /**
     * Scope a query to filter states by country code.
     */
    public function scopeByCountryCode($query, $countryCode)
    {
        return $query->where('country_code', strtoupper($countryCode));
    }

    /**
     * Scope a query to filter states by type.
     */
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope a query to filter states by level.
     */
    public function scopeByLevel($query, $level)
    {
        return $query->where('level', $level);
    }

    /**
     * Scope a query to get only parent states (no parent_id).
     */
    public function scopeParentStates($query)
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Scope a query to get only child states (has parent_id).
     */
    public function scopeChildStates($query)
    {
        return $query->whereNotNull('parent_id');
    }

    /**
     * Scope a query to search states by name.
     */
    public function scopeByName($query, $name)
    {
        return $query->where('name', 'LIKE', "%{$name}%");
    }

    /**
     * Scope a query to filter by ISO2 code.
     */
    public function scopeByIso2($query, $iso2)
    {
        return $query->where('iso2', strtoupper($iso2));
    }

    /**
     * Scope a query to filter by ISO3166-2 code.
     */
    public function scopeByIso3166_2($query, $iso3166_2)
    {
        return $query->where('iso3166_2', strtoupper($iso3166_2));
    }

    /**
     * Scope a query to filter by FIPS code.
     */
    public function scopeByFipsCode($query, $fipsCode)
    {
        return $query->where('fips_code', strtoupper($fipsCode));
    }

    /**
     * Get state coordinates as array.
     */
    public function getCoordinates()
    {
        return [
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
        ];
    }

    /**
     * Check if this state is a parent state.
     */
    public function isParentState()
    {
        return is_null($this->parent_id);
    }

    /**
     * Check if this state is a child state.
     */
    public function isChildState()
    {
        return !is_null($this->parent_id);
    }

    /**
     * Get all ancestor states (recursive parent lookup).
     */
    public function getAncestors()
    {
        $ancestors = collect();
        $current = $this;

        while ($current->parent) {
            $ancestors->push($current->parent);
            $current = $current->parent;
        }

        return $ancestors;
    }

    /**
     * Get all descendant states (recursive children lookup).
     */
    public function getDescendants()
    {
        $descendants = collect();
        
        foreach ($this->children as $child) {
            $descendants->push($child);
            $descendants = $descendants->merge($child->getDescendants());
        }

        return $descendants;
    }

    /**
     * Get the full hierarchical path as string.
     */
    public function getHierarchicalPath($separator = ' > ')
    {
        $ancestors = $this->getAncestors()->reverse();
        $path = $ancestors->pluck('name')->toArray();
        $path[] = $this->name;
        
        return implode($separator, $path);
    }

    /**
     * Count active cities in this state.
     */
    public function getActiveCitiesCountAttribute()
    {
        return $this->cities()->active()->count();
    }

    /**
     * Count active child states.
     */
    public function getActiveChildrenCountAttribute()
    {
        return $this->children()->active()->count();
    }

    /**
     * Get the display name (native name if available, otherwise name).
     */
    public function getDisplayNameAttribute()
    {
        return $this->native ?: $this->name;
    }

    /**
     * Get formatted state codes.
     */
    public function getStateCodesAttribute()
    {
        return [
            'iso2' => $this->iso2,
            'iso3166_2' => $this->iso3166_2,
            'fips_code' => $this->fips_code,
        ];
    }
}