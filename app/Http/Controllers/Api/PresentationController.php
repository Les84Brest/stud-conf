<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Presentation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PresentationController extends Controller
{
    /**
     * Список докладов для мероприятия с оценками текущего эксперта.
     */
    public function byEvent(Request $request, Event $event): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Не авторизован'], 401);
        }

        // Проверка доступа эксперта к мероприятию
        if ($user->isExpert() && !$event->experts()->where('users.id', $user->id)->exists()) {
            return response()->json(['message' => 'Доступ запрещён'], 403);
        }

        $presentations = Presentation::query()
            ->where('event_id', $event->id)
            ->with([
                'authors:id,full_name,university,faculty,group_number',
            ])
            ->withCount('assessments')
            ->withAvg('assessments', 'total_score')
            ->with([
                // Оценка текущего эксперта (если есть)
                'assessments' => function ($q) use ($user) {
                    $q->where('expert_id', $user->id);
                },
            ])
            ->orderBy('title')
            ->get()
            ->map(function ($presentation) use ($user) {
                $myAssessment = $presentation->assessments->first();

                return [
                    'id' => $presentation->id,
                    'title' => $presentation->title,
                    'abstract' => $presentation->abstract,
                    'status' => $presentation->status,
                    'submitted_at' => $presentation->submitted_at,
                    'authors' => $presentation->authors,
                    'assessments_count' => $presentation->assessments_count,
                    'assessments_avg' => $presentation->assessments_avg
                        ? round((float) $presentation->assessments_avg, 2)
                        : null,
                    'my_assessment' => $myAssessment ? [
                        'id' => $myAssessment->id,
                        'total_score' => $myAssessment->total_score,
                        'criteria_values' => $myAssessment->criteria_values,
                        'comment' => $myAssessment->comment,
                        'saved_at' => $myAssessment->saved_at,
                    ] : null,
                ];
            });

        return response()->json([
            'data' => $presentations,
        ]);
    }

public function show(Request $request, Presentation $presentation): JsonResponse
{
    $user = $request->user();

    $presentation->load([
        'event:id,title,max_score,conference_id',
        'event.criteria' => function ($q) {
            $q->where('is_active', true)->orderBy('sort_order');
        },
        'authors',
    ]);

    // Проверка доступа эксперта к мероприятию
    if ($user->isExpert() && !$presentation->event->experts()->where('users.id', $user->id)->exists()) {
        return response()->json(['message' => 'Доступ запрещён'], 403);
    }

    $myAssessment = $presentation->assessments()
        ->where('expert_id', $user->id)
        ->first();

    return response()->json([
        'data' => [
            'id' => $presentation->id,
            'title' => $presentation->title,
            'abstract' => $presentation->abstract,
            'status' => $presentation->status,
            'file_path' => $presentation->file_path,
            'video_link' => $presentation->video_link,
            'submitted_at' => $presentation->submitted_at,
            'event' => [
                'id' => $presentation->event->id,
                'title' => $presentation->event->title,
                'max_score' => $presentation->event->max_score,
                'criteria' => $presentation->event->criteria->map(fn ($c) => [
                    'id' => $c->id,
                    'key' => $c->key,
                    'name' => $c->name,
                    'max_value' => $c->pivot->max_value_override ?? $c->max_value,
                    'description' => $c->description,
                    'sort_order' => $c->sort_order,
                ]),
            ],
            'authors' => $presentation->authors,
            'my_assessment' => $myAssessment ? [
                'id' => $myAssessment->id,
                'total_score' => $myAssessment->total_score,
                'criteria_values' => $myAssessment->criteria_values,
                'comment' => $myAssessment->comment,
                'saved_at' => $myAssessment->saved_at,
            ] : null,
        ],
    ]);
}
}