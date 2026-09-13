<?php
// app/Filament/Resources/Authors/Schemas/AuthorInfolist.php

namespace App\Filament\Resources\Authors\Schemas;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\IconEntry;
use Filament\Support\Enums\TextSize;
use Filament\Schemas\Components\Grid;

class AuthorInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Личная информация')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('id')
                                    ->label('ID'),
                                
                                TextEntry::make('full_name')
                                    ->label('Полное имя')
                                    ->size(TextSize::Large)
                                    ->weight('bold'),
                            ]),

                        Grid::make(2)
                            ->schema([
                                TextEntry::make('email')
                                    ->label('Email')
                                    ->copyable()
                                    ->copyMessage('Email скопирован')
                                    ->icon('heroicon-m-envelope'),
                                
                                TextEntry::make('phone')
                                    ->label('Телефон')
                                    ->copyable()
                                    ->copyMessage('Номер телефона скопирован')
                                    ->icon('heroicon-m-phone')
                                    ->placeholder('Не указан'),
                            ]),

                        Grid::make(2)
                            ->schema([
                                TextEntry::make('university')
                                    ->label('Университет')
                                    ->placeholder('Не указан'),
                                
                                TextEntry::make('faculty')
                                    ->label('Факультет')
                                    ->placeholder('Не указан'),
                            ]),

                        Grid::make(2)
                            ->schema([
                                TextEntry::make('group_number')
                                    ->label('Номер группы')
                                    ->placeholder('Не указан'),
                                
                                TextEntry::make('degree')
                                    ->label('Ученая степень')
                                    ->placeholder('Не указана'),
                            ]),

                        Grid::make(2)
                            ->schema([
                                TextEntry::make('position')
                                    ->label('Должность')
                                    ->placeholder('Не указана'),
                                
                                IconEntry::make('is_verified')
                                    ->label('Подтвержден')
                                    ->boolean(),
                            ]),
                    ]),

                Section::make('Статистика')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('presentations_count')
                                    ->label('Количество докладов')
                                    ->getStateUsing(function ($record) {
                                        return $record->presentations()->count();
                                    })
                                    ->badge()
                                    ->color('success'),
                                
                                TextEntry::make('presenter_count')
                                    ->label('Докладов как докладчик')
                                    ->getStateUsing(function ($record) {
                                        return $record->presentations()
                                            ->wherePivot('is_presenter', true)
                                            ->count();
                                    })
                                    ->badge()
                                    ->color('warning'),
                                
                                TextEntry::make('corresponding_count')
                                    ->label('Докладов как ответственный')
                                    ->getStateUsing(function ($record) {
                                        return $record->presentations()
                                            ->wherePivot('is_corresponding', true)
                                            ->count();
                                    })
                                    ->badge()
                                    ->color('info'),
                            ]),
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