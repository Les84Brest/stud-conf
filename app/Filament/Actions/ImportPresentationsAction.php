<?php

namespace App\Filament\Actions;

use App\Models\Event;
use App\Services\PresentationImportService;
use App\Exports\PresentationsTemplateExport;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class ImportPresentationsAction
{
    public static function readRowsFromFile(string $filePath): array
    {
        $import = new class implements \Maatwebsite\Excel\Concerns\Import {
            use \Maatwebsite\Excel\Concerns\Importable;
        };

        return Excel::toArray($import, $filePath)[0] ?? [];
    }

    public static function make(): Action
    {
        return Action::make('import')
            ->label('Импорт из Excel')
            ->icon('heroicon-o-arrow-up-tray')
            ->color('info')
            ->modalHeading('Импорт докладов')
            ->modalDescription(
                'Загрузите файл Excel (.xlsx) или CSV с докладами. ' .
                'Первая строка должна содержать заголовки.'
            )
            ->modalSubmitActionLabel('Импортировать')
            ->extraModalFooterActions([
                Action::make('download_template')
                    ->label('Скачать шаблон')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('gray')
                    ->action(function () {
                        return Excel::download(
                            new PresentationsTemplateExport(),
                            'template_presentations.xlsx'
                        );
                    }),
            ])
            ->modalDescription(
                'Загрузите файл Excel (.xlsx) или CSV. Первая строка — заголовки. ' .
                'Обязательные колонки: «ФИО докладчика» и «Название доклада». ' .
                'Соавторы указываются через точку с запятой (;). ' .
                'Скачайте шаблон для примера.'
            )
            ->modalWidth('2xl')
            ->form([
                Select::make('event_id')
                    ->label('Мероприятие')
                    ->options(
                        Event::where('is_active', true)
                            ->orderBy('title')
                            ->pluck('title', 'id')
                    )
                    ->required()
                    ->searchable()
                    ->preload()
                    ->helperText('К какому мероприятию привязать доклады'),

                FileUpload::make('file')
                    ->label('Файл с докладами')
                    ->acceptedFileTypes([
                        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        'application/vnd.ms-excel',
                        'text/csv',
                        'text/plain',
                    ])
                    ->maxSize(10240) // 10 MB
                    ->required()
                    ->disk('local')
                    ->directory('imports')
                    ->helperText('Поддерживаются: .xlsx, .xls, .csv. Максимум 10 МБ.'),
            ])
            ->action(function (array $data) {
                $event = Event::findOrFail($data['event_id']);
                $filePath = Storage::disk('local')->path($data['file']);

                try {
                    // Читаем строки из файла (первая строка — заголовки)
                    $rows = self::readRowsFromFile($filePath);

                    if (empty($rows)) {
                        Notification::make()
                            ->title('Файл пустой')
                            ->danger()
                            ->send();
                        return;
                    }

                    // Первая строка — заголовки
                    $headings = array_shift($rows);

                    if (empty($rows)) {
                        Notification::make()
                            ->title('В файле нет данных')
                            ->body('Найдены только заголовки, но нет строк.')
                            ->warning()
                            ->send();
                        return;
                    }

                    // Собираем ассоциативные массивы
                    $assocRows = array_map(function ($row) use ($headings) {
                        // Заполняем пропущенные колонки null-ами
                        $row = array_pad($row, count($headings), null);
                        return array_combine($headings, array_slice($row, 0, count($headings)));
                    }, $rows);

                    // Импортируем
                    $service = app(PresentationImportService::class);
                    $result = $service->import($event, $assocRows);

                    // Удаляем загруженный файл
                    Storage::disk('local')->delete($data['file']);

                    // Показываем результат
                    $body = "Создано докладов: {$result['created']}";
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

                        Notification::make()
                            ->title('Импорт завершён с ошибками')
                            ->body($body . "\n\n" . $errorList)
                            ->warning()
                            ->persistent()
                            ->send();
                    } else {
                        Notification::make()
                            ->title('Импорт завершён успешно')
                            ->body($body)
                            ->success()
                            ->send();
                    }
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