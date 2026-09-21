<?php
// app/Filament/Resources/Users/Schemas/UserInfolist.php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Schemas\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\IconEntry;
use Filament\Schemas\Components\Grid;
use Filament\Support\Enums\TextSize;
use Filament\Schemas\Schema;

class UserInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Информация о пользователе')
                    ->schema([
                        TextEntry::make('id')
                            ->label('ID'),
                        
                        TextEntry::make('name')
                            ->label('Имя')
                            ->size(TextSize::Large),
                        
                        TextEntry::make('email')
                            ->label('Email')
                            ->copyable()
                            ->copyMessage('Email скопирован'),
                        
                        TextEntry::make('role')
                            ->label('Роль')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'admin' => 'danger',
                                'expert' => 'info',
                                'observer' => 'warning',
                                default => 'gray',
                            })
                            ->formatStateUsing(fn (string $state): string => match ($state) {
                                'admin' => 'Администратор',
                                'expert' => 'Эксперт',
                                'observer' => 'Наблюдатель',
                                default => $state,
                            }),
                        
                        IconEntry::make('is_active')
                            ->label('Активен')
                            ->boolean(),
                    ])->columns(2),

                Section::make('Статистика')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('events_count')
                                    ->label('Мероприятий')
                                    ->getStateUsing(fn ($record) => $record->events()->count())
                                    ->badge()
                                    ->color(fn ($record) => $record->events()->count() === 0 ? 'warning' : 'success'),

                                TextEntry::make('assessments_count')
                                    ->label('Выставлено оценок')
                                    ->getStateUsing(fn ($record) => $record->assessments()->count())
                                    ->badge()
                                    ->color('info'),

                                TextEntry::make('last_login_at')
                                    ->label('Последний вход')
                                    ->dateTime('d.m.Y H:i')
                                    ->placeholder('Не входил')
                                    ->badge()
                                    ->color(fn ($state) => $state ? 'success' : 'warning'),
                            ]),
                    ]),

                Section::make('Информация о входе')
                    ->schema([
                        TextEntry::make('last_login_at')
                            ->label('Последний вход')
                            ->dateTime('d.m.Y H:i')
                            ->placeholder('Не входил'),
                        
                        TextEntry::make('created_at')
                            ->label('Дата регистрации')
                            ->dateTime('d.m.Y H:i'),
                        
                        TextEntry::make('updated_at')
                            ->label('Последнее обновление')
                            ->dateTime('d.m.Y H:i'),
                    ])->columns(3),
                Section::make('Мероприятия')
                    ->schema([
                        TextEntry::make('events')
                            ->label('Назначенные мероприятия')
                            ->formatStateUsing(function ($record) {
                                if ($record->events->isEmpty()) {
                                    return 'Не назначен ни на одно мероприятие';
                                }

                                return $record->events
                                    ->map(fn ($event) => $event->title)
                                    ->implode(', ');
                            })
                            ->columnSpanFull()
                            ->badge(fn ($record) => $record->events->isNotEmpty())
                            ->color('info'),
                    ])
                    ->visible(fn ($record) => $record->role === 'expert'),
            ]);
    }
}