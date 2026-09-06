<?php
// app/Models/Author.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Author extends Model
{
    use HasFactory;

    protected $fillable = [
        'full_name',
        'email',
        'university',
        'faculty',
        'group_number',
        'phone',
        'degree',
        'position',
    ];

    protected $casts = [
        'email' => 'string',
    ];

    /**
     * Доклады автора
     */
    public function presentations(): BelongsToMany
    {
        return $this->belongsToMany(Presentation::class, 'presentation_author')
                    ->withPivot('is_presenter', 'is_corresponding', 'order')
                    ->withTimestamps()
                    ->orderBy('pivot_order');
    }

    /**
     * Доклады, где автор является докладчиком
     */
    public function presenterPresentations(): BelongsToMany
    {
        return $this->presentations()->wherePivot('is_presenter', true);
    }

    /**
     * Получить полное имя с дополнительной информацией
     */
    public function getFullNameWithDetailsAttribute(): string
    {
        $parts = [$this->full_name];
        if ($this->university) {
            $parts[] = "({$this->university})";
        }
        if ($this->group_number) {
            $parts[] = "гр. {$this->group_number}";
        }
        return implode(' ', $parts);
    }

    /**
     * Проверка, является ли автор докладчиком в конкретном докладе
     */
    public function isPresenterFor(Presentation $presentation): bool
    {
        return $this->presentations()
                    ->where('presentation_id', $presentation->id)
                    ->wherePivot('is_presenter', true)
                    ->exists();
    }
}