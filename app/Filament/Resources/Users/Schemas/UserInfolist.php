<?php
// app/Filament/Resources/Users/Schemas/UserInfolist.php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Schemas\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\IconEntry;
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
                        TextEntry::make('events_count')
                            ->label('Количество мероприятий')
                            ->default(0)
                            ->getStateUsing(function ($record) {
                                return $record->events()->count();
                            }),
                        
                        TextEntry::make('assessments_count')
                            ->label('Выставлено оценок')
                            ->default(0)
                            ->getStateUsing(function ($record) {
                                return $record->assessments()->count();
                            }),
                    ])->columns(2),

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
            ]);
    }
}