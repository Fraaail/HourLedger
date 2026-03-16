<?php

namespace App\Support;

use App\Models\Profile;

class ActiveProfile
{
    public const SESSION_KEY = 'active_profile_id';

    public static function current(): Profile
    {
        $sessionProfileId = self::sessionProfileId();

        if ($sessionProfileId !== null) {
            $profile = Profile::find($sessionProfileId);
            if ($profile) {
                return $profile;
            }
        }

        $default = Profile::ensureDefault();
        self::writeSessionProfileId($default->id);

        return $default;
    }

    public static function id(): int
    {
        return self::current()->id;
    }

    public static function set(int $profileId): Profile
    {
        $profile = Profile::find($profileId) ?? Profile::ensureDefault();
        self::writeSessionProfileId($profile->id);

        return $profile;
    }

    private static function sessionProfileId(): ?int
    {
        if (! app()->bound('request')) {
            return null;
        }

        $request = request();
        if (! $request->hasSession()) {
            return null;
        }

        $value = $request->session()->get(self::SESSION_KEY);

        return is_numeric($value) ? (int) $value : null;
    }

    private static function writeSessionProfileId(int $profileId): void
    {
        if (! app()->bound('request')) {
            return;
        }

        $request = request();
        if (! $request->hasSession()) {
            return;
        }

        $request->session()->put(self::SESSION_KEY, $profileId);
    }
}
