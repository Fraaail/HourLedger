<?php

namespace App\Models;

use App\Support\ActiveProfile;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = ['profile_id', 'key', 'value'];

    protected static function booted(): void
    {
        static::creating(function (self $setting): void {
            if (! $setting->profile_id) {
                $setting->profile_id = ActiveProfile::id();
            }
        });
    }

    public static function get(string $key, ?string $default = null): ?string
    {
        $setting = static::where('profile_id', ActiveProfile::id())
            ->where('key', $key)
            ->first();

        return $setting ? $setting->value : $default;
    }

    public static function set(string $key, ?string $value): void
    {
        static::updateOrCreate(
            ['profile_id' => ActiveProfile::id(), 'key' => $key],
            ['value' => $value]
        );
    }
}
