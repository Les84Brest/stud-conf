<?php
// app/Models/PresentationAuthor.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PresentationAuthor extends Model
{
    use HasFactory;

    protected $table = 'presentation_author';

    protected $fillable = [
        'presentation_id',
        'author_id',
        'is_presenter',
        'is_corresponding',
        'order',
    ];

    protected $casts = [
        'is_presenter' => 'boolean',
        'is_corresponding' => 'boolean',
        'order' => 'integer',
    ];

    /**
     * Доклад
     */
    public function presentation(): BelongsTo
    {
        return $this->belongsTo(Presentation::class);
    }

    /**
     * Автор
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(Author::class);
    }
}