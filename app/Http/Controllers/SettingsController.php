<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function index()
    {
        $timezone = Setting::get('timezone', config('app.timezone'));
        $timezones = timezone_identifiers_list();
        $theme = Setting::get('theme', 'dark');

        return view('settings', compact('timezone', 'timezones', 'theme'));
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
}
