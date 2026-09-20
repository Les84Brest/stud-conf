<?php
// app/Filament/Resources/Assessments/Pages/ViewAssessment.php

namespace App\Filament\Resources\Assessments\Pages;

use App\Filament\Resources\Assessments\AssessmentResource;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\ViewRecord;

class ViewAssessment extends ViewRecord
{
    protected static string $resource = AssessmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()
                ->label('Редактировать'),
            DeleteAction::make()
                ->label('Удалить')
                ->requiresConfirmation()
                ->modalHeading('Удаление оценки')
                ->modalDescription('Вы уверены, что хотите удалить эту оценку?'),
        ];
    }
}