// resources/js/components/layout/brand-logo.tsx
import { cn } from '@/lib/utils';

interface BrandLogoProps {
    className?: string;
    showWordmark?: boolean;
    tone?: 'default' | 'onDark';
}

const LOGO_PATH = '/images/logo.png';
const INVERSE_LOGO_PATH = '/images/logo-inverted.png';

export function BrandLogo({
    className,
    showWordmark = true,
    tone = 'default',
}: BrandLogoProps) {
    return (
        <span className={cn('inline-flex items-center gap-2.5', className)}>
            <span className="grid size-9 place-items-center rounded-lg  text-primary-foreground shadow-sm">
                <img src={tone === 'onDark' ? INVERSE_LOGO_PATH : LOGO_PATH} alt="Лого БрГТУ" />
            </span>
            {showWordmark && (
                <span
                    className={cn(
                        'text-lg font-bold tracking-tight',
                        tone === 'onDark' ? 'text-white' : 'text-foreground',
                    )}
                >
                    БрГТУ конференции
                </span>
            )}
        </span>
    );
}