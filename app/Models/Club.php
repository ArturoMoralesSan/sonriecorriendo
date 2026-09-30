<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Club extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'logo',
        'responsible',
        'phone',
        'email',
        'city',
        'address',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (Club $club) {
            if (empty($club->slug)) {
                $club->slug = Str::slug($club->name);
            }
        });

        static::updating(function (Club $club) {
            if ($club->isDirty('name') && empty($club->slug)) {
                $club->slug = Str::slug($club->name);
            }
        });
    }
}
