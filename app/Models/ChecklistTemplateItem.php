<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ChecklistTemplateItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'checklist_template_id',
        'title',
        'description',
        'category',
        'sort_order',
        'is_required',
    ];

    protected $casts = [
        'is_required' => 'boolean',
    ];

    /**
     * Plantilla a la que pertenece.
     */
    public function template(): BelongsTo
    {
        return $this->belongsTo(
            ChecklistTemplate::class,
            'checklist_template_id'
        );
    }

    /**
     * Elementos de carrera creados desde este item.
     */
    public function raceChecklistItems(): HasMany
    {
        return $this->hasMany(
            RaceChecklistItem::class,
            'checklist_template_item_id'
        );
    }
}