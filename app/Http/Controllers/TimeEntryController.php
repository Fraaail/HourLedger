<?php

namespace App\Http\Controllers;

use App\Models\Journal;
use App\Models\Setting;
use App\Models\TimeEntry;
use Carbon\Carbon;
use Illuminate\Http\Request;

class TimeEntryController extends Controller
{
    private function getTimezone(): string
    {
        return Setting::get('timezone', config('app.timezone'));
    }

    public function index()
    {
        $tz = $this->getTimezone();
        $today = Carbon::now($tz)->toDateString();
        $entryToday = TimeEntry::whereDate('date', $today)->first();

        $totals = TimeEntry::query()
            ->selectRaw('COALESCE(SUM(total_minutes), 0) as total_minutes')
            ->selectRaw('COUNT(time_out) as total_days')
            ->first();

        $totalMinutes = (int) ($totals?->total_minutes ?? 0);
        $totalDays = (int) ($totals?->total_days ?? 0);

        $missingEntries = $this->getMissingEntries();

        return view('dashboard', compact('entryToday', 'totalMinutes', 'totalDays', 'missingEntries', 'tz'));
    }

    public function timeIn(Request $request)
    {
        $tz = $this->getTimezone();
        $today = Carbon::now($tz)->toDateString();
        TimeEntry::firstOrCreate(
            ['date' => $today],
            ['time_in' => Carbon::now()]
        );

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Clocked in successfully.',
                'redirect' => route('dashboard', [], false),
            ]);
        }

        return redirect()->route('dashboard');
    }

    public function timeOut(Request $request)
    {
        $tz = $this->getTimezone();
        $today = Carbon::now($tz)->toDateString();
        $entry = TimeEntry::whereDate('date', $today)->first();

        if ($entry && $entry->time_in && ! $entry->time_out) {
            $now = Carbon::now();
            $minutes = (int) abs($entry->time_in->diffInMinutes($now));
            $entry->update([
                'time_out' => $now,
                'total_minutes' => $minutes,
            ]);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Clocked out successfully.',
                'redirect' => route('dashboard', [], false),
            ]);
        }

        return redirect()->route('dashboard');
    }

    public function calendar()
    {
        $tz = $this->getTimezone();
        $now = Carbon::now($tz);
        $monthStart = $now->copy()->startOfMonth()->toDateString();
        $monthEnd = $now->copy()->endOfMonth()->toDateString();

        $entries = TimeEntry::query()
            ->whereBetween('date', [$monthStart, $monthEnd])
            ->get()
            ->keyBy('date');

        $journals = Journal::query()
            ->whereBetween('date', [$monthStart, $monthEnd])
            ->get()
            ->keyBy('date');

        return view('calendar', compact('entries', 'journals', 'tz'));
    }

    private function getMissingEntries(): array
    {
        $firstEntryDate = TimeEntry::query()->min('date');
        if (! $firstEntryDate) {
            return [];
        }

        $tz = $this->getTimezone();
        $start = Carbon::parse($firstEntryDate, $tz);
        $end = Carbon::now($tz)->subDay();

        if ($start->gt($end)) {
            return [];
        }

        $completedDates = TimeEntry::query()
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->whereNotNull('time_out')
            ->pluck('date')
            ->flip();

        $missing = [];
        for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
            if ($date->isWeekday()) {
                $dateKey = $date->toDateString();
                if (! isset($completedDates[$dateKey])) {
                    $missing[] = $date->toDateString();
                }
            }
        }

        return $missing;
    }
}
