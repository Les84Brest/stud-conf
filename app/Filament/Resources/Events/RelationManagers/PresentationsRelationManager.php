<?php

namespace App\Filament\Resources\Events\RelationManagers;

use Filament\Actions\AssociateAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DissociateAction;
use Filament\Actions\DissociateBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PresentationsRelationManager extends RelationManager
{
    protected static string $relationship = 'presentations';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('title'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('Название')
                    ->searchable()
                    ->wrap(),

                TextColumn::make('authors_string') // Используем ваш аксессор
                    ->label('Авторы')
                    ->wrap(),

                TextColumn::make('status')
                    ->label('Статус')
                    ->badge(),

                TextColumn::make('assessments_count')
                    ->label('Оценок')
                    ->counts('assessments'),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
