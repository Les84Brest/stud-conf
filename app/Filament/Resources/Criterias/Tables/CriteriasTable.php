<?php
// app/Filament/Resources/Criterias/Tables/CriteriasTable.php

namespace App\Filament\Resources\Criterias\Tables;

use App\Models\CriteriaGroup;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class CriteriasTable
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
                    ->label('Название')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                
                TextColumn::make('key')
                    ->label('Ключ')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('gray'),
                
                TextColumn::make('group.name')
                    ->label('Группа')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('primary'),
                
                TextColumn::make('max_value')
                    ->label('Макс. балл')
                    ->sortable()
                    ->badge()
                    ->color('success'),
                
                TextColumn::make('sort_order')
                    ->label('Порядок')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('events_count')
                    ->label('Мероприятий')
                    ->counts('events')
                    ->sortable()
                    ->badge()
                    ->color('info')
                    ->toggleable(),
                
                IconColumn::make('is_active')
                    ->label('Активен')
                    ->boolean()
                    ->sortable(),
                
                TextColumn::make('created_at')
                    ->label('Создан')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('criteria_group_id')
                    ->label('Группа')
                    ->options(
                        CriteriaGroup::where('is_active', true)
                            ->get()
                            ->mapWithKeys(function ($group) {
                                return [$group->id => $group->name];
                            })
                    )
                    ->searchable()
                    ->placeholder('Все группы'),
                
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
                    ->modalHeading('Удаление критерия')
                    ->modalDescription('Вы уверены, что хотите удалить этот критерий? Это действие нельзя отменить.'),
            ])
            ->bulkActions([
                \Filament\Actions\BulkActionGroup::make([
                    \Filament\Actions\DeleteBulkAction::make()
                        ->label('Удалить выбранные')
                        ->requiresConfirmation(),
                    
                    \Filament\Actions\BulkAction::make('activate')
                        ->label('Активировать')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(function ($records) {
                            foreach ($records as $record) {
                                $record->update(['is_active' => true]);
                            }
                        })
                        ->requiresConfirmation(),
                    
                    \Filament\Actions\BulkAction::make('deactivate')
                        ->label('Деактивировать')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->action(function ($records) {
                            foreach ($records as $record) {
                                $record->update(['is_active' => false]);
                            }
                        })
                        ->requiresConfirmation(),
                ]),
            ])
            ->defaultSort('sort_order', 'asc')
            ->searchable()
            ->paginated([15, 25, 50, 'all']);
    }
}