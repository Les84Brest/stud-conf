<?php
// app/Filament/Resources/Criterias/Schemas/CriteriaForm.php

namespace App\Filament\Resources\Criterias\Schemas;

use App\Models\CriteriaGroup;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\TextInput;
use Illuminate\Support\Str;

class CriteriaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Основная информация')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('criteria_group_id')
                                    ->label('Группа критериев')
                                    ->options(
                                        CriteriaGroup::where('is_active', true)
                                            ->get()
                                            ->mapWithKeys(function ($group) {
                                                return [$group->id => $group->name];
                                            })
                                    )
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->placeholder('Выберите группу')
                                    ->helperText('К какой группе относится критерий'),
                                
                                TextInput::make('name')
                                    ->label('Название критерия')
                                    ->required()
                                    ->maxLength(255)
                                    ->placeholder('Актуальность темы и проблематика')
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function (string $state, callable $set) {
                                        $set('key', Str::slug($state, '_'));
                                    })
                                    ->helperText('Введите название критерия оценки'),
                            ]),
                        
                        Grid::make(2)
                            ->schema([
                                TextInput::make('key')
                                    ->label('Ключ (идентификатор)')
                                    ->required()
                                    ->maxLength(50)
                                    ->unique(ignoreRecord: true)
                                    ->helperText('Уникальный идентификатор для системы (например: relevance, novelty)')
                                    ->disabled(fn (string $operation): bool => $operation === 'edit'),
                                
                                TextInput::make('max_value')
                                    ->label('Максимальный балл')
                                    ->numeric()
                                    ->required()
                                    ->default(3)
                                    ->minValue(1)
                                    ->maxValue(10)
                                    ->helperText('Максимальное количество баллов по этому критерию'),
                            ]),
                        
                        TextInput::make('sort_order')
                            ->label('Порядок сортировки')
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->helperText('Чем меньше число, тем выше критерий в списке'),
                        
                        Textarea::make('description')
                            ->label('Описание критерия')
                            ->maxLength(1000)
                            ->rows(3)
                            ->placeholder('Опишите, что оценивается по этому критерию...')
                            ->columnSpanFull(),
                    ]),

                Section::make('Статус')
                    ->schema([
                        Toggle::make('is_active')
                            ->label('Активен')
                            ->default(true)
                            ->helperText('Неактивные критерии не будут отображаться при оценке'),
                    ]),
            ]);
    }
}