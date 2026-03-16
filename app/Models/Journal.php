<?php

namespace App\Models;

use App\Support\ActiveProfile;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Journal extends Model
{
    protected $fillable = ['profile_id', 'date', 'content'];

    protected static function booted(): void
    {
        static::creating(function (self $journal): void {
            if (! $journal->profile_id) {
                $journal->profile_id = ActiveProfile::id();
            }
        });
    }

    public function profile(): BelongsTo
    {
        return $this->belongsTo(Profile::class);
    }
}
