<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class UsersTemplateExport implements
    FromArray,
    WithHeadings,
    ShouldAutoSize,
    WithStyles,
    WithTitle
{
    public function array(): array
    {
        return [
            ['Петров Пётр Петрович', 'petrov@example.com', 'expert123'],
        ];
    }

    public function headings(): array
    {
        return ['ФИО', 'Email', 'Пароль'];
    }

    public function title(): string
    {
        return 'Шаблон импорта экспертов';
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'DBEAFE'],
                ],
            ],
        ];
    }
}