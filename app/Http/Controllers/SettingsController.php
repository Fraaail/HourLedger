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

        return view('settings', compact('timezone', 'timezones'));
    }

    public function updateTimezone(Request $request)
    {
        $request->validate([
            'timezone' => ['required', 'string', 'timezone'],
        ]);

        Setting::set('timezone', $request->input('timezone'));

        return redirect()->route('settings')->with('success', 'Timezone updated.');
    }
}
