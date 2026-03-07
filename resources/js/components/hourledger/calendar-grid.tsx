import { useMemo } from 'react';
import { ChevronLeft, ChevronRight } from 'lucide-react';
import { DayCell } from '@/components/hourledger/day-cell';
import { cn } from '@/lib/utils';
import type { TimeEntryData } from '@/types/hourledger';

type CalendarGridProps = {
    year: number;
    month: number;
    entries: TimeEntryData[];
    onSelectDate: (date: string) => void;
    onPrevMonth: () => void;
    onNextMonth: () => void;
};

const WEEKDAY_LABELS = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];

const MONTH_NAMES = [
    'January',
    'February',
    'March',
    'April',
    'May',
    'June',
    'July',
    'August',
    'September',
    'October',
    'November',
    'December',
];

export function CalendarGrid({
    year,
    month,
    entries,
    onSelectDate,
    onPrevMonth,
    onNextMonth,
}: CalendarGridProps) {
    const today = new Date();
    const todayString = `${today.getFullYear()}-${String(today.getMonth() + 1).padStart(2, '0')}-${String(today.getDate()).padStart(2, '0')}`;

    const entryMap = useMemo(() => {
        const map: Record<string, TimeEntryData> = {};
        entries.forEach((entry) => {
            map[entry.date] = entry;
        });
        return map;
    }, [entries]);

    const calendarDays = useMemo(() => {
        const firstDay = new Date(year, month - 1, 1);
        const lastDay = new Date(year, month, 0);
        const daysInMonth = lastDay.getDate();

        // Monday = 0, Sunday = 6 (ISO week)
        let startDow = firstDay.getDay() - 1;
        if (startDow < 0) startDow = 6;

        const days: Array<{
            day: number;
            date: string;
            isCurrentMonth: boolean;
            isWeekend: boolean;
            isFuture: boolean;
            isToday: boolean;
        }> = [];

        // Previous month padding
        const prevMonthLastDay = new Date(year, month - 1, 0).getDate();
        for (let i = startDow - 1; i >= 0; i--) {
            const d = prevMonthLastDay - i;
            const m = month - 1 <= 0 ? 12 : month - 1;
            const y = month - 1 <= 0 ? year - 1 : year;
            const dateStr = `${y}-${String(m).padStart(2, '0')}-${String(d).padStart(2, '0')}`;
            const dayOfWeek = new Date(y, m - 1, d).getDay();
            days.push({
                day: d,
                date: dateStr,
                isCurrentMonth: false,
                isWeekend: dayOfWeek === 0 || dayOfWeek === 6,
                isFuture: dateStr > todayString,
                isToday: dateStr === todayString,
            });
        }

        // Current month days
        for (let d = 1; d <= daysInMonth; d++) {
            const dateStr = `${year}-${String(month).padStart(2, '0')}-${String(d).padStart(2, '0')}`;
            const dayOfWeek = new Date(year, month - 1, d).getDay();
            days.push({
                day: d,
                date: dateStr,
                isCurrentMonth: true,
                isWeekend: dayOfWeek === 0 || dayOfWeek === 6,
                isFuture: dateStr > todayString,
                isToday: dateStr === todayString,
            });
        }

        // Next month padding (fill to complete the last week row)
        const remaining = 7 - (days.length % 7);
        if (remaining < 7) {
            for (let d = 1; d <= remaining; d++) {
                const m = month + 1 > 12 ? 1 : month + 1;
                const y = month + 1 > 12 ? year + 1 : year;
                const dateStr = `${y}-${String(m).padStart(2, '0')}-${String(d).padStart(2, '0')}`;
                const dayOfWeek = new Date(y, m - 1, d).getDay();
                days.push({
                    day: d,
                    date: dateStr,
                    isCurrentMonth: false,
                    isWeekend: dayOfWeek === 0 || dayOfWeek === 6,
                    isFuture: dateStr > todayString,
                    isToday: dateStr === todayString,
                });
            }
        }

        return days;
    }, [year, month, todayString]);

    const isCurrentMonth =
        today.getFullYear() === year && today.getMonth() + 1 === month;
    const canGoNext = !isCurrentMonth;

    return (
        <div className="animate-fade-in-up">
            {/* Month navigation header */}
            <div className="mb-4 flex items-center justify-between px-1">
                <button
                    onClick={onPrevMonth}
                    className="tap-effect flex h-10 w-10 items-center justify-center rounded-lg transition-colors hover:bg-accent active:scale-95"
                >
                    <ChevronLeft className="h-5 w-5" />
                </button>
                <h2 className="text-base font-semibold tracking-tight">
                    {MONTH_NAMES[month - 1]} {year}
                </h2>
                <button
                    onClick={onNextMonth}
                    disabled={!canGoNext}
                    className={cn(
                        'tap-effect flex h-10 w-10 items-center justify-center rounded-lg transition-colors active:scale-95',
                        canGoNext
                            ? 'hover:bg-accent'
                            : 'cursor-not-allowed opacity-30',
                    )}
                >
                    <ChevronRight className="h-5 w-5" />
                </button>
            </div>

            {/* Weekday labels */}
            <div className="mb-1 grid grid-cols-7 gap-1">
                {WEEKDAY_LABELS.map((label) => (
                    <div
                        key={label}
                        className="flex h-8 items-center justify-center text-[10px] font-semibold tracking-widest text-muted-foreground uppercase"
                    >
                        {label}
                    </div>
                ))}
            </div>

            {/* Day grid */}
            <div className="grid grid-cols-7 gap-1">
                {calendarDays.map((dayInfo, i) => (
                    <DayCell
                        key={`${dayInfo.date}-${i}`}
                        day={dayInfo.day}
                        date={dayInfo.date}
                        entry={entryMap[dayInfo.date]}
                        isToday={dayInfo.isToday}
                        isWeekend={dayInfo.isWeekend}
                        isFuture={dayInfo.isFuture}
                        isCurrentMonth={dayInfo.isCurrentMonth}
                        onSelect={onSelectDate}
                    />
                ))}
            </div>

            {/* Legend */}
            <div className="mt-4 flex items-center justify-center gap-4 text-[10px] text-muted-foreground">
                <span className="flex items-center gap-1">
                    <span className="h-2 w-2 rounded-full bg-success" />
                    Complete
                </span>
                <span className="flex items-center gap-1">
                    <span className="h-2 w-2 rounded-full bg-warning" />
                    Incomplete
                </span>
                <span className="flex items-center gap-1">
                    <span className="h-2 w-2 rounded-full bg-destructive/60" />
                    Missing
                </span>
            </div>
        </div>
    );
}
