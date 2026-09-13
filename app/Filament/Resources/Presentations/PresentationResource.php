<?php
// app/Filament/Resources/Presentations/PresentationResource.php

namespace App\Filament\Resources\Presentations;

use App\Filament\Resources\Presentations\Pages\CreatePresentation;
use App\Filament\Resources\Presentations\Pages\EditPresentation;
use App\Filament\Resources\Presentations\Pages\ListPresentations;
use App\Filament\Resources\Presentations\Pages\ViewPresentation;
use App\Filament\Resources\Presentations\Schemas\PresentationForm;
use App\Filament\Resources\Presentations\Schemas\PresentationInfolist;
use App\Filament\Resources\Presentations\Tables\PresentationsTable;
use App\Models\Presentation;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\DeleteAction;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class PresentationResource extends Resource
{
    protected static ?string $model = Presentation::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;
    
    protected static string|UnitEnum|null $navigationGroup = 'Управление контентом';
    
    protected static ?int $navigationSort = 2;
    
    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return PresentationForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return PresentationInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PresentationsTable::configure($table);
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
            'index' => ListPresentations::route('/'),
            'create' => CreatePresentation::route('/create'),
            'edit' => EditPresentation::route('/{record}/edit'),
            'view' => ViewPresentation::route('/{record}'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }
}