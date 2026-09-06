<?php
// app/Traits/HasStatus.php

namespace App\Traits;

trait HasStatus
{
    /**
     * Проверка статуса
     */
    public function hasStatus(string $status): bool
    {
        return $this->status === $status;
    }

    /**
     * Получить список всех статусов
     */
    public static function getStatuses(): array
    {
        if (defined('static::STATUSES')) {
            return static::STATUSES;
        }
        
        return [];
    }

    /**
     * Получить цвет статуса
     */
    public function getStatusColorAttribute(): string
    {
        $colors = [
            'draft' => 'gray',
            'submitted' => 'yellow',
            'approved' => 'green',
            'rejected' => 'red',
            'presented' => 'blue',
        ];

        return $colors[$this->status] ?? 'gray';
    }
}