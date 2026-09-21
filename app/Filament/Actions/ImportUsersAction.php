<?php

namespace App\Filament\Actions;

use App\Services\UserImportService;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\RawArrayImport;

class ImportUsersAction
{
    public static function make(): Action
    {
        return Action::make('import')
            ->label('Импорт экспертов')
            ->icon('heroicon-o-arrow-up-tray')
            ->color('info')
            ->modalHeading('Импорт экспертов')
            ->modalDescription(
                'Загрузите файл Excel (.xlsx) или CSV. ' .
                'Колонки: ФИО, Email, Пароль. Все пользователи создаются с ролью «Эксперт».'
            )
            ->modalSubmitActionLabel('Импортировать')
            ->modalWidth('xl')
            ->form([
                FileUpload::make('file')
                    ->label('Файл с экспертами')
                    ->acceptedFileTypes([
                        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        'application/vnd.ms-excel',
                        'text/csv',
                        'text/plain',
                    ])
                    ->maxSize(10240)
                    ->required()
                    ->disk('local')
                    ->directory('imports')
                    ->helperText('Поддерживаются: .xlsx, .xls, .csv. Максимум 10 МБ.'),
            ])
            ->action(function (array $data) {
                $filePath = Storage::disk('local')->path($data['file']);

                try {
                    $import = new RawArrayImport();
                    Excel::import($import, $filePath);

                    $rows = $import->rows;

                    if (empty($rows)) {
                        Notification::make()
                            ->title('Файл пустой')
                            ->danger()
                            ->send();
                        return;
                    }

                    $headings = array_shift($rows);

                    if (empty($rows)) {
                        Notification::make()
                            ->title('В файле нет данных')
                            ->body('Найдены только заголовки, но нет строк.')
                            ->warning()
                            ->send();
                        return;
                    }

                    // Преобразуем числовые строки в ассоциативные массивы
                    $assocRows = array_map(function ($row) use ($headings) {
                        $row = array_pad($row, count($headings), null);
                        return array_combine(
                            $headings,
                            array_slice($row, 0, count($headings))
                        );
                    }, $rows);

                    $service = app(UserImportService::class);
                    $result = $service->import($assocRows);

                    Storage::disk('local')->delete($data['file']);

                    $body = "Создано экспертов: {$result['created']}";
                    if ($result['skipped'] > 0) {
                        $body .= "\nПропущено: {$result['skipped']}";
                    }

                    if (!empty($result['errors'])) {
                        $errorList = collect($result['errors'])
                            ->take(5)
                            ->map(fn ($e) => "Строка {$e['row']}: {$e['message']}")
                            ->implode("\n");

                        if (count($result['errors']) > 5) {
                            $errorList .= "\n...и ещё " . (count($result['errors']) - 5);
                        }

                        $body .= "\n\n" . $errorList;
                    }

                    if (!empty($result['credentials'])) {
                        $credList = collect($result['credentials'])
                            ->map(fn ($c) => "{$c['name']} | {$c['email']} | {$c['password']}")
                            ->implode("\n");

                        $body .= "\n\nУчётные данные:\n" . $credList;
                    }

                    Notification::make()
                        ->title(
                            $result['skipped'] > 0
                                ? 'Импорт завершён с ошибками'
                                : 'Импорт завершён успешно'
                        )
                        ->body($body)
                        ->{$result['skipped'] > 0 ? 'warning' : 'success'}()
                        ->persistent()
                        ->send();
                } catch (\Throwable $e) {
                    Notification::make()
                        ->title('Ошибка импорта')
                        ->body($e->getMessage())
                        ->danger()
                        ->persistent()
                        ->send();
                }
            });
    }
}