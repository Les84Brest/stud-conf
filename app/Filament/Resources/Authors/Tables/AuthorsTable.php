<?php
// app/Filament/Resources/Authors/Tables/AuthorsTable.php

namespace App\Filament\Resources\Authors\Tables;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Table;
use Filament\Tables\Columns\BadgeColumn;

class AuthorsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('full_name')
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
                
                TextColumn::make('university')
                    ->label('Университет')
                    ->searchable()
                    ->sortable()
                    ->limit(30)
                    ->toggleable(),
                
                TextColumn::make('faculty')
                    ->label('Факультет')
                    ->searchable()
                    ->sortable()
                    ->limit(25)
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('group_number')
                    ->label('Группа')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('degree')
                    ->label('Ученая степень')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('gray')
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('presentations_count')
                    ->label('Докладов')
                    ->counts('presentations')
                    ->sortable()
                    ->badge()
                    ->color('success'),
                
                IconColumn::make('is_verified')
                    ->label('Подтвержден')
                    ->boolean()
                    ->sortable()
                    ->toggleable(),
                
                TextColumn::make('created_at')
                    ->label('Создан')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('is_verified')
                    ->label('Подтвержден')
                    ->placeholder('Все')
                    ->trueLabel('Подтвержденные')
                    ->falseLabel('Неподтвержденные'),
                
                Filter::make('has_presentations')
                    ->label('Есть доклады')
                    ->query(fn ($query) => $query->whereHas('presentations'))
                    ->toggle(),
                
                Filter::make('is_presenter')
                    ->label('Докладчик')
                    ->query(fn ($query) => $query->whereHas('presentations', function ($q) {
                        $q->wherePivot('is_presenter', true);
                    }))
                    ->toggle(),
                
                Filter::make('university')
                    ->form([
                        TextInput::make('university')
                            ->label('Университет')
                            ->placeholder('Введите название университета'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when(
                                $data['university'],
                                fn ($q) => $q->where('university', 'like', "%{$data['university']}%")
                            );
                    }),
            ])
            ->actions([
                \Filament\Actions\ViewAction::make()
                    ->label('Просмотр'),
                \Filament\Actions\EditAction::make()
                    ->label('Редактировать'),
                \Filament\Actions\DeleteAction::make()
                    ->label('Удалить')
                    ->requiresConfirmation()
                    ->modalHeading('Удаление автора')
                    ->modalDescription('Вы уверены, что хотите удалить этого автора? Это действие нельзя отменить.')
                    ->modalSubmitActionLabel('Да, удалить'),
            ])
            ->bulkActions([
                \Filament\Actions\BulkActionGroup::make([
                    \Filament\Actions\DeleteBulkAction::make()
                        ->label('Удалить выбранных')
                        ->requiresConfirmation()
                        ->modalHeading('Удаление авторов')
                        ->modalDescription('Вы уверены, что хотите удалить выбранных авторов? Это действие нельзя отменить.'),
                    
                    \Filament\Actions\BulkAction::make('verify')
                        ->label('Подтвердить')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(function ($records) {
                            foreach ($records as $record) {
                                $record->update(['is_verified' => true]);
                            }
                        })
                        ->requiresConfirmation()
                        ->modalHeading('Подтверждение авторов')
                        ->modalDescription('Вы уверены, что хотите подтвердить выбранных авторов?'),
                    
                    \Filament\Actions\BulkAction::make('unverify')
                        ->label('Снять подтверждение')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->action(function ($records) {
                            foreach ($records as $record) {
                                $record->update(['is_verified' => false]);
                            }
                        })
                        ->requiresConfirmation()
                        ->modalHeading('Снятие подтверждения')
                        ->modalDescription('Вы уверены, что хотите снять подтверждение с выбранных авторов?'),
                    
                    \Filament\Actions\BulkAction::make('export_csv')
                        ->label('Экспорт в CSV')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('info')
                        ->action(function ($records) {
                            // Здесь будет логика экспорта
                            \Filament\Notifications\Notification::make()
                                ->title('Экспорт начат')
                                ->body('CSV файл будет готов через несколько секунд')
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