<?php
// app/Filament/Resources/Presentations/Tables/PresentationsTable.php

namespace App\Filament\Resources\Presentations\Tables;

use App\Models\Event;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Filament\Forms\Components\TextInput;
use App\Filament\Actions\ImportPresentationsAction;
use App\Exports\PresentationsTemplateExport;
use Maatwebsite\Excel\Facades\Excel;
use Filament\Actions\Action;

class PresentationsTable
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
                    ->limit(40)
                    ->wrap(),
                
                TextColumn::make('event.title')
                    ->label('Мероприятие')
                    ->searchable()
                    ->sortable()
                    ->limit(30),
                
                TextColumn::make('event.conference.title')
                    ->label('Конференция')
                    ->searchable()
                    ->sortable()
                    ->limit(25)
                    ->toggleable(),
                
                TextColumn::make('authors_string')
                    ->label('Авторы')
                    ->getStateUsing(function ($record) {
                        return collect($record->authors)
                            ->pluck('full_name')
                            ->filter()
                            ->implode(', ');
                    })
                    ->searchable(query: function ($query, $search) {
                        // Поиск по JSON — используем LIKE по полю contributors
                        return $query->where('contributors', 'like', "%{$search}%");
                    })
                    ->wrap()
                    ->limit(50),
                
                TextColumn::make('status')
                    ->label('Статус')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'gray',
                        'submitted' => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                        'presented' => 'info',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'draft' => 'Черновик',
                        'submitted' => 'На рассмотрении',
                        'approved' => 'Одобрен',
                        'rejected' => 'Отклонен',
                        'presented' => 'Представлен',
                        default => $state,
                    })
                    ->sortable(),
                
                TextColumn::make('assessments_avg')
                    ->label('Средний балл')
                    ->getStateUsing(function ($record) {
                        $avg = $record->assessments()->avg('total_score');
                        return $avg ? number_format($avg, 2) : '-';
                    })
                    ->sortable()
                    ->badge()
                    ->color(fn ($state) => $state !== '-' ? 'success' : 'gray')
                    ->toggleable(),
                
                TextColumn::make('assessments_count')
                    ->label('Оценок')
                    ->counts('assessments')
                    ->sortable()
                    ->badge()
                    ->color('info')
                    ->toggleable(),
                
                IconColumn::make('has_file')
                    ->label('Файл')
                    ->boolean()
                    ->getStateUsing(fn ($record) => !empty($record->file_path))
                    ->trueIcon('heroicon-o-document-text')
                    ->falseIcon('heroicon-o-document')
                    ->toggleable(),
                
                TextColumn::make('submitted_at')
                    ->label('Дата подачи')
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
                    ->preload()
                    ->placeholder('Все мероприятия'),
                
                SelectFilter::make('status')
                    ->label('Статус')
                    ->options([
                        'draft' => 'Черновик',
                        'submitted' => 'На рассмотрении',
                        'approved' => 'Одобрен',
                        'rejected' => 'Отклонен',
                        'presented' => 'Представлен',
                    ])
                    ->placeholder('Все статусы'),
                
                TernaryFilter::make('has_file')
                    ->label('Есть файл')
                    ->placeholder('Все')
                    ->trueLabel('С файлом')
                    ->falseLabel('Без файла')
                    ->queries(
                        true: fn ($query) => $query->whereNotNull('file_path'),
                        false: fn ($query) => $query->whereNull('file_path'),
                        blank: fn ($query) => $query,
                    ),
                

                
                Filter::make('has_assessments')
                    ->label('Есть оценки')
                    ->query(fn ($query) => $query->whereHas('assessments'))
                    ->toggle(),
                
                Filter::make('has_authors')
                    ->label('Есть авторы')
                    ->query(fn ($query) => $query->whereNotNull('contributors'))
                    ->toggle(),

                Filter::make('author_name')
                    ->label('Автор')
                    ->form([
                        TextInput::make('author_name')->label('ФИО автора'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query->when(
                            $data['author_name'],
                            fn ($q) => $q->where('contributors', 'like', "%{$data['author_name']}%")
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
                    ->modalHeading('Удаление доклада')
                    ->modalDescription('Вы уверены, что хотите удалить этот доклад? Это действие нельзя отменить.')
                    ->modalSubmitActionLabel('Да, удалить'),
            ])
            ->bulkActions([
                \Filament\Actions\BulkActionGroup::make([
                    \Filament\Actions\DeleteBulkAction::make()
                        ->label('Удалить выбранные')
                        ->requiresConfirmation()
                        ->modalHeading('Удаление докладов')
                        ->modalDescription('Вы уверены, что хотите удалить выбранные доклады? Это действие нельзя отменить.'),
                    
                    \Filament\Actions\BulkAction::make('approve')
                        ->label('Одобрить')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(function ($records) {
                            foreach ($records as $record) {
                                $record->update([
                                    'status' => 'approved',
                                    'approved_at' => now(),
                                ]);
                            }
                        })
                        ->requiresConfirmation()
                        ->modalHeading('Одобрение докладов')
                        ->modalDescription('Вы уверены, что хотите одобрить выбранные доклады?'),
                    
                    \Filament\Actions\BulkAction::make('reject')
                        ->label('Отклонить')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->action(function ($records) {
                            foreach ($records as $record) {
                                $record->update([
                                    'status' => 'rejected',
                                    'approved_at' => null,
                                ]);
                            }
                        })
                        ->requiresConfirmation()
                        ->modalHeading('Отклонение докладов')
                        ->modalDescription('Вы уверены, что хотите отклонить выбранные доклады?'),
                    
                    \Filament\Actions\BulkAction::make('presented')
                        ->label('Отметить как представленные')
                        ->icon('heroicon-o-play-circle')
                        ->color('info')
                        ->action(function ($records) {
                            foreach ($records as $record) {
                                $record->update(['status' => 'presented']);
                            }
                        })
                        ->requiresConfirmation()
                        ->modalHeading('Отметка докладов')
                        ->modalDescription('Вы уверены, что хотите отметить выбранные доклады как представленные?'),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->searchable()
            ->paginated([15, 25, 50, 100, 'all'])
            ->headerActions([
                ImportPresentationsAction::make(),
                 Action::make('download_template')
                    ->label('Скачать шаблон')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('gray')
                    ->action(function () {
                        return Excel::download(
                            new PresentationsTemplateExport(),
                            'template_presentations.xlsx'
                        );
        }),
            ]);
    }
}