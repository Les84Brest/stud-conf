<?php
// app/Filament/Resources/Criterias/Pages/ViewCriteria.php

namespace App\Filament\Resources\Criterias\Pages;

use App\Filament\Resources\Criterias\CriteriaResource;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\ViewRecord;

class ViewCriteria extends ViewRecord
{
    protected static string $resource = CriteriaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()
                ->label('Редактировать'),
            DeleteAction::make()
                ->label('Удалить')
                ->requiresConfirmation()
                ->modalHeading('Удаление критерия')
                ->modalDescription('Вы уверены, что хотите удалить этот критерий?'),
        ];
    }
}