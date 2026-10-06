<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RouteMedia extends Model
{
    use HasFactory;

    protected $table = 'route_media';

    protected $fillable = [
        'route_id',
        'type',
        'file',
        'title',
        'sort_order',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    public function route(): BelongsTo
    {
        return $this->belongsTo(Route::class);
    }
}
