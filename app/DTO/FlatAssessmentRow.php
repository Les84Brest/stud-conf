<?php

namespace App\DTO;

class FlatAssessmentRow
{
    public function __construct(
        public readonly int $presentationId,
        public readonly string $presentationTitle,
        public readonly string $authors,
        public readonly int $eventId,
        public readonly string $eventTitle,
        public readonly string $conferenceTitle,
        public readonly int $expertId,
        public readonly string $expertName,
        /** @var array<string, int> key => value */
        public readonly array $criteriaValues,
        public readonly int $totalScore,
        public readonly int $maxScore,
        public readonly ?string $comment,
        public readonly ?string $savedAt,
    ) {}

    /**
     * Преобразовать в плоский массив для Excel/таблицы.
     *
     * @param  array<int, \App\Models\Criteria>  $criteria
     * @return array<string, mixed>
     */
    public function toArray(array $criteria): array
    {
        $row = [
            'Доклад' => $this->presentationTitle,
            'Авторы' => $this->authors,
            'Мероприятие' => $this->eventTitle,
            'Конференция' => $this->conferenceTitle,
            'Эксперт' => $this->expertName,
        ];

        foreach ($criteria as $criterion) {
            $row[$criterion->name] = $this->criteriaValues[$criterion->key] ?? 0;
        }

        $row['Итоговый балл'] = $this->totalScore;
        $row['Максимум'] = $this->maxScore;
        $row['Процент'] = $this->maxScore > 0
            ? round(($this->totalScore / $this->maxScore) * 100, 1) . '%'
            : '0%';
        $row['Комментарий'] = $this->comment ?? '';
        $row['Дата оценки'] = $this->savedAt
            ? \Carbon\Carbon::parse($this->savedAt)->format('d.m.Y H:i')
            : '';

        return $row;
    }
}