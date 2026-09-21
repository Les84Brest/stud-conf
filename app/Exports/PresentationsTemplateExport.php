<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class PresentationsTemplateExport implements
    FromArray,
    WithHeadings,
    ShouldAutoSize,
    WithStyles,
    WithTitle
{
    public function array(): array
    {
        return [
            [
                'Иванов Иван Иванович',
                'ivanov@example.com',
                'МГУ',
                'Экономический',
                'Э-401',
                'Применение блокчейна в бухгалтерском учёте',
                'Исследование возможностей блокчейн-технологий...',
                'Петров Пётр; Сидорова Анна',
            ],
        ];
    }

    public function headings(): array
    {
        return [
            'ФИО докладчика',
            'Email докладчика',
            'Университет',
            'Факультет',
            'Группа',
            'Название доклада',
            'Аннотация',
            'Соавторы',
        ];
    }

    public function title(): string
    {
        return 'Шаблон импорта';
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