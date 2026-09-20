
// resources/js/components/common/score-selector.tsx
import { cn } from '@/lib/utils';

interface ScoreSelectorProps {
    value: number | undefined;
    max: number;
    onChange: (value: number) => void;
    ariaLabel?: string;
    disabled?: boolean;
}

export function ScoreSelector({
    value,
    max,
    onChange,
    ariaLabel,
    disabled = false,
}: ScoreSelectorProps) {
    const options = Array.from({ length: max + 1 }, (_, i) => i);

    return (
        <div
            role="radiogroup"
            aria-label={ariaLabel}
            className="flex flex-wrap gap-1.5"
        >
            {options.map((n) => {
                const selected = value === n;
                return (
                    <button
                        key={n}
                        type="button"
                        role="radio"
                        aria-checked={selected}
                        disabled={disabled}
                        onClick={() => onChange(n)}
                        className={cn(
                            'grid size-9 place-items-center rounded-lg border text-sm font-semibold tabular-nums transition-colors',
                            selected
                                ? 'border-primary bg-primary text-primary-foreground shadow-sm'
                                : 'border-border bg-background text-foreground hover:border-primary/50 hover:bg-accent',
                            disabled &&
                                'cursor-not-allowed opacity-50 hover:border-border hover:bg-background',
                        )}
                    >
                        {n}
                    </button>
                );
            })}
        </div>
    );
}