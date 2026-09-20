<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Assessment;
use App\Models\AssessmentLog;
use App\Models\Presentation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AssessmentController extends Controller
{
    /**
     * Сохранить (создать или обновить) оценку.
     */
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user->isExpert() && !$user->isAdmin()) {
            return response()->json(['message' => 'Только эксперты могут оценивать'], 403);
        }

        $validated = $request->validate([
            'presentation_id' => 'required|exists:presentations,id',
            'criteria_values' => 'required|array',
            'criteria_values.*' => 'required|numeric|min:0',
            'comment' => 'nullable|string|max:2000',
        ]);

        $presentation = Presentation::with('event.criteria')->findOrFail(
            $validated['presentation_id']
        );

        // Проверка доступа
        if ($user->isExpert() && !$presentation->event->experts()->where('users.id', $user->id)->exists()) {
            return response()->json(['message' => 'Доступ запрещён'], 403);
        }

        // Валидация критериев: ключи должны совпадать с критериями мероприятия
        $eventCriteria = $presentation->event->criteria->keyBy('key');
        $criteriaValues = $validated['criteria_values'];

        foreach ($criteriaValues as $key => $value) {
            $criterion = $eventCriteria->get($key);
            if (!$criterion) {
                throw ValidationException::withMessages([
                    "criteria_values.$key" => ["Неизвестный критерий: $key"],
                ]);
            }

            $max = $criterion->pivot->max_value_override ?? $criterion->max_value;
            if ($value > $max) {
                throw ValidationException::withMessages([
                    "criteria_values.$key" => ["Значение превышает максимум ($max)"],
                ]);
            }
        }

        $totalScore = (int) array_sum($criteriaValues);

        return DB::transaction(function () use (
            $user,
            $presentation,
            $criteriaValues,
            $totalScore,
            $validated
        ) {
            // Ищем существующую оценку
            $assessment = Assessment::where([
                'presentation_id' => $presentation->id,
                'expert_id' => $user->id,
                'event_id' => $presentation->event_id,
            ])->first();

            $isNew = $assessment === null;
            $oldValues = $assessment ? [
                'criteria_values' => $assessment->criteria_values,
                'total_score' => $assessment->total_score,
                'comment' => $assessment->comment,
            ] : null;

            if ($isNew) {
                $assessment = new Assessment();
                $assessment->presentation_id = $presentation->id;
                $assessment->expert_id = $user->id;
                $assessment->event_id = $presentation->event_id;
            }

            $assessment->criteria_values = $criteriaValues;
            $assessment->total_score = $totalScore;
            $assessment->comment = $validated['comment'] ?? null;
            $assessment->saved_at = now();
            $assessment->save();

            // Логируем изменение
            AssessmentLog::create([
                'assessment_id' => $assessment->id,
                'user_id' => $user->id,
                'old_values' => $oldValues,
                'new_values' => [
                    'criteria_values' => $criteriaValues,
                    'total_score' => $totalScore,
                    'comment' => $validated['comment'] ?? null,
                ],
                'action' => $isNew ? 'create' : 'update',
            ]);

            return response()->json([
                'message' => $isNew ? 'Оценка сохранена' : 'Оценка обновлена',
                'data' => [
                    'id' => $assessment->id,
                    'total_score' => $assessment->total_score,
                    'criteria_values' => $assessment->criteria_values,
                    'comment' => $assessment->comment,
                    'saved_at' => $assessment->saved_at,
                ],
            ], $isNew ? 201 : 200);
        });
    }
}