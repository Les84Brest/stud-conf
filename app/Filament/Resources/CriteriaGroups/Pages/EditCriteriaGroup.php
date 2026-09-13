<?php
// app/Filament/Resources/CriteriaGroups/Pages/EditCriteriaGroup.php

namespace App\Filament\Resources\CriteriaGroups\Pages;

use App\Filament\Resources\CriteriaGroups\CriteriaGroupResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCriteriaGroup extends EditRecord
{
    protected static string $resource = CriteriaGroupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->label('Удалить')
                ->requiresConfirmation()
                ->modalHeading('Удаление группы')
                ->modalDescription('Вы уверены, что хотите удалить эту группу?'),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Группа критериев обновлена успешно';
    }
}