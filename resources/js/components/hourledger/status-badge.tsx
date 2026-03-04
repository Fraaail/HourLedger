import { cn } from '@/lib/utils';

type StatusBadgeProps = {
    status: 'complete' | 'incomplete' | 'missing';
    className?: string;
};

const statusConfig = {
    complete: {
        label: 'Complete',
        dotClass: 'bg-success',
        textClass: 'text-success',
    },
    incomplete: {
        label: 'In Progress',
        dotClass: 'bg-warning',
        textClass: 'text-warning',
    },
    missing: {
        label: 'Missing',
        dotClass: 'bg-destructive',
        textClass: 'text-destructive',
    },
};

export function StatusBadge({ status, className }: StatusBadgeProps) {
    const config = statusConfig[status];

    return (
        <span className={cn('inline-flex items-center gap-1.5 text-xs font-medium', config.textClass, className)}>
            <span className={cn('h-1.5 w-1.5 rounded-full', config.dotClass)} />
            {config.label}
        </span>
    );
}
