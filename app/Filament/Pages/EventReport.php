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
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
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

    protected string $view = 'filament.pages.event-report';

    public ?int $conferenceId = null;
    public ?int $eventId = null;

    public function mount(): void
    {
        // По умолчанию — первый активный мероприятие
        $this->eventId = Event::where('is_active', true)->value('id');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('conferenceId')
                    ->label('Конференция')
                    ->options(
                        Conference::orderBy('title')->pluck('title', 'id')
                    )
                    ->searchable()
                    ->live()
                    ->afterStateUpdated(function () {
                        $this->eventId = null;
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
                    ->live(),
            ])
            ->columns(2);
    }

    /**
     * Данные для таблицы докладов.
     */
    protected function getTableQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $query = \App\Models\Presentation::query()
            ->with(['authors', 'assessments.expert']);

        return $this->eventId
            ? $query->where('event_id', $this->eventId)
            : $query->whereRaw('1 = 0');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getTableQuery())
            ->columns([
                TextColumn::make('title')
                    ->label('Доклад')
                    ->searchable()
                    ->wrap()
                    ->limit(50),

                TextColumn::make('authors.full_name')
                    ->label('Авторы')
                    ->formatStateUsing(function ($state, $record) {
                        return $record->authors->pluck('full_name')->implode(', ');
                    })
                    ->wrap(),

                TextColumn::make('assessments_count')
                    ->label('Оценок')
                    ->counts('assessments')
                    ->badge()
                    ->color('info'),

                TextColumn::make('assessments_avg')
                    ->label('Средний балл')
                    ->getStateUsing(function ($record) {
                        $avg = $record->assessments->avg('total_score');
                        return $avg ? round($avg, 2) : '—';
                    })
                    ->badge()
                    ->color('success'),

                TextColumn::make('assessments_sum')
                    ->label('Сумма баллов')
                    ->getStateUsing(function ($record) {
                        return $record->assessments->sum('total_score');
                    })
                    ->badge()
                    ->color('warning'),

                TextColumn::make('experts_list')
                    ->label('Эксперты')
                    ->getStateUsing(function ($record) {
                        return $record->assessments
                            ->map(fn ($a) => $a->expert->name . ': ' . $a->total_score)
                            ->implode(' | ');
                    })
                    ->wrap()
                    ->toggleable(),
            ])
            ->defaultSort('title')
            ->paginated([25, 50, 100])
            ->striped();
    }

    /**
     * Кнопки в заголовке.
     */
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