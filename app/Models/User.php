<?php
// app/Models/User.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable implements FilamentUser
{
    use HasApiTokens, HasFactory, Notifiable;

    // Константы ролей
    public const ROLE_ADMIN = 'admin';
    public const ROLE_EXPERT = 'expert';
    public const ROLE_OBSERVER = 'observer';

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_active',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'last_login_at' => 'datetime',
        ];
    }

    /**
     * Проверка доступа к Filament панели
     */
    public function canAccessPanel(Panel $panel): bool
    {
        return $this->role === self::ROLE_ADMIN && $this->is_active;
    }

    /**
     * Проверка ролей
     */
    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isExpert(): bool
    {
        return $this->role === self::ROLE_EXPERT;
    }

    public function isObserver(): bool
    {
        return $this->role === self::ROLE_OBSERVER;
    }

    public function isActive(): bool
    {
        return $this->is_active;
    }

    /**
     * Связи
     */
    public function events(): BelongsToMany
    {
        return $this->belongsToMany(Event::class, 'expert_event')
                    ->withPivot('is_primary')
                    ->withTimestamps();
    }

    public function assessments(): HasMany
    {
        return $this->hasMany(Assessment::class, 'expert_id');
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }

    public function assessmentLogs(): HasMany
    {
        return $this->hasMany(AssessmentLog::class);
    }

    public function updateLastLogin(): void
    {
        $this->update(['last_login_at' => now()]);
    }
}