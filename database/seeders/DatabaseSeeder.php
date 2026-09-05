<?php
// database/seeders/DatabaseSeeder.php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Создаем администратора
        User::create([
            'name' => 'Администратор',
            'email' => 'admin@conference.ru',
            'password' => Hash::make('admin123'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        // Создаем экспертов
        User::create([
            'name' => 'Иван Петров',
            'email' => 'expert1@conference.ru',
            'password' => Hash::make('expert123'),
            'role' => 'expert',
            'is_active' => true,
        ]);

        User::create([
            'name' => 'Мария Сидорова',
            'email' => 'expert2@conference.ru',
            'password' => Hash::make('expert123'),
            'role' => 'expert',
            'is_active' => true,
        ]);

        // Создаем наблюдателя
        User::create([
            'name' => 'Анна Козлова',
            'email' => 'observer@conference.ru',
            'password' => Hash::make('observer123'),
            'role' => 'observer',
            'is_active' => true,
        ]);
        
        // Создаем дополнительных тестовых пользователей
        User::factory()->count(5)->create([
            'role' => 'expert',
            'is_active' => true,
        ]);
    }
}