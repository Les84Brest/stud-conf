<?php
// app/Models/Presentation.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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

    protected $appends = ['authors', 'supervisors'];

    protected $fillable = [
        'event_id',
        'title',
        'abstract',
        'contributors',
        'file_path',
        'video_link',
        'status',
        'submitted_at',
        'approved_at',
        'rejection_reason',
    ];

    // protected $casts = [
    //     'submitted_at' => 'datetime',
    //     'approved_at' => 'datetime',
    // ];

    protected function casts(): array
    {
        return [
            'contributors' => 'array',  // ← JSON автоматически распарсится
            'submitted_at' => 'datetime',
            'approved_at' => 'datetime',
        ];
    }


    /**
     * Событие, к которому относится доклад
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }


    /**
     * Оценки доклада
     */
    public function assessments(): HasMany
    {
        return $this->hasMany(Assessment::class);
    }

    /**
     * Получить список авторов без повторений для отображения в таблицах и отчётах.
     */
    public function getUniqueAuthorsListAttribute(): string
    {
        return collect($this->authors)
            ->filter(fn ($author) => filled($author['full_name'] ?? null))
            ->unique(fn ($author) => $author['full_name'] ?? null)
            ->values()
            ->pluck('full_name')
            ->implode(', ');
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


        /**
     * Получить список авторов.
     */
    public function getAuthorsAttribute(): array
    {
        return $this->contributors['authors'] ?? [];
    }

    /**
     * Получить научных руководителей.
     */
    public function getSupervisorsAttribute(): array
    {
        return $this->contributors['supervisors'] ?? [];
    }

    /**
     * Получить основного докладчика.
     */
    public function getPresenterAttribute(): ?array
    {
        foreach ($this->authors as $author) {
            if (!empty($author['is_presenter'])) {
                return $author;
            }
        }
        return null;
    }

    /**
     * Строка со всеми авторами (через запятую).
     */
    public function getAuthorsStringAttribute(): string
    {
        return collect($this->authors)
            ->pluck('full_name')
            ->filter()
            ->implode(', ');
    }
}