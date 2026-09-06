<?php
// app/Models/EventCriteria.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventCriteria extends Model
{
    use HasFactory;

    protected $table = 'event_criteria';

    protected $fillable = [
        'event_id',
        'criteria_id',
        'max_value_override',
        'sort_order',
    ];

    protected $casts = [
        'max_value_override' => 'integer',
        'sort_order' => 'integer',
    ];

    /**
     * Событие
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * Критерий
     */
    public function criteria(): BelongsTo
    {
        return $this->belongsTo(Criteria::class);
    }

    /**
     * Получить максимальное значение (с учетом переопределения)
     */
    public function getEffectiveMaxValue(): int
    {
        return $this->max_value_override ?? $this->criteria->max_value;
    }
}