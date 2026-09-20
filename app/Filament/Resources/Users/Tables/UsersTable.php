<?php
// app/Filament/Resources/Users/Tables/UsersTable.php

namespace App\Filament\Resources\Users\Tables;

use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('name')
                    ->label('Имя')
                    ->searchable()
                    ->sortable()
                    ->limit(30),
                
                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-m-envelope')
                    ->copyable()
                    ->copyMessage('Email скопирован'),
                
                TextColumn::make('role')
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
                    })
                    ->sortable(),
                
                TextColumn::make('events_count')
                    ->label('Мероприятий')
                    ->counts('events')
                    ->sortable()
                    ->toggleable(),
                
                TextColumn::make('assessments_count')
                    ->label('Оценок')
                    ->counts('assessments')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                IconColumn::make('is_active')
                    ->label('Активен')
                    ->boolean()
                    ->sortable(),
                
                TextColumn::make('last_login_at')
                    ->label('Последний вход')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('created_at')
                    ->label('Создан')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('role')
                    ->label('Роль')
                    ->options([
                        'admin' => 'Администратор',
                        'expert' => 'Эксперт',
                        'observer' => 'Наблюдатель',
                    ])
                    ->placeholder('Все роли'),
                
                TernaryFilter::make('is_active')
                    ->label('Активен')
                    ->placeholder('Все')
                    ->trueLabel('Активные')
                    ->falseLabel('Неактивные'),
            ])
            ->actions([
                \Filament\Actions\ViewAction::make()
                    ->label('Просмотр'),
                \Filament\Actions\EditAction::make()
                    ->label('Редактировать'),
                \Filament\Actions\DeleteAction::make()
                    ->label('Удалить')
                    ->requiresConfirmation()
                    ->modalHeading('Удаление пользователя')
                    ->modalDescription('Вы уверены, что хотите удалить этого пользователя? Это действие нельзя отменить.')
                    ->modalSubmitActionLabel('Да, удалить'),
            ])
            ->bulkActions([
                \Filament\Actions\BulkActionGroup::make([
                    \Filament\Actions\DeleteBulkAction::make()
                        ->label('Удалить выбранных')
                        ->requiresConfirmation()
                        ->modalHeading('Удаление пользователей')
                        ->modalDescription('Вы уверены, что хотите удалить выбранных пользователей? Это действие нельзя отменить.'),
                    
                    \Filament\Actions\BulkAction::make('activate')
                        ->label('Активировать')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(function ($records) {
                            foreach ($records as $record) {
                                $record->update(['is_active' => true]);
                            }
                        })
                        ->requiresConfirmation()
                        ->modalHeading('Активация пользователей')
                        ->modalDescription('Вы уверены, что хотите активировать выбранных пользователей?'),
                    
                    \Filament\Actions\BulkAction::make('deactivate')
                        ->label('Деактивировать')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->action(function ($records) {
                            foreach ($records as $record) {
                                $record->update(['is_active' => false]);
                            }
                        })
                        ->requiresConfirmation()
                        ->modalHeading('Деактивация пользователей')
                        ->modalDescription('Вы уверены, что хотите деактивировать выбранных пользователей?'),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->searchable()
            ->paginated([15, 25, 50, 100, 'all']);
    }
}