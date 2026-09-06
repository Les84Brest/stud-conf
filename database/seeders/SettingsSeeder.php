<?php
// database/seeders/SettingsSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            [
                'key' => 'scoring_type',
                'value' => 'average',
                'description' => 'Способ подсчета итогового балла: average|sum',
                'group' => 'scoring',
            ],
            [
                'key' => 'conference_name',
                'value' => 'Студенческая научно-практическая конференция 2026',
                'description' => 'Название конференции',
                'group' => 'general',
            ],
            [
                'key' => 'allow_auto_save',
                'value' => 'true',
                'description' => 'Автосохранение оценок: true|false',
                'group' => 'scoring',
            ],
            [
                'key' => 'enable_comments',
                'value' => 'true',
                'description' => 'Разрешить комментарии к оценкам',
                'group' => 'scoring',
            ],
            [
                'key' => 'max_experts_per_event',
                'value' => '10',
                'description' => 'Максимальное количество экспертов на одно мероприятие',
                'group' => 'general',
            ],
        ];

        foreach ($settings as $setting) {
            DB::table('settings')->insert([
                'key' => $setting['key'],
                'value' => $setting['value'],
                'description' => $setting['description'],
                'group' => $setting['group'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}