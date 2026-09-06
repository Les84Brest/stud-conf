<?php
// app/Models/ExpertEvent.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExpertEvent extends Model
{
    use HasFactory;

    protected $table = 'expert_event';

    protected $fillable = [
        'user_id',
        'event_id',
        'is_primary',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
    ];

    /**
     * Пользователь (эксперт)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Событие
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }
}