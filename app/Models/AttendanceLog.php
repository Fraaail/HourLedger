<?php

namespace App\Models;

use App\Support\ActiveProfile;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceLog extends Model
{
    protected $fillable = [
        'profile_id',
        'date',
        'time_in',
        'time_out',
        'total_minutes',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $log): void {
            if (! $log->profile_id) {
                $log->profile_id = ActiveProfile::id();
            }
        });
    }

    public function profile(): BelongsTo
    {
        return $this->belongsTo(Profile::class);
    }

    protected $casts = [
        'date' => 'date',
        'time_in' => 'datetime',
        'time_out' => 'datetime',
    ];
}
