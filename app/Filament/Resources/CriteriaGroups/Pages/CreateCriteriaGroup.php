<?php
// app/Filament/Resources/CriteriaGroups/Pages/CreateCriteriaGroup.php

namespace App\Filament\Resources\CriteriaGroups\Pages;

use App\Filament\Resources\CriteriaGroups\CriteriaGroupResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCriteriaGroup extends CreateRecord
{
    protected static string $resource = CriteriaGroupResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Группа критериев создана успешно';
    }
}