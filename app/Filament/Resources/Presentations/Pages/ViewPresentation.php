<?php
// app/Filament/Resources/Presentations/Pages/ViewPresentation.php

namespace App\Filament\Resources\Presentations\Pages;

use App\Filament\Resources\Presentations\PresentationResource;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\ViewRecord;

class ViewPresentation extends ViewRecord
{
    protected static string $resource = PresentationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()
                ->label('Редактировать'),
            DeleteAction::make()
                ->label('Удалить')
                ->requiresConfirmation()
                ->modalHeading('Удаление доклада')
                ->modalDescription('Вы уверены, что хотите удалить этот доклад?'),
        ];
    }
}