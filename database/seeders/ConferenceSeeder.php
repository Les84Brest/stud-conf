<?php
// database/seeders/ConferenceSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ConferenceSeeder extends Seeder
{
    public function run(): void
    {
        // Создаем конференцию
        $conferenceId = DB::table('conferences')->insertGetId([
            'title' => 'Студенческая научно-практическая конференция 2026',
            'slug' => Str::slug('Студенческая научно-практическая конференция 2026'),
            'description' => 'Ежегодная студенческая конференция с международным участием',
            'start_date' => '2026-04-15',
            'end_date' => '2026-04-17',
            'location' => 'Брест, ул. Московская 267',
            'organizer' => 'Брестский государственный технический университет',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Создаем события (секции)
        $events = [
            [
                'title' => 'Бухгалтерский учет и аудит - 1',
                'slug' => 'buai-1',
                'type' => 'section',
                'description' => 'Секция по бухгалтерскому учету и аудиту',
                'room' => 'Аудитория 101',
            ],
            [
                'title' => 'Мировая экономика',
                'slug' => 'buai-2',
                'type' => 'section',
                'description' => 'Секция по мировой экономике',
                'room' => 'Аудитория 102',
            ],
            [
                'title' => 'Финансы и кредит',
                'slug' => 'finance',
                'type' => 'section',
                'description' => 'Секция по финансам и кредиту',
                'room' => 'Аудитория 201',
            ],
            [
                'title' => 'Цифровая экономика',
                'slug' => 'digital-economy',
                'type' => 'section',
                'description' => 'Секция по цифровой экономике',
                'room' => 'Аудитория 202',
            ],
            [
                'title' => 'Таможенное дело',
                'slug' => 'customs',
                'type' => 'section',
                'description' => 'Секция по таможенному делу',
                'room' => 'Аудитория 203',
            ],
            [
                'title' => 'Олимпиада по экономике',
                'slug' => 'olympiad-economy',
                'type' => 'olympiad',
                'description' => 'Олимпиада по экономике для студентов',
                'room' => 'Аудитория 301',
            ],
            [
                'title' => 'Круглый стол: Актуальные проблемы экономики',
                'slug' => 'round-table-economy',
                'type' => 'round_table',
                'description' => 'Круглый стол по актуальным проблемам экономики',
                'room' => 'Конференц-зал',
            ],
        ];

        foreach ($events as $event) {
            DB::table('events')->insert([
                'conference_id' => $conferenceId,
                'title' => $event['title'],
                'slug' => $event['slug'],
                'type' => $event['type'],
                'description' => $event['description'],
                'max_score' => 19,
                'room' => $event['room'],
                'start_time' => now()->addDays(rand(1, 30)),
                'end_time' => now()->addDays(rand(31, 60)),
                'sort_order' => 0,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}