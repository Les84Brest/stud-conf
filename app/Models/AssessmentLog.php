<?php
// app/Models/AssessmentLog.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssessmentLog extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'assessment_id',
        'user_id',
        'old_values',
        'new_values',
        'action',
        'comment',
        'created_at',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'created_at' => 'datetime',
    ];

    /**
     * Оценка, к которой относится лог
     */
    public function assessment(): BelongsTo
    {
        return $this->belongsTo(Assessment::class);
    }

    /**
     * Пользователь, который произвел изменение
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}