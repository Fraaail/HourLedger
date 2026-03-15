<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $fillable = ['key', 'value'];

    /**
     * Request-level cache to avoid repeated DB/cache lookups in a single render.
     *
     * @var array<string, string|null>
     */
    private static array $runtimeCache = [];

    public static function get(string $key, ?string $default = null): ?string
    {
        if (array_key_exists($key, self::$runtimeCache)) {
            return self::$runtimeCache[$key] ?? $default;
        }

        $cacheKey = "settings:{$key}";
        $value = Cache::rememberForever($cacheKey, function () use ($key) {
            return static::where('key', $key)->value('value');
        });

        self::$runtimeCache[$key] = $value;

        return $value ?? $default;
    }

    public static function set(string $key, ?string $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);

        self::$runtimeCache[$key] = $value;
        Cache::forget("settings:{$key}");
    }
}
