<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RaceInclusion extends Model
{
    use HasFactory;

    protected $fillable = [
        'race_distance_id',
        'name',
        'description',
        'type',
        'included',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'included' => 'boolean',
            'sort_order' => 'integer',
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