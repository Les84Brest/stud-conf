<?php
// app/Filament/Resources/Authors/Pages/ListAuthors.php

namespace App\Filament\Resources\Authors\Pages;

use App\Filament\Resources\Authors\AuthorResource;
use Filament\Resources\Pages\ListRecords;

class ListAuthors extends ListRecords
{
    protected static string $resource = AuthorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\CreateAction::make()
                ->label('Новый автор')
                ->icon('heroicon-o-plus'),
            
            \Filament\Actions\Action::make('import')
                ->label('Импорт авторов')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('gray')
                ->modalHeading('Импорт авторов из CSV')
                ->modalDescription('Загрузите CSV файл с авторами')
                ->form([
                    \Filament\Forms\Components\FileUpload::make('file')
                        ->label('CSV файл')
                        ->acceptedFileTypes(['text/csv', 'application/csv'])
                        ->required()
                        ->helperText('Файл должен содержать колонки: full_name, email, university, faculty, group_number'),
                ])
                ->action(function (array $data) {
                    // Здесь будет логика импорта
                    \Filament\Notifications\Notification::make()
                        ->title('Импорт начат')
                        ->body('Авторы будут импортированы в фоновом режиме')
                        ->success()
                        ->send();
                }),
        ];
    }
}