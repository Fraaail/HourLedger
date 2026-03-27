<?php

namespace App\Http\Controllers;

use App\Models\Journal;
use App\Models\Profile;
use App\Models\Setting;
use App\Models\TimeEntry;
use App\Support\ActiveProfile;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

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

    public function exportTimesheetCsv()
    {
        $profile = ActiveProfile::current();
        $timezone = $this->getTimezone();

        $entries = TimeEntry::where('profile_id', $profile->id)
            ->orderBy('date')
            ->get();

        $journalsByDate = Journal::where('profile_id', $profile->id)
            ->pluck('content', 'date');

        $handle = fopen('php://temp', 'r+');

        fputcsv($handle, ['date', 'time_in', 'time_out', 'total_hours', 'journal']);

        foreach ($entries as $entry) {
            fputcsv($handle, [
                $entry->date,
                $entry->time_in?->timezone($timezone)->format('Y-m-d H:i:s') ?? '',
                $entry->time_out?->timezone($timezone)->format('Y-m-d H:i:s') ?? '',
                $entry->total_minutes !== null ? number_format($entry->total_minutes / 60, 2, '.', '') : '',
                $journalsByDate[$entry->date] ?? '',
            ]);
        }

        rewind($handle);
        $csv = stream_get_contents($handle) ?: '';
        fclose($handle);

        $fileSafeProfileName = Str::slug($profile->name) ?: 'profile';
        $filename = 'timesheet-'.$fileSafeProfileName.'-'.Carbon::now()->format('Ymd-His').'.csv';

        return response("\xEF\xBB\xBF".$csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }

    public function widgetSummary(Request $request): JsonResponse
    {
        $requestedProfileId = (int) $request->query('profile_id', 0);

        $profile = $requestedProfileId > 0
            ? Profile::where('id', $requestedProfileId)->where('is_archived', false)->first()
            : null;

        if (! $profile) {
            $profile = ActiveProfile::current();
        }

        return response()->json($this->buildWidgetSummary($profile->id, $profile->name));
    }

    private function buildWidgetSummary(int $profileId, string $profileName): array
    {
        $timezone = Setting::where('profile_id', $profileId)
            ->where('key', 'timezone')
            ->value('value') ?? config('app.timezone');

        $today = Carbon::now($timezone)->toDateString();
        $entryToday = TimeEntry::where('profile_id', $profileId)
            ->where('date', $today)
            ->first();

        $totalMinutes = (int) TimeEntry::where('profile_id', $profileId)->sum('total_minutes');
        $totalDays = TimeEntry::where('profile_id', $profileId)->whereNotNull('time_out')->count();

        $status = ($entryToday && $entryToday->time_in && ! $entryToday->time_out)
            ? 'clocked_in'
            : 'clocked_out';

        return [
            'profile_id' => $profileId,
            'profile_name' => $profileName,
            'status' => $status,
            'status_label' => $status === 'clocked_in' ? 'Clocked In' : 'Clocked Out',
            'total_minutes' => $totalMinutes,
            'total_hours' => round($totalMinutes / 60, 1),
            'total_days' => $totalDays,
            'clocked_in_at' => $entryToday?->time_in?->timezone($timezone)->format('h:i A'),
            'clocked_out_at' => $entryToday?->time_out?->timezone($timezone)->format('h:i A'),
            'timezone' => $timezone,
            'updated_at' => Carbon::now()->toIso8601String(),
        ];
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
