// resources/js/pages/expert/DashboardPage.tsx
import {
    CalendarCheck,
    ClipboardList,
    FileStack,
    Timer,
    AlertCircle,
} from "lucide-react";
import { observer } from "mobx-react-lite";
import { useEffect } from "react";
import { AppShell } from "@/components/layout/app-shell";
import { StatCard } from "@/components/common/stat-card";
import { EventCard } from "@/components/common/event-card";
import { Button } from "@/components/ui/button";
import { useAuth } from "@/hooks/useAuth";
import { useStore } from "@/context/StoreContext";
import { getGreetingName } from "@/lib/utils";

const DashboardPage = observer(function DashboardPage() {
    const auth = useAuth();
    const { events } = useStore();

    useEffect(() => {
        if (events.items.length === 0 && !events.loading) {
            void events.fetchMyEvents();
        }
    }, [events]);

    return (
        <AppShell title="Панель" breadcrumb="Главная / Панель">
            <section className="mb-8">
                <h2 className="text-xl font-semibold md:text-2xl">
                    Добрый день, {getGreetingName(auth.user?.name)}
                </h2>
                <p className="mt-1 text-sm text-muted-foreground">
                    Обзор мероприятий, на которых вы являетесь экспертом, и
                    прогресс ваших оценок.
                </p>
            </section>

            {/* ============ Ошибка загрузки ============ */}
            {events.error && (
                <div className="mb-6 flex items-start gap-3 rounded-lg border border-destructive/30 bg-destructive/10 p-4 text-sm text-destructive">
                    <AlertCircle className="mt-0.5 size-5 shrink-0" />
                    <div className="flex-1">
                        <p className="font-medium">
                            Ошибка загрузки мероприятий
                        </p>
                        <p className="mt-1 text-destructive/80">
                            {events.error}
                        </p>
                        <Button
                            variant="outline"
                            size="sm"
                            onClick={() => void events.fetchMyEvents()}
                            className="mt-3 border-destructive/30 text-destructive hover:bg-destructive/10"
                        >
                            Повторить
                        </Button>
                    </div>
                </div>
            )}

            {/* ============ Статистика ============ */}
            <section
                aria-label="Общая статистика"
                className="mb-10 grid grid-cols-2 gap-4 lg:grid-cols-4"
            >
                <StatCard
                    label="Активных мероприятий"
                    value={events.totalCount}
                    icon={CalendarCheck}
                    accent="deep"
                    hint="Вы в составе комиссии"
                />
                <StatCard
                    label="Докладов назначено"
                    value={events.totalPresentations}
                    icon={FileStack}
                    accent="primary"
                />
                <StatCard
                    label="Оценено"
                    value={events.completedAssessments}
                    icon={ClipboardList}
                    accent="success"
                />
                <StatCard
                    label="Ожидают оценки"
                    value={events.pendingAssessments}
                    icon={Timer}
                    accent="warning"
                />
            </section>

            {/* ============ Мероприятия ============ */}
            <section aria-label="Ваши мероприятия">
                <div className="mb-4 flex items-center justify-between">
                    <h2 className="text-lg font-semibold">Ваши мероприятия</h2>
                    {!events.loading && events.items.length > 0 && (
                        <span className="text-sm text-muted-foreground">
                            Всего: {events.totalCount}
                        </span>
                    )}
                </div>

                {/* Состояние: загрузка */}
                {events.loading && events.items.length === 0 && (
                    <div className="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                        {[1, 2, 3].map((i) => (
                            <div
                                key={i}
                                className="h-52 animate-pulse rounded-xl bg-muted"
                            />
                        ))}
                    </div>
                )}

                {/* Состояние: пусто */}
                {!events.loading &&
                    events.items.length === 0 &&
                    !events.error && (
                        <div className="rounded-xl border border-dashed border-border p-12 text-center">
                            <CalendarCheck className="mx-auto size-10 text-muted-foreground/50" />
                            <p className="mt-3 font-medium">
                                У вас пока нет активных мероприятий
                            </p>
                            <p className="mt-1 text-sm text-muted-foreground">
                                Когда администратор назначит вас экспертом,
                                мероприятия появятся здесь
                            </p>
                        </div>
                    )}

                {/* Состояние: есть данные */}
                {events.items.length > 0 && (
                    <div className="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                        {events.items.map((event) => (
                            <EventCard key={event.id} event={event} />
                        ))}
                    </div>
                )}
            </section>
        </AppShell>
    );
});

export default DashboardPage;
