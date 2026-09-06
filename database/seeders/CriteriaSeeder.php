<?php
// database/seeders/CriteriaSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CriteriaSeeder extends Seeder
{
    public function run(): void
    {
        // Создаем группу критериев
        $groupId = DB::table('criteria_groups')->insertGetId([
            'name' => 'Основные критерии оценки',
            'description' => 'Стандартные критерии для оценки докладов на конференции',
            'sort_order' => 1,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Создаем критерии
        $criteria = [
            [
                'name' => 'Актуальность темы и проблематика',
                'key' => 'relevance',
                'max_value' => 3,
                'sort_order' => 1,
            ],
            [
                'name' => 'Научная новизна',
                'key' => 'novelty',
                'max_value' => 5,
                'sort_order' => 2,
            ],
            [
                'name' => 'Практическая значимость / внедрение',
                'key' => 'practical',
                'max_value' => 3,
                'sort_order' => 3,
            ],
            [
                'name' => 'Содержание и структура доклада',
                'key' => 'structure',
                'max_value' => 3,
                'sort_order' => 4,
            ],
            [
                'name' => 'Ответы на вопросы комиссии',
                'key' => 'answers',
                'max_value' => 2,
                'sort_order' => 5,
            ],
            [
                'name' => 'Качество презентационных материалов',
                'key' => 'presentation',
                'max_value' => 3,
                'sort_order' => 6,
            ],
        ];

        foreach ($criteria as $item) {
            DB::table('criterias')->insert([
                'criteria_group_id' => $groupId,
                'name' => $item['name'],
                'key' => $item['key'],
                'max_value' => $item['max_value'],
                'description' => null,
                'sort_order' => $item['sort_order'],
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}