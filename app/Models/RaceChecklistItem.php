<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RaceChecklistItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'race_id',
        'checklist_template_item_id',
        'title',
        'description',
        'category',
        'status',
        'due_date',
        'completed_at',
        'assigned_to',
        'notes',
        'sort_order',
    ];

    protected $casts = [
        'due_date' => 'date',
        'completed_at' => 'datetime',
    ];

    /**
     * Carrera.
     */
    public function race(): BelongsTo
    {
        return $this->belongsTo(Race::class);
    }

    /**
     * Item de plantilla del que proviene.
     */
    public function templateItem(): BelongsTo
    {
        return $this->belongsTo(
            ChecklistTemplateItem::class,
            'checklist_template_item_id'
        );
    }

    /**
     * Usuario responsable.
     */
    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'assigned_to'
        );
    }

    /**
     * Indica si está completado.
     */
    public function getIsCompletedAttribute(): bool
    {
        return $this->status === 'completed';
    }
}