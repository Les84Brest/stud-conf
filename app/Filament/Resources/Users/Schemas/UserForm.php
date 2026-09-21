<?php
// app/Filament/Resources/Users/Schemas/UserForm.php

namespace App\Filament\Resources\Users\Schemas;

use App\Models\User;
use App\Models\Event;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\CheckboxList;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;


class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Личная информация')
                    ->schema([
                        TextInput::make('name')
                            ->label('Имя')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Введите имя пользователя'),
                        
                        TextInput::make('email')
                            ->label('Email')
                            ->required()
                            ->email()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->placeholder('user@example.com'),
                    ])->columns(2),

                Section::make('Данные для входа')
                    ->schema([
                        TextInput::make('password')
                            ->label('Пароль')
                            ->password()
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->rule(Password::default())
                            ->maxLength(255)
                            ->dehydrateStateUsing(fn ($state) => 
                                filled($state) ? Hash::make($state) : null
                            )
                            ->dehydrated(fn ($state) => filled($state))
                            ->placeholder(fn (string $operation): string => 
                                $operation === 'create' ? 'Введите пароль' : 'Оставьте пустым для сохранения текущего'
                            )
                            ->helperText(fn (string $operation): string => 
                                $operation === 'create' 
                                    ? 'Минимум 8 символов' 
                                    : 'Оставьте пустым, чтобы не менять пароль'
                            ),
                        
                        TextInput::make('password_confirmation')
                            ->label('Подтверждение пароля')
                            ->password()
                            ->requiredWith('password')
                            ->same('password')
                            ->maxLength(255)
                            ->dehydrated(false)
                            ->placeholder('Повторите пароль'),
                    ])->columns(2)
                    ->hidden(fn (string $operation): bool => $operation === 'edit' && request()->user()->role === 'admin'),

                Section::make('Назначение ролей')
                    ->schema([
                        Select::make('role')
                            ->label('Роль')
                            ->options([
                                'admin' => 'Администратор',
                                'expert' => 'Эксперт',
                                'observer' => 'Наблюдатель',
                            ])
                            ->default('expert')
                            ->required()
                            ->helperText('Администраторы имеют полный доступ к системе'),
                    ]),
                Section::make('Мероприятия')
                    ->description('Секции и события, на которых эксперт будет оценивать доклады')
                    ->schema([
                        CheckboxList::make('events')
                            ->label('Выберите мероприятия')
                            ->relationship('events', 'title')
                            ->options(function () {
                                return Event::where('is_active', true)
                                    ->orderBy('title')
                                    ->pluck('title', 'id')
                                    ->toArray();
                            })
                            ->columns(2)
                            ->searchable()
                            ->bulkToggleable()
                            ->helperText('Эксперт увидит доклады только с этих мероприятий')
                            ->columnSpanFull(),
                    ])
                    ->visible(fn (Get $get) => $get('role') === 'expert'),
                Section::make('Статус')
                    ->schema([
                        Toggle::make('is_active')
                            ->label('Активен')
                            ->default(true)
                            ->helperText('Неактивные пользователи не могут войти в систему'),
                    ]),
            ]);
    }
}