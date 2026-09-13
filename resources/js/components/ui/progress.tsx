// resources/js/components/ui/progress.tsx
import { cn } from '@/lib/utils';

type ProgressProps = React.ComponentProps<'div'> & {
    value?: number;
    indicatorClassName?: string;
};

function Progress({
    value = 0,
    className,
    indicatorClassName,
    ...props
}: ProgressProps) {
    const clamped = Math.max(0, Math.min(100, value));

    return (
        <div
            role="progressbar"
            aria-valuenow={clamped}
            aria-valuemin={0}
            aria-valuemax={100}
            data-slot="progress"
            className={cn(
                'relative h-2 w-full overflow-hidden rounded-full bg-muted',
                className,
            )}
            {...props}
        >
            <div
                className={cn(
                    'h-full rounded-full bg-primary transition-all',
                    indicatorClassName,
                )}
                style={{ width: `${clamped}%` }}
            />
        </div>
    );
}

export { Progress };
export type { ProgressProps };