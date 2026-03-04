<?php

namespace App\Http\Controllers;

use App\Models\EntryNotification;
use App\Models\TimeEntry;
use Carbon\Carbon;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        // Generate missing entry notifications
        EntryNotification::generateMissingEntryNotifications();

        // Today's entry
        $today = TimeEntry::whereDate('date', today())->first();

        // Calculate metrics
        $completedEntries = TimeEntry::complete()->get();

        $totalRenderedHours = $completedEntries->sum(function ($entry) {
            return $entry->rendered_hours;
        });

        $totalRenderedDays = $completedEntries->count();

        $averageHoursPerDay = $totalRenderedDays > 0
            ? round($totalRenderedHours / $totalRenderedDays, 2)
            : 0;

        // Current week hours
        $currentWeekEntries = TimeEntry::currentWeek()->complete()->get();
        $currentWeekHours = $currentWeekEntries->sum(function ($entry) {
            return $entry->rendered_hours;
        });

        // Unread notification count
        $unreadNotificationCount = EntryNotification::unread()->count();

        return Inertia::render('dashboard', [
            'today' => $today ? [
                'id' => $today->id,
                'date' => $today->date->toDateString(),
                'time_in' => $today->time_in?->toISOString(),
                'time_out' => $today->time_out?->toISOString(),
                'formatted_time_in' => $today->formatted_time_in,
                'formatted_time_out' => $today->formatted_time_out,
                'rendered_hours' => $today->rendered_hours,
                'status' => $today->status,
                'notes' => $today->notes,
            ] : null,
            'metrics' => [
                'total_rendered_hours' => round($totalRenderedHours, 2),
                'total_rendered_days' => $totalRenderedDays,
                'average_hours_per_day' => $averageHoursPerDay,
                'current_week_hours' => round($currentWeekHours, 2),
            ],
            'unread_notification_count' => $unreadNotificationCount,
        ]);
    }
}
