<?php

namespace App\Filament\Resources\Conferences;

use App\Filament\Resources\Conferences\Pages\CreateConference;
use App\Filament\Resources\Conferences\Pages\EditConference;
use App\Filament\Resources\Conferences\Pages\ListConferences;
use App\Filament\Resources\Conferences\Pages\ViewConference;
use App\Filament\Resources\Conferences\Schemas\ConferenceForm;
use App\Filament\Resources\Conferences\Schemas\ConferenceInfolist;
use App\Filament\Resources\Conferences\Tables\ConferencesTable;
use App\Models\Conference;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\DeleteAction;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use UnitEnum;

class ConferenceResource extends Resource
{
    protected static ?string $model = Conference::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;
    
    protected static string|UnitEnum|null $navigationGroup = 'Управление конференциями';
    
    protected static ?string $recordTitleAttribute = 'title';

public static function form(Schema $schema): Schema
    {
        return ConferenceForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ConferenceInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                ImageColumn::make('logo_path')
                    ->label('Логотип')
                    ->circular()
                    ->defaultImageUrl(asset('images/default-conference.png')),
                TextColumn::make('title')
                    ->label('Название')
                    ->searchable()
                    ->sortable()
                    ->limit(30),
                TextColumn::make('start_date')
                    ->label('Дата начала')
                    ->date('d.m.Y')
                    ->sortable(),
                TextColumn::make('end_date')
                    ->label('Дата окончания')
                    ->date('d.m.Y')
                    ->sortable(),
                TextColumn::make('events_count')
                    ->label('Мероприятий')
                    ->counts('events')
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label('Активна')
                    ->boolean()
                    ->sortable(),
            ])
            ->filters([
                TernaryFilter::make('is_active')
                    ->label('Активна'),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }
    
     public static function getPages(): array
    {
        return [
            'index' => Pages\ListConferences::route('/'),
            'create' => Pages\CreateConference::route('/create'),
            'edit' => Pages\EditConference::route('/{record}/edit'),
            'view' => Pages\ViewConference::route('/{record}'),
        ];
    }

}
