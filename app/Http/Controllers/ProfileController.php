<?php

namespace App\Http\Controllers;

use App\Models\Profile;
use App\Support\ActiveProfile;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function store(Request $request)
    {
        $payload = $request->validate([
            'name' => ['required', 'string', 'max:60', 'unique:profiles,name'],
        ]);

        $profile = Profile::create([
            'name' => trim($payload['name']),
            'is_default' => false,
        ]);

        ActiveProfile::set($profile->id);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Profile created.',
                'profile_id' => $profile->id,
            ]);
        }

        return redirect()->route('settings')->with('success', 'Profile created.');
    }

    public function switchProfile(Request $request)
    {
        $payload = $request->validate([
            'profile_id' => ['required', 'integer', 'exists:profiles,id'],
        ]);

        $profile = ActiveProfile::set((int) $payload['profile_id']);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Profile switched.',
                'profile_id' => $profile->id,
            ]);
        }

        return back()->with('success', 'Switched profile to '.$profile->name.'.');
    }
}
