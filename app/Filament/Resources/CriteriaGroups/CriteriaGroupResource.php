<?php
// app/Filament/Resources/CriteriaGroups/CriteriaGroupResource.php

namespace App\Filament\Resources\CriteriaGroups;

use App\Filament\Resources\CriteriaGroups\Pages\CreateCriteriaGroup;
use App\Filament\Resources\CriteriaGroups\Pages\EditCriteriaGroup;
use App\Filament\Resources\CriteriaGroups\Pages\ListCriteriaGroups;
use App\Filament\Resources\CriteriaGroups\Pages\ViewCriteriaGroup;
use App\Filament\Resources\CriteriaGroups\Schemas\CriteriaGroupForm;
use App\Filament\Resources\CriteriaGroups\Schemas\CriteriaGroupInfolist;
use App\Filament\Resources\CriteriaGroups\Tables\CriteriaGroupsTable;
use App\Models\CriteriaGroup;
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

class CriteriaGroupResource extends Resource
{
    protected static ?string $model = CriteriaGroup::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedFolder;
    
    protected static string|UnitEnum|null $navigationGroup = 'Управление конференциями';
    
    protected static ?int $navigationSort = 3;
    
    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return CriteriaGroupForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return CriteriaGroupInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CriteriaGroupsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            // Можно добавить Relation Manager для критериев
        ];
    }
    
    public static function getPages(): array
    {
        return [
            'index' => ListCriteriaGroups::route('/'),
            'create' => CreateCriteriaGroup::route('/create'),
            'edit' => EditCriteriaGroup::route('/{record}/edit'),
            'view' => ViewCriteriaGroup::route('/{record}'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'primary';
    }
}