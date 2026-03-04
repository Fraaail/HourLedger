import { Head, Link, usePage } from '@inertiajs/react';
import { Bell, CalendarDays, LayoutGrid } from 'lucide-react';
import type { ReactNode } from 'react';
import { cn } from '@/lib/utils';

type MobileLayoutProps = {
    children: ReactNode;
    title: string;
    unreadNotificationCount?: number;
};

const navItems = [
    { label: 'Dashboard', href: '/', icon: LayoutGrid },
    { label: 'Calendar', href: '/calendar', icon: CalendarDays },
    { label: 'Alerts', href: '/notifications', icon: Bell },
];

export default function MobileLayout({ children, title, unreadNotificationCount = 0 }: MobileLayoutProps) {
    const { url } = usePage();

    const isActive = (href: string) => {
        if (href === '/') return url === '/';
        return url.startsWith(href);
    };

    return (
        <>
            <Head title={`${title} - HourLedger`} />
            <div className="flex min-h-svh flex-col bg-background">
                {/* Main content area */}
                <main className="flex-1 overflow-y-auto pb-20">
                    <div className="animate-fade-in">{children}</div>
                </main>

                {/* Bottom navigation */}
                <nav className="safe-bottom fixed inset-x-0 bottom-0 z-50 border-t border-border bg-background/95 backdrop-blur-sm">
                    <div className="flex items-center justify-around px-2 pt-2 pb-1">
                        {navItems.map((item) => {
                            const active = isActive(item.href);
                            const Icon = item.icon;
                            const showBadge = item.label === 'Alerts' && unreadNotificationCount > 0;

                            return (
                                <Link
                                    key={item.href}
                                    href={item.href}
                                    className={cn(
                                        'tap-effect relative flex min-h-[44px] min-w-[64px] flex-col items-center justify-center gap-0.5 rounded-lg px-3 py-1.5 transition-all duration-200',
                                        active ? 'text-foreground' : 'text-muted-foreground',
                                    )}
                                >
                                    <div className="relative">
                                        <Icon
                                            className={cn(
                                                'h-5 w-5 transition-transform duration-200',
                                                active && 'scale-110',
                                            )}
                                            strokeWidth={active ? 2.5 : 2}
                                        />
                                        {showBadge && (
                                            <span className="absolute -top-1.5 -right-2 flex h-4 min-w-4 items-center justify-center rounded-full bg-destructive px-1 text-[10px] font-bold text-white">
                                                {unreadNotificationCount > 99 ? '99+' : unreadNotificationCount}
                                            </span>
                                        )}
                                    </div>
                                    <span
                                        className={cn(
                                            'text-[10px] leading-tight transition-all duration-200',
                                            active ? 'font-semibold' : 'font-medium',
                                        )}
                                    >
                                        {item.label}
                                    </span>
                                    {active && (
                                        <div className="absolute -bottom-1.5 h-0.5 w-6 rounded-full bg-foreground animate-scale-in" />
                                    )}
                                </Link>
                            );
                        })}
                    </div>
                </nav>
            </div>
        </>
    );
}
