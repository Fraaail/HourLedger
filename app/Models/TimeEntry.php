<?php

namespace App\Models;

use App\Support\ActiveProfile;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TimeEntry extends Model
{
    protected $fillable = ['profile_id', 'date', 'time_in', 'time_out', 'total_minutes'];

    protected static function booted(): void
    {
        static::creating(function (self $entry): void {
            if (! $entry->profile_id) {
                $entry->profile_id = ActiveProfile::id();
            }
        });
    }

    public function profile(): BelongsTo
    {
        return $this->belongsTo(Profile::class);
    }

    protected function casts(): array
    {
        return [
            'time_in' => 'datetime',
            'time_out' => 'datetime',
        ];
    }
}
