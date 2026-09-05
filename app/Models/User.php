<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    // Константы ролей
    public const ROLE_ADMIN = 'admin';
    public const ROLE_EXPERT = 'expert';
    public const ROLE_OBSERVER = 'observer';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_active',
        'last_login_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
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
     * Проверка, является ли пользователь администратором
     */
    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    /**
     * Проверка, является ли пользователь экспертом
     */
    public function isExpert(): bool
    {
        return $this->role === self::ROLE_EXPERT;
    }

    /**
     * Проверка, является ли пользователь наблюдателем
     */
    public function isObserver(): bool
    {
        return $this->role === self::ROLE_OBSERVER;
    }

    /**
     * Проверка активности пользователя
     */
    public function isActive(): bool
    {
        return $this->is_active && $this->role !== self::ROLE_ADMIN;
    }

    /**
     * Получить все события, где пользователь является экспертом
     */
    public function events(): HasMany
    {
        return $this->belongsToMany(Event::class, 'expert_event')
                    ->withPivot('is_primary')
                    ->withTimestamps();
    }

    /**
     * Получить все оценки пользователя
     */
    public function assessments(): HasMany
    {
        return $this->hasMany(Assessment::class, 'expert_id');
    }

    /**
     * Получить логи активности пользователя
     */
    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }

    /**
     * Обновление времени последнего входа
     */
    public function updateLastLogin(): void
    {
        $this->update(['last_login_at' => now()]);
    }
}