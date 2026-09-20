<?php
// app/Filament/Resources/Presentations/Pages/CreatePresentation.php

namespace App\Filament\Resources\Presentations\Pages;

use App\Filament\Resources\Presentations\PresentationResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePresentation extends CreateRecord
{
    protected static string $resource = PresentationResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Доклад создан успешно';
    }
}