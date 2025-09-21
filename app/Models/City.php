<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class City extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'cities';

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
        'state_id',
        'state_code',
        'country_id',
        'country_code',
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
     * Get the state that owns the city.
     */
    public function state(): BelongsTo
    {
        return $this->belongsTo(State::class, 'state_id', 'id');
    }

    /**
     * Get the country that owns the city.
     */
    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'country_id', 'id');
    }

    /**
     * Scope a query to only include active cities.
     */
    public function scopeActive($query)
    {
        return $query->where('flag', true);
    }

    /**
     * Scope a query to filter cities by country.
     */
    public function scopeByCountry($query, $countryId)
    {
        return $query->where('country_id', $countryId);
    }

    /**
     * Scope a query to filter cities by state.
     */
    public function scopeByState($query, $stateId)
    {
        return $query->where('state_id', $stateId);
    }

    /**
     * Scope a query to filter cities by country code.
     */
    public function scopeByCountryCode($query, $countryCode)
    {
        return $query->where('country_code', $countryCode);
    }
}