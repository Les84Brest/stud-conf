<?php

namespace App\Exports;

use App\Models\Event;
use App\Services\ReportService;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class EventReportExport implements
    FromArray,
    WithHeadings,
    ShouldAutoSize,
    WithStyles,
    WithTitle
{
    private array $rows;
    private array $headings;

    public function __construct(
        private readonly Event $event,
        ReportService $reportService,
    ) {
        $criteria = $reportService->getEventCriteria($event);
        $flatRows = $reportService->getFlatReport($event);

        // Каждую DTO превращаем в плоский массив
        $this->rows = $flatRows
            ->map(fn ($row) => array_values($row->toArray($criteria->all())))
            ->all();

        // Заголовки — из первой строки (если есть данные)
        $this->headings = !empty($this->rows)
            ? array_keys($flatRows->first()->toArray($criteria->all()))
            : $criteria->pluck('name')->prepend('Нет данных')->all();
    }

    public function array(): array
    {
        return $this->rows;
    }

    public function headings(): array
    {
        return $this->headings;
    }

    public function title(): string
    {
        return 'Плоская ведомость';
    }

    public function styles(Worksheet $sheet): array
    {
        $lastColumn = $sheet->getHighestColumn();
        $lastRow = $sheet->getHighestRow();

        return [
            1 => [
                'font' => ['bold' => true, 'size' => 11],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'E2E8F0'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                    'wrapText' => true,
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => 'CBD5E1'],
                    ],
                ],
            ],
            "A1:{$lastColumn}{$lastRow}" => [
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => 'E2E8F0'],
                    ],
                ],
            ],
        ];
    }
}