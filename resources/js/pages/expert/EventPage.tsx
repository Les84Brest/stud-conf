// resources/js/pages/expert/EventPage.tsx
import { useEffect } from "react";
import { Link, useParams } from "react-router-dom";
import {
    AlertCircle,
    ArrowLeft,
    CheckCircle2,
    Clock,
    FileText,
    User,
} from "lucide-react";
import { observer } from "mobx-react-lite";
import { AppShell } from "@/components/layout/app-shell";
import { Badge } from "@/components/ui/badge";
import { Button } from "@/components/ui/button";
import { Progress } from "@/components/ui/progress";
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from "@/components/ui/card";
import { useStore } from "@/context/StoreContext";
import { cn } from "@/lib/utils";
import type { Presentation } from "@/types";

// ============== Утилиты ==============
function formatDate(iso: string | null): string {
    if (!iso) return "—";
    try {
        return new Date(iso).toLocaleDateString("ru-RU", {
            day: "numeric",
            month: "short",
            year: "numeric",
        });
    } catch {
        return iso;
    }
}

function getStatusBadge(status: Presentation["status"]) {
    switch (status) {
        case "approved":
            return { label: "Одобрен", variant: "success" as const };
        case "rejected":
            return { label: "Отклонён", variant: "danger" as const };
        case "presented":
            return { label: "Представлен", variant: "default" as const };
        case "submitted":
            return { label: "На рассмотрении", variant: "warning" as const };
        default:
            return { label: "Черновик", variant: "neutral" as const };
    }
}

// ============== Карточка доклада ==============
const PresentationCard = observer(function PresentationCard({
    presentation,
    eventId,
}: {
    presentation: Presentation;
    eventId: number;
}) {
    const isAssessed = presentation.my_assessment !== null;
    const statusBadge = getStatusBadge(presentation.status);

    return (
        <Card className="group gap-0 overflow-hidden p-0 transition-shadow hover:shadow-md">
            <CardHeader className="p-5 pb-3">
                <div className="flex items-start justify-between gap-3">
                    <div className="flex flex-wrap items-center gap-2">
                        <Badge variant={statusBadge.variant}>
                            {statusBadge.label}
                        </Badge>
                        {isAssessed ? (
                            <Badge variant="success">
                                <CheckCircle2 className="size-3" />
                                Оценено:{" "}
                                {presentation.my_assessment?.total_score}
                            </Badge>
                        ) : (
                            <Badge variant="warning">
                                <Clock className="size-3" />
                                Ожидает оценки
                            </Badge>
                        )}
                    </div>
                </div>

                <CardTitle className="mt-3 text-base leading-snug">
                    {presentation.title}
                </CardTitle>

                {presentation.authors && presentation.authors.length > 0 && (
                    <CardDescription className="mt-2 flex flex-wrap items-center gap-x-3 gap-y-1">
                        <span className="inline-flex items-center gap-1.5">
                            <User className="size-3.5" />
                            {presentation.authors
                                .map((a) => a.full_name)
                                .join(", ")}
                        </span>
                    </CardDescription>
                )}
            </CardHeader>

            {presentation.abstract && (
                <CardContent className="px-5 pb-4 pt-0">
                    <p className="line-clamp-2 text-sm text-muted-foreground">
                        {presentation.abstract}
                    </p>
                </CardContent>
            )}

            <div className="mt-auto flex items-center justify-between border-t border-border px-5 py-3">
                <div className="flex items-center gap-3 text-xs text-muted-foreground">
                    <span className="inline-flex items-center gap-1">
                        <FileText className="size-3.5" />
                        Оценок: {presentation.assessments_count ?? 0}
                    </span>
                    {presentation.assessments_avg != null && (
                        <span className="tabular-nums">
                            Ср. балл: {presentation.assessments_avg}
                        </span>
                    )}
                </div>

                <Link
                    to={`/events/${eventId}/presentations/${presentation.id}`}
                >
                    <Button
                        size="sm"
                        variant={isAssessed ? "outline" : "default"}
                    >
                        {isAssessed ? "Изменить оценку" : "Оценить"}
                    </Button>
                </Link>
            </div>
        </Card>
    );
});

// ============== Страница ==============
const EventPage = observer(function EventPage() {
    const { eventId } = useParams<{ eventId: string }>();
    const numericEventId = Number(eventId);
    const { events, presentations } = useStore();

    const event = events.items.find((e) => e.id === numericEventId);

    useEffect(() => {
        if (!Number.isFinite(numericEventId)) return;

        // Загружаем доклады для мероприятия
        void presentations.fetchByEvent(numericEventId);
    }, [numericEventId, presentations]);

    // Если мероприятие не найдено в сторе — грузим все мероприятия
    useEffect(() => {
        if (!event && events.items.length === 0 && !events.loading) {
            void events.fetchMyEvents();
        }
    }, [event, events]);

    if (!Number.isFinite(numericEventId)) {
        return (
            <AppShell title="Ошибка" breadcrumb="Главная / Ошибка">
                <div className="rounded-lg border border-destructive/30 bg-destructive/10 p-6 text-center text-destructive">
                    <p>Некорректный идентификатор мероприятия</p>
                </div>
            </AppShell>
        );
    }

    return (
        <AppShell
            title={event?.title ?? "Мероприятие"}
            breadcrumb={
                <span className="inline-flex items-center gap-1.5">
                    <Link
                        to="/dashboard"
                        className="hover:text-foreground inline-flex items-center gap-1"
                    >
                        <ArrowLeft className="size-3" />
                        Панель
                    </Link>
                    <span>/</span>
                    <span>Мероприятие</span>
                </span>
            }
            actions={
                event ? (
                    <Badge variant="outline">
                        {event.type === "section" && "Секция"}
                        {event.type === "olympiad" && "Олимпиада"}
                        {event.type === "round_table" && "Круглый стол"}
                        {event.type === "master_class" && "Мастер-класс"}
                    </Badge>
                ) : null
            }
        >
            {/* Мета мероприятия */}
            {event && (
                <section className="mb-8">
                    <div className="flex flex-wrap items-center gap-4 text-sm text-muted-foreground">
                        {event.room && <span>Аудитория: {event.room}</span>}
                        <span>Начало: {formatDate(event.start_time)}</span>
                        <span>Максимум баллов: {event.max_score}</span>
                    </div>

                    {event.description && (
                        <p className="mt-3 text-sm text-muted-foreground">
                            {event.description}
                        </p>
                    )}
                </section>
            )}

            {/* Прогресс оценки */}
            {presentations.items.length > 0 && (
                <Card className="mb-6">
                    <CardContent className="p-5">
                        <div className="mb-2 flex items-center justify-between text-sm">
                            <span className="font-medium">
                                Прогресс ваших оценок
                            </span>
                            <span className="tabular-nums text-muted-foreground">
                                {presentations.assessedByMe}/
                                {presentations.totalCount} докладов
                            </span>
                        </div>
                        <Progress value={presentations.myProgress} />
                    </CardContent>
                </Card>
            )}

            {/* Ошибка */}
            {presentations.error && (
                <div className="mb-6 flex items-start gap-3 rounded-lg border border-destructive/30 bg-destructive/10 p-4 text-sm text-destructive">
                    <AlertCircle className="mt-0.5 size-5 shrink-0" />
                    <div className="flex-1">
                        <p className="font-medium">Ошибка загрузки докладов</p>
                        <p className="mt-1 text-destructive/80">
                            {presentations.error}
                        </p>
                        <Button
                            variant="outline"
                            size="sm"
                            onClick={() =>
                                void presentations.fetchByEvent(
                                    numericEventId,
                                    true,
                                )
                            }
                            className="mt-3 border-destructive/30 text-destructive hover:bg-destructive/10"
                        >
                            Повторить
                        </Button>
                    </div>
                </div>
            )}

            {/* Список докладов */}
            <section aria-label="Доклады">
                <div className="mb-4 flex items-center justify-between">
                    <h2 className="text-lg font-semibold">Доклады</h2>
                    {!presentations.loading &&
                        presentations.items.length > 0 && (
                            <span className="text-sm text-muted-foreground">
                                Всего: {presentations.totalCount}
                            </span>
                        )}
                </div>

                {/* Загрузка */}
                {presentations.loading && presentations.items.length === 0 && (
                    <div className="grid gap-4 md:grid-cols-2">
                        {[1, 2, 3, 4].map((i) => (
                            <div
                                key={i}
                                className="h-48 animate-pulse rounded-xl bg-muted"
                            />
                        ))}
                    </div>
                )}

                {/* Пусто */}
                {!presentations.loading &&
                    presentations.items.length === 0 &&
                    !presentations.error && (
                        <div className="rounded-xl border border-dashed border-border p-12 text-center">
                            <FileText className="mx-auto size-10 text-muted-foreground/50" />
                            <p className="mt-3 font-medium">
                                Пока нет докладов
                            </p>
                            <p className="mt-1 text-sm text-muted-foreground">
                                Доклады появятся после того, как их
                                зарегистрирует администратор
                            </p>
                        </div>
                    )}

                {/* Список */}
                {presentations.items.length > 0 && (
                    <div className="grid gap-4 md:grid-cols-2">
                        {presentations.items.map((p) => (
                            <PresentationCard
                                key={p.id}
                                presentation={p}
                                eventId={numericEventId}
                            />
                        ))}
                    </div>
                )}
            </section>
        </AppShell>
    );
});

export default EventPage;
