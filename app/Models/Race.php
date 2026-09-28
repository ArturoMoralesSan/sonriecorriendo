<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Race extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'event_date',
        'start_time',
        'end_time',
        'location',
        'address',
        'city',
        'state',
        'country',
        'logo',
        'banner',
        'registration_opens_at',
        'registration_closes_at',
        'status',
        'terms_and_conditions',
        'notes',
        'results_url'
    ];

    protected function casts(): array
    {
        return [
            'event_date' => 'date',
            'registration_opens_at' => 'datetime',
            'registration_closes_at' => 'datetime',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function distances(): HasMany
    {
        return $this->hasMany(RaceDistance::class);
    }

    public function sponsors(): HasMany
    {
        return $this->hasMany(RaceSponsor::class);
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(RaceExpense::class);
    }

    public function gallery(): HasMany
    {
        return $this->hasMany(RaceGallery::class);
    }

    /**
     * Checklist de la carrera.
     */
    public function checklistItems(): HasMany
    {
        return $this->hasMany(RaceChecklistItem::class)
            ->orderBy('sort_order');
    }
}