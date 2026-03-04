import { router, usePage } from '@inertiajs/react';
import { useCallback, useState } from 'react';
import { CalendarGrid } from '@/components/hourledger/calendar-grid';
import { TimeEntryModal } from '@/components/hourledger/time-entry-modal';
import MobileLayout from '@/layouts/mobile-layout';
import type { CalendarPageProps, TimeEntryData } from '@/types/hourledger';

export default function Calendar() {
    const { entries, year, month, unread_notification_count } =
        usePage<{ props: CalendarPageProps }>().props as unknown as CalendarPageProps;

    const [selectedDate, setSelectedDate] = useState<string | null>(null);
    const [isModalOpen, setIsModalOpen] = useState(false);

    const selectedEntry: TimeEntryData | null =
        selectedDate ? entries.find((e) => e.date === selectedDate) ?? null : null;

    const handleSelectDate = useCallback((date: string) => {
        setSelectedDate(date);
        setIsModalOpen(true);
    }, []);

    const handleCloseModal = useCallback(() => {
        setIsModalOpen(false);
        setSelectedDate(null);
    }, []);

    const handlePrevMonth = useCallback(() => {
        const prevMonth = month === 1 ? 12 : month - 1;
        const prevYear = month === 1 ? year - 1 : year;
        router.visit(`/calendar?year=${prevYear}&month=${prevMonth}`, {
            preserveState: true,
        });
    }, [year, month]);

    const handleNextMonth = useCallback(() => {
        const nextMonth = month === 12 ? 1 : month + 1;
        const nextYear = month === 12 ? year + 1 : year;
        router.visit(`/calendar?year=${nextYear}&month=${nextMonth}`, {
            preserveState: true,
        });
    }, [year, month]);

    return (
        <MobileLayout title="Calendar" unreadNotificationCount={unread_notification_count}>
            <div className="px-5 pt-6 pb-4">
                {/* Header */}
                <div className="animate-fade-in-up mb-5">
                    <h1 className="text-xl font-bold tracking-tight">Calendar</h1>
                    <p className="mt-0.5 text-xs text-muted-foreground">
                        Tap a day to view or add entries
                    </p>
                </div>

                {/* Calendar Grid */}
                <CalendarGrid
                    year={year}
                    month={month}
                    entries={entries}
                    onSelectDate={handleSelectDate}
                    onPrevMonth={handlePrevMonth}
                    onNextMonth={handleNextMonth}
                />
            </div>

            {/* Time Entry Modal */}
            {selectedDate && (
                <TimeEntryModal
                    isOpen={isModalOpen}
                    onClose={handleCloseModal}
                    date={selectedDate}
                    entry={selectedEntry}
                />
            )}
        </MobileLayout>
    );
}
