<?php
// app/Models/Assessment.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Assessment extends Model
{
    use HasFactory;

    protected $fillable = [
        'presentation_id',
        'expert_id',
        'event_id',
        'criteria_values',
        'total_score',
        'comment',
        'saved_at',
    ];

    protected $casts = [
        'criteria_values' => 'array',
        'saved_at' => 'datetime',
        'total_score' => 'integer',
    ];

    /**
     * Доклад, который оценивается
     */
    public function presentation(): BelongsTo
    {
        return $this->belongsTo(Presentation::class);
    }

    /**
     * Эксперт, который оценивает
     */
    public function expert(): BelongsTo
    {
        return $this->belongsTo(User::class, 'expert_id');
    }

    /**
     * Событие, в рамках которого выставлена оценка
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * Логи изменений этой оценки
     */
    public function logs(): HasMany
    {
        return $this->hasMany(AssessmentLog::class);
    }

    /**
     * Получить значение конкретного критерия
     */
    public function getCriteriaValue(string $key): ?int
    {
        return $this->criteria_values[$key] ?? null;
    }

    /**
     * Проверить, является ли оценка полной
     */
    public function isComplete(): bool
    {
        $event = $this->event;
        $criteriaCount = $event->criteria()->count();
        $filledCount = count(array_filter($this->criteria_values ?? []));
        
        return $filledCount === $criteriaCount;
    }

    /**
     * Получить процент от максимального балла
     */
    public function getPercentageAttribute(): float
    {
        $maxScore = $this->event->getCalculatedMaxScore();
        if ($maxScore === 0) {
            return 0;
        }
        return round(($this->total_score / $maxScore) * 100, 2);
    }
}