// resources/js/pages/expert/EventPage.tsx
import { useEffect } from "react";
import { Link, useParams } from "react-router-dom";
import {
    AlertCircle,
    ArrowLeft,
    CheckCircle2,
    Clock,
    FileText,
    GraduationCap,
    User,
} from "lucide-react";
import { observer } from "mobx-react-lite";
import { AppShell } from "@/components/layout/app-shell";
import { Badge } from "@/components/ui/badge";
import { Button } from "@/components/ui/button";
import { Progress } from "@/components/ui/progress";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { StatusBadge } from "@/components/common/status-badge";
import { useStore } from "@/context/StoreContext";
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

// ============== Карточка доклада ==============
const PresentationCard = observer(function PresentationCard({
    presentation,
    eventId,
}: {
    presentation: Presentation;
    eventId: number;
}) {
    const isAssessed = presentation.my_assessment !== null;
    const authors = presentation.authors ?? [];
    const supervisors = presentation.supervisors ?? [];

    return (
        <Card className="group gap-0 overflow-hidden p-0 transition-shadow hover:shadow-md">
            <CardHeader className="p-5 pb-3">
                <div className="flex items-start justify-between gap-3">
                    <div className="flex flex-wrap items-center gap-2">
                        <StatusBadge status={presentation.status} />
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

                {/* Авторы */}
                {authors.length > 0 && (
                    <span className="mt-2 inline-flex items-center gap-1.5 text-sm text-muted-foreground">
                        <User className="size-3.5" />
                        {authors.map((a) => a.full_name).join(", ")}
                    </span>
                )}

                {/* 🆕 Научный руководитель */}
                {supervisors.length > 0 && (
                    <span className="mt-1.5 inline-flex items-center gap-1.5 text-sm text-muted-foreground">
                        <GraduationCap className="size-3.5 text-primary" />
                        <span className="text-foreground/80">
                            {supervisors.map((s) => s.full_name).join(", ")}
                        </span>
                        {supervisors[0]?.degree && (
                            <span className="text-xs text-muted-foreground">
                                · {supervisors[0].degree}
                            </span>
                        )}
                    </span>
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

        void presentations.fetchByEvent(numericEventId);
    }, [numericEventId, presentations]);

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
