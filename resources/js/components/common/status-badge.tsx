// resources/js/components/common/status-badge.tsx
import { Badge } from '@/components/ui/badge';
import type { PresentationStatus } from '@/types';

const statusMeta: Record<
    PresentationStatus,
    { label: string; variant: 'default' | 'neutral' | 'success' | 'warning' | 'danger' | 'outline' }
> = {
    draft: { label: 'Черновик', variant: 'neutral' },
    submitted: { label: 'На рассмотрении', variant: 'warning' },
    approved: { label: 'Одобрен', variant: 'success' },
    rejected: { label: 'Отклонён', variant: 'danger' },
    presented: { label: 'Представлен', variant: 'default' },
};

interface StatusBadgeProps {
    status: PresentationStatus;
}

export function StatusBadge({ status }: StatusBadgeProps) {
    const meta = statusMeta[status];
    return <Badge variant={meta.variant}>{meta.label}</Badge>;
}