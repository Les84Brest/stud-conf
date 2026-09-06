<?php

namespace App\Filament\Resources\Assessments\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class AssessmentInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('presentation.title')
                    ->label('Presentation'),
                TextEntry::make('expert.name')
                    ->label('Expert'),
                TextEntry::make('event.title')
                    ->label('Event'),
                TextEntry::make('total_score')
                    ->numeric(),
                TextEntry::make('comment')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('saved_at')
                    ->dateTime(),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
