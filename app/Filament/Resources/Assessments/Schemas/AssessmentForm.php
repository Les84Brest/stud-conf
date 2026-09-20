<?php
// app/Filament/Resources/Assessments/Schemas/AssessmentForm.php

namespace App\Filament\Resources\Assessments\Schemas;

use App\Models\Event;
use App\Models\Presentation;
use App\Models\User;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Illuminate\Support\Collection;

class AssessmentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Информация об оценке')
                    ->schema([
                        Select::make('presentation_id')
                            ->label('Доклад')
                            ->options(
                                Presentation::query()
                                    ->with('event')
                                    ->get()
                                    ->mapWithKeys(function ($presentation) {
                                        return [
                                            $presentation->id => $presentation->title . ' (' . $presentation->event->title . ')'
                                        ];
                                    })
                            )
                            ->required()
                            ->searchable()
                            ->preload()
                            ->placeholder('Выберите доклад')
                            ->helperText('Выберите доклад, который оценивается'),
                        
                        Select::make('expert_id')
                            ->label('Эксперт')
                            ->options(
                                User::where('role', 'expert')
                                    ->get()
                                    ->mapWithKeys(function ($user) {
                                        return [$user->id => $user->name . ' (' . $user->email . ')'];
                                    })
                            )
                            ->required()
                            ->searchable()
                            ->preload()
                            ->placeholder('Выберите эксперта')
                            ->helperText('Эксперт, выставивший оценку'),
                        
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
                            ->helperText('Мероприятие, в рамках которого выставлена оценка'),
                    ])->columns(2),

                Section::make('Оценка по критериям')
                    ->schema([
                        Grid::make()
                            ->schema(function () {
                                $fields = [];
                                
                                // Получаем критерии из системы
                                $criteria = \App\Models\Criteria::where('is_active', true)
                                    ->orderBy('sort_order')
                                    ->get();
                                
                                foreach ($criteria as $criterion) {
                                    $fields[] = TextInput::make("criteria_values.{$criterion->key}")
                                        ->label($criterion->name)
                                        ->numeric()
                                        ->minValue(0)
                                        ->maxValue($criterion->max_value)
                                        ->default(0)
                                        ->suffix("/ {$criterion->max_value}")
                                        ->helperText("Максимальный балл: {$criterion->max_value}")
                                        ->required()
                                        ->step(0.5)
                                        ->live()
                                        ->afterStateUpdated(function (callable $set, callable $get) {
                                            // Автоматически пересчитываем сумму при изменении
                                            self::calculateTotal($set, $get);
                                        });
                                }
                                
                                return $fields;
                            })
                            ->columns(3),
                    ]),
                
                Section::make('Итоговая оценка')
                    ->schema([
                        TextInput::make('total_score')
                            ->label('Итоговый балл')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100)
                            ->required()
                            ->disabled()
                            ->helperText('Автоматически рассчитывается на основе выставленных оценок'),
                    ]),

                Section::make('Дополнительная информация')
                    ->schema([
                        Textarea::make('comment')
                            ->label('Комментарий эксперта')
                            ->maxLength(1000)
                            ->rows(4)
                            ->placeholder('Введите комментарий к оценке...')
                            ->columnSpanFull(),
                        
                        Toggle::make('is_complete')
                            ->label('Оценка завершена')
                            ->default(false)
                            ->helperText('Отметьте, если оценка полностью заполнена и готова к финализации'),
                    ]),
            ]);
    }

    /**
     * Автоматический расчет итоговой суммы
     */
    protected static function calculateTotal(callable $set, callable $get): void
    {
        $criteriaValues = $get('criteria_values') ?? [];
        $total = array_sum($criteriaValues);
        $set('total_score', $total);
    }
}