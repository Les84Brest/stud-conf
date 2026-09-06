<?php
// app/Models/Conference.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Conference extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'description',
        'start_date',
        'end_date',
        'location',
        'logo_path',
        'organizer',
        'is_active',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
    ];

    /**
     * Получить все события конференции
     */
    public function events(): HasMany
    {
        return $this->hasMany(Event::class);
    }

    /**
     * Получить активные события
     */
    public function activeEvents(): HasMany
    {
        return $this->events()->where('is_active', true);
    }

    /**
     * Проверка, активна ли конференция
     */
    public function isActive(): bool
    {
        return $this->is_active;
    }

    /**
     * Получить количество событий
     */
    public function getEventsCountAttribute(): int
    {
        return $this->events()->count();
    }

    /**
     * Получить URL конференции
     */
    public function getUrlAttribute(): string
    {
        return route('conferences.show', $this->slug);
    }
}