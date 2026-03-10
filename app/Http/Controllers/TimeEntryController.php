<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\TimeEntry;
use Carbon\Carbon;

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
        $entryToday = TimeEntry::where('date', $today)->first();

        $totalMinutes = TimeEntry::sum('total_minutes');
        $totalDays = TimeEntry::whereNotNull('time_out')->count();

        $missingEntries = $this->getMissingEntries();

        return view('dashboard', compact('entryToday', 'totalMinutes', 'totalDays', 'missingEntries', 'tz'));
    }

    public function timeIn()
    {
        $tz = $this->getTimezone();
        $today = Carbon::now($tz)->toDateString();
        TimeEntry::firstOrCreate(
            ['date' => $today],
            ['time_in' => Carbon::now()]
        );

        return redirect()->route('dashboard');
    }

    public function timeOut()
    {
        $tz = $this->getTimezone();
        $today = Carbon::now($tz)->toDateString();
        $entry = TimeEntry::where('date', $today)->first();

        if ($entry && $entry->time_in && ! $entry->time_out) {
            $now = Carbon::now();
            $minutes = (int) abs($entry->time_in->diffInMinutes($now));
            $entry->update([
                'time_out' => $now,
                'total_minutes' => $minutes,
            ]);
        }

        return redirect()->route('dashboard');
    }

    public function calendar()
    {
        $tz = $this->getTimezone();
        $entries = TimeEntry::all()->keyBy('date');

        return view('calendar', compact('entries', 'tz'));
    }

    private function getMissingEntries()
    {
        $firstEntry = TimeEntry::orderBy('date')->first();
        if (! $firstEntry) {
            return [];
        }

        $tz = $this->getTimezone();
        $start = Carbon::parse($firstEntry->date, $tz);
        $end = Carbon::now($tz)->subDay();

        $missing = [];
        for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
            if ($date->isWeekday()) {
                $exists = TimeEntry::where('date', $date->toDateString())->whereNotNull('time_out')->exists();
                if (! $exists) {
                    $missing[] = $date->toDateString();
                }
            }
        }

        return $missing;
    }
}
