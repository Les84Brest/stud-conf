<?php

namespace App\Services;

use App\Models\Author;
use App\Models\Event;
use App\Models\Presentation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PresentationImportService
{
    /**
     * Импортировать доклады для мероприятия.
     *
     * @param  Event  $event  Мероприятие
     * @param  array<int, array<string, mixed>>  $rows  Строки из Excel
     * @return array{created: int, skipped: int, errors: array<int, array{row: int, message: string}>}
     */
    public function import(Event $event, array $rows): array
    {
        $created = 0;
        $skipped = 0;
        $errors = [];

        DB::beginTransaction();

        try {
            foreach ($rows as $index => $row) {
                $rowNumber = $index + 2; // +2, потому что первая строка — заголовки

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

    /**
     * Импортировать одну строку.
     *
     * @throws \RuntimeException
     */
    protected function importRow(Event $event, array $row): void
    {
        // Нормализуем ключи (убираем пробелы, приводим к snake_case)
        $row = $this->normalizeKeys($row);

        // Обязательные поля
        $presenterName = trim($row['фио_докладчика'] ?? $row['fio_dokladchika'] ?? '');
        $presentationTitle = trim($row['название_доклада'] ?? $row['nazvanie_doklada'] ?? '');

        if ($presenterName === '') {
            throw new \RuntimeException('Не указано ФИО докладчика');
        }

        if ($presentationTitle === '') {
            throw new \RuntimeException('Не указано название доклада');
        }

        // Проверка на дубликат (по названию в этом мероприятии)
        $exists = Presentation::where('event_id', $event->id)
            ->where('title', $presentationTitle)
            ->exists();

        if ($exists) {
            throw new \RuntimeException("Доклад уже существует: {$presentationTitle}");
        }

        // Создаём или находим основного докладчика
        $presenter = $this->findOrCreateAuthor([
            'full_name' => $presenterName,
            'email' => $this->nullable($row['email_докладчика'] ?? $row['email'] ?? null),
            'university' => $this->nullable($row['университет'] ?? $row['university'] ?? null),
            'faculty' => $this->nullable($row['факультет'] ?? $row['faculty'] ?? null),
            'group_number' => $this->nullable($row['группа'] ?? $row['group_number'] ?? null),
        ]);

        // Создаём доклад
        $presentation = Presentation::create([
            'event_id' => $event->id,
            'title' => $presentationTitle,
            'abstract' => $this->nullable($row['аннотация'] ?? $row['abstract'] ?? null),
            'status' => Presentation::STATUS_SUBMITTED,
            'submitted_at' => now(),
        ]);

        // Привязываем основного докладчика
        $presentation->authors()->attach($presenter->id, [
            'is_presenter' => true,
            'is_corresponding' => true,
            'order' => 0,
        ]);

        // Обрабатываем соавторов
        $coAuthorsRaw = trim($row['соавторы'] ?? $row['co_authors'] ?? '');
        if ($coAuthorsRaw !== '') {
            $coAuthorNames = array_filter(
                array_map('trim', preg_split('/[;,]/', $coAuthorsRaw))
            );

            foreach ($coAuthorNames as $order => $coAuthorName) {
                if ($coAuthorName === '') {
                    continue;
                }

                $coAuthor = $this->findOrCreateAuthor([
                    'full_name' => $coAuthorName,
                ]);

                // Пропускаем, если совпадает с основным докладчиком
                if ($coAuthor->id === $presenter->id) {
                    continue;
                }

                $presentation->authors()->syncWithoutDetaching([
                    $coAuthor->id => [
                        'is_presenter' => false,
                        'is_corresponding' => false,
                        'order' => $order + 1,
                    ],
                ]);
            }
        }
    }

    /**
     * Найти или создать автора.
     * Ищем по ФИО (без учёта регистра) или по email.
     */
    protected function findOrCreateAuthor(array $data): Author
    {
        $fullName = trim($data['full_name']);

        // Сначала ищем по email, если он есть
        if (!empty($data['email'])) {
            $existing = Author::where('email', $data['email'])->first();
            if ($existing) {
                // Обновляем недостающие поля
                $existing->update(array_filter([
                    'full_name' => $existing->full_name ?: $fullName,
                    'university' => $existing->university ?: ($data['university'] ?? null),
                    'faculty' => $existing->faculty ?: ($data['faculty'] ?? null),
                    'group_number' => $existing->group_number ?: ($data['group_number'] ?? null),
                ]));
                return $existing;
            }
        }

        // Ищем по ФИО
        $existing = Author::whereRaw('LOWER(full_name) = ?', [mb_strtolower($fullName)])->first();
        if ($existing) {
            return $existing;
        }

        // Создаём нового
        return Author::create([
            'full_name' => $fullName,
            'email' => $data['email'] ?? null,
            'university' => $data['university'] ?? null,
            'faculty' => $data['faculty'] ?? null,
            'group_number' => $data['group_number'] ?? null,
        ]);
    }

    /**
     * Нормализовать ключи строки (транслит → snake_case).
     */
    protected function normalizeKeys(array $row): array
    {
        $normalized = [];
        foreach ($row as $key => $value) {
            $normalizedKey = Str::of($key)
                ->trim()
                ->lower()
                ->replaceMatches('/\s+/', '_')
                ->toString();
            $normalized[$normalizedKey] = $value;
        }
        return $normalized;
    }

    /**
     * Пустая строка → null.
     */
    protected function nullable(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }
        $trimmed = trim($value);
        return $trimmed === '' ? null : $trimmed;
    }
}