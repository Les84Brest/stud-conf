<?php
// app/Models/Presentation.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Presentation extends Model
{
    use HasFactory;

    // Константы статусов
    const STATUS_DRAFT = 'draft';
    const STATUS_SUBMITTED = 'submitted';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';
    const STATUS_PRESENTED = 'presented';

    protected $fillable = [
        'event_id',
        'title',
        'abstract',
        'file_path',
        'video_link',
        'status',
        'submitted_at',
        'approved_at',
        'rejection_reason',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'approved_at' => 'datetime',
    ];

    /**
     * Событие, к которому относится доклад
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * Авторы доклада
     */
    public function authors(): BelongsToMany
    {
        return $this->belongsToMany(Author::class, 'presentation_author')
                    ->withPivot('is_presenter', 'is_corresponding', 'order')
                    ->withTimestamps()
                    ->orderBy('pivot_order');
    }

    /**
     * Докладчик (основной автор)
     */
    public function presenter(): BelongsToMany
    {
        return $this->authors()->wherePivot('is_presenter', true);
    }

    /**
     * Ответственный автор
     */
    public function correspondingAuthor(): BelongsToMany
    {
        return $this->authors()->wherePivot('is_corresponding', true);
    }

    /**
     * Оценки доклада
     */
    public function assessments(): HasMany
    {
        return $this->hasMany(Assessment::class);
    }

    /**
     * Получить средний балл
     */
    public function getAverageScoreAttribute(): float
    {
        return $this->assessments()->avg('total_score') ?? 0;
    }

    /**
     * Получить сумму всех оценок
     */
    public function getTotalScoreAttribute(): float
    {
        return $this->assessments()->sum('total_score') ?? 0;
    }

    /**
     * Получить количество оценок
     */
    public function getAssessmentsCountAttribute(): int
    {
        return $this->assessments()->count();
    }

    /**
     * Проверить, одобрен ли доклад
     */
    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    /**
     * Проверить, отклонен ли доклад
     */
    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }

    /**
     * Проверить, представлен ли доклад
     */
    public function isPresented(): bool
    {
        return $this->status === self::STATUS_PRESENTED;
    }

    /**
     * Получить статус на русском
     */
    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            self::STATUS_DRAFT => 'Черновик',
            self::STATUS_SUBMITTED => 'На рассмотрении',
            self::STATUS_APPROVED => 'Одобрен',
            self::STATUS_REJECTED => 'Отклонен',
            self::STATUS_PRESENTED => 'Представлен',
            default => $this->status,
        };
    }

    /**
     * Получить цвет статуса для UI
     */
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            self::STATUS_DRAFT => 'gray',
            self::STATUS_SUBMITTED => 'yellow',
            self::STATUS_APPROVED => 'green',
            self::STATUS_REJECTED => 'red',
            self::STATUS_PRESENTED => 'blue',
            default => 'gray',
        };
    }
}