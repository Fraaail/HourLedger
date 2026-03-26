<?php

namespace App\Http\Controllers;

use App\Models\Profile;
use App\Models\Setting;
use App\Models\TimeEntry;
use App\Support\ActiveProfile;
use Carbon\Carbon;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function index()
    {
        $timezone = Setting::get('timezone', config('app.timezone'));
        $timezones = timezone_identifiers_list();
        $theme = Setting::get('theme', 'dark');
        $missingEntriesReminderEnabled = Setting::get('missing_entries_reminder_enabled', '1') === '1';

        return view('settings', compact('timezone', 'timezones', 'theme', 'missingEntriesReminderEnabled'));
    }

    public function updateTimezone(Request $request)
    {
        $request->validate([
            'timezone' => ['required', 'string', 'timezone'],
        ]);

        Setting::set('timezone', $request->input('timezone'));

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Timezone updated.',
                'currentTime' => now()->timezone($request->input('timezone'))->format('h:i A'),
                'timezone' => $request->input('timezone'),
            ]);
        }

        return redirect()->route('settings')->with('success', 'Timezone updated.');
    }

    public function updateTheme(Request $request)
    {
        $request->validate([
            'theme' => ['required', 'string', 'in:dark,light,system'],
        ]);

        Setting::set('theme', $request->input('theme'));

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Theme updated.']);
        }

        return redirect()->route('settings')->with('success', 'Theme updated.');
    }

    public function updateMissingEntriesReminder(Request $request)
    {
        $validated = $request->validate([
            'enabled' => ['required', 'boolean'],
        ]);

        $enabled = (bool) $validated['enabled'];
        Setting::set('missing_entries_reminder_enabled', $enabled ? '1' : '0');

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Missing entry reminders updated.',
                'enabled' => $enabled,
                'payload' => $this->buildMissingEntriesReminderPayload($enabled),
            ]);
        }

        return redirect()->route('settings')->with('success', 'Missing entry reminders updated.');
    }

    private function buildMissingEntriesReminderPayload(bool $enabled): array
    {
        $profileId = ActiveProfile::id();
        $timezone = Setting::get('timezone', config('app.timezone'));
        $today = Carbon::now($timezone)->toDateString();

        $entryToday = TimeEntry::where('profile_id', $profileId)
            ->where('date', $today)
            ->first();

        $profileName = Profile::find($profileId)?->name ?? 'HourLedger';

        return [
            'enabled' => $enabled,
            'timezone' => $timezone,
            'profile_name' => $profileName,
            'hour' => 9,
            'minute' => 0,
            'skip_today' => (bool) ($entryToday?->time_in),
        ];
    }
}
