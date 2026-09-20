// resources/js/components/common/save-indicator.tsx
import { AlertCircle, CheckCircle2, Loader2 } from 'lucide-react';
import { observer } from 'mobx-react-lite';
import { cn } from '@/lib/utils';

interface SaveIndicatorProps {
    status: 'idle' | 'saving' | 'saved' | 'error';
    savedAt: string | null;
    error: string | null;
    className?: string;
}

function formatTime(iso: string | null): string {
    if (!iso) return '';
    try {
        return new Date(iso).toLocaleTimeString('ru-RU', {
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit',
        });
    } catch {
        return '';
    }
}

export const SaveIndicator = observer(function SaveIndicator({
    status,
    savedAt,
    error,
    className,
}: SaveIndicatorProps) {
    if (status === 'idle') {
        return null;
    }

    return (
        <div
            className={cn(
                'inline-flex items-center gap-1.5 text-sm',
                status === 'saving' && 'text-muted-foreground',
                status === 'saved' && 'text-emerald-600 dark:text-emerald-400',
                status === 'error' && 'text-destructive',
                className,
            )}
        >
            {status === 'saving' && (
                <>
                    <Loader2 className="size-3.5 animate-spin" />
                    Сохранение...
                </>
            )}

            {status === 'saved' && (
                <>
                    <CheckCircle2 className="size-3.5" />
                    Сохранено {formatTime(savedAt)}
                </>
            )}

            {status === 'error' && (
                <>
                    <AlertCircle className="size-3.5" />
                    {error ?? 'Ошибка сохранения'}
                </>
            )}
        </div>
    );
});