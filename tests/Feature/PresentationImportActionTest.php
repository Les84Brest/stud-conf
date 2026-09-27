<?php

namespace Tests\Feature;

use App\Filament\Actions\ImportPresentationsAction;
use App\Models\Conference;
use App\Models\Event;
use App\Models\Presentation;
use App\Services\PresentationImportService;
use Tests\TestCase;

class PresentationImportActionTest extends TestCase
{
    public function test_it_reads_excel_rows_with_a_valid_import_class(): void
    {
        $file = tempnam(sys_get_temp_dir(), 'excel-import-');
        file_put_contents($file, "ФИО докладчика,Название доклада\nИванов Иван,Тестовый доклад\n");

        $rows = ImportPresentationsAction::readRowsFromFile($file);

        $this->assertNotEmpty($rows);
        $this->assertCount(2, $rows[0] ?? []);
        $this->assertSame('Иванов Иван', $rows[1]['ФИО докладчика'] ?? null);
        $this->assertSame('Тестовый доклад', $rows[1]['Название доклада'] ?? null);

        unlink($file);
    }

    public function test_it_saves_contributors_data_when_creating_presentation(): void
    {
        $presentation = Presentation::create([
            'event_id' => Event::create([
                'conference_id' => Conference::create([
                    'title' => 'Тестовая конференция',
                    'slug' => 'test-conference',
                ])->id,
                'title' => 'Тестовое событие',
                'slug' => 'test-event',
                'type' => Event::TYPE_SECTION,
            ])->id,
            'title' => 'Тестовый доклад',
            'abstract' => 'Описание',
            'contributors' => [
                'authors' => [
                    [
                        'full_name' => 'Иванов Иван Иванович',
                        'is_presenter' => true,
                        'is_corresponding' => true,
                        'order' => 0,
                    ],
                ],
                'supervisors' => [
                    [
                        'full_name' => 'Петров Петр Петрович',
                        'degree' => 'кандидат наук',
                        'position' => 'доцент',
                        'order' => 0,
                    ],
                ],
            ],
            'status' => Presentation::STATUS_SUBMITTED,
            'submitted_at' => now(),
        ]);

        $this->assertSame('Иванов Иван Иванович', $presentation->contributors['authors'][0]['full_name']);
        $this->assertSame('Петров Петр Петрович', $presentation->contributors['supervisors'][0]['full_name']);
    }

    public function test_it_skips_empty_rows_during_import(): void
    {
        $conference = Conference::create([
            'title' => 'Тестовая конференция 2',
            'slug' => 'test-conference-2',
        ]);

        $event = Event::create([
            'conference_id' => $conference->id,
            'title' => 'Тестовое событие 2',
            'slug' => 'test-event-2',
            'type' => Event::TYPE_SECTION,
        ]);

        $service = new PresentationImportService();

        $result = $service->import($event, [
            [
                'фио_докладчика' => 'Сидоров Сергей Иванович',
                'название_доклада' => 'Доклад №1',
                'фио_научного_руководителя' => 'Кузнецов Андрей Сергеевич',
            ],
            [
                'фио_докладчика' => '',
                'название_доклада' => '',
            ],
            [
                'фио_докладчика' => '   ',
                'название_доклада' => '   ',
            ],
        ]);

        $this->assertSame(1, $result['created']);
        $this->assertSame(0, $result['skipped']);
        $this->assertSame(0, $result['errors']);
    }
}
