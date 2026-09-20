<?php
// app/Filament/Resources/Events/Schemas/EventInfolist.php

namespace App\Filament\Resources\Events\Schemas;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\IconEntry;
use Filament\Schemas\Components\Grid;
use Filament\Support\Enums\TextSize;

class EventInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Информация о мероприятии')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('id')
                                    ->label('ID'),
                                
                                TextEntry::make('title')
                                    ->label('Название')
                                    ->size(TextSize::Large)
                                    ->weight('bold'),
                            ]),

                        Grid::make(2)
                            ->schema([
                                TextEntry::make('conference.title')
                                    ->label('Конференция')
                                    ->badge()
                                    ->color('primary'),
                                
                                TextEntry::make('type')
                                    ->label('Тип')
                                    ->badge()
                                    ->color(fn (string $state): string => match ($state) {
                                        'section' => 'info',
                                        'olympiad' => 'success',
                                        'round_table' => 'warning',
                                        'master_class' => 'primary',
                                        default => 'gray',
                                    })
                                    ->formatStateUsing(fn (string $state): string => match ($state) {
                                        'section' => 'Секция',
                                        'olympiad' => 'Олимпиада',
                                        'round_table' => 'Круглый стол',
                                        'master_class' => 'Мастер-класс',
                                        default => $state,
                                    }),
                            ]),

                        TextEntry::make('description')
                            ->label('Описание')
                            ->columnSpanFull()
                            ->placeholder('Описание отсутствует'),

                        Grid::make(2)
                            ->schema([
                                TextEntry::make('room')
                                    ->label('Аудитория')
                                    ->placeholder('Не указана'),
                                
                                TextEntry::make('max_score')
                                    ->label('Максимальный балл')
                                    ->badge()
                                    ->color('success'),
                            ]),

                        Grid::make(2)
                            ->schema([
                                TextEntry::make('start_time')
                                    ->label('Время начала')
                                    ->dateTime('d.m.Y H:i'),
                                
                                TextEntry::make('end_time')
                                    ->label('Время окончания')
                                    ->dateTime('d.m.Y H:i'),
                            ]),
                    ]),

                Section::make('Статистика')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('presentations_count')
                                    ->label('Докладов')
                                    ->getStateUsing(function ($record) {
                                        return $record->presentations()->count();
                                    })
                                    ->badge()
                                    ->color('info'),
                                
                                TextEntry::make('experts_count')
                                    ->label('Экспертов')
                                    ->getStateUsing(function ($record) {
                                        return $record->experts()->count();
                                    })
                                    ->badge()
                                    ->color('warning'),
                                
                                TextEntry::make('assessments_count')
                                    ->label('Оценок')
                                    ->getStateUsing(function ($record) {
                                        return $record->assessments()->count();
                                    })
                                    ->badge()
                                    ->color('success'),
                            ]),
                    ]),

                Section::make('Критерии оценки')
                    ->schema([
                        TextEntry::make('criteria_list')
                            ->label('Критерии')
                            ->getStateUsing(function ($record) {
                                $criteria = $record->criteria()->get();
                                if ($criteria->isEmpty()) {
                                    return 'Критерии не назначены';
                                }
                                return $criteria->map(function ($criterion) {
                                    return $criterion->name . ' (' . $criterion->max_value . ' баллов)';
                                })->implode(', ');
                            })
                            ->columnSpanFull(),
                    ]),

                Section::make('Эксперты')
                    ->schema([
                        TextEntry::make('experts_list')
                            ->label('Назначенные эксперты')
                            ->getStateUsing(function ($record) {
                                $experts = $record->experts()->get();
                                if ($experts->isEmpty()) {
                                    return 'Эксперты не назначены';
                                }
                                return $experts->map(function ($expert) {
                                    return $expert->name . ' (' . $expert->email . ')';
                                })->implode(', ');
                            })
                            ->columnSpanFull(),
                    ]),

                Section::make('Информация о создании')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('created_at')
                                    ->label('Создано')
                                    ->dateTime('d.m.Y H:i:s'),
                                
                                TextEntry::make('updated_at')
                                    ->label('Обновлено')
                                    ->dateTime('d.m.Y H:i:s'),
                            ]),
                    ]),
            ]);
    }
}