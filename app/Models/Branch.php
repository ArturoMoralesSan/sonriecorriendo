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
        'latitude',
        'longitude',
        'phone',
        'opening_time',
        'closing_time',
        'is_active',
    ];

    protected $appends = [
        'directions_url',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'latitude' => 'float',
            'longitude' => 'float',
        ];
    }

    public function deliveryAddresses(): HasMany
    {
        return $this->hasMany(DeliveryAddress::class);
    }

    public function hasCoordinates(): bool
    {
        return $this->latitude !== null && $this->longitude !== null;
    }

    /**
     * Enlace de "Cómo llegar" en Google Maps (no requiere API key).
     * Usa coordenadas si existen; si no, la dirección completa.
     */
    public function getDirectionsUrlAttribute(): string
    {
        $destination = $this->hasCoordinates()
            ? $this->latitude . ',' . $this->longitude
            : $this->full_address;

        return 'https://www.google.com/maps/dir/?api=1&destination='
            . rawurlencode($destination);
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