<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RaceExpense extends Model
{
    protected $fillable = [
        'race_id',
        'created_by',
        'title',
        'category',
        'supplier',
        'description',
        'amount',
        'expense_date',
        'status',
        'payment_method',
        'reference',
        'paid_at',
        'receipt_path',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'expense_date' => 'date',
            'paid_at' => 'datetime',
        ];
    }

    /**
     * Carrera a la que pertenece el gasto.
     */
    public function race(): BelongsTo
    {
        return $this->belongsTo(Race::class);
    }

    /**
     * Usuario que registró el gasto.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
