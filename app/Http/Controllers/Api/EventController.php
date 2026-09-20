<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EventController extends Controller
{
    /**
     * Список мероприятий, доступных текущему эксперту.
     *
     * - Админ видит все активные мероприятия.
     * - Эксперт видит только те, где он в комиссии.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Не авторизован'], 401);
        }

        $query = Event::query()
            ->where('is_active', true)
            ->with(['conference:id,title,slug'])
            ->withCount([
                'presentations',
                'experts',
                // Количество оценок, выставленных ТЕКУЩИМ экспертом
                'assessments as assessments_count' => function ($q) use ($user) {
                    $q->where('expert_id', $user->id);
                },
            ])
            ->orderBy('start_time');

        // Эксперт видит только свои мероприятия
        if ($user->isExpert()) {
            $query->whereHas('experts', function ($q) use ($user) {
                $q->where('users.id', $user->id);
            });
        }

        $events = $query->get();

        return response()->json([
            'data' => $events,
        ]);
    }

    /**
     * Детальная информация о мероприятии.
     */
    public function show(Request $request, Event $event): JsonResponse
    {
        $user = $request->user();

        // Проверяем доступ
        if ($user->isExpert() && !$event->experts()->where('users.id', $user->id)->exists()) {
            return response()->json(['message' => 'Доступ запрещён'], 403);
        }

        $event->load([
            'conference:id,title,slug',
            'criteria',
            'experts:id,name,email',
        ]);

        $event->loadCount([
            'presentations',
            'experts',
            'assessments as assessments_count' => function ($q) use ($user) {
                $q->where('expert_id', $user->id);
            },
        ]);

        return response()->json([
            'data' => $event,
        ]);
    }
}