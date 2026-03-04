import { cn } from '@/lib/utils';
import type { TimeEntryData } from '@/types/hourledger';

type DayCellProps = {
    day: number;
    date: string;
    entry?: TimeEntryData;
    isToday: boolean;
    isWeekend: boolean;
    isFuture: boolean;
    isCurrentMonth: boolean;
    onSelect: (date: string) => void;
};

export function DayCell({ day, date, entry, isToday, isWeekend, isFuture, isCurrentMonth, onSelect }: DayCellProps) {
    const getStatusClass = () => {
        if (!isCurrentMonth) return 'text-muted-foreground/30';
        if (isFuture) return 'text-muted-foreground/50';
        if (isWeekend) return 'text-muted-foreground/60 bg-muted/30';

        if (!entry) {
            // Missing weekday entry
            return 'text-foreground border-destructive/40 bg-destructive/5';
        }

        if (entry.status === 'complete') {
            return 'text-foreground border-success/40 bg-success/5';
        }

        if (entry.status === 'incomplete') {
            return 'text-foreground border-warning/40 bg-warning/5';
        }

        return 'text-muted-foreground';
    };

    const canSelect = isCurrentMonth && !isFuture;

    return (
        <button
            onClick={() => canSelect && onSelect(date)}
            disabled={!canSelect}
            className={cn(
                'tap-effect relative flex h-11 w-full flex-col items-center justify-center rounded-lg border border-transparent text-sm transition-all duration-200',
                getStatusClass(),
                isToday && 'ring-2 ring-foreground/20 ring-offset-1 ring-offset-background',
                canSelect && 'hover:bg-accent active:scale-95',
                !canSelect && 'cursor-default',
            )}
        >
            <span className={cn('text-xs font-medium', isToday && 'font-bold')}>{day}</span>
            {entry && isCurrentMonth && !isFuture && (
                <span
                    className={cn(
                        'mt-0.5 h-1 w-1 rounded-full',
                        entry.status === 'complete' && 'bg-success',
                        entry.status === 'incomplete' && 'bg-warning',
                    )}
                />
            )}
            {!entry && isCurrentMonth && !isFuture && !isWeekend && (
                <span className="mt-0.5 h-1 w-1 rounded-full bg-destructive/60" />
            )}
        </button>
    );
}
