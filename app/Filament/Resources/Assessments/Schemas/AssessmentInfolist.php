<?php
// app/Filament/Resources/Assessments/Schemas/AssessmentInfolist.php

namespace App\Filament\Resources\Assessments\Schemas;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\IconEntry;
use Filament\Schemas\Components\Grid;
use Filament\Support\Enums\TextSize;

class AssessmentInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Информация об оценке')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('id')
                                    ->label('ID'),
                                
                                TextEntry::make('saved_at')
                                    ->label('Дата сохранения')
                                    ->dateTime('d.m.Y H:i:s'),
                            ]),

                        Grid::make(2)
                            ->schema([
                                TextEntry::make('presentation.title')
                                    ->label('Доклад')
                                    ->weight('bold'),
                                
                                TextEntry::make('presentation.event.title')
                                    ->label('Мероприятие'),
                            ]),

                        Grid::make(2)
                            ->schema([
                                TextEntry::make('expert.name')
                                    ->label('Эксперт')
                                    ->weight('bold'),
                                
                                TextEntry::make('expert.email')
                                    ->label('Email эксперта')
                                    ->copyable()
                                    ->copyMessage('Email скопирован'),
                            ]),
                    ]),

                Section::make('Оценка по критериям')
                    ->schema(function ($record) {
                        $criteriaValues = $record->criteria_values ?? [];
                        $fields = [];
                        
                        $criteria = \App\Models\Criteria::where('is_active', true)
                            ->orderBy('sort_order')
                            ->get();
                        
                        foreach ($criteria as $criterion) {
                            $value = $criteriaValues[$criterion->key] ?? 0;
                            $fields[] = TextEntry::make("criteria_values.{$criterion->key}")
                                ->label($criterion->name)
                                ->formatStateUsing(function () use ($value, $criterion) {
                                    return "{$value} / {$criterion->max_value}";
                                })
                                ->badge()
                                ->color($value > 0 ? 'success' : 'gray');
                        }
                        
                        return $fields;
                    })
                    ->columns(3),

                Section::make('Итоговая оценка')
                    ->schema([
                        TextEntry::make('total_score')
                            ->label('Итоговый балл')
                            ->size(TextSize::Large)
                            ->weight('bold')
                            ->color('primary'),
                        
                        TextEntry::make('percentage')
                            ->label('Процент выполнения')
                            ->formatStateUsing(function ($record) {
                                $maxScore = $record->event->getCalculatedMaxScore() ?? 19;
                                $percentage = $maxScore > 0 ? round(($record->total_score / $maxScore) * 100, 1) : 0;
                                return "{$percentage}%";
                            })
                            ->badge()
                            ->color(fn ($record) => $record->percentage >= 70 ? 'success' : ($record->percentage >= 50 ? 'warning' : 'danger')),
                    ])->columns(2),

                Section::make('Дополнительная информация')
                    ->schema([
                        TextEntry::make('comment')
                            ->label('Комментарий')
                            ->columnSpanFull()
                            ->placeholder('Комментарий отсутствует'),
                        
                        IconEntry::make('is_complete')
                            ->label('Оценка завершена')
                            ->boolean(),
                        
                        TextEntry::make('created_at')
                            ->label('Создана')
                            ->dateTime('d.m.Y H:i:s'),
                        
                        TextEntry::make('updated_at')
                            ->label('Обновлена')
                            ->dateTime('d.m.Y H:i:s'),
                    ])->columns(2),
            ]);
    }
}