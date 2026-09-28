<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RaceCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'race_distance_id',
        'name',
        'description',
        'min_age',
        'max_age',
        'gender',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'min_age' => 'integer',
            'max_age' => 'integer',
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
        return $this->belongsTo(
            RaceDistance::class,
            'race_distance_id'
        );
    }
}