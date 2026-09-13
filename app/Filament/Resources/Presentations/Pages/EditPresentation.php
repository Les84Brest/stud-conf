<?php
// app/Filament/Resources/Presentations/Pages/EditPresentation.php

namespace App\Filament\Resources\Presentations\Pages;

use App\Filament\Resources\Presentations\PresentationResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPresentation extends EditRecord
{
    protected static string $resource = PresentationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->label('Удалить')
                ->requiresConfirmation()
                ->modalHeading('Удаление доклада')
                ->modalDescription('Вы уверены, что хотите удалить этот доклад?'),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Доклад обновлен успешно';
    }
}