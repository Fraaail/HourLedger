import { cn } from '@/lib/utils';
import { animations, stagger } from '@/lib/animations';

type MetricCardProps = {
    label: string;
    value: string | number;
    sublabel?: string;
    index?: number;
};

export function MetricCard({ label, value, sublabel, index = 0 }: MetricCardProps) {
    return (
        <div
            className={cn(
                'tap-effect rounded-xl border border-border bg-card p-4 opacity-0',
                animations.fadeInUp,
                stagger(index),
            )}
        >
            <p className="text-xs font-medium uppercase tracking-wider text-muted-foreground">{label}</p>
            <p className="mt-1.5 text-2xl font-bold tracking-tight text-foreground">{value}</p>
            {sublabel && <p className="mt-0.5 text-xs text-muted-foreground">{sublabel}</p>}
        </div>
    );
}
