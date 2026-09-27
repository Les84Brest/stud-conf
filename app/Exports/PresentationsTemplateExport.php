<?php
// app/Exports/PresentationsTemplateExport.php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class PresentationsTemplateExport implements
    FromArray,
    WithHeadings,
    ShouldAutoSize,
    WithStyles,
    WithTitle,
    WithColumnWidths
{
    /**
     * Примеры строк для шаблона.
     */
    public function array(): array
    {
        return [
            [
                'Попова Дарья Владимировна',
                'popova@example.com',
                'МГУ им. М.В. Ломоносова',
                'Экономический',
                'Э-401',
                'Анализ прибыли предприятия в современных условиях',
                'Исследование факторов, влияющих на формирование прибыли...',
                'Смирнов Иван Петрович; Кузнецова Ольга Сергеевна',
                'Козлов Сергей Николаевич',
                'Кандидат экономических наук',
                'Доцент кафедры финансов',
            ],
            [
                'Смирнова Екатерина Алексеевна',
                'smirnova@example.com',
                'МГУ им. М.В. Ломоносова',
                'Экономический',
                'Э-402',
                'Оптимизация затрат промышленного предприятия',
                'Анализ структуры затрат и методы их оптимизации...',
                '', // соавторов нет
                'Козлов Сергей Николаевич',
                'Кандидат экономических наук',
                'Доцент кафедры финансов',
            ],
        ];
    }

    /**
     * Заголовки колонок.
     */
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
            'ФИО научного руководителя',
            'Учёная степень руководителя',
            'Должность руководителя',
        ];
    }

    public function title(): string
    {
        return 'Шаблон импорта докладов';
    }

    public function styles(Worksheet $sheet): array
    {
        $lastColumn = $sheet->getHighestColumn();

        return [
            // Шапка — жирная, синеватая, с автопереносом
            1 => [
                'font' => ['bold' => true, 'size' => 11],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'DBEAFE'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                    'wrapText' => true,
                ],
            ],
            // Выделяем обязательные колонки другим цветом
            'A1:A1' => [
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'FECACA'],
                ],
            ],
            'F1:F1' => [
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'FECACA'],
                ],
            ],
            // Автоперенос для всех ячеек
            "A1:{$lastColumn}100" => [
                'alignment' => [
                    'vertical' => Alignment::VERTICAL_TOP,
                    'wrapText' => true,
                ],
            ],
        ];
    }

    /**
     * Ширина колонок.
     */
    public function columnWidths(): array
    {
        return [
            'A' => 30, // ФИО докладчика
            'B' => 25, // Email
            'C' => 28, // Университет
            'D' => 20, // Факультет
            'E' => 12, // Группа
            'F' => 45, // Название
            'G' => 50, // Аннотация
            'H' => 35, // Соавторы
            'I' => 30, // Руководитель
            'J' => 25, // Степень
            'K' => 25, // Должность
        ];
    }
}