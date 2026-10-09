<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Branch extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'street',
        'exterior_number',
        'interior_number',
        'neighborhood',
        'postal_code',
        'city',
        'state',
        'references',
        'phone',
        'opening_time',
        'closing_time',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function deliveryAddresses(): HasMany
    {
        return $this->hasMany(DeliveryAddress::class);
    }

    public function getFullAddressAttribute(): string
    {
        $parts = [
            $this->street,
            'No. ' . $this->exterior_number,
        ];

        if ($this->interior_number) {
            $parts[] = 'Interior ' . $this->interior_number;
        }

        $parts[] = $this->neighborhood;
        $parts[] = 'C.P. ' . $this->postal_code;
        $parts[] = $this->city;
        $parts[] = $this->state;

        return implode(', ', $parts);
    }
}