<?php
// app/Models/Setting.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
        'description',
        'group',
    ];

    protected $casts = [
        'value' => 'string',
    ];

    /**
     * Получить значение настройки по ключу
     */
    public static function getValue(string $key, $default = null)
    {
        $setting = static::where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }

    /**
     * Установить значение настройки
     */
    public static function setValue(string $key, string $value): void
    {
        static::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );
    }

    /**
     * Получить все настройки группы
     */
    public static function getGroup(string $group): array
    {
        return static::where('group', $group)
                    ->pluck('value', 'key')
                    ->toArray();
    }
}