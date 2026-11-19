<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

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
        'results_url',
        'discount_registration_url',
        'discount_amount',
    ];

    /**
     * El enlace de descuento solo se entrega tras validar folio y nombre.
     */
    protected $hidden = [
        'discount_registration_url',
    ];

    protected function casts(): array
    {
        return [
            'event_date' => 'date',
            'registration_opens_at' => 'datetime',
            'registration_closes_at' => 'datetime',
            'discount_amount' => 'float',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function discountParticipants(): HasMany
    {
        return $this->hasMany(RaceDiscountParticipant::class);
    }

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

    public function kitImages(): HasMany
    {
        return $this->hasMany(RaceKitImage::class)
            ->orderBy('sort_order');
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
