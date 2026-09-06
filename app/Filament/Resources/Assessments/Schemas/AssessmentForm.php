<?php

namespace App\Filament\Resources\Assessments\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class AssessmentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('presentation_id')
                    ->relationship('presentation', 'title')
                    ->required(),
                Select::make('expert_id')
                    ->relationship('expert', 'name')
                    ->required(),
                Select::make('event_id')
                    ->relationship('event', 'title')
                    ->required(),
                TextInput::make('criteria_values')
                    ->required(),
                TextInput::make('total_score')
                    ->required()
                    ->numeric(),
                Textarea::make('comment')
                    ->columnSpanFull(),
                DateTimePicker::make('saved_at')
                    ->required(),
            ]);
    }
}
