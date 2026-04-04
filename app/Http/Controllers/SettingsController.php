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
        $criticalAlertsEnabled = Setting::get('critical_alerts_enabled', '0') === '1';
        $criticalAlertRequiredMinutes = max(60, min(960, (int) (Setting::get('critical_alert_required_minutes', '480') ?? '480')));
        $criticalAlertHour = max(0, min(23, (int) (Setting::get('critical_alert_hour', '18') ?? '18')));
        $criticalAlertMinute = max(0, min(59, (int) (Setting::get('critical_alert_minute', '0') ?? '0')));

        $criticalUnderHoursPayload = $this->buildCriticalUnderHoursPayload(
            $criticalAlertsEnabled,
            $criticalAlertRequiredMinutes,
            $criticalAlertHour,
            $criticalAlertMinute
        );

        return view('settings', compact(
            'timezone',
            'timezones',
            'theme',
            'missingEntriesReminderEnabled',
            'criticalAlertsEnabled',
            'criticalAlertRequiredMinutes',
            'criticalAlertHour',
            'criticalAlertMinute',
            'criticalUnderHoursPayload'
        ));
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

    public function updateCriticalAlerts(Request $request)
    {
        $validated = $request->validate([
            'enabled' => ['required', 'boolean'],
            'required_minutes' => ['required', 'integer', 'min:60', 'max:960'],
            'hour' => ['required', 'integer', 'between:0,23'],
            'minute' => ['required', 'integer', 'between:0,59'],
        ]);

        $enabled = (bool) $validated['enabled'];
        $requiredMinutes = (int) $validated['required_minutes'];
        $hour = (int) $validated['hour'];
        $minute = (int) $validated['minute'];

        Setting::set('critical_alerts_enabled', $enabled ? '1' : '0');
        Setting::set('critical_alert_required_minutes', (string) $requiredMinutes);
        Setting::set('critical_alert_hour', (string) $hour);
        Setting::set('critical_alert_minute', (string) $minute);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Under-hours alerts updated.',
                'enabled' => $enabled,
                'payload' => $this->buildCriticalUnderHoursPayload($enabled, $requiredMinutes, $hour, $minute),
            ]);
        }

        return redirect()->route('settings')->with('success', 'Under-hours alerts updated.');
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

    private function buildCriticalUnderHoursPayload(bool $enabled, int $requiredMinutes, int $hour, int $minute): array
    {
        $profileId = ActiveProfile::id();
        $timezone = Setting::get('timezone', config('app.timezone'));
        $today = Carbon::now($timezone)->toDateString();

        $entryToday = TimeEntry::where('profile_id', $profileId)
            ->where('date', $today)
            ->first();

        $todayTotalMinutes = 0;

        if ($entryToday?->total_minutes !== null) {
            $todayTotalMinutes = (int) $entryToday->total_minutes;
        } elseif ($entryToday?->time_in) {
            $todayTotalMinutes = (int) abs($entryToday->time_in->diffInMinutes(Carbon::now()));
        }

        $profileName = Profile::find($profileId)?->name ?? 'HourLedger';

        return [
            'enabled' => $enabled,
            'timezone' => $timezone,
            'profile_name' => $profileName,
            'required_minutes' => max(60, min(960, $requiredMinutes)),
            'today_total_minutes' => max(0, $todayTotalMinutes),
            'under_hours' => $todayTotalMinutes < $requiredMinutes,
            'hour' => max(0, min(23, $hour)),
            'minute' => max(0, min(59, $minute)),
        ];
    }
}
