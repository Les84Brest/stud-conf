<?php
// app/Filament/Resources/Events/Schemas/EventForm.php

namespace App\Filament\Resources\Events\Schemas;

use App\Models\Conference;
use App\Models\Criteria;
use App\Models\User;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\DateTimePicker;
use Filament\Schemas\Components\Grid;
use Illuminate\Support\Str;

class EventForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Основная информация')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('conference_id')
                                    ->label('Конференция')
                                    ->options(
                                        Conference::where('is_active', true)
                                            ->get()
                                            ->mapWithKeys(function ($conference) {
                                                return [$conference->id => $conference->title];
                                            })
                                    )
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->placeholder('Выберите конференцию')
                                    ->helperText('Конференция, в рамках которой проводится мероприятие')
                                    ->live()
                                    ->afterStateUpdated(function (callable $set, callable $get) {
                                        // Автоматически генерируем slug при выборе конференции
                                        $conferenceId = $get('conference_id');
                                        $title = $get('title');
                                        if ($conferenceId && $title) {
                                            $conference = Conference::find($conferenceId);
                                            if ($conference) {
                                                $set('slug', Str::slug($conference->slug . '-' . $title));
                                            }
                                        }
                                    }),
                                
                                TextInput::make('title')
                                    ->label('Название мероприятия')
                                    ->required()
                                    ->maxLength(255)
                                    ->placeholder('Бухгалтерский учет и аудит - 1')
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function (string $state, callable $set, callable $get) {
                                        $conferenceId = $get('conference_id');
                                        if ($conferenceId) {
                                            $conference = Conference::find($conferenceId);
                                            if ($conference) {
                                                $set('slug', Str::slug($conference->slug . '-' . $state));
                                            }
                                        } else {
                                            $set('slug', Str::slug($state));
                                        }
                                    })
                                    ->helperText('Введите название мероприятия'),
                            ]),
                        
                        Grid::make(2)
                            ->schema([
                                TextInput::make('slug')
                                    ->label('URL-идентификатор')
                                    ->required()
                                    ->maxLength(255)
                                    ->unique(ignoreRecord: true)
                                    ->helperText('Автоматически генерируется из названия и конференции'),
                                
                                Select::make('type')
                                    ->label('Тип мероприятия')
                                    ->options([
                                        'section' => 'Секция',
                                        'olympiad' => 'Олимпиада',
                                        'round_table' => 'Круглый стол',
                                        'master_class' => 'Мастер-класс',
                                    ])
                                    ->required()
                                    ->default('section')
                                    ->helperText('Выберите тип мероприятия'),
                            ]),
                        
                        Textarea::make('description')
                            ->label('Описание')
                            ->maxLength(1000)
                            ->rows(3)
                            ->placeholder('Опишите мероприятие...')
                            ->columnSpanFull(),
                    ]),

                Section::make('Время и место')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('room')
                                    ->label('Аудитория')
                                    ->maxLength(100)
                                    ->placeholder('Ауд. 101')
                                    ->helperText('Номер аудитории или название зала'),
                                
                                TextInput::make('max_score')
                                    ->label('Максимальный балл')
                                    ->numeric()
                                    ->default(19)
                                    ->minValue(1)
                                    ->maxValue(100)
                                    ->helperText('Общий максимальный балл по всем критериям'),
                            ]),
                        
                        Grid::make(2)
                            ->schema([
                                DateTimePicker::make('start_time')
                                    ->label('Время начала')
                                    ->required()
                                    ->native(false)
                                    ->seconds(false)
                                    ->helperText('Дата и время начала мероприятия'),
                                
                                DateTimePicker::make('end_time')
                                    ->label('Время окончания')
                                    ->required()
                                    ->native(false)
                                    ->seconds(false)
                                    ->afterOrEqual('start_time')
                                    ->helperText('Дата и время окончания мероприятия'),
                            ]),
                    ]),

                Section::make('Критерии оценки')
                    ->schema([
                        Select::make('criteria')
                            ->label('Критерии оценки')
                            ->relationship('criteria', 'name')
                            ->multiple()
                            ->preload()
                            ->searchable()
                            ->helperText('Выберите критерии, по которым будут оцениваться доклады')
                            ->columnSpanFull(),
                    ]),

                Section::make('Назначение экспертов')
                    ->schema([
                        Select::make('experts')
                            ->label('Эксперты')
                            ->relationship('experts', 'name')
                            ->options(
                                User::where('role', 'expert')
                                    ->where('is_active', true)
                                    ->get()
                                    ->mapWithKeys(function ($user) {
                                        return [$user->id => $user->name . ' (' . $user->email . ')'];
                                    })
                            )
                            ->multiple()
                            ->preload()
                            ->searchable()
                            ->helperText('Выберите экспертов для этого мероприятия')
                            ->columnSpanFull(),
                    ]),

                Section::make('Дополнительные настройки')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('sort_order')
                                    ->label('Порядок сортировки')
                                    ->numeric()
                                    ->default(0)
                                    ->minValue(0)
                                    ->helperText('Чем меньше число, тем выше в списке'),
                                
                                Toggle::make('is_active')
                                    ->label('Мероприятие активно')
                                    ->default(true)
                                    ->helperText('Неактивные мероприятия не отображаются на главной странице'),
                            ]),
                    ]),
            ]);
    }
}