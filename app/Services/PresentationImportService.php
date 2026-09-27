<?php

namespace App\Services;

use App\Models\Event;
use App\Models\Presentation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PresentationImportService
{
    public function import(Event $event, array $rows): array
    {
        $created = 0;
        $skipped = 0;
        $errors = [];

        DB::beginTransaction();

        try {
            foreach ($rows as $index => $row) {
                if ($this->isEmptyRow($row)) {
                    continue;
                }

                $rowNumber = $index + 2;

                try {
                    $this->importRow($event, $row);
                    $created++;
                } catch (\Throwable $e) {
                    $skipped++;
                    $errors[] = [
                        'row' => $rowNumber,
                        'message' => $e->getMessage(),
                    ];
                }
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        return [
            'created' => $created,
            'skipped' => $skipped,
            'errors' => $errors,
        ];
    }

    protected function importRow(Event $event, array $row): void
    {
        $row = $this->normalizeKeys($row);

        $presenterName = trim((string) ($row['фио_докладчика'] ?? ''));
        $presentationTitle = trim((string) ($row['название_доклада'] ?? ''));

        if ($presenterName === '') {
            throw new \RuntimeException('Не указано ФИО докладчика');
        }
        if ($presentationTitle === '') {
            throw new \RuntimeException('Не указано название доклада');
        }

        if (Presentation::where('event_id', $event->id)
            ->where('title', $presentationTitle)
            ->exists()) {
            throw new \RuntimeException("Доклад уже существует: {$presentationTitle}");
        }

        // Собираем авторов
        $authors = [
            [
                'full_name' => $presenterName,
                'email' => $this->nullable($row['email_докладчика'] ?? null),
                'university' => $this->nullable($row['университет'] ?? null),
                'faculty' => $this->nullable($row['факультет'] ?? null),
                'group_number' => $this->nullable($row['группа'] ?? null),
                'is_presenter' => true,
                'is_corresponding' => true,
                'order' => 0,
            ],
        ];

        $supervisors = [];
        $supervisorNames = array_filter(
            array_map('trim', preg_split('/[;,]/', (string) ($row['фио_научного_руководителя'] ?? $row['научный_руководитель'] ?? $row['руководитель'] ?? '')))
        );

        foreach ($supervisorNames as $order => $name) {
            if ($name === '') {
                continue;
            }

            $supervisors[] = [
                'full_name' => $name,
                'degree' => $this->nullable($row['учёная_степень_руководителя'] ?? $row['ученая_степень_руководителя'] ?? null),
                'position' => $this->nullable($row['должность_руководителя'] ?? null),
                'email' => null,
                'order' => $order,
            ];
        }

        // Соавторы
        $coAuthorsRaw = trim((string) ($row['соавторы'] ?? ''));
        if ($coAuthorsRaw !== '') {
            $coAuthorNames = array_filter(
                array_map('trim', preg_split('/[;,]/', $coAuthorsRaw))
            );

            foreach ($coAuthorNames as $order => $name) {
                if ($name === '' || $name === $presenterName) {
                    continue;
                }
                $authors[] = [
                    'full_name' => $name,
                    'is_presenter' => false,
                    'is_corresponding' => false,
                    'order' => $order + 1,
                ];
            }
        }

        Presentation::create([
            'event_id' => $event->id,
            'title' => $presentationTitle,
            'abstract' => $this->nullable($row['аннотация'] ?? null),
            'contributors' => [
                'authors' => $authors,
                'supervisors' => $supervisors,
            ],
            'status' => Presentation::STATUS_SUBMITTED,
            'submitted_at' => now(),
        ]);
    }

    protected function isEmptyRow(array $row): bool
    {
        foreach ($row as $value) {
            if ($value === null || $value === '') {
                continue;
            }

            if (is_string($value) && trim($value) === '') {
                continue;
            }

            return false;
        }

        return true;
    }

    protected function normalizeKeys(array $row): array
    {
        $normalized = [];
        foreach ($row as $key => $value) {
            $normalizedKey = Str::of((string) $key)
                ->trim()
                ->lower()
                ->replaceMatches('/\s+/', '_')
                ->toString();
            $normalized[$normalizedKey] = $value;
        }
        return $normalized;
    }

    protected function nullable(?string $value): ?string
    {
        if ($value === null) return null;
        $trimmed = trim($value);
        return $trimmed === '' ? null : $trimmed;
    }
}