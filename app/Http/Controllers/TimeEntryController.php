<?php

namespace App\Http\Controllers;

use App\Models\EntryNotification;
use App\Models\TimeEntry;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TimeEntryController extends Controller
{
    /**
     * Clock in for today.
     */
    public function clockIn(): RedirectResponse
    {
        $today = today()->toDateString();

        $entry = TimeEntry::firstOrCreate(
            ['date' => $today],
            [
                'time_in' => now(),
                'time_out' => null,
            ]
        );

        // If entry already exists but has no time_in, update it
        if (! $entry->wasRecentlyCreated && ! $entry->time_in) {
            $entry->update(['time_in' => now()]);
        }

        // Resolve any missing entry notification for today
        EntryNotification::resolveForDate($today);

        return redirect()->route('dashboard');
    }

    /**
     * Clock out for today.
     */
    public function clockOut(): RedirectResponse
    {
        $entry = TimeEntry::whereDate('date', today())->first();

        if ($entry && $entry->time_in && ! $entry->time_out) {
            $entry->update(['time_out' => now()]);
        }

        return redirect()->route('dashboard');
    }

    /**
     * Calendar view.
     */
    public function calendar(Request $request): Response
    {
        $year = $request->input('year', now()->year);
        $month = $request->input('month', now()->month);

        $entries = TimeEntry::forMonth($year, $month)->get()->map(function ($entry) {
            return [
                'id' => $entry->id,
                'date' => $entry->date->toDateString(),
                'time_in' => $entry->time_in?->toISOString(),
                'time_out' => $entry->time_out?->toISOString(),
                'formatted_time_in' => $entry->formatted_time_in,
                'formatted_time_out' => $entry->formatted_time_out,
                'rendered_hours' => $entry->rendered_hours,
                'status' => $entry->status,
                'notes' => $entry->notes,
            ];
        });

        $unreadNotificationCount = EntryNotification::unread()->count();

        return Inertia::render('calendar', [
            'entries' => $entries,
            'year' => (int) $year,
            'month' => (int) $month,
            'unread_notification_count' => $unreadNotificationCount,
        ]);
    }

    /**
     * Store or update a manual time entry.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'time_in' => 'required|date',
            'time_out' => 'nullable|date|after:time_in',
            'notes' => 'nullable|string|max:500',
        ]);

        TimeEntry::updateOrCreate(
            ['date' => $validated['date']],
            [
                'time_in' => Carbon::parse($validated['time_in']),
                'time_out' => isset($validated['time_out']) ? Carbon::parse($validated['time_out']) : null,
                'notes' => $validated['notes'] ?? null,
            ]
        );

        // Resolve any missing entry notification for this date
        EntryNotification::resolveForDate($validated['date']);

        return back();
    }

    /**
     * Update an existing time entry.
     */
    public function update(Request $request, TimeEntry $timeEntry): RedirectResponse
    {
        $validated = $request->validate([
            'time_in' => 'required|date',
            'time_out' => 'nullable|date|after:time_in',
            'notes' => 'nullable|string|max:500',
        ]);

        $timeEntry->update([
            'time_in' => Carbon::parse($validated['time_in']),
            'time_out' => isset($validated['time_out']) ? Carbon::parse($validated['time_out']) : null,
            'notes' => $validated['notes'] ?? null,
        ]);

        return back();
    }

    /**
     * Delete a time entry.
     */
    public function destroy(TimeEntry $timeEntry): RedirectResponse
    {
        $timeEntry->delete();

        return back();
    }
}
