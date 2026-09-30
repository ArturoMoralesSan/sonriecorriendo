<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RacePrice extends Model
{
    use HasFactory;

    protected $fillable = [
        'race_distance_id',
        'name',
        'price',
        'starts_at',
        'ends_at',
        'capacity',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
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

    public function distance(): BelongsTo
    {
        return $this->belongsTo(RaceDistance::class, 'race_distance_id');
    }
}
