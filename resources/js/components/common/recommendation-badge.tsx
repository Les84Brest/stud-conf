// resources/js/components/common/recommendation-badge.tsx
import { cn } from '@/lib/utils';

type RecommendationVariant = 'success' | 'primary' | 'warning' | 'danger';

interface Recommendation {
    label: string;
    variant: RecommendationVariant;
}

function getRecommendation(percent: number): Recommendation {
    if (percent >= 80) return { label: 'Отличная работа', variant: 'success' };
    if (percent >= 60) return { label: 'Принять', variant: 'primary' };
    if (percent >= 40) return { label: 'На грани', variant: 'warning' };
    return { label: 'Отклонить', variant: 'danger' };
}

const variantClasses: Record<RecommendationVariant, string> = {
    success: 'bg-emerald-500/12 text-emerald-700 dark:text-emerald-400',
    primary: 'bg-primary/10 text-primary',
    warning: 'bg-amber-500/15 text-amber-700 dark:text-amber-400',
    danger: 'bg-destructive/12 text-destructive',
};

interface RecommendationBadgeProps {
    percent: number;
    className?: string;
}

export function RecommendationBadge({
    percent,
    className,
}: RecommendationBadgeProps) {
    const rec = getRecommendation(percent);

    return (
        <div
            className={cn(
                'inline-flex w-fit items-center gap-1.5 rounded-full px-3 py-1 text-sm font-medium',
                variantClasses[rec.variant],
                className,
            )}
        >
            {rec.label}
            <span className="tabular-nums opacity-80">· {percent}%</span>
        </div>
    );
}