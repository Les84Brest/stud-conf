<?php
// app/Filament/Resources/Criterias/CriteriaResource.php

namespace App\Filament\Resources\Criterias;

use App\Filament\Resources\Criterias\Pages\CreateCriteria;
use App\Filament\Resources\Criterias\Pages\EditCriteria;
use App\Filament\Resources\Criterias\Pages\ListCriterias;
use App\Filament\Resources\Criterias\Pages\ViewCriteria;
use App\Filament\Resources\Criterias\Schemas\CriteriaForm;
use App\Filament\Resources\Criterias\Schemas\CriteriaInfolist;
use App\Filament\Resources\Criterias\Tables\CriteriasTable;
use App\Models\Criteria;
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

class CriteriaResource extends Resource
{
    protected static ?string $model = Criteria::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;
    
    protected static string|UnitEnum|null $navigationGroup = 'Управление конференциями';
    
    protected static ?int $navigationSort = 4;
    
    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return CriteriaForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return CriteriaInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CriteriasTable::configure($table);
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
            'index' => ListCriterias::route('/'),
            'create' => CreateCriteria::route('/create'),
            'edit' => EditCriteria::route('/{record}/edit'),
            'view' => ViewCriteria::route('/{record}'),
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