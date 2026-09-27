<?php
// app/Filament/Resources/Presentations/Schemas/PresentationForm.php

namespace App\Filament\Resources\Presentations\Schemas;

use App\Models\Event;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\DateTimePicker;

class PresentationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Основная информация')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('event_id')
                                    ->label('Мероприятие')
                                    ->options(
                                        Event::where('is_active', true)
                                            ->get()
                                            ->mapWithKeys(function ($event) {
                                                return [$event->id => $event->title . ' (' . $event->type_label . ')'];
                                            })
                                    )
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->placeholder('Выберите мероприятие')
                                    ->helperText('Мероприятие, на котором будет представлен доклад'),
                                
                                TextInput::make('title')
                                    ->label('Название доклада')
                                    ->required()
                                    ->maxLength(255)
                                    ->placeholder('Введите название доклада')
                                    ->helperText('Название должно отражать суть исследования'),
                            ]),
                        
                        Textarea::make('abstract')
                            ->label('Аннотация')
                            ->maxLength(5000)
                            ->rows(5)
                            ->placeholder('Опишите основные положения доклада...')
                            ->helperText('Краткое описание исследования (до 5000 символов)')
                            ->columnSpanFull(),
                    ]),
                    Section::make('Авторы доклада')
                        ->description('Перечислите всех авторов. Отметьте одного как докладчика.')
                        ->schema([
                            Repeater::make('contributors.authors')
                                ->label('Авторы')
                                ->schema([
                                    TextInput::make('full_name')
                                        ->label('ФИО')
                                        ->required()
                                        ->maxLength(255),

                                    TextInput::make('email')
                                        ->label('Email')
                                        ->email()
                                        ->maxLength(255),

                                    TextInput::make('university')
                                        ->label('Университет')
                                        ->maxLength(255),

                                    TextInput::make('faculty')
                                        ->label('Факультет')
                                        ->maxLength(255),

                                    TextInput::make('group_number')
                                        ->label('Группа')
                                        ->maxLength(50),

                                    Toggle::make('is_presenter')
                                        ->label('Докладчик')
                                        ->default(false),

                                    Toggle::make('is_corresponding')
                                        ->label('Ответственный')
                                        ->default(false),
                                ])
                                ->columns(3)
                                ->defaultItems(1)
                                ->minItems(1)
                                ->reorderable()
                                ->collapsible()
                                ->itemLabel(fn (array $state): ?string => $state['full_name'] ?? null)
                                ->addActionLabel('Добавить автора')
                                ->columnSpanFull(),
                        ]),

                    Section::make('Научный руководитель')
                        ->description('Научные руководители доклада (необязательно)')
                        ->schema([
                            Repeater::make('contributors.supervisors')
                                ->label('Руководители')
                                ->schema([
                                    TextInput::make('full_name')
                                        ->label('ФИО')
                                        ->required()
                                        ->maxLength(255),

                                    TextInput::make('degree')
                                        ->label('Учёная степень')
                                        ->maxLength(150),

                                    TextInput::make('position')
                                        ->label('Должность')
                                        ->maxLength(150),

                                    TextInput::make('email')
                                        ->label('Email')
                                        ->email()
                                        ->maxLength(255),
                                ])
                                ->columns(2)
                                ->defaultItems(0)
                                ->reorderable()
                                ->collapsible()
                                ->itemLabel(fn (array $state): ?string => $state['full_name'] ?? null)
                                ->addActionLabel('Добавить руководителя')
                                ->columnSpanFull(),
                        ]),
                Section::make('Файлы и материалы')
                    ->schema([
                        FileUpload::make('file_path')
                            ->label('Файл презентации')
                            ->directory('presentations')
                            ->acceptedFileTypes([
                                'application/pdf',
                                'application/vnd.ms-powerpoint',
                                'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                            ])
                            ->maxSize(20480) // 20MB
                            ->helperText('Поддерживаемые форматы: PDF, PPT, PPTX (макс. 20MB)')
                            ->downloadable()
                            ->openable()
                            ->columnSpanFull(),
                        
                        TextInput::make('video_link')
                            ->label('Ссылка на видео')
                            ->maxLength(255)
                            ->url()
                            ->placeholder('https://www.youtube.com/watch?v=...')
                            ->helperText('Ссылка на видеозапись выступления (YouTube, Vimeo и т.д.)')
                            ->columnSpanFull(),
                    ]),

                Section::make('Статус и даты')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('status')
                                    ->label('Статус')
                                    ->options([
                                        'draft' => 'Черновик',
                                        'submitted' => 'На рассмотрении',
                                        'approved' => 'Одобрен',
                                        'rejected' => 'Отклонен',
                                        'presented' => 'Представлен',
                                    ])
                                    ->required()
                                    ->default('draft')
                                    ->helperText('Текущий статус доклада'),
                                
                                DateTimePicker::make('submitted_at')
                                    ->label('Дата подачи')
                                    ->native(false)
                                    ->helperText('Дата подачи доклада на рассмотрение')
                                    ->default(now()),
                            ]),
                        
                        Grid::make(2)
                            ->schema([
                                DateTimePicker::make('approved_at')
                                    ->label('Дата одобрения')
                                    ->native(false)
                                    ->helperText('Дата одобрения доклада'),
                                
                                Textarea::make('rejection_reason')
                                    ->label('Причина отклонения')
                                    ->rows(3)
                                    ->placeholder('Укажите причину отклонения доклада...')
                                    ->helperText('Укажите причину, если доклад был отклонен'),
                            ]),
                    ]),
            ]);
    }
}