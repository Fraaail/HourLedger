import { Head } from '@inertiajs/react';
import type { ReactNode } from 'react';

type MobileLayoutProps = {
    children: ReactNode;
    title: string;
    /**
     * @deprecated Notification badge is now handled by native EDGE BottomNav.
     * Kept for backward compatibility but no longer used in the layout.
     */
    unreadNotificationCount?: number;
};

/**
 * Mobile layout shell optimized for NativePHP EDGE.
 *
 * The bottom navigation and top bar are rendered as **native** Android/iOS
 * components via the SetEdgeComponents middleware, so this layout only
 * provides the scrollable content area with appropriate padding to avoid
 * overlap with the native chrome.
 */
export default function MobileLayout({ children, title }: MobileLayoutProps) {
    return (
        <>
            <Head title={`${title} - HourLedger`} />
            <div className="flex min-h-svh flex-col bg-background">
                {/* Main content area - padded for native EDGE TopBar and BottomNav */}
                <main className="native-content-area flex-1 overflow-y-auto">
                    <div className="animate-fade-in">{children}</div>
                </main>
            </div>
        </>
    );
}
