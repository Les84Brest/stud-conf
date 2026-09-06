<?php
// app/Models/Criteria.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Criteria extends Model
{
    use HasFactory;

    protected $table = 'criterias';

    protected $fillable = [
        'criteria_group_id',
        'name',
        'key',
        'max_value',
        'description',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'max_value' => 'integer',
        'sort_order' => 'integer',
    ];

    /**
     * Группа критериев
     */
    public function group(): BelongsTo
    {
        return $this->belongsTo(CriteriaGroup::class, 'criteria_group_id');
    }

    /**
     * События, использующие этот критерий
     */
    public function events(): BelongsToMany
    {
        return $this->belongsToMany(Event::class, 'event_criteria')
                    ->withPivot('max_value_override', 'sort_order')
                    ->withTimestamps();
    }

    /**
     * Получить максимальное значение для события
     */
    public function getMaxValueForEvent(Event $event): int
    {
        $pivot = $this->events()->where('event_id', $event->id)->first();
        if ($pivot && $pivot->pivot->max_value_override) {
            return $pivot->pivot->max_value_override;
        }
        return $this->max_value;
    }
}