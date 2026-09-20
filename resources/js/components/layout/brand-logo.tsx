// resources/js/components/layout/brand-logo.tsx
import { cn } from '@/lib/utils';

interface BrandLogoProps {
    className?: string;
    showWordmark?: boolean;
    tone?: 'default' | 'onDark';
}

export function BrandLogo({
    className,
    showWordmark = true,
    tone = 'default',
}: BrandLogoProps) {
    return (
        <span className={cn('inline-flex items-center gap-2.5', className)}>
            <span className="grid size-9 place-items-center rounded-lg bg-primary text-primary-foreground shadow-sm">
                <svg
                    viewBox="0 0 24 24"
                    className="size-5"
                    fill="none"
                    aria-hidden="true"
                >
                    <path
                        d="M5 4h9l5 5v11a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V5a1 1 0 0 1 1-1Z"
                        stroke="currentColor"
                        strokeWidth="1.7"
                        strokeLinejoin="round"
                    />
                    <path
                        d="M8 12.5l2.4 2.4L16 9.5"
                        stroke="currentColor"
                        strokeWidth="1.7"
                        strokeLinecap="round"
                        strokeLinejoin="round"
                    />
                </svg>
            </span>
            {showWordmark && (
                <span
                    className={cn(
                        'text-lg font-bold tracking-tight',
                        tone === 'onDark' ? 'text-white' : 'text-foreground',
                    )}
                >
                    Reviewa
                </span>
            )}
        </span>
    );
}