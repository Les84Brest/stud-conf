<?php
// app/Filament/Resources/Assessments/AssessmentResource.php

namespace App\Filament\Resources\Assessments;

use App\Filament\Resources\Assessments\Pages\CreateAssessment;
use App\Filament\Resources\Assessments\Pages\EditAssessment;
use App\Filament\Resources\Assessments\Pages\ListAssessments;
use App\Filament\Resources\Assessments\Pages\ViewAssessment;
use App\Filament\Resources\Assessments\Schemas\AssessmentForm;
use App\Filament\Resources\Assessments\Schemas\AssessmentInfolist;
use App\Filament\Resources\Assessments\Tables\AssessmentsTable;
use App\Models\Assessment;
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

class AssessmentResource extends Resource
{
    protected static ?string $model = Assessment::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedStar;
    
    protected static string|UnitEnum|null $navigationGroup = 'Оценки и отчеты';
    
    protected static ?int $navigationSort = 1;
    
    protected static ?string $recordTitleAttribute = 'id';

    public static function form(Schema $schema): Schema
    {
        return AssessmentForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return AssessmentInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AssessmentsTable::configure($table);
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
            'index' => ListAssessments::route('/'),
            'create' => CreateAssessment::route('/create'),
            'edit' => EditAssessment::route('/{record}/edit'),
            'view' => ViewAssessment::route('/{record}'),
        ];
    }

    /**
     * Получить бейдж с количеством оценок
     */
    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    /**
     * Цвет бейджа
     */
    public static function getNavigationBadgeColor(): ?string
    {
        return 'success';
    }
}