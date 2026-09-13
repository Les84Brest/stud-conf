<?php
// app/Filament/Resources/Criterias/Pages/ListCriterias.php

namespace App\Filament\Resources\Criterias\Pages;

use App\Filament\Resources\Criterias\CriteriaResource;
use Filament\Resources\Pages\ListRecords;

class ListCriterias extends ListRecords
{
    protected static string $resource = CriteriaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\CreateAction::make()
                ->label('Новый критерий')
                ->icon('heroicon-o-plus'),
        ];
    }
}