// resources/js/components/ui/badge.tsx
import { cva, type VariantProps } from 'class-variance-authority';
import { cn } from '@/lib/utils';

const badgeVariants = cva(
    'inline-flex items-center gap-1 rounded-full border px-2.5 py-0.5 text-xs font-medium whitespace-nowrap transition-colors [&_svg]:size-3',
    {
        variants: {
            variant: {
                default: 'border-transparent bg-primary/10 text-primary',
                neutral: 'border-transparent bg-muted text-muted-foreground',
                success:
                    'border-transparent bg-emerald-500/12 text-emerald-700 dark:text-emerald-400',
                warning:
                    'border-transparent bg-amber-500/15 text-amber-700 dark:text-amber-400',
                danger: 'border-transparent bg-destructive/12 text-destructive',
                outline: 'border-border text-foreground',
            },
        },
        defaultVariants: {
            variant: 'default',
        },
    },
);

type BadgeProps = React.ComponentProps<'span'> & VariantProps<typeof badgeVariants>;

function Badge({ className, variant, ...props }: BadgeProps) {
    return (
        <span
            data-slot="badge"
            className={cn(badgeVariants({ variant }), className)}
            {...props}
        />
    );
}

export { Badge, badgeVariants };
export type { BadgeProps };