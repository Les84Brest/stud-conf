<?php
// app/Filament/Resources/Presentations/Pages/ListPresentations.php

namespace App\Filament\Resources\Presentations\Pages;

use App\Filament\Resources\Presentations\PresentationResource;
use Filament\Resources\Pages\ListRecords;

class ListPresentations extends ListRecords
{
    protected static string $resource = PresentationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\CreateAction::make()
                ->label('Новый доклад')
                ->icon('heroicon-o-plus'),
        ];
    }
}