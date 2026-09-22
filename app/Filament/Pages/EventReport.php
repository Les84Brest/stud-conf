<?php

namespace App\Filament\Pages;

use App\Exports\EventReportExport;
use App\Models\Conference;
use App\Models\Event;
use App\Services\ReportService;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Facades\Excel;
use UnitEnum;
use BackedEnum;

class EventReport extends Page implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-document-chart-bar';
    protected static string|UnitEnum|null $navigationGroup = 'Оценки и отчеты';

    protected static ?string $navigationLabel = 'Сводная ведомость';
    protected static ?int $navigationSort = 10;
    protected static ?string $title = 'Сводная ведомость';

    protected  string $view = 'filament.pages.event-report';

    public ?int $conferenceId = null;
    public ?int $eventId = null;

    public function mount(): void
    {
        $this->eventId = Event::where('is_active', true)->value('id');
        $this->conferenceId = Event::find($this->eventId)?->conference_id;
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Select::make('conferenceId')
                    ->label('Конференция')
                    ->options(Conference::orderBy('title')->pluck('title', 'id'))
                    ->searchable()
                    ->live()
                    ->afterStateUpdated(function () {
                        $this->eventId = null;
                        $this->resetTable();
                    }),
                Select::make('eventId')
                    ->label('Мероприятие')
                    ->options(function () {
                        $query = Event::query()->orderBy('title');
                        if ($this->conferenceId) {
                            $query->where('conference_id', $this->conferenceId);
                        }
                        return $query->pluck('title', 'id');
                    })
                    ->searchable()
                    ->live()
                    ->afterStateUpdated(fn () => $this->resetTable()),
            ])
            ->columns(2)
            ->statePath('');
    }

    /**
     * Базовый запрос для плоской таблицы.
     */
    protected function getTableQuery(): ?Builder
    {
        if (!$this->eventId) {
            return null;
        }

        // Загружаем assessments (одна строка = assessment)
        return \App\Models\Assessment::query()
            ->where('event_id', $this->eventId)
            ->with([
                'presentation.authors:id,full_name',
                'presentation.event.conference:id,title',
                'expert:id,name',
                'presentation.event.criteria',
            ])
            ->orderBy('presentation_id');
    }

    public function table(Table $table): Table
    {
        $event = $this->eventId ? Event::find($this->eventId) : null;
        $criteria = $event
            ? app(ReportService::class)->getEventCriteria($event)
            : collect();

        // Динамические колонки критериев
        $criteriaColumns = $criteria->map(function ($criterion) {
            return TextColumn::make("criteria.{$criterion->key}")
                ->label($criterion->name)
                ->getStateUsing(function ($record) use ($criterion) {
                    $values = $record->criteria_values ?? [];
                    return $values[$criterion->key] ?? 0;
                })
                ->alignCenter()
                ->badge()
                ->color(fn ($state) => $state > 0 ? 'success' : 'gray');
        })->all();

        return $table
            ->query($this->getTableQuery())
            ->columns([
                TextColumn::make('presentation.title')
                    ->label('Доклад')
                    ->searchable()
                    ->sortable()
                    ->wrap()
                    ->limit(50),

                TextColumn::make('presentation.authors')
                    ->label('Авторы')
                    ->getStateUsing(function ($record) {
                        return $record->presentation->authors
                            ->pluck('full_name')
                            ->implode(', ');
                    })
                    ->wrap()
                    ->toggleable(),

                TextColumn::make('presentation.event.title')
                    ->label('Мероприятие')
                    ->wrap()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('presentation.event.conference.title')
                    ->label('Конференция')
                    ->wrap()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('expert.name')
                    ->label('Эксперт')
                    ->searchable()
                    ->sortable(),

                // Динамические колонки критериев
                ...$criteriaColumns,

                TextColumn::make('total_score')
                    ->label('Итог')
                    ->badge()
                    ->color('primary')
                    ->sortable()
                    ->alignCenter(),

                TextColumn::make('comment')
                    ->label('Комментарий')
                    ->wrap()
                    ->limit(30)
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('saved_at')
                    ->label('Дата')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('expert_id')
                    ->label('Эксперт')
                    ->relationship('expert', 'name')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('presentation_id')
                    ->label('Доклад')
                    ->relationship('presentation', 'title')
                    ->searchable()
                    ->preload(),
            ])
            ->defaultSort('presentation.title')
            ->paginated([25, 50, 100, 'all'])
            ->striped()
            ->deferLoading();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('export')
                ->label('Выгрузить в Excel')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->visible(fn () => $this->eventId !== null)
                ->action('exportToExcel'),

            Action::make('refresh')
                ->label('Обновить')
                ->icon('heroicon-o-arrow-path')
                ->color('gray')
                ->action(function () {
                    $this->resetTable();
                    Notification::make()
                        ->title('Таблица обновлена')
                        ->success()
                        ->send();
                }),
        ];
    }

    public function exportToExcel()
    {
        if (!$this->eventId) {
            Notification::make()
                ->title('Выберите мероприятие')
                ->warning()
                ->send();
            return;
        }

        $event = Event::findOrFail($this->eventId);

        $filename = sprintf(
            'report_%s_%s.xlsx',
            \Illuminate\Support\Str::slug($event->title),
            now()->format('Y-m-d_His'),
        );

        return Excel::download(
            new EventReportExport($event, app(ReportService::class)),
            $filename,
        );
    }
}