import { useForm } from '@inertiajs/react';
import { LogIn, LogOut } from 'lucide-react';
import { cn } from '@/lib/utils';

type ClockButtonProps = {
    todayStatus: 'none' | 'clocked_in' | 'clocked_out';
};

export function ClockButton({ todayStatus }: ClockButtonProps) {
    const clockInForm = useForm({});
    const clockOutForm = useForm({});

    const handleClockIn = () => {
        clockInForm.post('/time-entries/clock-in', { preserveScroll: true });
    };

    const handleClockOut = () => {
        clockOutForm.post('/time-entries/clock-out', { preserveScroll: true });
    };

    if (todayStatus === 'clocked_out') {
        return (
            <div className="animate-fade-in-up flex flex-col items-center gap-3">
                <div className="flex h-20 w-20 items-center justify-center rounded-full border-2 border-success/30 bg-success/10">
                    <LogOut className="h-8 w-8 text-success" />
                </div>
                <div className="text-center">
                    <p className="text-sm font-semibold text-success">
                        Clocked Out
                    </p>
                    <p className="text-xs text-muted-foreground">
                        You're done for today
                    </p>
                </div>
            </div>
        );
    }

    if (todayStatus === 'clocked_in') {
        return (
            <div className="animate-fade-in-up flex flex-col items-center gap-3">
                <button
                    onClick={handleClockOut}
                    disabled={clockOutForm.processing}
                    className={cn(
                        'tap-effect flex h-20 w-20 items-center justify-center rounded-full border-2 border-destructive/30 bg-destructive/10 transition-all duration-300',
                        'hover:border-destructive/50 hover:bg-destructive/20',
                        'active:scale-95',
                        clockOutForm.processing &&
                            'animate-pulse-soft opacity-70',
                    )}
                >
                    <LogOut className="h-8 w-8 text-destructive" />
                </button>
                <div className="text-center">
                    <p className="text-sm font-semibold text-foreground">
                        Clock Out
                    </p>
                    <p className="text-xs text-muted-foreground">
                        Tap to end your shift
                    </p>
                </div>
            </div>
        );
    }

    return (
        <div className="animate-fade-in-up flex flex-col items-center gap-3">
            <button
                onClick={handleClockIn}
                disabled={clockInForm.processing}
                className={cn(
                    'tap-effect flex h-20 w-20 items-center justify-center rounded-full border-2 border-foreground/20 bg-foreground/5 transition-all duration-300',
                    'hover:border-foreground/40 hover:bg-foreground/10',
                    'active:scale-95',
                    clockInForm.processing && 'animate-pulse-soft opacity-70',
                )}
            >
                <LogIn className="h-8 w-8 text-foreground" />
            </button>
            <div className="text-center">
                <p className="text-sm font-semibold text-foreground">
                    Clock In
                </p>
                <p className="text-xs text-muted-foreground">
                    Tap to start your shift
                </p>
            </div>
        </div>
    );
}
