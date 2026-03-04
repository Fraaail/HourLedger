<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EntryNotification extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'title',
        'message',
        'date',
        'is_read',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'is_read' => 'boolean',
        ];
    }

    /**
     * Scope: unread notifications.
     */
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    /**
     * Scope: by type.
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Mark this notification as read.
     */
    public function markAsRead(): void
    {
        $this->update(['is_read' => true]);
    }

    /**
     * Generate missing entry notifications for weekdays without time entries.
     */
    public static function generateMissingEntryNotifications(): void
    {
        $firstEntry = TimeEntry::orderBy('date', 'asc')->first();

        if (! $firstEntry) {
            return;
        }

        $startDate = $firstEntry->date->copy();
        $endDate = now()->subDay();

        if ($startDate->greaterThan($endDate)) {
            return;
        }

        $existingEntryDates = TimeEntry::whereBetween('date', [
            $startDate->toDateString(),
            $endDate->toDateString(),
        ])->pluck('date')->map(fn ($d) => $d->toDateString())->toArray();

        $existingNotificationDates = self::ofType('missing_entry')
            ->pluck('date')
            ->map(fn ($d) => $d->toDateString())
            ->toArray();

        $currentDate = $startDate->copy();
        $notifications = [];

        while ($currentDate->lte($endDate)) {
            // Only check weekdays (Monday=1 to Friday=5)
            if ($currentDate->isWeekday()) {
                $dateString = $currentDate->toDateString();

                if (
                    ! in_array($dateString, $existingEntryDates) &&
                    ! in_array($dateString, $existingNotificationDates)
                ) {
                    $notifications[] = [
                        'type' => 'missing_entry',
                        'title' => 'Missing Time Entry',
                        'message' => 'No time entry recorded for '.$currentDate->format('l, F j, Y').'. Please add your hours.',
                        'date' => $dateString,
                        'is_read' => false,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }

            $currentDate->addDay();
        }

        if (! empty($notifications)) {
            self::insert($notifications);
        }
    }

    /**
     * Resolve notification for a specific date when entry is created.
     */
    public static function resolveForDate(string $date): void
    {
        self::where('type', 'missing_entry')
            ->whereDate('date', $date)
            ->delete();
    }
}
