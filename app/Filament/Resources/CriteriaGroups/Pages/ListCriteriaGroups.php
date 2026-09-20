<?php
// app/Filament/Resources/CriteriaGroups/Pages/ListCriteriaGroups.php

namespace App\Filament\Resources\CriteriaGroups\Pages;

use App\Filament\Resources\CriteriaGroups\CriteriaGroupResource;
use Filament\Resources\Pages\ListRecords;

class ListCriteriaGroups extends ListRecords
{
    protected static string $resource = CriteriaGroupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\CreateAction::make()
                ->label('Новая группа')
                ->icon('heroicon-o-plus'),
        ];
    }
}