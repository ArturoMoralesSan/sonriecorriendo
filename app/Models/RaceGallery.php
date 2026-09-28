<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RaceGallery extends Model
{
    use HasFactory;

    protected $fillable = [
        'race_id',
        'image',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * Carrera a la que pertenece la fotografía.
     */
    public function race(): BelongsTo
    {
        return $this->belongsTo(Race::class);
    }
}

