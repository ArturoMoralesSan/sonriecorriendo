<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'image',
        'type',
        'year',
        'price',
        'stock',
        'is_active',
    ];

    protected $casts = [
        'year' => 'integer',
        'price' => 'decimal:2',
        'stock' => 'integer',
        'is_active' => 'boolean',
    ];
}
