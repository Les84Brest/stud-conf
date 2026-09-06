<?php
// app/Models/CriteriaGroup.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CriteriaGroup extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Критерии в этой группе
     */
    public function criterias(): HasMany
    {
        return $this->hasMany(Criteria::class);
    }

    /**
     * Активные критерии в группе
     */
    public function activeCriterias(): HasMany
    {
        return $this->criterias()->where('is_active', true);
    }
}