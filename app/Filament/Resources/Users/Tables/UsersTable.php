<?php

namespace App\Filament\Resources\Users\Tables;

use App\Models\Event;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\CheckboxList;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;

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
                    ->label('ФИО')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

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
                    ->badge()
                    ->color(fn ($state) => $state === 0 ? 'warning' : 'success')
                    ->sortable()
                    ->tooltip(fn ($state) => $state === 0
                        ? 'Эксперт не назначен ни на одно мероприятие'
                        : 'Количество мероприятий'),

                TextColumn::make('assessments_count')
                    ->label('Оценок')
                    ->counts('assessments')
                    ->badge()
                    ->color('info')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                IconColumn::make('is_active')
                    ->label('Активен')
                    ->boolean()
                    ->sortable(),

                TextColumn::make('last_login_at')
                    ->label('Последний вход')
                    ->dateTime('d.m.Y H:i')
                    ->placeholder('Не входил')
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
                    ->falseLabel('Заблокированные'),

                SelectFilter::make('events')
                    ->label('Мероприятие')
                    ->relationship('events', 'title')
                    ->searchable()
                    ->preload()
                    ->multiple()
                    ->placeholder('Все мероприятия'),

                TernaryFilter::make('has_events')
                    ->label('Назначен на мероприятие')
                    ->placeholder('Все')
                    ->trueLabel('Назначенные')
                    ->falseLabel('Без мероприятий')
                    ->queries(
                        true: fn ($query) => $query->has('events'),
                        false: fn ($query) => $query->doesntHave('events'),
                        blank: fn ($query) => $query,
                    ),

                TernaryFilter::make('has_logged_in')
                    ->label('Заходил в систему')
                    ->placeholder('Все')
                    ->trueLabel('Заходил')
                    ->falseLabel('Ни разу')
                    ->queries(
                        true: fn ($query) => $query->whereNotNull('last_login_at'),
                        false: fn ($query) => $query->whereNull('last_login_at'),
                        blank: fn ($query) => $query,
                    ),
            ])
            ->actions([
                ViewAction::make()
                    ->label('Просмотр'),
                EditAction::make()
                    ->label('Редактировать'),
                DeleteAction::make()
                    ->label('Удалить')
                    ->requiresConfirmation(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    // 🎯 Главный bulk action — назначить на мероприятие
                    BulkAction::make('assign_events')
                        ->label('Назначить на мероприятия')
                        ->icon('heroicon-o-calendar')
                        ->color('success')
                        ->form([
                            CheckboxList::make('event_ids')
                                ->label('Выберите мероприятия')
                                ->options(function () {
                                    return Event::where('is_active', true)
                                        ->orderBy('title')
                                        ->pluck('title', 'id')
                                        ->toArray();
                                })
                                ->columns(2)
                                ->searchable()
                                ->required()
                                ->helperText('Все выбранные эксперты будут назначены на отмеченные мероприятия'),
                        ])
                        ->action(function (Collection $records, array $data) {
                            $eventIds = $data['event_ids'];
                            $assigned = 0;

                            foreach ($records as $user) {
                                // Синхронизируем без отсоединения существующих
                                $user->events()->syncWithoutDetaching($eventIds);
                                $assigned++;
                            }

                            Notification::make()
                                ->title('Эксперты назначены')
                                ->body("Обновлено пользователей: {$assigned}, мероприятия: " . count($eventIds))
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),

                    // Снять назначения
                    BulkAction::make('detach_events')
                        ->label('Снять с мероприятий')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->form([
                            CheckboxList::make('event_ids')
                                ->label('Выберите мероприятия')
                                ->options(function () {
                                    return Event::where('is_active', true)
                                        ->orderBy('title')
                                        ->pluck('title', 'id')
                                        ->toArray();
                                })
                                ->columns(2)
                                ->searchable()
                                ->required(),
                        ])
                        ->action(function (Collection $records, array $data) {
                            foreach ($records as $user) {
                                $user->events()->detach($data['event_ids']);
                            }

                            Notification::make()
                                ->title('Назначения сняты')
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),

                    // Активация / деактивация
                    BulkAction::make('activate')
                        ->label('Активировать')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(function (Collection $records) {
                            foreach ($records as $user) {
                                $user->update(['is_active' => true]);
                            }
                        })
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion(),

                    BulkAction::make('deactivate')
                        ->label('Заблокировать')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->action(function (Collection $records) {
                            foreach ($records as $user) {
                                $user->update(['is_active' => false]);
                            }
                        })
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion(),

                    // Сбросить пароль
                    BulkAction::make('reset_password')
                        ->label('Сбросить пароль')
                        ->icon('heroicon-o-key')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->modalHeading('Сброс паролей')
                        ->modalDescription('Всем выбранным пользователям будет установлен пароль password123. Продолжить?')
                        ->action(function (Collection $records) {
                            foreach ($records as $user) {
                                $user->update([
                                    'password' => \Illuminate\Support\Facades\Hash::make('password123'),
                                ]);
                            }

                            Notification::make()
                                ->title('Пароли сброшены')
                                ->body('Новый пароль: password123')
                                ->success()
                                ->persistent()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),

                    // Удаление
                    DeleteBulkAction::make()
                        ->label('Удалить выбранных'),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->searchable()
            ->paginated([15, 25, 50, 100, 'all']);
    }
}