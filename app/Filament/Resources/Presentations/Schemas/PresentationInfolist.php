<?php
// app/Filament/Resources/Presentations/Schemas/PresentationInfolist.php

namespace App\Filament\Resources\Presentations\Schemas;

use Filament\Schemas\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\IconEntry;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Grid;
use Filament\Support\Enums\TextSize;
use Filament\Infolists\Components\TextEntry\TextEntrySize;
use Filament\Infolists\Components\RepeatableEntry;

class PresentationInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Информация о докладе')
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
                                TextEntry::make('event.title')
                                    ->label('Мероприятие')
                                    ->badge()
                                    ->color('primary'),
                                
                                TextEntry::make('status')
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
                                    }),
                            ]),

                        TextEntry::make('abstract')
                            ->label('Аннотация')
                            ->columnSpanFull()
                            ->placeholder('Аннотация отсутствует')
                            ->html()
                            ->limit(500),

                        Grid::make(2)
                            ->schema([
                                TextEntry::make('file_path')
                                    ->label('Файл презентации')
                                    ->formatStateUsing(function ($state) {
                                        if (!$state) return 'Не загружен';
                                        $path = pathinfo($state);
                                        $extension = strtoupper($path['extension'] ?? '');
                                        $icon = match ($extension) {
                                            'PDF' => '📄',
                                            'PPT', 'PPTX' => '📊',
                                            default => '📎',
                                        };
                                        return $icon . ' ' . basename($state);
                                    })
                                    ->url(function ($record) {
                                        return $record->file_path ? asset('storage/' . $record->file_path) : null;
                                    })
                                    ->openUrlInNewTab()
                                    ->badge()
                                    ->color('info'),
                                
                                TextEntry::make('video_link')
                                    ->label('Видео')
                                    ->formatStateUsing(fn ($state) => $state ? '▶️ Ссылка' : 'Не указана')
                                    ->url(fn ($record) => $record->video_link)
                                    ->openUrlInNewTab()
                                    ->badge()
                                    ->color('success'),
                            ]),
                    ]),

                    Section::make('Авторы')
                        ->schema([
                            RepeatableEntry::make('contributors.authors')
                                ->label('')
                                ->schema([
                                    TextEntry::make('full_name')
                                        ->label('ФИО')
                                        ->weight('bold'),

                                    TextEntry::make('email')
                                        ->label('Email')
                                        ->placeholder('—')
                                        ->copyable(),

                                    TextEntry::make('university')
                                        ->label('Университет')
                                        ->placeholder('—'),

                                    TextEntry::make('is_presenter')
                                        ->label('Докладчик')
                                        ->badge()
                                        ->formatStateUsing(fn ($state) => $state ? 'Да' : 'Нет')
                                        ->color(fn ($state) => $state ? 'success' : 'gray'),
                                ])
                                ->columns(4),
                        ]),

                    Section::make('Научный руководитель')
                        ->schema([
                            RepeatableEntry::make('contributors.supervisors')
                                ->label('')
                                ->schema([
                                    TextEntry::make('full_name')
                                        ->label('ФИО')
                                        ->weight('bold'),

                                    TextEntry::make('degree')
                                        ->label('Учёная степень')
                                        ->placeholder('—'),

                                    TextEntry::make('position')
                                        ->label('Должность')
                                        ->placeholder('—'),
                                ])
                                ->columns(3),
                        ])
                        ->visible(fn ($record) => !empty($record->supervisors)),

                Section::make('Информация о подаче')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('submitted_at')
                                    ->label('Дата подачи')
                                    ->dateTime('d.m.Y H:i')
                                    ->placeholder('Не подана'),
                                
                                TextEntry::make('approved_at')
                                    ->label('Дата одобрения')
                                    ->dateTime('d.m.Y H:i')
                                    ->placeholder('Не одобрен'),
                            ]),

                        TextEntry::make('rejection_reason')
                            ->label('Причина отклонения')
                            ->columnSpanFull()
                            ->placeholder('Не отклонен'),
                    ]),

                Section::make('Оценки')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('assessments_count')
                                    ->label('Количество оценок')
                                    ->getStateUsing(function ($record) {
                                        return $record->assessments()->count();
                                    })
                                    ->badge()
                                    ->color('info'),
                                
                                TextEntry::make('average_score')
                                    ->label('Средний балл')
                                    ->getStateUsing(function ($record) {
                                        $avg = $record->assessments()->avg('total_score');
                                        return $avg ? number_format($avg, 2) : 'Нет оценок';
                                    })
                                    ->badge()
                                    ->color(fn ($state) => $state !== 'Нет оценок' ? 'success' : 'gray'),
                                
                                TextEntry::make('total_score')
                                    ->label('Общая сумма баллов')
                                    ->getStateUsing(function ($record) {
                                        $sum = $record->assessments()->sum('total_score');
                                        return $sum ?: 'Нет оценок';
                                    })
                                    ->badge()
                                    ->color(fn ($state) => $state !== 'Нет оценок' ? 'warning' : 'gray'),
                            ]),
                    ]),

                Section::make('Информация о создании')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('created_at')
                                    ->label('Создан')
                                    ->dateTime('d.m.Y H:i:s'),
                                
                                TextEntry::make('updated_at')
                                    ->label('Обновлен')
                                    ->dateTime('d.m.Y H:i:s'),
                            ]),
                    ]),
            ]);
    }
}