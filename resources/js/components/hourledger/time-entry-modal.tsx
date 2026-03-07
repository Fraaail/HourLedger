import { useForm } from '@inertiajs/react';
import { Trash2, X } from 'lucide-react';
import { useEffect, useRef, useState } from 'react';
import { StatusBadge } from '@/components/hourledger/status-badge';
import { Button } from '@/components/ui/button';
import { cn } from '@/lib/utils';
import { useTimeFormat } from '@/hooks/use-time-format';
import type { TimeEntryData } from '@/types/hourledger';

type TimeEntryModalProps = {
    isOpen: boolean;
    onClose: () => void;
    date: string;
    entry?: TimeEntryData | null;
};

export function TimeEntryModal({
    isOpen,
    onClose,
    date,
    entry,
}: TimeEntryModalProps) {
    const { formatDate } = useTimeFormat();
    const backdropRef = useRef<HTMLDivElement>(null);
    const [isAnimating, setIsAnimating] = useState(false);

    // Form for creating/updating
    const form = useForm({
        date: date,
        time_in: entry?.time_in
            ? new Date(entry.time_in).toTimeString().slice(0, 5)
            : '',
        time_out: entry?.time_out
            ? new Date(entry.time_out).toTimeString().slice(0, 5)
            : '',
        notes: entry?.notes ?? '',
    });

    // Form for deleting
    const deleteForm = useForm({});

    useEffect(() => {
        if (isOpen) {
            setIsAnimating(true);
            // Reset form when opening with new data
            form.setData({
                date: date,
                time_in: entry?.time_in
                    ? new Date(entry.time_in).toTimeString().slice(0, 5)
                    : '',
                time_out: entry?.time_out
                    ? new Date(entry.time_out).toTimeString().slice(0, 5)
                    : '',
                notes: entry?.notes ?? '',
            });
        }
    }, [isOpen, date, entry]);

    const handleClose = () => {
        setIsAnimating(false);
        setTimeout(onClose, 300);
    };

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();

        const dateStr = date;
        const timeInDate = form.data.time_in
            ? `${dateStr}T${form.data.time_in}:00`
            : '';
        const timeOutDate = form.data.time_out
            ? `${dateStr}T${form.data.time_out}:00`
            : '';

        if (entry?.id) {
            form.transform(() => ({
                time_in: timeInDate,
                time_out: timeOutDate || null,
                notes: form.data.notes || null,
            }));
            form.put(`/time-entries/${entry.id}`, {
                preserveScroll: true,
                onSuccess: () => handleClose(),
            });
        } else {
            form.transform(() => ({
                date: dateStr,
                time_in: timeInDate,
                time_out: timeOutDate || null,
                notes: form.data.notes || null,
            }));
            form.post('/time-entries', {
                preserveScroll: true,
                onSuccess: () => handleClose(),
            });
        }
    };

    const handleDelete = () => {
        if (entry?.id) {
            deleteForm.delete(`/time-entries/${entry.id}`, {
                preserveScroll: true,
                onSuccess: () => handleClose(),
            });
        }
    };

    if (!isOpen && !isAnimating) return null;

    return (
        <div
            ref={backdropRef}
            className={cn(
                'fixed inset-0 z-50 flex items-end justify-center transition-colors duration-300',
                isAnimating ? 'bg-black/40' : 'bg-transparent',
            )}
            onClick={(e) => {
                if (e.target === backdropRef.current) handleClose();
            }}
        >
            <div
                className={cn(
                    'w-full max-w-lg rounded-t-2xl border-t border-border bg-card px-5 pt-4 pb-8 transition-transform duration-300 ease-out',
                    isAnimating ? 'translate-y-0' : 'translate-y-full',
                )}
            >
                {/* Handle */}
                <div className="mb-4 flex justify-center">
                    <div className="h-1 w-10 rounded-full bg-muted-foreground/30" />
                </div>

                {/* Header */}
                <div className="mb-5 flex items-start justify-between">
                    <div>
                        <h3 className="text-base font-semibold">
                            {formatDate(date)}
                        </h3>
                        {entry && (
                            <StatusBadge
                                status={entry.status}
                                className="mt-1"
                            />
                        )}
                    </div>
                    <button
                        onClick={handleClose}
                        className="tap-effect flex h-8 w-8 items-center justify-center rounded-lg hover:bg-accent"
                    >
                        <X className="h-4 w-4" />
                    </button>
                </div>

                {/* Form */}
                <form onSubmit={handleSubmit} className="space-y-4">
                    <div className="grid grid-cols-2 gap-3">
                        <div>
                            <label className="mb-1.5 block text-xs font-medium text-muted-foreground">
                                Time In
                            </label>
                            <input
                                type="time"
                                value={form.data.time_in}
                                onChange={(e) =>
                                    form.setData('time_in', e.target.value)
                                }
                                className="h-12 w-full rounded-lg border border-input bg-background px-3 text-sm transition-colors focus:border-foreground focus:ring-1 focus:ring-foreground focus:outline-none"
                                required
                            />
                            {form.errors.time_in && (
                                <p className="mt-1 text-xs text-destructive">
                                    {form.errors.time_in}
                                </p>
                            )}
                        </div>
                        <div>
                            <label className="mb-1.5 block text-xs font-medium text-muted-foreground">
                                Time Out
                            </label>
                            <input
                                type="time"
                                value={form.data.time_out}
                                onChange={(e) =>
                                    form.setData('time_out', e.target.value)
                                }
                                className="h-12 w-full rounded-lg border border-input bg-background px-3 text-sm transition-colors focus:border-foreground focus:ring-1 focus:ring-foreground focus:outline-none"
                            />
                            {form.errors.time_out && (
                                <p className="mt-1 text-xs text-destructive">
                                    {form.errors.time_out}
                                </p>
                            )}
                        </div>
                    </div>

                    <div>
                        <label className="mb-1.5 block text-xs font-medium text-muted-foreground">
                            Notes (optional)
                        </label>
                        <textarea
                            value={form.data.notes}
                            onChange={(e) =>
                                form.setData('notes', e.target.value)
                            }
                            placeholder="What did you work on?"
                            rows={3}
                            maxLength={500}
                            className="w-full resize-none rounded-lg border border-input bg-background px-3 py-2.5 text-sm transition-colors placeholder:text-muted-foreground/50 focus:border-foreground focus:ring-1 focus:ring-foreground focus:outline-none"
                        />
                    </div>

                    <div className="flex items-center gap-2 pt-1">
                        <Button
                            type="submit"
                            disabled={form.processing || !form.data.time_in}
                            className="tap-effect h-12 flex-1 rounded-lg text-sm font-semibold transition-transform active:scale-[0.98]"
                        >
                            {form.processing
                                ? 'Saving...'
                                : entry?.id
                                  ? 'Update Entry'
                                  : 'Save Entry'}
                        </Button>

                        {entry?.id && (
                            <Button
                                type="button"
                                variant="outline"
                                onClick={handleDelete}
                                disabled={deleteForm.processing}
                                className="tap-effect h-12 w-12 rounded-lg border-destructive/30 text-destructive transition-transform hover:bg-destructive/10 active:scale-95"
                            >
                                <Trash2 className="h-4 w-4" />
                            </Button>
                        )}
                    </div>
                </form>
            </div>
        </div>
    );
}
