<?php

namespace App\DTO;

class AssessmentReportRow
{
    public function __construct(
        public readonly int $presentationId,
        public readonly string $presentationTitle,
        public readonly string $authors,
        public readonly string $eventTitle,
        public readonly string $conferenceTitle,
        /** @var array<int, array{expert: string, total: int, criteria: array<string, int>}> */
        public readonly array $expertAssessments,
        public readonly float $averageScore,
        public readonly int $sumScore,
        public readonly int $maxScore,
        public readonly int $assessmentsCount,
    ) {}

    /**
     * Возвращает оценки по критериям для конкретного эксперта.
     */
    public function getExpertCriteria(int $expertIndex): array
    {
        return $this->expertAssessments[$expertIndex]['criteria'] ?? [];
    }
}