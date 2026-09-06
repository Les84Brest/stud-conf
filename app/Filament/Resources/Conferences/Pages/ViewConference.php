<?php

namespace App\Filament\Resources\Conferences\Pages;

use App\Filament\Resources\Conferences\ConferenceResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewConference extends ViewRecord
{
    protected static string $resource = ConferenceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
