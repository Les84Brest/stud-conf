<?php
// app/Filament/Resources/Events/Tables/EventsTable.php

namespace App\Filament\Resources\Events\Tables;

use App\Models\Conference;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Filament\Forms\Components\DatePicker;

class EventsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('title')
                    ->label('Название')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->limit(40),
                
                TextColumn::make('conference.title')
                    ->label('Конференция')
                    ->searchable()
                    ->sortable()
                    ->limit(30),
                
                TextColumn::make('type')
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
                    })
                    ->sortable(),
                
                TextColumn::make('room')
                    ->label('Аудитория')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                
                TextColumn::make('start_time')
                    ->label('Начало')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
                
                TextColumn::make('end_time')
                    ->label('Окончание')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(),
                
                TextColumn::make('experts_count')
                    ->label('Экспертов')
                    ->counts('experts')
                    ->sortable()
                    ->badge()
                    ->color('warning'),
                
                TextColumn::make('presentations_count')
                    ->label('Докладов')
                    ->counts('presentations')
                    ->sortable()
                    ->badge()
                    ->color('info')
                    ->toggleable(),
                
                IconColumn::make('is_active')
                    ->label('Активно')
                    ->boolean()
                    ->sortable(),
                
                TextColumn::make('created_at')
                    ->label('Создано')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('conference_id')
                    ->label('Конференция')
                    ->options(
                        Conference::where('is_active', true)
                            ->get()
                            ->mapWithKeys(function ($conference) {
                                return [$conference->id => $conference->title];
                            })
                    )
                    ->searchable()
                    ->preload()
                    ->placeholder('Все конференции'),
                
                SelectFilter::make('type')
                    ->label('Тип')
                    ->options([
                        'section' => 'Секция',
                        'olympiad' => 'Олимпиада',
                        'round_table' => 'Круглый стол',
                        'master_class' => 'Мастер-класс',
                    ])
                    ->placeholder('Все типы'),
                
                TernaryFilter::make('is_active')
                    ->label('Активно')
                    ->placeholder('Все')
                    ->trueLabel('Активные')
                    ->falseLabel('Неактивные'),
                
                Filter::make('start_time')
                    ->label('Дата проведения')
                    ->form([
                        DatePicker::make('start_from')
                            ->label('Дата от')
                            ->native(false),
                        DatePicker::make('start_to')
                            ->label('Дата до')
                            ->native(false),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when(
                                $data['start_from'],
                                fn ($q) => $q->whereDate('start_time', '>=', $data['start_from'])
                            )
                            ->when(
                                $data['start_to'],
                                fn ($q) => $q->whereDate('start_time', '<=', $data['start_to'])
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['start_from'] ?? null) {
                            $indicators['start_from'] = 'Дата от: ' . $data['start_from'];
                        }
                        if ($data['start_to'] ?? null) {
                            $indicators['start_to'] = 'Дата до: ' . $data['start_to'];
                        }
                        return $indicators;
                    }),
                
                Filter::make('has_experts')
                    ->label('Есть эксперты')
                    ->query(fn ($query) => $query->whereHas('experts'))
                    ->toggle(),
                
                Filter::make('has_presentations')
                    ->label('Есть доклады')
                    ->query(fn ($query) => $query->whereHas('presentations'))
                    ->toggle(),
            ])
            ->actions([
                \Filament\Actions\ViewAction::make()
                    ->label('Просмотр'),
                \Filament\Actions\EditAction::make()
                    ->label('Редактировать'),
                \Filament\Actions\DeleteAction::make()
                    ->label('Удалить')
                    ->requiresConfirmation()
                    ->modalHeading('Удаление мероприятия')
                    ->modalDescription('Вы уверены, что хотите удалить это мероприятие? Это действие нельзя отменить.')
                    ->modalSubmitActionLabel('Да, удалить'),
            ])
            ->bulkActions([
                \Filament\Actions\BulkActionGroup::make([
                    \Filament\Actions\DeleteBulkAction::make()
                        ->label('Удалить выбранные')
                        ->requiresConfirmation()
                        ->modalHeading('Удаление мероприятий')
                        ->modalDescription('Вы уверены, что хотите удалить выбранные мероприятия? Это действие нельзя отменить.'),
                    
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
                        ->modalHeading('Активация мероприятий')
                        ->modalDescription('Вы уверены, что хотите активировать выбранные мероприятия?'),
                    
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
                        ->modalHeading('Деактивация мероприятий')
                        ->modalDescription('Вы уверены, что хотите деактивировать выбранные мероприятия?'),
                ]),
            ])
            ->defaultSort('start_time', 'asc')
            ->searchable()
            ->paginated([15, 25, 50, 100, 'all']);
    }
}