<?php
// app/Filament/Resources/Assessments/Tables/AssessmentsTable.php

namespace App\Filament\Resources\Assessments\Tables;

use App\Models\Event;
use App\Models\User;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Filament\Tables\Columns\BadgeColumn;

class AssessmentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('presentation.title')
                    ->label('Доклад')
                    ->searchable()
                    ->sortable()
                    ->limit(40)
                    ->wrap(),
                
                TextColumn::make('presentation.event.title')
                    ->label('Мероприятие')
                    ->searchable()
                    ->sortable()
                    ->limit(30),
                
                TextColumn::make('expert.name')
                    ->label('Эксперт')
                    ->searchable()
                    ->sortable(),
                
                TextColumn::make('total_score')
                    ->label('Итоговый балл')
                    ->sortable()
                    ->color(fn ($record) => $record->total_score >= 15 ? 'success' : ($record->total_score >= 10 ? 'warning' : 'danger'))
                    ->weight('bold'),
                
                TextColumn::make('percentage')
                    ->label('Выполнение')
                    ->formatStateUsing(function ($record) {
                        $maxScore = $record->event->getCalculatedMaxScore() ?? 19;
                        $percentage = $maxScore > 0 ? round(($record->total_score / $maxScore) * 100, 1) : 0;
                        return "{$percentage}%";
                    })
                    ->badge()
                    ->color(fn ($record) => $record->percentage >= 70 ? 'success' : ($record->percentage >= 50 ? 'warning' : 'danger'))
                    ->sortable(query: function ($query, $direction) {
                        return $query->orderBy('total_score', $direction);
                    }),
                
                TextColumn::make('criteria_count')
                    ->label('Критериев заполнено')
                    ->formatStateUsing(function ($record) {
                        $criteriaValues = $record->criteria_values ?? [];
                        $filled = count(array_filter($criteriaValues));
                        $total = count($criteriaValues);
                        return "{$filled} / {$total}";
                    }),
                
                IconColumn::make('is_complete')
                    ->label('Завершена')
                    ->boolean()
                    ->sortable(),
                
                TextColumn::make('saved_at')
                    ->label('Сохранена')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('created_at')
                    ->label('Создана')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('presentation_id')
                    ->label('Доклад')
                    ->relationship('presentation', 'title')
                    ->searchable()
                    ->preload()
                    ->placeholder('Все доклады'),
                
                SelectFilter::make('expert_id')
                    ->label('Эксперт')
                    ->options(
                        User::where('role', 'expert')
                            ->get()
                            ->mapWithKeys(function ($user) {
                                return [$user->id => $user->name];
                            })
                    )
                    ->searchable()
                    ->placeholder('Все эксперты'),
                
                SelectFilter::make('event_id')
                    ->label('Мероприятие')
                    ->options(
                        Event::where('is_active', true)
                            ->get()
                            ->mapWithKeys(function ($event) {
                                return [$event->id => $event->title];
                            })
                    )
                    ->searchable()
                    ->placeholder('Все мероприятия'),
                
                TernaryFilter::make('is_complete')
                    ->label('Статус завершения')
                    ->placeholder('Все')
                    ->trueLabel('Завершенные')
                    ->falseLabel('Незавершенные'),
                
                TernaryFilter::make('total_score')
                    ->label('Высокие оценки')
                    ->trueLabel('Средний балл ≥ 15')
                    ->falseLabel('Средний балл < 10')
                    ->queries(
                        true: fn ($query) => $query->where('total_score', '>=', 15),
                        false: fn ($query) => $query->where('total_score', '<', 10),
                        blank: fn ($query) => $query,
                    ),
            ])
            ->actions([
                \Filament\Actions\ViewAction::make()
                    ->label('Просмотр'),
                \Filament\Actions\EditAction::make()
                    ->label('Редактировать'),
                \Filament\Actions\DeleteAction::make()
                    ->label('Удалить')
                    ->requiresConfirmation()
                    ->modalHeading('Удаление оценки')
                    ->modalDescription('Вы уверены, что хотите удалить эту оценку? Это действие нельзя отменить.'),
            ])
            ->bulkActions([
                \Filament\Actions\BulkActionGroup::make([
                    \Filament\Actions\DeleteBulkAction::make()
                        ->label('Удалить выбранные')
                        ->requiresConfirmation()
                        ->modalHeading('Удаление оценок')
                        ->modalDescription('Вы уверены, что хотите удалить выбранные оценки? Это действие нельзя отменить.'),
                    
                    \Filament\Actions\BulkAction::make('mark_complete')
                        ->label('Отметить как завершенные')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(function ($records) {
                            foreach ($records as $record) {
                                $record->update(['is_complete' => true]);
                            }
                        })
                        ->requiresConfirmation()
                        ->modalHeading('Завершение оценок')
                        ->modalDescription('Вы уверены, что хотите отметить выбранные оценки как завершенные?'),
                    
                    \Filament\Actions\BulkAction::make('export_excel')
                        ->label('Экспорт в Excel')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('info')
                        ->action(function ($records) {
                            // Здесь будет логика экспорта
                            // Можно использовать Laravel Excel
                            \Filament\Notifications\Notification::make()
                                ->title('Экспорт начат')
                                ->body('Отчет будет готов через несколько секунд')
                                ->success()
                                ->send();
                        }),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->searchable()
            ->paginated([15, 25, 50, 100, 'all']);
    }
}