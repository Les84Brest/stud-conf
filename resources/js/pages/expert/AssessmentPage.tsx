// resources/js/pages/expert/AssessmentPage.tsx
import { useEffect } from 'react';
import { Link, useNavigate, useParams } from 'react-router-dom';
import {
    AlertCircle,
    ArrowLeft,
    CheckCircle2,
    ExternalLink,
    FileText,
    Loader2,
    Save,
    User,
} from 'lucide-react';
import { observer } from 'mobx-react-lite';
import { AppShell } from '@/components/layout/app-shell';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Textarea } from '@/components/ui/textarea';
import { Label } from '@/components/ui/label';
import { Progress } from '@/components/ui/progress';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { StarRating } from '@/components/common/star-rating';
import { useStore } from '@/context/StoreContext';
import { cn } from '@/lib/utils';

const AssessmentPage = observer(function AssessmentPage() {
    const { eventId, presentationId } = useParams<{
        eventId: string;
        presentationId: string;
    }>();
    const numericPresentationId = Number(presentationId);
    const { assessment } = useStore();
    const navigate = useNavigate();

    // Загружаем доклад
    useEffect(() => {
        if (Number.isFinite(numericPresentationId)) {
            void assessment.fetchPresentation(numericPresentationId);
        }

        return () => {
            assessment.reset();
        };
    }, [numericPresentationId, assessment]);

    const handleSave = async () => {
        const success = await assessment.save();
        if (success) {
            // Возвращаемся на страницу мероприятия
            navigate(`/events/${eventId}`);
        }
    };

    // ============ Загрузка ============
    if (assessment.loading || !assessment.current) {
        return (
            <AppShell title="Загрузка..." breadcrumb="Главная / Оценка">
                <div className="flex items-center justify-center py-20">
                    <Loader2 className="size-8 animate-spin text-muted-foreground" />
                </div>
            </AppShell>
        );
    }

    // ============ Ошибка ============
    if (assessment.error) {
        return (
            <AppShell title="Ошибка" breadcrumb="Главная / Оценка">
                <div className="rounded-lg border border-destructive/30 bg-destructive/10 p-6 text-destructive">
                    <div className="flex items-start gap-3">
                        <AlertCircle className="mt-0.5 size-5" />
                        <div>
                            <p className="font-medium">
                                Не удалось загрузить доклад
                            </p>
                            <p className="mt-1 text-destructive/80">
                                {assessment.error}
                            </p>
                            <Button
                                variant="outline"
                                size="sm"
                                onClick={() =>
                                    void assessment.fetchPresentation(
                                        numericPresentationId,
                                    )
                                }
                                className="mt-3"
                            >
                                Повторить
                            </Button>
                        </div>
                    </div>
                </div>
            </AppShell>
        );
    }

    const presentation = assessment.current;
    const isAlreadyAssessed = assessment.isAssessed;

    return (
        <AppShell
            title={presentation.title}
            breadcrumb={
                <span className="inline-flex items-center gap-1.5">
                    <Link
                        to="/dashboard"
                        className="hover:text-foreground"
                    >
                        Панель
                    </Link>
                    <span>/</span>
                    <Link
                        to={`/events/${eventId}`}
                        className="hover:text-foreground"
                    >
                        {presentation.event.title}
                    </Link>
                    <span>/</span>
                    <span>Оценка</span>
                </span>
            }
            actions={
                isAlreadyAssessed ? (
                    <Badge variant="success">
                        <CheckCircle2 className="size-3" />
                        Сохранено: {presentation.my_assessment?.total_score}
                    </Badge>
                ) : null
            }
        >
            <div className="grid gap-6 lg:grid-cols-3">
                {/* ============ Левая колонка: доклад ============ */}
                <div className="space-y-6 lg:col-span-1">
                    <Card>
                        <CardHeader>
                            <CardTitle className="text-base">
                                О докладе
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-4 text-sm">
                            {presentation.authors.length > 0 && (
                                <div>
                                    <p className="text-xs font-medium text-muted-foreground uppercase">
                                        Авторы
                                    </p>
                                    <ul className="mt-1.5 space-y-1">
                                        {presentation.authors.map((a) => (
                                            <li
                                                key={a.id}
                                                className="flex items-start gap-2"
                                            >
                                                <User className="mt-0.5 size-4 shrink-0 text-muted-foreground" />
                                                <div>
                                                    <p className="font-medium">
                                                        {a.full_name}
                                                    </p>
                                                    {a.university && (
                                                        <p className="text-xs text-muted-foreground">
                                                            {a.university}
                                                        </p>
                                                    )}
                                                </div>
                                            </li>
                                        ))}
                                    </ul>
                                </div>
                            )}

                            {presentation.abstract && (
                                <div>
                                    <p className="text-xs font-medium text-muted-foreground uppercase">
                                        Аннотация
                                    </p>
                                    <p className="mt-1.5 whitespace-pre-line text-muted-foreground">
                                        {presentation.abstract}
                                    </p>
                                </div>
                            )}

                            {presentation.file_path && (
                                <a
                                    href={`/storage/${presentation.file_path}`}
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    className="inline-flex items-center gap-1.5 text-primary hover:underline"
                                >
                                    <FileText className="size-4" />
                                    Открыть презентацию
                                    <ExternalLink className="size-3" />
                                </a>
                            )}

                            {presentation.video_link && (
                                <a
                                    href={presentation.video_link}
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    className="inline-flex items-center gap-1.5 text-primary hover:underline"
                                >
                                    <ExternalLink className="size-4" />
                                    Посмотреть видео
                                </a>
                            )}
                        </CardContent>
                    </Card>
                </div>

                {/* ============ Правая колонка: форма оценки ============ */}
                <div className="lg:col-span-2 space-y-6">
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center justify-between">
                                <span>Оценка доклада</span>
                                <span className="text-base font-medium text-muted-foreground">
                                    {assessment.draftTotal} /{' '}
                                    {assessment.maxScore}
                                </span>
                            </CardTitle>
                            <CardDescription>
                                Оцените каждый критерий по шкале звёзд
                            </CardDescription>
                            <Progress
                                value={assessment.draftPercent}
                                className="mt-3"
                            />
                        </CardHeader>

                        <CardContent className="space-y-6">
                            {presentation.event.criteria.map((criterion) => {
                                const currentValue =
                                    assessment.draftValues[criterion.key] ?? 0;

                                return (
                                    <div
                                        key={criterion.id}
                                        className="space-y-2 rounded-lg border border-border p-4"
                                    >
                                        <div className="flex items-start justify-between gap-3">
                                            <div className="min-w-0 flex-1">
                                                <Label className="text-sm font-medium">
                                                    {criterion.name}
                                                </Label>
                                                {criterion.description && (
                                                    <p className="mt-1 text-xs text-muted-foreground">
                                                        {
                                                            criterion.description
                                                        }
                                                    </p>
                                                )}
                                            </div>
                                            <Badge
                                                variant="outline"
                                                className="shrink-0"
                                            >
                                                макс. {criterion.max_value}
                                            </Badge>
                                        </div>

                                        <StarRating
                                            value={currentValue}
                                            max={criterion.max_value}
                                            onChange={(v) =>
                                                assessment.setValue(
                                                    criterion.key,
                                                    v,
                                                )
                                            }
                                            size="lg"
                                        />
                                    </div>
                                );
                            })}
                        </CardContent>
                    </Card>

                    {/* Комментарий */}
                    <Card>
                        <CardHeader>
                            <CardTitle className="text-base">
                                Комментарий
                            </CardTitle>
                            <CardDescription>
                                Необязательно. Поможет другим экспертам понять
                                вашу оценку.
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <Textarea
                                value={assessment.draftComment}
                                onChange={(e) =>
                                    assessment.setComment(e.target.value)
                                }
                                placeholder="Введите комментарий..."
                                rows={4}
                                maxLength={2000}
                            />
                        </CardContent>
                    </Card>

                    {/* Кнопки */}
                    <div className="flex items-center justify-between gap-3">
                        <Button
                            variant="outline"
                            onClick={() => navigate(`/events/${eventId}`)}
                        >
                            <ArrowLeft className="size-4" />
                            Назад
                        </Button>

                        <Button
                            onClick={handleSave}
                            disabled={
                                assessment.saving ||
                                (!isAlreadyAssessed && !assessment.hasChanges)
                            }
                            size="lg"
                        >
                            {assessment.saving ? (
                                <>
                                    <Loader2 className="size-4 animate-spin" />
                                    Сохранение...
                                </>
                            ) : (
                                <>
                                    <Save className="size-4" />
                                    {isAlreadyAssessed
                                        ? 'Обновить оценку'
                                        : 'Сохранить оценку'}
                                </>
                            )}
                        </Button>
                    </div>
                </div>
            </div>
        </AppShell>
    );
});

export default AssessmentPage;