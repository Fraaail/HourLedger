import { router, usePage } from '@inertiajs/react';
import { BellOff, CheckCheck } from 'lucide-react';
import { NotificationCard } from '@/components/hourledger/notification-card';
import { Button } from '@/components/ui/button';
import MobileLayout from '@/layouts/mobile-layout';
import type { NotificationsPageProps } from '@/types/hourledger';

export default function Notifications() {
    const { notifications, unread_notification_count } =
        usePage<{ props: NotificationsPageProps }>().props as unknown as NotificationsPageProps;

    const handleMarkAllRead = () => {
        router.post('/notifications/mark-all-read', {}, { preserveScroll: true });
    };

    const unreadCount = notifications.filter((n) => !n.is_read).length;

    return (
        <MobileLayout title="Notifications" unreadNotificationCount={unread_notification_count}>
            <div className="px-5 pt-6 pb-4">
                {/* Header */}
                <div className="animate-fade-in-up mb-5 flex items-start justify-between">
                    <div>
                        <h1 className="text-xl font-bold tracking-tight">Notifications</h1>
                        <p className="mt-0.5 text-xs text-muted-foreground">
                            {unreadCount > 0
                                ? `${unreadCount} unread alert${unreadCount !== 1 ? 's' : ''}`
                                : 'All caught up'}
                        </p>
                    </div>
                    {unreadCount > 0 && (
                        <Button
                            variant="ghost"
                            size="sm"
                            onClick={handleMarkAllRead}
                            className="tap-effect h-9 gap-1.5 text-xs"
                        >
                            <CheckCheck className="h-3.5 w-3.5" />
                            Mark all read
                        </Button>
                    )}
                </div>

                {/* Notification list */}
                {notifications.length === 0 ? (
                    <div className="animate-fade-in-up flex flex-col items-center justify-center py-20">
                        <div className="flex h-16 w-16 items-center justify-center rounded-2xl bg-muted">
                            <BellOff className="h-7 w-7 text-muted-foreground" />
                        </div>
                        <p className="mt-4 text-sm font-medium text-foreground">No notifications</p>
                        <p className="mt-1 text-xs text-muted-foreground">
                            Missing entries will appear here
                        </p>
                    </div>
                ) : (
                    <div className="space-y-2">
                        {notifications.map((notification, index) => (
                            <NotificationCard
                                key={notification.id}
                                notification={notification}
                                index={index}
                            />
                        ))}
                    </div>
                )}
            </div>
        </MobileLayout>
    );
}
