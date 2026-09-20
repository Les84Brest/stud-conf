// resources/js/components/common/event-card.tsx
import { Link } from 'react-router-dom';
import { ArrowRight, CalendarDays, MapPin } from 'lucide-react';
import { Card } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Progress } from '@/components/ui/progress';
import type { Event, EventType } from '@/types';

// ============== Утилиты ==============
const eventTypeLabels: Record<EventType, string> = {
    section: 'Секция',
    olympiad: 'Олимпиада',
    round_table: 'Круглый стол',
    master_class: 'Мастер-класс',
};

const eventTypeVariants: Record<
    EventType,
    'default' | 'success' | 'warning' | 'danger' | 'neutral' | 'outline'
> = {
    section: 'default',
    olympiad: 'success',
    round_table: 'warning',
    master_class: 'neutral',
};

function formatDate(iso: string): string {
    try {
        const date = new Date(iso);
        return date.toLocaleDateString('ru-RU', {
            day: 'numeric',
            month: 'short',
            year: 'numeric',
        });
    } catch {
        return iso;
    }
}

function getEventProgress(event: Event): {
    completed: number;
    total: number;
    percent: number;
} {
    const total = event.presentations_count ?? 0;
    const completed = event.assessments_count ?? 0;
    const percent = total > 0 ? Math.round((completed / total) * 100) : 0;
    return { completed, total, percent };
}

// ============== Компонент ==============
interface EventCardProps {
    event: Event;
}

export function EventCard({ event }: EventCardProps) {
    const { completed, total, percent } = getEventProgress(event);

    return (
        <Card className="group gap-0 overflow-hidden p-0 transition-shadow hover:shadow-md">
            <Link
                to={`/events/${event.id}`}
                className="flex h-full flex-col p-5 focus:outline-none"
            >
                {/* Заголовок + бейдж + стрелка */}
                <div className="flex items-start justify-between gap-3">
                    <Badge variant={eventTypeVariants[event.type]}>
                        {eventTypeLabels[event.type]}
                    </Badge>
                    <ArrowRight className="size-4 text-muted-foreground transition-transform group-hover:translate-x-0.5 group-hover:text-primary" />
                </div>

                {/* Название */}
                <h3 className="mt-3 text-pretty text-lg font-semibold leading-snug">
                    {event.title}
                </h3>

                {/* Дата и место */}
                <div className="mt-3 flex flex-wrap items-center gap-x-4 gap-y-1 text-sm text-muted-foreground">
                    <span className="inline-flex items-center gap-1.5">
                        <CalendarDays className="size-4" />
                        {formatDate(event.start_time)}
                    </span>
                    {event.room && (
                        <span className="inline-flex items-center gap-1.5">
                            <MapPin className="size-4" />
                            {event.room}
                        </span>
                    )}
                </div>

                {/* Описание */}
                {event.description && (
                    <p className="mt-3 line-clamp-2 text-sm text-muted-foreground">
                        {event.description}
                    </p>
                )}

                {/* Прогресс */}
                <div className="mt-auto pt-5">
                    <div className="mb-1.5 flex items-center justify-between text-sm">
                        <span className="font-medium">Прогресс оценок</span>
                        <span className="tabular-nums text-muted-foreground">
                            {completed}/{total} докладов
                        </span>
                    </div>
                    <Progress value={percent} />
                </div>
            </Link>
        </Card>
    );
}