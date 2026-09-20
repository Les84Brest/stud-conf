<?php
// app/Filament/Resources/Authors/Schemas/AuthorForm.php

namespace App\Filament\Resources\Authors\Schemas;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use  Filament\Forms\Components\Toggle;

class AuthorForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Личная информация')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('full_name')
                                    ->label('Полное имя')
                                    ->required()
                                    ->maxLength(255)
                                    ->placeholder('Иванов Иван Иванович')
                                    ->helperText('Введите полное ФИО автора'),
                                
                                TextInput::make('email')
                                    ->label('Email')
                                    ->email()
                                    ->maxLength(255)
                                    ->placeholder('author@example.com')
                                    ->helperText('Контактный email автора'),
                            ]),
                    ]),

                Section::make('Информация об учебном заведении')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('university')
                                    ->label('Университет')
                                    ->maxLength(255)
                                    ->placeholder('Московский государственный университет')
                                    ->helperText('Название университета'),
                                
                                TextInput::make('faculty')
                                    ->label('Факультет')
                                    ->maxLength(255)
                                    ->placeholder('Экономический факультет')
                                    ->helperText('Название факультета'),
                                
                                TextInput::make('group_number')
                                    ->label('Номер группы')
                                    ->maxLength(50)
                                    ->placeholder('Э-401')
                                    ->helperText('Номер учебной группы'),
                                
                                TextInput::make('degree')
                                    ->label('Ученая степень')
                                    ->maxLength(100)
                                    ->placeholder('Кандидат экономических наук')
                                    ->helperText('Ученая степень (если есть)'),
                            ]),
                    ]),

                Section::make('Контактная информация')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('phone')
                                    ->label('Телефон')
                                    ->tel()
                                    ->maxLength(50)
                                    ->placeholder('+7 (999) 123-45-67')
                                    ->helperText('Контактный телефон'),
                                
                                TextInput::make('position')
                                    ->label('Должность')
                                    ->maxLength(100)
                                    ->placeholder('Студент, Аспирант, Преподаватель')
                                    ->helperText('Текущая должность'),
                            ]),
                    ]),

                Section::make('Дополнительная информация')
                    ->schema([
                        Toggle::make('is_verified')
                            ->label('Автор подтвержден')
                            ->default(false)
                            ->helperText('Отметьте, если личность автора подтверждена'),
                    ]),
            ]);
    }
}