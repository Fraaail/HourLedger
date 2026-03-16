<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    protected $fillable = ['name', 'is_default'];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    public static function ensureDefault(): self
    {
        $default = static::where('is_default', true)->orderBy('id')->first();
        if ($default) {
            return $default;
        }

        $first = static::orderBy('id')->first();
        if ($first) {
            $first->update(['is_default' => true]);

            return $first;
        }

        return static::create([
            'name' => 'Default',
            'is_default' => true,
        ]);
    }

    public static function ordered(): Collection
    {
        return static::orderBy('name')->orderBy('id')->get();
    }
}
