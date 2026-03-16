<?php

namespace App\Http\Controllers;

use App\Models\Journal;
use App\Models\Setting;
use App\Models\TimeEntry;
use App\Support\ActiveProfile;
use Carbon\Carbon;

class TimeEntryController extends Controller
{
    private function getTimezone(): string
    {
        return Setting::get('timezone', config('app.timezone'));
    }

    public function index()
    {
        $profileId = ActiveProfile::id();
        $tz = $this->getTimezone();
        $today = Carbon::now($tz)->toDateString();
        $entryToday = TimeEntry::where('profile_id', $profileId)->where('date', $today)->first();

        $totalMinutes = TimeEntry::where('profile_id', $profileId)->sum('total_minutes');
        $totalDays = TimeEntry::where('profile_id', $profileId)->whereNotNull('time_out')->count();

        $missingEntries = $this->getMissingEntries();

        return view('dashboard', compact('entryToday', 'totalMinutes', 'totalDays', 'missingEntries', 'tz'));
    }

    public function timeIn()
    {
        $profileId = ActiveProfile::id();
        $tz = $this->getTimezone();
        $today = Carbon::now($tz)->toDateString();
        TimeEntry::firstOrCreate(
            ['profile_id' => $profileId, 'date' => $today],
            ['time_in' => Carbon::now()]
        );

        return redirect()->route('dashboard');
    }

    public function timeOut()
    {
        $profileId = ActiveProfile::id();
        $tz = $this->getTimezone();
        $today = Carbon::now($tz)->toDateString();
        $entry = TimeEntry::where('profile_id', $profileId)->where('date', $today)->first();

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
        $profileId = ActiveProfile::id();
        $tz = $this->getTimezone();
        $entries = TimeEntry::where('profile_id', $profileId)->get()->keyBy('date');
        $journals = Journal::where('profile_id', $profileId)->get()->keyBy('date');

        return view('calendar', compact('entries', 'journals', 'tz'));
    }

    private function getMissingEntries()
    {
        $profileId = ActiveProfile::id();
        $firstEntry = TimeEntry::where('profile_id', $profileId)->orderBy('date')->first();
        if (! $firstEntry) {
            return [];
        }

        $tz = $this->getTimezone();
        $start = Carbon::parse($firstEntry->date, $tz);
        $end = Carbon::now($tz)->subDay();

        $missing = [];
        for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
            if ($date->isWeekday()) {
                $exists = TimeEntry::where('profile_id', $profileId)
                    ->where('date', $date->toDateString())
                    ->whereNotNull('time_out')
                    ->exists();
                if (! $exists) {
                    $missing[] = $date->toDateString();
                }
            }
        }

        return $missing;
    }
}
