// resources/js/pages/expert/AssessmentPage.tsx
import { useEffect } from "react";
import { Link, useNavigate, useParams } from "react-router-dom";
import {
    AlertCircle,
    ArrowLeft,
    Check,
    ExternalLink,
    FileText,
    Loader2,
    Save,
    Tag,
    User,
} from "lucide-react";
import { observer } from "mobx-react-lite";
import { AppShell } from "@/components/layout/app-shell";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";
import { Textarea } from "@/components/ui/textarea";
import { Label } from "@/components/ui/label";
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from "@/components/ui/card";
import { ScoreSelector } from "@/components/common/score-selector";
import { SaveIndicator } from "@/components/common/save-indicator";
import { StatusBadge } from "@/components/common/status-badge";
import { RecommendationBadge } from "@/components/common/recommendation-badge";
import { useStore } from "@/context/StoreContext";
import { cn } from "@/lib/utils";

const AssessmentPage = observer(function AssessmentPage() {
    const { eventId, presentationId } = useParams<{
        eventId: string;
        presentationId: string;
    }>();
    const numericPresentationId = Number(presentationId);
    const { assessment } = useStore();
    const navigate = useNavigate();

    // Загрузка + сохранение при уходе
    useEffect(() => {
        if (Number.isFinite(numericPresentationId)) {
            void assessment.fetchPresentation(numericPresentationId);
        }

        return () => {
            // Fire-and-forget сохранение при уходе со страницы
            if (assessment.hasChanges && assessment.current) {
                void assessment.save(true);
            }
        };
    }, [numericPresentationId, assessment]);

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
    if (assessment.error && !assessment.current) {
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
    const allScored = presentation.event.criteria.every(
        (c) => typeof assessment.draftValues[c.key] === "number",
    );
    const scoredCount = presentation.event.criteria.filter(
        (c) => assessment.draftValues[c.key] !== undefined,
    ).length;

    return (
        <AppShell
            title="Оценка доклада"
            breadcrumb={
                <span className="inline-flex items-center gap-1.5">
                    <Link
                        to={`/events/${eventId}`}
                        className="text-muted-foreground transition-colors hover:text-foreground"
                        title={presentation.event.title}
                    >
                        {presentation.event.title}
                    </Link>
                    <span className="text-muted-foreground/50">/</span>
                    <span>Оценка</span>
                </span>
            }
            actions={
                <Button
                    variant="outline"
                    size="sm"
                    onClick={() => navigate(`/events/${eventId}`)}
                    className="hidden sm:inline-flex"
                >
                    <ArrowLeft className="size-4" />
                    Назад
                </Button>
            }
        >
            {/* ============ Карточка доклада ============ */}
            <section className="mb-6">
                <Card>
                    <CardHeader>
                        <div className="flex flex-wrap items-center gap-2">
                            <StatusBadge status={presentation.status} />
                            {presentation.authors.length > 0 && (
                                <Badge variant="neutral">
                                    <Tag className="size-3" />
                                    {presentation.authors.length} автор
                                    {presentation.authors.length > 1 ? "а" : ""}
                                </Badge>
                            )}
                        </div>
                        <CardTitle className="mt-2 text-balance text-xl leading-snug md:text-2xl">
                            {presentation.title}
                        </CardTitle>
                    </CardHeader>

                    <CardContent className="flex flex-col gap-5 pt-0">
                        {/* Аннотация */}
                        {presentation.abstract && (
                            <div>
                                <p className="mb-1.5 flex items-center gap-1.5 text-xs font-semibold tracking-wide text-muted-foreground uppercase">
                                    <FileText className="size-3.5" />
                                    Аннотация
                                </p>
                                <p className="text-sm leading-relaxed text-foreground/90 whitespace-pre-line">
                                    {presentation.abstract}
                                </p>
                            </div>
                        )}

                        {/* Авторы */}
                        {presentation.authors.length > 0 && (
                            <div>
                                <p className="mb-2 text-xs font-semibold uppercase tracking-wide text-muted-foreground">
                                    Авторы
                                </p>
                                <ul className="flex flex-wrap gap-3">
                                    {presentation.authors.map(
                                        (author, index) => (
                                            <li
                                                key={`${author.full_name}-${index}`}
                                                className="flex items-center gap-2.5 rounded-lg border border-border px-3 py-2"
                                            >
                                                <User className="size-4 text-muted-foreground" />
                                                <div className="leading-tight">
                                                    <p className="text-sm font-medium">
                                                        {author.full_name}
                                                        {author.is_presenter && (
                                                            <span className="ml-2 rounded bg-primary/10 px-1.5 py-0.5 text-xs text-primary">
                                                                Докладчик
                                                            </span>
                                                        )}
                                                    </p>
                                                    {author.university && (
                                                        <p className="text-xs text-muted-foreground">
                                                            {author.university}
                                                        </p>
                                                    )}
                                                </div>
                                            </li>
                                        ),
                                    )}
                                </ul>
                            </div>
                        )}

                        {/* Ссылки */}
                        {(presentation.file_path ||
                            presentation.video_link) && (
                            <div className="flex flex-wrap gap-3">
                                {presentation.file_path && (
                                    <a
                                        href={`/storage/${presentation.file_path}`}
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        className="inline-flex items-center gap-1.5 text-sm text-primary hover:underline"
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
                                        className="inline-flex items-center gap-1.5 text-sm text-primary hover:underline"
                                    >
                                        <ExternalLink className="size-4" />
                                        Посмотреть видео
                                    </a>
                                )}
                            </div>
                        )}
                    </CardContent>
                </Card>
            </section>

            {/* ============ 2-колоночная сетка ============ */}
            <div className="grid gap-6 lg:grid-cols-[1fr_20rem] lg:items-start">
                {/* ЛЕВАЯ КОЛОНКА: критерии + комментарий */}
                <div className="flex flex-col gap-6">
                    <Card>
                        <CardHeader>
                            <CardTitle className="text-lg">
                                Критерии оценки
                            </CardTitle>
                            <CardDescription>
                                Оцените каждый критерий. Итоговый балл
                                пересчитывается автоматически.
                            </CardDescription>
                        </CardHeader>
                        <CardContent className="flex flex-col gap-6 pt-0">
                            {presentation.event.criteria.map((criterion, i) => {
                                const currentValue =
                                    assessment.draftValues[criterion.key];

                                return (
                                    <div
                                        key={criterion.id}
                                        className={cn(
                                            "flex flex-col gap-3",
                                            i > 0 &&
                                                "border-t border-border pt-6",
                                        )}
                                    >
                                        <div className="flex items-start justify-between gap-4">
                                            <div className="min-w-0 flex-1">
                                                <p className="font-medium leading-snug">
                                                    {criterion.name}
                                                </p>
                                                {criterion.description && (
                                                    <p className="mt-0.5 text-sm text-muted-foreground">
                                                        {criterion.description}
                                                    </p>
                                                )}
                                            </div>
                                            <span className="shrink-0 rounded-md bg-muted px-2 py-1 text-xs font-medium tabular-nums text-muted-foreground">
                                                {currentValue ?? "–"}/
                                                {criterion.max_value}
                                            </span>
                                        </div>

                                        <ScoreSelector
                                            value={currentValue}
                                            max={criterion.max_value}
                                            onChange={(v) =>
                                                assessment.setValue(
                                                    criterion.key,
                                                    v,
                                                )
                                            }
                                            ariaLabel={`Оценка: ${criterion.name}`}
                                        />
                                    </div>
                                );
                            })}
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle className="text-lg">
                                Общий комментарий
                            </CardTitle>
                            <CardDescription>
                                Обратная связь для организаторов и авторов.
                            </CardDescription>
                        </CardHeader>
                        <CardContent className="pt-0">
                            <Label htmlFor="comment" className="sr-only">
                                Комментарий
                            </Label>
                            <Textarea
                                id="comment"
                                value={assessment.draftComment}
                                onChange={(e) =>
                                    assessment.setComment(e.target.value)
                                }
                                placeholder="Опишите сильные и слабые стороны доклада, вашу рекомендацию…"
                                className="min-h-32"
                                maxLength={2000}
                            />
                        </CardContent>
                    </Card>
                </div>

                {/* ПРАВАЯ КОЛОНКА: summary rail */}
                <Card className="gap-0 p-5 lg:sticky lg:top-20">
                    <p className="text-sm font-medium text-muted-foreground">
                        Итоговый балл
                    </p>
                    <p className="mt-1 text-4xl font-bold tracking-tight tabular-nums">
                        {assessment.draftTotal}
                        <span className="text-xl font-normal text-muted-foreground">
                            /{assessment.maxScore}
                        </span>
                    </p>

                    <RecommendationBadge
                        percent={assessment.draftPercent}
                        className="mt-4"
                    />

                    <ul className="mt-5 flex flex-col gap-2 border-t border-border pt-5 text-sm">
                        {presentation.event.criteria.map((c) => (
                            <li
                                key={c.id}
                                className="flex items-center justify-between gap-2"
                            >
                                <span className="truncate text-muted-foreground">
                                    {c.name}
                                </span>
                                <span className="shrink-0 tabular-nums font-medium">
                                    {assessment.draftValues[c.key] ?? "–"}
                                    <span className="font-normal text-muted-foreground">
                                        /{c.max_value}
                                    </span>
                                </span>
                            </li>
                        ))}
                    </ul>

                    <div className="mt-5 flex items-center gap-2 border-t border-border pt-4 text-sm text-muted-foreground">
                        <Check
                            className={cn(
                                "size-4",
                                allScored
                                    ? "text-emerald-500"
                                    : "text-muted-foreground/40",
                            )}
                        />
                        {scoredCount}/{presentation.event.criteria.length}{" "}
                        критериев
                    </div>

                    {/* Индикатор автосохранения */}
                    <div className="mt-4 min-h-5 border-t border-border pt-4">
                        <SaveIndicator
                            status={assessment.saveStatus}
                            savedAt={assessment.savedAt}
                            error={assessment.error}
                        />
                    </div>

                    <Button
                        className="mt-3 w-full"
                        size="lg"
                        onClick={() => void assessment.save(false)}
                        disabled={
                            assessment.isSaving ||
                            !allScored ||
                            !assessment.hasChanges
                        }
                    >
                        {assessment.isSaving ? (
                            <>
                                <Loader2 className="size-4 animate-spin" />
                                Сохранение…
                            </>
                        ) : (
                            <>
                                <Save className="size-4" />
                                {assessment.isAssessed
                                    ? "Обновить оценку"
                                    : "Сохранить оценку"}
                            </>
                        )}
                    </Button>

                    <Button
                        variant="ghost"
                        className="mt-2 w-full"
                        onClick={() => navigate(`/events/${eventId}`)}
                    >
                        <ArrowLeft className="size-4" />К мероприятию
                    </Button>
                </Card>
            </div>
        </AppShell>
    );
});

export default AssessmentPage;
