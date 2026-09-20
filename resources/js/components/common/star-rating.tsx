// resources/js/components/common/star-rating.tsx
import { Star } from 'lucide-react';
import { cn } from '@/lib/utils';

interface StarRatingProps {
    /** Текущее значение */
    value: number;
    /** Максимум (по умолчанию 5) */
    max?: number;
    /** Колбэк при изменении */
    onChange?: (value: number) => void;
    /** Только для чтения */
    readOnly?: boolean;
    /** Размер звёзд */
    size?: 'sm' | 'md' | 'lg';
    /** CSS-классы */
    className?: string;
}

const sizeClasses = {
    sm: 'size-5',
    md: 'size-7',
    lg: 'size-9',
};

export function StarRating({
    value,
    max = 5,
    onChange,
    readOnly = false,
    size = 'md',
    className,
}: StarRatingProps) {
    const stars = Array.from({ length: max }, (_, i) => i + 1);

    const handleClick = (starValue: number) => {
        if (readOnly || !onChange) return;
        // Клик по той же звезде — сбрасываем на 0
        onChange(starValue === value ? 0 : starValue);
    };

    return (
        <div
            role={readOnly ? 'img' : 'radiogroup'}
            aria-label={readOnly ? `Оценка: ${value} из ${max}` : 'Оценка'}
            className={cn('inline-flex items-center gap-1', className)}
        >
            {stars.map((starValue) => {
                const isActive = starValue <= value;

                return (
                    <button
                        key={starValue}
                        type="button"
                        role={readOnly ? undefined : 'radio'}
                        aria-checked={readOnly ? undefined : starValue === value}
                        aria-label={`${starValue} из ${max}`}
                        disabled={readOnly}
                        onClick={() => handleClick(starValue)}
                        className={cn(
                            'transition-all',
                            !readOnly &&
                                'cursor-pointer hover:scale-110 active:scale-95',
                            readOnly && 'cursor-default',
                        )}
                    >
                        <Star
                            className={cn(
                                sizeClasses[size],
                                'transition-colors',
                                isActive
                                    ? 'fill-amber-400 text-amber-400'
                                    : 'fill-transparent text-muted-foreground/40',
                                !readOnly && 'hover:text-amber-400',
                            )}
                        />
                    </button>
                );
            })}

            {!readOnly && (
                <span className="ml-2 text-sm tabular-nums text-muted-foreground">
                    {value}/{max}
                </span>
            )}
        </div>
    );
}