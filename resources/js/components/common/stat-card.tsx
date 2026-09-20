// resources/js/components/common/stat-card.tsx
import type { LucideIcon } from 'lucide-react';
import { Card } from '@/components/ui/card';
import { cn } from '@/lib/utils';

export type StatCardAccent = 'primary' | 'success' | 'warning' | 'deep';

export interface StatCardProps {
    label: string;
    value: string | number;
    hint?: string;
    icon: LucideIcon;
    accent?: StatCardAccent;
}

const accentClasses: Record<StatCardAccent, string> = {
    primary: 'bg-primary/10 text-primary',
    success:
        'bg-emerald-500/12 text-emerald-600 dark:text-emerald-400',
    warning: 'bg-amber-500/15 text-amber-600 dark:text-amber-400',
    deep: 'bg-brand-deep/10 text-brand-deep dark:bg-primary/10 dark:text-primary',
};

export function StatCard({
    label,
    value,
    hint,
    icon: Icon,
    accent = 'primary',
}: StatCardProps) {
    return (
        <Card className="gap-0 p-5">
            <div className="flex items-start justify-between gap-3">
                <div className="min-w-0">
                    <p className="text-sm font-medium text-muted-foreground">
                        {label}
                    </p>
                    <p className="mt-2 text-3xl font-bold tracking-tight tabular-nums">
                        {value}
                    </p>
                    {hint && (
                        <p className="mt-1 text-xs text-muted-foreground">
                            {hint}
                        </p>
                    )}
                </div>
                <span
                    className={cn(
                        'grid size-10 shrink-0 place-items-center rounded-lg',
                        accentClasses[accent],
                    )}
                >
                    <Icon className="size-5" />
                </span>
            </div>
        </Card>
    );
}