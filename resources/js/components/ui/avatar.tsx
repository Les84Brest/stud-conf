// resources/js/components/ui/avatar.tsx
import { cn } from '@/lib/utils';

function Avatar({
    name,
    className,
    ...props
}: React.ComponentProps<'div'> & { name: string }) {
    const initials = name
        .split(' ')
        .map((part) => part[0])
        .filter(Boolean)
        .slice(0, 2)
        .join('')
        .toUpperCase();

    return (
        <div
            data-slot="avatar"
            aria-hidden="true"
            className={cn(
                'flex size-9 shrink-0 items-center justify-center rounded-full bg-brand-deep text-xs font-semibold text-white',
                className,
            )}
            {...props}
        >
            {initials}
        </div>
    );
}

export { Avatar };