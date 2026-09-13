<?php
// app/Filament/Resources/Criterias/Schemas/CriteriaInfolist.php

namespace App\Filament\Resources\Criterias\Schemas;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Support\Enums\TextSize;

class CriteriaInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Информация о критерии')
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

                        Grid::make(2)
                            ->schema([
                                TextEntry::make('key')
                                    ->label('Ключ')
                                    ->badge()
                                    ->color('gray'),
                                
                                TextEntry::make('max_value')
                                    ->label('Максимальный балл')
                                    ->badge()
                                    ->color('success')
                                    ->formatStateUsing(fn ($state) => "{$state} баллов"),
                            ]),

                        TextEntry::make('group.name')
                            ->label('Группа критериев')
                            ->badge()
                            ->color('primary'),

                        TextEntry::make('description')
                            ->label('Описание')
                            ->columnSpanFull()
                            ->placeholder('Описание отсутствует'),

                        Grid::make(2)
                            ->schema([
                                TextEntry::make('sort_order')
                                    ->label('Порядок сортировки'),
                                
                                IconEntry::make('is_active')
                                    ->label('Активен')
                                    ->boolean(),
                            ]),

                        TextEntry::make('events_count')
                            ->label('Используется в мероприятиях')
                            ->getStateUsing(function ($record) {
                                return $record->events()->count();
                            })
                            ->badge()
                            ->color('info'),
                    ]),

                Section::make('Информация о создании')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('created_at')
                                    ->label('Создан')
                                    ->dateTime('d.m.Y H:i:s'),
                                
                                TextEntry::make('updated_at')
                                    ->label('Обновлен')
                                    ->dateTime('d.m.Y H:i:s'),
                            ]),
                    ]),
            ]);
    }
}