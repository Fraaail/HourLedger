<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    protected $fillable = ['name', 'is_default', 'is_archived', 'biometric_auth'];

    protected $casts = [
        'is_default' => 'boolean',
        'is_archived' => 'boolean',
        'biometric_auth' => 'boolean',
    ];

    public static function ensureDefault(): self
    {
        $default = static::where('is_default', true)->orderBy('id')->first();
        if ($default) {
            if ($default->is_archived) {
                $default->update(['is_archived' => false]);
            }

            return $default;
        }

        $firstActive = static::where('is_archived', false)->orderBy('id')->first();
        if ($firstActive) {
            $firstActive->update(['is_default' => true]);

            return $firstActive;
        }

        $first = static::orderBy('id')->first();
        if ($first) {
            $first->update([
                'is_default' => true,
                'is_archived' => false,
            ]);

            return $first;
        }

        return static::create([
            'name' => 'Default',
            'is_default' => true,
            'is_archived' => false,
        ]);
    }

    public static function ordered(): Collection
    {
        return static::where('is_archived', false)
            ->orderBy('name')
            ->orderBy('id')
            ->get();
    }

    public static function orderedForManagement(): Collection
    {
        return static::orderBy('is_archived')
            ->orderBy('name')
            ->orderBy('id')
            ->get();
    }
}
