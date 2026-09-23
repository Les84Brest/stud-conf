<?php
// app/Filament/Resources/Users/Pages/ListUsers.php

namespace App\Filament\Resources\Users\Pages;

use App\Exports\UsersTemplateExport;
use App\Filament\Actions\ImportUsersAction;
use App\Filament\Resources\Users\UserResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Maatwebsite\Excel\Facades\Excel;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ImportUsersAction::make(),
            Action::make('download_template')
                ->label('Скачать шаблон')
                ->icon('heroicon-o-document-arrow-down')
                ->color('gray')
                ->action(function () {
                    return Excel::download(
                        new UsersTemplateExport(),
                        'template_users.xlsx'
                    );
                }),
            CreateAction::make()
                ->label('Новый пользователь')
                ->icon('heroicon-o-plus'),
        ];
    }
}