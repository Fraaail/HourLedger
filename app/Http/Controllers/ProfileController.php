<?php

namespace App\Http\Controllers;

use App\Models\AttendanceLog;
use App\Models\Journal;
use App\Models\Profile;
use App\Models\Setting;
use App\Models\TimeEntry;
use App\Support\ActiveProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function index()
    {
        $managedProfiles = Profile::orderedForManagement();

        return view('profiles', compact('managedProfiles'));
    }

    public function store(Request $request)
    {
        $payload = $request->validate([
            'name' => ['required', 'string', 'max:60', 'unique:profiles,name'],
        ]);

        $profile = Profile::create([
            'name' => trim($payload['name']),
            'is_default' => false,
            'is_archived' => false,
        ]);

        ActiveProfile::set($profile->id);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Profile created.',
                'profile_id' => $profile->id,
            ]);
        }

        return redirect()->route('profiles.index')->with('success', 'Profile created.');
    }

    public function switchProfile(Request $request)
    {
        $payload = $request->validate([
            'profile_id' => ['required', 'integer'],
        ]);

        $profile = Profile::findOrFail((int) $payload['profile_id']);

        if ($profile->biometric_auth && \Native\Mobile\Facades\System::isMobile()) {
            $lastBiometricId = 'switch-profile-' . $profile->id;

            if (!session('_native_biometric_success_' . $lastBiometricId)) {
                \Native\Mobile\Facades\Biometrics::prompt()
                    ->id($lastBiometricId)
                    ->remember()
                    ->prompt();

                return response()->json([
                    'success' => true,
                    'requires_biometrics' => true,
                    'biometric_id' => $lastBiometricId,
                    'profile_id' => $profile->id,
                ]);
            }
        }

        ActiveProfile::set($profile->id);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Profile switched.',
                'profile_id' => $profile->id,
            ]);
        }

        return back()->with('success', 'Switched profile to '.$profile->name.'.');
    }

    public function update(Request $request, Profile $profile)
    {
        $payload = $request->validate([
            'name' => ['required', 'string', 'max:60', Rule::unique('profiles', 'name')->ignore($profile->id)],
            'biometric_auth' => ['nullable', 'boolean'],
        ]);

        $profile->update([
            'name' => trim($payload['name']),
            'biometric_auth' => $request->boolean('biometric_auth'),
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Profile updated.',
                'profile_id' => $profile->id,
            ]);
        }

        return back()->with('success', 'Profile updated.');
    }

    public function archive(Request $request, Profile $profile)
    {
        if ($profile->is_default) {
            return $this->profileActionError($request, 'The default profile cannot be archived.');
        }

        $activeCount = Profile::where('is_archived', false)->count();
        if ($activeCount <= 1) {
            return $this->profileActionError($request, 'You must keep at least one active profile.');
        }

        $activeProfileId = ActiveProfile::id();

        $profile->update([
            'is_archived' => true,
        ]);

        if ($activeProfileId === $profile->id) {
            ActiveProfile::set(Profile::ensureDefault()->id);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Profile archived.',
            ]);
        }

        return back()->with('success', 'Profile archived.');
    }

    public function destroy(Request $request, Profile $profile)
    {
        if ($profile->is_default) {
            return $this->profileActionError($request, 'The default profile cannot be deleted.');
        }

        if (ActiveProfile::id() === $profile->id) {
            return $this->profileActionError($request, 'Switch to another profile before deleting this one.');
        }

        DB::transaction(function () use ($profile): void {
            $this->deleteProfileData($profile->id);
            $profile->delete();
        });

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Profile deleted.',
            ]);
        }

        return back()->with('success', 'Profile deleted.');
    }

    public function unarchive(Request $request, Profile $profile)
    {
        if (! $profile->is_archived) {
            return $this->profileActionError($request, 'This profile is already active.');
        }

        $profile->update([
            'is_archived' => false,
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Profile unarchived.',
                'profile_id' => $profile->id,
            ]);
        }

        return redirect()->route('profiles.index')->with('success', 'Profile unarchived.');
    }

    private function deleteProfileData(int $profileId): void
    {
        TimeEntry::where('profile_id', $profileId)->delete();
        Journal::where('profile_id', $profileId)->delete();
        Setting::where('profile_id', $profileId)->delete();
        AttendanceLog::where('profile_id', $profileId)->delete();
    }

    private function profileActionError(Request $request, string $message)
    {
        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => $message,
            ], 422);
        }

        return back()->withErrors(['profile' => $message]);
    }
}
