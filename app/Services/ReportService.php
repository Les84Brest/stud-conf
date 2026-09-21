<?php

namespace App\Services;

use App\DTO\AssessmentReportRow;
use App\Models\Assessment;
use App\Models\Criteria;
use App\Models\Event;
use App\Models\Presentation;
use Illuminate\Support\Collection;

class ReportService
{
    /**
     * Получить сводную ведомость по мероприятию.
     *
     * @return Collection<int, AssessmentReportRow>
     */
    public function getEventReport(Event $event): Collection
    {
        // Загружаем критерии мероприятия
        $criteria = $event->criteria()
            ->where('is_active', true)
            ->orderBy('event_criteria.sort_order')
            ->get();

        // Максимальный балл мероприятия
        $maxScore = $event->getCalculatedMaxScore();

        // Загружаем доклады с авторами, оценками и экспертами
        $presentations = Presentation::query()
            ->where('event_id', $event->id)
            ->with([
                'authors:id,full_name',
                'assessments.expert:id,name',
            ])
            ->orderBy('title')
            ->get();

      
        return $presentations->map(function (Presentation $presentation) use ($criteria, $maxScore, $event) {
            $authors = $presentation->authors->pluck('full_name')->implode(', ');

            // Группируем оценки по экспертам
            $expertAssessments = $presentation->assessments
                ->map(function (Assessment $assessment) use ($criteria) {
                    $criteriaValues = $assessment->criteria_values ?? [];

                    // Собираем значения по всем критериям мероприятия
                    $criteriaMap = [];
                    foreach ($criteria as $criterion) {
                        $criteriaMap[$criterion->key] = $criteriaValues[$criterion->key] ?? 0;
                    }

                    return [
                        'expert' => $assessment->expert->name,
                        'total' => $assessment->total_score,
                        'criteria' => $criteriaMap,
                    ];
                })
                ->values()
                ->all();

            $totalScores = array_column($expertAssessments, 'total');
            $sumScore = array_sum($totalScores);
            $count = count($totalScores);
            $averageScore = $count > 0 ? round($sumScore / $count, 2) : 0.0;

            return new AssessmentReportRow(
                presentationId: $presentation->id,
                presentationTitle: $presentation->title,
                authors: $authors,
                eventTitle: $event->title,          // ← теперь $event доступен
                conferenceTitle: $event->conference->title, // ← и здесь
                expertAssessments: $expertAssessments,
                averageScore: $averageScore,
                sumScore: $sumScore,
                maxScore: $maxScore,
                assessmentsCount: $count,
            );
        });
    }

    /**
     * Собрать плоские строки для экспорта в Excel.
     * Каждая строка = доклад + разбивка по экспертам.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getFlatRows(Collection $rows, Collection $criteria): array
    {
        $flat = [];

        foreach ($rows as $row) {
            $line = [
                'Доклад' => $row->presentationTitle,
                'Авторы' => $row->authors,
                'Мероприятие' => $row->eventTitle,
                'Конференция' => $row->conferenceTitle,
                'Кол-во оценок' => $row->assessmentsCount,
            ];

            // Для каждого эксперта — свой блок колонок
            foreach ($row->expertAssessments as $index => $expertData) {
                $expertNumber = $index + 1;
                $line["Эксперт {$expertNumber}"] = $expertData['expert'];

                // Критерии
                foreach ($criteria as $criterion) {
                    $line["Эксперт {$expertNumber}: {$criterion->name}"] =
                        $expertData['criteria'][$criterion->key] ?? 0;
                }

                $line["Эксперт {$expertNumber}: Итог"] = $expertData['total'];
            }

            // Итоговые колонки
            $line['Сумма баллов'] = $row->sumScore;
            $line['Средний балл'] = $row->averageScore;
            $line['Макс. балл'] = $row->maxScore;

            $flat[] = $line;
        }

        return $flat;
    }
}