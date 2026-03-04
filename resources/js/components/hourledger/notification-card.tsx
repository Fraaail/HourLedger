import { router } from '@inertiajs/react';
import { AlertTriangle, Check } from 'lucide-react';
import { cn } from '@/lib/utils';
import { tapEffect } from '@/lib/animations';
import type { NotificationData } from '@/types/hourledger';

type NotificationCardProps = {
    notification: NotificationData;
    index: number;
};

export function NotificationCard({ notification, index }: NotificationCardProps) {
    const handleTap = () => {
        // Mark as read
        if (!notification.is_read) {
            router.post(`/notifications/${notification.id}/mark-read`, {}, { preserveScroll: true });
        }

        // Navigate to calendar for that date
        const date = new Date(notification.date + 'T00:00:00');
        router.visit(`/calendar?year=${date.getFullYear()}&month=${date.getMonth() + 1}`);
    };

    const handleMarkRead = (e: React.MouseEvent) => {
        e.stopPropagation();
        if (!notification.is_read) {
            router.post(`/notifications/${notification.id}/mark-read`, {}, { preserveScroll: true });
        }
    };

    return (
        <button
            onClick={handleTap}
            className={cn(
                tapEffect,
                'animate-fade-in-up w-full rounded-xl border border-border p-4 text-left transition-all duration-300',
                notification.is_read
                    ? 'opacity-50 bg-card'
                    : 'bg-card shadow-sm',
            )}
            style={{ animationDelay: `${index * 60}ms` }}
        >
            <div className="flex items-start gap-3">
                {/* Icon */}
                <div
                    className={cn(
                        'mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-lg',
                        notification.is_read
                            ? 'bg-muted text-muted-foreground'
                            : 'bg-destructive/10 text-destructive',
                    )}
                >
                    <AlertTriangle className="h-4 w-4" />
                </div>

                {/* Content */}
                <div className="min-w-0 flex-1">
                    <div className="flex items-start justify-between gap-2">
                        <p
                            className={cn(
                                'text-sm',
                                notification.is_read ? 'font-medium' : 'font-semibold',
                            )}
                        >
                            {notification.title}
                        </p>
                        {!notification.is_read && (
                            <button
                                onClick={handleMarkRead}
                                className="tap-effect flex h-6 w-6 shrink-0 items-center justify-center rounded-md hover:bg-accent"
                                title="Mark as read"
                            >
                                <Check className="h-3.5 w-3.5 text-muted-foreground" />
                            </button>
                        )}
                    </div>
                    <p className="mt-0.5 text-xs text-muted-foreground line-clamp-2">{notification.message}</p>
                    <p className="mt-1.5 text-[10px] font-medium uppercase tracking-wider text-muted-foreground/70">
                        {notification.formatted_date}
                    </p>
                </div>
            </div>
        </button>
    );
}
