<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SponsorPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'race_sponsor_id',
        'amount',
        'paid_at',
        'payment_method',
        'reference',
        'notes',
        'receipt',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'paid_at' => 'date',
        ];
    }

    public function raceSponsor(): BelongsTo
    {
        return $this->belongsTo(
            RaceSponsor::class
        );
    }
}
