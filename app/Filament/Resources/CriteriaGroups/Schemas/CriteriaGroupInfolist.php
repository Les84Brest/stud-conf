<?php
// app/Filament/Resources/CriteriaGroups/Schemas/CriteriaGroupInfolist.php

namespace App\Filament\Resources\CriteriaGroups\Schemas;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Support\Enums\TextSize;
use Filament\Schemas\Components\Grid;

class CriteriaGroupInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Информация о группе')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('id')
                                    ->label('ID'),
                                
                                TextEntry::make('name')
                                    ->label('Название')
                                    ->size(TextSize::Large)
                                    ->weight('bold'),
                            ]),

                        TextEntry::make('description')
                            ->label('Описание')
                            ->columnSpanFull()
                            ->placeholder('Описание отсутствует'),

                        Grid::make(2)
                            ->schema([
                                TextEntry::make('sort_order')
                                    ->label('Порядок сортировки'),
                                
                                IconEntry::make('is_active')
                                    ->label('Активна')
                                    ->boolean(),
                            ]),

                        TextEntry::make('criterias_count')
                            ->label('Количество критериев')
                            ->getStateUsing(function ($record) {
                                return $record->criterias()->count();
                            })
                            ->badge()
                            ->color('success'),
                    ]),

                Section::make('Информация о создании')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('created_at')
                                    ->label('Создана')
                                    ->dateTime('d.m.Y H:i:s'),
                                
                                TextEntry::make('updated_at')
                                    ->label('Обновлена')
                                    ->dateTime('d.m.Y H:i:s'),
                            ]),
                    ]),
            ]);
    }
}