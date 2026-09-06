<?php
// app/Models/Event.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Event extends Model
{
    use HasFactory;

    // Константы типов событий
    const TYPE_SECTION = 'section';
    const TYPE_OLYMPIAD = 'olympiad';
    const TYPE_ROUND_TABLE = 'round_table';
    const TYPE_MASTER_CLASS = 'master_class';

    protected $fillable = [
        'conference_id',
        'title',
        'slug',
        'type',
        'description',
        'max_score',
        'room',
        'start_time',
        'end_time',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'is_active' => 'boolean',
        'max_score' => 'integer',
        'sort_order' => 'integer',
    ];

    /**
     * Конференция, к которой относится событие
     */
    public function conference(): BelongsTo
    {
        return $this->belongsTo(Conference::class);
    }

    /**
     * Эксперты, назначенные на событие
     */
    public function experts(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'expert_event')
                    ->withPivot('is_primary')
                    ->withTimestamps();
    }

    /**
     * Главные эксперты события
     */
    public function primaryExperts(): BelongsToMany
    {
        return $this->experts()->wherePivot('is_primary', true);
    }

    /**
     * Доклады на событии
     */
    public function presentations(): HasMany
    {
        return $this->hasMany(Presentation::class);
    }

    /**
     * Критерии оценки для события
     */
    public function criteria(): BelongsToMany
    {
        return $this->belongsToMany(Criteria::class, 'event_criteria')
                    ->withPivot('max_value_override', 'sort_order')
                    ->withTimestamps();
    }

    /**
     * Оценки для этого события
     */
    public function assessments(): HasMany
    {
        return $this->hasMany(Assessment::class);
    }

    /**
     * Проверка, является ли событие секцией
     */
    public function isSection(): bool
    {
        return $this->type === self::TYPE_SECTION;
    }

    /**
     * Проверка, является ли событие олимпиадой
     */
    public function isOlympiad(): bool
    {
        return $this->type === self::TYPE_OLYMPIAD;
    }

    /**
     * Проверка, является ли событие круглым столом
     */
    public function isRoundTable(): bool
    {
        return $this->type === self::TYPE_ROUND_TABLE;
    }

    /**
     * Получить максимальный балл с учетом переопределения критериев
     */
    public function getCalculatedMaxScore(): int
    {
        $total = 0;
        foreach ($this->criteria as $criterion) {
            $total += $criterion->pivot->max_value_override ?? $criterion->max_value;
        }
        return $total > 0 ? $total : $this->max_score;
    }

    /**
     * Получить тип события на русском
     */
    public function getTypeLabelAttribute(): string
    {
        return match($this->type) {
            self::TYPE_SECTION => 'Секция',
            self::TYPE_OLYMPIAD => 'Олимпиада',
            self::TYPE_ROUND_TABLE => 'Круглый стол',
            self::TYPE_MASTER_CLASS => 'Мастер-класс',
            default => $this->type,
        };
    }
}