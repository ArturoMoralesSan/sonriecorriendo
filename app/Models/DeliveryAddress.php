<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeliveryAddress extends Model
{
    use HasFactory;

    public const METHOD_HOME = 'home_delivery';

    public const METHOD_BRANCH = 'branch_pickup';

    protected $fillable = [
        'sale_id',
        'delivery_method',
        'branch_id',
        'branch_name',
        'branch_address',
        'street',
        'exterior_number',
        'interior_number',
        'neighborhood',
        'postal_code',
        'city',
        'state',
        'references',
    ];

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function getFullAddressAttribute(): ?string
    {
        if ($this->delivery_method === self::METHOD_BRANCH) {
            return $this->branch_address;
        }

        $parts = [
            $this->street,
            $this->exterior_number
                ? 'No. ' . $this->exterior_number
                : null,
            $this->interior_number
                ? 'Interior ' . $this->interior_number
                : null,
            $this->neighborhood,
            $this->postal_code
                ? 'C.P. ' . $this->postal_code
                : null,
            $this->city,
            $this->state,
        ];

        return implode(', ', array_filter($parts));
    }
}