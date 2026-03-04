import { usePage } from '@inertiajs/react';
import { Clock, Timer } from 'lucide-react';
import { ClockButton } from '@/components/hourledger/clock-button';
import { MetricCard } from '@/components/hourledger/metric-card';
import { StatusBadge } from '@/components/hourledger/status-badge';
import { useTimeFormat } from '@/hooks/use-time-format';
import MobileLayout from '@/layouts/mobile-layout';
import { cn } from '@/lib/utils';
import type { DashboardPageProps } from '@/types/hourledger';

export default function Dashboard() {
    const { today, metrics, unread_notification_count } =
        usePage<{ props: DashboardPageProps }>().props as unknown as DashboardPageProps;
    const { formatHours, getCurrentGreeting, formatTodayDate } = useTimeFormat();

    const todayStatus: 'none' | 'clocked_in' | 'clocked_out' = today
        ? today.time_out
            ? 'clocked_out'
            : 'clocked_in'
        : 'none';

    return (
        <MobileLayout title="Dashboard" unreadNotificationCount={unread_notification_count}>
            <div className="px-5 pt-6 pb-4">
                {/* Header */}
                <div className="animate-fade-in-up mb-6">
                    <p className="text-xs font-medium uppercase tracking-wider text-muted-foreground">
                        {formatTodayDate()}
                    </p>
                    <h1 className="mt-1 text-xl font-bold tracking-tight">{getCurrentGreeting()}</h1>
                </div>

                {/* Clock In/Out Section */}
                <div className="animate-fade-in-up stagger-2 mb-6 rounded-xl border border-border bg-card p-5">
                    <div className="flex flex-col items-center">
                        <ClockButton todayStatus={todayStatus} />

                        {/* Today's time info */}
                        {today && (
                            <div className="mt-5 w-full border-t border-border pt-4">
                                <div className="flex items-center justify-between">
                                    <StatusBadge status={today.status} />
                                    {today.rendered_hours > 0 && (
                                        <span className="text-xs font-medium text-muted-foreground">
                                            {formatHours(today.rendered_hours)}
                                        </span>
                                    )}
                                </div>
                                <div className="mt-3 grid grid-cols-2 gap-3">
                                    <div className="flex items-center gap-2 rounded-lg bg-muted/50 px-3 py-2.5">
                                        <Clock className="h-3.5 w-3.5 text-muted-foreground" />
                                        <div>
                                            <p className="text-[10px] font-medium uppercase tracking-wider text-muted-foreground">
                                                In
                                            </p>
                                            <p className="text-sm font-semibold">
                                                {today.formatted_time_in ?? '--:--'}
                                            </p>
                                        </div>
                                    </div>
                                    <div className="flex items-center gap-2 rounded-lg bg-muted/50 px-3 py-2.5">
                                        <Timer className="h-3.5 w-3.5 text-muted-foreground" />
                                        <div>
                                            <p className="text-[10px] font-medium uppercase tracking-wider text-muted-foreground">
                                                Out
                                            </p>
                                            <p className="text-sm font-semibold">
                                                {today.formatted_time_out ?? '--:--'}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        )}
                    </div>
                </div>

                {/* Metrics */}
                <div className="mb-2">
                    <h2
                        className={cn(
                            'animate-fade-in-up stagger-3 mb-3 text-xs font-semibold uppercase tracking-wider text-muted-foreground',
                        )}
                    >
                        Overview
                    </h2>
                    <div className="grid grid-cols-2 gap-3">
                        <MetricCard label="Total Hours" value={formatHours(metrics.total_rendered_hours)} index={0} />
                        <MetricCard
                            label="Total Days"
                            value={metrics.total_rendered_days}
                            sublabel="completed"
                            index={1}
                        />
                        <MetricCard
                            label="Daily Average"
                            value={formatHours(metrics.average_hours_per_day)}
                            sublabel="per day"
                            index={2}
                        />
                        <MetricCard
                            label="This Week"
                            value={formatHours(metrics.current_week_hours)}
                            sublabel="hours"
                            index={3}
                        />
                    </div>
                </div>
            </div>
        </MobileLayout>
    );
}
