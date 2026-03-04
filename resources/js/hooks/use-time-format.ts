import { useMemo } from 'react';

export function useTimeFormat() {
    return useMemo(
        () => ({
            formatHours: (hours: number): string => {
                if (hours === 0) return '0h 0m';
                const h = Math.floor(hours);
                const m = Math.round((hours - h) * 60);
                if (h === 0) return `${m}m`;
                if (m === 0) return `${h}h`;
                return `${h}h ${m}m`;
            },

            formatTime: (isoString: string | null): string => {
                if (!isoString) return '--:--';
                const date = new Date(isoString);
                return date.toLocaleTimeString('en-US', {
                    hour: '2-digit',
                    minute: '2-digit',
                    hour12: true,
                });
            },

            formatDate: (dateString: string): string => {
                const date = new Date(dateString + 'T00:00:00');
                return date.toLocaleDateString('en-US', {
                    weekday: 'long',
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric',
                });
            },

            formatShortDate: (dateString: string): string => {
                const date = new Date(dateString + 'T00:00:00');
                return date.toLocaleDateString('en-US', {
                    month: 'short',
                    day: 'numeric',
                });
            },

            getCurrentGreeting: (): string => {
                const hour = new Date().getHours();
                if (hour < 12) return 'Good morning';
                if (hour < 17) return 'Good afternoon';
                return 'Good evening';
            },

            formatTodayDate: (): string => {
                return new Date().toLocaleDateString('en-US', {
                    weekday: 'long',
                    month: 'long',
                    day: 'numeric',
                    year: 'numeric',
                });
            },
        }),
        [],
    );
}
