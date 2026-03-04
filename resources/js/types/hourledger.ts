export type TimeEntryData = {
    id: number;
    date: string;
    time_in: string | null;
    time_out: string | null;
    formatted_time_in: string | null;
    formatted_time_out: string | null;
    rendered_hours: number;
    status: 'complete' | 'incomplete' | 'missing';
    notes: string | null;
};

export type DashboardMetrics = {
    total_rendered_hours: number;
    total_rendered_days: number;
    average_hours_per_day: number;
    current_week_hours: number;
};

export type DashboardPageProps = {
    today: TimeEntryData | null;
    metrics: DashboardMetrics;
    unread_notification_count: number;
};

export type CalendarPageProps = {
    entries: TimeEntryData[];
    year: number;
    month: number;
    unread_notification_count: number;
};

export type NotificationData = {
    id: number;
    type: string;
    title: string;
    message: string;
    date: string;
    formatted_date: string;
    is_read: boolean;
    created_at: string;
};

export type NotificationsPageProps = {
    notifications: NotificationData[];
    unread_notification_count: number;
};
