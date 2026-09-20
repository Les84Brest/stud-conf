<?php
// app/Filament/Resources/CriteriaGroups/Pages/ViewCriteriaGroup.php

namespace App\Filament\Resources\CriteriaGroups\Pages;

use App\Filament\Resources\CriteriaGroups\CriteriaGroupResource;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\ViewRecord;

class ViewCriteriaGroup extends ViewRecord
{
    protected static string $resource = CriteriaGroupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()
                ->label('Редактировать'),
            DeleteAction::make()
                ->label('Удалить')
                ->requiresConfirmation()
                ->modalHeading('Удаление группы')
                ->modalDescription('Вы уверены, что хотите удалить эту группу?'),
        ];
    }
}