<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RaceSponsor extends Model
{
    use HasFactory;

    protected $fillable = [
        'race_id',
        'sponsor_id',
        'type',
        'amount',
        'benefits',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function race(): BelongsTo
    {
        return $this->belongsTo(
            Race::class
        );
    }

    public function sponsor(): BelongsTo
    {
        return $this->belongsTo(
            Sponsor::class
        );
    }

    public function payments(): HasMany
    {
        return $this->hasMany(
            SponsorPayment::class
        )->orderByDesc('paid_at');
    }
}