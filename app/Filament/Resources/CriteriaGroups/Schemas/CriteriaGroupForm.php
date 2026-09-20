<?php
// app/Filament/Resources/CriteriaGroups/Schemas/CriteriaGroupForm.php

namespace App\Filament\Resources\CriteriaGroups\Schemas;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;

class CriteriaGroupForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Основная информация')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('name')
                                    ->label('Название группы')
                                    ->required()
                                    ->maxLength(255)
                                    ->placeholder('Основные критерии оценки')
                                    ->helperText('Введите название группы критериев'),
                                
                                TextInput::make('sort_order')
                                    ->label('Порядок сортировки')
                                    ->numeric()
                                    ->default(0)
                                    ->minValue(0)
                                    ->helperText('Чем меньше число, тем выше в списке'),
                            ]),
                        
                        Textarea::make('description')
                            ->label('Описание')
                            ->maxLength(1000)
                            ->rows(3)
                            ->placeholder('Опишите назначение группы критериев...')
                            ->columnSpanFull(),
                    ]),

                Section::make('Статус')
                    ->schema([
                        Toggle::make('is_active')
                            ->label('Активна')
                            ->default(true)
                            ->helperText('Неактивные группы не будут отображаться при выборе критериев'),
                    ]),
            ]);
    }
}