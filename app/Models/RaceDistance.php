<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RaceDistance extends Model
{
    use HasFactory;

    protected $fillable = [
        'race_id',
        'name',
        'distance',
        'unit',
        'start_time',
        'capacity',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'distance' => 'decimal:2',
            'capacity' => 'integer',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function race(): BelongsTo
    {
        return $this->belongsTo(Race::class);
    }

    public function prices(): HasMany
    {
        return $this->hasMany(RacePrice::class);
    }

    public function inclusions(): HasMany
    {
        return $this->hasMany(RaceInclusion::class);
    }

    public function categories(): HasMany
    {
        return $this->hasMany(RaceCategory::class);
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(Registration::class);
    }

    public function routes(): HasMany
    {
        return $this->hasMany(RaceRoute::class);
    }

    public function results(): HasMany
    {
        return $this->hasMany(RaceResult::class);
    }
}