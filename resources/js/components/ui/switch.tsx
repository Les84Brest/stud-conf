// resources/js/components/ui/switch.tsx
import { cn } from '@/lib/utils';

type SwitchProps = {
    checked: boolean;
    onCheckedChange: (checked: boolean) => void;
    className?: string;
    id?: string;
} & Omit<React.ComponentProps<'button'>, 'onClick'>;

function Switch({
    checked,
    onCheckedChange,
    className,
    id,
    ...props
}: SwitchProps) {
    return (
        <button
            type="button"
            role="switch"
            id={id}
            aria-checked={checked}
            onClick={() => onCheckedChange(!checked)}
            className={cn(
                'relative inline-flex h-6 w-11 shrink-0 items-center rounded-full transition-colors outline-none focus-visible:ring-3 focus-visible:ring-ring/40',
                checked ? 'bg-primary' : 'bg-muted-foreground/30',
                className,
            )}
            {...props}
        >
            <span
                className={cn(
                    'inline-block size-5 transform rounded-full bg-white shadow transition-transform',
                    checked ? 'translate-x-5' : 'translate-x-0.5',
                )}
            />
        </button>
    );
}

export { Switch };
export type { SwitchProps };